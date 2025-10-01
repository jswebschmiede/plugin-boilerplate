# Plugin Boilerplate

A WordPress plugin for creating and displaying bicycle rental or sales listings with an interactive tabbed interface.

## Description

The Plugin Boilerplate plugin allows you to create and manage example listings in WordPress. It provides a custom post type for examples, taxonomy for example types, and a shortcode to display an interactive overview of available examples grouped by type.

## Features

-   **Custom Post Type**: Create and manage example listings
-   **Taxonomy Support**: Organize examples by type

## Installation

1. Download the plugin ZIP file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Requirements

-   WordPress 5.0 or higher
-   PHP 7.4 or higher
-   Composer (for dependency management)
-   Node.js and pnpm (for building assets)

## Setup and Configuration

### 1. Install Dependencies

After activation, install PHP dependencies:

```bash
composer install
```

Install and build frontend assets:

```bash
pnpm install
pnpm run build
```

### 4. Display on Frontend

Add the shortcode to any page or post:

```php
[plugin-boilerplate]
```

## Usage

### Shortcode

Use the `[plugin-boilerplate]` shortcode on any page or post to display the plugin boilerplate overview.

## Development

### Build Process

```bash
# Install dependencies
composer install
pnpm install

# Build for production
composer run build

# Development build with watch
pnpm run dev

# Lint code
composer run php:lint
```

### Code Standards

-   Follows WordPress Coding Standards
-   Uses strict PHP types
-   PHPDoc comments in English
-   ESLint for JavaScript

### File Structure

```
plugin-boilerplate/
├── assets/                 # Source assets (JS/SCSS)
├── build/                  # Compiled assets
├── includes/               # PHP classes
│   ├── Assets/            # Asset management
│   ├── Backend/           # Admin functionality
│   └── Shortcodes/        # Shortcode handlers
├── languages/             # Translation files
├── views/                 # Template files
└── vendor/                # Composer dependencies
```

## API Reference

### Shortcodes

-   `[plugin-boilerplate]`: Displays the plugin boilerplate interface

### PHP Classes

-   `PluginBoilerplate`: Main plugin class
-   `ExamplePostType`: Custom post type and taxonomy management
-   `AdminSettings`: Admin settings page
-   `Shortcode`: Shortcode handler
-   `Assets`: Asset registration and management

### Hooks

-   `carbon_fields_register_fields`: Register custom fields
-   `init`: Register post types and taxonomies
-   `wp_enqueue_scripts`: Register frontend assets

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## License

MIT License

## Author

**Joerg Schoeneburg**

-   Website: [https://joerg-schoeneburg.de](https://joerg-schoeneburg.de)
-   Email: schoeneburg.j@googlemail.com

## Support

For support, please contact the author or create an issue in the repository.
