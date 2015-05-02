jQuery(function($) {
	// create widget
	var w = new ArcticReserveWidget({
		gfsDomain: "reservations.theraftingcompany.com"
	});

	// fetch all upcoming trips
	var _resultLookup = {};
	w.fetch({
		query: "triptypeid = 1 LIMIT 100",
		group: false
	}, function (data) {
		// construct trip tables
		var html = '<table border="1"><tr> <th>Name</th> <th>Start</th> <th>End</th> <th>Action</th> </tr>';

		// add trips
		$.each(data, function (j, result) {
			var button = '';

			// store in result lookup
			_resultLookup[result.id] = result;

			// create button
			switch (result.action) {
				case 2:
					button = '<a href="' + result.reserve_url + '" class="reserve" data-resultid="' + result.id + '">Reserve</a>';
					break;
				case 1:
					button = '<a href="' + result.inquire_url + '" class="inquire" data-resultid="' + result.id + '">Inquire</a>';
					break;
			}

			html = html + '<tr> <td>' + escape(result.name) + '</td> <td>' + formatDateTime(result.start) + '</td> ' +
			'<td>' + formatDateTime(result.end) + '</td> <td>' + button + '</td> </tr>';
		});

		// close table
		html = html + '</table>';

		// add table to container
		$(html).appendTo("#results");

		// make reserve buttons clickable
		$("#results").on("click", ".reserve", function (ev) {
			var id = $(this).data("resultid");
			if (_resultLookup[id]) {
				// prevent default
				ev.preventDefault();

				// empty booking form
				$("#book").empty().append('<h2>Make Reservation</h2>');

				// make booking form
				w.buildBookFormElement(_resultLookup[id]).appendTo("#book");

				scrollToElement("#book");
			}
		});
	}, function (err) {
		// show error message
		$('<div class="error"><p><strong>Error.</strong> Unable to fetch trip dates.</p></div>').appendTo("#results");
	});


	// UTILITIES

	var _escapeMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;'
	};

	function _escapeReplace(tag) {
		return _escapeMap[tag] || tag;
	}

	function escape(str) {
		return str.replace(/[&<>]/g, _escapeReplace);
	}

	function scrollToElement(el) {
		$("html, body").animate({scrollTop: $(el).offset().top - 20});
	}

	// this is a very simple function for parsing and formatting dates
	// using a more robust library, like moment is strongly recommended
	var _reDateTime = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;

	function formatDateTime(str) {
		var match = _reDateTime.exec(str);

		if (!match)
			return '';

		var dt = parseInt(match[2], 10) + "/" + parseInt(match[3], 10) + "/" + parseInt(match[1], 10),
			hr = parseInt(match[4], 10), apm = "AM", tm;

		// just date
		if (0 === hr && "00" === match[5]) {
			return dt;
		}

		// format time
		if (12 < hr) {
			apm = "PM";
			hr = hr - 12;
		}
		else if (12 === hr) {
			apm = "PM";
		}
		else if (0 === hr) {
			hr = 12;
		}

		tm = hr + ":" + match[5] + " " + apm;

		return dt + " " + tm;
	}
});
