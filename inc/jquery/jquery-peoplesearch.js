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

	var Peoplesearch = function (element, options) {

		$.fn.peoplesearch.defaults = {
			multiple: false,
			selectedPeople: [],
			avalues: [],
			adescs: [],
			astatus: [],
			atitles: [],
			removalWarning: 'Wollen Sie diese Zuweisung wirklich entfernen?',
			showRemovalWarning: true,
			disabled: false,
			exclude: '',
			excludeSql: '',
			mode: '',
			accessToken: ''
		};

		this.$element = $(element);
		this.options = $.extend({}, $.fn.peoplesearch.defaults, {
			multiple: this.$element.data("multiple"),
			avalues: typeof this.$element.data("avalues") !== 'undefined' ? this.$element.data("avalues").toString().split(';;') : [],
			adescs: typeof this.$element.data("adescs") !== 'undefined' ? this.$element.data("adescs").toString().split(';;') : [],
			astatus: typeof this.$element.data("astatus") !== 'undefined' ? this.$element.data("astatus").toString().split(';;') : [],
			atitles: typeof this.$element.data("atitles") !== 'undefined' ? this.$element.data("atitles").toString().split(';;') : [],
			removalWarning: this.$element.data("removal-warning"),
			showRemovalWarning: this.$element.data("show-removal-warning"),
			disabled: this.$element.data("disabled"),
			exclude: this.$element.data("exclude"),
			excludeSql: this.$element.data("exclude-sql"),
			mode: this.$element.data("mode"),
			accessToken: this.$element.data("accessToken")
		}, options);
		this.multiple = this.options.multiple || this.multiple;
		this.selectedPeople = this.options.selectedPeople || this.selectedPeople;
		this.avalues = this.options.avalues || this.avalues;
		this.adescs = this.options.adescs || this.adescs;
		this.astatus = this.options.astatus || this.astatus;
		this.atitles = this.options.atitles || this.atitles;
		this.removalWarning = this.options.removalWarning || this.removalWarning;
		this.showRemovalWarning = this.options.showRemovalWarning || this.showRemovalWarning;
		this.disabled = this.options.disabled || this.disabled;
		this.exclude = this.options.exclude || this.exclude;
		this.excludeSql = this.options.excludeSql || this.excludeSql;
		this.mode = this.options.mode || this.mode;
		this.accessToken = this.options.accessToken || this.accessToken;

		this.placeholderText = kOOL_ll.peoplesearch_placeholder_text;

		if (this.selectedPeople.length == 0) {
			for (var i = 0; i < this.avalues.length; i++) {
				var value = this.avalues[i];
				if (value == null) continue;
				var desc = this.adescs[i];
				var astatus = this.astatus[i];
				var title = '';
				if (this.atitles[i]) title = this.atitles[i];
				else title = desc;
				this.selectedPeople.push({id: value, name: desc, title: title, status: astatus});
			}
		} else {
			for (var i = 0; i < this.selectedPeople.length; i++) {
				this.selectedPeople[i].title = this.getTitle(this.selectedPeople[i]);
			}
		}

		this.initLayout();

		this.updateLayout();
		this.updateValue();

		this.listen();
	};

	Peoplesearch.prototype = {

		constructor: Peoplesearch,

		initLayout: function () {
			this.$wrapper = this.$element
				.hide()
				.wrap('<div class="peoplesearch-wrapper"></div>')
				.parent();
			if (this.disabled) {
				this.$input = this.$wrapper.append('<input type="text" placeholder="'+this.placeholderText+'" class="peoplesearch-input-disabled input-sm form-control" disabled>').find('.peoplesearch-input-disabled');
			} else {
				this.$input = this.$wrapper.append('<input type="text" placeholder="'+this.placeholderText+'" class="peoplesearch-input input-sm form-control">').find('.peoplesearch-input');
			}

			this.$input.typeahead({
				source: $.proxy(this.fetch, this),
				matcher: function (item) {
					/*
					var it = this.displayText(item).toLowerCase();
					var displayParts = it.split(' ');
					var queryParts = this.query.toLowerCase().split(' ');
					var queryPartsMatched = 0;
					queryParts.forEach(function(queryPart) {
						var matchIndex = -1;
						displayParts.forEach(function(displayPart, i) {
							if (displayPart.indexOf(queryPart) == 0) matchIndex = i;
						});
						if (queryPart == '') {
							queryPartsMatched++;
						} else if (matchIndex >= 0) {
							displayParts.splice(matchIndex, 1);
							queryPartsMatched++;
						}
					});
					return queryPartsMatched > 0;*/
					return true;
				}
			});

			if (this.multiple) {
				this.$buttonsWrapper = this.$input.after('<div class="peoplesearch-buttons-wrapper row"></div>').next();
			} else {
				if (this.disabled) {
					this.$button = this.$input.after('<button type="button" class="peoplesearch-button-disabled btn btn-default btn-sm full-width" disabled></button>').next();
				} else {
					this.$button = this.$input.after('<button type="button" class="peoplesearch-button btn btn-default btn-sm full-width"></button>').next();
				}
			}
		},

		updateLayout: function () {
			if (this.multiple) {
				this.$buttonsWrapper.html('');
				for (var id_ in this.selectedPeople) {
					var person = this.selectedPeople[id_];
					if (this.disabled) {
						this.$buttonsWrapper.append('<div class="col-sm-6"><button type="button" style="width:100%" class="btn btn-default btn-sm peoplesearch__status__'+person.status+'" title="'+person.title+'" data-id="'+person.id+'" disabled>'+person.name+'</button></div>');
					} else {
						this.$buttonsWrapper.append('<div class="col-sm-6"><button type="button" style="width:100%" class="peoplesearch-button btn btn-default btn-sm peoplesearch__status__'+person.status+'" title="'+person.title+'" data-id="'+person.id+'"><span class="pull-left">'+person.name+'</span><i class="text-danger pull-right fa fa-remove icon-line-height"></i></button></div>');
					}
				}
			} else {
				if (this.selectedPeople.length > 0) {
					this.$input.hide();
					var removeButtonHtml = '';
					if (!this.disabled) removeButtonHtml = '<i class="text-danger pull-right fa fa-remove icon-line-height"></i>';
					this.$button
						.show()
						.html('<span class="pul-left">'+this.selectedPeople[0].name+'</span>'+removeButtonHtml)
						.attr('title', this.selectedPeople[0].title)
						.addClass("peoplesearch__status__"+this.selectedPeople[0].status);
				}
				else {
					this.$button.hide();
					this.$input.show();
				}
			}
		},

		updateValue: function () {
			var values = [];
			this.selectedPeople.forEach(function(person) {
				values.push(person.id);
			});
			this.$element.val(values.join(',')).trigger("change");
		},

		listen: function () {
			this.$wrapper
				.on('click', '.typeahead', $.proxy(this.clickAdd, this));
			this.$input
				.on('keyup', $.proxy(this.keyupAdd, this));

			if (this.multiple) {
				this.$buttonsWrapper
					.on('click', '.peoplesearch-button', $.proxy(this.remove, this));
			} else {
				this.$button
					.on('click', $.proxy(this.remove, this));
			}
		},

		clickAdd: function () {
			this.add();
		},

		keyupAdd: function (e) {
			if (e.which == 13) {
				this.add();
			} else {
				return false;
			}
		},

		add: function (person) {
			if (typeof(person) == 'undefined') person = this.$input.typeahead('getActive');

			if (this.multiple) {
				this.selectedPeople.push(person);
			} else {
				this.selectedPeople = [person];
			}
			this.updateLayout();
			this.updateValue();

			this.$input.val('');
			this.$input.focus();

			this.$element.trigger('peoplesearch.add', person);
		},

		remove: function (e) {
			var $el = $(e.currentTarget);
			var id = $el.attr('data-id');
			if (this.multiple) {
				for (var index in this.selectedPeople) {
					if (this.selectedPeople[index].id == id) {
						this.selectedPeople.splice(index, 1);
						break;
					}
				}
			} else {
				if (this.showRemovalWarning) {
					var c = confirm(this.removalWarning);
					if (!c) return false;
				}
				this.selectedPeople = [];
			}
			this.updateLayout();
			this.updateValue();

			this.$input.focus();

			this.$element.trigger('peoplesearch.remove', id);
		},

		getName: function (person) {
			return person.name;
		},

		getId: function (person) {
			return person.id;
		},

		getTitle: function (person) {
			if (person.title) {
				return person.title;
			} else {
				return person.name;
			}
		},

		fetch: function(query, process) {
			$.get(
				"../leute/inc/ajax.php",
				{
					action: "peoplesearchnew",
					query: query,
					token: this.mode + "-" + this.accessToken,
					exclude: this.exclude,
					excludesql: this.excludeSql,
					name: this.$element.attr('name'),
					sesid: kOOL.sid
				}, function(data) {
					var result = JSON.parse(data);

					process(result);
				}
			);
		}

	};

	var old = $.fn.peoplesearch;

	$.fn.peoplesearch = function (option) {
		var arg = arguments;
		if (typeof option == 'string' && option == 'getActive') {
			return this.data('peoplesearch').selectedPeople;
		}
		return this.each(function () {
			var $this = $(this)
				, data = $this.data('peoplesearch')
				, options = typeof option == 'object' && option;
			if (!data) $this.data('peoplesearch', (data = new Peoplesearch(this, options)));
			if (typeof option == 'string') {
				if (arg.length > 1) {
					data[option].apply(data, Array.prototype.slice.call(arg ,1));
				} else {
					data[option]();
				}
			}
		});
	};

	$.fn.peoplesearch.Constructor = Peoplesearch;

	$.fn.peoplesearch.noConflict = function () {
		$.fn.peoplesearch = old;
		return this;
	};

}));
