<?php
/**
 * Template: Carousel Layout 1 (Slider) — redesigned card.
 *
 * Own hook `rttm-slider1`. It deliberately NO LONGER borrows the `.layout3` class
 * (grid Layout 3's redesign would otherwise clip the slide). `$grid` / `$class` are
 * kept because they carry the Swiper `.swiper-slide` + column classes the carousel
 * JS needs. Plugin element classes (h3, .tlp-position, .short-bio, .social-icons,
 * .readmore-btn .rt-ream-me-btn) are preserved so the Styling tab + Primary Color
 * keep working.
 *
 * @package RT_Team
 */

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$html = null;

$html .= '<div class="' . esc_attr( $grid ) . ' ' . esc_attr( $class ) . ' rttm-slider1" data-id="' . absint( $mID ) . '">';
$html .= '<div class="single-team-area">';

// ---- Media: photo + social chips that slide up on hover ----
$html .= '<div class="rttm-slide-media">';

if ( $imgHtml ) {
	if ( $link ) {
		$html .= '<figure><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" href="' . esc_url( $pLink ) . '">' . Fns::htmlKses( $imgHtml, 'image' ) . '</a></figure>';
	} else {
		$html .= '<figure>' . Fns::htmlKses( $imgHtml, 'image' ) . '</figure>';
	}
}

$html .= Fns::get_formatted_social_link( $sLink, $items );

$html .= '</div>'; // .rttm-slide-media

// ---- Band: name + role on the accent gradient ----
$band = '';

if ( in_array( 'name', $items, true ) && $title ) {
	if ( $link ) {
		$band .= '<h3><span class="team-name"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" title="' . esc_attr( $title ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $title ) . '</a></span></h3>';
	} else {
		$band .= '<h3><span class="team-name">' . esc_html( $title ) . '</span></h3>';
	}
}

if ( in_array( 'designation', $items, true ) && $designation ) {
	if ( $link ) {
		$band .= '<div class="tlp-position"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" title="' . esc_attr( $title ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $designation ) . '</a></div>';
	} else {
		$band .= '<div class="tlp-position">' . esc_html( $designation ) . '</div>';
	}
}

$html .= $band ? '<div class="rttm-slide-band">' . $band . '</div>' : '';

// ---- Body: department + bio + contact + skill + buttons ----
$html .= '<div class="rttm-slide-body">';

// The field-selection key is `department`; older card templates check `tax_department`
// (a pre-existing mismatch, so department never rendered). Accept BOTH keys, and use
// ! empty() because the carousel render scope does not always define $tax_department.
if ( ( in_array( 'department', $items, true ) || in_array( 'tax_department', $items, true ) ) && ! empty( $tax_department ) ) {
	$html .= '<div class="tlp-department">' . esc_html( $tax_department ) . '</div>';
}

$html .= Fns::get_formatted_short_bio( $short_bio, $items );

$html .= Fns::get_formatted_contact_info(
	[
		'email'     => $email,
		'telephone' => $telephone,
		'mobile'    => $mobile,
		'fax'       => $fax,
		'location'  => $location,
		'web_url'   => $web_url,
	],
	$items
);

$html .= Fns::get_formatted_skill( $tlp_skill, $items );

$read_more_btn = isset( $read_more_btn_text ) ? Fns::get_formatted_readmore_text( $items, $read_more_btn_text, $anchorClass, $mID, $target, $title, $pLink ) : null;
$resume_btn    = isset( $ttp_my_resume ) ? Fns::get_formatted_resume( $items, $ttp_my_resume, $my_resume_text ) : null;
$hire_me_btn   = isset( $ttp_hire_me ) ? Fns::get_formatted_hire_me( $items, $ttp_hire_me, $hire_me_text ) : null;

if ( $read_more_btn || $resume_btn || $hire_me_btn ) {
	$html .= '<div class="rttm-slide-foot">';

	if ( $resume_btn || $hire_me_btn ) {
		$html .= '<div class="readmore-btn">';
		if ( $resume_btn ) {
			$html .= $resume_btn;
		}
		if ( $hire_me_btn ) {
			$html .= $hire_me_btn;
		}
		$html .= '</div>';
	}

	if ( $read_more_btn ) {
		$html .= '<div class="readmore-btn hirme-resume">' . $read_more_btn . '</div>';
	}

	$html .= '</div>'; // .rttm-slide-foot
}

$html .= '</div>'; // .rttm-slide-body

$html .= '</div>'; // .single-team-area
$html .= '</div>'; // slide / column

Fns::print_html( $html );
