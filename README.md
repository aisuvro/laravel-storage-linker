# Laravel Storage Linker

[![Latest Version](https://img.shields.io/badge/version-1.0.1-blue.svg)](https://github.com/aisuvro/laravel-storage-linker)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-787CB5.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E9.0%7C%5E10.0%7C%5E11.0-FF2D20.svg)](https://laravel.com)

A Laravel package that provides an interactive way to create symbolic links for all your storage disks with local drivers.

## Recent Updates (v1.0.1)

🔧 **Fixed**: Resolved symbolic link creation issues by replacing hard link creation with proper symbolic links using native PHP `symlink()` function.

## Features

- 🔗 Interactive symlink creation with disk selection
- 📋 Display all available local disks in a table format
- ⚡ Create symlinks for all local disks with `--all` flag
- 🗑️ Remove existing symlinks with `--remove` flag
- 🔄 Force recreation of existing symlinks with `--force` flag
- ✅ Real-time symlink status checking
- 🛡️ Safe error handling and validation

## Installation

You can install the package via composer:

```bash
composer require aisuvro/laravel-storage-linker
```

The package will automatically register itself via Laravel's package discovery feature.

## Usage

### Interactive Mode

Run the command without any flags to see all available local disks and select which ones to link:

```bash
php artisan storage:link-all
```

This will show you a table with:
- Disk name
- Root path
- Current symlink status

You can then select one or multiple disks to create symlinks for.

### Create All Symlinks

To create symlinks for all local disks at once:

```bash
php artisan storage:link-all --all
```

### Remove Symlinks

To remove all existing symlinks:

```bash
php artisan storage:link-all --remove
```

### Force Recreation

To force recreation of symlinks (even if they already exist):

```bash
php artisan storage:link-all --all --force
```

## How It Works

The package scans your `config/filesystems.php` file for all disks with the `local` driver and creates symbolic links in the `public/storage/{disk-name}` directory pointing to the disk's root path.

### Example

If you have these disks configured:

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
    ],
    'uploads' => [
        'driver' => 'local',
        'root' => storage_path('app/uploads'),
    ],
    's3' => [
        'driver' => 's3',
        // ... s3 config (will be ignored)
    ],
],
```

The package will create:
- `public/storage/local` → `storage/app`
- `public/storage/public` → `storage/app/public`
- `public/storage/uploads` → `storage/app/uploads`

The S3 disk will be ignored since it's not a local driver.

## Requirements

- PHP ^8.1
- Laravel ^9.0|^10.0|^11.0

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to info@appenjel.com.

## Credits

- [Md. Al Imran Suvro](https://github.com/aisuvro)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
