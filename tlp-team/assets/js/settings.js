(function (global, $) {
	$.browser = {};
	$.browser.msie = false;
	$.browser.version = 0;
	if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
		$.browser.msie = true;
		$.browser.version = RegExp.$1;
	}
	var editor,
		syncCSS = function () {
			$('.rt-custom-css').each(function () {
				var e = ace.edit($(this).find('.custom-css').attr('id'));
				$(this).find('.rt-custom-css-textarea').val(e.getSession().getValue());
			});
		},
		loadAce = function () {
			$('.rt-custom-css').each(function () {
				var id = $(this).find('.custom-css').attr('id');
				editor = ace.edit(id);
				global.safecss_editor = editor;
				editor.getSession().setUseWrapMode(true);
				editor.setShowPrintMargin(false);
				editor.getSession().setValue($(this).find('.rt-custom-css-textarea').val());
				editor.getSession().setMode("ace/mode/css");
			});

			$.fn.spin && $('.rt-custom-css-container').spin(false);
		},
		AjaxCallTeam = function (element, action, arg, handle) {
			var data;
			if (action) data = "action=" + action;
			if (arg) data = arg + "&action=" + action;
			if (arg && !action) data = arg;
			var n = data.search(ttp.nonceID);
			if (n < 0) {
				data = data + "&" + ttp.nonceID + "=" + ttp.nonce;
			}

			$.ajax({
				type: "post",
				url: ttp.ajaxurl,
				data: data,
				beforeSend: function () {
					$("<span class='tlp_loading'></span>").insertAfter(element);
				},
				success: function (data) {
					element.next(".tlp_loading").remove();
					handle(data);
				},
				error: function (error) {
					element.next(".tlp_loading").remove();
					handle(error);
				}
			});
		},
		teamGalleryFrame = null,
		/**
		 * Existing attachment ids in the gallery list, so a re-pick cannot double up.
		 */
		teamGalleryIds = function () {
			return $("ul#tlp-team-gallery input[name='tlp_team_gallery[]']").map(function () {
				return String($(this).val());
			}).get();
		},
		/**
		 * Show the "No image found" placeholder only while the list is really empty.
		 * It used to be hidden on the first add and never restored, so removing every
		 * image left a blank box with no hint that the field was empty.
		 */
		syncTeamGalleryPlaceholder = function () {
			var list = $("ul#tlp-team-gallery"),
				hasImages = list.find("input[name='tlp_team_gallery[]']").length > 0,
				placeholder = list.find("li.no-img");

			if (hasImages) {
				placeholder.hide();
			} else if (placeholder.length) {
				placeholder.show();
			} else {
				list.append("<li class='no-img'>" + (ttp.noImageText || 'No image found') + "</li>");
			}
		},
		renderTeamMediaUploader = function () {
			// Build the frame once and reuse it. The previous version declared
			// `file_frame` as a local, so its `undefined !== file_frame` guard could
			// never be true and every click built a brand new frame.
			if (teamGalleryFrame) {
				teamGalleryFrame.open();
				return;
			}

			teamGalleryFrame = wp.media.frames.file_frame = wp.media({
				title: 'Select or Upload Media For your member profile gallery',
				button: {
					text: 'Use this media'
				},
				// Multi-select: pick or upload any number of images in one pass.
				multiple: 'add'
			});

			teamGalleryFrame.on('select', function () {
				var selection = teamGalleryFrame.state().get('selection'),
					list = $("ul#tlp-team-gallery"),
					existing = teamGalleryIds();

				selection.map(function (item) {
					var attachment = item.toJSON(),
						imgId = String(attachment.id),
						sizes = attachment.sizes || {},
						imgUrl = sizes.thumbnail ? sizes.thumbnail.url : attachment.url;

					// Skip anything already in the list — WP keeps previous picks
					// selected when the frame is reopened.
					if ($.inArray(imgId, existing) !== -1) {
						return;
					}
					existing.push(imgId);

					// `rttm-unsaved` marks an image that exists only in this form until
					// the post is saved — the meta row is written by PostMeta.php from the
					// hidden inputs on submit, so until then it is not attached to anything.
					list.append(
						"<li class='rttm-unsaved'><span class='dashicons dashicons-dismiss'></span>" +
						"<img src='" + imgUrl + "' alt='' />" +
						"<span class='rttm-unsaved-badge'>" + (ttp.unsavedText || 'Unsaved') + "</span>" +
						"<input type='hidden' name='tlp_team_gallery[]' value='" + imgId + "' />" +
						"</li>"
					);
				});

				syncTeamGalleryPlaceholder();
			});

			// Reopening: mark what is already in the list as selected, so the frame
			// reflects the current gallery instead of starting blank each time.
			teamGalleryFrame.on('open', function () {
				var selection = teamGalleryFrame.state().get('selection');
				selection.reset();
				$.each(teamGalleryIds(), function (i, id) {
					var attachment = wp.media.attachment(id);
					attachment.fetch();
					selection.add(attachment);
				});
			});

			teamGalleryFrame.open();
		};


	if ($.browser.msie && parseInt($.browser.version, 10) <= 7) {
		$('.rt-custom-css-container').hide();
		$('.rt-custom-css-textarea').show();
		return false;
	} else {
		$(global).load(loadAce);
	}
	global.aceSyncCSS = syncCSS;

	$("#tagsdiv-tlp_designation, #tagsdiv-tlp_skill").remove();

	$(function () {

		//techlabpro23
		if ($(".rttm-select2").length && $.fn.select2) {
			$(".rttm-select2").select2({
				multiple: true,
				width: '80%',
				placeholder: "Choose Items"
			});
		}

		if ($(".rttm-select2-single").length && $.fn.select2) {
			$(".rttm-select2-single").select2();
		}

		$("#sc-tabs, #tlp_team_meta, #settings-tabs").on('click', '.pro-field', function (e) {
			e.preventDefault();
			$('.rttm-pro-alert').show();
		});

		//pro alert close
		$('.rttm-pro-alert-close').on('click', function (e) {
			e.preventDefault();
			$('.rttm-pro-alert').hide();
		});

		$('#license_key').on('keyup', function () {
			$('.license-status').hide();
		});

		// dynamic layout list
		dynamicLayoutList();
		$('input[name=layout_type]').on('change', function () {
			dynamicLayoutList();
		});
		//end tachlabpro23

		if ($('.tlp-color').length) {
			$('.tlp-color').wpColorPicker();
		}

		if ($("#metaSocialHolder").length || $("#metaSkillHolder").length) {
			$("#metaSocialHolder, #metaSkillHolder").sortable();
		}

		changeEffect();
	});

	function dynamicLayoutList() {

		let layout_type = $('input[name=layout_type]:checked').val(),
			layout = $("#rttm-style");
		if (layout_type) {
			let layout_option = '';
			for (const [key, value] of Object.entries(ttp.layout_group[layout_type])) {
				let checked = (ttp.layout == value.value) ? 'checked' : '';
				layout_option += `<label for="rttm-style-${value.value}">
				<input type="radio" id="rttm-style-${value.value}" name="layout" ${checked} value="${value.value}" data-pro="">
				<div class="rttm-radio-image-pro-wrap">
				<img src="${value.img}" title="${value.name}" alt="${value.value}">
				<div class="rttm-checked"><span class="dashicons dashicons-yes"></span></div>
				</div><div class="rttm-demo-url"><a href="${value.demo}" target="_blank">${value.name}</a></div>
				</label>`;
			}

			layout.empty();
			layout.append(layout_option);
			layout_style();
			changeEffect();
		}
	}

	$("#addNewSocial").on('click', function (e) {
		var total = $('.socialLink').length;
		arg = "id=" + total;
		bindElement = $('#addNewSocial');
		$('.socialLink').each(function (index, e) {
			$(e).attr('id', 'slh-' + index);
			$(e).children('.tlp-label').children('select').attr('name', 'social[' + index + '][id]');
			$(e).children('.tlp-field').children('.tlpfield').attr('name', 'social[' + index + '][url]');
			$(e).children('.sRemove').attr('data-id', index);
		});
		AjaxCallTeam(bindElement, 'tlpTeamSocialInput', arg, function (data) {
			$("#metaSocialHolder").append(data);
			tlpSocialInputPlaceholder();
		});
	});

	tlpSocialInputPlaceholder();

	$(document).on('click', ".sRemove", function (e) {
		var id = $(this).data("id");
		$('#slh-' + id).slideUp("slow", function () {
			$(this).remove();
			$('.socialLink').each(function (index, e) {
				$(e).attr('id', 'slh-' + index);
				$(e).children('.tlp-label').children('select').attr('name', 'social[' + index + '][id]');
				$(e).children('.tlp-field').children('.tlpfield').attr('name', 'social[' + index + '][url]');
				$(e).children('.sRemove').attr('data-id', index);
			});
		});
	});

	$("#addNewSkill").on('click', function () {
		var total = $('.skillHolder').length;
		arg = "id=" + total;
		bindElement = jQuery('#addNewSkill');
		$('.skillHolder').each(function (index, e) {
			$(e).attr('id', 'sh-' + index);
			$(e).children('.tlp-label').children('select').attr('name', 'skill[' + index + '][id]');
			$(e).children('.tlp-field').children('.tlpfield').attr('name', 'skill[' + index + '][percent]');
			$(e).children('.sRemove').attr('data-id', index);
		});
		AjaxCallTeam(bindElement, 'tlpTeamSkillInput', arg, function (data) {
			$("#metaSkillHolder").append(data);
		});
	});

	$(document).on('click', ".skRemove", function (e) {
		var id = $(this).data("id");
		$('#sh-' + id).slideUp("slow", function () {
			$(this).remove();
			$('.skillHolder').each(function (index, e) {
				$(e).attr('id', 'sh-' + index);
				$(e).children('.tlp-label').children('select').attr('name', 'skill[' + index + '][id]');
				$(e).children('.tlp-field').children('.tlpfield').attr('name', 'skill[' + index + '][percent]');
				$(e).children('.skRemove').attr('data-id', index);
			});
		});
	});

	$("#add-new-team-img").on('click', function (evt) {
		evt.preventDefault();
		renderTeamMediaUploader();
	});

	/*
	 * Clear the "Unsaved" markers once the images really are saved.
	 *
	 * The classic editor reloads the whole screen on Update, so the server simply
	 * re-renders the list without them. The block editor does NOT: it posts the
	 * meta box form over ajax and leaves this markup in place, so without this the
	 * badges would sit there claiming the images are unsaved long after they were
	 * written. `isSavingMetaBoxes` is the flag that covers exactly that request; we
	 * clear on its falling edge.
	 */
	if (window.wp && wp.data && typeof wp.data.subscribe === 'function') {
		(function () {
			var wasSaving = false;
			wp.data.subscribe(function () {
				var editPost = wp.data.select('core/edit-post');
				if (!editPost || typeof editPost.isSavingMetaBoxes !== 'function') {
					return;
				}
				var saving = editPost.isSavingMetaBoxes();
				if (wasSaving && !saving) {
					$('ul#tlp-team-gallery li.rttm-unsaved')
						.removeClass('rttm-unsaved')
						.find('.rttm-unsaved-badge').remove();
				}
				wasSaving = saving;
			});
		})();
	}

	if ($('ul#tlp-team-gallery').length) {
		$('ul#tlp-team-gallery').sortable({
			items: 'li',
			opacity: 0.5,
			cursor: 'pointer'
		});
	}

	/*
	 * Gallery image removal.
	 *
	 * Two things were wrong here. The handler was bound DIRECTLY to the elements
	 * present at page load, so an image just added by the media frame had no handler
	 * at all — it could not be removed until the post was saved and the server
	 * re-rendered the list. (The delegated form was sitting right above, commented
	 * out.) And removal went through the `tlp_team_profile_img_remove` ajax call,
	 * which does `delete_post_meta( $post_id, 'tlp_team_gallery', $id )` — that
	 * returns false for an image that has never been saved, so even a bound handler
	 * would have refused to drop it.
	 *
	 * Removal is now delegated and purely client-side. Nothing is lost: the save
	 * handler in PostMeta.php deletes all `tlp_team_gallery` meta and re-adds it from
	 * the submitted `tlp_team_gallery[]` inputs, so dropping the <li> is exactly what
	 * makes the image go away on Update — and, as everywhere else in the editor,
	 * leaving without saving discards the change instead of committing it.
	 */
	$(document).on("click", "ul#tlp-team-gallery li span.dashicons-dismiss", function (e) {
		e.preventDefault();

		var li = $(this).closest('li');

		li.fadeOut(200, function () {
			$(this).remove();
			syncTeamGalleryPlaceholder();
		});
	});


	$(".rt-tab-nav li").on('click', 'a', function (e) {
		e.preventDefault();
		var container = $(this).parents('.rt-tab-container');
		var nav = container.children('.rt-tab-nav');
		var content = container.children(".rt-tab-content");
		var $this, $id;
		$this = $(this);
		$id = $this.attr('href');

		switch ($id) {
			case '#sc-layout':
				$('#_rttm_sc_tab').val('layout');
				break;

			case '#sc-filtering':
				$('#_rttm_sc_tab').val('filtering');
				break;

			case '#sc-field-selection':
				$('#_rttm_sc_tab').val('field-selection');
				break;

			case '#sc-styling':
				$('#_rttm_sc_tab').val('styling');
				break;
		}

		content.hide();
		nav.find('li').removeClass('active');
		$this.parent().addClass('active');
		container.find($id).show();
	});
	jQuery(document).ready(function ($) {
		$('#settings-tabs .rt-tab-nav li a').on('click', function (e) {
			e.preventDefault();

			const $this = $(this);
			const target = $this.attr('href');

			$('#settings-tabs .rt-tab-nav li').removeClass('active');
			$('#settings-tabs .rt-tab-content').hide();

			$this.parent('li').addClass('active');
			$(target).fadeIn(200);
		});
	});


	if (rttm.is_pro && $('.post-type-team table.posts #the-list').length) {
		var fixHelper = function (e, ui) {
			ui.children().children().each(function () {
				$(this).width($(this).width());
			});
			return ui;
		};
		$('.post-type-team table.posts #the-list').sortable({
			'items': 'tr',
			'axis': 'y',
			'helper': fixHelper,
			'update': function (e, ui) {
				var order = $('#the-list').sortable('serialize');
				$.ajax({
					type: "post",
					url: ajaxurl,
					data: order + "&action=tlp-team-update-menu-order&"+ ttp.nonceID + "=" + ttp.nonce,
					beforeSend: function () {
						$('body').append($("<div id='rt-loading'><span class='rt-loading'>Updating ...</span></div>"));
					},
					success: function (data) {
						console.log(data);
						$("#rt-loading").remove();
					}
				});
			}
		});
	}

	$("#tlp-team-settings").on("click", "#tlpSaveButton", function (e) {
		e.preventDefault();
		syncCSS();
		$('#rt-response').hide();
		var self = $(this),
			form = self.parents('form'),
			arg = form.serialize(),
			bindElement = $('#tlpSaveButton', form),
			responseHolder = $('#rt-response');
		AjaxCallTeam(bindElement, 'tlpTeamSettings', arg, function (data) {
			responseHolder.show('slow').text(data.msg).addClass('updated');
			if (data.error) {
				responseHolder.addClass('error');
			} else {
				responseHolder.removeClass('error');
				var holder = $("#license_key_holder");
				if (!$(".license-status", holder).length && $("#license_key", holder).val()) {
					var bindElement = $("#license_key", holder),
						target = $(".description", holder);
					target.find(".rt-licence-msg").remove();
					AjaxCallTeam(bindElement, 'rtTeam_active_Licence', '', function (data) {
						if (data.html) {
							target.append("<span class='license-status'>" + data.html + "</span>");
						}
						if (data.msg) {
							if (target.find(".rt-licence-msg").length) {
								target.find(".rt-licence-msg").html(data.msg);
							} else {
								target.append("<span class='rt-licence-msg'>" + data.msg + "</span>");
							}
							if (!data.error) {
								target.find(".rt-licence-msg").addClass('success');
							}
						}
					});
				}
				if (!$("#license_key", holder).val()) {
					$('.license-status', holder).remove();
				}
			}
		});

		return false;
	});

	$("#tlp-team-settings").on('click', '.rt-team-licensing-btn', function (e) {
		e.preventDefault();
		var self = $(this),
			type = self.attr('name'),
			data = 'type=' + type;
		$("#license_key_holder").find(".rt-licence-msg").remove();
		AjaxCallTeam(self, 'rtTeamManageLicencing', data, function (data) {
			console.log(data);
			if (!data.error) {
				self.val(data.value);
				self.attr('name', data.name);
				self.addClass(data.class);
				if (data.name == 'license_deactivate') {
					self.removeClass('button-primary');
					self.addClass('danger');
				} else if (data.name == 'license_activate') {
					self.removeClass('danger');
					self.addClass('button-primary');
				}
			}
			if (data.msg) {
				$("<div class='rt-licence-msg'>" + data.msg + "</div>").insertAfter(self);
			}
			self.blur();
		});

		return false;
	});

	function layout_style() {
		$("input[type=radio][name=layout]").on('change', function () {
			changeEffect();
		});
	}

	layout_style();

	$("#ttp_pagination").on('change', function () {
		paginationEffect();
	});
	$("#ttp_image").on('change', function () {
		featureImageEffect();
	});
	$("#ttp_detail_page_link").on('change', function () {
		detailLinkEffect();
	});
	$("#ttp_image_size").on('change', function () {
		imageSizeEffect();
	});
	$("#ttp_filter_taxonomy").on('change', function () {
		setDefaultItemsForFilterGrid();
	});
	$("#ttp_isotope_filter_taxonomy").on('change', function () {
		setDefaultItemsForFilterIso();
	});
	$("#ttp_filter-_taxonomy_filter").on('change', function () {
		taxonomyFilterEffect();
	});
	$("#ttp_filter input[name='ttp_filter']").on('change', function () {
		filterEffectToPagination();
	});
	$("#ttp_detail_page_link_type  input[name='ttp_detail_page_link_type']").on('change', function () {
		linkTypeEffect();
	});

	function changeEffect() {
		featureImageEffect();
		imageSizeEffect();
		let layout = $("input[name=layout]:checked").val();
		if (layout) {
			let isGrid = layout.match(/^layout/i),
				isCarousel = layout.match(/^carousel/i),
				isIsotope = layout.match(/^isotope/i),
				plType = $("#ttp_pagination_type");
			plType.find("label[for='ttp_pagination_type-pagination'],label[for='ttp_pagination_type-pagination_ajax']").show();
			$("#ttl_image_column_holder").hide();
			// Shown by default and taken away in the carousel branch below, so switching
			// away from a slider brings it back.
			$(".tlp-field-holder.ttp-grid-style-item").show();
			if (isGrid) {
				$(".tlp-field-holder.ttp-isotope-filter-item, .tlp-field-holder.ttp-carousel-item").hide();
				$("#ttp_filter_holder,.tlp-field-holder.ttp-pagination-item.pagination").show();
				if (layout == "layout2") {
					$("#ttl_image_column_holder").show();
				}
			} else if (isCarousel) {
				$(".tlp-field-holder.ttp-pagination-item,.tlp-field-holder.ttp-isotope-filter-item,.tlp-field-holder.sc-ttp-grid-filter,.tlp-field-holder.ttp-grid-style-item").hide();
				$(".tlp-field-holder.ttp-carousel-item").show();
			} else if (isIsotope) {
				$(".tlp-field-holder.ttp-carousel-item,.tlp-field-holder.sc-ttp-grid-filter").hide();
				$(".tlp-field-holder.ttp-pagination-item.pagination,.tlp-field-holder.ttp-isotope-filter-item").show();
				plType.find("label[for='ttp_pagination_type-pagination'],label[for='ttp_pagination_type-pagination_ajax']").hide();
				var ltype = plType.find("input[name=ttp_pagination_type]:checked").val();
				if (ltype == "pagination" || ltype == "pagination_ajax") {
					plType.find("label[for='ttp_pagination_type-load_more'] input").prop("checked", true);
				}
				if ($("#rt-tpg-sc-isotope-filter option:selected").length) {
					setDefaultItems();
				}
			}
			if ($(".tlp-field-holder.ttp-pagination-item.pagination").is(':visible')) {
				paginationEffect()
			}
			if ($("#ttp_filter_holder").is(':visible')) {
				taxonomyFilterEffect();
				filterEffectToPagination();
			}
			detailLinkEffect();
			setDefaultItemsForFilterGrid();
			setDefaultItemsForFilterIso();
		}
	}

	function paginationEffect() {
		var pagination = $("#ttp_pagination").is(':checked');

		if (pagination) {
			$(".tlp-field-holder.ttp-pagination-item").show();
		} else {
			$(".tlp-field-holder.ttp-pagination-item,.tlp-field-holder.ttp_link_target").not('.pagination').hide();
		}
		toggleLoadMoreField();

		$('#ttp_pagination_type input[type=radio]').on('change', toggleLoadMoreField);
		$('#ttp_pagination').on('change', toggleLoadMoreField);
	}
	function toggleLoadMoreField() {
		const selectedValue = $('#ttp_pagination_type input[type=radio]:checked').val();
		const isPaginationChecked = $('#ttp_pagination').is(':checked');

		const shouldShow = selectedValue === 'load_more' && isPaginationChecked;
		$('.tlp-field-holder.ttp-load-more-item').toggle(shouldShow);
	}


	function featureImageEffect() {
		if ($("#ttp_image").is(':checked')) {
			$(".tlp-field-holder.ttp-feature-image-option").hide();
		} else {
			$(".tlp-field-holder.ttp-feature-image-option").show();
		}
	}

	function detailLinkEffect() {
		var detailPageLink = $("#ttp_detail_page_link").is(':checked');
		if (detailPageLink) {
			$(".tlp-field-holder.ttp_detail_page_link_type").show();
		} else {
			$(".tlp-field-holder.ttp_detail_page_link_type,.tlp-field-holder.ttp_link_target,.tlp-field-holder.ttp_popup_type").hide();
		}
		if (detailPageLink) {
			linkTypeEffect();
		}

	}

	function linkTypeEffect() {

		var linkType = $("#ttp_detail_page_link_type input[name='ttp_detail_page_link_type']:checked").val();

		if (  (linkType == "new_page" || linkType == "external_link")) {
			$(".tlp-field-holder.ttp_link_target").show();
			$(".tlp-field-holder.ttp_popup_type").hide();
		} else {
			$(".tlp-field-holder.ttp_popup_type").show();
			$(".tlp-field-holder.ttp_link_target").hide();
		}
	}

	function imageSizeEffect() {
		var size = $("#ttp_image_size").val();
		if (size == "ttp_custom") {
			$("#ttp_custom_image_size_holder").show();
		} else {
			$("#ttp_custom_image_size_holder").hide();
		}
	}

	function taxonomyFilterEffect() {
		if ($("#ttp_filter-_taxonomy_filter").is(':checked')) {
			$(".sc-ttp-grid-filter.sc-ttp-filter-item").show();
		} else {
			$(".sc-ttp-grid-filter.sc-ttp-filter-item").not("#ttp_filter_holder").hide();
		}
	}

	function filterEffectToPagination() {
		var plType = $("#ttp_pagination_type"),
			ltype = plType.find("input[name=ttp_pagination_type]:checked").val();
		if ($("#ttp_filter input[name='ttp_filter[]']").is(':checked')) {
			plType.find("label[for='ttp_pagination_type-pagination']").hide();
			if (ltype == "pagination") {
				plType.find("label[for='ttp_pagination_type-pagination_ajax'] input").prop("checked", true);
			}
		} else {
			plType.find("label[for='ttp_pagination_type-pagination']").show();
		}
	}

	function setDefaultItemsForFilterGrid() {
		var target_from = $("#ttp_filter_taxonomy"),
			target = $('#ttp_default_filter'),
			$fId = target_from.val();
		if ($fId) {
			var data = 'action=ttpDefaultFilterItem&filter=' + $fId + "&" + ttp.nonceID + "=" + ttp.nonce;
			$.ajax({
				type: "post",
				url: ttp.ajaxurl,
				data: data,
				beforeSend: function () {
					$("<span class='rt-loading'></span>").insertAfter(target);
				},
				success: function (data) {
					if (!data.error) {
						var selected = target.data('selected');
						target.html(data.data);
						if (selected) {
							target.val(selected).trigger("change");
						}

					} else {
						console.log(data.msg);
					}
					target.next(".rt-loading").remove();
				}
			});
		}
	}

	function setDefaultItemsForFilterIso() {

		var target_from_iso = $("#ttp_isotope_filter_taxonomy"),
			target_iso = $('#ttp_isotope_selected_filter'),
			$fId_iso = target_from_iso.val();
		if ($fId_iso) {
			var data = 'action=ttpDefaultFilterItem&filter=' + $fId_iso + "&" + ttp.nonceID + "=" + ttp.nonce;
			$.ajax({
				type: "post",
				url: ttp.ajaxurl,
				data: data,
				beforeSend: function () {
					$("<span class='rt-loading'></span>").insertAfter(target_iso);
				},
				success: function (data) {
					if (!data.error) {
						var selected = target_iso.data('selected');
						target_iso.html(data.data);
						if (selected) {
							target_iso.val(selected).trigger("change");
						}

					} else {
						console.log(data.msg);
					}
					target_iso.next(".rt-loading").remove();
				}
			});
		}
	}

	function tlpSocialInputPlaceholder() {
		$("#metaSocialHolder").find('.tlp-label>select').on('change', function(e) {
			if(e.target.value === 'whatsapp') {
				$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://wa.me/14134567890');
			} else if(e.target.value === 'skype') {
				$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://join.skype.com/invite/Li8Uhdn6tsYe');
			} else {
				$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', '');
			}

			switch (e.target.value) {
				case 'whatsapp':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://wa.me/14134567890/');
					break;

				case 'skype':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://join.skype.com/invite/Li8Uhdn6tsYe/');
					break;

				case 'facebook':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: http://facebook.com/username/');
					break;

				case 'twitter':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: http://twitter.com/username/');
					break;

				case 'linkedin':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://www.linkedin.com/in/username/');
					break;

				case 'youtube':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://www.youtube.com/@username');
					break;

				case 'instagram':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://www.instagram.com/username/');
					break;

				case 'pinterest':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://www.pinterest.com/username/');
					break;

				case 'soundcloud':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://soundcloud.com/username/');
					break;

				case 'bandcamp':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://username.bandcamp.com/');
					break;

				case 'envelope-o':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: admin@example.com');
					break;

				case 'globe':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://google.com');
					break;

				case 'xing':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://www.xing.com/profile/username');
					break;

				case 'telegram':
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', 'Example: https://t.me/username');
					break;

				default:
					$(this).parents('.tlp-field-holder').find('.tlpfield').attr('placeholder', '');
					break;
			}
		});
	}

})(this, jQuery);
