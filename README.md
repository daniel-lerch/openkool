# OpenKool

[![](https://images.microbadger.com/badges/image/daniellerch/openkool:r48.svg)](https://microbadger.com/images/daniellerch/openkool:r48 "Get your own image badge on microbadger.com")
[![](https://images.microbadger.com/badges/version/daniellerch/openkool:r48.svg)](https://microbadger.com/images/daniellerch/openkool:r48 "Get your own version badge on microbadger.com")

This application is a dockerized version of kOOL form [churchtool.org](http://www.churchtool.org).

kOOL is the most advanced open source church organization software which is currently available.
Unfortunately, kOOL is built on old software and never had a good architecture. Improving this, however, would result in thousands of merge conflicts for every new upstream version. That is why OpenKool just aims to keep kOOL operational for next the years until there are better alternatives available.

## Installation
The recommended deployment option for OpenKool is to use Docker containers. An official image is available at [daniellerch/openkool:r48](https://hub.docker.com/r/daniellerch/openkool).

For a detailed installation guide please refer to the `docs` folder.


## Upgrading
All major upgrades have to be performed manually as they usually ship with breaking changes which require special attention. Read the changelogs at [OpenKool's releases page](https://github.com/daniel-lerch/openkool/releases) carefully.

You cannot skip major upgrades. If you want to upgrade from R45 to R48 for example you have to migrate to R46, R47 and finally to R48.

## Contributing
Contributions are very welcome. Please open an issue to discuss your wishes before implementing them.
