<?php
/**
 * Template: Grid Layout 4.
 *
 * Dark-scrim sibling of Grid Layout 3. Same interaction model — a persistent
 * name/role caption at rest that gives way to a full hover reveal carrying every
 * enabled field — but themed as a dark glass card (see the `.rt-l4-*` block in
 * assets/css/tlpteam.css) instead of Layout 3's brand-colour flood. Layout 4 owns
 * the `.rt-l4-tag` / `.rt-l4-reveal` hooks so its styles never collide with
 * Layout 3's `.rt-l3-*`.
 *
 * @package RT_Team
 */

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$html    = null;
$content = null;

$html .= '<div class="' . esc_attr( $grid ) . ' ' . esc_attr( $class ) . '" data-id="' . absint( $mID ) . '">';
$html .= '<div class="single-team-area">';

if ( $imgHtml ) {
	if ( $link ) {
		$html .= '<figure><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" href="' . esc_url( $pLink ) . '">' . Fns::htmlKses( $imgHtml, 'image' ) . '</a></figure>';
	} else {
		$html .= '<figure>' . Fns::htmlKses( $imgHtml, 'image' ) . '</figure>';
	}
}

// Persistent name / designation caption bar — shown until hover (reference `.team-tag`).
$l4_tag = '';
if ( in_array( 'name', $items, true ) && $title ) {
	$l4_tag .= '<span class="rt-l4-tag-name">' . esc_html( $title ) . '</span>';
}
if ( in_array( 'designation', $items, true ) && $designation ) {
	$l4_tag .= '<span class="rt-l4-tag-role">' . esc_html( $designation ) . '</span>';
}
$html .= $l4_tag ? '<div class="rt-l4-tag">' . $l4_tag . '</div>' : '';

// Hover-reveal overlay layer — carries every enabled field (reference `.team-overlay`).
// Kept as a wrapper so the whole overlay can fade/slide in with opacity + transform
// ONLY (no box-size change), which is what keeps the card from resizing on hover.
$html .= '<div class="rt-l4-reveal">';

if ( in_array( 'name', $items, true ) && $title ) {
	if ( $link ) {
		$content .= '<h3><span class="team-name"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" title="' . esc_attr( $title ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $title ) . '</a></span></h3>';
	} else {
		$content .= '<h3><span class="team-name">' . esc_html( $title ) . '</span></h3>';
	}
}

if ( in_array( 'designation', $items, true ) && $designation ) {
	if ( $link ) {
		$content .= '<div class="tlp-position"><a class="' . esc_attr( $anchorClass ) . '" data-id="' . absint( $mID ) . '" target="' . esc_attr( $target ) . '" title="' . esc_attr( $title ) . '" href="' . esc_url( $pLink ) . '">' . esc_html( $designation ) . '</a></div>';
	} else {
		$content .= '<div class="tlp-position">' . esc_html( $designation ) . '</div>';
	}
}

if ( in_array( 'tax_department', $items, true ) && $tax_department ) {
	$content .= '<div class="tlp-department">' . esc_html( $tax_department ) . '</div>';
}

$html .= $content ? '<div class="tlp-content">' . $content . '</div>' : null;

$html .= Fns::get_formatted_short_bio( $short_bio, $items );
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

$html .= '</div>'; // close .rt-l4-reveal
$html .= '</div>'; // close .single-team-area
$html .= '</div>'; // close grid column

Fns::print_html( $html );
