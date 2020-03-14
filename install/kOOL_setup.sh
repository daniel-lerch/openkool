#!/bin/bash

##################################################################################
#  Copyright notice
#
#  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
#  All rights reserved
#
#  This script is part of the kOOL project. The kOOL project is
#  free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  The GNU General Public License can be found at
#  http://www.gnu.org/copyleft/gpl.html.
#  A copy is found in the textfile GPL.txt and important notices to the license
#  from the author is found in LICENSE.txt distributed with these scripts.
#
#  kOOL is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  This copyright notice MUST APPEAR in all copies of the script!
##################################################################################


# #############################
# KOOL MULTIHOST SETUP SCRIPT #
# #############################


# Settings
# --------
# adapt them to meet your server settings
#

# The path to the kOOL source directory to be used
kOOL_LIB_PATH="/var/lib/kOOL/lib"

# The group the apache webserver runs as
WWW_GROUP="www-data"




# -------------------------------------
# End of configurations section
# Don't change anything below this line
# -------------------------------------


# Check for bash as calling shell
#if [ ! $SHELL =~ "bash" ] ; then
#	echo >&2
#	echo "ERROR: It looks like another shell than bash is running this script: '$SHELL'" >&2
#	echo "       Call it again using bash." >&2
#	echo >&2
#	exit 1;
#fi;


# Check for correct directory by locating kOOL_setup.sh in current directory
if [ ! -e ./kOOL_setup.sh ]; then
	echo >&2
	echo "ERROR: Can not find kOOL_setup.sh in current directory. Can not continue." >&2
	echo "       Make sure you run this script from inside your kOOL web directory (e.g. /var/www/kOOL_demo)" >&2
	echo >&2
	exit 1;
fi;


#
# Start installation
#
echo " *** kOOL Setup Script *** "
echo


# symlink kOOL_lib
if [ ! -L ./kOOL_lib ]; then rm -f kOOL_lib && ln -s $kOOL_LIB_PATH ./kOOL_lib ; fi


# General files
echo -n "general files, ";
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ./kOOL_lib/index.php ./index.php ; fi
if [ ! -L ./menu.php ]; then rm -f menu.php && ln -s ./kOOL_lib/menu.php ./menu.php ; fi
if [ ! -L ./download.php ]; then rm -f download.php && ln -s ./kOOL_lib/download.php ./download.php ; fi
if [ ! -L ./kOOL.css ]; then rm -f kOOL.css && ln -s ./kOOL_lib/kOOL.css ./kOOL.css ; fi
if [ ! -L ./kool-base.css ]; then rm -f kool-base.css && ln -s ./kOOL_lib/kool-base.css ./kool-base.css ; fi
if [ ! -L ./kool-mobile.css ]; then rm -f kool-mobile.css && ln -s ./kOOL_lib/kool-mobile.css ./kool-mobile.css ; fi
if [ ! -L ./print.css ]; then rm -f print.css && ln -s ./kOOL_lib/print.css ./print.css ; fi
if [ ! -L ./ie6.css ]; then rm -f ie6.css && ln -s ./kOOL_lib/ie6.css ./ie6.css ; fi
if [ ! -L ./ie7.css ]; then rm -f ie7.css && ln -s ./kOOL_lib/ie7.css ./ie7.css ; fi
if [ ! -L ./get.php ]; then rm -f get.php && ln -s ./kOOL_lib/get.php ./get.php ; fi
if [ ! -L ./post.php ]; then rm -f post.php && ln -s ./kOOL_lib/post.php ./post.php ; fi
if [ ! -L ./mailing.php ]; then rm -f mailing.php && ln -s ./kOOL_lib/mailing.php ./mailing.php ; fi
if [ ! -L ./scheduler.php ]; then rm -f scheduler.php && ln -s ./kOOL_lib/scheduler.php ./scheduler.php ; fi


