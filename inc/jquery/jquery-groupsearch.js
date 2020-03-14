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

	var Groupsearch = function (element, options) {

		$.fn.groupsearch.defaults = {
			multiple: false,
			selectedGroups: [],
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
			accessToken: '',
			includeRoles: false
		};

		this.$element = $(element);
		this.options = $.extend({}, $.fn.groupsearch.defaults, {
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
			accessToken: this.$element.data("accessToken"),
			includeRoles: this.$element.data("includeRoles")
		}, options);
		this.multiple = this.options.multiple || this.multiple;
		this.selectedGroups = this.options.selectedGroups || this.selectedGroups;
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
		this.accessToken = this.options.accessToken || this.accessToken;
		this.includeRoles = this.options.includeRoles || this.includeRoles;

		this.placeholderText = kOOL_ll.groupsearch_placeholder_text;

		var that = this;

		if (this.selectedGroups.length == 0) {
			if (this.avalues.length == 0 && this.value) {
				this.value.split(',').forEach(function(e) {
					if (e) that.avalues.push(e);
				});
			}

			this.avalues = this.avalues.map(function(e) {
				var groupId = e,
					roleId = '';

				if (e.indexOf(':') > -1) {
					var s = e.split(':');
					if (s.length != 2) {
						alert("The found group id was formatted incorrectly");
						return false;
					}
					groupId = s[0];
					roleId = s[1];
				}
				while (groupId.length < 6) groupId = '0'+groupId;
				var r = 'g'+groupId;
				if (roleId) {
					while (roleId.length < 6) roleId = '0'+roleId;
					r = r + ':r' + roleId;
				}

				return r;
			});
		}

		var deferredInit = false;
		if (this.selectedGroups.length == 0 && this.adescs.length == 0) {
			deferredInit = true;
			this.fetchIds(this.avalues, function(result) {
				result.forEach(function(e) {
					that.selectedGroups.push(e);
				});
				that.init();
			});
		} else if (this.selectedGroups.length == 0) {
			for (var i = 0; i < this.avalues.length; i++) {
				var value = this.avalues[i];
				if (value == null) continue;
				var desc = this.adescs[i];
				var title = '';
				if (this.atitles[i]) title = this.atitles[i];
				else title = desc;
				this.selectedGroups.push({id: value, name: desc, title: title});
			}
		} else {
			for (var i = 0; i < this.selectedGroups.length; i++) {
				this.selectedGroups[i].title = this.getTitle(this.selectedGroups[i]);
			}
		}


		if (!deferredInit) {
			this.init();
		}
	};

	Groupsearch.prototype = {

		constructor: Groupsearch,

		init: function() {
			this.initLayout();

			this.updateLayout();
			this.updateValue();

			this.listen();

			this.$element.trigger('groupsearch.init');
		},

		initLayout: function () {
			this.$wrapper = this.$element
				.hide()
				.wrap('<div class="groupsearch-wrapper"></div>')
				.parent();
			if (this.disabled) {
				this.$input = this.$wrapper.append('<input type="text" placeholder="'+this.placeholderText+'" class="groupsearch-input-disabled input-sm form-control" disabled>').find('.groupsearch-input-disabled');
			} else {
				this.$input = this.$wrapper.append('<input type="text" placeholder="'+this.placeholderText+'" class="groupsearch-input input-sm form-control">').find('.groupsearch-input');
			}

			this.$input.typeahead({
				source: $.proxy(this.fetch, this),
				matcher: function (item) {
					return true;
				},
				items: 'all',
				minLength: 0,
				showHintOnFocus: true,
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
						if (input.data('disallowplaceholder') === true && newVal.placeholder) {
							alert("Platzhalter können nicht als Gruppe gesetzt werden.");
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
				this.$buttonsWrapper = this.$input.after('<div class="groupsearch-buttons-wrapper row"></div>').next();
			} else {
				if (this.disabled) {
					this.$button = this.$input.after('<button type="button" class="groupsearch-button-disabled btn btn-default btn-sm full-width" disabled></button>').next();
				} else {
					this.$button = this.$input.after('<button type="button" class="groupsearch-button btn btn-default btn-sm full-width"></button>').next();
				}
			}
		},

		updateLayout: function () {
			if (this.multiple) {
				this.$buttonsWrapper.html('');
				for (var id_ in this.selectedGroups) {
					var group = this.selectedGroups[id_];
					var name = group.name.replace(/&nbsp;/gi,'');
					if (this.disabled) {
						this.$buttonsWrapper.append('<div class="col-sm-6"><button type="button" style="width:100%" class="btn btn-default btn-sm" title="'+group.title+'" data-id="'+group.id+'" disabled>'+name+'</button></div>');
					} else {
						this.$buttonsWrapper.append('<div class="col-sm-6"><button type="button" style="width:100%" class="groupsearch-button btn btn-default btn-sm" title="'+group.title+'" data-id="'+group.id+'"><span class="pull-left">'+name+'</span><i class="text-danger pull-right fa fa-remove icon-line-height"></i></button></div>');
					}
				}
			} else {
				if (this.selectedGroups.length > 0) {
					this.$input.hide();
					var removeButtonHtml = '';
					if (!this.disabled) removeButtonHtml = '<i class="text-danger pull-right fa fa-remove icon-line-height"></i>';
					this.$button
						.show()
						.html('<span class="pull-left">'+this.selectedGroups[0].name.replace(/&nbsp;/gi,'')+'</span>'+removeButtonHtml)
						.attr('title', this.selectedGroups[0].title);
				}
				else {
					this.$button.hide();
					this.$input.show();
				}
			}
		},

		updateValue: function () {
			var values = [];
			this.selectedGroups.forEach(function(group) {
				values.push(group.id);
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
					.on('click', '.groupsearch-button', $.proxy(this.remove, this));
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

		add: function (group) {
			if (typeof(group) == 'undefined') group = this.$input.typeahead('getActive');
			var input = this.$element.parent().parent().find('[data-disallowplaceholder="true"]');
			if (input.data('disallowplaceholder') === true  && group.placeholder) {
				return false; // prevent default selection
			}

			if (this.multiple) {
				var found = false;
				this.selectedGroups.forEach(function(e) {
					if (e.id == group.id) found = true;
				});
				if (!found) this.selectedGroups.push(group);
			} else {
				this.selectedGroups = [group];
			}
			this.updateLayout();
			this.updateValue();

			this.$input.val('');

			this.$input.blur();
			this.$input.focus();

			this.$element.trigger('groupsearch.add', group);
		},

		remove: function (e) {
			var $el = $(e.currentTarget);
			var id = $el.attr('data-id');
			if (this.multiple) {
				for (var index in this.selectedGroups) {
					if (this.selectedGroups[index].id == id) {
						this.selectedGroups.splice(index, 1);
						break;
					}
				}
			} else {
				if (this.showRemovalWarning) {
					var c = confirm(this.removalWarning);
					if (!c) return false;
				}
				this.selectedGroups = [];
			}
			this.updateLayout();
			this.updateValue();

			this.$element.trigger('groupsearch.remove', id);
		},

		getName: function (group) {
			return group.name;
		},

		getId: function (group) {
			return group.id;
		},

		getTitle: function (group) {
			if (group.title) {
				return group.title;
			} else {
				return group.name;
			}
		},

		fetch: function(query, process) {
			$.get(
				"../groups/inc/ajax.php",
				{
					action: "groupsearch",
					query: query,
					token: this.mode + "-" + this.accessToken,
					exclude: this.exclude,
					excludesql: this.excludeSql,
					name: this.$element.attr('name'),
					includeroles: this.includeRoles,
					sesid: kOOL.sid
				}, function(data) {
					var result = JSON.parse(data);
					process(result);
				}
			);
		},

		fetchIds: function(ids, done) {
			$.get(
				"../groups/inc/ajax.php",
				{
					action: "groupsearchids",
					ids: ids.join(','),
					token: this.mode + "-" + this.accessToken,
					name: this.$element.attr('name'),
					sesid: kOOL.sid
				}, function(data) {
					done(JSON.parse(data));
				}
			);
		}

	};

	var old = $.fn.groupsearch;

	$.fn.groupsearch = function (option) {
		var arg = arguments;
		if (typeof option == 'string' && option == 'getActive') {
			return this.data('groupsearch').selectedGroups;
		}
		return this.each(function () {
			var $this = $(this)
				, data = $this.data('groupsearch')
				, options = typeof option == 'object' && option;
			if (!data) $this.data('groupsearch', (data = new Groupsearch(this, options)));
			if (typeof option == 'string') {
				if (arg.length > 1) {
					data[option].apply(data, Array.prototype.slice.call(arg ,1));
				} else {
					data[option]();
				}
			}
		});
	};

	$.fn.groupsearch.Constructor = Groupsearch;

	$.fn.groupsearch.noConflict = function () {
		$.fn.groupsearch = old;
		return this;
	};

}));
