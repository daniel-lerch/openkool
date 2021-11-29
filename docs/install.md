# OpenKool installation guide

## General setup
1. Download `docker-compose.yml` from the `docs` folder
2. Add your database passwords to the downloaded Compose file
3. Download and launch OpenKool  
`docker-compose up -d`
4. Run the setup script  
`docker-compose exec app bash /var/www/html/install/setup.sh`
5. Enable installation  
`docker-compose exec app touch /var/www/html/install/ENABLE_INSTALL`
6. Open http://localhost/install/ in your browser and follow the instructions

## Migrate from kOOL
You can easily migrate your data from an existing kOOL instance to an OpenKool instance with the same major version.
1. Create a dump of your existing database
2. Copy the file into the database container
3. Import your database dump
4. Skip database initialization in the web installation wizard (step 6 above)

## Install in a subfolder
It is possible to run OpenKool with a specific path base like https://domain.tld/kool. However, it is not recommended because most of the JavaScript code does not respect custom path bases.
If you are using the Docker container, you have to add an `Alias` directive to the site configuration in `/etc/apache2/sites-available/000-default.conf` and mount this file outside of the container to persist your changes.
In any deployment you have to prepend the `RewriteCond` patterns with your pathbase in the `.htaccess` file.
