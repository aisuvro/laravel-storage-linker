<?php

namespace Aisuvro\LaravelStorageLinker\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Aisuvro\LaravelStorageLinker\StorageLinkerServiceProvider;

class StorageLinkCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            StorageLinkerServiceProvider::class,
        ];
    }

    public function test_command_is_registered()
    {
        $this->assertTrue($this->artisan('storage:link-all', ['--help']) !== null);
    }

    public function test_command_shows_help()
    {
        $this->artisan('storage:link-all', ['--help'])
            ->assertExitCode(0);
    }
}
