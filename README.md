# OpenKool

This application is an open source fork of kOOL form [churchtool.org](http://www.churchtool.org) which is maintained closed source on [laupercomputing.ch/kool](https://www.laupercomputing.ch/kool).

As kOOL is built on old software and never had a good architecture, it would be too much work to make it first class church organization software which is able to compete with ChurchTools or Planning Center. However, kOOL is the most advanced open source church organization software which is currently available. This fork aims to keep kOOL operational for next years until there are better alternatives available.

## Installation
The recommended deployment option is to use Docker containers. An example _Compose file_ is available in the `docs` folder.

### Running in a subfolder
It is possible to run OpenKool with a specific path base like https://domain.tld/kool. When you are using the Docker container, you have to add an `Alias` directive to the site configuration in `/etc/apache2/sites-available/000-default.conf` and mount this file outside of the container to persist your changes. In any deployment you have to prepend the `RewriteCond` patterns with your pathbase in the `.htaccess` file.

## Contributing
Contributions are very welcome. Please open an issue to discuss your wishes before implementing them.
