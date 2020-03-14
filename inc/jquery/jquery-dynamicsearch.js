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

	var Dynamicsearch = function (element, options) {

		$.fn.dynamicsearch.defaults = {
			multiple: false,
			selectedData: [],
			avalues: [],
			adescs: [],
			atitles: [],
			value: '',
			removalWarning: 'Wollen Sie diese Zuweisung wirklich entfernen?',
			showRemovalWarning: true,
			disabled: false,
			exclude: '',
			excludeSql: '',
			mode: '',
			module: '',
			accessToken: '',
			ajaxHandler: false,
			allowParentselect: false
		};

		this.$element = $(element);
		this.options = $.extend({}, $.fn.dynamicsearch.defaults, {
			multiple: this.$element.data("multiple"),
			avalues: typeof this.$element.data("avalues") !== 'undefined' ? this.$element.data("avalues").toString().split(',') : [],
			adescs: typeof this.$element.data("adescs") !== 'undefined' ? this.$element.data("adescs").toString().split(',') : [],
			atitles: typeof this.$element.data("atitles") !== 'undefined' ? this.$element.data("atitles").toString().split(',') : [],
			value: typeof this.$element.data("value") !== 'undefined' ? this.$element.data("value").toString() : '',
			removalWarning: this.$element.data("removal-warning"),
			showRemovalWarning: this.$element.data("show-removal-warning"),
			disabled: this.$element.data("disabled"),
			exclude: this.$element.data("exclude"),
			excludeSql: this.$element.data("exclude-sql"),
			mode: this.$element.data("mode"),
			module: this.$element.data("module"),
			accessToken: this.$element.data("accessToken"),
			ajaxHandler: this.$element.data("ajaxHandler"),
			allowParentselect: this.$element.data("allowParentselect")
		}, options);
		this.multiple = this.options.multiple || this.multiple;
		this.selectedData = this.options.selectedData || this.selectedData;
		this.avalues = this.options.avalues || this.avalues;
		this.adescs = this.options.adescs || this.adescs;
		this.atitles = this.options.atitles || this.atitles;
		this.value = this.options.value || this.value;
		this.removalWarning = this.options.removalWarning || this.removalWarning;
		this.showRemovalWarning = this.options.showRemovalWarning || this.showRemovalWarning;
		this.disabled = this.options.disabled || this.disabled;
		this.exclude = this.options.exclude || this.exclude;
		this.excludeSql = this.options.excludeSql || this.excludeSql;
		this.mode = this.options.mode || this.mode;
		this.module = this.options.module || this.module;
		this.accessToken = this.options.accessToken || this.accessToken;
		this.ajaxHandler = this.options.ajaxHandler || this.ajaxHandler;
		this.allowParentselect = this.options.allowParentselect || this.allowParentselect;

		this.placeholderText = '';

		var that = this;

		if (this.selectedData.length === 0) {
			if (this.avalues.length === 0 && this.value) {
				this.value.split(',').forEach(function(e) {
					if (e) that.avalues.push(e);
				});
			}

		}

		if (this.selectedData.length >= 1) {
		    for (var i in this.selectedData) {
				this.selectedData[i].title = this.getTitle(this.selectedData[i]);
			}
		}

		this.init();
	};

	Dynamicsearch.prototype = {

		constructor: Dynamicsearch,

		init: function() {
			this.initLayout();

			this.updateLayout();
			this.updateValue();

			this.listen();

			this.$element.trigger('dynamicsearch.init');
		},

		initLayout: function () {
			this.$wrapper = this.$element
				.hide()
				.wrap('<div class="dynamicsearch-wrapper"></div>')
				.parent();

			this.$wrapper.append('<div class="taxonomy__suggestnew" id="' + this.$element.attr('id') + '_suggestbox"></div>\n').find('.dynamicsearch-input');

			if (this.disabled) {
				this.$input = this.$wrapper.append('<input type="text" data-fieldid="'+this.options.module+'_input" placeholder="'+this.placeholderText+'" class="dynamicsearch-input-disabled input-sm form-control" disabled>').find('.dynamicsearch-input-disabled');
			} else {
				this.$input = this.$wrapper.append('<input type="text" data-fieldid="'+this.options.module+'_input" placeholder="'+this.placeholderText+'" class="dynamicsearch-input input-sm form-control inlineform input">').find('.dynamicsearch-input');
			}

			this.$input.typeahead({
                source: $.proxy(this.fetch, this),
                matcher: function (item) {
					return true;
				},
				items: 'all',
				minLength: 0,
				showHintOnFocus: true,
                autoSelect: false,

                sorter: function (items) {
					return items;
				},
				focus: function (e) {
					if (!this.focused) {
						this.focused = true;
						if (this.options.showHintOnFocus) {
							this.lookup(this.$element.val());
						}
					}
				},
				highlighter: function (item) {
					return item;
				},
				select: function () {
					var val = this.$menu.find('.active').data('value');
					var input = this.$element.parent().parent().find('[data-disallowplaceholder="true"]');
					this.$element.data('active', val);
					if (this.autoSelect || val) {
						var newVal = this.updater(val);
						if (newVal.placeholder) {
							alert("Obergruppen können nicht direkt ausgewählt werden.");
							return false; // prevent default selection
						}

						this.$element
							.val(this.displayText(newVal) || newVal)
							.change();
						this.afterSelect(newVal);
					}

					return this.hide();
				}
			});

			if (this.multiple) {
				this.$buttonsWrapper = this.$input.after('<div class="dynamicsearch-buttons-wrapper row"></div>').next();
			} else {
				if (this.disabled) {
					this.$button = this.$input.after('<button type="button" class="dynamicsearch-button-disabled btn btn-default btn-sm full-width" disabled></button>').next();
				} else {
					this.$button = this.$input.after('<button type="button" class="dynamicsearch-button btn btn-default btn-sm full-width"></button>').next();
				}
			}
		},

		updateLayout: function () {
			if (this.multiple) {
				this.$buttonsWrapper.html('');
				for (var id_ in this.selectedData) {
					var data = this.selectedData[id_];
					if(typeof data.name === "undefined") continue;
					var name = data.name.replace(/&nbsp;/gi,'');
					var title = data.name.replace(/"/gi,'&quot;');
					data.title = title + " entfernen";

					if(this.options.module === "taxonomy") {
						if (this.disabled) {
							this.$buttonsWrapper.append('<button type="button" class="taxonomy-term__button btn btn-sm btn-primary" title="' + data.title + '" data-id="' + data.id + '" disabled>' + name + '</button>');
						} else {
							this.$buttonsWrapper.append('<button type="button" class="dynamicsearch-button taxonomy-term__button btn btn-sm btn-primary" title="' + data.title + '" data-id="' + data.id + '"><span class="pull-left">' + name + '</span><i class="text-danger pull-right fa fa-remove icon-line-height"></i></button>');
						}
					} else {
						if (this.disabled) {
							this.$buttonsWrapper.append('<div class="col-sm-6"><button type="button" style="width:100%" class="btn btn-default btn-sm" title="' + data.title + '" data-id="' + data.id + '" disabled>' + name + '</button></div>');
						} else {
							this.$buttonsWrapper.append('<div class="col-sm-6"><button type="button" style="width:100%" class="dynamicsearch-button btn btn-default btn-sm" title="' + data.title + '" data-id="' + data.id + '"><span class="pull-left">' + name + '</span><i class="text-danger pull-right fa fa-remove icon-line-height"></i></button></div>');
						}
					}
				}
			} else {
				if (this.selectedData.length > 0) {
					this.$input.hide();
					var removeButtonHtml = '';
					if (!this.disabled) removeButtonHtml = '<i class="text-danger pull-right fa fa-remove icon-line-height"></i>';
					this.$button
						.show()
						.html('<span class="pull-left">'+this.selectedData[0].name.replace(/&nbsp;/gi,'')+'</span>'+removeButtonHtml)
						.attr('title', this.selectedData[0].title);
				}
				else {
					this.$button.hide();
					this.$input.show();
				}
			}
		},

		updateValue: function () {
			var values = [];
			this.selectedData.forEach(function(data) {
				values.push(data.id);
			});
			this.$element.val(values.join(','));
			this.$element.trigger('change');
		},

		listen: function () {
			this.$wrapper
				.on('click', '.typeahead', $.proxy(this.clickAdd, this));
			this.$input
				.on('keydown', $.proxy(this.keydownAdd, this));
			$(".taxonomy__suggestnew")
				.on('click', $.proxy(this.insertNew, this));

			if (this.multiple) {
				this.$buttonsWrapper
					.on('click', '.dynamicsearch-button', $.proxy(this.remove, this));
			} else {
				this.$button
					.on('click', $.proxy(this.remove, this));
			}
		},

		clickAdd: function () {
			this.add();
		},
		keydownAdd: function (e) {
			if (e.which === 13) {
				e.stopImmediatePropagation();
				e.preventDefault();

				$(".dropdown-menu").find("li.active").trigger('click');
				this.$input.focus();

			}
		},

		add: function (data) {
            if (typeof(data) == 'undefined') {
            	data = this.$input.typeahead('getActive');
            }
			if (data.placeholder) {
				return false; // prevent default selection
			}

			if (this.multiple) {
				var found = false;
				this.selectedData.forEach(function(e) {
					if (e.id == data.id) found = true;
				});
				if (!found) this.selectedData.push(data);
			} else {
				this.selectedData = [data];
			}
			this.updateLayout();
			this.updateValue();

			this.$input.val('');
			this.$input.blur();
			this.$element.trigger('dynamicsearch.add', data);
		},

		remove: function (e) {
			var $el = $(e.currentTarget);
			var id = $el.attr('data-id');
			if (this.multiple) {
				for (var index in this.selectedData) {
					if (this.selectedData[index].id === id) {
						this.selectedData.splice(index, 1);
						break;
					}
				}
			} else {
				if (this.showRemovalWarning) {
					var c = confirm(this.removalWarning);
					if (!c) return false;
				}
				this.selectedData = [];
			}
			this.updateLayout();
			this.updateValue();

			this.$element.trigger('dynamicsearch.remove', id);
		},

		getName: function (data) {
			return data.name;
		},

		getId: function (data) {
			return data.id;
		},

		getTitle: function (data) {
			if (data.title) {
				return data.title;
			} else {
				return data.name;
			}
		},
		fetch: function(query, process) {
			var target = $(this);

			if(this.options.ajaxHandler) {
				$.ajax({
					url: this.options.ajaxHandler.url,
					context: document.body,
					data: {
						action: this.options.ajaxHandler.actions.search,
						query: query,
						token: this.mode + "-" + this.accessToken,
						exclude: this.exclude,
						excludesql: this.excludeSql,
						name: this.$element.attr('name'),
						sesid: kOOL.sid,
						allowParentselect: this.allowParentselect,
						allowInsert: (this.options.ajaxHandler.actions.insert !== undefined)
					}
				}).done(function(data) {
					var result = JSON.parse(data);
					var suggestbox = $("#" + $(target)[0].$element.attr("id") + "_suggestbox");
					if(result.length > 0 && result[0].hasOwnProperty("id") && result[0].id == -1) {
						$('.dynamicsearch-wrapper .dropdown-menu').css('display','none');
						suggestbox[0].innerHTML = result[0].name;

						var pos = $(target)[0].$input.position();
						pos.width = $(target)[0].$input.width() + 25;
						pos.height = $(target)[0].$input.height() + 12;
						pos.top = pos.top + pos.height;
						if(pos.width < 250) {
							pos.height = $(target)[0].$input.height() + 32;
						}

						suggestbox.css({
								top: pos.top,
								left: pos.left,
								height: pos.height,
								width: pos.width,
								display: "block"
							});

					} else {
						suggestbox.css("display","none");
						process(result);
					}

					$('.dynamicsearch-wrapper .dropdown-menu li').each(function() {
						var dropdown_item = $(this).find("a");
						if(dropdown_item.attr("title").substring(0,8) === "[parent]") {
							dropdown_item.parent().addClass("dropdown-menu__parent");
							var title = dropdown_item.attr("title");
							dropdown_item.attr("title", title.replace("[parent] ", ""));
						} else if(dropdown_item.attr("title").substring(0,10) === "[children]") {
							dropdown_item.parent().addClass("dropdown-menu__children");
							var title = dropdown_item.attr("title");
							dropdown_item.attr("title", title.replace("[children] ", ""));
						}
					});
				});

			} else {
				var result = this.avalues;
				process(result);
			}
		},
		insertNew: function() {
			if(this.options.ajaxHandler.actions.insert) {
				var result = null;

				$.ajax({
					url: this.options.ajaxHandler.url,
					async: false,
					data: {
						action: this.options.ajaxHandler.actions.insert,
						query: this.$input.val(),
						token: this.mode + "-" + this.accessToken,
						exclude: this.exclude,
						excludesql: this.excludeSql,
						name: this.$element.attr('name'),
						sesid: kOOL.sid
					},
					success: function (data) {
						result = JSON.parse(data);
					}
				});
			}

			this.$input.blur();
			// this.$input.focus();
			$(".taxonomy__suggestnew").css("display","none");
			this.add(result[0]);

		}
	};

	var old = $.fn.dynamicsearch;

	$.fn.dynamicsearch = function (option) {
		var arg = arguments;
		if (typeof option == 'string' && option == 'getActive') {
			return this.data('dynamicsearch').selectedData;
		}
		return this.each(function () {
			var $this = $(this)
				, data = $this.data('dynamicsearch')
				, options = typeof option == 'object' && option;
			if (!data) $this.data('dynamicsearch', (data = new Dynamicsearch(this, options)));
			if (typeof option == 'string') {
				if (arg.length > 1) {
					data[option].apply(data, Array.prototype.slice.call(arg ,1));
				} else {
					data[option]();
				}
			}
		});
	};

	$.fn.dynamicsearch.Constructor = Dynamicsearch;

	$.fn.typeahead.Constructor.prototype.click = function(e) {
		e.preventDefault();
		this.select();
		// this.$element.focus();
	};

	$.fn.dynamicsearch.noConflict = function () {
		$.fn.dynamicsearch = old;
		return this;
	};

}));
