(function ($) {
	'use strict';
	var mdModalWrap = $("#tlp-modal");
	var elementThumbInstances = [];
	window.initTlpTeam = function () {
		$(".rt-team-container").each(function (index) {
			// The Elementor editor re-renders a widget on every edit, so this can run
			// many times over containers that are already wired up. Bind each one once,
			// otherwise every re-render stacks another set of handlers (and another
			// Swiper) on the same markup. On the frontend this runs once anyway.
			if ($(this).data("tlpTeamInit")) {
				return;
			}
			$(this).data("tlpTeamInit", true);

			var container = $(this),
				str = container.attr("data-layout"),
				popupBg = container.attr("data-popup-bg"),
				id = $.trim(container.attr('id')),
				randId = id.replace("rt-team-container-", ""),
				scID = $.trim(container.attr("data-sc-id")),
				mdPopup = $('.ttp-single-md-popup', container),
				multiPopup = $('.ttp-multi-popup', document),
				$default_order_by = $('.rt-order-by-action .order-by-default', container),
				$default_order = $('.rt-sort-order-action .rt-sort-order-action-arrow', container),
				$taxonomy_filter = $('.rt-filter-item-wrap.rt-tax-filter', container),
				$pagination_wrap = $('.rt-pagination-wrap', container),
				$loadmore = $('.rt-loadmore-action', container),
				$infinite = $('.rt-infinite-action', container),
				$page_prev_next = $('.rt-cb-page-prev-next', container),
				$page_numbers = $('.rt-page-numbers', container),
				html_loading = '<div class="rt-loading-overlay"></div><div class="rt-loading rt-ball-clip-rotate"><div></div></div>',
				preLoader = container.find('.ttp-pre-loader'),
				loader = container.find(".rt-content-loader"),
				contentLoader = container.children(".rt-row.rt-content-loader"),
				search_wrap = container.find(".rt-search-filter-wrap"),
				ttp_order = '',
				ttp_order_by = '',
				ttp_taxonomy = '',
				ttp_term = '',
				ttp_search = '',
				ttp_paged = 1,
				temp_total_pages = parseInt($pagination_wrap.attr('data-total-pages'), 10),
				ttp_total_pages = typeof (temp_total_pages) != 'undefined' && temp_total_pages != '' ? temp_total_pages : 1,
				temp_posts_per_page = parseInt($pagination_wrap.attr('data-posts-per-page'), 10),
				ttp_posta_per_page = typeof (temp_posts_per_page) != 'undefined' && temp_posts_per_page != '' ? temp_posts_per_page : 3,
				infinite_status = 0,
				paramsRequest = {},
				mIsotopeWrap = '',
				IsotopeWrap = '',
				// `ttp-masonry`, not `tpg-masonry` (that is The Post Grid's prefix, and no
				// markup here ever carries it). While this looked for the wrong class it
				// was always empty, so every `isMasonry.length` branch below was dead:
				// AJAX-loaded items were appended without ever being handed to Isotope,
				// so they kept `position: relative` and stacked on top of the absolutely
				// positioned cards already on the page.
				isMasonry = $('.rt-row.rt-content-loader.ttp-masonry', container),
				isIsotope = $(".tlp-team-isotope", container),
				IsoButton = $(".ttp-isotope-buttons", container),
				IsoDropDownFilter = $("select.isotope-dropdown-filter", container),
				isCarousel = $('.rt-carousel-holder', container),
				caroThumb = $('.rttm-carousel-main', container),
				isSpecial = $('.rt-special-wrapper', container),
				placeholder_loading = function () {
					if (loader.find('.rt-loading-overlay').length == 0) {
						loader.addClass('ttp-pre-loader');
						loader.append(html_loading);
					}
				},
				remove_placeholder_loading = function () {
					loader.find('.rt-loading').fadeOut(300);
					loader.removeClass('ttp-pre-loader');
					$loadmore.removeClass('rt-lm-loading');
					$page_numbers.removeClass('rt-lm-loading');
					$infinite.removeClass('rt-active-elm');
					search_wrap.find('input').prop("disabled", false);
				},
				check_query = function () {
					if ($taxonomy_filter.length > 0) {
						ttp_taxonomy = $taxonomy_filter.attr('data-taxonomy');
						var term;
						if ($taxonomy_filter.hasClass('rt-filter-button-wrap')) {
							term = $taxonomy_filter.find('.rt-filter-button-item.selected').attr('data-term');
						} else {
							term = $taxonomy_filter.find('.term-default').attr('data-term');
						}
						if (typeof (term) != 'undefined' && term != '') {
							ttp_term = term;
						}
					}
					if ($default_order_by.length > 0) {
						var order_by_param = $default_order_by.attr('data-order-by');
						if (typeof (order_by_param) != 'undefined' && order_by_param != '' && (order_by_param.toLowerCase())) {
							ttp_order_by = order_by_param;
						}
					}
					if ($default_order_by.length > 0) {
						var order_param = $default_order.attr('data-sort-order');
						if (typeof (order_param) != 'undefined' && order_param != '' && (order_param == 'DESC' || order_param == 'ASC')) {
							ttp_order = order_param;
						}
					}
					if (search_wrap.length > 0) {
						ttp_search = $.trim(search_wrap.find('input').val());
					}
					paramsRequest = {
						'ttp_post__not_in': [20],
						'scID': scID,
						'order': ttp_order,
						'order_by': ttp_order_by,
						'taxonomy': ttp_taxonomy,
						'term': ttp_term,
						'paged': ttp_paged,
						'action': 'ttp_Layout_Ajax_Action',
						'search': ttp_search,
						'tlp_nonce': ttp.nonce
					};
				},
				infinite_scroll = function () {
					if (infinite_status == 1 || $infinite.hasClass('rt-hidden-elm') || $pagination_wrap.length == 0) {
						return;
					}
					var ajaxVisible = $pagination_wrap.offset().top,
						ajaxScrollTop = $(window).scrollTop() + $(window).height();

					if (ajaxVisible <= (ajaxScrollTop) && (ajaxVisible + $(window).height()) > ajaxScrollTop) {
						infinite_status = 1; //stop inifite scroll
						ttp_paged = ttp_paged + 1;
						$infinite.addClass('rt-active-elm');
						ajax_action(true, true);
					}
				},
				generateData = function (number) {
					var result = [];
					for (var i = 1; i < number + 1; i++) {
						result.push(i);
					}
					return result;
				},
				createPagination = function () {
					if ($page_numbers.length > 0) {
						$page_numbers.pagination({
							dataSource: generateData(ttp_total_pages * parseFloat(ttp_posta_per_page)),
							pageSize: parseFloat(ttp_posta_per_page),
							autoHidePrevious: true,
							autoHideNext: true,
							prevText: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>',
							nextText: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>'
						});
						$page_numbers.addHook('beforePaging', function (pagination) {
							infinite_status = 1;
							ttp_paged = pagination;
							$page_numbers.addClass('rt-lm-loading');
							$page_numbers.pagination('disable');
							ajax_action(true, false);
						});
						if (ttp_total_pages <= 1) {
							$page_numbers.addClass('rt-hidden-elm');
						} else {
							$page_numbers.removeClass('rt-hidden-elm');
						}
					}
				},
				getItemsArray = function (contentLoader) {
					return contentLoader.find('.rt-grid-item').map(function () {
						var _self = $(this);
						if (_self.is(":visible")) {
							return _self.attr("data-id");
						}
					}).get();
				},
				animateAction = function () {
					var $postItem = $('.rt-grid-item:not(.rt-ready-animation, .isotope-item)', container);
					$postItem.addClass('rt-ready-animation animated fadeIn');
				},
				ajax_action = function (page_request, append) {
					page_request = page_request || false;
					append = append || false;
					if (!page_request) {
						ttp_paged = 1;
					}
					check_query();
					if (page_request == true && ttp_total_pages > 1 && paramsRequest.paged > ttp_total_pages) {
						remove_placeholder_loading();
						return;
					}
					$.ajax({
						url: ttp.ajaxurl,
						type: 'POST',
						data: paramsRequest,
						cache: false,
						beforeSend: function () {
							placeholder_loading();
						},
						success: function (data) {
							if (!data.error) {
								ttp_paged = data.paged;
								ttp_total_pages = data.total_pages;
								if (data.paged >= ttp_total_pages) {
									if ($loadmore.length) {
										$loadmore.addClass('rt-hidden-elm');
									}
									if ($infinite.length) {
										infinite_status = 1;
										$infinite.addClass('rt-hidden-elm');
									}
									if ($page_prev_next.length) {
										if (!page_request) {
											$page_prev_next.addClass('rt-hidden-elm');
										} else {
											$page_prev_next.find('.rt-cb-prev-btn').removeClass('rt-disabled');
											$page_prev_next.find('.rt-cb-next-btn').addClass('rt-disabled');
										}
									}
								} else {
									if ($loadmore.length) {
										$loadmore.removeClass('rt-hidden-elm');
									}
									if ($infinite.length) {
										infinite_status = 0;
										$infinite.removeClass('rt-hidden-elm');
									}
									if ($page_prev_next.length) {
										if (!page_request) {
											$page_prev_next.removeClass('rt-hidden-elm');
										} else {
											if (data.paged == 1) {
												$page_prev_next.find('.rt-cb-prev-btn').addClass('rt-disabled');
												$page_prev_next.find('.rt-cb-next-btn').removeClass('rt-disabled');
											} else {
												$page_prev_next.find('.rt-cb-prev-btn').removeClass('rt-disabled');
												$page_prev_next.find('.rt-cb-next-btn').removeClass('rt-disabled');
											}
										}
									}
								}
								if (append) {
									if (isIsotope.length) {
										// Parse ONCE — append(html) and isotope('appended', html)
										// each parse the string separately, so Isotope was
										// registering a detached copy of the new cards.
										var $isoAppended = $(data.data);

										// No reloadItems()/updateSortData(): they rebuild every Item
										// and discard the positions `appended` just assigned, so the
										// arrange that followed animated each new card in from the
										// grid's origin. They are only for sort/filter data.
										//
										// Put the markup in the DOM but hand it to Isotope only once
										// the images are in — registering earlier measures cards
										// that have not finished loading, so they land at the wrong
										// y and then visibly slide when the arrange corrects them.
										// transitionDuration 0 keeps that correction from animating.
										IsotopeWrap.append($isoAppended);
										IsotopeWrap.imagesLoaded(function () {
											preFunction();
											IsotopeWrap.isotope({ transitionDuration: 0 });
											IsotopeWrap.isotope('appended', $isoAppended);
											IsotopeWrap.isotope();
											IsotopeWrap.isotope({ transitionDuration: '0.4s' });

											// preFunction() -> HeightResize() pins every
											// .even-grid-item to the tallest card, from its OWN
											// async imagesLoaded pass on the row. That lands after
											// the arrange above, so if the cards just loaded are
											// taller than the ones already there, the rows stay
											// spaced by the old height and the new row overlaps the
											// one above it. Registering on the same row queues this
											// settle behind HeightResize's write.
											contentLoader.imagesLoaded(function () {
												IsotopeWrap.isotope({ transitionDuration: 0 });
												IsotopeWrap.isotope();
												IsotopeWrap.isotope({ transitionDuration: '0.4s' });
											});
										});
									} else if (isMasonry.length) {
										// Parse the markup ONCE. `append(html)` and
										// `isotope('appended', html)` each parse the string
										// separately, so Isotope would end up tracking a second,
										// detached copy instead of the cards actually on screen.
										var $appended = $(data.data);

										// No reloadItems()/updateSortData() here. `appended`
										// already places the new cards instantly, in their slot;
										// reloadItems() rebuilds every Item and throws those
										// positions away, so the relayout below animated each new
										// card in from the grid's top-left corner. Those two are
										// only needed when sort/filter data changes.
										mIsotopeWrap.append($appended).isotope('appended', $appended);
										mIsotopeWrap.imagesLoaded(function () {
											mIsotopeWrap.isotope('layout');
										});
									} else if (isSpecial.length) {
										// Special paginates the thumbnail grid only - the stage
										// and the feature panel have to survive the swap.
										container.find('.special-items-wrapper').append(data.data);
										syncSpecialActive();
									} else {
										contentLoader.append(data.data);
									}
								} else {
									if (isMasonry.length) {
										// Filter / sort / search / AJAX pagination replace the
										// whole row. reloadItems() is REQUIRED here (the exact
										// opposite of the append case above): Isotope still holds
										// Item objects for the elements html() just destroyed, so
										// laying out without it positions detached nodes, sets the
										// container to height 0 and leaves the new cards in normal
										// flow spilling over whatever follows on the page.
										// `.isotope()` with no args re-runs arrange, which is what
										// actually measures and places items — `isotope('layout')`
										// alone leaves item.size undefined and collapses the row.
										mIsotopeWrap.html(data.data);
										mIsotopeWrap.imagesLoaded(function () {
											mIsotopeWrap.isotope('reloadItems').isotope();
										});
									} else if (isSpecial.length) {
										container.find('.special-items-wrapper').html(data.data);
										syncSpecialActive();
									} else {
										contentLoader.html(data.data);
									}
								}
								contentLoader.imagesLoaded(function () {
									preFunction();
									remove_placeholder_loading();
								});
								if (!page_request) {
									createPagination();
								}
								animateAction();
							} else {
								remove_placeholder_loading();
							}
						},
						error: function () {
							remove_placeholder_loading();
						}
					});
					if ($('.paginationjs-pages .paginationjs-page', $page_numbers).length > 0) {
						$page_numbers.pagination('enable');
					}
				},
				syncSpecialActive = function () {
					var activeId = container.find('#special-selected-wrapper').attr('data-id'),
						cells = container.find('.special-items-wrapper .rt-grid-item');
					cells.removeClass('selected');
					if (activeId) {
						cells.filter('[data-id="' + activeId + '"]').addClass('selected');
					}
				},
				scrollToTopMember = function () {
										// below 992 the stage stacks with the details BELOW the thumbnails,
					// so bring the freshly loaded profile into view after a tap
					if ($(window).width() < 992) {
						$('html, body').animate({
							scrollTop: $('#special-selected-wrapper').offset().top - 35
						}, 200);
					}
				};

			switch ($pagination_wrap.attr('data-type')) {
				case 'load_more':
					$loadmore.on('click', function () {
						$(this).addClass('rt-lm-loading');
						ttp_paged = ttp_paged + 1;
						ajax_action(true, true);
					});
					break;
				case 'pagination_ajax':
					createPagination();
					break;
				case 'pagination':
					break;
				case 'load_on_scroll':
					$(window).on('scroll load', function () {
						infinite_scroll();
					});
					break;
				case 'page_prev_next':
					if (ttp_paged == 1) {
						$page_prev_next.find('.rt-cb-prev-btn').addClass('rt-disabled');
					}
					if (ttp_paged == ttp_total_pages) {
						$page_prev_next.find('.rt-cb-next-btn').addClass('rt-disabled');
					}
					if (ttp_total_pages == 1) {
						$page_prev_next.addClass('rt-hidden-elm');
					}
					break;
			}

			if (str) {
				var qsRegex, buttonFilter;
				if (preLoader.find('.rt-loading-overlay').length == 0) {
					preLoader.append(html_loading);
				}

				if (isCarousel.length) {
					isCarousel.imagesLoaded(function () {
						if (str === "carousel10") {
							var carouselOptions = caroThumb.data('options');

							RTElementThumbCarousel(container, carouselOptions, elementThumbInstances, index);
							remove_placeholder_loading();
						} else {
							rtSliderInit($);
						}

						$(document).on('rttm_slider_loaded', function () {
							remove_placeholder_loading();
						});
					});
				} else if (isIsotope.length) {
					if (!buttonFilter) {
						buttonFilter = IsoButton.find('button.selected').data('filter');
					}
					IsotopeWrap = isIsotope.imagesLoaded(function () {
						preFunction();
						// Grid Style. Isotope defaults to masonry AND passing a `masonry`
						// option pins it there, so the hardcoded option below used to make
						// "Even" render as masonry on every isotope layout. `ttp-even` is
						// written on the row by the renderer; its absence means masonry
						// (the shortcode path adds no class at all in that case).
						IsotopeWrap.isotope($.extend({
							itemSelector: '.isotope-item',
							filter: function () {
								return buttonFilter ? $(this).is(buttonFilter) : true;
							}
						}, isIsotope.closest('.rt-row').hasClass('ttp-even')
							? { layoutMode: 'fitRows' }
							: { layoutMode: 'masonry', masonry: { columnWidth: '.isotope-item' } }));
						setTimeout(function () {
							IsotopeWrap.isotope();
							remove_placeholder_loading();
						}, 100);
					});

					IsoButton.on('click touchstart', 'button', function (e) {
						e.preventDefault();
						buttonFilter = $(this).attr('data-filter');
						IsotopeWrap.isotope();
						$(this).parent().find('.selected').removeClass('selected');
						$(this).addClass('selected');
					});

				} else if (isSpecial.length) {
					// Every member stays in the grid; clicking one promotes it to the
					// active thumbnail and loads that profile into the feature panel.
					target = container.find('#special-selected-wrapper');

					var loadSpecialMember = function (id) {
						if (!id) {
							remove_placeholder_loading();
							return;
						}
						$.ajax({
							url: ttp.ajaxurl,
							type: 'POST',
							data: { memberId: id, scID: scID, action: 'rtGetSpecialLayoutData', tlp_nonce: ttp.nonce },
							cache: false,
							beforeSend: function () {
								placeholder_loading();
							},
							success: function (data) {
								if (!data.error) {
									target.attr('data-id', id);
									target.html(data.data);
								}
								remove_placeholder_loading();
							},
							error: function () {
								remove_placeholder_loading();
							}
						});
					};

					var activeCell = container.find('.special-items-wrapper .rt-grid-item.selected').first();
					if (!activeCell.length) {
						activeCell = container.find('.special-items-wrapper .rt-grid-item').first().addClass('selected');
					}
					loadSpecialMember(activeCell.attr('data-id'));

					container.on('click', '.special-items-wrapper .single-team-item.image-wrapper', function (e) {
						e.preventDefault();
						var self = $(this),
							cell = self.closest('.rt-grid-item'),
							id = self.attr('data-id');

						if (!id || cell.hasClass('selected')) {
							return;
						}

						container.find('.special-items-wrapper .rt-grid-item.selected').removeClass('selected');
						cell.addClass('selected');
						loadSpecialMember(id);
						scrollToTopMember();
					});

				} else if (container.find('.rt-row.rt-content-loader.ttp-masonry').length) {
					var masonryTarget = $('.rt-row.rt-content-loader.ttp-masonry', container);
					mIsotopeWrap = masonryTarget.imagesLoaded(function () {
						preFunction();
						mIsotopeWrap.isotope({
							itemSelector: '.masonry-grid-item',
							masonry: { columnWidth: '.masonry-grid-item' },
							// Fade only. Isotope reveals appended items from
							// `scale(0.001)` by default, which makes a card loaded by
							// "Load more" balloon out of the centre of its slot on top of
							// the fadeIn the cards already do for themselves. Overriding
							// both styles drops the scale and leaves the card to simply
							// appear where it belongs.
							hiddenStyle: { opacity: 0 },
							visibleStyle: { opacity: 1 }
						});
						remove_placeholder_loading();
					});
				} else {
					var target = $('.rt-row.rt-content-loader.ttp-masonry', container);
					target.imagesLoaded(function () {
						preFunction();
						remove_placeholder_loading();
					});
				}
			}

			$('#' + id).on('click', '.rt-search-filter-wrap .rt-action', function (e) {
				search_wrap.find('input').prop("disabled", true);
				ajax_action();
			});
			$('#' + id).on('keypress', '.rt-search-filter-wrap .rt-search-input', function (e) {
				if (e.which == 13) {
					search_wrap.find('input').prop("disabled", true);
					ajax_action();
				}
			});
			$('#' + id).on('click', '.rt-filter-dropdown-wrap', function (event) {
				var self = $(this);
				self.toggleClass('active-dropdown');
			});// Dropdown click
			$('#' + id).on('click', '.term-dropdown-item', function (event) {
				$loadmore.addClass('rt-lm-loading');
				var $this_item = $(this),
					default_target = $taxonomy_filter.find('.rt-filter-dropdown-default'),
					old_param = default_target.attr('data-term'),
					old_text = default_target.find('.rt-text').html();
				$this_item.parents('.rt-filter-dropdown-wrap').removeClass('active-dropdown');
				$this_item.parents('.rt-filter-dropdown-wrap').toggleClass('active-dropdown');
				default_target.attr('data-term', $this_item.attr('data-term'));
				default_target.find('.rt-text').html($this_item.html());
				$this_item.attr('data-term', old_param);
				$this_item.html(old_text);
				ajax_action();
			});//term
			$('#' + id).on('click', '.order-by-dropdown-item', function (event) {
				$loadmore.addClass('rt-lm-loading');
				var $this_item = $(this),
					old_param = $default_order_by.attr('data-order-by'),
					old_text = $default_order_by.find('.rt-text-order-by').html();

				$this_item.parents('.rt-order-by-action').removeClass('active-dropdown');
				$this_item.parents('.rt-order-by-action').toggleClass('active-dropdown');
				$default_order_by.attr('data-order-by', $this_item.attr('data-order-by'));
				$default_order_by.find('.rt-text-order-by').html($this_item.html());
				$this_item.attr('data-order-by', old_param);
				$this_item.html(old_text);
				ajax_action();
			});//Order By

			//Sort Order
			$('#' + id).on('click', '.rt-sort-order-action', function (event) {
				$loadmore.addClass('rt-lm-loading');
				var $this_item = $(this),
					$sort_order_elm = $('.rt-sort-order-action-arrow', $this_item),
					sort_order_param = $sort_order_elm.attr('data-sort-order');
				if (typeof (sort_order_param) != 'undefined' && sort_order_param.toLowerCase() == 'desc') {
					$default_order.attr('data-sort-order', 'ASC');
				} else {
					$default_order.attr('data-sort-order', 'DESC');
				}
				ajax_action();
			});//Sort Order

			$taxonomy_filter.on('click touchstart', '.rt-filter-button-item', function () {
				var self = $(this);
				self.parents('.rt-filter-button-wrap').find('.rt-filter-button-item').removeClass('selected');
				self.addClass('selected');
				ajax_action();
			});

			$page_prev_next.on('click', '.rt-cb-prev-btn', function (event) {
				if (ttp_paged <= 1) {
					return;
				}
				ttp_paged = ttp_paged - 1;
				ajax_action(true, false);
			});
			$page_prev_next.on('click', '.rt-cb-next-btn', function (event) {
				if (ttp_paged >= ttp_total_pages) {
					return;
				}
				ttp_paged = ttp_paged + 1;
				ajax_action(true, false);
			});

			// md Popup
			// The redesigned single popup can page between MEMBERS, so the open call is
			// a named function and the member order is read from the triggers already in
			// this container (deduped — a card usually has several triggers: the photo,
			// the name and the Read More link).
			var singleMemberIds = [];

			function ttpCollectSingleMembers() {
				singleMemberIds = [];
				container.find('.ttp-single-md-popup[data-id]').each(function () {
					var mid = String($(this).attr('data-id'));
					if (mid && singleMemberIds.indexOf(mid) === -1) {
						singleMemberIds.push(mid);
					}
				});
			}

			function ttpOpenSinglePopup(id, isStep) {
				id = String(id);
				var data = "action=tlp_md_popup_single&id=" + id + "&tlp_nonce=" + ttp.nonce;

				$.ajax({
					type: "post",
					url: ttp.ajaxurl,
					data: data,
					beforeSend: function () {
						mdModalWrap.addClass("tlp-modal-" + scID);
						mdModalWrap.addClass('md-show');
						// Paging between members must NOT tear the card out: `.tlp-md-loading` is
					// absolutely positioned, so replacing the holder with it leaves the shell
					// with no in-flow content and the modal collapses to height 0 (measured:
					// 583 -> 0 -> 538 across one click). Keep the outgoing card, drop
					// `is-ready` so the existing CSS fades it to the spinner, and pin the
					// height so it cannot collapse while the next member loads.
					var $pop = mdModalWrap.find('.rttm-pop');
					if (isStep && $pop.length) {
						ttpPopLockHeight();
						$pop.removeClass('is-ready');
					} else {
						mdModalWrap.find('.tlp-md-content-holder').html('<div class="tlp-md-loading">Loading...</div>');
					}
						// The visible card is `.rttm-pop` INSIDE .md-content, so painting the
						// holder put the colour behind an opaque surface and the control
						// appeared to do nothing. Set the surface token on the shell instead
						// — every surface the popup owns reads it.
						if (popupBg) {
							mdModalWrap[0].style.setProperty('--rttm-pop-surface', popupBg);
						}
						// Carry the opening container's Primary Color onto the shared modal so
						// the popup's pills, icon chips, skill bars and buttons use the same
						// accent as the cards instead of the design's fallback blue.
						var rttmPrim = (getComputedStyle(container[0]).getPropertyValue('--l1-primary') || '').trim();
						if (rttmPrim) {
							mdModalWrap[0].style.setProperty('--rttm-pop-primary', rttmPrim);
						} else {
							mdModalWrap[0].style.removeProperty('--rttm-pop-primary');
						}

					},
					success: function (data) {
						mdModalWrap.find('.tlp-md-content-holder').html(data.data);
						// The modal is shared by every shortcode on the page, so the member
						// list travels ON it rather than in a per-container closure.
						mdModalWrap.data('rttmMember', id);
						mdModalWrap.data('rttmMembers', singleMemberIds);
						ttpSyncPager();
						ttpRefreshGallery();
						ttpPopReady(ttpPopReleaseHeight);
					},
					error: function (e) {
						console.log(e);
					}
				});
			}

			// Hide the pager when there is nothing to page to; otherwise it is a wrap-around
			// so neither button is ever disabled.
			// Reveal the card as ONE piece, only once its photos have decoded. The AJAX
			// lands ~60ms after the open animation starts, so without this the modal
			// scales in empty and the content then the image snap in mid-flight.
			// The 8s guard means a blocked image can never leave the card hidden.
			function ttpPopShell() {
				return mdModalWrap.find('.md-content').first();
			}

			// Pin the shell to its current height so the swap cannot collapse the modal.
			function ttpPopLockHeight() {
				var $el = ttpPopShell();
				if ($el.length) {
					$el.css('height', $el.outerHeight() + 'px');
				}
			}

			// Ease from the pinned height to whatever the new member needs, then hand the
			// box back to auto so nothing is left inline-styled.
			function ttpPopReleaseHeight() {
				var $el = ttpPopShell();
				if (!$el.length || !$el[0].style.height) {
					return;
				}
				var from = $el[0].style.height;
				$el.css('height', '');
				var to = $el.outerHeight();
				$el.css('height', from);
				void $el[0].offsetWidth;
				$el.css({ transition: 'height .35s cubic-bezier(.4, 0, .2, 1)', height: to + 'px' });
				setTimeout(function () {
					$el.css({ transition: '', height: '' });
				}, 400);
			}

			function ttpPopReady(onReady) {
				var pop = mdModalWrap.find('.rttm-pop');
				if (!pop.length) {
					return;
				}
				pop.removeClass('is-ready');

				var imgs = pop.find('.rttm-pop-gallery img').get(),
					pending = imgs.length,
					reveal = function () {
						// Force a style flush, then flip on the next frame. Adding the class
						// in the same frame as the insert lets the browser collapse the
						// before/after states into one, so the cross-fade never runs and the
						// card just appears — which is what made the open look snappy/janky.
						void pop[0].offsetWidth;
						window.requestAnimationFrame(function () {
							pop.addClass('is-ready');
							if (typeof onReady === 'function') {
								onReady();
							}
						});
					};

				if (!pending) {
					reveal();
					return;
				}

				var settle = function () {
					pending -= 1;
					if (pending <= 0) {
						reveal();
					}
				};

				$.each(imgs, function (i, img) {
					if (img.complete && img.naturalWidth) {
						settle();
					} else {
						$(img).one('load error', settle);
					}
				});

				setTimeout(reveal, 8000);
			}

			// Swiper measures the gallery while the modal is still mid open-transition
			// (scaled down), so slide widths come out wrong and two part-slides show.
			// Re-measure once the transition has settled.
			function ttpRefreshGallery() {
				setTimeout(function () {
					mdModalWrap.find('.rttm-pop-gallery .swiper').each(function () {
						if (this.swiper) {
							this.swiper.update();
						}
					});
				}, 450);
			}

			function ttpSyncPager() {
				var pager = mdModalWrap.find('.rttm-pop-pager');
				if (!pager.length) {
					return;
				}
				if (singleMemberIds.length < 2) {
					pager.hide();
				} else {
					pager.show();
				}
			}

			function ttpStepMember(step) {
				var ids = mdModalWrap.data('rttmMembers') || [];
				var open = mdModalWrap.data('rttmOpen');
				if (ids.length < 2 || typeof open !== 'function') {
					return;
				}
				var cur = String(mdModalWrap.data('rttmMember') || '');
				var i = ids.indexOf(cur);
				if (i === -1) {
					i = 0;
				}
				open(ids[(i + step + ids.length) % ids.length], true);
			}

			$(document).on('click', '.ttp-single-md-popup', function (e) {
				// This handler is bound once PER CONTAINER, so on a page with more than
				// one shortcode every copy fires. Only the container that actually owns
				// the clicked trigger may respond — otherwise the last copy to run wins
				// and overwrites the member list with its own (often empty) one.
				if (!container.has(this).length) {
					return;
				}
				e.preventDefault();
				ttpCollectSingleMembers();
				// Hand the pager THIS container's opener, so paging keeps using the
				// scID / popup background of the shortcode that was actually clicked.
				mdModalWrap.data('rttmOpen', ttpOpenSinglePopup);
				ttpOpenSinglePopup($(this).attr("data-id"));
				return false;
			});

			// Bind the pager ONCE. `#tlp-modal` is a single shared element, so binding
			// inside this per-container loop would fire the handler once per shortcode
			// on the page and race between their member lists.
			if (!mdModalWrap.data('rttmPagerBound')) {
				mdModalWrap.data('rttmPagerBound', true);
				mdModalWrap.on('click', '.rttm-pop-prev', function (e) {
					e.preventDefault();
					ttpStepMember(-1);
				});
				mdModalWrap.on('click', '.rttm-pop-next', function (e) {
					e.preventDefault();
					ttpStepMember(1);
				});
			}

			// ---------- smart popup ----------
			// A right-hand drawer over a blurred backdrop. The shell is built once per
			// open and survives every member step; only `.rt-smart-modal-main-content` is
			// swapped. The state the stepper needs travels ON the shell via .data(), so
			// the delegated handlers bound at build time keep working after each swap.
			$(document).on('click', '.ttp-smart-popup', function (e) {
				e.preventDefault();

				var self = $(this);

				// Bound inside the per-container .each(), so on a page with two widgets
				// both copies run; without this the last one wins and opens the drawer
				// with the wrong member list.
				if (!container.has(this).length) {
					return false;
				}

				var current = String(self.attr('data-id')),
					contentLoader = $('.rt-row.rt-content-loader', container),
					itemArray = $.map(getItemsArray(contentLoader), function (v) { return String(v); }),
					$wrap = ttpSpBuild(scID);

				$wrap.data('rttmList', itemArray);

				// The drawer paints every surface it owns from --rttm-pop-surface, so the
				// token covers the panel and the scrolling body together. The gradient bar
				// is deliberately left alone — it follows Primary Color, exactly as the
				// Elementor path keeps PopUp Header Background separate from PopUp
				// Background.
				if (popupBg) {
					$wrap[0].style.setProperty('--rttm-pop-surface', popupBg);
				}

				// Carry the opening container's Primary Color onto the drawer — the shell
				// lives outside the widget wrapper, so no per-widget selector reaches it.
				var rttmPrim = (getComputedStyle(container[0]).getPropertyValue('--l1-primary') || '').trim();
				if (rttmPrim) {
					$wrap[0].style.setProperty('--rttm-pop-primary', rttmPrim);
				} else {
					$wrap[0].style.removeProperty('--rttm-pop-primary');
				}

				ttpSpLoad($wrap, current, false);

				return false;
			});

			// ---------- multiple popup ----------
			// One shared viewer element; the state it needs to step between members
			// travels ON it via .data(), so the delegated handlers built with the shell
			// keep working after every panel swap.
			$(document).on('click', '.ttp-multi-popup', function () {
				var self = $(this);

				// `.ttp-multi-popup` is bound inside the per-container .each(), so on a
				// page with two widgets both copies run; without this the last one wins
				// and opens the viewer with the wrong member list.
				if (!container.has(this).length) {
					return;
				}

				var current = String(self.attr('data-id')),
					contentLoader = self.parents('.rt-team-container').children('.rt-row.rt-content-loader'),
					itemArray = $.map(getItemsArray(contentLoader), function (v) { return String(v); }),
					$wrap = ttpMpopBuild();

				$wrap.addClass('tlp-popup-wrap-' + scID);
				$wrap.data('rttmList', itemArray);
				$wrap.data('rttmContainer', container);

				// This used to paint only the top BAR, so the viewer's stage — the surface
				// the control is named for — stayed white. The token covers the stage and
				// the thumbnail strip; the bar follows Primary Color.
				if (popupBg) {
					$wrap[0].style.setProperty('--rttm-pop-surface', popupBg);
				}

				// Carry the opening container's Primary Color onto the viewer, exactly as
				// the single popup does — the shell lives outside the widget wrapper, so
				// no per-widget selector can reach it.
				var rttmPrim = (getComputedStyle(container[0]).getPropertyValue('--l1-primary') || '').trim();
				if (rttmPrim) {
					$wrap[0].style.setProperty('--rttm-pop-primary', rttmPrim);
				} else {
					$wrap[0].style.removeProperty('--rttm-pop-primary');
				}

				ttpMpopLoad($wrap, current, 0);

				return false;
			});


		});
	};
	initTlpTeam();
	function preFunction() {
		HeightResize();
	}

	function equalHeight4Layout4() {
		var $maxH = $(".rt-row.rt-content-loader.layout4 .layout4item").height();
		$(".rt-row.rt-content-loader.layout4 .layout4item .layoutInner .rt-img-holder img,.rt-row.rt-content-loader.layout4 .layout4item .layoutInner.layoutInner-content").height($maxH + "px");
	}

    function HeightResize() {
        if($(".rt-team-container[data-layout*='layout']").length > 0) {
            return;
        }

        var wWidth = $(window).width();
        $(".rt-team-container").each(function () {
            var self = $(this),
                dCol = self.data('desktop-col'),
                tCol = self.data('tab-col'),
                mCol = self.data('mobile-col'),
                target = $(this).find('.rt-row.rt-content-loader.ttp-even');
            if ((wWidth >= 992 && dCol > 1) || (wWidth >= 768 && tCol > 1) || (wWidth < 768 && mCol > 1)) {
                target.imagesLoaded(function () {
                    var tlpMaxH = 0;
                    target.find('.even-grid-item').height('auto');
                    target.find('.even-grid-item').each(function () {
                        var $thisH = $(this).outerHeight();
                        if ($thisH > tlpMaxH) {
                            tlpMaxH = $thisH;
                        }
                    });
                    target.find('.even-grid-item').height(tlpMaxH + "px");
                });
            } else {
                target.find('.even-grid-item').height('auto');
            }

        });
        if ($(".rt-row.rt-content-loader.layout4").length) {
            equalHeight4Layout4();
        }
    }

	/* ---------- smart popup: the right-hand drawer ----------
	   The shell keeps every legacy hook class (.rt-smart-modal-main,
	   .rt-smart-modal-header, .rt-smart-modal-nav, .rt-smart-nav-item,
	   .rt-smart-modal-close, .rt-smart-modal, .rt-smart-modal-main-content-wrapper)
	   because user Style controls are wired to them — see
	   ElementorFilters::colorControls() "PopUp Colors" and the smart-modal arms in
	   Fns::layoutStyleGenerator() and templates/sc-css.php. */

	function ttpSpClose() {
		var $wrap = $('#rt-smart-modal-container');
		if (!$wrap.length) {
			return;
		}
		$('html').removeClass('rt-smart-modal-on');
		$wrap.removeClass('open');
		setTimeout(function () {
			$wrap.remove();
		}, 450);
	}

	function ttpSpBuild(scID) {
		$('#rt-smart-modal-container').remove();

		var html =
			'<div id="rt-smart-modal-container" class="rt-modal-' + scID + ' rttm-sp">' +
			'<div class="rt-smart-modal-main">' +
			'<div class="rt-smart-modal-header">' +
			'<span class="rt-smart-modal-nav">' +
			'<a href="#" class="rt-smart-nav-left rt-smart-nav-item" aria-label="Previous member"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>' +
			'<a href="#" class="rt-smart-nav-right rt-smart-nav-item" aria-label="Next member"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>' +
			'</span>' +
			'<a href="#" class="rt-smart-modal-close" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></a>' +
			'</div>' +
			'<div class="rt-smart-modal"><div class="rt-smart-modal-main-content-wrapper"></div></div>' +
			'</div>' +
			'</div>';

		$('body').append(html);
		$('html').addClass('rt-smart-modal-on');

		var $wrap = $('#rt-smart-modal-container');

		// Adding the reveal class in the same frame as the insert collapses both states
		// and the drawer just appears; force a style flush first.
		void $wrap[0].offsetWidth;
		requestAnimationFrame(function () {
			$wrap.addClass('open');
		});

		$wrap.on('click', '.rt-smart-modal-close', function (e) {
			e.preventDefault();
			ttpSpClose();
			return false;
		});
		$wrap.on('click', '.rt-smart-nav-right', function (e) {
			e.preventDefault();
			ttpSpStep($wrap, 1);
			return false;
		});
		$wrap.on('click', '.rt-smart-nav-left', function (e) {
			e.preventDefault();
			ttpSpStep($wrap, -1);
			return false;
		});
		$wrap.on('click', '.rttm-sp-garrow', function () {
			ttpSpShot($wrap, $(this).hasClass('next') ? 1 : -1, false);
			return false;
		});
		$wrap.on('click', '.rttm-sp-dot', function () {
			ttpSpShot($wrap, parseInt($(this).attr('data-i'), 10), true);
			return false;
		});
		// clicking the dimmed backdrop closes; clicks inside the drawer must not
		$wrap.on('click', function (e) {
			if (e.target === this) {
				ttpSpClose();
			}
		});

		return $wrap;
	}

	/* hero slider — a plain cross-fade, so nothing has to measure a drawer that is
	   still sliding in */
	function ttpSpShot($wrap, value, absolute) {
		var $slides = $wrap.find('.rttm-sp-slide'),
			$dots = $wrap.find('.rttm-sp-dot');

		if (!$slides.length) {
			return;
		}

		var cur = $slides.index($slides.filter('.is-active'));
		if (cur < 0) {
			cur = 0;
		}

		var i = absolute ? value : cur + value;
		i = ((i % $slides.length) + $slides.length) % $slides.length;

		$slides.removeClass('is-active').eq(i).addClass('is-active');
		$dots.removeClass('is-active').eq(i).addClass('is-active');
	}

	function ttpSpStep($wrap, delta) {
		var list = $wrap.data('rttmList') || [],
			cur = String($wrap.data('rttmCurrent'));

		if (list.length < 2) {
			return;
		}

		var i = $.inArray(cur, list);
		if (i < 0) {
			i = 0;
		}
		i = (i + delta + list.length) % list.length;

		ttpSpLoad($wrap, list[i], true);
	}

	/* Loads one member. `swap` is false on first open (spinner) and true when stepping,
	   which fades the outgoing panel out and the new one back in. */
	function ttpSpLoad($wrap, id, swap) {
		var $body = $wrap.find('.rt-smart-modal'),
			$holder = $wrap.find('.rt-smart-modal-main-content-wrapper'),
			$old = $holder.find('.rttm-sp-panel'),
			list = $wrap.data('rttmList') || [];

		$wrap.data('rttmCurrent', String(id));
		// hide the stepper when there is only one member to step to
		$wrap.find('.rt-smart-modal-nav').css('display', list.length > 1 ? '' : 'none');

		var data = {
			action: 'tlp_team_smart_popup',
			id: id
		};
		data[ttp.nonceID] = ttp.nonce;

		var fire = function () {
			$.ajax({
				type: 'post',
				url: ttp.ajaxurl,
				data: data,
				beforeSend: function () {
					if (!swap) {
						$wrap.addClass('loading');
						$holder.html('<span class="rt-spinner"></span>');
					}
				},
				success: function (response) {
					$wrap.removeClass('loading').addClass('ready');
					$holder.html(response.data);
					$body.scrollTop(0);

					var $panel = $holder.find('.rttm-sp-panel');
					if (swap && $panel.length) {
						$panel.addClass('is-swapping');
						void $panel[0].offsetWidth;
						requestAnimationFrame(function () {
							$panel.removeClass('is-swapping');
						});
					}

					ttpSpSkills($wrap);
				},
				error: function (e) {
					console.log(e);
					$wrap.removeClass('loading');
					$holder.html('<p>Loading error!!!</p>');
				}
			});
		};

		if (swap && $old.length) {
			$old.addClass('is-swapping');
			setTimeout(fire, 200);
		} else {
			fire();
		}
	}

	/* the skill bars animate from 0 to their data-progress-animation width */
	function ttpSpSkills($wrap) {
		$('.tlp-tooltip', $wrap).rtTooltip();
		$('.tlp-team-skill', $wrap).find('.fill').css('width', '0%');
		$('.tlp-team-skill .fill', $wrap).each(function () {
			var k = 0,
				f = $(this),
				p = f.attr('data-progress-animation'),
				w = f.width();
			if (w == 0 && p) {
				p = p.substring(0, p.length - 1);
				var go = function () {
					return k >= p || k >= 100 ? false : ((k += 1), f.css('width', k + '%'), setTimeout(go, 20));
				};
				go();
			}
		});
	}

	/* Bound ONCE — the old code re-bound its close/nav handlers on every card click. */
	$(document).on('keydown.rttmSp', function (event) {
		var $wrap = $('#rt-smart-modal-container.open');

		if (!$wrap.length) {
			return;
		}

		if (event.keyCode === 27) {
			ttpSpClose();
		} else if (event.keyCode === 37) {
			ttpSpStep($wrap, -1);
		} else if (event.keyCode === 39) {
			ttpSpStep($wrap, 1);
		}
	});

	/* ---------- multiple popup: the fullscreen viewer ----------
	   The shell is built once per open and survives every member step; only the panel
	   inside `.tlp-popup-content` is swapped. Every legacy hook class is kept on it
	   (.tlp-popup-navigation-wrap, .tlp-popup-navigation, .tlp-popup-prev/-close/-next,
	   .tlp-popup-content and the counter) because user Style controls are wired to
	   them — see ElementorFilters::colorControls() "PopUp Colors" and the popup
	   background block in Fns::layoutStyleGenerator(). */

	function ttpMpopClose() {
		var $wrap = $('#tlp-popup-wrap');
		if (!$wrap.length) {
			return;
		}
		$wrap.removeClass('is-open');
		setTimeout(function () {
			$wrap.remove();
		}, 350);
	}

	function ttpMpopBuild() {
		$('#tlp-popup-wrap').remove();

		var html =
			'<div id="tlp-popup-wrap" class="tlp-popup-wrap rttm-mpop">' +
			'<div class="tlp-popup-navigation-wrap rttm-mpop-bar">' +
			'<div class="tlp-popup-singlePage-counter rttm-mpop-counter"><b class="ccurrent"></b> <span class="rttm-mpop-of">' + ttp.lan.of + '</span> <span class="ctotal"></span></div>' +
			'<div class="tlp-popup-navigation rttm-mpop-controls">' +
			'<button type="button" class="tlp-popup-prev rttm-mpop-ctrl" title="Previous (Left arrow key)" data-action="prev" aria-label="Previous member"><i class="fas fa-chevron-left"></i></button>' +
			'<button type="button" class="tlp-popup-close rttm-mpop-ctrl is-close" title="Close (Esc key)" data-action="close" aria-label="Close"><i class="fas fa-times"></i></button>' +
			'<button type="button" class="tlp-popup-next rttm-mpop-ctrl" title="Next (Right arrow key)" data-action="next" aria-label="Next member"><i class="fas fa-chevron-right"></i></button>' +
			'</div>' +
			'<div class="rttm-mpop-spacer"></div>' +
			'<div class="rttm-mpop-progress"></div>' +
			'</div>' +
			'<div class="tlp-popup-content rttm-mpop-stage"><div class="rttm-mpop-loading"></div></div>' +
			'<div class="rttm-mpop-strip"></div>' +
			'</div>';

		$('body').append(html);

		var $wrap = $('#tlp-popup-wrap');

		// Adding the reveal class in the same frame as the insert collapses both states
		// and the viewer just appears; force a style flush first.
		void $wrap[0].offsetWidth;
		requestAnimationFrame(function () {
			$wrap.addClass('is-open');
		});

		$wrap.on('click', '.tlp-popup-close', function () {
			ttpMpopClose();
			return false;
		});
		$wrap.on('click', '.tlp-popup-prev', function () {
			ttpMpopStep($wrap, -1);
			return false;
		});
		$wrap.on('click', '.tlp-popup-next', function () {
			ttpMpopStep($wrap, 1);
			return false;
		});
		$wrap.on('click', '.rttm-mpop-thumb', function () {
			var id = String($(this).attr('data-id')),
				list = $wrap.data('rttmList') || [],
				cur = String($wrap.data('rttmCurrent'));
			if (id === cur) {
				return false;
			}
			ttpMpopLoad($wrap, id, $.inArray(id, list) > $.inArray(cur, list) ? 1 : -1);
			return false;
		});
		$wrap.on('click', '.rttm-mpop-garrow', function () {
			ttpMpopShot($wrap, $(this).hasClass('next') ? 1 : -1, false);
			return false;
		});
		$wrap.on('click', '.rttm-mpop-dot', function () {
			ttpMpopShot($wrap, parseInt($(this).attr('data-i'), 10), true);
			return false;
		});

		return $wrap;
	}

	/* counter, progress bar, and hiding the stepper when there is only one member */
	function ttpMpopLevel($wrap, current, list) {
		var index = $.inArray(String(current), list) + 1,
			count = list.length;

		$wrap.find('.ccurrent').text(index || 1);
		$wrap.find('.ctotal').text(count);
		$wrap.find('.rttm-mpop-progress').css('width', count ? ((index || 1) / count) * 100 + '%' : 0);
		$wrap.find('.tlp-popup-prev, .tlp-popup-next').css('display', count > 1 ? '' : 'none');
	}

	/* The strip mirrors exactly the members the viewer can step through — the visible
	   cards getItemsArray() counts — so the thumbnails come from the opening
	   container's own cards rather than a second query. */
	function ttpMpopStrip($wrap, container, list, current) {
		var $strip = $wrap.find('.rttm-mpop-strip');

		if (!$strip.length) {
			return;
		}

		if (!container || list.length < 2) {
			$strip.empty();
			return;
		}

		if ($strip.data('rttmFor') !== list.join(',')) {
			var html = '';
			$.each(list, function (i, id) {
				var $img = container.find('.rt-grid-item[data-id="' + id + '"]').first().find('img').first(),
					src = $img.attr('src') || '',
					alt = ($img.attr('alt') || '').replace(/"/g, '');
				html +=
					'<button type="button" class="rttm-mpop-thumb" data-id="' + id + '" aria-label="' + alt + '">' +
					(src ? '<img src="' + src + '" alt="' + alt + '" />' : '') +
					'</button>';
			});
			$strip.html(html).data('rttmFor', list.join(','));
		}

		$strip.find('.rttm-mpop-thumb').each(function () {
			$(this).toggleClass('is-active', String($(this).attr('data-id')) === String(current));
		});
	}

	/* photo slider inside the panel — a plain cross-fade, so nothing has to measure a
	   container that is still fading in */
	function ttpMpopShot($wrap, value, absolute) {
		var $slides = $wrap.find('.rttm-mpop-slide'),
			$dots = $wrap.find('.rttm-mpop-dot');

		if (!$slides.length) {
			return;
		}

		var cur = $slides.index($slides.filter('.is-active'));
		if (cur < 0) {
			cur = 0;
		}

		var i = absolute ? value : cur + value;
		i = ((i % $slides.length) + $slides.length) % $slides.length;

		$slides.removeClass('is-active').eq(i).addClass('is-active');
		$dots.removeClass('is-active').eq(i).addClass('is-active');
	}

	function ttpMpopStep($wrap, delta) {
		var list = $wrap.data('rttmList') || [],
			cur = String($wrap.data('rttmCurrent'));

		if (list.length < 2) {
			return;
		}

		var i = $.inArray(cur, list);
		if (i < 0) {
			i = 0;
		}
		i = (i + delta + list.length) % list.length;

		ttpMpopLoad($wrap, list[i], delta);
	}

	/* Loads one member. `dir` is 0 on first open and ±1 when stepping, which drives the
	   slide-out / slide-back-in: the outgoing panel leaves in the direction of travel
	   and the incoming one arrives from the opposite side. */
	function ttpMpopLoad($wrap, id, dir) {
		var $stage = $wrap.find('.rttm-mpop-stage'),
			$old = $stage.find('.rttm-mpop-card'),
			list = $wrap.data('rttmList') || [];

		$wrap.data('rttmCurrent', String(id));
		ttpMpopLevel($wrap, id, list);
		ttpMpopStrip($wrap, $wrap.data('rttmContainer'), list, id);

		var fire = function () {
			$.ajax({
				type: 'post',
				url: ttp.ajaxurl,
				data: ttp.nonceID + '=' + ttp.nonce + '&action=tlp_multi_popup_single&id=' + id,
				success: function (data) {
					$stage.html(data.data);

					var $card = $stage.find('.rttm-mpop-card');
					if (dir && $card.length) {
						$card[0].style.setProperty('--rttm-mpop-swap', dir > 0 ? '24px' : '-24px');
						$card.addClass('is-swapping');
						void $card[0].offsetWidth;
						requestAnimationFrame(function () {
							$card.removeClass('is-swapping');
						});
					}

					tlpSingleTeamScript();
				},
				error: function (e) {
					console.log(e);
					$stage.html('<p>Loading error!!!</p>');
				}
			});
		};

		if (dir && $old.length) {
			$old[0].style.setProperty('--rttm-mpop-swap', dir > 0 ? '-24px' : '24px');
			$old.addClass('is-swapping');
			setTimeout(fire, 220);
		} else {
			$stage.html('<div class="rttm-mpop-loading"></div>');
			fire();
		}
	}

	/* Bound ONCE. The old code bound this inside the card click handler, so every card
	   ever clicked added another listener and a single arrow press stepped several
	   times. */
	$(document).on('keydown.rttmMpop', function (event) {
		var $wrap = $('#tlp-popup-wrap.is-open');

		if (!$wrap.length) {
			return;
		}

		if (event.keyCode === 27) {
			ttpMpopClose();
		} else if (event.keyCode === 37) {
			ttpMpopStep($wrap, -1);
		} else if (event.keyCode === 39) {
			ttpMpopStep($wrap, 1);
		}
	});

	/* the viewer sizes itself from the viewport; the old slide-in panel needed a
	   display:block nudge on resize, this one must never be forced visible. */
	function navResize() {}

	$(window).on('load resize', function () {
		navResize();
		HeightResize();
		if ($(".tlp-md-content").length) {
			$(".tlp-md-content").mCustomScrollbar({
				scrollbarPosition: "outside"
			});
		}
	});
	$.fn.alterClass = function (removals, additions) {
		var self = this;
		if (removals.indexOf('*') === -1) {
			// Use native jQuery methods if there is no wildcard matching
			self.removeClass(removals);
			return !additions ? self : self.addClass(additions);
		}
		var patt = new RegExp('\\s' +
			removals.replace(/\*/g, '[A-Za-z0-9-_]+').split(' ').join('\\s|\\s') +
			'\\s', 'g');
		self.each(function (i, it) {
			var cn = ' ' + it.className + ' ';
			while (patt.test(cn)) {
				cn = cn.replace(patt, ' ');
			}
			it.className = $.trim(cn);
		});
		return !additions ? self : self.addClass(additions);
	};

	$('.md-close').on('click', function (e) {
		e.preventDefault();
		mdModalWrap.removeClass('md-show');
		mdModalWrap.alterClass('tlp-modal-*');
		mdModalWrap.find('.tlp-md-content-holder').html('');
	});

	skillAnimation();

	if( $('.tlp-tooltip').length > 0 ) {
		$('.tlp-tooltip').rtTooltip();
	}

	var RttmSlider = function ($slider) {
		this.$slider = $slider;
		this.slider = this.$slider.get(0);
		this.swiperSlider = this.slider.swiper || null;
		this.defaultOptions = {
			breakpointsInverse: true,
			observer: true,
			navigation: {
				nextEl: this.$slider.find('.swiper-button-next').get(0),
				prevEl: this.$slider.find('.swiper-button-prev').get(0),
			},
			pagination: {
				el: this.$slider.find('.swiper-pagination').get(0),
				type: 'bullets',
				clickable: true
			}
		};

		this.slider_enabled = 'function' === typeof Swiper;
		this.options = Object.assign({}, this.defaultOptions, this.$slider.data('options') || {});
		this.initSlider = function () {
			if (!this.slider_enabled) {
				return;
			}
			if (this.options.rtl) {
				this.$slider.attr('dir', 'rtl');
			}
			if (this.swiperSlider) {
				this.swiperSlider.parents = this.options;
				this.swiperSlider.update();
			} else {
				this.swiperSlider = new Swiper(this.$slider.get(0), this.options);
			}
		};
		this.imagesLoaded = function () {
			if(this.$slider.data('options').lazy) {
				this.$slider.trigger('rttm_slider_loaded', this);
				return;
			}

			var that = this;

			if (!$.isFunction($.fn.imagesLoaded) || $.fn.imagesLoaded.done) {
				this.$slider.trigger('rttm_slider_loading', this);
				this.$slider.trigger('rttm_slider_loaded', this);
				return;
			}

			this.$slider.imagesLoaded().progress(function (instance, image) {
				that.$slider.trigger('rttm_slider_loading', [that]);
			}).done(function (instance) {
				that.$slider.trigger('rttm_slider_loaded', [that]);
			});
		};
		this.start = function () {
			var that = this;
			this.$slider.on('rttm_slider_loaded', this.init.bind(this));
			setTimeout(function () {
				that.imagesLoaded();
			}, 1);
		};
		this.init = function () {
			this.initSlider();
		};
		this.rtSwiper = function () {
			return new Swiper(this.$slider.get(0), this.options);
		};

		this.start();
	};

	$.fn.rttm_slider = function () {
		new RttmSlider(this);
		return this;
	};
	window.rttmpBuilderInit = {
		initSlider: function () {
			var $ = jQuery;
			rtSliderInit($);
		},
		initLoader: function () {
			$(".rttmp-team-builder").each(function (index) {
				var container = $(this),
					loader = container.find(".rt-content-loader");

				loader.find('.rt-loading').fadeOut(300);
				loader.removeClass('ttp-pre-loader');
			});

		}
	};
	rttmpBuilderInit.initLoader();
	rttmpBuilderInit.initSlider()


})(jQuery);

function rtSliderInit($) {
	$('.rttm-carousel-slider').each(function () {
		$(this).rttm_slider();
	});
}

function skillAnimation() {
	jQuery('.tlp-team-skill').find('.fill').css('width', '0%');
	jQuery(window).bind('load', function () {
		jQuery('.tlp-team-skill').each(function () {
			jQuery(this).find('.fill').each(function () {
				var k = 0, f = jQuery(this), p = f.attr('data-progress-animation'), w = f.width();
				if (w == 0) {
					p = p.substring(0, p.length - 1);
					var go = function () {
						return k >= p || k >= 100 ? (false) : (k += 1, f.css('width', k + '%'), setTimeout(go, 20))
					};
					go();
				}
			});
		});
	});
}

function mdPopUpSkillAnimation() {
	rtSliderInit(jQuery);
	jQuery('#tlp-modal .tlp-md-content').mCustomScrollbar({
		scrollbarPosition: 'outside'
	});
	jQuery('#tlp-modal .tlp-tooltip').rtTooltip();
	jQuery('#tlp-modal .tlp-team-skill').find('.fill').css('width', '0%');

	jQuery('#tlp-modal .tlp-team-skill').each(function () {
		jQuery(this).find('.fill').each(function () {
			var k = 0, f = jQuery(this), p = f.attr('data-progress-animation'), w = f.width();
			if (w == 0) {
				p = p.substring(0, p.length - 1);
				var go = function () {
					return k >= p || k >= 100 ? (false) : (k += 1, f.css('width', k + '%'), setTimeout(go, 20))
				};
				go();
			}
		});
	});
}

function tlpSingleTeamScript() {
	jQuery('#tlp-popup-wrap .tlp-tooltip').rtTooltip();
	jQuery('#tlp-popup-wrap .tlp-team-skill').find('.fill').css('width', '0%');
	jQuery('#tlp-popup-wrap .tlp-team-skill .fill').each(function () {
		var k = 0, f = jQuery(this), p = f.attr('data-progress-animation'), w = f.width();
		if (w == 0) {
			p = p.substring(0, p.length - 1);
			var go = function () {
				return k >= p || k >= 100 ? (false) : (k += 1, f.css('width', k + '%'), setTimeout(go, 20))
			};
			go();
		}
	});
	rtSliderInit(jQuery);
}

function RTElementThumbCarousel(container, options, instance, index) {
	// Params
	var mainSlider = container.find('.rttm-carousel-main').addClass('instance-' + index),
		navSlider = container.find('.ttp-carousel-thumb').addClass('instance-' + index),
		navPrev = '.swiper-button-prev',
		navNext = '.swiper-button-next',
		dotsEl = '.swiper-pagination';

	container.find(navPrev).addClass('prev-' + index);
	container.find(navNext).addClass('next-' + index);

	// Main Slider
	var mainSliderOptions = {
		loop: true,
		speed: options.speed ? options.speed : 1000,
		loopedSlides: 5,
		autoHeight: options.autoHeight ? options.autoHeight : false,
		navigation: {
			nextEl: navNext,
			prevEl: navPrev
		},
		pagination: {
			el: dotsEl,
			type: 'bullets',
			clickable: true
		}
	};

	var main = new Swiper(mainSlider[0], mainSliderOptions);

	// Navigation Slider
	var navSliderOptions = {
		loop: true,
		speed: options.speed ? options.speed : 1000,
		observer: true,
		observerParents: true,
		slidesPerView: options.slidesPerView ? options.slidesPerView : 5,
		centeredSlides: true,
		spaceBetween: 0,
		touchRatio: 0.2,
		slideToClickedSlide: true,
		loopedSlides: 5,
		watchSlidesProgress: true,
		breakpoints: options.breakpoints
	};

	if (options.autoplay) {
		navSliderOptions.autoplay = {
			delay: options.autoplay.delay,
			pauseOnMouseEnter: options.autoplay.pauseOnMouseEnter,
			disableOnInteraction: false
		};
	}

	if (options.lazy) {
		navSliderOptions.preloadImages = false;
		navSliderOptions.lazy = true;
	}

	var thumb = new Swiper(navSlider[0], navSliderOptions);

	// Syncing the sliders
	main.controller.control = thumb;
	thumb.controller.control = main;

	instance[index] = [main, thumb];
}
