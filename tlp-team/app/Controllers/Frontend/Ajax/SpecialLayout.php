<?php
/**
 * Special Layout Ajax Class.
 *
 * @package RT_Team
 */

namespace RT\Team\Controllers\Frontend\Ajax;

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Special Layout Ajax Class.
 */
class SpecialLayout {
	use \RT\Team\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_rtGetSpecialLayoutData', [ $this, 'response' ] );
		add_action( 'wp_ajax_nopriv_rtGetSpecialLayoutData', [ $this, 'response' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {

		$memberId = ! empty( $_REQUEST['memberId'] ) ? absint( $_REQUEST['memberId'] ) : null;
		$toggleId = ! empty( $_REQUEST['toggleId'] ) ? absint( $_REQUEST['toggleId'] ) : null;
		$scID     = ! empty( $_REQUEST['scID'] ) ? absint( $_REQUEST['scID'] ) : null;
		$html     = $toggle_image_src = null;
		$error    = true;

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error( [
				'data'  => __('Security Issue','tlp-team'),
				'error' => $error,
			] );

		}

		if ( $memberId ) {
			$name        = get_the_title( $memberId );
			$designation = wp_strip_all_tags(
				get_the_term_list(
					$memberId,
					rttlp_team()->taxonomies['designation'],
					null,
					', '
				)
			);
			$short_bio   = get_post_meta( $memberId, 'short_bio', true );
			$imgHtml     = Fns::getFeatureImageHtml( $memberId );

			if ( $toggleId ) {
				$toggle_image_src = Fns::getFeatureImageSrc( $toggleId );
			}

            $settings     = get_option( rttlp_team()->options['settings'] );
            $resume_btn_text = isset( $settings['resume_btn_text'] ) ? $settings['resume_btn_text'] : "Resume";
            $hire_btn_text = isset( $settings['hire_me_text'] ) ? $settings['hire_me_text'] : "Hire Me";
            $resume_url      = get_post_meta( $memberId, 'ttp_my_resume', true );
            $hire_me_url     = get_post_meta( $memberId, 'ttp_hire_me', true );

			$fields    = get_post_meta( $scID, 'ttp_selected_field' );
			$htmlName  = $htmlDesignation = $htmlShortBio = $htmlCInfo = $anchorClass = null;
			$email     = get_post_meta( $memberId, 'email', true );
			$web_url   = get_post_meta( $memberId, 'web_url', true );
			$telephone = get_post_meta( $memberId, 'telephone', true );
			$mobile    = get_post_meta( $memberId, 'mobile', true );
			$fax       = get_post_meta( $memberId, 'fax', true );
			$location  = get_post_meta( $memberId, 'location', true );
			$link      = get_post_meta( $scID, 'ttp_detail_page_link', true );
			$linkType  = get_post_meta( $scID, 'ttp_detail_page_link_type', true );
			$linkType  = ! empty( $linkType ) ? $linkType : 'popup';
			$pLink     = get_permalink( $memberId );

			if ( $link && $linkType == 'popup' ) {
				$popupType = get_post_meta( $scID, 'ttp_popup_type', true );
				$popupType = ! empty( $popupType ) ? $popupType : 'single';
				if ( $popupType == 'single' ) {
					$anchorClass .= ' ttp-single-md-popup';
				} elseif ( $popupType == 'multiple' ) {
					$anchorClass .= ' ttp-multi-popup';
				} elseif ( $popupType == 'smart' ) {
					$anchorClass .= ' ttp-smart-popup';
				}
			}

			if ( $name && in_array( 'name', $fields ) ) {
				if ( $link ) {
					$htmlName = '<h3><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $memberId ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $name ) . '</a></h3>';
				} else {
					$htmlName = '<h3>' . esc_html( $name ) . '</h3>';
				}
			}

			if ( $designation && in_array( 'designation', $fields ) ) {
				if ( $link ) {
					$htmlDesignation = '<div class="tlp-position"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $memberId ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $designation ) . '</a></div>';
				} else {
					$htmlDesignation = '<div class="tlp-position">' . esc_html( $designation ) . '</div>';
				}
			}

			if ( $short_bio && in_array( 'short_bio', $fields ) ) {
				$htmlShortBio = '<div class="special-selected-short-bio short-bio"><p>' . Fns::htmlKses( $short_bio, 'basic' ) . '</p></div>';
			}

			if ( $email && in_array( 'email', $fields ) ) {
				$htmlCInfo .= '<li class="tlp-email"><i class="far fa-envelope"></i> <a href="mailto:' . esc_attr( $email ) . '"><span>' . esc_html( $email ) . '</span></a> </li>';
			}

			if ( $telephone && in_array( 'telephone', $fields ) ) {
				$htmlCInfo .= '<li class="tlp-phone"><i class="fa fa-phone"></i> <a href="tel:' . esc_attr( $telephone ) . '">' . esc_html( $telephone ) . '</a></li>';
			}

			if ( $fax && in_array( 'fax', $fields ) ) {
				$htmlCInfo .= '<li class="tlp-fax"><i class="fa fa-fax"></i> <a href="fax:' . esc_attr( $fax ) . '"> <span>' . esc_html( $fax ) . '</span> </a> </li>';
			}

			if ( $mobile && in_array( 'mobile', $fields ) ) {
				$htmlCInfo .= '<li class="tlp-mobile"><i class="fa fa-mobile"></i> <a href="tel:' . esc_attr( $mobile ) . '"><span>' . esc_html( $mobile ) . '</span></a> </li>';
			}

			if ( $location && in_array( 'location', $fields ) ) {
				$htmlCInfo .= '<li class="tlp-location"><i class="fa fa-map-marker"></i> <span>' . esc_html( $location ) . '</span> </li>';
			}

			if ( $web_url && in_array( 'web_url', $fields ) ) {
				$htmlCInfo .= '<li class="tlp-web-url"><i class="fa fa-globe"></i> <a href="' . esc_url( $web_url ) . '">' . esc_html( $web_url ) . '</a> </li>';
			}

            $resume  = $resume_url && in_array( 'resume_btn', $fields );
            $hire_me = $hire_me_url && in_array( 'hire_me_btn', $fields );
            if( ( $resume && $resume_btn_text ) || ( $hire_me && $hire_btn_text ) ) {
                $htmlCInfo .= '<div class="rt-team-container">';
                $htmlCInfo .= '<div class="readmore-btn">';
                if( $resume && $resume_btn_text ){
                    $htmlCInfo .= '<a class="rt-resume-btn" data-id="480" target="_self" title="'. esc_attr( $resume_btn_text ) .'" href="'. esc_url( $resume_url ) .'" class="rt-resume-btn">'. esc_html( $resume_btn_text ) .'</a>';
                }
                if( $hire_me && $hire_btn_text ){
                    $htmlCInfo .= '<a class="rt-hire-btn" data-id="480" target="_self" title="'. esc_attr( $hire_btn_text ) .'" href="'. esc_url( $hire_me_url ) .'" class="rt-resume-btn">'. esc_html( $hire_btn_text ) .'</a>';
                }
                $htmlCInfo .= '</div>';
                $htmlCInfo .= '</div>';
            }



			$htmlCInfo = $htmlCInfo ? '<div class="contact-info"><ul>' . $htmlCInfo . '</ul></div>' : null;
			$html .= "<div class='special-selected-top-wrap'><div class='rt-col-xs-6 img'>" . $imgHtml . '</div>';
			$html .= '<div class="rt-col-xs-6 ttp-label"> <div class="ttp-label-inner">' . $htmlName . $htmlDesignation . '</div></div></div>';
			$html .= '<div class="rt-col-sm-12">' . $htmlShortBio . $htmlCInfo . '</div>';
			$error = false;
		}

		wp_send_json(
			[
				'data'             => wp_kses_post( $html ),
				'toggle_image_src' => $toggle_image_src,
				'error'            => $error,
				'field'            => $fields,
			]
		);

		die();
	}
}
