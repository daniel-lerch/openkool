var onMobile = false;
$(document).ready(function () {
	// Copyright 2014-2015 Twitter, Inc.
	// Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
	if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
		var msViewportStyle = document.createElement('style');
		msViewportStyle.appendChild(
			document.createTextNode(
				'@-ms-viewport{width:auto!important}'
			)
		);
		document.querySelector('head').appendChild(msViewportStyle)
	}

	$('#login-menu').click(function(event) {
		event.stopPropagation();
	});

	// detect mobile browsers
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
		onMobile = true;
	}

	// get the initial platform
	window.devices = {
		'xs': new Device(0, 767, 'xs'),
		'sm': new Device(768, 991, 'sm'),
		'md': new Device(992, 1199, 'md'),
		'lg': new Device(1200, -1, 'lg')
	};
	window.device = getDevice();

	if (window.device.lt(window.devices.md)) {
		$('#sidebar-container').removeClass('shown').removeClass('in');
		$('#sidebar-toggle-sub').removeClass('active');
	}

	// adjust dropdowns in a manner of adding class dropdown-right to dropdowns which would else be cut off
	dropDowns = $('#navbar-main li.dropdown');
	var windowWidth = $(window).outerWidth();
	for (i = 0; i < dropDowns.length; i++) {
		var dropDown = $(dropDowns[i]);
		var left = dropDown.position().left;
		var dropDownWidth = $(dropDown.children('ul.dropdown-menu')).outerWidth();
		$(dropDown.children('ul')).removeClass('dropdown-menu-right');
		if (left + dropDownWidth > windowWidth) {
			$(dropDown.children('ul')).addClass('dropdown-menu-right');
		}
	}

	adjust_main_content_height();
	window.onresize = function(event) {
		var oldDevice = window.device;
		// adjust platform variable in the event of resizing
		window.device = getDevice();

		var changedToXS = oldDevice.gt(window.devices.xs) && window.device.le(window.devices.xs);
		var changedFromXS = oldDevice.le(window.devices.xs) && window.device.gt(window.devices.xs);

		adjust_main_content_height();

		// adjust dropdowns in a manner of adding class dropdown-right to dropdowns which would else be cut off
		dropDowns = $('#navbar-main li.dropdown');
		var windowWidth = $(window).outerWidth();
		for (i = 0; i < dropDowns.length; i++) {
			var dropDown = $(dropDowns[i]);
			var left = dropDown.position().left;
			var dropDownWidth = $(dropDown.children('ul.dropdown-menu')).outerWidth();
			$(dropDown.children('ul')).removeClass('dropdown-menu-right');
			if (left + dropDownWidth > windowWidth) {
				$(dropDown.children('ul')).addClass('dropdown-menu-right');
			}
		}

	};

	// initialite tooltips
	$('body').tooltip({
		selector: '[data-toggle="tooltip"]'
	});


	// initialize switch plugin
	$('body').on('switchChange.bootstrapSwitch', '.switch', function(event, state) {
		$(this).attr('checked', state);
	});

	// set sidebar affix
	/*if (navigator.userAgent.indexOf('Firefox') < 0) {
		$('#sidebar').affix({
			offset: {
				top: $('#top-header').outerHeight() + $('#navbar-main').outerHeight() + $('#navbar-sec').outerHeight(),
				bottom: 0
			}
		});
	}*/
	$('#navbar-main').affix({
		offset: {
			top: $('#top-header').outerHeight(),
			bottom: 0
		}
	});
	$('body').on('affix.bs.affix', '#navbar-main', function() {
		$('#navbar-sec').addClass('affix');
		$('#navbar-sec').css({
			top: $(this).outerHeight()
		});
		$('#navbar-placeholder').height($(this).outerHeight() + $('#navbar-sec').outerHeight());
	});
	$('body').on('affix-top.bs.affix', '#navbar-main', function() {
		$('#navbar-sec').removeClass('affix');
		$('#navbar-sec').css({
			top: 0
		});
		$('#navbar-placeholder').height(0);
	});

	// sidebar handling (show/hide)
	$('body').on('click', '.sidebar-toggle[data-toggle="offcanvas"]', function() {
		var button = $(this);
		var buttons = $('.sidebar-toggle[data-toggle="offcanvas"]');
		var target = $(button.attr('data-target'));
		var container = $(target.attr('data-container'));
		var toState;
		if (button.hasClass('active')) {
			container.removeClass('in');
			window.setTimeout(function () {
				container.removeClass('shown');
				buttons.removeClass('active');
				$('#main-table-layout').trigger('resize');
			}, 350);
			toState = 'closed';
		}
		else {
			buttons.addClass('active');
			container.addClass('shown');
			window.setTimeout(function () {
				container.addClass('in');
			}, 10);
			window.setTimeout(function () {
				$('#main-table-layout').trigger('resize');
			}, 350);
			toState = 'open';
		}
		if (window.device.ge(window.devices.md)) {
			$.get('../inc/ajax.php', {
				action: 'togglesidebar',
				tostate: toState,
				sesid: kOOL.sid
			});
		}
	});

	// prevent default action of heading links in sidebar
	$('#sidebar h4.panel-title > a:first-child').click(function(event) {
		event.preventDefault()
	});

	// Open drop down menu on mouseOver and close it on mouseOut. Prevent bootstrap-default action on item click.
	$('#navbar-main li.dropdown').mouseover(function (){
		if (window.device.ge(window.devices.md)) {
			$(this).addClass('open');
		}
	});
	$('#navbar-main li.dropdown').mouseout(function (){
		if (window.device.ge(window.devices.md)) {
			$(this).removeClass('open');
		}
	});
	$('#navbar-main li.dropdown').click(function (e){
		e.stopPropagation();
		return true;
	});
	/**
	 * functionality of top-menu links on mobile devices: on first click open dropdown menu, on second click goto default
	 * of that module
	 */
	$('body').on('tap', '#navbar-main li.dropdown', function (e){
		var dropDownOpened = $(this).hasClass('open');
		if (!dropDownOpened) {
			$('.dropdown').removeClass('open');
			$(this).addClass('open');
			return false;
		}
		else {
			$(this).click();
		}
	});

	// make items in sidebar sortable
	$('#sidebar div.sortable').sortable({
		axis: "y",
		handle: ".panel-heading",
		update: function(event, ui) { // save position after sorting
			var prev = ui.item.prev();
			var next = ui.item.next();
			var mod = ui.item.attr('data-sm-mod');
			var itemId = ui.item.attr('data-sm-id');
			var prevId = ''; var nextId = '';
			if (prev.length > 0) prevId = prev.attr('data-sm-id');
			if (next.length > 0) nextId = next.attr('data-sm-id');
			$.get("../inc/ajax.php", {
				action: 'movesm',
				mod: mod,
				id: itemId,
				nextId: nextId,
				prevId: prevId,
				sesid: kOOL.sid
			});
		}
	});
	// make items in secondary menu sortable
	secmenuSortableOption = {
		axis: "x",
		helper: "clone",
		update: function(event, ui) { // save position after sorting
			var removed = (ui.item.hasClass('removed') ? 1 : 0);
			var prev = ui.item.prev();
			var next = ui.item.next();
			var mod = kOOL.module;
			var itemId = ui.item.attr('data-action');
			var subMenuId = ui.item.attr('data-sm-id');
			var prevId = ''; var nextId = '';
			if (prev.length > 0) prevId = prev.attr('data-action');
			if (next.length > 0) nextId = next.attr('data-action');
			$.get("../inc/ajax.php", {
				action: 'updatesecmenu',
				mod: mod,
				id: itemId,
				nextId: nextId,
				prevId: prevId,
				removed: removed,
				smId: subMenuId,
				sesid: kOOL.sid
			}, function(data) {
				var result = data.split('@@@');
				if (result[0] != '') {
					element = document.getElementsByName(result[0])[0];
					$(element).html(result[1]);
					$('.sm-item-link li').draggable(sidebarDraggableOptions);
				}
			}).always(function() {
				// TODO: HIDE WAITING ICON
			});;
		},
		receive: function(event, ui) {
			$.each($('#navbar-sec ul.sortable').children(), function(key, value) {
				$(value).addClass('nowrap');
			});
		},
		start: function(event, ui) {
			if (ui.originalPosition.top == 0) {
				$('#shortlink-trash').show();
			}
		},
		stop: function() {
			$('#shortlink-trash').hide();
		}
	};
	$('#navbar-sec ul.sortable').sortable(secmenuSortableOption);
	// make link items in submenu draggable
	sidebarDraggableOptions = {
		connectToSortable: '#navbar-sec ul.sortable',
		start: function() {
			$(this).attr('data-sm-id', $(this).parents('div.panel').attr('data-sm-id'));
		},
		helper: 'clone',
		containment: "window",
		appendTo: 'body'
	};
	$('.sm-item-link li').draggable(sidebarDraggableOptions)

	shortlinkTrashDroppableOption = {
		hoverClass: 'active',
		tolerance: 'pointer',
		over: function(event, ui) {
			ui.helper.css('opacity', '0.3');
		},
		out: function(event, ui) {
			ui.helper.css('opacity', '1');
		},
		drop: function(event, ui) {
			ui.draggable.addClass('removed');
			ui.draggable.remove();
		}
	};
	$('#shortlink-trash').droppable(shortlinkTrashDroppableOption);
});



