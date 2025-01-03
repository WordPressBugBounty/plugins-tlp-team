<?php
/**
 * Template: List Layout 1.
 *
 * @package RT_Team
 */

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$html = null;

$html .= '<div class="' . esc_attr( $grid ) . ' ' . esc_attr( $class ) . '" data-id="' . absint( $mID ) . '">';
$html .= '<div class="single-team-area">';

if ( $imgHtml ) {
	$html .= '<div class="img-area ' . esc_attr( $image_area ) . '">';

	if ( $link ) {
		$html .= '<figure><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" href="' . esc_url( $pLink ) . '">' . Fns::htmlKses( $imgHtml, 'image' ) . '</a></figure>';
	} else {
		$html .= '<figure>' . Fns::htmlKses( $imgHtml, 'image' ) . '</figure>';
	}

	$html .= '</div>';
}

$html .= '<div class="rttm-content-area ' . esc_attr( $content_area ) . '">';

if ( in_array( 'name', $items, true ) && $title ) {
	if ( $link ) {
		$html .= '<h3><span class="team-name"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" title="' . esc_attr( $title ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $title ) . '</a></span></h3>';
	} else {
		$html .= '<h3><span class="team-name">' . esc_html( $title ) . '</span></h3>';
	}
}

if ( in_array( 'designation', $items, true ) && $designation ) {
	if ( $link ) {
		$html .= '<div class="tlp-position"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" title="' . esc_attr( $title ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $designation ) . '</a></div>';
	} else {
		$html .= '<div class="tlp-position">' . esc_html( $designation ) . '</div>';
	}
}

if ( in_array( 'tax_department', $items, true ) && $tax_department ) {
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

$read_more_btn = isset( $read_more_btn_text ) ? Fns::get_formatted_readmore_text($items, $read_more_btn_text, $anchorClass, $mID, $target, $title, $pLink) : null;
$resume_btn = isset( $ttp_my_resume ) ? Fns::get_formatted_resume( $items, $ttp_my_resume, $my_resume_text ) : null;
$hire_me_btn = isset( $ttp_hire_me ) ? Fns::get_formatted_hire_me( $items, $ttp_hire_me, $hire_me_text ) : null;
if ( $read_more_btn || $resume_btn || $hire_me_btn ) {
    $html .= '<div class="readmore-btn">';
    if( $resume_btn ){
        $html .= $resume_btn;
    }
    if( $hire_me_btn ){
        $html .= $hire_me_btn;
    }
    $html .= '</div>';
    $html .= '<div class="readmore-btn hirme-resume">';
    if( $read_more_btn ){
        $html .= $read_more_btn;
    }
    $html .= '</div>';
}

$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

Fns::print_html( $html );