# Images
echo -n "images, ";
if [ ! -L ./images ]; then rm -rf images && ln -s ./kOOL_lib/images ./images ; fi
if [ ! -d ./my_images ]; then mkdir my_images ; fi
chmod g+w my_images
cd my_images
# cache folder
if [ ! -d ./cache ]; then mkdir cache ; fi
# v11 folder (used for vesr)
if [ ! -d ./v11 ]; then mkdir v11 ; fi
chmod g+w cache
# Don't override any files in my_images
for datei in `ls ../kOOL_lib/install/default/my_images/*.gif` ; do
	if [ ! -e `basename $datei` ]; then
		cp $datei . ;
	fi
done
touch index.html



# Include directory
echo -n "inc, "
cd ..
if [ ! -d ./inc ]; then mkdir inc ; fi
cd inc
files='ajax.php ColorPicker2.js error_handling.inc front_modules.inc kOOL.js tooltip.js selectmenu.js ko.inc kota.inc kotafcn.php session.inc js-sessiontimeout.inc smarty.inc submenu_actions.inc submenu.inc Clickatell.php abuse.inc hooks.inc lang.inc class.kOOL_listview.php class.mcrypt.php graph_bar.php class.html2text.php graph_piechart.php fullcalendar.css js-fullcalendar.min.js class.excelwriter.php aspsms.php cron.php qrcode.php class.iCalReader.php ZeroClipboard.swf ZeroClipboard.min.js ZeroClipboard.min.map class.dbStructUpdater.php class.koNotifier.php class.rawSmtpMailer.php'
for datei in $files ; do
	if [ ! -L ./$datei ]; then rm -f $datei && ln -s ../kOOL_lib/inc/$datei ./$datei ; fi
done
if [ ! -L ./fullcalendar ]; then rm -rf fullcalendar && ln -s ../kOOL_lib/inc/fullcalendar ./fullcalendar ; fi
if [ ! -L ./calendar ]; then rm -rf calendar && ln -s ../kOOL_lib/inc/calendar ./calendar ; fi
if [ ! -L ./jquery ]; then rm -rf jquery && ln -s ../kOOL_lib/inc/jquery ./jquery ; fi
if [ ! -L ./phpexcel ]; then rm -rf phpexcel && ln -s ../kOOL_lib/inc/phpexcel ./phpexcel ; fi
if [ ! -L ./phpword ]; then rm -rf phpword && ln -s ../kOOL_lib/inc/phpword ./phpword ; fi
if [ ! -L ./bootstrap ]; then rm -rf bootstrap && ln -s ../kOOL_lib/inc/bootstrap ./bootstrap ; fi
if [ ! -L ./qrcode ]; then rm -rf qrcode && ln -s ../kOOL_lib/inc/qrcode ./qrcode ; fi
if [ ! -L ./swiftmailer ]; then rm -rf swiftmailer && ln -s ../kOOL_lib/inc/swiftmailer ./swiftmailer ; fi
if [ ! -L ./SabreDAV ]; then rm -rf SabreDAV && ln -s ../kOOL_lib/inc/SabreDAV ./SabreDAV ; fi
if [ ! -L ./ckeditor ]; then rm -rf ckeditor && ln -s ../kOOL_lib/inc/ckeditor ./ckeditor ; fi
if [ ! -L ./moment ]; then rm -rf moment && ln -s ../kOOL_lib/inc/moment ./moment ; fi
if [ ! -L ./chartist ]; then rm -rf chartist && ln -s ../kOOL_lib/inc/chartist ./chartist ; fi
# Delete old links
if [ -L ./js-kOOL.inc ]; then rm -f js-kOOL.inc ; fi
if [ -L ./js-ajax.inc ]; then rm -f js-ajax.inc ; fi
if [ -L ./js-popup.inc ]; then rm -f js-popup.inc ; fi
if [ -L ./js-selectmenu.inc ]; then rm -f js-selectmenu.inc ; fi
if [ -L ./popup.js ]; then rm -f popup.js ; fi
if [ -L ./ajax.js ]; then rm -f ajax.js ; fi
if [ -L ./auth.inc ]; then rm -f auth.inc ; fi
if [ -L ./mime_mail.inc ]; then rm -f mime_mail.inc ; fi


