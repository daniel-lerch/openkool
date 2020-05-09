# OpenKool

This application is a dockerized version of kOOL form [churchtool.org](http://www.churchtool.org).

kOOL is the most advanced open source church organization software which is currently available.
Unfortunately, kOOL is built on old software and never had a good architecture. Improving this, however, would result in thousands of merge conflicts for every new upstream version. That is why OpenKool just aims to keep kOOL operational for next the years until there are better alternatives available. 

## Installation
The recommended deployment option for OpenKool is to use Docker containers. An example _Compose file_ is available in the `docs` folder.

All major upgrades have to be performed manually as they usually ship with breaking changes which require special attention. Read the changelogs at [churchtool.org](http://www.churchtool.org) as well as at [OpenKool's releases page](https://github.com/daniel-lerch/openkool/releases) carefully. 

### Running in a subfolder
It is possible to run OpenKool with a specific path base like https://domain.tld/kool. When you are using the Docker container, you have to add an `Alias` directive to the site configuration in `/etc/apache2/sites-available/000-default.conf` and mount this file outside of the container to persist your changes. In any deployment you have to prepend the `RewriteCond` patterns with your pathbase in the `.htaccess` file.

## Contributing
Contributions are very welcome. Please open an issue to discuss your wishes before implementing them.
