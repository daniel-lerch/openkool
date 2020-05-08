#!/usr/bin/env bash

################################################################################
#
#    OpenKool - Online church organization tool
#
#    Copyright © 2003-2020 Renzo Lauper (renzo@churchtool.org)
#    Copyright © 2019-2020 Daniel Lerch
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
################################################################################

WEB_UID=www-data
WEB_GID=www-data

function dir_config() {
    echo "Preparing ~/config..."
    if [[ ! -d ../config ]]; then mkdir ../config; fi
    cp -f default/config/address.rtf default/config/footer.php default/config/header.php \
        default/config/ko-config.php default/config/leute_formular.inc ../config
    chown --recursive $WEB_UID:$WEB_GID ../config
}

function dir_download() {
    echo "Preparing ~/download..."
    if [[ ! -d ../download ]]; then mkdir ../download; fi
    if [[ ! -d ../download/dp ]]; then mkdir ../download/dp; fi
    if [[ ! -d ../download/excel ]]; then mkdir ../download/excel; fi
    if [[ ! -d ../download/pdf ]]; then mkdir ../download/pdf; fi
    if [[ ! -d ../download/word ]]; then mkdir ../download/word; fi
    cp -f default/download/index1.php ../download/index.php
    cp -f default/download/index2.php ../download/dp/index.php
    cp -f default/download/index2.php ../download/excel/index.php
    cp -f default/download/index2.php ../download/pdf/index.php
    cp -f default/download/index2.php ../download/word/index.php
    chown $WEB_UID:$WEB_GID ../download ../download/dp ../download/excel ../download/pdf ../download/word
}

function dir_latex() {
    echo "Preparing ~/latex..."
    if [[ ! -d ../latex/compile ]]; then mkdir ../latex/compile; fi
    if [[ ! -d ../latex/images ]]; then mkdir ../latex/images; fi
    cp -f default/latex/images/.htaccess ../latex/images
    cp -f default/latex/layouts/letter_default.lco ../latex/layouts
    chown --recursive $WEB_UID:$WEB_GID ../latex/compile ../latex/images
}

function dir_my_images() {
    echo "Preparing ~/my_images..."
    if [[ ! -d ../my_images ]]; then mkdir ../my_images; fi
    if [[ ! -d ../my_images/cache ]]; then mkdir ../my_images/cache; fi
    chown --recursive $WEB_UID:$WEB_GID ../my_images
}

function dir_templates_c() {
    echo "Preparing ~/templates_c..."
    if [[ ! -d ../templates_c ]]; then mkdir ../templates_c; fi
    chown $WEB_UID:$WEB_GID ../templates_c
}

function dir_webfolders() {
    echo "Preparing ~/webfolders and ~/.webfolders..."
    if [[ ! -d ../webfolders ]]; then mkdir ../webfolders; fi
    if [[ ! -d ../.webfolders ]]; then mkdir ../.webfolders; fi
    chown $WEB_UID:$WEB_GID ../webfolders ../.webfolders
}

function main() {
    echo "# OpenKool setup script #"
    echo ""
    cd $(dirname $0)

    if [[ $1 == "--docker-build" ]]; then
        dir_download
        dir_latex
        dir_templates_c

        echo ""
        echo "Initial setup finished. You need to run the script again with all Docker volumes mounted."
        echo ""
    else
        if grep -qa docker /proc/1/cgroup; then
            dir_config
            dir_my_images
            dir_webfolders
        else
            dir_config
            dir_download
            dir_latex
            dir_my_images
            dir_templates_c
            dir_webfolders
        fi
        
        echo ""
        echo "Setup finished. Navigate to http://kool.your.tld/install to continue with the webinstaller."
        echo ""
    fi
}

main $@