# The files for FreePDF
echo -n "FreePDF, "
cd ..
if [ ! -d ./fpdf ]; then mkdir fpdf ; fi
cd fpdf
if [ ! -L ./fpdf.php ]; then rm -f fpdf.php && ln -s ../kOOL_lib/fpdf/fpdf.php ./fpdf.php ; fi
if [ ! -L ./pdf_leute.php ]; then rm -f pdf_leute.php && ln -s ../kOOL_lib/fpdf/pdf_leute.php ./pdf_leute.php ; fi
if [ ! -L ./pdf_tracking.php ]; then rm -f pdf_tracking.php && ln -s ../kOOL_lib/fpdf/pdf_tracking.php ./pdf_tracking.php ; fi
if [ ! -L ./mc_table.php ]; then rm -f mc_table.php && ln -s ../kOOL_lib/fpdf/mc_table.php ./mc_table.php ; fi
if [ ! -L ./PDF_HTML.php ]; then rm -f PDF_HTML.php && ln -s ../kOOL_lib/fpdf/PDF_HTML.php ./PDF_HTML.php ; fi
if [ ! -d ./schriften ]; then mkdir schriften ; fi
cd schriften
for datei in `ls ../../kOOL_lib/install/default/fpdf/schriften/*` ; do
	if [ ! -e `basename $datei` ]; then
		cp $datei . ;
	fi
done
cd ..
if [ ! -d ./images ]; then mkdir images ; fi
if [ ! -e ./images/logo.png ]; then cp ../kOOL_lib/install/default/fpdf/images/logo.png ./images ; fi
if [ ! -e ./images/pdf-point.png ]; then cp ../kOOL_lib/install/default/fpdf/images/pdf-point.png ./images ; fi


# Default files
echo -n "defaults, "
cd ..
if [ ! -e ./footer.php ]; then cp ./kOOL_lib/install/default/footer.php . ; fi
if [ ! -e ./header.php ]; then cp ./kOOL_lib/install/default/header.php . ; fi
if [ ! -e ./ko.css ]; then cp ./kOOL_lib/install/default/ko.css . ; fi
if [ ! -d ./config ]; then mkdir config ; fi
cd config
if [ ! -e ./ko-config.php ]; then cp ../kOOL_lib/install/default/config/ko-config.php . ; fi
if [ ! -e ./leute_formular.inc ]; then cp ../kOOL_lib/install/default/config/leute_formular.inc . ; fi
if [ ! -e ./address.rtf ]; then cp ../kOOL_lib/install/default/config/address.rtf . ; fi
chmod o-rx ./ko-config.php
chmod g+w ./ko-config.php
chmod g+w ./leute_formular.inc
cd ..


# Installation
echo -n "install, "
if [ ! -d ./install ]; then mkdir install ; fi
chmod g+w install
cd install
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/install/index.php ./index.php ; fi
if [ ! -L ./update.phpsh ]; then rm -f update.phpsh && ln -s ../kOOL_lib/install/update.phpsh ./update.phpsh ; fi
if [ ! -L ./kOOL_db.sql ]; then rm -f kOOL_db.sql && ln -s ../kOOL_lib/install/kOOL_db.sql ./kOOL_db.sql ; fi
if [ ! -L ./db_de.sql ]; then rm -f db_de.sql && ln -s ../kOOL_lib/install/db_de.sql ./db_de.sql ; fi
cd ..


