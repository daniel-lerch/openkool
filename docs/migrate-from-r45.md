# Migrate from kOOL R45 to OpenKool

> ⚠️ If you want to migrate to OpenKool from an older kOOL version than R45 you have to upgrade to kOOL R45 first.

## Installation
- kOOL's multisite feature has been removed in OpenKool. You have to install OpenKool separately in each webroot.
- `install/update.phpsh` has been replaced by `install/console.php` which offers an command `migrate` which will run all pending database migrations. You can use this command to migrate your database from _kOOL R45_ to _OpenKool_.

## Configuration
- The option `$INCLUDE_PATH_SMARTY` has been removed. You have to install Smarty using Composer now.
- Rename your custom `config/kota.inc` file to `config/kota.inc.php`.
- Rename the `leute_formular.inc` file in cofig to `leute_formular.inc.php`.

## Plugins
- Rename the `kota.inc` file in plugin root to `kota.inc.php` if existent.
- The function `ko_export_to_excel` has been removed. Plugins using it have to find an alternative.
