
/* =========================================================================
 * Isotope filter bar — count badges + sliding glider (redesign, appended).
 * Self-contained: enhances the plugin's .ttp-isotope-buttons on EVERY isotope
 * layout (global nav style), not just isotope-free. Counts are read from the
 * loaded .isotope-item items in the .rt-content-loader row; the glider is a
 * span that slides to the active pill. Adds .rttm-iso-has-glider to the group so
 * the CSS can make the active button transparent (glider provides the gradient);
 * without JS the CSS keeps a gradient background on .selected as a fallback.
 * ========================================================================= */
(function ($) {
	'use strict';

	function positionGlider($group) {
		var $active = $group.children('button.selected').filter(':visible').first();
		var $glider = $group.children('.rttm-iso-glider');
		if (!$active.length || !$glider.length) {
			$glider.css('opacity', 0);
			return;
		}
		var btn = $active[0];
		$glider.css({
			left: btn.offsetLeft + 'px',
			top: btn.offsetTop + 'px',
			width: btn.offsetWidth + 'px',
			height: btn.offsetHeight + 'px',
			opacity: 1
		});
	}

	function initGroup($group) {
		if ($group.data('rttmIsoInit')) {
			positionGlider($group);
			return;
		}
		$group.data('rttmIsoInit', true);

		// `.rt-content-loader` is the row shared by EVERY isotope layout (it wraps both the
		// filter bar and the items), and `.isotope-item` is the item class every isotope layout
		// uses — so counting works on any isotope layout, not just isotope-free.
		var $row = $group.closest('.rt-content-loader');

		// 1. count badge per button (from the loaded items); hide empty non-"all" filters
		$group.children('button').each(function () {
			var $btn = $(this);
			var f = $btn.attr('data-filter');
			var count;
			try {
				count = (f === '*') ? $row.find('.isotope-item').length : $row.find('.isotope-item' + f).length;
			} catch (e) {
				count = 0;
			}
			if (count === 0 && f !== '*') {
				$btn.hide();
				return;
			}
			if (!$btn.children('.count').length) {
				$btn.append(' <span class="count">' + count + '</span>');
			}
		});

		// 2. glider element (behind the pills) + flag the group so CSS clears the button bg
		if (!$group.children('.rttm-iso-glider').length) {
			$group.prepend('<span class="rttm-iso-glider" aria-hidden="true"></span>');
		}
		$group.addClass('rttm-iso-has-glider');

		// 3. slide the glider when a pill is clicked (after the plugin toggles .selected)
		$group.on('click.rttmIso', 'button', function () {
			setTimeout(function () { positionGlider($group); }, 0);
		});

		positionGlider($group);
		// re-measure once more after fonts/layout settle
		setTimeout(function () { positionGlider($group); }, 250);
	}

	function initAll() {
		$('.rt-team-container .ttp-isotope-buttons').each(function () {
			initGroup($(this));
		});
	}

	$(window).on('load', initAll);
	$(document).ready(initAll);

	// Expose the initialiser for contexts that inject the filter-bar markup AFTER page load,
	// where the load/ready handlers above never fire — e.g. the admin shortcode preview, which
	// re-renders via AJAX on every form change. Safe to call repeatedly: initGroup is idempotent
	// per group (guards on .data('rttmIsoInit')), and the preview replaces the DOM each render so
	// a fresh group re-initialises cleanly.
	window.rttmIsoFilter = { init: initAll, initGroup: initGroup, position: positionGlider };

	// Elementor renders widgets asynchronously and, in the EDITOR, re-renders them on every
	// change WITHOUT a page reload — so the load/ready handlers above never fire for the fresh
	// DOM. Hook the widget's ready event so the counts + glider initialise each time it renders
	// (idempotent: initGroup guards with .data('rttmIsoInit'), and the editor recreates the DOM
	// so the guard is naturally clear on the new node). Runs on the real frontend too, harmless.
	$(window).on('elementor/frontend/init', function () {
		if (!window.elementorFrontend || !elementorFrontend.hooks || !elementorFrontend.hooks.addAction) {
			return;
		}
		elementorFrontend.hooks.addAction('frontend/element_ready/rttm-team-isotope.default', function ($scope) {
			$scope.find('.ttp-isotope-buttons').each(function () {
				initGroup($(this));
			});
		});
	});

	var resizeT;
	$(window).on('resize', function () {
		clearTimeout(resizeT);
		resizeT = setTimeout(function () {
			$('.rt-team-container .ttp-isotope-buttons').each(function () {
				positionGlider($(this));
			});
		}, 150);
	});
})(jQuery);