# Module: admin
echo -n "admin, "
if [ ! -d ./admin ]; then mkdir admin ; fi
cd admin
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/admin/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/admin.inc ]; then rm -f inc/admin.inc && ln -s ../../kOOL_lib/admin/inc/admin.inc ./inc/admin.inc ; fi
if [ ! -L ./inc/js-admin.inc ]; then rm -f inc/js-admin.inc && ln -s ../../kOOL_lib/admin/inc/js-admin.inc ./inc/js-admin.inc ; fi
if [ ! -L ./inc/ckeditor_custom_config.js ]; then rm -f inc/ckeditor_custom_config.js && ln -s ../../kOOL_lib/admin/inc/ckeditor_custom_config.js ./inc/ckeditor_custom_config.js ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/admin/inc/ajax.php ./inc/ajax.php ; fi
if [ -L ./inc/js-admin.inc ]; then rm -f inc/js-admin.inc ; fi


# Module: events
echo -n "events, "
cd ..
if [ ! -d ./daten ]; then mkdir daten ; fi
cd daten
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/daten/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/daten.inc ]; then rm -f inc/daten.inc && ln -s ../../kOOL_lib/daten/inc/daten.inc ./inc/daten.inc ; fi
if [ ! -L ./inc/js-daten.inc ]; then rm -f inc/js-daten.inc && ln -s ../../kOOL_lib/daten/inc/js-daten.inc ./inc/js-daten.inc ; fi
if [ ! -L ./inc/ckeditor_custom_config.js ]; then rm -f inc/ckeditor_custom_config.js && ln -s ../../kOOL_lib/daten/inc/ckeditor_custom_config.js ./inc/ckeditor_custom_config.js ; fi
if [ ! -L ./inc/js-seleventgroup.inc ]; then rm -f inc/js-seleventgroup.inc && ln -s ../../kOOL_lib/daten/inc/js-seleventgroup.inc ./inc/js-seleventgroup.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/daten/inc/ajax.php ./inc/ajax.php ; fi


# Module: addresses
echo -n "addresses, "
cd ..
if [ ! -d ./leute ]; then mkdir leute ; fi
cd leute
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/leute/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/js-leute.inc ]; then rm -f inc/js-leute.inc && ln -s ../../kOOL_lib/leute/inc/js-leute.inc ./inc/js-leute.inc ; fi
if [ ! -L ./inc/js-groupmenu.inc ]; then rm -f inc/js-groupmenu.inc && ln -s ../../kOOL_lib/leute/inc/js-groupmenu.inc ./inc/js-groupmenu.inc ; fi
if [ ! -L ./inc/leute.inc ]; then rm -f inc/leute.inc && ln -s ../../kOOL_lib/leute/inc/leute.inc ./inc/leute.inc ; fi
if [ ! -L ./inc/kg.inc ]; then rm -f inc/kg.inc && ln -s ../../kOOL_lib/leute/inc/kg.inc ./inc/kg.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/leute/inc/ajax.php ./inc/ajax.php ; fi
if [ ! -L ./inc/vcard.php ]; then rm -f inc/vcard.php && ln -s ../../kOOL_lib/leute/inc/vcard.php ./inc/vcard.php ; fi


# Module: reservations
echo -n "reservation, "
cd ..
if [ ! -d ./reservation ]; then mkdir reservation ; fi
cd reservation
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/reservation/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/reservation.inc ]; then rm -f inc/reservation.inc && ln -s ../../kOOL_lib/reservation/inc/reservation.inc ./inc/reservation.inc ; fi
if [ ! -L ./inc/js-reservation.inc ]; then rm -f inc/js-reservation.inc && ln -s ../../kOOL_lib/reservation/inc/js-reservation.inc ./inc/js-reservation.inc ; fi
if [ ! -L ./inc/ckeditor_custom_config.js ]; then rm -f inc/ckeditor_custom_config.js && ln -s ../../kOOL_lib/reservation/inc/ckeditor_custom_config.js ./inc/ckeditor_custom_config.js ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/reservation/inc/ajax.php ./inc/ajax.php ; fi


