#!/usr/bin/env pwsh

<###############################################################################
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
###############################################################################>

[CmdletBinding()]
param (
    [Parameter(Mandatory = $false)]
    [switch]$Setup,
    [Parameter(Mandatory = $false)]
    [string]$Composer,
    [Parameter(Mandatory = $false)]
    [switch]$Start
)

function EnsureExists ($Path) {
    if (!(Test-Path -Path $Path)) {
        New-Item -Path $Path -ItemType Directory -Force | Out-Null
    }
}

function GetPhpLocation () {
    $vsConfigPath = "$env:APPDATA\Code\User\settings.json"
    if (Test-Path -Path $vsConfigPath -PathType Leaf) {
        $vsConfig = Get-Content -Path $vsConfigPath | ConvertFrom-Json
        if ($vsConfig.'php.executablePath') {
            return $vsConfig.'php.executablePath'
        }
        if ($vsConfig.'php.validate.executablePath') {
            return $vsConfig.'php.validate.executablePath'
        }
    }

    $programFile = "$env:ProgramFiles\PHP\php.exe"
    if (Test-Path -Path $programFile -PathType Leaf) {
        return $programFile
    }

    $programFileX86 = "${env:ProgramFiles(x86)}\PHP\php.exe"
    if (Test-Path -Path $programFileX86 -PathType Leaf) {
        return $programFileX86
    }
}

function SetupFiles {
    Write-Host "Preparing ~/config..."
    EnsureExists -Path "..\config"
    Copy-Item -Path ".\default\config\footer.php",".\default\config\header.php",`
        ".\default\config\ko-config.php" -Destination "..\config" -Force
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

    Write-Host "Preparing ~/my_images..."
    EnsureExists -Path "..\my_images"
    EnsureExists -Path "..\my_images\cache"
    # TODO: Handle permissions
    
    Write-Host "Preparing ~/templates_c..."
    EnsureExists -Path "..\templates_c"
    # TODO: Handle permissions
}

function SetupEnvironment {
    Write-Host "Preparing development environment..."
    Write-Host ""
    Set-Location "install"
    SetupFiles
    Set-Location ".."

    Write-Host "Preparing PHP runtime components..."
    $executablePath = GetPhpLocation
    if ($executablePath) {
        $extensionDir = Join-Path -Path (Split-Path -Path $executablePath) -ChildPath "ext"
        $xdebugDir = [IO.Path]::GetFileName((Resolve-Path -Path (Join-Path $extensionDir "php_xdebug*.dll")))
        $ini = (Get-Content -Path ".\install\default\php-windows.ini" -Raw) `
            -replace "extension_dir = `"`"", "extension_dir = `"$extensionDir`"" `
            -replace "`"php_xdebug.dll`"", "`"$xdebugDir`""
        New-Item -Path ".\php.ini" -Value $ini -Force | Out-Null
    } else {
        Write-Host "PHP could not be configured automatically. You have to adjust php.ini manually."
        Copy-Item -Path ".\install\default\php-windows.ini" -Destination ".\php.ini" -Force
    }

    if (Test-Path -Path ".\composer.phar") {
        Write-Host "Composer is already installed"
    } else {
        Write-Host "Downloading Composer Setup..."
        Invoke-WebRequest -Uri "https://getcomposer.org/installer" -UseBasicParsing -OutFile ".\composer-setup.php"
        if ($executablePath) {
            Write-Host "Installing Composer..."
            Start-Process -FilePath $executablePath -ArgumentList "-c",".\php.ini",".\composer-setup.php" -NoNewWindow -Wait
            Remove-Item -Path ".\composer-setup.php"
        } else {
            Write-Host "You have to adjust the php.ini and call composer-setup.php with PHP"
        }
    }
}

function InvokeComposer {
    Write-Host "Invoking composer $Composer..."
    Write-Host ""
    $executablePath = GetPhpLocation
    Start-Process -FilePath $executablePath -ArgumentList "-c",".\php.ini",".\composer.phar",$Composer -NoNewWindow -Wait
}

function StartServer {
    Write-Host "Starting development server..."
    Write-Host ""
    $executablePath = GetPhpLocation
    Start-Process -FilePath $executablePath -ArgumentList "-c",".\php.ini","-S","localhost:8080"
}

function Main {
    Write-Host "OpenKool Development Environment"
    Write-Host ""
    if ($Setup) {
        SetupEnvironment
    }
    if ($Composer) {
        InvokeComposer
    }
    if ($Start) {
        StartServer
    }
    if (!($Setup) -and !($Composer) -and !($Start)) {
        Write-Host "Usage: devenv.ps1 [-Setup] [-Composer <arguments>] [-Start]"
        Write-Host ""
    }
}

$oldLocation = Get-Location
Set-Location (Split-Path -Path $PSScriptRoot)
$ErrorActionPreference = "Stop"
Main
Set-Location $oldLocation
