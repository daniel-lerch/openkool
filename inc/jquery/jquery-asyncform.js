(function (root, factory) {

	'use strict';

	// CommonJS module is defined
	if (typeof module !== 'undefined' && module.exports) {
		module.exports = factory(require('jquery'));
	}
	// AMD module is defined
	else if (typeof define === 'function' && define.amd) {
		define(['jquery'], function ($) {
			return factory ($);
		});
	} else {
		factory(root.jQuery);
	}

}(this, function ($) {

	'use strict';

	var Asyncform = function (element, options) {

		$.fn.asyncform.defaults = {
			minHeight: '400px',
			height: 'fitParent',
			tag: ''
		};

		this.$btn = $(element);
		this.options = $.extend({}, $.fn.asyncform.defaults, {
			table: this.$btn.data("af-table"),
			mode: this.$btn.data("af-mode"),
			entryId: this.$btn.data("af-entry-id")+"",
			target: this.$btn.data("target"),
			minHeight: this.$btn.data("min-height"),
			height: this.$btn.data("height"),
			tag: this.$btn.data("tag")
		}, options);
		this.table = this.options.table || this.table;
		this.mode = this.options.mode || this.mode;
		this.entryId = this.options.entryId || this.entryId;
		this.target = this.options.target || this.target;
		this.minHeight = this.options.minHeight || $.fn.asyncform.defaults.minHeight
		this.height = this.options.height || $.fn.asyncform.defaults.height;
		this.tag = this.options.tag || $.fn.asyncform.defaults.tag;

		this.modalId = this.target.substring(1);
		this.frameId = this.modalId + "-frame";

		this.source = "/inc/form.php?table="+this.table+"&mode="+this.mode+"&id="+this.entryId+"&target="+this.modalId+"&tag="+this.tag+"";

		this.initLayout();

		this.listen();
	};

	Asyncform.prototype = {

		constructor: Asyncform,

		initLayout: function () {
			var heightStyle = '';
			if (this.height != 'fitParent' && this.height != 'wrapContent') heightStyle = 'height:' + this.height + ';';
			this.$btn.after(
				'<div class="modal fade" id="'+this.modalId+'" tabindex="-1">\
					<div class="modal-dialog modal-async-form" role="document">\
						<div class="modal-content">\
							<div class="modal-header">\
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
								<h4 class="modal-title"></h4>\
							</div>\
							<div class="modal-body">\
								<iframe class="kota-async-form-frame" name="'+this.frameId+'" id="'+this.frameId+'" src="" style="width:100%;min-height:'+this.minHeight+';'+heightStyle+'"></iframe>\
							</div>\
						</div>\
					</div>\
				</div>');
			this.$modal = this.$btn.next();
			this.$modal.find('.modal-dialog').draggable({ handle: ".modal-header" });
			this.$frame = this.$modal.find('iframe');

			var $frame = this.$frame;
			var $modal = this.$modal;
			$frame.on('load', function() {
				var $title = $frame.contents().find('h3').first();
				var html = $title.html();
				$title.remove();
				$modal.find('.modal-title').html(html);
			});

			// Init modal
			this.$btn.attr('data-toggle', 'modal');
			//this.$modal.modal();
		},

		listen: function () {
			this.$modal
				.on("show.bs.modal", $.proxy(this.onShowModal, this));
		},


		onResponse: function (response) {
			if (response.status == 'success') {
				this.$modal.modal('hide');
				document.getElementById(this.frameId).src = this.source;
				var $mainContent = $('#main_content');
				if ($mainContent.length > 0) $mainContent.prepend(response.notifications);
				else $('#notifications').html(response.notifications);
			} else {
				this.$frame.contents().find('#main_content').prepend(response.notifications);
			}
			this.$btn.trigger('asyncform.response', [response]);
		},

		onFormLoad: function() {
			if (this.height == 'wrapContent') this.$frame.height(this.$frame.contents().find('body').height());
		},

		onShowModal: function () {
			if (!this.$frame.attr('src')) this.$frame.attr('src', this.source);
			if (this.height == 'fitParent') this.$frame.height($(window).height() - 200);
		}
	};

	var old = $.fn.asyncform;

	$.fn.asyncform = function (option) {
		var arg = arguments;
		if (typeof option == 'string' && option == 'getActive') {
			return this.data('asyncform').selectedPeople;
		}
		return this.each(function () {
			var $this = $(this)
				, data = $this.data('asyncform')
				, options = typeof option == 'object' && option;
			if (!data) $this.data('asyncform', (data = new Asyncform(this, options)));
			if (typeof option == 'string') {
				if (arg.length > 1) {
					data[option].apply(data, Array.prototype.slice.call(arg ,1));
				} else {
					data[option]();
				}
			}
		});
	};

	$.fn.asyncform.Constructor = Asyncform;

	$.fn.asyncform.noConflict = function () {
		$.fn.asyncform = old;
		return this;
	};

}));