# Module: rota
echo -n "rota, "
cd ..
if [ ! -d ./rota ]; then mkdir rota ; fi
cd rota
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/rota/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/rota.inc ]; then rm -f inc/rota.inc && ln -s ../../kOOL_lib/rota/inc/rota.inc ./inc/rota.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/rota/inc/ajax.php ./inc/ajax.php ; fi
if [ ! -L ./inc/ckeditor_custom_config.js ]; then rm -f inc/ckeditor_custom_config.js && ln -s ../../kOOL_lib/rota/inc/ckeditor_custom_config.js ./inc/ckeditor_custom_config.js ; fi
if [ ! -L ./inc/js-rota.inc ]; then rm -f inc/js-rota.inc && ln -s ../../kOOL_lib/rota/inc/js-rota.inc ./inc/js-rota.inc ; fi


# consensus
echo -n "consensus, "
cd ..
if [ ! -d ./consensus ]; then mkdir consensus ; fi
cd consensus
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/consensus/index.php ./index.php ; fi
if [ ! -L ./ajax.php ]; then rm -f ajax.php && ln -s ../kOOL_lib/consensus/ajax.php ./ajax.php ; fi
if [ ! -L ./consensus.css ]; then rm -f consensus.css && ln -s ../kOOL_lib/consensus/consensus.css ./consensus.css ; fi
if [ ! -L ./consensus.inc ]; then rm -f consensus.inc && ln -s ../kOOL_lib/consensus/consensus.inc ./consensus.inc ; fi
if [ ! -L ./consensus.js ]; then rm -f consensus.js && ln -s ../kOOL_lib/consensus/consensus.js ./consensus.js ; fi
if [ ! -L ./js-consensus.inc ]; then rm -f js-consensus.inc && ln -s ../kOOL_lib/consensus/js-consensus.inc ./js-consensus.inc ; fi


# Module: fileshare
echo -n "fileshare, "
cd ..
if [ ! -d ./fileshare ]; then mkdir fileshare ; fi
cd fileshare
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/fileshare/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/fileshare.inc ]; then rm -f inc/fileshare.inc && ln -s ../../kOOL_lib/fileshare/inc/fileshare.inc ./inc/fileshare.inc ; fi
if [ ! -L ./inc/js-fileshare.inc ]; then rm -f inc/js-fileshare.inc && ln -s ../../kOOL_lib/fileshare/inc/js-fileshare.inc ./inc/js-fileshare.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/fileshare/inc/ajax.php ./inc/ajax.php ; fi


# Module: tools
echo -n "tools, "
cd ..
if [ ! -d ./tools ]; then mkdir tools ; fi
cd tools
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/tools/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/tools.inc ]; then rm -f inc/tools.inc && ln -s ../../kOOL_lib/tools/inc/tools.inc ./inc/tools.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/tools/inc/ajax.php ./inc/ajax.php ; fi


# Module: tapes
echo -n "tapes, "
cd ..
if [ ! -d ./tapes ]; then mkdir tapes ; fi
cd tapes
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/tapes/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/tapes.inc ]; then rm -f inc/tapes.inc && ln -s ../../kOOL_lib/tapes/inc/tapes.inc ./inc/tapes.inc ; fi
if [ ! -L ./inc/js-tapes.inc ]; then rm -f inc/js-tapes.inc && ln -s ../../kOOL_lib/tapes/inc/js-tapes.inc ./inc/js-tapes.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/tapes/inc/ajax.php ./inc/ajax.php ; fi



# Module: groups
echo -n "groups, "
cd ..
if [ ! -d ./groups ]; then mkdir groups ; fi
cd groups
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/groups/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/groups.inc ]; then rm -f inc/groups.inc && ln -s ../../kOOL_lib/groups/inc/groups.inc ./inc/groups.inc ; fi
if [ ! -L ./inc/js-groups.inc ]; then rm -f inc/js-groups.inc && ln -s ../../kOOL_lib/groups/inc/js-groups.inc ./inc/js-groups.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/groups/inc/ajax.php ./inc/ajax.php ; fi