/**
 * @returns {Device} an Instance of the class Device corresponding to the current view port
 */
function getDevice() {
	var deviceIndex;
	var device;
	var min;
	var max;
	var viewPort = getViewport();
	for (deviceIndex in window.devices) {
		device = window.devices[deviceIndex];
		if (viewPort[0] >= device.min) {
			if (device.max == -1) {
				return device;
			}
			else if (viewPort[0] <= device.max) {
				return device;
			}
		}
	}
	return null;
}

/**
 * @returns {float[]} the width and height of the current viewport
 */
function getViewport() {
	var viewPortWidth = $(window).innerWidth();
	var viewPortHeight = $(window).innerHeight();
	return [viewPortWidth, viewPortHeight];
}

/**
 * @param min the minimum width of this device
 * @param max the maximum width of this device
 * @param name the name of the device (currently 'xs', 'sm', 'md', 'lg')
 * @constructor
 */
function Device(min,max,name) {
	this.min = min;
	this.max = max;
	this.name = name;
}
Device.prototype.lt = function(v2) {
	return this.max < v2.min && this.max > -1;
};
Device.prototype.eq = function(v2) {
	return this.min == v2.min && this.max == v2.max;
};
Device.prototype.neq = function(v2) {
	return !this.equals(v2);
};
Device.prototype.le = function(v2) {
	return this.eq(v2) || this.lt(v2);
};
Device.prototype.gt = function(v2) {
	return !this.le(v2);
};
Device.prototype.ge = function(v2) {
	return !this.lt(v2);
};

// This function creates a valid jquery selector (escaped) from a string
function jq( myid ) {
	return "#" + myid.replace( /(:|\.|\[|\]|,)/g, "\\$1" );
}

function adjust_main_content_height() {
	if (window.device.gt(window.devices.xs)) {
		$('#main-table-layout').css('height', $(window).height() - $('#top-header').outerHeight() - $('#navbar-main').outerHeight() - $('#navbar-sec').outerHeight() - $('#footer').outerHeight());
	}
	else {
		$('#main-table-layout').css('height', 'auto');
	}
}
