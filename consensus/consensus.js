/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
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
        var res = jQuery.parseJSON(data);
        if (res.status == 1) {
            handleResponse(res);
        }
        else {
            alert(res.message);
        }
    })
        .done(function() {
            consensus_filter();
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
        if (ids[i].substring(0, 5) === "attr:") {
            var event_id = ids[i].substring(5);
            $("#" + event_id).data("filter-status", contents[i]);
        } else {
            $(ids[i]).html(contents[i]);
        }
    }
}//do_element_content()


$(document).ready(function(){
    $("button[data-action='comment_dialog']").on("click", function() {
        var team_id = $(this).attr("data-team_id");
        var comment = $(this).attr('data-comment_text');
        var modal = $("#comment_modal");
        $(modal).find('#comment_team_id').val(team_id);
        $(modal).find('#comment_text').val(comment);
        $(modal).modal("show");
    });

    $("button[data-action='comment_save']").on("click", function() {
        var modal = $("#comment_modal");
        var comment = $(modal).find("#comment_text").val();
        var team_id = $(modal).find('#comment_team_id').val();

        var urlparams = new RegExp('[\?&]x=([^&#]*)').exec(window.location.href);

        var params = {
            "action": "savecomment",
            "comment": comment,
            "team_id": team_id,
            "x": urlparams[1]
        };

        $.ajax({
            method: "POST",
            url: "/consensus/ajax.php",
            data: params
        })
        .done(function() {
            var button = $("button[data-action='comment_dialog'][data-team_id="+team_id+"]");
            button.attr("data-comment_text", comment);

            if(comment !== '') {
                button.find("i.fa-comment-o").addClass('fa-comment').removeClass('fa-comment-o');
            } else {
                button.find("i.fa-comment").addClass('fa-comment-o').removeClass('fa-comment');
            }
            $(modal).modal("hide");
        });
    });

    $(document).tooltip({
        selector:'.rota-tooltip',
        html:true,
        container:'body',
        title:function() {
            return $(this).data('tooltip-code');
        },
    });
    $(document).on('show.bs.tooltip','.rota-tooltip',function() {
        $(this).on('remove',function() {
            $(this).tooltip('destroy');
        });
    });
});
