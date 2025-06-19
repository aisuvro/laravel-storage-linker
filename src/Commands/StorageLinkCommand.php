<?php

namespace Aisuvro\LaravelStorageLinker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:link-all 
                            {disk? : The disk name to create symlink for}
                            {--all : Create links for all local disks} 
                            {--remove : Remove existing symlinks}
                            {--force : Force creation even if symlink exists}';

    /**
     * The console command description.
     */
    protected $description = 'Create symbolic links for storage disks (specify disk name, use --all for all disks, or interactive selection)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('remove')) {
            return $this->removeSymlinks();
        }

        $disks = $this->getLocalDisks();

        if (empty($disks)) {
            $this->error('No local disks found in your filesystem configuration.');
            return 1;
        }

        // Check if a specific disk name was provided
        $diskName = $this->argument('disk');
        if ($diskName) {
            return $this->createSymlinkForDisk($diskName, $disks);
        }

        if ($this->option('all')) {
            return $this->createAllSymlinks($disks);
        }

        return $this->interactiveSymlinkCreation($disks);
    }

    /**
     * Get all local disks from filesystem configuration.
     */
    protected function getLocalDisks(): array
    {
        $filesystems = config('filesystems.disks', []);
        $localDisks = [];

        foreach ($filesystems as $name => $config) {
            if (isset($config['driver']) && $config['driver'] === 'local') {
                $localDisks[$name] = $config;
            }
        }

        return $localDisks;
    }

    /**
     * Create symlinks for all local disks.
     */
    protected function createAllSymlinks(array $disks): int
    {
        $this->info('Creating symlinks for all local disks...');
        
        $success = 0;
        $failed = 0;

        foreach ($disks as $name => $config) {
            if ($this->createSymlink($name, $config)) {
                $success++;
            } else {
                $failed++;
            }
        }

        $this->info("Created {$success} symlinks successfully.");
        if ($failed > 0) {
            $this->warn("{$failed} symlinks failed to create.");
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Interactive symlink creation with user selection.
     */
    protected function interactiveSymlinkCreation(array $disks): int
    {
        $this->info('Available local disks:');
        $this->table(
            ['Disk Name', 'Root Path', 'Symlink Status'],
            collect($disks)->map(function ($config, $name) {
                $symlinkPath = $this->getSymlinkPath($name);
                $status = $this->getSymlinkStatus($symlinkPath);
                
                return [
                    $name,
                    $config['root'] ?? 'N/A',
                    $status
                ];
            })->values()->toArray()
        );

        $selectedDisks = $this->choice(
            'Which disks would you like to create symlinks for? (comma-separated for multiple)',
            array_keys($disks),
            null,
            null,
            true
        );

        if (empty($selectedDisks)) {
            $this->info('No disks selected.');
            return 0;
        }

        $success = 0;
        $failed = 0;

        foreach ($selectedDisks as $diskName) {
            if (isset($disks[$diskName])) {
                if ($this->createSymlink($diskName, $disks[$diskName])) {
                    $success++;
                } else {
                    $failed++;
                }
            }
        }

        $this->info("Created {$success} symlinks successfully.");
        if ($failed > 0) {
            $this->warn("{$failed} symlinks failed to create.");
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Create a symlink for a specific disk.
     */
    protected function createSymlink(string $diskName, array $config): bool
    {
        $symlinkPath = $this->getSymlinkPath($diskName);
        $targetPath = $config['root'] ?? null;

        if (!$targetPath) {
            $this->error("No root path configured for disk '{$diskName}'.");
            return false;
        }

        // Resolve relative paths
        if (!Str::startsWith($targetPath, '/')) {
            $targetPath = base_path($targetPath);
        }

        if (!File::exists($targetPath)) {
            $this->error("Target path does not exist: {$targetPath}");
            return false;
        }

        if (file_exists($symlinkPath)) {
            if (!$this->option('force')) {
                $this->error('The "' . $symlinkPath . '" link already exists.');
                return false;
            }
            
            File::delete($symlinkPath);
        }

        try {
            symlink($targetPath, $symlinkPath);
            $this->info('Symbolic link created successfully: ' . $symlinkPath . ' -> ' . $targetPath);
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to create symlink for '{$diskName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create symlink for a specific disk by name.
     */
    protected function createSymlinkForDisk(string $diskName, array $disks): int
    {
        if (!isset($disks[$diskName])) {
            $this->error("Disk '{$diskName}' not found or is not a local disk.");
            $this->info('Available local disks: ' . implode(', ', array_keys($disks)));
            return 1;
        }

        $config = $disks[$diskName];
        
        if ($this->createSymlink($diskName, $config)) {
            $this->info("Symlink created successfully for disk '{$diskName}'.");
            return 0;
        } else {
            $this->error("Failed to create symlink for disk '{$diskName}'.");
            return 1;
        }
    }

    /**
     * Remove existing symlinks.
     */
    protected function removeSymlinks(): int
    {
        $disks = $this->getLocalDisks();
        $removed = 0;

        foreach ($disks as $name => $config) {
            $symlinkPath = $this->getSymlinkPath($name);
            
            if (File::exists($symlinkPath) && is_link($symlinkPath)) {
                File::delete($symlinkPath);
                $this->info("Removed symlink for '{$name}': {$symlinkPath}");
                $removed++;
            }
        }

        if ($removed === 0) {
            $this->info('No symlinks found to remove.');
        } else {
            $this->info("Removed {$removed} symlinks.");
        }

        return 0;
    }

    /**
     * Get the symlink path for a disk.
     */
    protected function getSymlinkPath(string $diskName): string
    {
        return public_path("{$diskName}");
    }

    /**
     * Get the status of a symlink.
     */
    protected function getSymlinkStatus(string $path): string
    {
        if (!File::exists($path)) {
            return '❌ Not linked';
        }

        if (is_link($path)) {
            return File::exists(readlink($path)) ? '✅ Linked' : '⚠️ Broken link';
        }

        return '⚠️ File exists (not symlink)';
    }
}
