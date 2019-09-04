# Migrate from kOOL R45 to OpenKool

## Installation
- kOOL's multisite feature has been removed in OpenKool. You have to install OpenKool separately in each webroot.

## Configuration
- The option `$INCLUDE_PATH_SMARTY` has been removed. You have to install Smarty using Composer now.
- Rename your custom `config/kota.inc` file to `config/kota.inc.php`.
- Rename the `leute_formular.inc` file in cofig to `leute_formular.inc.php`.

## Plugins
- Rename the `kota.inc` file in plugin root to `kota.inc.php` if existent.
- The function `ko_export_to_excel` has been removed. Plugins using it have to find an alternative.
