# OpenKool

[![](https://img.shields.io/docker/pulls/daniellerch/openkool.svg)](https://hub.docker.com/r/daniellerch/openkool)
[![](https://img.shields.io/docker/image-size/daniellerch/openkool/r48.svg)](https://hub.docker.com/r/daniellerch/openkool)
![](https://img.shields.io/badge/php-7.4-red)

This application is a dockerized version of kOOL form [churchtool.org](http://www.churchtool.org).

## Legacy Software

?? If you have never heard about kOOL, **do not use it**! ??

With PHP 7.4 having reached its end of life on 28 November 2022, OpenKool does not receive security fixes to its underlying Docker images anymore.
Supporting more recent PHP versions, however, is difficult: [#25](https://github.com/daniel-lerch/openkool/issues/25)

kOOL is probably the most advanced open source organization software for small churches available today.
Unfortunately, it is built on old software and never had a good architecture. Improving this, however, would result in thousands of merge conflicts for every new upstream version.
That is why OpenKool just aims to keep kOOL operational for next the years until there are better alternatives available.

## Installation
The recommended deployment option for OpenKool is to use Docker containers. An official image is available at [daniellerch/openkool:r48](https://hub.docker.com/r/daniellerch/openkool).

You can install OpenKool from scratch or take over your database from an existing kOOL instance. Please read the [installation guide](docs/install.md) for more information.


## Upgrading
All major upgrades have to be performed manually as they usually ship with breaking changes which require special attention. Read the changelogs at [OpenKool's releases page](https://github.com/daniel-lerch/openkool/releases) carefully.

You cannot skip major upgrades. If you want to upgrade from R45 to R48 for example you have to migrate to R46, R47 and finally to R48. Please refer to the `docs` folder for upgrade instructions.

## Contributing
Contributions are very welcome. Please open an issue to discuss your wishes before implementing them. For a development environment you will need PHP, XDebug and Visual Studio Code with PHP Intelephense and PHP Debug extensions. On Windows you will find `install/devenv.ps1` very useful and may use [psmodules](https://github.com/daniel-lerch/psmodules) to install a suitable database server.
