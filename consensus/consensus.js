/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
 *  All rights reserved
 *
 *  This script is part of the kOOL project. The kOOL project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  kOOL is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


function sendReq(serverFileName, variableNames, variableValues, handleResponse) {

    variableNames = variableNames.split(',');
    variableValues = variableValues.split(',');

    var params = {};
    for(i=0; i<variableNames.length; i++) {
        params[variableNames[i]] = variableValues[i];
    }

    var jqxhr = $.post( serverFileName, params, function(data) {
        console.log(data);
        var res = jQuery.parseJSON(data);
        if (res.status == 1) {
            handleResponse(res);
        }
        else {
            alert(data.content);
        }
    })
        .done(function() {
        })
        .fail(function() {
        })
        .always(function() {
        });
}


/**
 * Zeichnet ein Element neu
 * ID ist die ID des HTML-Elementes (z.B. div), HTML ist der ganze Code
 */
function do_element_content(data) {
    var ids = data.ids;
    var contents = data.contents;

    var size = ids.length;

    for (i = 0; i < size; i++) {
        $(ids[i]).html(contents[i]);
    }
}//do_element_content()