# Module: donations
echo -n "donations, "
cd ..
if [ ! -d ./donations ]; then mkdir donations ; fi
cd donations
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/donations/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/donations.inc ]; then rm -f inc/donations.inc && ln -s ../../kOOL_lib/donations/inc/donations.inc ./inc/donations.inc ; fi
if [ ! -L ./inc/js-donations.inc ]; then rm -f inc/js-donations.inc && ln -s ../../kOOL_lib/donations/inc/js-donations.inc ./inc/js-donations.inc ; fi
if [ ! -L ./inc/js-donationsmod.inc ]; then rm -f inc/js-donationsmod.inc && ln -s ../../kOOL_lib/donations/inc/js-donationsmod.inc ./inc/js-donationsmod.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/donations/inc/ajax.php ./inc/ajax.php ; fi
if [ ! -L ./inc/ckeditor_custom_config.js ]; then rm -f inc/ckeditor_custom_config.js && ln -s ../../kOOL_lib/donations/inc/ckeditor_custom_config.js ./inc/ckeditor_custom_config.js ; fi



# Module: tracking
echo -n "tracking, "
cd ..
if [ ! -d ./tracking ]; then mkdir tracking ; fi
cd tracking
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/tracking/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/tracking.inc ]; then rm -f inc/tracking.inc && ln -s ../../kOOL_lib/tracking/inc/tracking.inc ./inc/tracking.inc ; fi
if [ ! -L ./inc/js-tracking.inc ]; then rm -f inc/js-tracking.inc && ln -s ../../kOOL_lib/tracking/inc/js-tracking.inc ./inc/js-tracking.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/tracking/inc/ajax.php ./inc/ajax.php ; fi


# Module: crm
echo -n "crm, "
cd ..
if [ ! -d ./crm ]; then mkdir crm ; fi
cd crm
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/crm/index.php ./index.php ; fi
if [ ! -d ./inc ]; then mkdir inc ; fi
if [ ! -L ./inc/crm.inc ]; then rm -f inc/crm.inc && ln -s ../../kOOL_lib/crm/inc/crm.inc ./inc/crm.inc ; fi
if [ ! -L ./inc/js-crm.inc ]; then rm -f inc/js-crm.inc && ln -s ../../kOOL_lib/crm/inc/js-crm.inc ./inc/js-crm.inc ; fi
if [ ! -L ./inc/js-selproject.inc ]; then rm -f inc/js-selproject.inc && ln -s ../../kOOL_lib/crm/inc/js-selproject.inc ./inc/js-selproject.inc ; fi
if [ ! -L ./inc/ajax.php ]; then rm -f inc/ajax.php && ln -s ../../kOOL_lib/crm/inc/ajax.php ./inc/ajax.php ; fi



# Download folder
echo -n "download, "
cd ..
if [ ! -d ./download ]; then mkdir download ; fi
if [ ! -e ./download/index.php ]; then cp ./kOOL_lib/install/default/download/index1.php ./download/index.php ; fi
if [ ! -d ./download/excel ]; then mkdir download/excel ; fi
if [ ! -e ./download/excel/index.php ]; then cp ./kOOL_lib/install/default/download/index2.php ./download/excel/index.php ; fi
if [ ! -d ./download/pdf ]; then mkdir download/pdf ; fi
if [ ! -e ./download/pdf/index.php ]; then cp ./kOOL_lib/install/default/download/index2.php ./download/pdf/index.php ; fi
if [ ! -d ./download/dp ]; then mkdir download/dp ; fi
if [ ! -e ./download/dp/index.php ]; then cp ./kOOL_lib/install/default/download/index2.php ./download/dp/index.php ; fi
if [ ! -d ./download/word ]; then mkdir download/word ; fi
if [ ! -e ./download/word/index.php ]; then cp ./kOOL_lib/install/default/download/index2.php ./download/word/index.php ; fi
# chgrp $WWW_GROUP download download/excel download/pdf download/dp download/word
chmod g+w download download/excel download/pdf download/dp download/word


