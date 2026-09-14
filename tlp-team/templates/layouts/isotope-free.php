<?php
/**
 * Template: Isotope Layout Free — redesigned card ("Team Isotope Filter Layout").
 *
 * Own hook `rttm-iso`: square photo → accent gradient band (name + role) → body
 * (department + bio + contact chips + social chips + Read More). `$grid`/`$class`/
 * `$isoFilter` are preserved because they carry the column + Isotope filter classes
 * (`.iso_NN`) the isotope JS needs. Plugin element classes are kept so the Styling tab
 * + Primary Color keep working. Card CSS is in assets/css/tlpteam.css under `.rttm-iso`.
 *
 * @package RT_Team
 */

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$html         = null;
$isoFilter    = isset( $isoFilter ) ? $isoFilter : '';
$wrapperClass = $grid . ' ' . $class . ' ' . $isoFilter . ' rttm-iso';

$html .= '<div class="' . esc_attr( $wrapperClass ) . '" data-id="' . absint( $mID ) . '">';
$html .= '<div class="single-team-area">';

// ---- Media: square photo ----
$html .= '<div class="rttm-iso-media">';
if ( $imgHtml ) {
	if ( $link ) {
		$html .= '<figure><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" href="' . esc_url( $pLink ) . '">' . Fns::htmlKses( $imgHtml, 'image' ) . '</a></figure>';
	} else {
		$html .= '<figure>' . Fns::htmlKses( $imgHtml, 'image' ) . '</figure>';
	}
}
$html .= '</div>';

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
$html .= $band ? '<div class="rttm-iso-band">' . $band . '</div>' : '';

// ---- Body: department + bio + contact + skill + social + buttons ----
$html .= '<div class="rttm-iso-body">';

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
$html .= Fns::get_formatted_social_link( $sLink, $items );

$read_more_btn = isset( $read_more_btn_text ) ? Fns::get_formatted_readmore_text( $items, $read_more_btn_text, $anchorClass, $mID, $target, $title, $pLink ) : null;
$resume_btn    = isset( $ttp_my_resume ) ? Fns::get_formatted_resume( $items, $ttp_my_resume, $my_resume_text ) : null;
$hire_me_btn   = isset( $ttp_hire_me ) ? Fns::get_formatted_hire_me( $items, $ttp_hire_me, $hire_me_text ) : null;

if ( $read_more_btn || $resume_btn || $hire_me_btn ) {
	$html .= '<div class="rttm-iso-foot">';
	if ( $resume_btn || $hire_me_btn ) {
		$html .= '<div class="readmore-btn">';
		$html .= $resume_btn ? $resume_btn : '';
		$html .= $hire_me_btn ? $hire_me_btn : '';
		$html .= '</div>';
	}
	if ( $read_more_btn ) {
		$html .= '<div class="readmore-btn hirme-resume">' . $read_more_btn . '</div>';
	}
	$html .= '</div>';
}

$html .= '</div>'; // .rttm-iso-body

$html .= '</div>'; // .single-team-area
$html .= '</div>'; // isotope item

Fns::print_html( $html );
