#!/usr/bin/env pwsh

<###############################################################################
#
#    OpenKool - Online church organization tool
#
#    Copyright © 2003-2015 Renzo Lauper (renzo@churchtool.org)
#    Copyright © 2019      Daniel Lerch
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
###############################################################################>

function EnsureExists ($Path) {
    if (!(Test-Path -Path $Path)) {
        New-Item -Path $Path -ItemType Directory -Force | Out-Null
    }
}

function Main {
    Write-Host "OpenKool setup script"
    Write-Host ""

    Write-Host "Preparing ~/config..."
    EnsureExists -Path "..\config"
    Copy-Item -Path ".\default\config\address.rtf",".\default\config\footer.php",".\default\config\header.php",`
        ".\default\config\ko-config.php",".\default\config\leute_formular.inc.php" -Destination "..\config" -Force
    # TODO: Handle permissions

    Write-Host "Preparing ~/download..."
    EnsureExists -Path "../download"
    EnsureExists -Path "../download/dp"
    EnsureExists -Path "../download/excel"
    EnsureExists -Path "../download/pdf"
    EnsureExists -Path "../download/word"
    Copy-Item -Path ".\default\download\index1.php" -Destination "..\download\index.php" -Force
    Copy-Item -Path ".\default\download\index2.php" -Destination "..\download\dp\index.php" -Force
    Copy-Item -Path ".\default\download\index2.php" -Destination "..\download\excel\index.php" -Force
    Copy-Item -Path ".\default\download\index2.php" -Destination "..\download\pdf\index.php" -Force
    Copy-Item -Path ".\default\download\index2.php" -Destination "..\download\word\index.php" -Force
    # TODO: Handle permissions

    Write-Host "Preparing ~/latex..."
    EnsureExists -Path "..\latex\compile"
    EnsureExists -Path "..\latex\images"
    Copy-Item -Path ".\default\latex\images\.htaccess" -Destination "..\latex\images" -Force
    Copy-Item -Path ".\default\latex\layouts\letter_default.lco" -Destination "..\latex\layouts" -Force
    # TODO: Handle permissions

    Write-Host "Preparing ~/my_images..."
    EnsureExists -Path "..\my_images"
    EnsureExists -Path "..\my_images\cache"
    # TODO: Handle permissions
    
    Write-Host "Preparing ~/templates_c..."
    EnsureExists -Path "..\templates_c"
    # TODO: Handle permissions

    Write-Host "Preparing ~/webfolders and ~/.webfolders..."
    EnsureExists -Path "..\webfolders"
    EnsureExists -Path "..\.webfolders"
    # TODO: Handle permissions
}

$oldLocation = Get-Location
Set-Location $PSScriptRoot
$ErrorActionPreference = "Stop"
Main
Set-Location $oldLocation
