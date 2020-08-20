# OpenKool R48 upgrade instructions

## Prerequisites
- Dockerized OpenKool R47 installation
- Backup of config files (might get overridden)
- MySQL database backup (no downgrade path)

## Steps
1. Change the image name in your compose file  
`daniellerch/openkool:r47` > `daniellerch/openkool:r48`
2. Pull image and restart  
`docker-compose up -d`
3. Run the setup script  
A) `docker-compose exec app bash /var/www/html/install/setup.sh`  
You reject overriding ko-config.php and other modified config files. Manual changes to ko-config.php are not required this time.  
B) `docker-compose exec app bash /var/www/html/install/setup.sh --force`  
The script will override your config files and you have to run the web installer again (see [install.md](install.md)).
4. Call install/update.phpsh from your web root with parameter -p to show changes, with -a to update the database and with -s to run update scripts.  
`docker-compose exec app bash`  
`./install/update.phpsh -a`  
`./install/update.phpsh -s`
6. Your upgrade is finished. You may enable the new module `telegram` in `ko-config.php` now.
