$(function() {
	// UTILITIES
	var _escapeMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;'
	};

	function _escapeReplace(tag) {
		return _escapeMap[tag] || tag;
	}

	function _escape(str) {
		return str.replace(/[&<>]/g, _escapeReplace);
	}

	function _isTouchDevice() {
		return ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;
	}

	function _scrollToElement(el) {
		$("body").animate({scrollTop: $(el).offset().top - 20});
	}

	// MAIN CODE
	// create widget
	var w = new ArcticReserveWidget({
		gfsDomain: "reservations.theraftingcompany.com"
	});

	// populate calendar
	var _calendarRequest = 0;
	function _getCalendarEvents(start, end, tz, callback) {
		var guest_count = parseInt($("#guests").val(), 10), req = ++_calendarRequest;

		if (!(guest_count > 0))
			guest_count = null;

		// run query
		w.fetch({
			start: start.format("YYYY-MM-DD"),
			end: end.format("YYYY-MM-DD"),
			guests: guest_count
		}, function(data) {
			var events = [], legend = [];

			$.each(data, function(i, group) {
				var group_color = group.results[0].color;

				// add to legend
				legend.push('<div><span class="swatch" style="background-color:' + group_color + ';"></span> ' + _escape(group.name) + '</div>');

				// add events
				$.each(group.results, function(i, result) {
					var start = moment(result.start), end = moment(result.end), event = {
						id: result.id,
						title: result.name,
						allDay: 0 === start.hour() && 0 === start.minute() && 0 === end.hour() && 0 === end.minute(),
						color: group_color,
						start: start,
						end: end,
						result: result // store result
					};

					// change the color
					if (2 !== result.action) {
						event.borderColor = group_color;
						event.textColor = '#999999';
						event.backgroundColor = '#ffffff';
						event.className = 'trip-inquire';
						event.url = result.inquire_url;
					}
					else {
						event.color = group_color;
						event.className = 'trip-reserve';
						event.url = result.reserve_url;
					}

					events.push(event);
				});
			});

			// only draw legend if current request
			if (_calendarRequest === req) {
				$("#legend").html(legend.join(""))[0 === legend.length ? "hide" : "show"]();
			}

			// update calendar
			callback(events);
		}, function(err) {
			// run callback
			callback([]);
		});
	}


	// create full calendar
	$("#calendar").fullCalendar({
		events:_getCalendarEvents,
		header:{
			left:'prev,next today',
			center:'title',
			right:''
		},
		fixedWeekCount: false,
		height: "auto",
		defaultView: "month",
		eventClick:function(event){
			if (event.result && 2 === event.result.action) {
				// empty booking form
				$("#book").empty().append('<h2>Make Reservation</h2>');

				// make booking form
				w.buildBookFormElement(event.result).appendTo("#book");

				// scroll to booking form
				_scrollToElement("#book");

				return false;
			}
		},
		eventRender: function( event, element , view ) {
			// only add popover if non-touch device
			if (_isTouchDevice()) return;

			if (event.result.notes) {
				element.attr("title", event.results.notes);
				if (element['tooltip']) element.tooltip();
			}
		},
		loading: function(isLoading, view) {
			if (isLoading) {
				$("#filter-guests").find("button").prop("disabled", true).text("Loading...");
			}
			else {
				$("#filter-guests").find("button").prop("disabled", false).text("Filter Availability");
			}
		}
	});

	// listen for guest count change
	$("#filter-guests").submit(function(ev) {
		// prevent submit
		ev.preventDefault();

		// scroll to calendar
		_scrollToElement("#calendar");

		// trigger loading
		$("#calendar").fullCalendar("refetchEvents");
	});

	$("#guests").blur(function() {
		$(this).closest("form").submit();
	});
});
