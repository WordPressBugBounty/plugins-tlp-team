<?php
/**
 * Template: Special Layout 1.
 *
 * Shared by BOTH render paths (shortcode + Elementor) — `special01` has no
 * `-pro` twin, so this single file drives them both.
 *
 * @package RT_Team
 */

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$html = null;

// Not every caller supplies $i (the admin preview does not).
if ( isset( $i ) && 1 === (int) $i ) {
	$class = $class . ' selected';
}

$sp1_name = ! empty( $title ) ? $title : '';
$sp1_role = ! empty( $designation ) ? $designation : '';

// Hover label — the name/role that slides up over the thumbnail.
$sp1_label = null;

if ( $sp1_name ) {
	$sp1_label .= '<span class="rt-sp1-tn">' . esc_html( $sp1_name ) . '</span>';
}

if ( $sp1_role ) {
	$sp1_label .= '<span class="rt-sp1-tr">' . esc_html( $sp1_role ) . '</span>';
}

$sp1_label = $sp1_label ? '<span class="rt-sp1-thumb-label">' . $sp1_label . '</span>' : '';

$html .= '<div class="' . esc_attr( $grid ) . ' ' . esc_attr( $class ) . ' rt-sp1-cell" data-id="' . absint( $mID ) . '">';
$html .= '<div class="single-team-item image-wrapper rt-sp1-thumb" data-id="' . absint( $mID ) . '" role="button" aria-label="' . esc_attr( $sp1_name ) . '">';
$html .= Fns::htmlKses( $imgHtml, 'image' ) . $sp1_label;
$html .= '</div>';
$html .= '</div>';

Fns::print_html( $html );
