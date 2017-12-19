/**
 * Online booking widget constructor for Arctic Reservations.
 * Allows replacing the browse trips page with custom interfaces as part of the larger site. Requires
 * custom JavaScript to actually implement this specific interface.
 *
 * Requires jQuery.
 * Supports AMD loading (more testing needed).
 */
(function(factory) {
	if (typeof define === 'function' && define.amd) {
		define("ArcticReserveWidget", ["jquery"], factory);
	}
	else {
		this.ArcticReserveWidget = factory(jQuery);
	}
})(function($) {
	// BEGIN DEFAULTS

	var defaults = {
		gfsDomain: '', // REQUIRED; e.g., reservations.raftingcompany.com
		useSsl: true, // for debugging purposes only (all gfs require SSL)
		showAvailability: true,
		templates: {
			field: function (obj) {
				// <div class="control-group"><% if (id) { %><label class="control-label" for="<%= id %>"><% if(typeof html !== "undefined" && html ) { %><%= label %><% } else { %><%- label %><% } %></label><% } else { %><div class="control-label"><%- label %></div><% } %><div class="controls"><%= controls %><% if (help) { %><span class="help-inline"><%- help %></span><% } %></div></div>
				return '<div class="control-group">' + (obj.id ? '<label class="control-label" for="' + obj.id + '">' : '<div class="control-label">') + _escape(obj.label) + (obj.id ? '</label>' : '</div>') + '<div class="controls">' + obj.controls + (obj.help ? '<span class="help-inline">' + _escape(obj.help) + '</span>' : '') + '</div></div>';
			},
			bookForm: function (obj) {
				// <div class="book-form"><form action="<%- url %>" method="post"><%= details %><%= fields %><div class="form-actions><button type="submit" autocomplete="off">Next</button></div></form></div>
				return '<div class="book-form"><form action="' + _escape(obj.url) + '" method="post">' + obj.details + obj.fields + '<div class="form-actions"><button type="submit" autocomplete="off">Next</button></div></form></div>';
			},
			bookAddOnHeader: function (obj) {
				// <% if (description) { %><p><%- description %></p><% } %>
				if (obj.description) {
					return "<p>" + _escape(obj.description) + "</p>";
				}
				return "";
			},
			bookAddOnGroupHeader: function (obj) {
				// <p><b><%- name %></b><% if (description) { %><br><%- description %><% } %></p><% if ( other ) { %><div class="addons addon-other"><% } else { %><div class="addons addon-set"><% } %>
				return "<p><b>" + _escape(obj.name) + "</b>" + (obj.description ? "<br>" + _escape(obj.description) : "") + "</p>" + (obj.other ? '<div class="addons addon-other">' : '<div class="addons addon-set">');
			},
			bookAddOnGroupFooter: function(obj) {
				return "</div>";
			},
			bookAddOnReservation: function (obj) {
				// <select name="<%= id %>" id="<%= id %>"><option value="false"<% if ( has ) { %> selected<% } %>>No</option><option value="true"<% if ( has ) { %> selected<% } %>>Yes (add <%= TUNDRA.formatCurrency(amount) %>)</option></select>
				return '<select name="' + obj.name + '" id="' + obj.id + '"><option value="false"' + (obj.has ? "" : " selected") + '>No</option><option value="true"' + (obj.has ? " selected" : "") + '>Yes (add ' + _formatCurrency(obj.amount) + ')</option></select>';
			},
			bookAddOnGuest: function (obj) {
				return '<input type="number" name="' + obj.name + '" id="' + obj.id + '" size="3" maxlength="3" class="input-small" value="' + _escape(obj.has) + '" min="0" step="' + _escape(obj.step || 1) + '"' + (obj.mul ? ' data-mul="' + obj.mul + '"' : '') + (obj.def ? ' data-isdefault="true"' : '') + '> (' + _formatCurrency(obj.amount) + ' ' + obj.amount_per + ')';
			},
			bookAvailabilityMessage: function(obj) {
				if (false === obj.available) {
					return '<div class="availability"><p>' + _escape(obj.message) + '</p><p>You can <a href="' + obj.inquire_url + '"><strong>make an inquiry</strong></a> and we will help complete your reservation.</p><input type="hidden" name="_hint" value="inquiry"></div>';
				}
				return '';
			}
		}
	};

	// BEGIN UTILITIES

	// escape utility from loDash
	var escapeMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#x27;'
	}, escapeRegex = new RegExp('[&<>"\']', 'g');

	function _escape(string) {
		if (string === null) return '';
		return ('' + string).replace(escapeRegex, function(match) {
			return escapeMap[match];
		});
	}

	function _formatCurrency(num) {
		var sign, cents;
		num = num.toString().replace(/\$|\,/g, '');
		if (isNaN(num)) num = "0";
		sign = (num == (num = Math.abs(num)));
		num = Math.floor(num * 100 + 0.50000000001);
		cents = num % 100;
		num = Math.floor(num / 100).toString();
		if (cents < 10) cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++) {
			num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
		}
		return (((sign) ? '' : '-') + '$' + num + '.' + cents);
	}

	function _debounce(func, threshold, execAsap) {
		var timeout;
		return function debounced() {
			var obj = this, args = arguments;
			function delayed() {
				if (!execAsap)
					func.apply(obj, args);
				timeout = null;
			}
			if (timeout)
				clearTimeout(timeout);
			else if (execAsap)
				func.apply(obj, args);
			timeout = setTimeout(delayed, threshold || 100);
		};
	}

	// BEGIN WIDGET CODE

	function widget(instance_options) {
		var options = $.extend(true, {}, defaults, instance_options), req, t = this;

		// allows use of the reserve widget on the guest-facing site without resorting to JSONP
		function _useJsonp() {
			return (options.gfsDomain && location.host !== options.gfsDomain);
		}

		this.fetchLandingPage = function (web_name, callback) {
			return this.fetch({
				web_name: web_name
			}, callback);
		};

		this.fetchQuery = function (query, callback) {
			return this.fetch({
				query: query
			}, callback);
		};

		this.fetchRange = function (start, end, callback) {
			return this.fetch({
				start: start,
				end: end
			}, callback);
		};

		function _getBaseUrl() {
			if (options.gfsDomain) {
				return (options.useSsl ? "https://" : "http://") + options.gfsDomain;
			}
			return "";
		}

		this.fetch = function (opts, callback, err_callback) {
			// opts: start (default: empty), end (default: empty), query (default: empty),
			//       from (default: 0), limit (default: 100), web_name (default: empty),
			//       group (default: true), guests (default: empty), multi (default: false),
			//       filters (default: empty), success (instead of callback),
			//       error (instead of err_callback)

			var url = _getBaseUrl() + "/reserve/api", get_params = {}, cur_req;

			// add web_name
			if (opts.web_name) {
				url = url + "/" + opts.web_name;
			}

			// add get_params
			$.each(["start", "end", "query", "from", "limit", "group", "guests"], function (k, v) {
				if (v in opts) {
					get_params[v] = opts[v];
				}
			});

			// support additional filters
			if (opts.filters) {
				$.each(["bg", "date", "lp", "guests", "time", "type", "on"], function(k, v) {
					if (v in opts.filters) {
						get_params[v] = opts.filters[v];
					}
				});
			}

			// callback via options
			if (opts.success) {
				callback = opts.success;
			}
			if (opts.error) {
				err_callback = opts.error;
			}

			// cancel existing request
			if (!opts.multi) {
				if (req) {
					req.abort();
					req = null;
				}
			}

			// run query
			cur_req = $.ajax({
				cache: true,
				dataType: (_useJsonp() ? "jsonp" : "json"),
				url: url,
				data: get_params
			}).done(function (data) {
				if (data.success) {
					callback(data.results, data.filters);
				}
				else if (data.error) {
					err_callback(data.details);
				}
				else {
					err_callback("Unexpected response.");
				}
			}).fail(function (jqXHR, text_status, error_thrown) {
				// error callback
				if (err_callback) {
					err_callback("Request failed: " + (error_thrown || text_status));
				}
			}).always(function () {
				// clean up
				if (!opts.multi) {
					req = null;
				}
			});

			// if not multi, store as single request
			if (!opts.multi) {
				req = cur_req;
			}

			return cur_req;
		};

		this.checkAvailability = function (result, guests, callback) {
			var url = _getBaseUrl() + "/reserve/api/check/", get_params = {guests: guests};

			// add trip id
			if (result.id) {
				url = url + result.id;
			}
			else {
				url = url + result;
			}

			return $.ajax({
				cache: true,
				dataType: (_useJsonp() ? "jsonp" : "json"),
				url: url,
				data: get_params
			}).success(function (data) {
				if (data.success && callback) {
					callback(data.action, data.label);
				}
			});
		};

		var _bookFormCount = 0;
		function _buildBookFields(pricing, guests) {
			// make sets
			var sets = {":pl": '<div class="pricing-levels">'}, other = "", prefix = "bf" + ++_bookFormCount + "_";

			$.each(pricing, function (ind, el) {
				//set,nm,amnt,desc,per,def,div
				var code;

				// per reservation add on, use a select
				if (el.per && "res" === el.per) {
					code = options.templates.bookAddOnReservation({
						id: prefix + el.id,
						name: el.id,
						has: (el.def ? true : false),
						amount: el.amnt,
						def: ':pl' !== el.set && el.def
					});
				}
				else if (el.pi) {
					// per guest add-on, where quantity is specified per item
					code = options.templates.bookAddOnGuest({
						id: prefix + el.id,
						name: el.id + '_ic',
						has: (el.def && guests ? Math.ceil(guests / el.div) : 0),
						step: 1,
						amount: el.amnt * el.div,
						amount_per: el.qw || "per item",
						mul: el.div,
						def: ':pl' !== el.set && el.def
					});
				}
				else {
					code = options.templates.bookAddOnGuest({
						id: prefix + el.id,
						name: el.id,
						has: (el.def && guests ? guests : 0),
						step: (el.div || 1),
						amount: el.amnt,
						amount_per: el.qw || "per guest",
						mul: null,
						def: ':pl' !== el.set && el.def
					});
				}

				// add field
				code = options.templates.field({
					label: el.nm,
					id: prefix + el.id,
					help: (el.set && el.desc ? el.desc : null),
					image: el.img,
					image_url: el.img ? t.getImageUrl(el.img) : "",
					controls: code
				});

				// if
				if (el.set) {
					sets[el.set] = ( sets[el.set] || options.templates.bookAddOnGroupHeader({
						name: el.set,
						description: null,
						other: false
					}) ) + code;
				}
				else {
					other = other + options.templates.bookAddOnHeader({
						description: el.desc
					}) + code;
				}
			});

			// add a header and footer to the other category
			if (other) {
				other = options.templates.bookAddOnGroupHeader({
					name: "Additional Options",
					description: null,
					other: true
				}) + other + options.templates.bookAddOnGroupFooter({
					set: null
				});
			}

			// join sets
			var ret = '';
			$.each(sets, function (k, v) {
				ret = ret + v + (":pl" === k ? '</div>' : options.templates.bookAddOnGroupFooter({
					set: k
				}));
			});
			return ret + other;
		}

		this.buildBookFormHTML = function(result, guests) {
			// checking
			if (!result.id) throw "Expected a result object.";
			if (!result.reserve_url) throw "Result object is not eligible for booking.";

			return options.templates.bookForm({
				fields: _buildBookFields(result.pricing, guests),
				url: result.reserve_url,
				details: result.details||""
			});
		};

		this.getImageUrl = function(image_id, width, height) {
			var sz;

			// default size
			if (!width)
				width = 400;

			if (!height || height === width) {
				sz = width; // square
			}
			else {
				sz = width + "x" + height;
			}
			// build URL
			return _getBaseUrl() + "/image/" + image_id + "/" + sz;
		};

		function _getGuestCountContainer(el) {
			var $el = $(el);

			// use pricing level container, if not empty
			var pl = $el.find(".pricing-levels");
			if (pl.find("input").length) {
				return pl;
			}

			// use add-on set
			var ao = $el.find(".addon-set");
			if (ao.length) {
				return ao.first();
			}

			return pl; // fallback to pricing level
		}

		var _total_guest_count;

		// get guest count
		function _getGuestCount(ev) {
			var $cont = _getGuestCountContainer($(ev.target).closest("form"));
			_total_guest_count = _totalInputs($cont);
		}

		// update availability message
		function _updateAvailabilityMessage(ev) {
			var $form = $(ev.target), new_html = options.templates.bookAvailabilityMessage(ev), a_m;

			a_m = $form.data("amsg");

			if (!a_m) {
				// nothing to do
				if (!new_html)
					return;

				// show availability message
				$form.data("amsg", $(new_html).insertAfter(_getGuestCountContainer($form)).hide().show("fast"));
			}
			else {
				// nothing to show
				if (!new_html) {
					// remove data
					$form.removeData("amsg");

					// hide and remove
					a_m.hide("fast", function() {
						a_m.remove();
					});

					return;
				}

				// create
				var new_el = $(new_html);

				// update data
				$form.data("amsg", new_el);

				// replace
				a_m.replaceWith(new_el);
			}
		}

		// check guest count
		var _checkCount = 0, _checkGuestCount = _debounce(function(result, form, total_guests) {
			var $form = $(form), cur_count = ++_checkCount;

			if (!total_guests) {
				total_guests = _totalInputs(_getGuestCountContainer($form));
			}

			// run check availability
			t.checkAvailability(result, total_guests).done(function(data) {
				// only run if current request
				if (_checkCount !== cur_count)
					return;

				if (data.success) {
					if (1 === data.action) {
						$form.trigger($.Event("updateavailability", {
							available: false,
							message: data.message,
							inquire_url: data.inquire_url,
							guests: total_guests
						}));
					}
					else {
						$form.trigger($.Event("updateavailability", {
							available: true,
							guests: total_guests
						}));
					}
				}
				else {
					$form.trigger($.Event("updateavailability", {
						available: null,
						error: data.error || "Unknown response.",
						guests: total_guests
					}));
				}
			}).fail(function() {
				// only run if current request
				if (_checkCount !== cur_count)
					return;

				$form.trigger($.Event("updateavailability", {
					available: null,
					error: "Unable to connect.",
					guests: total_guests
				}));
			});
		}, 200);

		function _adjustGuestCount(result, ev) {
			var $cont = _getGuestCountContainer($(ev.target).closest("form"));

			// get delta
			var new_total = _totalInputs($cont), delta = (new_total - _total_guest_count);

			// look for defaults
			var bf = $(ev.target).closest("form");

			// no change
			if (delta === 0) {
				return;
			}

			// fetch booking message
			_checkGuestCount(result, bf);

			// adjust defaults
			bf.find("input[data-isdefault]").each(function () {
				var $this = $(this);
				var cur_mul = (parseInt($this.data("mul"), 10) || 1);
				var cur_val = (parseInt($this.val(), 10) || 0) * cur_mul;
				var new_val;
				var $set = $this.closest(".addons");

				// skip if this is the container used for guest count
				if ($set[0] === $cont[0]) { return; }

				if (delta > 0) {
					// adjust default
					new_val = cur_val + delta;
					if (new_val > new_total) {
						new_val = new_total;
					}
					$this.val(Math.ceil(new_val / cur_mul)).trigger($.Event("autoadjust", {
						old_val: cur_val,
						new_val: cur_val + delta,
						delta: delta
					}));
				}
				else if (delta < 0) {
					// adjust downward, if there are more than the total
					if ($set.hasClass("addon-other")) {
						// adjust downward
						if (cur_val > new_total) {
							new_val = new_total;
							$this.val(Math.ceil(new_val / cur_mul)).trigger($.Event("autoadjust", {
								old_val: cur_val,
								new_val: new_total,
								delta: delta
							}));
						}
					}
					else {
						var set_total = _totalInputs($set);
						if (set_total > new_total) {
							new_val = cur_val - ( set_total - new_total );
							if (new_val < 0) {
								new_val = 0;
							}
							$this.val(Math.ceil(new_val / cur_mul)).trigger($.Event("autoadjust", {
								old_val: cur_val,
								new_val: new_val,
								delta: delta
							}));
						}
					}
				}
			});

			_total_guest_count = new_total;
		}

		this.buildBookFormElement = function(result, guests) {
			var html = this.buildBookFormHTML(result, guests), $el;

			// turn into DOM object
			$el = $(html);

			// add events
			_getGuestCountContainer($el).find("input").on("focus", _getGuestCount).on("change", function(ev) {
				_adjustGuestCount(result, ev);
			});

			// add event listener to show availability
			if (options.showAvailability) {
				$el.find("form").on("updateavailability", _updateAvailabilityMessage);
			}

			// cache guest count (in case quantity is changed without focus)
			_getGuestCount({target: $el});

			return $el;
		};

		// utility
		function _totalInputs(el) {
			var $el = $(el), cnt = 0;
			$el.find("input[type=number]").each(function () {
				var $this = $(this);
				cnt = cnt + (parseInt($this.val(), 10) || 0) * (parseInt($this.data("mul"), 10) || 1);
			});
			return cnt;
		}

		this.getTotalGuests = function (el) {
			return _totalInputs(_getGuestCountContainer(el));
		};
	}

	// export utilities
	widget.utilities = {
		escape: _escape,
		formatCurrency: _formatCurrency,
		debounce: _debounce
	};

	return widget;
});
