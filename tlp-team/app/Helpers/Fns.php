<?php
/**
 * Helpers class.
 *
 * @package RT_Team
 */

namespace RT\Team\Helpers;

use RT\Team\Models\Fields;
use RT\Team\Models\ReSizer;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Helpers class.
 */
class Fns {

	/**
	 * Classes instatiation.
	 *
	 * @param array $classes Classes to init.
	 *
	 * @return void
	 */
	public static function instances( array $classes ) {
		if ( empty( $classes ) ) {
			return;
		}

		foreach ( $classes as $class ) {
			$class::get_instance();
		}
	}


	/**
	 * Nonce verification.
	 *
	 * @return boolean
	 */
	public static function verifyNonce() {
		$nonce     = isset( $_REQUEST[ self::nonceID() ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::nonceID() ] ) ) : null;
		$nonceText = self::nonceText();
		if ( ! wp_verify_nonce( $nonce, $nonceText ) ) {
			return false;
		}

		return true;
	}

	public static function getNonce(  ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_REQUEST[ self::nonceID() ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::nonceID() ] ) ) : null;

    }

	/**
	 * Nonce text.
	 *
	 * @return string
	 */
	public static function nonceText() {
		return 'tlp_team_nonce';
	}

	/**
	 * Nonce ID.
	 *
	 * @return string
	 */
	public static function nonceID() {
		return 'tlp_nonce';
	}

	/**
	 * Render.
	 *
	 * @param string  $view_name View name.
	 * @param array   $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function render( $view_name, $args = [], $return = false ) {
		$path = str_replace( '.', '/', $view_name );
		if ( $args ) {
			extract( $args );
		}

		$template = [
			"tlp-team/{$path}.php",
		];

		$pro_path = rttlp_team()->pro_templates_path() . $view_name . '.php';

		if ( locate_template( $template ) ) {
			$template_file = locate_template( $template );
		} elseif ( function_exists( 'rttmp' ) && file_exists( $pro_path ) ) {
			$template_file = $pro_path;
		} else {
			$template_file = rttlp_team()->templates_path() . $view_name . '.php';
		}

		if ( ! file_exists( $template_file ) ) {
			return;
		}

		if ( $return ) {
			ob_start();
			include $template_file;

			return ob_get_clean();
		} else {
			include $template_file;
		}
	}

	/**
	 * Render view.
	 *
	 * @param string  $view_name View name.
	 * @param array   $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function render_view( $view_name, $args = [], $return = false ) {
		$path           = str_replace( '.', '/', $view_name );
		$resources_path = rttlp_team()->plugin_path() . '/resources/' . $path . '.php';

		if ( ! file_exists( $resources_path ) ) {
			return new \WP_Error(
				'brock',
				sprintf(
                        /* translators: %s is the file name */
					__( '%s file not found', 'tlp-team' ),
					esc_html( $resources_path )
				)
			);
		}

		if ( $args ) {
			extract( $args );
		}

		if ( $return ) {
			ob_start();
			include $resources_path;

			return ob_get_clean();
		}

		include $resources_path;
	}

	/**
	 * Field Generator.
	 *
	 * @param array $fields Fields.
	 *
	 * @return void|string
	 */
	public static function rtFieldGenerator( $fields = [] ) {
		$html = null;
		if ( is_array( $fields ) && ! empty( $fields ) ) {
			$tlpField = new Fields();
			foreach ( $fields as $fieldKey => $field ) {
				$html .= $tlpField->Field( $fieldKey, $field );
			}
		}
		return $html;
	}

	/**
	 * Checks if metadata exists.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $type Post type.
	 *
	 * @return Boolean
	 */
	public static function meta_exist( $post_id, $meta_key, $type = 'post' ) {
		if ( ! $post_id ) {
			return false;
		}

		return metadata_exists( $type, $post_id, $meta_key );
	}

	public static function getScTeamMetaFields() {
		return array_merge(
			Options::get_sc_layout_settings_meta_fields(),
			Options::get_sc_query_filter_meta_fields(),
			Options::get_sc_field_selection_meta(),
			Options::get_sc_field_style_meta()
		);
	}

	public static function tlpAllMemberInfoFields() {
		$fields  = [];
		$fieldsA = Options::teamMemberInfoField();
		foreach ( $fieldsA as $field ) {
			if ( is_array( $field ) ) {
				$fields[] = $field['name'];
			}
		}

		return $fields;
	}


    public static function getMemberList() {
        $members = [];
        $memberQ = get_posts(
            [
                'post_type'      => rttlp_team()->post_type,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );
        if ( ! empty( $memberQ ) && is_array( $memberQ ) ) {
            foreach ( $memberQ as $member ) {
                $members[ $member->ID ] = $member->post_title;
            }
        }
        return $members;
    }

	public static function getTTPShortcodeList() {
		$scList = null;
		$scQ    = get_posts(
			[
				'post_type'      => rttlp_team()->shortCodePT,
				'order_by'       => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			]
		);
		if ( ! empty( $scQ ) ) {
			foreach ( $scQ as $sc ) {
				$scList[ $sc->ID ] = $sc->post_title;
			}
		}

		return $scList;
	}

	/**
	 * @param $post_id
	 * @param $mates
	 * @param $request
	 * Update meta fields
	 */
	public static function updateMetaFields( $post_id, $mates, $request ) {
		if ( is_array( $mates ) && ! empty( $mates ) ) {
			foreach ( $mates as $metaKey => $field ) {
				$rValue = ! empty( $request[ $metaKey ] ) ? $request[ $metaKey ] : null;
				$value  = self::sanitize( $field, $rValue );
				if ( empty( $field['multiple'] ) ) {
					update_post_meta( $post_id, $metaKey, $value );
				} else {
					delete_post_meta( $post_id, $metaKey );
					if ( is_array( $value ) && ! empty( $value ) ) {
						foreach ( $value as $item ) {
							add_post_meta( $post_id, $metaKey, $item );
						}
					}
				}
			}
		}
	}

	/**
	 * Sanitize field value
	 *
	 * @param array $field
	 * @param null  $value
	 *
	 * @return array|null
	 * @internal param $value
	 */
	public static function sanitize( $field = [], $value = null ) {
		$newValue = null;
		if ( is_array( $field ) ) {
			$type = ( ! empty( $field['type'] ) ? esc_attr( $field['type'] ) : 'text' );
			if ( empty( $field['multiple'] ) ) {
				if ( $type == 'text' || $type == 'number' || $type == 'select' || $type == 'checkbox' || $type == 'radio' ) {
					$newValue = sanitize_text_field( $value );
				} elseif ( $type == 'email' ) {
					$newValue = sanitize_email( $value );
				} elseif ( $type == 'url' ) {
					$newValue = esc_url( $value );
				} elseif ( $type == 'slug' ) {
					$newValue = sanitize_title_with_dashes( $value );
				} elseif ( $type == 'textarea' ) {
					$newValue = wp_kses_post( $value );
				} elseif ( $type == 'custom_css' ) {
					$newValue = esc_textarea( $value );
				} elseif ( $type == 'colorpicker' ) {
					$newValue = self::sanitize_hex_color( $value );
				} elseif ( $type == 'image_size' ) {
					$newValue = [];
					foreach ( $value as $k => $v ) {
						if ( $k == 'width' || $k == 'height' ) {
							$newValue[ $k ] = absint( $v );
						} else {
							$newValue[ $k ] = esc_attr( $v );
						}
					}
				} elseif ( $type == 'style' || $type == 'multiple_options' ) {
					$newValue = [];
					foreach ( $value as $k => $v ) {
						$nV = null;
						if ( $k == 'color' ) {
							$nV = self::sanitize_hex_color( $v );
						} else {
							$nV = self::sanitize( [ 'type' => 'text' ], $v );
						}
						if ( $nV ) {
							$newValue[ $k ] = $nV;
						}
					}
					if ( empty( $newValue ) ) {
						$newValue = null;
					}
				} else {
					$newValue = sanitize_text_field( $value );
				}
			} else {
				$newValue = [];
				if ( ! empty( $value ) ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $key => $val ) {
							if ( $type == 'style' && $key == 0 ) {
								if ( function_exists( 'sanitize_hex_color' ) ) {
									$newValue = sanitize_hex_color( $val );
								} else {
									$newValue[] = self::sanitize_hex_color( $val );
								}
							} else {
								$newValue[] = sanitize_text_field( $val );
							}
						}
					} else {
						$newValue[] = sanitize_text_field( $value );
					}
				}
			}
		}
		return $newValue;
	}

	public static function sanitize_hex_color( $color ) {
		if ( function_exists( 'sanitize_hex_color' ) ) {
			return sanitize_hex_color( $color );
		} else {
			if ( '' === $color ) {
				return '';
			}

			// 3 or 6 hex digits, or the empty string.
			if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
				return $color;
			}
		}
	}

	public static function custom_pagination( $pages = '', $range = 4, $page_num = null ) {
		$html      = null;
		$showitems = ( $range * 2 ) + 1;
		global $paged;
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}
		if ( empty( $paged ) ) {
			$paged = 1;
		}

		if ( $pages == '' ) {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if ( ! $pages ) {
				$pages = 1;
			}
		}

		if ( 1 != $pages ) {

			$html .= '<div class="tlp-pagination"><ul class="pagination">';

			if ( $page_num ) {
				$html .= '<li class="disabled hidden-xs"><span><span aria-hidden="true">Page ' . $paged . ' of ' . $pages . '</span></span></li>';
			}

			if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) {
				$html .= "<li><a href='" . get_pagenum_link( 1 ) . "' aria-label='First'>&laquo;<span class='hidden-xs'> First</span></a></li>";
			}

			if ( $paged > 1 && $showitems < $pages ) {
				$html .= "<li><a href='" . get_pagenum_link( $paged - 1 ) . "' aria-label='Previous'>&lsaquo;<span class='hidden-xs'> Previous</span></a></li>";
			}

			for ( $i = 1; $i <= $pages; $i ++ ) {
				if ( 1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
					$html .= ( $paged == $i ) ? '<li class="active"><span>' . $i . '</span></li>' : "<li><a href='" . get_pagenum_link( $i ) . "'>" . $i . '</a></li>';
				}
			}

			if ( $paged < $pages && $showitems < $pages ) {
				$html .= '<li><a href="' . get_pagenum_link( $paged + 1 ) . "\"  aria-label='Next'><span class='hidden-xs'>Next </span>&rsaquo;</a></li>";
			}

			if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
				$html .= "<li><a href='" . get_pagenum_link( $pages ) . "' aria-label='Last'><span class='hidden-xs'>Last </span>&raquo;</a></li>";
			}

			$html .= '</ul>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * The member's photo set, in display order: the featured image first (unless the
	 * `remove_feature_image` field is on) followed by the gallery images.
	 *
	 * Extracted so the multi popup's own slider shows exactly the same photos, in the
	 * same order, as `memberDetailGallery()` — they used to be two copies of this rule.
	 *
	 * @param int $post_id Team member ID.
	 *
	 * @return array Attachment IDs.
	 */
	public static function memberGalleryImageIds( $post_id = null ) {
		if ( ! $post_id ) {
			return [];
		}

		$settings  = get_option( rttlp_team()->options['settings'] );
		$fields    = isset( $settings['detail_page_fields'] ) ? $settings['detail_page_fields'] : [];
		$image_ids = get_post_meta( $post_id, 'tlp_team_gallery' );
		$image_ids = is_array( $image_ids ) ? $image_ids : [];
		$fID       = get_post_thumbnail_id( $post_id );

		// The featured image only leads the gallery; with no gallery images at all it is
		// still the member's single photo, so it is kept regardless of the field.
		if ( $fID && ( ! $image_ids || ! in_array( 'remove_feature_image', $fields, true ) ) ) {
			array_unshift( $image_ids, $fID );
		}

		return array_values( array_unique( array_filter( array_map( 'absint', $image_ids ) ) ) );
	}

	public static function memberDetailGallery( $post_id = null ) {
		if ( ! $post_id ) {
			return;
		}
		$html         = '';
		$settings     = get_option( rttlp_team()->options['settings'] );
		$fields       = isset( $settings['detail_page_fields'] ) ? $settings['detail_page_fields'] : [];
		$show_caption = ! empty( $settings['detail_image_caption'] );
		$image_ids    = get_post_meta( $post_id, 'tlp_team_gallery' );

		if ( ! empty( $image_ids ) && is_array( $image_ids ) ) {
			$image_ids = self::memberGalleryImageIds( $post_id );

			$sliderOption = self::swiper_options();

			$html .= "<div id='team-member-profile-gallery' class='rt-carousel-holder swiper rttm-carousel-slider rt-pos-s' data-options='" . wp_json_encode( $sliderOption ) . "'>";
			$html .= '<div class="swiper-wrapper">';
			foreach ( $image_ids as $id ) {
				$img_alt  = trim( wp_strip_all_tags( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );
				$alt_tag  = ! empty( $img_alt ) ? $img_alt : get_the_title( $post_id );
                $image_html = wp_get_attachment_image( $id, 'large', false, [
                    'alt' => $alt_tag
                ]);
				$full_url = wp_get_attachment_image_src( $id, 'large' );
				if ( isset( $full_url[0] ) ) {
					$html .= '<div class="swiper-slide">';
					$html .= '<div class="profile-img-wrapper">';
					$html .=  $image_html;;
					$html .= '</div>';
					if ( $show_caption ) {
						$caption = wp_get_attachment_caption( $id );
						if ( $caption ) {
							$html .= '<figcaption class="wp-caption-text">' . wp_kses_post( $caption ) . '</figcaption>';
						}
					}
					$html .= '</div>';
				}
			}
			$html .= '</div>';
			// Only worth drawing when there is somewhere to slide to. This used to be
			// unconditional, so a member with a single photo got a full set of arrows and
			// a lone pagination bullet that did nothing — and, because the no-gallery
			// branch below draws no controls at all, two members with one photo each
			// looked different depending on whether that photo came from the gallery or
			// the featured image. The multi and smart popups gate their own controls the
			// same way.
			if ( count( $image_ids ) > 1 ) {
				$html .= '<div class="swiper-arrow swiper-button-next"><i class="fa fa-chevron-right"></i></div>';
				$html .= '<div class="swiper-arrow swiper-button-prev"><i class="fa fa-chevron-left"></i></div>';
				$html .= '<div class="swiper-pagination"></div>';
			}
			$html .= '</div>';
		} else {
			if ( has_post_thumbnail( $post_id ) ) {
                $html .= '<div class="tlp-single-img-wrapper">';
				$html .= get_the_post_thumbnail( $post_id, 'large' );
				if ( $show_caption ) {
					$caption = wp_get_attachment_caption( get_post_thumbnail_id( $post_id ) );
					if ( $caption ) {
						$html .= '<figcaption class="wp-caption-text">' . wp_kses_post( $caption ) . '</figcaption>';
					}
				}
                $html .='</div>';
			} else {
				/*
				 * No gallery and no featured image. The grid already substitutes the
				 * shortcode's "Default preview image" here (Fns::getFeatureImageHtml via the
				 * $defaultImgId argument); the detail page rendered nothing at all, so the
				 * same member looked fine in the grid and imageless once opened.
				 *
				 * No caption: this is a placeholder, not the member's own photo, so the
				 * attachment's caption would be describing the wrong thing.
				 */
				$default_id = self::singlePageDefaultImageId();

				if ( $default_id ) {
					$html .= '<div class="tlp-single-img-wrapper">';
					$html .= wp_get_attachment_image( $default_id, 'large', false, [ 'alt' => get_the_title( $post_id ) ] );
					$html .= '</div>';
				}
			}
		}
        return apply_filters( 'tlp_team_member_detail_gallery', $html, $post_id, $image_ids );
	}

	/**
	 * Body markup for the SINGLE detail popup — the two-column "detail popup" design:
	 * photo gallery on the left, scrollable details on the right (role pill, name,
	 * bio, contact list, then everything else), with a footer holding the social
	 * chips, the Resume/Hire buttons and the member pager.
	 *
	 * Both paths render this one builder — free's Frontend\Ajax\SinglePopup and pro's
	 * AjaxController::mdPopupSingle — so the two can never drift. Only the container
	 * class differs, because the shortcode and Elementor stylesheets scope on it.
	 *
	 * Every field is still produced by the existing `get_formatted_*` helpers, so the
	 * per-field Style controls (which target `.contact-info li`, `.social-icons a`,
	 * `.short-bio`, `.tlp-team-skill`, `.readmore-btn a`, `h3`, `h4` …) keep working;
	 * the redesign is structure + CSS, not new markup hooks.
	 *
	 * @param int   $post_id  Team member ID.
	 * @param array $fields   Enabled detail-page fields.
	 * @param array $settings Plugin settings (for the button labels).
	 * @param bool  $is_el    True for the Elementor path.
	 *
	 * @return string
	 */
	public static function singlePopupMarkup( $post_id, $fields = [], $settings = [], $is_el = false ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$container = $is_el ? 'rt-elementor-container' : 'rt-team-container';

		$name        = $post->post_title;
		$designation = wp_strip_all_tags( get_the_term_list( $post_id, rttlp_team()->taxonomies['designation'], null, ', ' ) );
		$experience  = get_post_meta( $post_id, 'experience_year', true );
		$tag_line    = get_post_meta( $post_id, 'ttp_tag_line', true );
		$short_bio   = get_post_meta( $post_id, 'short_bio', true );
		$skill       = get_post_meta( $post_id, 'skill', true );
		$skill       = $skill ? maybe_unserialize( $skill ) : [];
		$sLink       = get_post_meta( $post_id, 'social', true );
		$sLink       = $sLink ? $sLink : [];

		$resume_url  = get_post_meta( $post_id, 'ttp_my_resume', true );
		$hire_me_url = get_post_meta( $post_id, 'ttp_hire_me', true );
		$resume_text = isset( $settings['resume_btn_text'] ) ? $settings['resume_btn_text'] : __( 'Resume', 'tlp-team' );
		$hire_text   = isset( $settings['hire_me_text'] ) ? $settings['hire_me_text'] : __( 'Hire Me', 'tlp-team' );

		$html = '<div class="rttm-pop ' . esc_attr( $container ) . '" data-member="' . absint( $post_id ) . '">';

		// Covers the whole card until the runtime adds `is-ready`. The AJAX returns well
		// before the photos decode, so without this the modal scales in empty and the
		// content — then the image — snap in mid-animation.
		$html .= '<span class="rttm-pop-spinner" aria-hidden="true"></span>';

		// ---------- Gallery (left) ----------
		// memberDetailGallery() emits the swiper when the member has gallery images and a
		// plain `.tlp-single-img-wrapper` with the featured image when they do not — both
		// shapes are sized by the stylesheet.
		$html .= '<div class="rttm-pop-gallery">'
			. self::memberDetailGallery( $post_id )
			. '</div>';

		// ---------- Details (right, scrollable) ----------
		$html .= '<div class="rttm-pop-body">';
		$html .= '<div class="md-header">';

		// Designation and Department sit together as pills. `department` is enabled by
		// default in Settings → Field Selection but the old popup never rendered it, so
		// the setting silently did nothing — hence the explicit arm here.
		$pills = '';

		if ( $designation && in_array( 'designation', $fields, true ) ) {
			$exp = ( $experience && in_array( 'experience_year', $fields, true ) )
				? '<span class="experience">(' . esc_html( $experience ) . ')</span>'
				: null;
			$pills .= '<h4 class="title-experience rttm-pop-role">' . esc_html( $designation ) . self::htmlKses( $exp, 'basic' ) . '</h4>';
		}

		if ( in_array( 'department', $fields, true ) ) {
			$department = wp_strip_all_tags( get_the_term_list( $post_id, rttlp_team()->taxonomies['department'], null, ', ' ) );
			if ( $department ) {
				$pills .= '<div class="tlp-department rttm-pop-dept">' . esc_html( $department ) . '</div>';
			}
		}

		$html .= $pills ? '<div class="rttm-pop-pills">' . $pills . '</div>' : '';

		$html .= '<h3 class="tlp-title rttm-pop-name">' . esc_html( $name ) . '</h3>';

		if ( $tag_line && in_array( 'ttp_tag_line', $fields, true ) ) {
			$html .= '<div class="tlp-tag-line">' . self::htmlKses( $tag_line, 'basic' ) . '</div>';
		}

		$html .= '</div>'; // .md-header

		$html .= self::get_formatted_short_bio( $short_bio, $fields );

		if ( $post->post_content && in_array( 'content', $fields, true ) ) {
			$html .= '<div class="tlp-md-member-details">' . apply_filters( 'the_content', $post->post_content ) . '</div>';
		}

		$html .= self::get_formatted_contact_info(
			[
				'email'     => get_post_meta( $post_id, 'email', true ),
				'telephone' => get_post_meta( $post_id, 'telephone', true ),
				'mobile'    => get_post_meta( $post_id, 'mobile', true ),
				'fax'       => get_post_meta( $post_id, 'fax', true ),
				'location'  => get_post_meta( $post_id, 'location', true ),
				'web_url'   => get_post_meta( $post_id, 'web_url', true ),
			],
			$fields
		);

		// Everything the reference design does not show is kept, below the contact list.
		$extra = [
			'ttp_qualifications'             => __( 'Qualifications : ', 'tlp-team' ),
			'ttp_professional_memberships'   => __( 'Professional Memberships : ', 'tlp-team' ),
			'ttp_area_of_expertise'          => __( 'Area of Expertise : ', 'tlp-team' ),
		];

		foreach ( $extra as $key => $label ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( $value && in_array( $key, $fields, true ) ) {
				$html .= '<div class="rt-extra-curriculum"><strong>' . esc_html( $label ) . '</strong>' . self::htmlKses( $value, 'basic' ) . '</div>';
			}
		}

		$html .= self::get_formatted_skill( $skill, $fields );

		// Settings → Field Selection is authoritative: every enabled field renders here
		// and every disabled one does not.
		if ( in_array( 'author_post', $fields, true ) ) {
			$html .= self::memberDetailPosts( $post_id );
		}

		// ---------- Footer ----------
		$social  = self::get_formatted_social_link( $sLink, $fields );
		$resume  = ( $resume_url && in_array( 'resume_btn', $fields, true ) && $resume_text );
		$hire_me = ( $hire_me_url && in_array( 'hire_me_btn', $fields, true ) && $hire_text );

		$buttons = '';

		// `readmore_btn` is offered in Settings → Field Selection for the detail page, so
		// honour it here too: a link through to the member's own page. Without this the
		// checkbox was simply inert in the popup.
		if ( in_array( 'readmore_btn', $fields, true ) ) {
			$readmore_text = isset( $settings['readmore_btn_text'] ) && $settings['readmore_btn_text']
				? $settings['readmore_btn_text']
				: __( 'Read More', 'tlp-team' );
			$buttons      .= '<a class="rt-ream-me-btn rttm-pop-readmore" target="_self" title="' . esc_attr( $readmore_text ) . '" href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $readmore_text ) . '</a>';
		}

		if ( $resume ) {
			$buttons .= '<a class="rt-resume-btn" target="_self" title="' . esc_attr( $resume_text ) . '" href="' . esc_url( $resume_url ) . '">' . esc_html( $resume_text ) . '</a>';
		}
		if ( $hire_me ) {
			$buttons .= '<a class="rt-hire-btn" target="_self" title="' . esc_attr( $hire_text ) . '" href="' . esc_url( $hire_me_url ) . '">' . esc_html( $hire_text ) . '</a>';
		}

		$html .= '<div class="rttm-pop-foot">';
		$html .= $social ? $social : '';
		$html .= $buttons ? '<div class="readmore-btn rttm-pop-actions">' . $buttons . '</div>' : '';
		// Member pager. The runtime fills in / disables these from the trigger list in
		// the container, so it renders even when there is only one member and is hidden
		// by the JS in that case.
		$html .= '<div class="rttm-pop-pager">'
			. '<button type="button" class="rttm-pop-nav rttm-pop-prev" aria-label="' . esc_attr__( 'Previous member', 'tlp-team' ) . '"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>'
			. '<button type="button" class="rttm-pop-nav rttm-pop-next" aria-label="' . esc_attr__( 'Next member', 'tlp-team' ) . '"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>'
			. '</div>';
		$html .= '</div>'; // .rttm-pop-foot

		$html .= '</div>'; // .rttm-pop-body
		$html .= '</div>'; // .rttm-pop

		return apply_filters( 'rttm_single_popup_markup', $html, $post_id, $fields, $is_el );
	}

	/**
	 * One member's panel inside the MULTIPLE popup — the fullscreen viewer.
	 *
	 * Shared by both AJAX handlers (free `Frontend\Ajax\MultiPopup` and pro
	 * `AjaxController::multiPopup`) so the two paths cannot drift; only the container
	 * class differs, because the shortcode and Elementor stylesheets scope on it.
	 *
	 * The viewer SHELL — top bar, stage, member thumbnail strip — is built by the
	 * runtime, not here: it survives across members while this markup is swapped out on
	 * every step, so it must not be part of the AJAX payload.
	 *
	 * The gallery is a plain cross-fade slider rather than `memberDetailGallery()`'s
	 * swiper. Swiper has to measure its container, which is unreliable while the viewer
	 * is still fading in, and the design wants its own arrow/dot treatment; the photo
	 * list still comes from `memberGalleryImageIds()`, so both popups show the same
	 * images in the same order.
	 *
	 * Field markup comes from the existing `get_formatted_*` helpers, so the per-field
	 * Style controls (`.contact-info li`, `.social-icons a`, `.short-bio`,
	 * `.tlp-team-skill`, `.readmore-btn a`, `h3`, `h4`) keep working.
	 *
	 * @param int   $post_id  Team member ID.
	 * @param array $fields   Enabled detail-page fields.
	 * @param array $settings Plugin settings (for the button labels).
	 * @param bool  $is_el    True for the Elementor path.
	 *
	 * @return string
	 */
	public static function multiPopupMarkup( $post_id, $fields = [], $settings = [], $is_el = false ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$container = $is_el ? 'rt-elementor-container' : 'rt-team-container';

		$name        = $post->post_title;
		$designation = wp_strip_all_tags( get_the_term_list( $post_id, rttlp_team()->taxonomies['designation'], null, ', ' ) );
		$experience  = get_post_meta( $post_id, 'experience_year', true );
		$tag_line    = get_post_meta( $post_id, 'ttp_tag_line', true );
		$short_bio   = get_post_meta( $post_id, 'short_bio', true );
		$skill       = get_post_meta( $post_id, 'skill', true );
		$skill       = $skill ? maybe_unserialize( $skill ) : [];
		$sLink       = get_post_meta( $post_id, 'social', true );
		$sLink       = $sLink ? $sLink : [];

		$resume_url  = get_post_meta( $post_id, 'ttp_my_resume', true );
		$hire_me_url = get_post_meta( $post_id, 'ttp_hire_me', true );
		$resume_text = isset( $settings['resume_btn_text'] ) ? $settings['resume_btn_text'] : __( 'Resume', 'tlp-team' );
		$hire_text   = isset( $settings['hire_me_text'] ) ? $settings['hire_me_text'] : __( 'Hire Me', 'tlp-team' );

		$show_caption = ! empty( $settings['detail_image_caption'] );
		$image_ids    = self::memberGalleryImageIds( $post_id );

		/*
		 * Same fallback the detail page uses: a member with no gallery and no featured image
		 * showed the shortcode's "Default preview image" in the grid and nothing here.
		 *
		 * It comes from the Settings source rather than from the shortcode that opened the
		 * popup, because the popup never learns which one that was -- both AJAX payloads carry
		 * only the member id and the nonce, so threading a shortcode ID through would mean
		 * changing all four runtimes and all four handlers.
		 *
		 * Captions are switched off with it: this is a placeholder, so the attachment's own
		 * caption would be describing the wrong person. It is the only image in this branch,
		 * so nothing else loses its caption.
		 */
		if ( ! $image_ids ) {
			$default_id = self::singlePageDefaultImageId();

			if ( $default_id ) {
				$image_ids    = [ $default_id ];
				$show_caption = false;
			}
		}

		$classes = 'rttm-mpop-card ' . $container;
		if ( ! $image_ids ) {
			// Nothing to show on the left, so the details take the full stage width.
			$classes .= ' rttm-mpop-card--nogallery';
		}

		$html = '<div class="' . esc_attr( $classes ) . '" data-member="' . absint( $post_id ) . '">';

		// ---------- Gallery (left) ----------
		if ( $image_ids ) {
			$html .= '<div class="rttm-mpop-gallery">';
			$html .= '<div class="rttm-mpop-frame">';

			foreach ( $image_ids as $i => $id ) {
				$img_alt = trim( wp_strip_all_tags( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );
				$alt_tag = $img_alt ? $img_alt : get_the_title( $post_id );
				$image   = wp_get_attachment_image( $id, 'large', false, [ 'alt' => $alt_tag ] );

				if ( ! $image ) {
					continue;
				}

				$html .= '<div class="rttm-mpop-slide' . ( 0 === $i ? ' is-active' : '' ) . '">';
				$html .= $image;

				if ( $show_caption ) {
					$caption = wp_get_attachment_caption( $id );
					if ( $caption ) {
						$html .= '<figcaption class="wp-caption-text">' . wp_kses_post( $caption ) . '</figcaption>';
					}
				}

				$html .= '</div>';
			}

			$html .= '</div>'; // .rttm-mpop-frame

			// A single photo needs no controls.
			if ( count( $image_ids ) > 1 ) {
				$html .= '<button type="button" class="rttm-mpop-garrow prev" aria-label="' . esc_attr__( 'Previous photo', 'tlp-team' ) . '"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>';
				$html .= '<button type="button" class="rttm-mpop-garrow next" aria-label="' . esc_attr__( 'Next photo', 'tlp-team' ) . '"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';
				$html .= '<div class="rttm-mpop-dots">';
				foreach ( $image_ids as $i => $id ) {
					$html .= '<button type="button" class="rttm-mpop-dot' . ( 0 === $i ? ' is-active' : '' ) . '" data-i="' . absint( $i ) . '" aria-label="'
						/* translators: %d: photo number. */
						. esc_attr( sprintf( __( 'Photo %d', 'tlp-team' ), $i + 1 ) ) . '"></button>';
				}
				$html .= '</div>';
			}

			$html .= '</div>'; // .rttm-mpop-gallery
		}

		// ---------- Details (right) ----------
		$html .= '<div class="rttm-mpop-details">';

		// Designation and Department sit together as pills, as on the single popup —
		// `department` is enabled by default in Settings → Field Selection.
		$pills = '';

		if ( $designation && in_array( 'designation', $fields, true ) ) {
			$exp = ( $experience && in_array( 'experience_year', $fields, true ) )
				? '<span class="experience">(' . esc_html( $experience ) . ')</span>'
				: null;
			$pills .= '<h4 class="title-experience rttm-mpop-role">' . esc_html( $designation ) . self::htmlKses( $exp, 'basic' ) . '</h4>';
		}

		if ( in_array( 'department', $fields, true ) ) {
			$department = wp_strip_all_tags( get_the_term_list( $post_id, rttlp_team()->taxonomies['department'], null, ', ' ) );
			if ( $department ) {
				$pills .= '<div class="tlp-department rttm-mpop-dept">' . esc_html( $department ) . '</div>';
			}
		}

		$html .= $pills ? '<div class="rttm-mpop-pills">' . $pills . '</div>' : '';

		if ( in_array( 'name', $fields, true ) ) {
			$html .= '<h3 class="tlp-title rttm-mpop-name">' . esc_html( $name ) . '</h3>';
		}

		if ( $tag_line && in_array( 'ttp_tag_line', $fields, true ) ) {
			$html .= '<div class="tlp-tag-line">' . self::htmlKses( $tag_line, 'basic' ) . '</div>';
		}

		$html .= self::get_formatted_short_bio( $short_bio, $fields );

		if ( $post->post_content && in_array( 'content', $fields, true ) ) {
			$html .= '<div class="tlp-md-member-details">' . apply_filters( 'the_content', $post->post_content ) . '</div>';
		}

		$html .= self::get_formatted_contact_info(
			[
				'email'     => get_post_meta( $post_id, 'email', true ),
				'telephone' => get_post_meta( $post_id, 'telephone', true ),
				'mobile'    => get_post_meta( $post_id, 'mobile', true ),
				'fax'       => get_post_meta( $post_id, 'fax', true ),
				'location'  => get_post_meta( $post_id, 'location', true ),
				'web_url'   => get_post_meta( $post_id, 'web_url', true ),
			],
			$fields
		);

		// Everything the reference design does not show is kept, below the contact list.
		$extra = [
			'ttp_qualifications'           => __( 'Qualifications : ', 'tlp-team' ),
			'ttp_professional_memberships' => __( 'Professional Memberships : ', 'tlp-team' ),
			'ttp_area_of_expertise'        => __( 'Area of Expertise : ', 'tlp-team' ),
		];

		foreach ( $extra as $key => $label ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( $value && in_array( $key, $fields, true ) ) {
				$html .= '<div class="rt-extra-curriculum"><strong>' . esc_html( $label ) . '</strong>' . self::htmlKses( $value, 'basic' ) . '</div>';
			}
		}

		$html .= self::get_formatted_skill( $skill, $fields );

		if ( in_array( 'author_post', $fields, true ) ) {
			$html .= self::memberDetailPosts( $post_id );
		}

		// ---------- Footer: socials + buttons ----------
		$social  = self::get_formatted_social_link( $sLink, $fields );
		$resume  = ( $resume_url && in_array( 'resume_btn', $fields, true ) && $resume_text );
		$hire_me = ( $hire_me_url && in_array( 'hire_me_btn', $fields, true ) && $hire_text );

		$buttons = '';

		if ( in_array( 'readmore_btn', $fields, true ) ) {
			$readmore_text = isset( $settings['readmore_btn_text'] ) && $settings['readmore_btn_text']
				? $settings['readmore_btn_text']
				: __( 'Read More', 'tlp-team' );
			$buttons      .= '<a class="rt-ream-me-btn rttm-mpop-readmore" target="_self" title="' . esc_attr( $readmore_text ) . '" href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $readmore_text ) . '</a>';
		}

		if ( $resume ) {
			$buttons .= '<a class="rt-resume-btn" target="_self" title="' . esc_attr( $resume_text ) . '" href="' . esc_url( $resume_url ) . '">' . esc_html( $resume_text ) . '</a>';
		}
		if ( $hire_me ) {
			$buttons .= '<a class="rt-hire-btn" target="_self" title="' . esc_attr( $hire_text ) . '" href="' . esc_url( $hire_me_url ) . '">' . esc_html( $hire_text ) . '</a>';
		}

		if ( $social || $buttons ) {
			$html .= '<div class="rttm-mpop-foot">';
			$html .= $social ? $social : '';
			$html .= $buttons ? '<div class="readmore-btn rttm-mpop-actions">' . $buttons . '</div>' : '';
			$html .= '</div>';
		}

		$html .= '</div>'; // .rttm-mpop-details
		$html .= '</div>'; // .rttm-mpop-card

		return apply_filters( 'rttm_multi_popup_markup', $html, $post_id, $fields, $is_el );
	}

	/**
	 * One member's panel inside the SMART popup — the right-hand drawer.
	 *
	 * Shared by both AJAX handlers (free `Frontend\Ajax\SmartPopup` and pro
	 * `AjaxController::smartPopup`) so the two paths cannot drift; only the container
	 * class differs, because the shortcode and Elementor stylesheets scope on it.
	 *
	 * The drawer SHELL — backdrop, top bar, scrolling body — is built by the runtime and
	 * survives across members, so it is deliberately not part of this payload. The
	 * legacy wrappers `.rt-smart-modal-main-content`, `.team-images` and
	 * `.member-details` are kept: user Style controls and the runtime both hook them.
	 *
	 * Like the multi popup this builds a plain cross-fade hero rather than
	 * `memberDetailGallery()`'s swiper — swiper has to measure a container that is
	 * still sliding in — but the photo list comes from `memberGalleryImageIds()`, so
	 * every popup shows the same images in the same order.
	 *
	 * @param int   $post_id  Team member ID.
	 * @param array $fields   Enabled detail-page fields.
	 * @param array $settings Plugin settings (for the button labels).
	 * @param bool  $is_el    True for the Elementor path.
	 *
	 * @return string
	 */
	public static function smartPopupMarkup( $post_id, $fields = [], $settings = [], $is_el = false ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$container = $is_el ? 'rt-elementor-container' : 'rt-team-container';

		$name        = $post->post_title;
		$designation = wp_strip_all_tags( get_the_term_list( $post_id, rttlp_team()->taxonomies['designation'], null, ', ' ) );
		$experience  = get_post_meta( $post_id, 'experience_year', true );
		$tag_line    = get_post_meta( $post_id, 'ttp_tag_line', true );
		$short_bio   = get_post_meta( $post_id, 'short_bio', true );
		$skill       = get_post_meta( $post_id, 'skill', true );
		$skill       = $skill ? maybe_unserialize( $skill ) : [];
		$sLink       = get_post_meta( $post_id, 'social', true );
		$sLink       = $sLink ? $sLink : [];

		$resume_url  = get_post_meta( $post_id, 'ttp_my_resume', true );
		$hire_me_url = get_post_meta( $post_id, 'ttp_hire_me', true );
		$resume_text = isset( $settings['resume_btn_text'] ) ? $settings['resume_btn_text'] : __( 'Resume', 'tlp-team' );
		$hire_text   = isset( $settings['hire_me_text'] ) ? $settings['hire_me_text'] : __( 'Hire Me', 'tlp-team' );

		$show_caption = ! empty( $settings['detail_image_caption'] );
		$image_ids    = self::memberGalleryImageIds( $post_id );

		/*
		 * Same fallback the detail page uses: a member with no gallery and no featured image
		 * showed the shortcode's "Default preview image" in the grid and nothing here.
		 *
		 * It comes from the Settings source rather than from the shortcode that opened the
		 * popup, because the popup never learns which one that was -- both AJAX payloads carry
		 * only the member id and the nonce, so threading a shortcode ID through would mean
		 * changing all four runtimes and all four handlers.
		 *
		 * Captions are switched off with it: this is a placeholder, so the attachment's own
		 * caption would be describing the wrong person. It is the only image in this branch,
		 * so nothing else loses its caption.
		 */
		if ( ! $image_ids ) {
			$default_id = self::singlePageDefaultImageId();

			if ( $default_id ) {
				$image_ids    = [ $default_id ];
				$show_caption = false;
			}
		}

		$html = '<div class="rt-smart-modal-main-content rttm-sp-panel ' . esc_attr( $container ) . '" data-member="' . absint( $post_id ) . '">';

		// ---------- hero ----------
		if ( $image_ids ) {
			$html .= '<div class="team-images rttm-sp-hero">';

			foreach ( $image_ids as $i => $id ) {
				$img_alt = trim( wp_strip_all_tags( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );
				$alt_tag = $img_alt ? $img_alt : get_the_title( $post_id );
				$image   = wp_get_attachment_image( $id, 'large', false, [ 'alt' => $alt_tag ] );

				if ( ! $image ) {
					continue;
				}

				$html .= '<div class="rttm-sp-slide' . ( 0 === $i ? ' is-active' : '' ) . '">';
				$html .= $image;

				if ( $show_caption ) {
					$caption = wp_get_attachment_caption( $id );
					if ( $caption ) {
						$html .= '<figcaption class="wp-caption-text">' . wp_kses_post( $caption ) . '</figcaption>';
					}
				}

				$html .= '</div>';
			}

			// A single photo needs no controls.
			if ( count( $image_ids ) > 1 ) {
				$html .= '<button type="button" class="rttm-sp-garrow prev" aria-label="' . esc_attr__( 'Previous photo', 'tlp-team' ) . '"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>';
				$html .= '<button type="button" class="rttm-sp-garrow next" aria-label="' . esc_attr__( 'Next photo', 'tlp-team' ) . '"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>';
				$html .= '<div class="rttm-sp-dots">';
				foreach ( $image_ids as $i => $id ) {
					$html .= '<button type="button" class="rttm-sp-dot' . ( 0 === $i ? ' is-active' : '' ) . '" data-i="' . absint( $i ) . '" aria-label="'
						/* translators: %d: photo number. */
						. esc_attr( sprintf( __( 'Photo %d', 'tlp-team' ), $i + 1 ) ) . '"></button>';
				}
				$html .= '</div>';
			}

			$html .= '</div>'; // .rttm-sp-hero
		}

		// ---------- details ----------
		$html .= '<div class="member-details rttm-sp-body">';

		$pills = '';

		if ( $designation && in_array( 'designation', $fields, true ) ) {
			$exp = ( $experience && in_array( 'experience_year', $fields, true ) )
				? '<span class="experience">(' . esc_html( $experience ) . ')</span>'
				: null;
			$pills .= '<h4 class="title-experience rttm-sp-role">' . esc_html( $designation ) . self::htmlKses( $exp, 'basic' ) . '</h4>';
		}

		if ( in_array( 'department', $fields, true ) ) {
			$department = wp_strip_all_tags( get_the_term_list( $post_id, rttlp_team()->taxonomies['department'], null, ', ' ) );
			if ( $department ) {
				$pills .= '<div class="tlp-department rttm-sp-dept">' . esc_html( $department ) . '</div>';
			}
		}

		$html .= $pills ? '<div class="rttm-sp-pills">' . $pills . '</div>' : '';

		if ( in_array( 'name', $fields, true ) ) {
			$html .= '<h3 class="tlp-title rttm-sp-name">' . esc_html( $name ) . '</h3>';
		}

		if ( $tag_line && in_array( 'ttp_tag_line', $fields, true ) ) {
			$html .= '<div class="tlp-tag-line">' . self::htmlKses( $tag_line, 'basic' ) . '</div>';
		}

		$html .= self::get_formatted_short_bio( $short_bio, $fields );

		if ( $post->post_content && in_array( 'content', $fields, true ) ) {
			$html .= '<div class="tlp-md-member-details">' . apply_filters( 'the_content', $post->post_content ) . '</div>';
		}

		$html .= self::get_formatted_contact_info(
			[
				'email'     => get_post_meta( $post_id, 'email', true ),
				'telephone' => get_post_meta( $post_id, 'telephone', true ),
				'mobile'    => get_post_meta( $post_id, 'mobile', true ),
				'fax'       => get_post_meta( $post_id, 'fax', true ),
				'location'  => get_post_meta( $post_id, 'location', true ),
				'web_url'   => get_post_meta( $post_id, 'web_url', true ),
			],
			$fields
		);

		$extra = [
			'ttp_qualifications'           => __( 'Qualifications : ', 'tlp-team' ),
			'ttp_professional_memberships' => __( 'Professional Memberships : ', 'tlp-team' ),
			'ttp_area_of_expertise'        => __( 'Area of Expertise : ', 'tlp-team' ),
		];

		foreach ( $extra as $key => $label ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( $value && in_array( $key, $fields, true ) ) {
				$html .= '<div class="rt-extra-curriculum"><strong>' . esc_html( $label ) . '</strong>' . self::htmlKses( $value, 'basic' ) . '</div>';
			}
		}

		$html .= self::get_formatted_skill( $skill, $fields );

		if ( in_array( 'author_post', $fields, true ) ) {
			$html .= self::memberDetailPosts( $post_id );
		}

		// ---------- footer: socials + buttons ----------
		$social  = self::get_formatted_social_link( $sLink, $fields );
		$resume  = ( $resume_url && in_array( 'resume_btn', $fields, true ) && $resume_text );
		$hire_me = ( $hire_me_url && in_array( 'hire_me_btn', $fields, true ) && $hire_text );

		$buttons = '';

		if ( in_array( 'readmore_btn', $fields, true ) ) {
			$readmore_text = isset( $settings['readmore_btn_text'] ) && $settings['readmore_btn_text']
				? $settings['readmore_btn_text']
				: __( 'Read More', 'tlp-team' );
			$buttons      .= '<a class="rt-ream-me-btn rttm-sp-readmore" target="_self" title="' . esc_attr( $readmore_text ) . '" href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $readmore_text ) . '</a>';
		}

		if ( $resume ) {
			$buttons .= '<a class="rt-resume-btn" target="_self" title="' . esc_attr( $resume_text ) . '" href="' . esc_url( $resume_url ) . '">' . esc_html( $resume_text ) . '</a>';
		}
		if ( $hire_me ) {
			$buttons .= '<a class="rt-hire-btn" target="_self" title="' . esc_attr( $hire_text ) . '" href="' . esc_url( $hire_me_url ) . '">' . esc_html( $hire_text ) . '</a>';
		}

		$html .= $buttons ? '<div class="readmore-btn rttm-sp-actions">' . $buttons . '</div>' : '';
		// The reference rules the socials off with a divider; keep that even when the
		// buttons above already ended the block.
		$html .= $social ? '<div class="rttm-sp-foot">' . $social . '</div>' : '';

		$html .= '</div>'; // .rttm-sp-body
		$html .= '</div>'; // .rttm-sp-panel

		return apply_filters( 'rttm_smart_popup_markup', $html, $post_id, $fields, $is_el );
	}

	public static function memberDetailPosts( $post_id = null ) {
		if ( ! $post_id ) {
			return;
		}
		$output   = null;
		$authorId = get_post_field( 'post_author', $post_id );
		// Initialised up front: a member with no author (post_author 0 -- importers and
		// programmatic creation both produce it) skipped the branch below and left this
		// undefined, so `is_array()` raised a warning straight onto the detail page.
		$authors_posts = [];

		if ( $authorId ) {
			$authors_posts = get_posts(
				[
					'author'         => $authorId,
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				]
			);
		}
		if ( is_array( $authors_posts ) && ! empty( $authors_posts ) ) {
			$output .= "<div class='rt-team-latest-post-wrap'>";
			$output .= '<h3>' . esc_html__( 'Latest post(s)', 'tlp-team' ) . '</h3>';
			$output .= '<ul class="author-latest-post">';
			foreach ( $authors_posts as $authors_post ) {
				$output .= '<li><a href="' . get_permalink( $authors_post->ID ) . '">' . apply_filters(
					'the_title',
					$authors_post->post_title,
					$authors_post->ID
				) . '</a></li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
		}

		return $output;
	}

	public static function rt_get_all_taxonomy_by_post_type() {
		$taxonomies = [];
		$taxObj     = get_object_taxonomies( rttlp_team()->post_type, 'objects' );
		if ( is_array( $taxObj ) && ! empty( $taxObj ) ) {
			foreach ( $taxObj as $tKey => $taxonomy ) {
				if ( $tKey == rttlp_team()->taxonomies['skill'] ) {
					continue;
				}
				$taxonomies[ $tKey ] = $taxonomy->label;
			}
		}

		return $taxonomies;
	}

	public static function rt_get_all_terms_by_taxonomy( $taxonomy = null ) {
		$terms = [];
		if ( $taxonomy ) {
			$temp_terms = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => 0,
				]
			);
			if ( is_array( $temp_terms ) && ! empty( $temp_terms ) && empty( $temp_terms['errors'] ) ) {
				foreach ( $temp_terms as $term ) {
					$order = get_term_meta( $term->term_id, '_rt_order', true );
					if ( $order === '' ) {
						update_term_meta( $term->term_id, '_rt_order', 0 );
					}
				}
				$termObjs = get_terms(
					[
						'taxonomy'   => $taxonomy,
						'orderby'    => 'meta_value_num',
						'meta_key'   => '_rt_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'order'      => 'ASC',
						'hide_empty' => false,
					]
				);

				foreach ( $termObjs as $term ) {
					$terms[ $term->term_id ] = $term->name;
				}
			}
		}

		return $terms;
	}

	/**
	 * Member counts for the filter pills' count badges.
	 *
	 * `rt_get_all_terms_by_taxonomy()` returns `[ id => name ]` and throws the term
	 * objects away, and three callers rely on that shape — so the counts are fetched
	 * separately rather than changing it.
	 *
	 * `$term->count` is the number of published posts in the term. These taxonomies
	 * are registered to the team post type alone, so that is the member count. The
	 * `all` entry is the total published members, matching the reference design where
	 * the badges describe the whole directory and do not change as you filter.
	 *
	 * @param string|null $taxonomy Taxonomy slug.
	 *
	 * @return array<string|int, int> `all` plus term_id => count.
	 */
	public static function rt_filter_term_counts( $taxonomy = null ) {
		$counts = [ 'all' => 0 ];

		$totals = wp_count_posts( rttlp_team()->post_type );
		if ( isset( $totals->publish ) ) {
			$counts['all'] = (int) $totals->publish;
		}

		if ( ! $taxonomy ) {
			return $counts;
		}

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);

		if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( isset( $term->term_id ) ) {
					$counts[ $term->term_id ] = (int) $term->count;
				}
			}
		}

		return $counts;
	}

	/**
	 * One markup helper for the pill count badge, so the three hand-synced filter
	 * renderers (Shortcode, admin Preview, pro's ElementorFilters) cannot drift.
	 *
	 * @param array           $counts Map from self::rt_filter_term_counts().
	 * @param string|int|null $key    `all` or a term id.
	 *
	 * @return string
	 */
	public static function rt_filter_count_badge( $counts, $key ) {
		if ( ! is_array( $counts ) || ! isset( $counts[ $key ] ) ) {
			return '';
		}

		return '<span class="rt-filter-count">'
			. esc_html( number_format_i18n( (int) $counts[ $key ] ) )
			. '</span>';
	}

	/* Convert hexdec color string to rgb(a) string */
	public static function TLPhex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';

		// Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) == 6 ) {
			$hex = [ $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] ];
		} elseif ( strlen( $color ) == 3 ) {
			$hex = [ $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] ];
		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string
		return $output;
	}

	/**
	 * @return array
	 * Image size
	 */
	public static function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = [];
		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, [ 'thumbnail', 'medium', 'large' ] ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = [
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				];
			}
		}

		$imgSize = [];
		foreach ( $sizes as $key => $img ) {
			$imgSize[ $key ] = ucfirst( $key ) . " ({$img['width']}*{$img['height']})";
		}
		$imgSize['ttp_custom'] = esc_html__( 'Custom image size', 'tlp-team' );

		return $imgSize;
	}

	public static function getAllTermsByTaxonomyName( $taxonomy ) {

		$terms = [];
		if ( $taxonomy ) {
            /*old code*/
//			$termList = get_terms( [ rttlp_team()->taxonomies[ $taxonomy ] ], [ 'hide_empty' => 0 ] );
            $termList = get_terms( array(
	            'taxonomy'   => rttlp_team()->taxonomies[ $taxonomy ],
	            'hide_empty' => false,
            ) );
			if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
				foreach ( $termList as $term ) {
					$terms[ $term->term_id ] = $term->name;
				}
			}
		}

		return $terms;
	}

	public static function getFeatureImageSrc( $post_id, $fImgSize = 'medium', $defaultImgId = null, $customImgSize = [] ) {

		$imgSrc = null;
		$cSize  = false;
		if ( $fImgSize == 'ttp_custom' ) {
			$fImgSize = 'full';
			$cSize    = true;
		}

		if ( $aID = get_post_thumbnail_id( $post_id ) ) {
			$image  = wp_get_attachment_image_src( $aID, $fImgSize );
			$imgSrc = $image[0];
		}
		if ( ! $imgSrc && $defaultImgId ) {
			$image  = wp_get_attachment_image_src( $defaultImgId, $fImgSize );
			$imgSrc = $image[0];
		}
		if ( $imgSrc && $cSize ) {
			$w = ( ! empty( $customImgSize['width'] ) ? absint( $customImgSize['width'] ) : null );
			$h = ( ! empty( $customImgSize['height'] ) ? absint( $customImgSize['height'] ) : null );
			$c = ( ! empty( $customImgSize['crop'] ) && $customImgSize['crop'] == 'soft' ? false : true );
			if ( $w && $h ) {
				$imgSrc = self::rtImageReSize( $imgSrc, $w, $h, $c );
			}
		}
		if ( ! $imgSrc ) {
			$imgSrc = rttlp_team()->assets_url() . 'images/demo.jpg';
		}

		return $imgSrc;
	}

	/**
	 * @param        $post_id
	 * @param string  $fImgSize
	 * @param null    $defaultImgId
	 * @param array   $customImgSize
	 *
	 * @return string|null
	 */
	public static function getFeatureImageHtml( $post_id, $fImgSize = 'medium', $defaultImgId = null, $customImgSize = [], $lazy = false ) {

		$imgHtml = $imgSrc = $attachment_id = null;
		$cSize   = false;
		if ( $fImgSize == 'ttp_custom' ) {
			$fImgSize = 'full';
			$cSize    = true;
		}
		$aID        = get_post_thumbnail_id( $post_id );
		$post_title = get_the_title( $post_id );
		$img_alt    = trim( wp_strip_all_tags( get_post_meta( $aID, '_wp_attachment_image_alt', true ) ) );
		$alt_tag    = ! empty( $img_alt ) ? $img_alt : trim( wp_strip_all_tags( $post_title ) );
		$lazy_class = $lazy ? ' swiper-lazy' : '';
		$attr       = [
			'class' => 'img-responsive rt-team-img' . $lazy_class,
			'alt'   => $alt_tag,
		];

		if ( $aID ) {
			$imgHtml       = wp_get_attachment_image( $aID, $fImgSize, false, $attr );
			$attachment_id = $aID;
		}

		if ( ! $imgHtml && $defaultImgId ) {
			$imgHtml       = wp_get_attachment_image( $defaultImgId, $fImgSize, false, $attr );
			$attachment_id = $defaultImgId;
		}

		if ( $imgHtml && $cSize ) {
			preg_match( '@src="([^"]+)"@', $imgHtml, $match );
			$imgSrc = array_pop( $match );
			$w      = ! empty( $customImgSize['width'] ) ? absint( $customImgSize['width'] ) : null;
			$h      = ! empty( $customImgSize['height'] ) ? absint( $customImgSize['height'] ) : null;
			$c      = ! empty( $customImgSize['crop'] ) && $customImgSize['crop'] == 'soft' ? false : true;

			if ( $w && $h ) {
				$image = self::rtImageReSize( $imgSrc, $w, $h, $c, false );

				if ( ! empty( $image ) ) {
					if ( $lazy ) {
						list( $src, $width, $height ) = $image;

						$hwstring         = image_hwstring( $width, $height );
						$attachment       = get_post( $attachment_id );
						$attr             = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $fImgSize );
						$attr['data-src'] = $src;
						$attr             = array_map( 'esc_attr', $attr );
						$imgHtml          = rtrim( "<img $hwstring" );
						foreach ( $attr as $name => $value ) {
							$imgHtml .= " $name=" . '"' . $value . '"';
						}
						$imgHtml .= ' />';
					} else {
						list( $src, $width, $height ) = $image;

						$hwstring    = image_hwstring( $width, $height );
						$attachment  = get_post( $attachment_id );
						$attr        = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $fImgSize );
						$attr['src'] = $src;
						$attr        = array_map( 'esc_attr', $attr );
						$imgHtml     = rtrim( "<img $hwstring" );
						foreach ( $attr as $name => $value ) {
							$imgHtml .= " $name=" . '"' . $value . '"';
						}
						$imgHtml .= ' />';
					}
				}
			}
		}

		if ( ! $imgHtml ) {
			$hwstring      = image_hwstring( 160, 160 );
			$attr          = isset( $attr['src'] ) ? apply_filters( 'wp_get_attachment_image_attributes', $attr, false, $fImgSize ) : [];
			$attr['class'] = 'default-img';
			$attr['src']   = esc_url( rttlp_team()->assets_url() . 'images/demo.jpg' );
			$attr['alt']   = esc_html__( 'Default Image', 'tlp-team' );
			$imgHtml       = rtrim( "<img $hwstring" );
			foreach ( $attr as $name => $value ) {
				$imgHtml .= " $name=" . '"' . $value . '"';
			}
			$imgHtml .= ' />';
		}

		if ( $lazy ) {
			$imgHtml = $imgHtml . '<div class="swiper-lazy-preloader swiper-lazy-preloader"></div>';
		}

		return $imgHtml;
	}

	/**
	 * Call the Image resize model for resize function
	 *
	 * @param            $url
	 * @param null       $width
	 * @param null       $height
	 * @param null       $crop
	 * @param bool|true  $single
	 * @param bool|false $upscale
	 *
	 * @return array|bool|string
	 * @throws Exception
	 * @throws TTPException
	 */
	public static function rtImageReSize( $url, $width = null, $height = null, $crop = null, $single = true, $upscale = false ) {
		$rtResize = new ReSizer();

		return $rtResize->process( $url, $width, $height, $crop, $single, $upscale );
	}


	public static function get_ttp_short_description( $short_bio, $character_limit, $after_desc ) {
		$text = '';

		/*
		 * No character limit means the bio is used whole — HTML and all, since nothing
		 * needs stripping to count characters. This used to `return $short_bio` outright,
		 * which skipped the "After Short Description" append below: the option was simply
		 * dead unless a limit happened to be set, even though its own description says
		 * "Add something after short description" and says nothing about truncation.
		 */
		if ( empty( $character_limit ) ) {
			$text = $short_bio;

			return self::append_after_short_desc( $text, $after_desc );
		}

		$character_limit ++;

		if ( mb_strlen( $short_bio ) > $character_limit ) {
			$subex   = mb_substr( wp_strip_all_tags( $short_bio ), 0, $character_limit );
			$exwords = explode( ' ', $subex );
			$excut   = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

			if ( $excut < 0 ) {
				$text .= mb_substr( $subex, 0, $excut );
			} else {
				$text .= $subex;
			}
		} else {
			$text .= $short_bio;
		}

		return self::append_after_short_desc( $text, $after_desc );
	}

	/**
	 * Append the "After Short Description" text to a bio.
	 *
	 * Wrapped in `.rttm-after-bio` so it can be told apart from the bio itself. It used
	 * to be concatenated raw — no element, no class — so it ran straight on from the
	 * truncated sentence ("…a galley of type Read more") and there was no hook to style
	 * it with; readers could not tell where the member's own words ended.
	 *
	 * `span` + `class` survive `allowedHtml( 'basic' )`, which is what every caller runs
	 * the bio through, and the Elementor path has already escaped the value by the time
	 * it reaches here, so the markup is added around escaped text.
	 *
	 * Nothing is appended to an empty bio: there is no short description for it to come
	 * after, and a lone chip floating where the text should be reads as a glitch.
	 *
	 * @param string $text       The (possibly truncated) bio.
	 * @param string $after_desc The configured text to append.
	 *
	 * @return string
	 */
	private static function append_after_short_desc( $text, $after_desc ) {
		if ( '' === trim( wp_strip_all_tags( (string) $text ) ) ) {
			return $text;
		}

		if ( '' === trim( (string) $after_desc ) ) {
			return $text;
		}

		return $text . ' <span class="rttm-after-bio">' . $after_desc . '</span>';
	}


	public static function get_formatted_contact_info( $items, $fields ) {

		$contact_info = null;
		if ( ! empty( $items['email'] ) && in_array( 'email', $fields, true ) ) {
			$contact_info .= '<li class="tlp-email"><i class="far fa-envelope"></i><a href="mailto:' . esc_attr( $items['email'] ) . '"><span class="tlp-email">' . esc_html( $items['email'] ) . '</span></a></li>';
		}
		if ( ! empty( $items['telephone'] ) && in_array( 'telephone', $fields, true ) ) {
			$contact_info .= '<li class="tlp-phone"><i class="fa fa-phone-alt"></i><a href="tel:' . esc_attr( $items['telephone'] ) . '" class="tlp-phone">' . esc_html( $items['telephone'] ) . '</a></li>';
		}
		if ( $items['mobile'] && in_array( 'mobile', $fields, true ) ) {
			$contact_info .= "<li class='tlp-mobile'><i class='fa fa-mobile'></i> <a href='tel:" . esc_attr( $items['mobile'] ) . "'>" . esc_html( $items['mobile'] ) . '</a></li>';
		}
		if ( $items['fax'] && in_array( 'fax', $fields, true ) ) {
			$contact_info .= "<li class='tlp-fax'><i class='fa fa-fax'></i> <a href='fax:" . esc_attr( $items['fax'] ) . "'><span> " . esc_html( $items['fax'] ) . '</span></a></li>';
		}
		if ( ! empty( $items['location'] ) && in_array( 'location', $fields, true ) ) {
			$contact_info .= '<li class="tlp-location"><i class="fa fa-map-marker"></i><span class="tlp-location">' . esc_html( $items['location'] ) . '</span></li>';
		}
		if ( ! empty( $items['web_url'] ) && in_array( 'web_url', $fields, true ) ) {
			$contact_info .= '<li class="tlp-website"><a target="_blank" href="' . esc_url( $items['web_url'] ) . '"><i class="fa fa-globe"></i><span class="tlp-url">' . esc_url( $items['web_url'] ) . '</span></a></li>';
		}
		return $contact_info ? '<div class="contact-info"><ul>' . $contact_info . '</ul></div>' : null;

	}

	public static function get_formatted_designation( $designation, $fields, $experience_year = null ) {
		$html = $exp = null;
		if ( $experience_year && in_array( 'experience_year', $fields ) ) {
			$exp = "<span class='experience'>({$experience_year})</span>";
		}
		if ( in_array( 'designation', $fields ) && $designation ) {
			$html .= '<div class="tlp-position">' . $designation . $exp . '</div>';
		}
		return $html;
	}

	public static function get_formatted_short_bio( $short_bio, $fields ) {

		$html = null;
		if ( $short_bio && in_array( 'short_bio', $fields, true ) ) {

			if ( class_exists( 'Avada' ) ) {
				$html .= '<div class="short-bio avada-support">' . apply_filters( 'the_content', self::htmlKses( $short_bio, 'basic' ) ) . '</div>';
			} else {
				$html .= '<div class="short-bio">' . self::htmlKses( wpautop( $short_bio ), 'basic' ) . '</div>';
			}
		}
		return $html;
	}

    public static function get_formatted_readmore_text( $fields, $read_more_btn_text, $anchorClass, $mID, $target, $title, $pLink ) {
        $html = '';
        if (in_array('readmore_btn', $fields, true) && $read_more_btn_text) {
            $html .= '<a class="rt-ream-me-btn' . esc_attr($anchorClass) . '" data-id="' . absint($mID) . '" target="' . esc_attr($target) . '" title="' . esc_attr($title) . '" href="' . esc_url($pLink) . '">' . esc_html($read_more_btn_text) . '</a>';
        }
        return $html;
    }
	public static function get_formatted_resume( $fields, $ttp_my_resume, $my_resume_text ) {
        $html = '';
        if (in_array('resume_btn', $fields, true) && $ttp_my_resume && $my_resume_text ) {
            $html .= '<a href="' . esc_url($ttp_my_resume) . '" class="rt-resume-btn">' . esc_html($my_resume_text) . '</a>';
        }
        return $html;
	}

	public static function get_formatted_hire_me( $fields, $ttp_hire_me, $hire_me_text ) {
        $html = '';
        if (in_array('hire_me_btn', $fields, true) && $hire_me_text && $ttp_hire_me ) {
            $html .= '<a href="' . esc_url($ttp_hire_me) . '" class="rt-hire-btn">' . esc_html($hire_me_text) . '</a>';
        }
        return $html;
	}

	public static function get_formatted_skill( $tlp_skill, $fields ) {
		if ( ! rttlp_team()->has_pro() ) {
			return;
		}
		$html = null;
		if ( is_array( $tlp_skill ) && ! empty( $tlp_skill ) && in_array( 'skill', $fields, true ) ) {
			$html .= '<div class="tlp-team-skill">';
			foreach ( $tlp_skill as $id => $skill ) {
				if ( ! isset( $skill['id'] ) ) {
					continue;
				}
				$html .= '<div class="skill_name"> ' . esc_html( $skill['id'] ) . ' </div><div class="skill-prog tlp-tooltip"><div class="fill" data-progress-animation="' . absint( $skill['percent'] ) . '%"><span class="percent-text">'. esc_html( $skill['percent'] ) .'%</span></div></div>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	/**
	 * Builds the header row for the Layout 5 table.
	 *
	 * Layout 5 is the one table layout, and it renders a column per enabled
	 * field. The headings below are emitted in the same order that
	 * `templates/layouts/layout5.php` emits its cells — keep the two in step or
	 * the header stops lining up with the body.
	 *
	 * Callers are the three places that *open* a Layout 5 table: the shortcode,
	 * the admin preview and the Elementor grid renderer. The AJAX load-more
	 * handlers deliberately do not call it — they append further rows to a table
	 * that already has a header.
	 *
	 * @param array $items     Enabled field keys.
	 * @param bool  $showImage Whether the image column is being rendered.
	 * @return string
	 */
	public static function layout5TableHead( $items, $showImage = true ) {
		$columns = [
			'name'        => esc_html__( 'Member', 'tlp-team' ),
			'designation' => esc_html__( 'Role', 'tlp-team' ),
			'email'       => esc_html__( 'Email', 'tlp-team' ),
			'telephone'   => esc_html__( 'Phone', 'tlp-team' ),
			'mobile'      => esc_html__( 'Mobile', 'tlp-team' ),
			'fax'         => esc_html__( 'Fax', 'tlp-team' ),
			'location'    => esc_html__( 'Location', 'tlp-team' ),
			'web_url'     => esc_html__( 'Website', 'tlp-team' ),
			'social'      => esc_html__( 'Social', 'tlp-team' ),
		];

		$items = is_array( $items ) ? $items : [];
		$cells = '';
		$named = in_array( 'name', $items, true );

		/*
		 * Photo and name are two body columns but one heading: "Member" spans the
		 * pair, so the label gets the width of both while the cells underneath keep
		 * the tight photo-to-name gap the design has.
		 */
		if ( $showImage && $named ) {
			$cells .= '<th class="rt-l5-th rt-l5-th-member" colspan="2">' . $columns['name'] . '</th>';
			unset( $columns['name'] );
		} elseif ( $showImage ) {
			$cells .= '<th class="rt-l5-th rt-l5-th-avatar"></th>';
		}

		foreach ( $columns as $key => $label ) {
			if ( in_array( $key, $items, true ) ) {
				$cells .= '<th class="rt-l5-th rt-l5-th-' . esc_attr( str_replace( '_', '-', $key ) ) . '">' . $label . '</th>';
			}
		}

		if ( ! $cells ) {
			return '';
		}

		return '<thead><tr>' . $cells . '</tr></thead>';
	}

	/**
	 * Builds the social icon list.
	 *
	 * @param array $sLink         Saved social links.
	 * @param array $fields        Enabled field keys.
	 * @param array $iconOverrides Optional per-network Font Awesome class overrides,
	 *                             keyed by network id (e.g. [ 'linkedin' => 'fab fa-linkedin-in' ]).
	 * @return string
	 */
	public static function get_formatted_social_link( $sLink, $fields, $iconOverrides = [] ) {
		$html = null;

		if ( ! empty( $sLink ) && is_array( $sLink ) && in_array( 'social', $fields, true ) ) {

			$html .= '<div class="social-icons">';

			foreach ( $sLink as $id => $itemLink ) {

				$lURL = ! empty( $itemLink['url'] ) ? esc_url( $itemLink['url'] ) : '#';
				$lID  = ! empty( $itemLink['id'] ) ? esc_html( $itemLink['id'] ) : null;

				if ( 'envelope-o' === $lID ) {
					$lURL = ! empty( $itemLink['url'] ) ? $itemLink['url'] : null;
					$lURL = 'mailto:' . esc_attr( $lURL );
				}

				$icon_class = '';

				switch ( $lID ) {
					case 'facebook':
						$icon_class = 'fab fa-facebook-f';
						break;
					case 'twitter':
						$icon_class = 'fab fa-x-twitter';
						break;
					case 'linkedin':
						$icon_class = 'fab fa-linkedin';
						break;
					case 'youtube':
						$icon_class = 'fab fa-youtube';
						break;
					case 'instagram':
						$icon_class = 'fab fa-instagram';
						break;
					case 'pinterest':
						$icon_class = 'fab fa-pinterest-p';
						break;
					case 'soundcloud':
						$icon_class = 'fab fa-soundcloud';
						break;
					case 'bandcamp':
						$icon_class = 'fab fa-bandcamp';
						break;
					case 'vimeo':
						$icon_class = 'fab fa-vimeo-v';
						break;
					case 'envelope-o':
						$icon_class = 'far fa-envelope';
						break;
					case 'globe':
						$icon_class = 'fas fa-globe';
						break;
					case 'xing':
						$icon_class = 'fab fa-xing';
						break;
					case 'skype':
						$icon_class = 'fa-solid fa-user-plus';
						break;
					case 'whatsapp':
						$icon_class = 'fab fa-whatsapp';
						break;
					case 'telegram':
						$icon_class = 'fab fa-telegram';
						break;
                    case 'github':
                        $icon_class = 'fab fa-github';
                        break;
                    case 'bluesky':
                        $icon_class = 'fa-brands fa-bluesky';
                        break;
				}

				/*
				 * Per-network glyph overrides, keyed by network id. Layouts whose social
				 * links are drawn as chips pass their own variants — e.g. Layout 5 asks
				 * for `fa-linkedin-in` (bare "in" lettermark) instead of the default
				 * `fa-linkedin`, which is the filled brand SQUARE and reads as a solid
				 * block next to `fa-facebook-f` and `fa-x-twitter`. Scoped per call so
				 * no other layout's icon set changes.
				 */
				if ( ! empty( $iconOverrides[ $lID ] ) ) {
					$icon_class = $iconOverrides[ $lID ];
				}

				if ( 'google-plus' !== $lID && $icon_class ) {
					$html .= '<a href="' . $lURL . '" title="' . esc_attr( $lID ) . '" target="_blank"><i class="' . esc_attr( $icon_class ) . '"></i></a>';
				}
			}
			$html .= '</div>';
		}

		return $html;
	}

	public static function layoutStyleGenerator( $layoutID, $scMeta, $scID = null ) {


		$css  = null;
		$css .= '<style>';
		// Variable
		if ( $scID ) {

			$primaryColor   = ( isset( $scMeta['primary_color'][0] ) ? $scMeta['primary_color'][0] : null );
            $hireme_btn     = ! empty( $scMeta['hireme_btn_style'][0] ) ? unserialize( $scMeta['hireme_btn_style'][0] ) : null;
            $resume_btn     = ! empty( $scMeta['resume_btn_style'][0] ) ? unserialize( $scMeta['resume_btn_style'][0] ) : null;
            $readmore_btn   = ! empty( $scMeta['readmore_btn_style'][0] ) ? unserialize( $scMeta['readmore_btn_style'][0] ) : null;
			$button         = ! empty( $scMeta['ttp_button_style'][0] ) ? unserialize( $scMeta['ttp_button_style'][0] ) : null;
			$popupBg        = ! empty( $scMeta['ttp_popup_bg_color'][0] ) ? $scMeta['ttp_popup_bg_color'][0] : null;
			$popupTextColor = ! empty( $scMeta['ttp_popup_text_color'][0] ) ? $scMeta['ttp_popup_text_color'][0] : null;
			$name           = ! empty( $scMeta['name'][0] ) ? unserialize( $scMeta['name'][0] ) : null;
			$designation    = ! empty( $scMeta['designation'][0] ) ? unserialize( $scMeta['designation'][0] ) : null;
			$short_bio      = ! empty( $scMeta['short_bio'][0] ) ? unserialize( $scMeta['short_bio'][0] ) : null;
			$email          = ! empty( $scMeta['email'][0] ) ? unserialize( $scMeta['email'][0] ) : null;
			$web_url        = ! empty( $scMeta['web_url'][0] ) ? unserialize( $scMeta['web_url'][0] ) : null;
			$telephone      = ! empty( $scMeta['telephone'][0] ) ? unserialize( $scMeta['telephone'][0] ) : null;
			$mobile         = ! empty( $scMeta['mobile'][0] ) ? unserialize( $scMeta['mobile'][0] ) : null;
			$fax            = ! empty( $scMeta['fax'][0] ) ? unserialize( $scMeta['fax'][0] ) : null;
			$location       = ! empty( $scMeta['location'][0] ) ? unserialize( $scMeta['location'][0] ) : null;
			$skill          = ! empty( $scMeta['skill'][0] ) ? unserialize( $scMeta['skill'][0] ) : null;
			$social_icon    = ! empty( $scMeta['social'][0] ) ? unserialize( $scMeta['social'][0] ) : null;
			$social_icon_bg = ! empty( $scMeta['social_icon_bg'][0] ) ? $scMeta['social_icon_bg'][0] : null;
			$social_hover_bg = ! empty( $scMeta['social_icon_hover_bg'][0] ) ? $scMeta['social_icon_hover_bg'][0] : null;
			$content_bg     = ! empty( $scMeta['ttp_content_bg_color'][0] ) ? $scMeta['ttp_content_bg_color'][0] : null;
			$mObg           = ! empty( $scMeta['overlay_rgba_bg'][0] ) ? unserialize( $scMeta['overlay_rgba_bg'][0] ) : null;
			$itemP          = ! empty( $scMeta['overlay_padding'][0] ) ? intval( $scMeta['overlay_padding'][0] ) : null;
			$gutter         = ! empty( $scMeta['ttp_gutter'][0] ) ? absint( $scMeta['ttp_gutter'][0] ) : null;

		} else {

			$primaryColor   = ! empty( $scMeta['primary_color'] ) ? $scMeta['primary_color'] : null;
            $hireme_btn     = ! empty( $scMeta['hireme_btn_style'] ) ? $scMeta['hireme_btn_style'] : null;
            $resume_btn     = ! empty( $scMeta['resume_btn_style'] ) ? $scMeta['resume_btn_style'] : null;
            $readmore_btn   = ! empty( $scMeta['readmore_btn_style'] ) ? $scMeta['readmore_btn_style'] : null;
			$button         = ! empty( $scMeta['ttp_button_style'] ) ? $scMeta['ttp_button_style'] : null;
			$popupBg        = ! empty( $scMeta['ttp_popup_bg_color'] ) ? $scMeta['ttp_popup_bg_color'] : null;
			$popupTextColor = ! empty( $scMeta['ttp_popup_text_color'] ) ? $scMeta['ttp_popup_text_color'] : null;
			$name           = ! empty( $scMeta['name'] ) ? $scMeta['name'] : null;
			$designation    = ! empty( $scMeta['designation'] ) ? $scMeta['designation'] : null;
			$short_bio      = ! empty( $scMeta['short_bio'] ) ? $scMeta['short_bio'] : null;
			$email          = ! empty( $scMeta['email'] ) ? $scMeta['email'] : null;
			$web_url        = ! empty( $scMeta['web_url'] ) ? $scMeta['web_url'] : null;
			$telephone      = ! empty( $scMeta['telephone'] ) ? $scMeta['telephone'] : null;
			$mobile         = ! empty( $scMeta['mobile'] ) ? $scMeta['mobile'] : null;
			$fax            = ! empty( $scMeta['fax'] ) ? $scMeta['fax'] : null;
			$location       = ! empty( $scMeta['location'] ) ? $scMeta['location'] : null;
			$skill          = ! empty( $scMeta['skill'] ) ? $scMeta['skill'] : null;
			$social_icon    = ! empty( $scMeta['social'] ) ? $scMeta['social'] : null;
            $content_bg     = ! empty( $scMeta['ttp_content_bg_color'] ) ? $scMeta['ttp_content_bg_color'] : null;
			$social_icon_bg = ! empty( $scMeta['social_icon_bg'] ) ? $scMeta['social_icon_bg'] : null;
			$social_hover_bg = ! empty( $scMeta['social_icon_hover_bg'] ) ? $scMeta['social_icon_hover_bg'] : null;
			$mObg           = ! empty( $scMeta['overlay_rgba_bg'] ) ? $scMeta['overlay_rgba_bg'] : null;
			$itemP          = ! empty( $scMeta['overlay_padding'] ) ? intval( $scMeta['overlay_padding'] ) : null;
			$gutter         = ! empty( $scMeta['ttp_gutter'] ) ? absint( $scMeta['ttp_gutter'] ) : null;

		}

		if ( $primaryColor ) {
			// Publish the accent as a custom property so Grid Layout 1 derives its
			// whole palette (role, icon chips, socials, buttons, pagination) from it
			// in the live preview, exactly as templates/sc-css.php does on the front
			// end. Without this the preview coloured only a few hard-coded elements
			// (e.g. the active pagination button) and left the rest at the default.
			$css .= "#{$layoutID}{--l1-primary:{$primaryColor};}";
			// Isotope 2 reads --iso2-primary for the role text and dash, the contact icon
			// tiles, the skill bars, the social chip hover, the buttons and the card's
			// hover border. Mirrors templates/sc-css.php; keep the two in step.
			$css .= "#{$layoutID} .isotope2{--iso2-primary:{$primaryColor};}";
			// Isotope 3 — role pill, department pill, social chip hover and Read More button.
			$css .= "#{$layoutID} .isotope3{--iso3-primary:{$primaryColor};}";
			// Special Layout 01 reads its whole accent palette (name plate + notch,
			// contact/social chips, buttons, active thumbnail ring) from this one.
			$css .= "#{$layoutID} .special01{--rt-sp1-primary:{$primaryColor};}";
			// Layout 5's table reads this for the role text, the contact icon chips and
			// the social chips + their hover fill.
			$css .= "#{$layoutID} .layout5{--l5-primary:{$primaryColor};}";
			// Layout 7 (and carousel 2, which shares its card) reads this for the
			// hover overlay, the social glyph on hover and the pill buttons.
			$css .= "#{$layoutID} .layout7,#{$layoutID} .isotope4{--l7-primary:{$primaryColor};}";
			// Layout 8 (and carousel 3 and isotope 5, which share its card) reads this
			// for the floating name label, the social glyph and the pill buttons.
			// carousel3's slides carry the `layout8` class, so the first arm covers
			// them; isotope5 keeps its own class and needs its own arm.
			$css .= "#{$layoutID} .layout8,#{$layoutID} .isotope5{--l8-primary:{$primaryColor};}";
			// Layout 9 (and carousel 4, which shares its card) reads this for the
			// name pill, the social glyph on hover and the pill buttons.
			// isotope6 shares layout9's card and keeps its own per-path key, so it
			// needs its own arm (carousel4's slides carry `layout9` already).
			$css .= "#{$layoutID} .layout9,#{$layoutID} .isotope6{--l9-primary:{$primaryColor};}";
			$css .= "#{$layoutID} .single-team-area .overlay a.detail-popup,
					#{$layoutID} .contact-info ul li i{";
			$css .= 'color:' . $primaryColor . ';';
			$css .= '}';
			/*
			 * Layout 6 opts out: its contact chips sit on the brand panel, which this same
			 * control paints with $primaryColor below, so the arm above would render them
			 * invisible against their own background. tlpteam.css carries the identical
			 * correction for the front end, but it cannot reach here -- the arm above is
			 * ID-scoped (1,1,3) and no class selector out-ranks that.
			 */
			$css .= "#{$layoutID} .layout6 .tlp-info-block .contact-info ul li i{color:#fff;}";
			// Note: `#{$layoutID} .layout1 .tlp-content` is intentionally NOT in the
			// background list below — Layout 1's name strip stays white and the
			// accent flows through --l1-primary instead.
			$css .= "#{$layoutID} .single-team-area .skill-prog .fill,
			         .tlp-team #{$layoutID} .tlp-content,
					.tlp-tooltip + .tooltip > .tooltip-inner,
					#{$layoutID} .layout11 .single-team-area .tlp-title,
					#{$layoutID} .carousel7 .single-team-area .team-name,
					#{$layoutID} .layout14 .rt-grid-item .tlp-overlay,
					#{$layoutID} .carousel8 .rt-grid-item .tlp-overlay,
					#{$layoutID} .isotope6 .single-team-area h3 .team-name,
					#{$layoutID} .carousel8 .rt-grid-item .tlp-overlay .social-icons:before,
					#{$layoutID} .layout14 .rt-grid-item .tlp-overlay .social-icons:before,
					#{$layoutID} .skill-prog .fill,
					#{$layoutID}.rt-team-container .layout16 .single-team-area .social-icons,
					#{$layoutID}.rt-team-container .layout16 .single-team-area:hover:before,
					#{$layoutID} .layout6 .tlp-info-block, #{$layoutID} .carousel9 .single-team-area .tlp-overlay{";
			$css .= 'background:' . $primaryColor . ';';
			$css .= '}';
			$css .= "#{$layoutID} .layout15 .single-team-area .ttp-member-title,
						#{$layoutID} .isotope10 .single-team-area .ttp-member-title,
						#{$layoutID} .carousel11 .single-team-area .ttp-member-title{";
			$css .= 'background:' . self::TLPhex2rgba( $primaryColor, 0.8 );
			$css .= '}';
			$css .= "#rt-smart-modal-container.loading.rt-modal-{$scID} .rt-spinner,
						.tlp-team-skill .tooltip.top .tooltip-arrow{";
			$css .= 'border-top-color:' . $primaryColor . ';';
			$css .= '}';
			$css .= "#{$layoutID} .layout6 .tlp-right-arrow:after{";
			$css .= 'border-color: transparent ' . $primaryColor . ';';
			$css .= '}';
			$css .= "#{$layoutID} .layout6 .tlp-left-arrow:after{";
			$css .= 'border-color:' . $primaryColor . ' transparent transparent;';
			$css .= '}';
			$css .= "#{$layoutID} .layout12 .single-team-area h3 .team-name,
					#{$layoutID} .isotope6 .single-team-area h3 .team-name,
					.rt-team-container .layout12 .single-team-area h3 .team-name,
					.rt-team-container .isotope6 .single-team-area h3 .team-name {";
			$css .= 'background:' . $primaryColor . ';';
			$css .= '}';
			$css .= "#{$layoutID} .special-selected-top-wrap .img:after{";
			$css .= 'background:' . self::TLPhex2rgba( $primaryColor, 0.2 );
			$css .= '}';

            $css .= "#{$layoutID}.rt-team-container .layout16 .single-team-area:hover:after{";
            $css .= 'border-color:' . $primaryColor . ' !important;';
            $css .= '}';

			$css .= "#rt-smart-modal-container.rt-modal-{$scID} .rt-smart-modal-header a.rt-smart-nav-item{";
			$css .= '-webkit-text-stroke: 1px ' . self::TLPhex2rgba( $primaryColor ) . ';';
			$css .= '}';
			$css .= "#rt-smart-modal-container.rt-modal-{$scID} .rt-smart-modal-header a.rt-smart-modal-close{";
			$css .= '-webkit-text-stroke: 6px ' . self::TLPhex2rgba( $primaryColor ) . ';';
			$css .= '}';

		}
		/* button */
		if ( ! empty( $button ) ) {
			if ( ! empty( $button['bg'] ) ) {
				$css .= "#{$layoutID} .rt-pagination-wrap .rt-loadmore-btn,
						#{$layoutID} .rt-pagination-wrap .pagination > li > a,
						#{$layoutID} .rt-pagination-wrap .pagination > li > span,
						#{$layoutID} .ttp-isotope-buttons.button-group button,
						#{$layoutID} .rt-pagination-wrap .rt-loadmore-btn,
						#{$layoutID} .rt-carousel-holder .swiper-arrow,
						#{$layoutID} .rt-team-container .rt-carousel-holder.swiper .swiper-pagination-bullet,
						#{$layoutID} .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item,
						#{$layoutID} .rt-pagination-wrap .paginationjs .paginationjs-pages li>a{";
				$css .= "background-color: {$button['bg']};";
				$css .= '}';
				$css .= "#{$layoutID} .rt-pagination-wrap .rt-infinite-action .rt-infinite-loading{";
				$css .= 'color: ' . self::TLPhex2rgba( $button['bg'], 0.5 );
				$css .= '}';
			}
			if ( ! empty( $button['hover_bg'] ) ) {
				$css .= "#{$layoutID} .rt-pagination-wrap .rt-loadmore-btn:hover,
						#{$layoutID} .rt-pagination-wrap .pagination > li > a:hover,
						#{$layoutID} .rt-pagination-wrap .pagination > li > span:hover,
						#{$layoutID} .rt-carousel-holder .swiper-arrow:hover,
						#{$layoutID} .rt-team-container .rt-carousel-holder.swiper .swiper-pagination-bullet:hover,
						#{$layoutID} .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item:hover,
						#{$layoutID} .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item:hover,
						#{$layoutID} .ttp-isotope-buttons.button-group button:hover,
						#{$layoutID} .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages li>a:hover{";
				$css .= "background-color: {$button['hover_bg']};";
				$css .= '}';
			}
			if ( ! empty( $button['active_bg'] ) ) {
				$css .= "#{$layoutID} .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li.active > a,
				#{$layoutID} .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item.selected,
				#{$layoutID} .ttp-isotope-buttons.button-group .selected,
				#{$layoutID} .rt-carousel-holder .swiper-pagination-bullet.swiper-pagination-bullet-active,
				#{$layoutID} .rt-pagination-wrap .pagination > .active > span{";
				$css .= "background-color: {$button['active_bg']};";
				$css .= '}';
			}
			if ( ! empty( $button['text'] ) ) {
				$css .= "#{$layoutID} .rt-pagination-wrap .rt-loadmore-btn,
						#{$layoutID} .rt-pagination-wrap .pagination > li > a,
						#{$layoutID} .rt-pagination-wrap .pagination > li > span,
						#{$layoutID} .ttp-isotope-buttons.button-group button,
						#{$layoutID} .rt-carousel-holder .swiper-arrow i,
						#{$layoutID} .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item,
						#{$layoutID} .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item,
						#{$layoutID} .rt-pagination-wrap .paginationjs .paginationjs-pages li>a{";
				$css .= "color: {$button['text']};";
				$css .= '}';
			}
			if ( ! empty( $button['hover_text'] ) ) {
				$css .= "#{$layoutID} .rt-pagination-wrap .rt-loadmore-btn:hover,
						#{$layoutID} .rt-pagination-wrap .pagination > li > a:hover,
						#{$layoutID} .rt-pagination-wrap .pagination > li > span:hover,
						#{$layoutID} .ttp-isotope-buttons.button-group button:hover,
						#{$layoutID} .rt-carousel-holder .swiper-arrow:hover i,
						#{$layoutID} .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item:hover,
						#{$layoutID} .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item:hover,
						#{$layoutID} .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages li>a:hover{";
				$css .= "color: {$button['hover_text']};";
				$css .= '}';
			}
			if ( ! empty( $button['border'] ) ) {
				$css .= "#{$layoutID} .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item,
						#{$layoutID} .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-sort-order-action,
						#{$layoutID} .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap{";
				$css .= "border-color: {$button['border']};";
				$css .= '}';
			}
		}


        // ReadMore Button.
        if ( ! empty( $readmore_btn ) ) {
            if ( ! empty( $readmore_btn['bg'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn{";
                $css .= "background-color: {$readmore_btn['bg']};";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['hover_bg'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn:hover{";
                $css .= "background-color: {$readmore_btn['hover_bg']};";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['border_color'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn{";
                $css .= "border-color: {$readmore_btn['border_color']};";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['text'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn{";
                $css .= "color: {$readmore_btn['text']};";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['hover_text'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn:hover{";
                $css .= "color: {$readmore_btn['hover_text']};";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['border_hover_color'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn:hover{";
                $css .= "border-color: {$readmore_btn['border_hover_color']};";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['border_width'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn{";
                $css .= "border-width: {$readmore_btn['border_width']}px;";
                $css .= '}';
            }

            if ( ! empty( $readmore_btn['border_radius'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-ream-me-btn{";
                $css .= "border-radius: {$readmore_btn['border_radius']}px;";
                $css .= '}';
            }

        }

        /*  Resume Button */

        if ( ! empty( $hireme_btn ) ) {
            if ( ! empty( $hireme_btn['bg'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn{";
                $css .= "background-color: {$hireme_btn['bg']};";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['hover_bg'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn:hover{";
                $css .= "background-color: {$hireme_btn['hover_bg']};";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['border_color'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn{";
                $css .= "border-color: {$hireme_btn['border_color']};";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['text'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn{";
                $css .= "color: {$hireme_btn['text']};";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['hover_text'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn:hover{";
                $css .= "color: {$hireme_btn['hover_text']};";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['border_hover_color'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn:hover{";
                $css .= "border-color: {$hireme_btn['border_hover_color']};";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['border_width'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn{";
                $css .= "border-width: {$hireme_btn['border_width']}px;";
                $css .= '}';
            }

            if ( ! empty( $hireme_btn['border_radius'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-hire-btn{";
                $css .= "border-radius: {$hireme_btn['border_radius']}px;";
                $css .= '}';
            }
        }

        /*  Hireme Button */

        if ( ! empty( $resume_btn ) ) {
            if ( ! empty( $resume_btn['bg'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn{";
                $css .= "background-color: {$resume_btn['bg']};";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['hover_bg'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn:hover{";
                $css .= "background-color: {$resume_btn['hover_bg']};";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['border_color'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn{";
                $css .= "border-color: {$resume_btn['border_color']};";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['text'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn{";
                $css .= "color: {$resume_btn['text']};";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['hover_text'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn:hover{";
                $css .= "color: {$resume_btn['hover_text']};";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['border_hover_color'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn:hover{";
                $css .= "border-color: {$resume_btn['border_hover_color']};";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['border_width'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn{";
                $css .= "border-width: {$resume_btn['border_width']}px;";
                $css .= '}';
            }

            if ( ! empty( $resume_btn['border_radius'] ) ) {
                $css .= "#{$layoutID} .readmore-btn .rt-resume-btn{";
                $css .= "border-radius: {$resume_btn['border_radius']}px;";
                $css .= '}';
            }
        }


		/* gutter */
		if ( $gutter ) {
			$css    .= "#{$layoutID} [class*='rt-col-'] {";
			$css    .= "padding-left : {$gutter}px;";
			$css    .= "padding-right : {$gutter}px;";
			$bGutter = $gutter * 2;
			$css    .= "margin-bottom : {$bGutter}px;";
			$css    .= '}';
			$css    .= "#{$layoutID} .rt-row.special01 .rt-special-wrapper .rt-col-sm-4 [class*='rt-col-'] {";
			$css    .= 'padding-left : 0;';
			$css    .= 'padding-right : 0;';
			$css    .= '}';
			$css    .= "#{$layoutID} .rt-row.special01 .rt-special-wrapper #special-selected-wrapper {";
			$css    .= 'margin : 0;';
			$css    .= '}';
			$css    .= "#{$layoutID} .rt-row.special01 .rt-special-wrapper #special-selected-wrapper .special-selected-top-wrap > div {";
			$css    .= 'margin-bottom : 0;';
			$css    .= '}';
			$css    .= "#{$layoutID} .rt-row.special01 .rt-special-wrapper #special-selected-wrapper .rt-col-sm-12 {";
			$css    .= 'margin-bottom : 0;';
			$css    .= '}';
			$css    .= "#{$layoutID} .rt-row{";
			$css    .= "margin-left : -{$gutter}px;";
			$css    .= "margin-right : -{$gutter}px;";
			$css    .= '}';
			$css    .= "#{$layoutID}.rt-container-fluid,#{$layoutID}.rt-container,#{$layoutID}.rt-team-container{";
			$css    .= "padding-left : {$gutter}px;";
			$css    .= "padding-right : {$gutter}px;";
			$css    .= '}';
		}

		/* popup background color */
		if ( $popupBg ) {
			// $id = str_replace("rt-team-container-", "", $layoutID);

			$css .= "#tlp-popup-wrap.tlp-popup-wrap-{$scID} .tlp-popup-navigation-wrap,
					#tlp-modal.tlp-modal-{$scID} .md-content,
					#tlp-modal.tlp-modal-{$scID} .md-content > .tlp-md-content-holder .tlp-md-content{";
			$css .= 'background-color:' . $popupBg . ';';
			$css .= '}';
			/*
			 * Every redesigned popup paints the surfaces it owns — the single popup's
			 * card, the multi viewer's stage and thumbnail strip, the smart drawer —
			 * from --rttm-pop-surface, so the token recolours all of them together.
			 * The arms above only reach the outer shell, which on the single popup sits
			 * BEHIND the card and is invisible. The gradient bars are deliberately NOT
			 * included: they follow Primary Color, exactly as the Elementor path splits
			 * PopUp Header Background from PopUp Background.
			 */
			$css .= "#tlp-modal.tlp-modal-{$scID},
					#tlp-popup-wrap.tlp-popup-wrap-{$scID},
					#rt-smart-modal-container.rt-modal-{$scID}{--rttm-pop-surface:{$popupBg};}";
		}

		/* popup text color — twin of the sc-css.php block */
		if ( $popupTextColor ) {
			$css .= "#tlp-modal.tlp-modal-{$scID},
					#tlp-popup-wrap.tlp-popup-wrap-{$scID},
					#rt-smart-modal-container.rt-modal-{$scID}{--rttm-pop-text:{$popupTextColor};--rttm-pop-heading:{$popupTextColor};--rttm-pop-muted:{$popupTextColor};}";
			$css .= "#rt-smart-modal-container.rt-modal-{$scID} .member-details,#tlp-modal.tlp-modal-{$scID} .md-content{color:{$popupTextColor};}";
		}

		// Name
		if ( ! empty( $name ) ) {

			$cCss  = null;
			$cCss .= ! empty( $name['color'] ) ? 'color:' . $name['color'] . ';' : null;
			$cCss .= ! empty( $name['align'] ) ? 'text-align:' . $name['align'] . ';' : null;
			$cCss .= ! empty( $name['size'] ) ? 'font-size:' . $name['size'] . 'px;' : null;
			$cCss .= ! empty( $name['weight'] ) ? 'font-weight:' . $name['weight'] . ';' : null;
			if ( $cCss ) {
				$css .= "#{$layoutID} .layout9 .tlp-label-name,
					#{$layoutID} h3,
						#{$layoutID} h3 a,
						#{$layoutID} .overlay h3 a,
						#{$layoutID} .single-team-area .tlp-content h3 a{ {$cCss} }";
			}
			if ( ! empty( $name['hover_color'] ) ) {
				// isotope2 needs its own arm — its card recolours the name when the CARD is
				// hovered, not the heading, so the `h3 a:hover` arms never fire there. Mirrors
				// the same addition in templates/sc-css.php; keep the two in step.
				$css .= "#{$layoutID} .isotope2 .team-member:hover h3 a,
						#{$layoutID} h3:hover,
						#{$layoutID} h3 a:hover,
						#{$layoutID} .overlay h3 a:hover,
						#{$layoutID} .single-team-area .tlp-content h3 a:hover{ color: {$name['hover_color']}; }";
			}
		}
		// Designation
		if ( ! empty( $designation ) ) {
			$cCss  = null;
			$cCss .= ! empty( $designation['color'] ) ? 'color:' . $designation['color'] . ';' : null;
			$cCss .= ! empty( $designation['align'] ) ? 'text-align:' . $designation['align'] . ';' : null;
			$cCss .= ! empty( $designation['size'] ) ? 'font-size:' . $designation['size'] . 'px;' : null;
			$cCss .= ! empty( $designation['weight'] ) ? 'font-weight:' . $designation['weight'] . ';' : null;

			$css .= "#{$layoutID} .layout9 .tlp-label-role,#{$layoutID} .tlp-position,
					#{$layoutID} .tlp-position a,
					#{$layoutID} .overlay .tlp-position,
					#{$layoutID} .tlp-layout-isotope .overlay .tlp-position{ {$cCss} }";

			if ( ! empty( $designation['hover_color'] ) ) {
				$css .= "#{$layoutID} .tlp-position:hover,
						#{$layoutID} .tlp-position a:hover,
						#{$layoutID} .overlay .tlp-position:hover,
						#{$layoutID} .tlp-layout-isotope .overlay .tlp-position:hover{ color: {$designation['hover_color']}; }";
			}
		}

		// Short biography
		if ( ! empty( $short_bio ) ) {
			$cCss  = null;
			$cCss .= ! empty( $short_bio['color'] ) ? 'color:' . $short_bio['color'] . ';' : null;
			$cCss .= ! empty( $short_bio['align'] ) ? 'text-align:' . $short_bio['align'] . ';' : null;
			$cCss .= ! empty( $short_bio['size'] ) ? 'font-size:' . $short_bio['size'] . 'px;' : null;
			$cCss .= ! empty( $short_bio['weight'] ) ? 'font-weight:' . $short_bio['weight'] . ';' : null;
			$css  .= "#{$layoutID} .short-bio p,#{$layoutID} .short-bio p a,
					#{$layoutID} .overlay .short-bio p, #{$layoutID} .overlay .short-bio p a{{$cCss}}";
		}

		// Email
		if ( ! empty( $email ) ) {
			$cCss  = null;
			$cCss .= ! empty( $email['color'] ) ? 'color:' . $email['color'] . ';' : null;
			$cCss .= ! empty( $email['size'] ) ? 'font-size:' . $email['size'] . 'px;' : null;
			$cCss .= ! empty( $email['weight'] ) ? 'font-weight:' . $email['weight'] . ';' : null;
			$cCss .= ! empty( $email['align'] ) ? 'text-align:' . $email['align'] . ';' : null;
			$css  .= "#{$layoutID} .tlp-email, #{$layoutID} a .tlp-email{ {$cCss} }";
		}

		// Web URL
		if ( ! empty( $web_url ) ) {
			$cCss  = null;
			$cCss .= ! empty( $web_url['color'] ) ? 'color:' . $web_url['color'] . ';' : null;
			$cCss .= ! empty( $web_url['size'] ) ? 'font-size:' . $web_url['size'] . 'px;' : null;
			$cCss .= ! empty( $web_url['weight'] ) ? 'font-weight:' . $web_url['weight'] . ';' : null;
            $cCss .= ! empty( $web_url['align'] ) ? 'text-align:' . $web_url['align'] . ';' : null;
			// .tlp-website (the <li> block) added so text-align applies — see sc-css.php.
			$css  .= "#{$layoutID} .tlp-url,#{$layoutID} .tlp-website{{$cCss}}";
		}

		// Telephone
		if ( ! empty( $telephone ) ) {
			$cCss  = null;
			$cCss .= ! empty( $telephone['color'] ) ? 'color:' . $telephone['color'] . ';' : null;
			$cCss .= ! empty( $telephone['size'] ) ? 'font-size:' . $telephone['size'] . 'px;' : null;
			$cCss .= ! empty( $telephone['weight'] ) ? 'font-weight:' . $telephone['weight'] . ';' : null;
            $cCss .= ! empty( $telephone['align'] ) ? 'text-align:' . $telephone['align'] . ';' : null;
			$css  .= "#{$layoutID} .tlp-phone{{$cCss}}";
		}
		// mobile
		if ( ! empty( $mobile ) ) {
			$cCss  = null;
			$cCss .= ! empty( $mobile['color'] ) ? 'color:' . $mobile['color'] . ';' : null;
			$cCss .= ! empty( $mobile['size'] ) ? 'font-size:' . $mobile['size'] . 'px;' : null;
			$cCss .= ! empty( $mobile['weight'] ) ? 'font-weight:' . $mobile['weight'] . ';' : null;
            $cCss .= ! empty( $mobile['align'] ) ? 'text-align:' . $mobile['align'] . ';' : null;
			$css  .= "#{$layoutID} .tlp-mobile{{$cCss}}";
		}
		// Fax
		if ( ! empty( $fax ) ) {
			$cCss  = null;
			$cCss .= ! empty( $fax['color'] ) ? 'color:' . $fax['color'] . ';' : null;
			$cCss .= ! empty( $fax['size'] ) ? 'font-size:' . $fax['size'] . 'px;' : null;
			$cCss .= ! empty( $fax['weight'] ) ? 'font-weight:' . $fax['weight'] . ';' : null;
            $cCss .= ! empty( $fax['align'] ) ? 'text-align:' . $fax['align'] . ';' : null;
			$css  .= "#{$layoutID} .tlp-fax{{$cCss}}";
		}

		// Location
		if ( ! empty( $location ) ) {
			$cCss  = null;
			$cCss .= ! empty( $location['color'] ) ? 'color:' . $location['color'] . ';' : null;
			$cCss .= ! empty( $location['size'] ) ? 'font-size:' . $location['size'] . 'px;' : null;
			$cCss .= ! empty( $location['weight'] ) ? 'font-weight:' . $location['weight'] . ';' : null;
            $cCss .= ! empty( $location['align'] ) ? 'text-align:' . $location['align'] . ';' : null;
			$css  .= "#{$layoutID} .tlp-location{{$cCss}}";
		}

		// Skill
		if ( ! empty( $skill ) ) {
			$cCss  = null;
			$cCss .= ! empty( $skill['color'] ) ? 'color:' . $skill['color'] . ';' : null;
			$cCss .= ! empty( $skill['align'] ) ? 'text-align:' . $skill['align'] . ';' : null;
			$cCss .= ! empty( $skill['size'] ) ? 'font-size:' . $skill['size'] . 'px;' : null;
			$cCss .= ! empty( $skill['weight'] ) ? 'font-weight:' . $skill['weight'] . ';' : null;
			$css  .= "#{$layoutID} .skill_name{{$cCss}}";
		}

		// Social Icon
		if ( ! empty( $social_icon ) ) {
			$cCss  = null;
			$cCss .= ! empty( $social_icon['size'] ) ? 'font-size:' . $social_icon['size'] . 'px;' : null;
			$cCss .= ! empty( $social_icon['weight'] ) ? 'font-weight:' . $social_icon['weight'] . ';' : null;

			if ( $cCss ) {
				$css .= "#{$layoutID} .overlay .social-icons a,
					#{$layoutID} .tlp-social,
					#{$layoutID} .social-icons a{ {$cCss} }";
			}

			// Colour is scoped to the resting state, exactly as templates/sc-css.php now does —
			// keep the two in step. Here the arms are ID-based, so they out-specify a layout's
			// own `… .social-icons a:hover{color:…}` (0,4,1) even without `!important`, and the
			// preview showed the same invisible glyph the front end did on the layouts that flip
			// their chip to #fff on hover. The `:where()` arm is (0,2,1): above the theme's
			// generic `a:hover`, below any layout that states a hover colour.
			if ( ! empty( $social_icon['color'] ) ) {
				$css .= "#{$layoutID} .overlay .social-icons a:not(:hover):not(:focus),
					#{$layoutID} .tlp-social:not(:hover):not(:focus),
					#{$layoutID} .social-icons a:not(:hover):not(:focus){color:{$social_icon['color']};}";
				$css .= ":where(#{$layoutID}) .social-icons a:hover,
					:where(#{$layoutID}) .social-icons a:focus,
					:where(#{$layoutID}) .tlp-social:hover{color:{$social_icon['color']};}";
			}
			if ( ! empty( $social_icon['align'] ) ) {
				$css .= "#{$layoutID} .social-icons,#{$layoutID} .tlp-social, #{$layoutID} .overlay .social-icons { text-align: {$social_icon['align']}; }";
			}
		}

		// Social Icon bg
		if ( $social_icon_bg ) {
			$css .= "#{$layoutID} .social-icons a{background:{$social_icon_bg};}";
		}

		// Shortcode twin of Elementor's "Social → Hover → Background Color". `!important` for
		// the same reason as in templates/sc-css.php: a redesigned card's own chip hover rule
		// out-specifies this one. Keep the two in step.
		if ( ! empty( $social_hover_bg ) ) {
			$css .= "#{$layoutID} .social-icons a:hover{background:{$social_hover_bg} !important;}";
		}

        // Content Bg
        if ( $content_bg ) {
            // isotope2's card surface is `.rt-iso2-card`, layout8/isotope5's is
            // `.single-team-area` — both mirror the arms in templates/sc-css.php;
            // keep the two in step.
            $css .= "#{$layoutID}  .layout17 .single-team-area .tlp-content,#{$layoutID}  .layout1 .single-team-area,#{$layoutID} .layout16 .single-team-area,#{$layoutID} .layout3 .single-team-area,#{$layoutID} .layout10 .tlp-team-item,#{$layoutID} .isotope2 .rt-iso2-card,#{$layoutID} .layout8 .single-team-area,#{$layoutID} .isotope5 .single-team-area,#{$layoutID}  .layout18 .single-team-area .tlp-content,#{$layoutID} .layout18 .single-team-area .tlp-content:after{background:{$content_bg};}";
        }

		// Overlay
		if ( ! empty( $mObg ) ) {
			if ( ! empty( $mObg['color'] ) && ! empty( $mObg['opacity'] ) ) {
				$css .= "#{$layoutID} .single-team-area .overlay,
						#{$layoutID} .single-team-area:hover .tlp-overlay,
						#{$layoutID} .single-team-area:hover .tlp-overlay,
						#{$layoutID} .layout7 figcaption:hover,
						#{$layoutID} .isotope8 .tlp-overlay,
						#{$layoutID} .layout13 .tlp-overlay,
						#{$layoutID} .layout8 .tlp-title,
						#{$layoutID} .isotope5 .tlp-title,
						#{$layoutID} .isotope1 .rt-grid-item:hover .overlay,
						#{$layoutID} .isotope4 figcaption:hover,
						#{$layoutID} .layout11 .single-team-area .tlp-title {";
				$css .= 'background:' . self::TLPhex2rgba(
					$mObg['color'],
					( $mObg['opacity'] ? $mObg['opacity'] : .8 )
				) . ';';
				$css .= '}';
			}
		}

		// Overlay item padding
		if ( $itemP ) {
			// layout8 / isotope5 pad `.tlp-overlay` like layout9: their name lives in
			// `.tlp-title` OUTSIDE the overlay, so the generic `:hover h3` arm aimed at
			// the wrong element — and lost to `.rt-team-container h3 { padding:0
			// !important }` anyway. Mirrors templates/sc-css.php; keep the two in step.
			//
			// layout7 / isotope4 carry no `:hover`: their figcaption is in flow and sets the
			// card height, so padding it only on hover grew the card and shook the page.
			$css .= "#{$layoutID} .single-team-area .overlay .overlay-element,
					#{$layoutID} .single-team-area:hover h3,
					#{$layoutID} .isotope3 .single-team-area:hover h3,
					#{$layoutID} .layout7 figcaption,
					#{$layoutID} .isotope4 figcaption,
					#{$layoutID} .layout8 .tlp-overlay,
					#{$layoutID} .isotope5 .tlp-overlay,
					#{$layoutID} .layout9 .tlp-overlay,
					#{$layoutID} .isotope6 .tlp-overlay,
					#{$layoutID} .layout10 .tlp-overlay,
					#{$layoutID} .isotope7 .tlp-overlay{";
			$css .= 'padding-top:' . $itemP . '%; ';
			$css .= '}';
		}
		$css .= '</style>';

		return $css;
	}

	/**
	 * Shortcode whose Styling tab drives the single team page's Resume / Hire Me buttons.
	 *
	 * That page is not a shortcode, so nothing scoped to `.rt-team-container-{id}` reaches
	 * it. Exactly one shortcode is allowed to emit its button rules a second time under the
	 * page's own `.tlp-single-container` class -- every shortcode doing it would tie on
	 * specificity and leave the winner to whichever block sits last in team-sc.css, i.e. the
	 * shortcode saved most recently. Settings > Detail page field selection > Button style
	 * source names the one that wins.
	 *
	 * @return int Shortcode ID, or 0 for "use the plugin default look".
	 */
	/**
	 * Attachment ID standing in for a member with no photo on the single team page.
	 *
	 * "Default preview image" is a per-shortcode field, and the detail page is not a
	 * shortcode, so Settings > Detail page field selection > Default preview image source
	 * names the one to borrow it from -- the same arrangement as
	 * self::singlePageStyleSourceId() and for the same reason.
	 *
	 * @return int Attachment ID, or 0 for "show no image".
	 */
	public static function singlePageDefaultImageId() {
		// Pro-only setting: without Pro the detail page keeps its current behaviour.
		if ( ! rttlp_team()->has_pro() ) {
			return 0;
		}

		$settings = get_option( rttlp_team()->options['settings'] );
		$source   = ! empty( $settings['detail_default_image_source'] ) ? absint( $settings['detail_default_image_source'] ) : 0;

		if ( ! $source ) {
			return 0;
		}

		return absint( get_post_meta( $source, 'default_preview_image', true ) );
	}

	public static function singlePageStyleSourceId() {
		// Pro-only setting: without Pro the single page keeps the built-in button look.
		if ( ! rttlp_team()->has_pro() ) {
			return 0;
		}

		$settings = get_option( rttlp_team()->options['settings'] );

		return ! empty( $settings['detail_button_style_source'] ) ? absint( $settings['detail_button_style_source'] ) : 0;
	}

	public static function generatorShortcodeCss( $scID ) {
		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$upload_dir     = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir'];
		$cssFile        = $upload_basedir . '/tlp-team/team-sc.css';

		$css = self::render( 'sc-css', compact( 'scID' ), true );

		/*
		 * Wrap this shortcode's rules in its own fence. An empty $css is kept as
		 * an empty string on purpose rather than bailing out: the block below
		 * still has to strip the PREVIOUS fence. Bailing here meant that clearing
		 * every field on the Styling tab left the old rules live on the front end
		 * forever, because the file was never rewritten.
		 */
		$css = $css ? sprintf( '/*sc-%2$d-start*/%1$s/*sc-%2$d-end*/', $css, $scID ) : '';

		if ( file_exists( $cssFile ) && ( $oldCss = $wp_filesystem->get_contents( $cssFile ) ) ) {
			if ( strpos( $oldCss, '/*sc-' . $scID . '-start' ) !== false ) {
				$oldCss = preg_replace( '/\/\*sc-' . $scID . '-start[\s\S]+?sc-' . $scID . '-end\*\//', '', $oldCss );
				$oldCss = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $oldCss );
			}
			$css = $oldCss . $css;
		} elseif ( ! file_exists( $cssFile ) ) {
			$upload_basedir_trailingslashit = trailingslashit( $upload_basedir );
			$wp_filesystem->mkdir( $upload_basedir_trailingslashit . 'tlp-team' );
		}

		if ( ! $wp_filesystem->put_contents( $cssFile, $css ) ) {
			/*  phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log */
			error_log( print_r( 'Team: Error Generated css file ', true ) );
		}
	}

	/**
	 * Generate Shortcode css
	 *
	 * @param integer $scID
	 *
	 * @return void
	 */
    public static function removeGeneratorShortcodeCss( $scID ) {
        $upload_dir     = wp_upload_dir();
        $upload_basedir = $upload_dir['basedir'];
        $cssFile        = $upload_basedir . '/tlp-team/team-sc.css';
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if ( file_exists( $cssFile ) ) {
            $oldCss = file_get_contents( $cssFile ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            if ( $oldCss !== false && strpos( $oldCss, '/*sc-' . $scID . '-start' ) !== false ) {
                // Ensure $oldCss is a string before passing to preg_replace
                $css = preg_replace('/\/\*sc-' . $scID . '-start[\s\S]+?sc-' . $scID . '-end\*\//', '', $oldCss);

                // Ensure $css is a string before cleaning up line breaks
                if (!empty($css)) {
                    $css = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $css);
                } else {
                    $css = ''; // Default to an empty string if preg_replace returns null
                }
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
                file_put_contents( $cssFile, $css );
            }
        }
    }

	public static function rt_plugin_team_sc_pro_information() {
		?>
		<div class="rt-document-box">
			<div class="rt-box-icon"><i class="dashicons dashicons-media-document"></i></div>
			<div class="rt-box-content">
				<h3 class="rt-box-title">Documentation</h3>
				<p>Get started by spending some time with the documentation we included step by step process with
					screenshots with video.</p>
				<a href="<?php echo esc_url( rttlp_team()->documentation_link() ); ?>" target="_blank" class="rt-admin-btn">Documentation</a>
			</div>
		</div>
		<div class="rt-document-box">
			<div class="rt-box-icon"><i class="dashicons dashicons-sos"></i></div>
			<div class="rt-box-content">
				<h3 class="rt-box-title">Need Help?</h3>
				<p>Stuck with something? Please create a
					<a href="<?php echo esc_url( rttlp_team()->ticket_link() ); ?>">ticket here</a> or post on <a href="<?php echo esc_url( rttlp_team()->fb_link() ); ?>">facebook group</a>. For emergency
					case join our <a href="<?php echo esc_url( rttlp_team()->radius_link() ); ?>">live chat</a>.</p>
				<a href="<?php echo esc_url( rttlp_team()->ticket_link() ); ?>" target="_blank" class="rt-admin-btn">Get
					Support</a>
			</div>
		</div>
		<?php
	}

	public static function swiper_options() {
		$options = [
			'speed'         => (int) 1000,
			'slidesPerView' => (int) 1,
			'loop'          => (bool) true,
			'autoplay'      => [
				'delay'                => (int) 7000,
				'pauseOnMouseEnter'    => (bool) true,
				'disableOnInteraction' => (bool) false,
			],
		];

		return $options;
	}

	/**
	 * Register Elementor widget controls.
	 *
	 * Adds different control fields into the widget settings.
	 *
	 * @param array  $fields Control fields to add.
	 * @param object $obj Object in which controls are adding.
	 *
	 * @return void
	 *
	 * @access public
	 */
	public static function addElControls( $fields, $obj ) {
		foreach ( $fields as $field ) {
			if ( ! empty( $field['type'] ) ) {
				$field['type'] = self::elFields( $field['type'] );
			}
			if ( isset( $field['mode'] ) && 'section_start' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->start_controls_section( $id, $field );
			} elseif ( isset( $field['mode'] ) && 'section_end' === $field['mode'] ) {
				$obj->end_controls_section();
			} elseif ( isset( $field['mode'] ) && 'tabs_start' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->start_controls_tabs( $id );
			} elseif ( isset( $field['mode'] ) && 'tabs_end' === $field['mode'] ) {
				$obj->end_controls_tabs();
			} elseif ( isset( $field['mode'] ) && 'tab_start' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->start_controls_tab( $id, $field );
			} elseif ( isset( $field['mode'] ) && 'tab_end' === $field['mode'] ) {
				$obj->end_controls_tab();
			} elseif ( isset( $field['mode'] ) && 'group' === $field['mode'] ) {
				$type          = $field['type'];
				$field['name'] = $field['id'];
				unset( $field['mode'] );
				unset( $field['type'] );
				unset( $field['id'] );
				$obj->add_group_control( $type, $field );
			} elseif ( isset( $field['mode'] ) && 'responsive' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->add_responsive_control( $id, $field );
			} else {
				$id = $field['id'];
				unset( $field['id'] );
				$obj->add_control( $id, $field );
			}
		}
	}

	/**
	 * Elementor Fields.
	 *
	 * @param string $type Control type.
	 *
	 * @return object
	 */
	private static function elFields( $type ) {
		$controls = \Elementor\Controls_Manager::class;

		switch ( $type ) {
			case 'text':
				$type = $controls::TEXT;
				break;

			case 'html':
				$type = $controls::RAW_HTML;
				break;

			case 'select':
				$type = $controls::SELECT;
				break;

			case 'select2':
				$type = $controls::SELECT2;
				break;

			case 'number':
				$type = $controls::NUMBER;
				break;

			case 'image-dimensions':
				$type = $controls::IMAGE_DIMENSIONS;
				break;

			case 'dimensions':
				$type = $controls::DIMENSIONS;
				break;

			case 'media':
				$type = $controls::MEDIA;
				break;

			case 'switch':
				$type = $controls::SWITCHER;
				break;

			case 'color':
				$type = $controls::COLOR;
				break;

			case 'choose':
				$type = $controls::CHOOSE;
				break;

			case 'slider':
				$type = $controls::SLIDER;
				break;

			case 'typography':
				$type = \Elementor\Group_Control_Typography::get_type();
				break;

			case 'border':
				$type = \Elementor\Group_Control_Border::get_type();
				break;

			case 'box-shadow':
				$type = \Elementor\Group_Control_Box_Shadow::get_type();
				break;
		}

		return $type;
	}

	/**
	 * Adding filter hook.
	 *
	 * @param string $filterName Filter hook name.
	 * @param string $var Variable to apply.
	 * @param object $obj Reference object.
	 *
	 * @return mixed
	 */
	public static function filter( $filterName, $obj ) {
		return array_merge( apply_filters( $filterName, $obj ) );
	}

	/**
	 * Prints HTMl.
	 *
	 * @param string $html HTML.
	 * @param bool   $allHtml All HTML.
	 *
	 * @return mixed
	 */
	public static function print_html( $html, $allHtml = false ) {
		if ( $allHtml ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo stripslashes_deep( $html );
		} else {
			echo wp_kses_post( stripslashes_deep( $html ) );
		}
	}

	/**
	 * Allowed HTML for wp_kses.
	 *
	 * @param string $level Tag level.
	 *
	 * @return mixed
	 */
	public static function allowedHtml( $level = 'basic' ) {
		$allowed_html = [];

		switch ( $level ) {
			case 'basic':
				$allowed_html = [
					'b'      => [
						'class' => [],
						'id'    => [],
					],
					'i'      => [
						'class' => [],
						'id'    => [],
					],
					'u'      => [
						'class' => [],
						'id'    => [],
					],
					'br'     => [
						'class' => [],
						'id'    => [],
					],
					'em'     => [
						'class' => [],
						'id'    => [],
					],
					'span'   => [
						'class' => [],
						'id'    => [],
					],
					'strong' => [
						'class' => [],
						'id'    => [],
					],
					'hr'     => [
						'class' => [],
						'id'    => [],
					],
					'p'     => [
						'class' => [],
						'id'    => [],
					],
					'div'   => [
						'class' => [],
						'id'    => [],
					],
					'a'      => [
						'href'   => [],
						'title'  => [],
						'class'  => [],
						'id'     => [],
						'target' => [],
					],
				];
				break;

			case 'advanced':
				$allowed_html = [
					'b'      => [
						'class' => [],
						'id'    => [],
					],
					'i'      => [
						'class' => [],
						'id'    => [],
					],
					'u'      => [
						'class' => [],
						'id'    => [],
					],
					'br'     => [
						'class' => [],
						'id'    => [],
					],
					'em'     => [
						'class' => [],
						'id'    => [],
					],
					'span'   => [
						'class' => [],
						'id'    => [],
					],
					'strong' => [
						'class' => [],
						'id'    => [],
					],
					'hr'     => [
						'class' => [],
						'id'    => [],
					],
					'a'      => [
						'href'   => [],
						'title'  => [],
						'class'  => [],
						'id'     => [],
						'target' => [],
					],
					'input'  => [
						'type'   => [],
						'name'   => [],
						'class'  => [],
						'value'  => [],
					],
				];
				break;

			case 'image':
				$allowed_html = [
					'img' => [
						'src'      => [],
						'data-src' => [],
						'alt'      => [],
						'height'   => [],
						'width'    => [],
						'class'    => [],
						'id'       => [],
						'style'    => [],
						'srcset'   => [],
						'loading'  => [],
						'sizes'    => [],
					],
					'div' => [
						'class' => [],
					],
				];
				break;

			case 'anchor':
				$allowed_html = [
					'a' => [
						'href'  => [],
						'title' => [],
						'class' => [],
						'id'    => [],
						'style' => [],
					],
				];
				break;

			default:
				// code...
				break;
		}
		return $allowed_html;
	}
    public static function print_validated_html_tag( $tag ) {
        self::print_html( self::get_validated_html_tag( $tag ) );
    }
    public static function get_validated_html_tag( $tag ) {
        $allowed_html_wrapper_tags = [
            'a',
            'article',
            'aside',
            'button',
            'div',
            'footer',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'header',
            'main',
            'nav',
            'p',
            'section',
            'span',
        ];

        return in_array( strtolower( $tag ), $allowed_html_wrapper_tags, true ) ? $tag : 'div';
    }
    public static function get_header($wp_version) {
        if ( version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) { ?>
            <!doctype html>
        <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo( 'charset' ); ?>">
                <?php wp_head(); ?>
            </head>
        <body <?php body_class(); ?>>
            <?php wp_body_open(); ?>
            <div class="wp-site-blocks">
            <?php
            $theme      = wp_get_theme();
            $theme_slug = $theme->get( 'TextDomain' );
            echo do_blocks( '<!-- wp:template-part {"slug":"header","theme":"' . esc_attr( $theme_slug ) . '","tagName":"header","className":"site-header"} /-->' );
        } else {
            get_header();
        }
    }

    public static function get_footer($wp_version) {
        if ( version_compare( $wp_version, '5.9', '>=' ) && function_exists( 'wp_is_block_theme' ) && true === wp_is_block_theme() ) {
            $theme      = wp_get_theme();
            $theme_slug = $theme->get( 'TextDomain' );
            echo do_blocks('<!-- wp:template-part {"slug":"footer","theme":"' . esc_attr( $theme_slug ) . '","tagName":"footer","className":"site-footer"} /-->');
            echo '</div>';
            wp_footer();
            echo '</body>';
            echo '</html>';
        } else {
            get_footer();
        }
    }

	/**
	 * Definition for wp_kses.
	 *
	 * @param string $string String to check.
	 * @param string $level Tag level.
	 *
	 * @return mixed
	 */
	public static function htmlKses( $string, $level ) {
		if ( empty( $string ) ) {
			return;
		}

		return wp_kses( $string, self::allowedHtml( $level ) );
	}

    public static function el_pro_grid_layouts()
    {
        $status = !rttlp_team()->has_pro();
        return [
            'layout-el-4' => [
                'title' => esc_html__( 'Layout 3', 'tlp-team' ),
                'url'   => rttlp_team()->assets_url() . 'images/layouts/layout4.png',
                'is_pro' => $status,
            ],
            'layout-el-6' => [
                'title' => esc_html__( 'Layout 4', 'tlp-team' ),
                'url'   => rttlp_team()->assets_url() . 'images/layouts/layout6.png',
                'is_pro' => $status,
            ],
            'layout7' => [
                'title'  => esc_html__( 'Layout 5', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout7.png',
                'is_pro' => $status,
            ],
            'layout-el-8' => [
                'title'  => esc_html__( 'Layout 6', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout8.png',
                'is_pro' => $status,
            ],
            'layout9'   =>[
                'title'  => esc_html__( 'Layout 7', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout9.png',
                'is_pro' => $status,
            ],
            'layout-el-10' =>[
                'title'  => esc_html__( 'Layout 8', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout10.png',
                'is_pro' => $status,
            ],
            'layout11'   =>[
                'title'  => esc_html__( 'Layout 9', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout11.png',
                'is_pro' => $status,
            ],
            'layout12'   =>[
                'title'  => esc_html__( 'Layout 10', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout12.png',
                'is_pro' => $status,
            ],
            'layout13'   =>[
                'title'  => esc_html__( 'Layout 11', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout13.png',
                'is_pro' => $status,
            ],
            'layout14'   =>[
                'title'  => esc_html__( 'Layout 12', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout14.png',
                'is_pro' => $status,
            ],
            'layout15'   =>[
                'title'  => esc_html__( 'Layout 13', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout15.png',
                'is_pro' => $status,
            ],
            'layout17'   =>[
                'title'  => esc_html__( 'Layout 17', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout15.png',
                'is_pro' => $status,
            ],
            'layout18'   =>[
                'title'  => esc_html__( 'Layout 18', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/layout15.png',
                'is_pro' => $status,
            ],
            'special01'  =>[
                'title'  => esc_html__( 'Special 01', 'tlp-team' ),
                'url'    => rttlp_team()->assets_url() . 'images/layouts/special01.png',
                'is_pro' => $status,
            ]
        ];
    }
}