# iCal
echo -n "iCal, "
if [ ! -d ./ical ]; then mkdir ical ; fi
cd ical
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/ical/index.php ./index.php ; fi
cd ..
if [ ! -d ./resical ]; then mkdir resical ; fi
cd resical
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/resical/index.php ./index.php ; fi
cd ..
if [ ! -d ./rotaical ]; then mkdir rotaical ; fi
cd rotaical
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/rotaical/index.php ./index.php ; fi
cd ..


# Webfolders
echo -n "Webfolders, "
if [ ! -d ./webfolders ]; then mkdir webfolders ; fi
if [ ! -d ./.webfolders ]; then mkdir .webfolders ; fi
# chgrp $WWW_GROUP webfolders .webfolders
chmod g+w webfolders .webfolders


# Plugins
echo -n "Plugins, "
if [ ! -d ./plugins ]; then mkdir plugins ; fi
cd plugins
for plugin in `ls -d ../kOOL_lib/install/default/plugins/*` ; do
	if [ ! -d `basename $plugin` ]; then
		cp -r $plugin . ;
	fi
done
if [ ! -e ./.htaccess ]; then cp ../kOOL_lib/install/default/plugins/.htaccess ./.htaccess ; fi
cd ..


# Languages
echo -n "LocalLang, "
if [ ! -L ./locallang ]; then rm -rf locallang && ln -s ./kOOL_lib/locallang ./locallang ; fi


# Templates-Directories
echo -n "Templates, "
if [ ! -L ./templates ]; then rm -rf templates && ln -s ./kOOL_lib/templates ./templates ; fi
if [ ! -d ./templates_c ]; then mkdir templates_c ; fi
chmod g+w templates_c



# DAV
echo -n "DAV, "
if [ ! -d ./.well-known ]; then mkdir .well-known ; fi
cd .well-known
if [ ! -d ./carddav ]; then mkdir carddav ; fi
cd carddav
if [ ! -L ./index.php ]; then rm -rf index.php && ln -s ../../kOOL_lib/.well-known/carddav/index.php ./index.php ; fi
cd ../..

if [ ! -d ./dav ]; then mkdir dav ; fi
cd dav
if [ ! -L ./index.php ]; then rm -f index.php && ln -s ../kOOL_lib/dav/index.php ./index.php ; fi
if [ ! -L ./AuthBackend_kOOL.php ]; then rm -f AuthBackend_kOOL.php && ln -s ../kOOL_lib/dav/AuthBackend_kOOL.php ./AuthBackend_kOOL.php ; fi
if [ ! -L ./CardDAVBackend_kOOL.php ]; then rm -f CardDAVBackend_kOOL.php && ln -s ../kOOL_lib/dav/CardDAVBackend_kOOL.php ./CardDAVBackend_kOOL.php ; fi
if [ ! -L ./PrincipalBackend_kOOL.php ]; then rm -f PrincipalBackend_kOOL.php && ln -s ../kOOL_lib/dav/PrincipalBackend_kOOL.php ./PrincipalBackend_kOOL.php ; fi
if [ ! -L ./.htaccess ]; then rm -f .htaccess && ln -s ../kOOL_lib/dav/.htaccess ./.htaccess ; fi
cd ..



# Module: news
echo -n ", DELETING news"
if [ -d ./news ]; then rm -rf news ; fi

# Module: dp
echo -n ", DELETING dp"
if [ -d ./dp ]; then rm -rf dp ; fi




# Change group of the whole directory to apache group
# chgrp -R $WWW_GROUP .


echo
echo
echo "You can start the web based installation: http://your.kool.server/install"
echo
echo "kOOL setup has finished. Enjoy!"
echo
