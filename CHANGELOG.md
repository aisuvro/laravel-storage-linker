# Changelog

All notable changes to `laravel-storage-linker` will be documented in this file.

## [1.1.0] - 2025-06-19

### Added
- Added support for explicit disk name parameter in storage:link command
- Command now accepts disk name as argument: `php artisan storage:link {disk?}`
- Enhanced flexibility: use interactive selection when no disk specified, or target specific disk directly

### Changed
- Updated command signature to accept optional disk parameter
- Refactored command logic to handle both parameter-based and interactive modes

## [1.0.1] - 2025-06-19

### Fixed
- Fixed symbolic link creation by replacing `File::link()` with native PHP `symlink()` function
- Corrected hard link creation issue that prevented proper symbolic links from being established
- Improved error handling and success messages for symlink operations

## [1.0.0] - 2025-06-19

### Added
- Initial release
- Interactive symlink creation with disk selection
- Support for `--all` flag to create all symlinks at once
- Support for `--remove` flag to remove existing symlinks
- Support for `--force` flag to force recreation of symlinks
- Real-time symlink status checking
- Table display of available disks and their status
- Safe error handling and validation
- Support for Laravel 9.x, 10.x, and 11.x
- PHPUnit test suite with basic functionality tests
- Comprehensive documentation and examples
