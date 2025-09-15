# Emoncms WiFi Module

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

The emoncms WiFi module is a PHP-based web application module that provides WiFi configuration management for emoncms installations, specifically designed for emonPi and emonBase devices. This module integrates with the main emoncms application to provide a web interface for WiFi network scanning, configuration, and management.

## Working Effectively

### Prerequisites
- PHP 8.0+ (PHP 8.3.6 confirmed working)
- Composer for dependency management
- Access to system WiFi commands via sudo (for full functionality)

### Bootstrap and Build
- Install PHP dependencies: `composer install --no-interaction --prefer-dist` -- takes 30 seconds. NEVER CANCEL. Set timeout to 2+ minutes.
- Run syntax validation: `composer test` -- takes less than 1 second. This runs php-parallel-lint on all PHP files.
- **IMPORTANT**: The vendor/ directory is excluded from git via .gitignore. Always run `composer install` after cloning.

### Testing and Validation
- Lint PHP files: `composer test` -- runs php-parallel-lint across all .php files
- Individual syntax check: `php -l filename.php` for any specific file
- **CRITICAL**: This module requires full emoncms installation and system-level WiFi access for complete functionality testing
- Basic class instantiation can be tested in isolation, but WiFi scanning/configuration requires sudo access to system WiFi commands

### Running the Application
- **CANNOT RUN STANDALONE**: This module must be installed within an emoncms installation at `/var/www/emoncms/Modules/wifi/`
- **WEB ACCESS**: Access via emoncms web interface at `http://emoncms-host/wifi` (requires emoncms authentication)
- **SYSTEM REQUIREMENTS**: Requires sudo permissions for www-data user to execute WiFi system commands

## Key Components

### PHP Files
- `wifi.php` - Main Wifi class with methods for scan, info, getconfig, setconfig, start, stop, restart
- `wifi_controller.php` - emoncms controller handling HTTP requests and routing to Wifi class
- `wifi_menu.php` - emoncms menu integration (adds WiFi to setup menu)

### Frontend
- `view.html` - Complete HTML/JavaScript/CSS interface for WiFi management
- `icons/` - WiFi signal strength icons (wifi0.png through wifi4.png, with secure variants)

### Configuration
- `module.json` - emoncms module metadata (name: "WiFi", version: "2.1.1")
- `composer.json` - PHP dependencies (only php-parallel-lint for testing)
- `.travis.yml` - Legacy CI configuration (PHP 7.0-7.3)

## Validation Requirements

### Manual Testing Scenarios
Since this module requires full emoncms + system WiFi access, complete validation requires:
1. **emoncms Installation**: Module must be in `/var/www/emoncms/Modules/wifi/`
2. **System Permissions**: sudo access configured as documented in README.md
3. **WiFi Hardware**: Functional WiFi interface (wlan0)

### Limited Testing Available
- **Syntax Validation**: `composer test` confirms all PHP files are syntactically correct
- **Class Instantiation**: Wifi class can be instantiated with mocked EmonLogger
- **Method Availability**: All expected methods (scan, info, getconfig, setconfig, start, stop, restart) are present

### CI/CD Validation
- Always run `composer test` before committing - this is the primary validation available
- No GitHub Actions workflow exists (only legacy .travis.yml)
- PHP syntax validation is the main automated quality gate

## Common Development Tasks

### Adding New Functionality
- Modify `wifi.php` for new WiFi management features
- Update `wifi_controller.php` for new HTTP endpoints
- Extend `view.html` for new UI features
- Always test with `composer test` after changes

### Debugging
- Check `wifi.php` method implementations for system command execution
- Review `view.html` JavaScript for frontend functionality
- Most functionality requires sudo access to system WiFi commands like:
  - `/sbin/ifconfig wlan0`
  - `wpa_cli -i wlan0 scan`
  - `/etc/wpa_supplicant/wpa_supplicant.conf` manipulation

### Code Style
- No specific code style enforcement beyond PHP syntax validation
- Follow existing PHP code patterns in the module
- JavaScript follows jQuery patterns established in view.html

## Important Notes

### System Integration
- **DEPLOYMENT TARGET**: Designed for Raspberry Pi-based emonPi/emonBase devices
- **SYSTEM COMMANDS**: Heavily relies on Linux WiFi system commands via sudo
- **SECURITY**: Requires specific sudo permissions for www-data user (documented in README.md)

### Development Limitations
- **NO STANDALONE TESTING**: Full functionality requires complete emoncms + system setup
- **LIMITED CI**: Only syntax validation available in development environment
- **HARDWARE DEPENDENT**: Real functionality requires WiFi hardware interface

### Performance Notes
- Dependency installation: ~30 seconds
- Syntax validation: <1 second
- WiFi scanning: 3+ seconds (includes hardware scan + sleep)
- Module is designed for occasional use, not high-frequency operations

## Repository Structure
```
.
├── .travis.yml           # Legacy CI configuration
├── LICENSE.txt          # GNU AGPL license
├── README.md           # Installation and configuration instructions
├── composer.json       # PHP dependencies (php-parallel-lint)
├── icons/             # WiFi signal strength icons
├── module.json        # emoncms module metadata
├── view.html         # Complete web interface
├── wifi.php         # Main Wifi class implementation
├── wifi_controller.php  # emoncms HTTP request controller
└── wifi_menu.php      # emoncms menu integration
```