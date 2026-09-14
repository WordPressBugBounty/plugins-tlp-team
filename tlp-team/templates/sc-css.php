<?php

/**
 * Custom CSS.
 *
 * @package RT_Team
 * @var  int $scID
 */

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$css      = null;
$selector = '.rt-team-container.rt-team-container-' . $scID;

/**
 * Single team page: Resume / Hire Me buttons.
 *
 * That page is not a shortcode, so nothing scoped to `.rt-team-container-{id}` reaches
 * it and the two buttons fell back to the plain look in tlpteam.css. Emitting the rules
 * a second time under the page's own class lets a Styling tab drive them.
 *
 * Only the shortcode named by Settings > Detail page field selection > Button style
 * source may do that. Every shortcode emitting it would tie on specificity (0,3,0), and
 * team-sc.css is appended per shortcode, so the winner would silently be whichever
 * shortcode was saved last. `$singleScope` is an empty string for every other shortcode,
 * which drops the extra selector and leaves the tlpteam.css default in charge.
 */
$singleScope = ( $scID && Fns::singlePageStyleSourceId() === (int) $scID ) ? '.tlp-single-container' : '';

$resumeSel      = "$selector .readmore-btn .rt-resume-btn" . ( $singleScope ? ",{$singleScope} .readmore-btn .rt-resume-btn" : '' );
$resumeSelHover = "$selector .readmore-btn .rt-resume-btn:hover" . ( $singleScope ? ",{$singleScope} .readmore-btn .rt-resume-btn:hover" : '' );
$hireSel        = "$selector .readmore-btn .rt-hire-btn" . ( $singleScope ? ",{$singleScope} .readmore-btn .rt-hire-btn" : '' );
$hireSelHover   = "$selector .readmore-btn .rt-hire-btn:hover" . ( $singleScope ? ",{$singleScope} .readmore-btn .rt-hire-btn:hover" : '' );

// Variables.
$scMeta         = get_post_meta( $scID );
$primaryColor   = ( isset( $scMeta['primary_color'][0] ) ? sanitize_text_field( $scMeta['primary_color'][0] ) : null );
$resume_btn     = ! empty( $scMeta['ttp_resume_btn_style'][0] ) ? unserialize( $scMeta['ttp_resume_btn_style'][0] ) : null;
$hireme_btn     = ! empty( $scMeta['ttp_hireme_btn_style'][0] ) ? unserialize( $scMeta['ttp_hireme_btn_style'][0] ) : null;
$readmore_btn   = ! empty( $scMeta['ttp_readmore_btn_style'][0] ) ? unserialize( $scMeta['ttp_readmore_btn_style'][0] ) : null;
$button         = ! empty( $scMeta['ttp_button_style'][0] ) ? unserialize( $scMeta['ttp_button_style'][0] ) : null;
$popupBg        = ! empty( $scMeta['ttp_popup_bg_color'][0] ) ? sanitize_text_field( $scMeta['ttp_popup_bg_color'][0] ) : null;
$popupTextColor = ! empty( $scMeta['ttp_popup_text_color'][0] ) ? sanitize_text_field( $scMeta['ttp_popup_text_color'][0] ) : null;
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
$social_icon_bg = ! empty( $scMeta['social_icon_bg'][0] ) ? sanitize_text_field( $scMeta['social_icon_bg'][0] ) : null;
$social_hover_bg = ! empty( $scMeta['social_icon_hover_bg'][0] ) ? sanitize_text_field( $scMeta['social_icon_hover_bg'][0] ) : null;
$content_bg     = ! empty( $scMeta['ttp_content_bg_color'][0] ) ? sanitize_text_field( $scMeta['ttp_content_bg_color'][0] ) : null;
$mObg           = ! empty( $scMeta['overlay_rgba_bg'][0] ) ? unserialize( $scMeta['overlay_rgba_bg'][0] ) : null;
$itemP          = ! empty( $scMeta['overlay_padding'][0] ) ? intval( $scMeta['overlay_padding'][0] ) : null;
$gutter         = ! empty( $scMeta['ttp_gutter'][0] ) ? absint( $scMeta['ttp_gutter'][0] ) : null;


if ( $primaryColor ) {
	/**
	 * Grid Layout 1 derives its whole accent palette (role text, contact icon
	 * chips, social chips, buttons, pagination) from this single custom
	 * property, so "Primary Color" restyles the card coherently instead of
	 * only recolouring isolated parts. Other layouts ignore it.
	 */
	/**
	 * `$singleScope` is appended — for the one shortcode named as the single page's
	 * button style source — so that page's Resume / Hire Me buttons follow "Primary
	 * Color" too: they read `var(--l1-primary, #143ee5)` and would otherwise be stuck
	 * on the default accent. The detail page's Latest post(s) block reads it as well,
	 * through `--rttm-detail-primary` in tlpteam.css, so the two sit on one accent.
	 * Nothing ELSE on that page consumes it — every other reader is behind a layout
	 * class the detail view has not got — so the setting cannot bleed further.
	 */
	$css .= $singleScope ? "$selector,{$singleScope}{" : "$selector{";
	$css .= '--l1-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Isotope 2 reads --iso2-primary for the role text and its dash, the contact icon tiles,
	 * the skill track/fill/percent, the social chip hover, all three buttons and the card's
	 * hover border. It deliberately does NOT borrow --l1-primary above: only this path writes
	 * that one, so the Elementor twin of the control would have had nothing to drive.
	 */
	$css .= "$selector .isotope2{";
	$css .= '--iso2-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Isotope 3 reads --iso3-primary for the role pill, the department pill's text, the social
	 * chip hover glyph and the Read More button. Its Elementor twin (isotope-el-3) gets the
	 * same variable from ElementorFilters::colorControls().
	 */
	$css .= "$selector .isotope3{";
	$css .= '--iso3-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Special Layout 01 does the same thing through --rt-sp1-primary: the name
	 * plate gradient, its folded notch, the contact icon chips, the social
	 * chips, the buttons and the active thumbnail ring all read that one
	 * property, so Primary Color restyles the whole layout coherently.
	 */
	$css .= "$selector .special01{";
	$css .= '--rt-sp1-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Layout 5 (the table layout) reads --l5-primary for the role text, the
	 * contact icon chips, the social chips and their hover fill, so Primary
	 * Color recolours the whole table rather than nothing at all — before this
	 * the control had no selector that matched the layout.
	 */
	$css .= "$selector .layout5{";
	$css .= '--l5-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Layout 7 — and carousel 2, which shares its card — reads --l7-primary for
	 * the hover overlay, the social glyph on hover and the pill buttons. Without
	 * this the control had no selector matching the layout at all, so Primary
	 * Color silently did nothing on either of them.
	 */
	$css .= "$selector .layout7,$selector .isotope4{";
	$css .= '--l7-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Layout 8 — and carousel 3 and isotope 5, which share its card — reads
	 * --l8-primary for the floating name label, the social glyph and the pill
	 * buttons. carousel3's slides carry the `layout8` class so the first arm
	 * already covers them; isotope5 keeps its own class and needs its own arm.
	 */
	$css .= "$selector .layout8,$selector .isotope5{";
	$css .= '--l8-primary:' . $primaryColor . ';';
	$css .= '}';

	/*
	 * Layout 9 — and carousel 4 and isotope 6, which share its card — reads
	 * --l9-primary for the name pill, the social glyph on hover and the pill
	 * buttons. carousel4's slides carry the `layout9` class so the first arm
	 * already covers them; isotope 6 keeps its own per-path key (`isotope6` here,
	 * `isotope-el-6` on Elementor) and needs its own arm.
	 */
	$css .= "$selector .layout9,$selector .isotope6{";
	$css .= '--l9-primary:' . $primaryColor . ';';
	$css .= '}';

	$css .= "$selector .single-team-area .overlay a.detail-popup,$selector .layout18 .single-team-area .tlp-overlay  a.share-icon,$selector .layout18 .single-team-area .tlp-overlay .social-icons > a, $selector .contact-info ul li i{";
	$css .= 'color:' . $primaryColor . ';';
	$css .= '}';

	$css .= "$selector .tlp-team-skill .skill-prog .fill .percent-text, $selector .layout16 .single-team-area .social-icons, $selector .layout16 .single-team-area:hover:before,  $selector .single-team-area .skill-prog .fill,.tlp-team $selector .tlp-content, .tlp-popup-wrap-{$scID} .tlp-tooltip + .tooltip > .tooltip-inner, .tlp-modal-{$scID} .tlp-tooltip + .tooltip > .tooltip-inner, .rt-modal-{$scID} .tlp-tooltip + .tooltip > .tooltip-inner,$selector .layout11 .single-team-area .tlp-title,$selector .carousel7 .single-team-area .team-name,$selector .isotope6 .single-team-area h3 .team-name,$selector .carousel8 .rt-grid-item .tlp-overlay .social-icons:before,$selector .layout14 .rt-grid-item .tlp-overlay .social-icons:before,$selector .skill-prog .fill,$selector .layout6 .tlp-info-block, $selector .isotope-free .tlp-content,$selector .layout17 .single-team-area .social-icons a:hover,$selector .layout18 .single-team-area .tlp-overlay  a.share-icon:hover,$selector .layout18 .single-team-area .tlp-overlay  .social-icons > a:hover{";
	$css .= 'background:' . $primaryColor . ' !important;';
	$css .= '}';

    $css .= "$selector .layout16 .single-team-area:hover:after{";
    $css .= 'border-color:' . $primaryColor . ' !important;';
    $css .= '}';

	// Layouts 15 / isotope 10 / carousel 11 draw their diagonal band as a real element
	// (`.ttp-member-title`) rather than the old `::before`, so Primary Color paints that.
	$css .= "$selector .layout15 .single-team-area .ttp-member-title,$selector .isotope10 .single-team-area .ttp-member-title,$selector .carousel11 .single-team-area .ttp-member-title{";
	$css .= 'background:' . Fns::TLPhex2rgba( $primaryColor, 0.8 );
	$css .= '}';

	$css .= "#rt-smart-modal-container.loading.rt-modal-{$scID} .rt-spinner, $selector .tlp-team-skill .tooltip.top .tooltip-arrow, .tlp-popup-wrap-{$scID} .tlp-tooltip + .tooltip > .tooltip-arrow, .tlp-modal-{$scID} .tlp-tooltip + .tooltip > .tooltip-arrow, .rt-modal-{$scID} .tlp-tooltip + .tooltip > .tooltip-arrow {";
	$css .= 'border-top-color:' . $primaryColor . ';';
	$css .= '}';

	$css .= "$selector .layout6 .tlp-right-arrow:after{";
	$css .= 'border-color: transparent ' . $primaryColor . ';';
	$css .= '}';

	$css .= "$selector .tlp-team-skill .skill-prog .fill .percent-text:before, $selector .layout6 .tlp-left-arrow:after{";
	$css .= 'border-color:' . $primaryColor . ' transparent transparent;';
	$css .= '}';

	$css .= "$selector .layout12 .single-team-area h3 .team-name,$selector .isotope6 .single-team-area h3 .team-name,$selector  .layout12 .single-team-area h3 .team-name,$selector .isotope6 .single-team-area h3 .team-name {";
	$css .= 'background:' . $primaryColor . ';';
	$css .= '}';

	$css .= ".tlp-popup-wrap-{$scID} .skill-prog .fill, .tlp-modal-{$scID} .skill-prog .fill{";
	$css .= 'background-color:' . $primaryColor . ';';
	$css .= '}';

	$css .= "$selector .special-selected-top-wrap .img:after{";
	$css .= 'background:' . Fns::TLPhex2rgba( $primaryColor, 0.2 );
	$css .= '}';

	$css .= "#rt-smart-modal-container.rt-modal-{$scID} .rt-smart-modal-header a.rt-smart-nav-item{";
	$css .= '-webkit-text-stroke: 1px ' . Fns::TLPhex2rgba( $primaryColor ) . ';';
	$css .= '}';

	$css .= "#rt-smart-modal-container.rt-modal-{$scID} .rt-smart-modal-header a.rt-smart-modal-close{";
	$css .= '-webkit-text-stroke: 6px ' . Fns::TLPhex2rgba( $primaryColor ) . ';';
	$css .= '}';

}


// ReadMore Button.
if ( ! empty( $readmore_btn ) ) {
	if ( ! empty( $readmore_btn['bg'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn{";
		$css .= "background-color: {$readmore_btn['bg']};";
		$css .= '}';
	}

	if ( ! empty( $readmore_btn['hover_bg'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn:hover{";
		$css .= "background-color: {$readmore_btn['hover_bg']};";
		$css .= '}';
	}

    if ( ! empty( $readmore_btn['border_color'] ) ) {
        $css .= "$selector .readmore-btn .rt-ream-me-btn{";
        $css .= "border-color: {$readmore_btn['border_color']};";
        $css .= '}';
    }

	if ( ! empty( $readmore_btn['text'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn{";
		$css .= "color: {$readmore_btn['text']};";
		$css .= '}';
	}

	if ( ! empty( $readmore_btn['hover_text'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn:hover{";
		$css .= "color: {$readmore_btn['hover_text']};";
		$css .= '}';
	}

	if ( ! empty( $readmore_btn['border_hover_color'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn:hover{";
		$css .= "border-color: {$readmore_btn['border_hover_color']};";
		$css .= '}';
	}

	if ( ! empty( $readmore_btn['border_width'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn{";
		$css .= "border-width: {$readmore_btn['border_width']}px;";
		$css .= '}';
	}

	if ( ! empty( $readmore_btn['border_radius'] ) ) {
		$css .= "$selector .readmore-btn .rt-ream-me-btn{";
		$css .= "border-radius: {$readmore_btn['border_radius']}px;";
		$css .= '}';
	}

}

// Resume Button.
if ( ! empty( $resume_btn ) ) {
	if ( ! empty( $resume_btn['bg'] ) ) {
		$css .= "{$resumeSel}{";
		$css .= "background-color: {$resume_btn['bg']};";
		$css .= '}';
	}

	if ( ! empty( $resume_btn['hover_bg'] ) ) {
		$css .= "{$resumeSelHover}{";
		$css .= "background-color: {$resume_btn['hover_bg']};";
		$css .= '}';
	}

    if ( ! empty( $resume_btn['border_color'] ) ) {
        $css .= "{$resumeSel}{";
        $css .= "border-color: {$resume_btn['border_color']};";
        $css .= '}';
    }

	if ( ! empty( $resume_btn['text'] ) ) {
		$css .= "{$resumeSel}{";
		$css .= "color: {$resume_btn['text']};";
		$css .= '}';
	}

	if ( ! empty( $resume_btn['hover_text'] ) ) {
		$css .= "{$resumeSelHover}{";
		$css .= "color: {$resume_btn['hover_text']};";
		$css .= '}';
	}

	if ( ! empty( $resume_btn['border_hover_color'] ) ) {
		$css .= "{$resumeSelHover}{";
		$css .= "border-color: {$resume_btn['border_hover_color']};";
		$css .= '}';
	}

	if ( ! empty( $resume_btn['border_width'] ) ) {
		$css .= "{$resumeSel}{";
		$css .= "border-width: {$resume_btn['border_width']}px;";
		$css .= '}';
	}

	if ( ! empty( $resume_btn['border_radius'] ) ) {
		$css .= "{$resumeSel}{";
		$css .= "border-radius: {$resume_btn['border_radius']}px;";
		$css .= '}';
	}

}

// Hire Me Button.
if ( ! empty( $hireme_btn ) ) {
	if ( ! empty( $hireme_btn['bg'] ) ) {
		$css .= "{$hireSel}{";
		$css .= "background-color: {$hireme_btn['bg']};";
		$css .= '}';
	}

	if ( ! empty( $hireme_btn['hover_bg'] ) ) {
		$css .= "{$hireSelHover}{";
		$css .= "background-color: {$hireme_btn['hover_bg']};";
		$css .= '}';
	}

    if ( ! empty( $hireme_btn['border_color'] ) ) {
        $css .= "{$hireSel}{";
        $css .= "border-color: {$hireme_btn['border_color']};";
        $css .= '}';
    }

	if ( ! empty( $hireme_btn['text'] ) ) {
		$css .= "{$hireSel}{";
		$css .= "color: {$hireme_btn['text']};";
		$css .= '}';
	}

	if ( ! empty( $hireme_btn['hover_text'] ) ) {
		$css .= "{$hireSelHover}{";
		$css .= "color: {$hireme_btn['hover_text']};";
		$css .= '}';
	}

	if ( ! empty( $hireme_btn['border_hover_color'] ) ) {
		$css .= "{$hireSelHover}{";
		$css .= "border-color: {$hireme_btn['border_hover_color']};";
		$css .= '}';
	}

	if ( ! empty( $hireme_btn['border_width'] ) ) {
		$css .= "{$hireSel}{";
		$css .= "border-width: {$hireme_btn['border_width']}px;";
		$css .= '}';
	}

	if ( ! empty( $hireme_btn['border_radius'] ) ) {
		$css .= "{$hireSel}{";
		$css .= "border-radius: {$hireme_btn['border_radius']}px;";
		$css .= '}';
	}

}

// Buttons.
if ( ! empty( $button ) ) {
    if ( ! empty( $button['bg'] ) ) {
        /* The filter DROPDOWN ITEM is deliberately absent from this resting-background
           group. tlpteam.css draws the dropdown as a white panel whose rows are
           `background: transparent` until hovered; painting every row with the Button
           background turned the menu into a solid block of the primary colour with the
           Button TEXT colour on top — #333 on #2804f2 measured 1.44:1 on the grid-filter-3
           demo, and it is the only thing those controls paint on a page that has no
           pagination, load-more or isotope buttons. The button-style filter
           (`.rt-filter-button-wrap span.rt-filter-button-item`) is already handled this
           way: resting TEXT plus both HOVER groups, no resting background. The dropdown
           item stays in those three groups below, so the control still reaches it. */
        $css .= "$selector .rt-pagination-wrap .rt-loadmore-btn,$selector .rt-pagination-wrap .pagination > li > a, $selector .rt-pagination-wrap .pagination > li > span,$selector .ttp-isotope-buttons.button-group button,$selector .rt-pagination-wrap .rt-loadmore-btn,$selector .rt-carousel-holder .swiper-arrow,$selector .rt-carousel-holder.swiper .swiper-pagination-bullet,$selector .rt-pagination-wrap .paginationjs .paginationjs-pages li>a{";
        $css .= "background-color: {$button['bg']};";
        $css .= '}';

        $css .= "$selector .rt-carousel-holder .swiper-arrow{";
        $css .= "border-color: {$button['bg']};";
        $css .= '}';

        $css .= "$selector .rt-pagination-wrap .rt-infinite-action .rt-infinite-loading{";
        $css .= 'color: ' . Fns::TLPhex2rgba( $button['bg'], 0.5 );
        $css .= '}';

    }

    if ( ! empty( $button['hover_bg'] ) ) {
        $css .= "$selector .rt-pagination-wrap .rt-loadmore-btn:hover,$selector .rt-pagination-wrap .pagination > li > a:hover, $selector .rt-pagination-wrap .pagination > li > span:hover,$selector .rt-carousel-holder .swiper-arrow:hover,$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item:hover,$selector .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item:hover,$selector .rt-carousel-holder.swiper .swiper-pagination-bullet:hover,$selector .ttp-isotope-buttons.button-group button:hover,$selector .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages li>a:hover{";
        $css .= "background-color: {$button['hover_bg']};";
        $css .= '}';

        $css .= "$selector .rt-carousel-holder .swiper-arrow:hover{";
        $css .= "border-color: {$button['hover_bg']};";
        $css .= '}';

    }

    if ( ! empty( $button['active_bg'] ) ) {
        $css .= "$selector .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li.active > a,$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item.selected,$selector .ttp-isotope-buttons.button-group .selected,$selector .rt-carousel-holder .swiper-pagination-bullet.swiper-pagination-bullet-active,$selector .rt-pagination-wrap .pagination > .active > span{";
        $css .= "background-color: {$button['active_bg']};";
        $css .= '}';

        // Redesigned isotope filter bar: the active pill is painted by the sliding glider
        // (`.rttm-iso-glider`), not the `.selected` button itself (which is cleared to
        // transparent while the glider is present). Point the active-bg control at the glider
        // so it keeps working on the new layout; the flat colour replaces the accent gradient.
        $css .= "$selector .ttp-isotope-buttons .rttm-iso-glider{";
        $css .= "background: {$button['active_bg']};";
        $css .= '}';
    }

    if ( ! empty( $button['text'] ) ) {
        $css .= "$selector .rt-pagination-wrap .rt-loadmore-btn,$selector .rt-pagination-wrap .pagination > li > a, $selector .rt-pagination-wrap .pagination > li > span,$selector .ttp-isotope-buttons.button-group button,$selector .rt-carousel-holder .swiper-arrow i,$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item .rt-filter-count,$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item,$selector .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item,$selector .rt-pagination-wrap .paginationjs .paginationjs-pages li>a{";
        $css .= "color: {$button['text']};";
        $css .= '}';

    }

    if ( ! empty( $button['hover_text'] ) ) {
        $css .= "$selector .rt-pagination-wrap .rt-loadmore-btn:hover,$selector .rt-pagination-wrap .pagination > li > a:hover, $selector .rt-pagination-wrap .pagination > li > span:hover,$selector .ttp-isotope-buttons.button-group button:hover,$selector .rt-carousel-holder .swiper-arrow:hover i,$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item:hover .rt-filter-count,$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item:hover,$selector .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap .rt-filter-dropdown .rt-filter-dropdown-item:hover,$selector .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages li>a:hover{";
        $css .= "color: {$button['hover_text']};";
        $css .= '}';
    }

    if ( ! empty( $button['border'] ) ) {
        $css .= "$selector .rt-filter-item-wrap.rt-filter-button-wrap span.rt-filter-button-item,$selector .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-sort-order-action,$selector .rt-layout-filter-container .rt-filter-wrap .rt-filter-item-wrap.rt-filter-dropdown-wrap{";
        $css .= "border-color: {$button['border']};";
        $css .= '}';
    }
}


// Gutter.
if ( $gutter ) {
	$css    .= "$selector [class*='rt-col-']:not(.paddingl0,.rt-paddingr0 ) {";
	$css    .= "padding-left : {$gutter}px;";
	$css    .= "padding-right : {$gutter}px;";
	$bGutter = $gutter * 2;
	$css    .= "margin-bottom : {$bGutter}px;";
	$css    .= '}';

	$css .= "$selector .rt-row.special01 .rt-special-wrapper .rt-col-sm-4 [class*='rt-col-'] {";
	$css .= 'padding-left : 0;';
	$css .= 'padding-right : 0;';
	$css .= '}';

	$css .= "$selector .rt-row.special01 .rt-special-wrapper #special-selected-wrapper {";
	$css .= 'margin : 0;';
	$css .= '}';

	$css .= "$selector .rt-row.special01 .rt-special-wrapper #special-selected-wrapper .special-selected-top-wrap > div {";
	$css .= 'margin-bottom : 0;';
	$css .= '}';

	$css .= "$selector .rt-row.special01 .rt-special-wrapper #special-selected-wrapper .rt-col-sm-12 {";
	$css .= 'margin-bottom : 0;';
	$css .= '}';

	$css .= "$selector .rt-row{";
	$css .= "margin-left : -{$gutter}px;";
	$css .= "margin-right : -{$gutter}px;";
	$css .= '}';

	$css .= "$selector.rt-container-fluid,$selector.rt-container,$selector.rt-team-container, $selector .rt-content-loader:not(.carousel9, .carousel10, .carousel11 ) .owl-nav{";
	$css .= "padding-left : {$gutter}px;";
	$css .= "padding-right : {$gutter}px;";
	$css .= '}';
}

// Name.
if ( ! empty( $name ) ) {
	$namecCss  = null;
	$namecCss .= ! empty( $name['color'] ) ? 'color:' . $name['color'] . ';' : null;
	$namecCss .= ! empty( $name['align'] ) ? 'text-align:' . $name['align'] . ';' : null;
	$namecCss .= ! empty( $name['size'] ) ? 'font-size:' . $name['size'] . 'px;' : null;
	$namecCss .= ! empty( $name['weight'] ) ? 'font-weight:' . $name['weight'] . ';' : null;

	if ( $namecCss ) {
		$css .= "$selector .layout9 .tlp-label-name,
                $selector h3,
                $selector .isotope1 .team-member h3,
                $selector h3 a,$selector .overlay h3 a,
                $selector .layout8 .tlp-title h3 a,
                $selector .isotope5 .tlp-title h3 a,
                $selector .layout9 .single-team-area h3 a,
                $selector .layout6 .tlp-info-block h3 a,
                $selector .carousel11 .single-team-area .ttp-member-title h3 a,
                $selector .layout10 .tlp-overlay .tlp-title h3 a,
                $selector .layout11 .single-team-area .ttp-member-title h3 a,
                $selector .layout12 .single-team-area h3 a,
                $selector .layout15 .single-team-area .ttp-member-title h3 a,
                $selector .layout17 .single-team-area .tlp-title h3,
                $selector .layout17 .single-team-area .tlp-title h3 a,
                $selector .isotope6 .single-team-area h3 a,
                $selector .isotope10 .single-team-area .ttp-member-title h3 a,
                $selector .single-team-area .tlp-content h3,
                $selector .single-team-area .tlp-content h3 a{ {$namecCss} }";
	}

	if ( ! empty( $name['hover_color'] ) ) {
		/*
		 * isotope1 needs its own arm: the REST list above carries
		 * `$selector .isotope1 .team-member h3` (0,4,1), which out-specifies the generic
		 * `$selector h3:hover` (0,3,1) — so without this the rest colour wins even while
		 * hovering and the Name Hover Color control does nothing on that layout.
		 */
		/*
		 * isotope2 needs one too, for the mirror-image reason: its redesigned card recolours
		 * the name when the CARD is hovered, not the heading — `.isotope2 .team-member:hover
		 * h3 a` (0,4,2) in tlpteam.css. The generic `$selector h3 a:hover` (0,2,1) only fires
		 * while the pointer is literally over the text, so without this arm the layout default
		 * paints the whole card-hover and the control looks broken.
		 */
		$css .= "$selector .isotope2 .team-member:hover h3 a,
                $selector .isotope1 .team-member h3:hover,
                $selector .isotope1 .team-member h3 a:hover,
                $selector h3:hover,
                $selector h3 a:hover,
                $selector .layout8 .tlp-title h3 a:hover,
                $selector .isotope5 .tlp-title h3 a:hover,
                $selector .layout9 .single-team-area h3 a:hover,
                $selector .layout6 .tlp-info-block h3 a:hover,
                $selector .carousel11 .single-team-area .ttp-member-title h3 a:hover,
                $selector .layout12 .single-team-area h3 a:hover,
                $selector .overlay h3 a:hover,
                $selector .layout10 .tlp-overlay .tlp-title h3 a:hover,
                $selector .layout11 .single-team-area .ttp-member-title h3 a:hover,
                $selector .layout14 .rt-grid-item .tlp-overlay h3 a:hover,
                $selector .layout15 .single-team-area .ttp-member-title h3 a:hover,
                $selector .layout17 .single-team-area .tlp-title h3 a:hover,
                $selector .isotope6 .single-team-area h3 a:hover,
                $selector .isotope10 .single-team-area .ttp-member-title h3 a:hover,
                $selector .single-team-area .tlp-content h3 a:hover,
                /*
                 * Hovering the HEADING itself needs its own arm at the same depth as the
                 * REST arm `$selector .single-team-area .tlp-content h3` (0,4,1) further
                 * up. The generic `$selector h3:hover` (0,3,1) loses to it, so on every
                 * card whose name sits in `.single-team-area .tlp-content h3` — Grid
                 * Layout 10, carousel5 and isotope7 — Name Hover Color did nothing. This
                 * arm ties the rest arm and wins on source order (it is emitted later).
                 */
                $selector .single-team-area .tlp-content h3:hover{ color: {$name['hover_color']}; }";
	}
}

// Designation.
if ( ! empty( $designation ) ) {
	$cCss  = null;
	$cCss .= ! empty( $designation['color'] ) ? 'color:' . $designation['color'] . ';' : null;
	$cCss .= ! empty( $designation['align'] ) ? 'text-align:' . $designation['align'] . ';' : null;
	$cCss .= ! empty( $designation['size'] ) ? 'font-size:' . $designation['size'] . 'px;' : null;
	$cCss .= ! empty( $designation['weight'] ) ? 'font-weight:' . $designation['weight'] . ';' : null;

	$css .= "$selector .layout9 .tlp-label-role,$selector .tlp-position,$selector .isotope10 .single-team-area .ttp-member-title .tlp-position a,$selector .isotope1 .team-member .overlay .tlp-position,$selector .layout11 .single-team-area .ttp-member-title .tlp-position a,$selector .carousel11 .single-team-area .ttp-member-title .tlp-position a,$selector .layout15 .single-team-area .ttp-member-title .tlp-position a,$selector .layout16 .single-team-area .tlp-position a ,$selector .layout18 .single-team-area .tlp-position,$selector .layout18 .single-team-area .tlp-position a,$selector .tlp-position a,$selector .layout17 .single-team-area .tlp-position,$selector .layout17 .single-team-area .tlp-position a,$selector .overlay .tlp-position,$selector .tlp-layout-isotope .overlay .tlp-position{ {$cCss} }";

	if ( ! empty( $designation['hover_color'] ) ) {
		$css .= "$selector .tlp-position:hover,$selector .isotope10 .single-team-area .ttp-member-title .tlp-position a:hover,$selector .layout11 .single-team-area .ttp-member-title .tlp-position a:hover,$selector .carousel11 .single-team-area .ttp-member-title .tlp-position a:hover,$selector .layout15 .single-team-area .ttp-member-title .tlp-position a:hover,$selector .layout16 .single-team-area .tlp-position a:hover,$selector .layout18 .single-team-area .tlp-position a:hover,$selector .tlp-position a:hover,$selector .layout17 .single-team-area .tlp-position a:hover,$selector .overlay .tlp-position:hover,$selector .tlp-layout-isotope .overlay .tlp-position:hover{ color: {$designation['hover_color']}; }";
	}
}

// Short biography.
if ( ! empty( $short_bio ) ) {
	// $cCss  = null;
	$short_bio_cCss  = ! empty( $short_bio['color'] ) ? 'color:' . $short_bio['color'] . ';' : null;
	$short_bio_cCss .= ! empty( $short_bio['align'] ) ? 'text-align:' . $short_bio['align'] . ';' : null;
	$short_bio_cCss .= ! empty( $short_bio['size'] ) ? 'font-size:' . $short_bio['size'] . 'px;' : null;
	$short_bio_cCss .= ! empty( $short_bio['weight'] ) ? 'font-weight:' . $short_bio['weight'] . ';' : null;

	if ( $short_bio_cCss ) {
		$css .= "$selector .short-bio p,$selector .short-bio p a,$selector .overlay .short-bio p, $selector .overlay .short-bio p a{{$short_bio_cCss}}";
	}
}

// Email.
if ( ! empty( $email ) ) {

	// $cCss  = null;
	$emailcCss  = ! empty( $email['color'] ) ? 'color:' . $email['color'] . ';' : null;
	$emailcCss .= ! empty( $email['size'] ) ? 'font-size:' . $email['size'] . 'px;' : null;
	$emailcCss .= ! empty( $email['weight'] ) ? 'font-weight:' . $email['weight'] . ';' : null;
	$emailcCss .= ! empty( $email['align'] ) ? 'text-align:' . $email['align'] . ';' : null;

	if ( $emailcCss ) {
		$css .= "$selector .tlp-email, $selector .layout6 .contact-info i, $selector .tlp-email a{ {$emailcCss} }";
	}
}

// Web URL.
if ( ! empty( $web_url ) ) {
	// $cCss  = null;
	$web_urlcCss  = ! empty( $web_url['color'] ) ? 'color:' . $web_url['color'] . ';' : null;
	$web_urlcCss .= ! empty( $web_url['size'] ) ? 'font-size:' . $web_url['size'] . 'px;' : null;
	$web_urlcCss .= ! empty( $web_url['weight'] ) ? 'font-weight:' . $web_url['weight'] . ';' : null;
    $web_urlcCss .= ! empty( $web_url['align'] ) ? 'text-align:' . $web_url['align'] . ';' : null;
	if ( $web_urlcCss ) {
		// Include the .tlp-website <li> (the block) so text-align works — .tlp-url
		// is a <span> (inline) and ignores text-align, which is why the Web URL
		// alignment control had no effect while its colour/size did.
		$css .= "$selector .tlp-web-url a,$selector .tlp-url,$selector .tlp-website{{$web_urlcCss}}";
	}
}

// Telephone.
if ( ! empty( $telephone ) ) {
	// $cCss  = null;
	$telephonecss  = ! empty( $telephone['color'] ) ? 'color:' . $telephone['color'] . ';' : null;
	$telephonecss .= ! empty( $telephone['size'] ) ? 'font-size:' . $telephone['size'] . 'px;' : null;
	$telephonecss .= ! empty( $telephone['weight'] ) ? 'font-weight:' . $telephone['weight'] . ';' : null;
    $telephonecss .= ! empty( $telephone['align'] ) ? 'text-align:' . $telephone['align'] . ';' : null;
	if ( $telephonecss ) {
		$css .= "$selector .tlp-phone a,$selector .tlp-phone{{$telephonecss}}";
	}
}
// Mobile.
if ( ! empty( $mobile ) ) {
	// $cCss  = null;
	$mobilecCss  = ! empty( $mobile['color'] ) ? 'color:' . $mobile['color'] . ';' : null;
	$mobilecCss .= ! empty( $mobile['size'] ) ? 'font-size:' . $mobile['size'] . 'px;' : null;
	$mobilecCss .= ! empty( $mobile['weight'] ) ? 'font-weight:' . $mobile['weight'] . ';' : null;
    $mobilecCss .= ! empty( $mobile['align'] ) ? 'text-align:' . $mobile['align'] . ';' : null;
	if ( $mobilecCss ) {
		$css .= "$selector .tlp-mobile{{$mobilecCss}}";
	}
}
// Fax.
if ( ! empty( $fax ) ) {
	// $cCss  = null;
	$faxcCss  = ! empty( $fax['color'] ) ? 'color:' . $fax['color'] . ';' : null;
	$faxcCss .= ! empty( $fax['size'] ) ? 'font-size:' . $fax['size'] . 'px;' : null;
	$faxcCss .= ! empty( $fax['weight'] ) ? 'font-weight:' . $fax['weight'] . ';' : null;
    $faxcCss .= ! empty( $fax['align'] ) ? 'text-align:' . $fax['align'] . ';' : null;
	if ( $faxcCss ) {
		$css .= "$selector .tlp-fax{{$faxcCss}}";
	}
}

// Location.
if ( ! empty( $location ) ) {
	// $cCss  = null;
	$locationcCss  = ! empty( $location['color'] ) ? 'color:' . $location['color'] . ';' : null;
	$locationcCss .= ! empty( $location['size'] ) ? 'font-size:' . $location['size'] . 'px;' : null;
	$locationcCss .= ! empty( $location['weight'] ) ? 'font-weight:' . $location['weight'] . ';' : null;
    $locationcCss .= ! empty( $location['align'] ) ? 'text-align:' . $location['align'] . ';' : null;
	if ( $locationcCss ) {
		$css .= "$selector .tlp-location{{$locationcCss}}";
	}
}

// Skill.
if ( ! empty( $skill ) ) {
	// $cCss  = null;
	$colorSkill = ! empty( $skill['color'] ) ? $skill['color'] : null;
	$skillcCss  = ! empty( $colorSkill ) ? 'color:' . $colorSkill . ';' : null;
	$skillcCss .= ! empty( $skill['align'] ) ? 'text-align:' . $skill['align'] . ';' : null;
	$skillcCss .= ! empty( $skill['size'] ) ? 'font-size:' . $skill['size'] . 'px;' : null;
	$skillcCss .= ! empty( $skill['weight'] ) ? 'font-weight:' . $skill['weight'] . ';' : null;

	if ( $skillcCss ) {
		$css .= "$selector .skill_name{{$skillcCss}}";
	}

	$css .= ".rt-modal-{$scID} .skill-prog .fill, .tlp-modal-{$scID} .skill-prog .fill, .tlp-popup-wrap-{$scID} .skill-prog .fill{background: {$colorSkill}}";
	// The three redesigned popups paint the bar from --rttm-pop-primary at a much higher
	// specificity than the arms above (id + 4-5 classes), so Skill Color silently lost to
	// Primary Color inside every popup. These carry the redesigned hooks and are one
	// class higher again, so the control wins outright rather than by stylesheet order.
	$css .= "#tlp-modal.tlp-modal-{$scID} .md-content .rttm-pop-body .tlp-team-skill .skill-prog .fill,
			#tlp-popup-wrap.tlp-popup-wrap-{$scID}.rttm-mpop .rttm-mpop-details .tlp-team-skill .skill-prog .fill,
			#rt-smart-modal-container.rt-modal-{$scID}.rttm-sp .rttm-sp-body .tlp-team-skill .skill-prog .fill{background: {$colorSkill}}";

}

// Social Icon.
if ( ! empty( $social_icon ) ) {
	// $cCss  = null;
	$social_iconcCss  = ! empty( $social_icon['size'] ) ? 'font-size:' . $social_icon['size'] . 'px;' : null;
	$social_iconcCss .= ! empty( $social_icon['weight'] ) ? 'font-weight:' . $social_icon['weight'] . ';' : null;

	// `.single-team-area .social-icons a` (0,4,1) is included so the Social size/weight win
	// over the legacy `.isotope-free .single-team-area .social-icons a{font-size:16px}` rule
	// that the Elementor CSS (incl. the un-editable pro tlp-el-team-pro.min.css) loads last.
	$social_icon_arms = "$selector .overlay .social-icons a,$selector .layout17 .single-team-area .icons-wrapper a.share-icon,$selector .isotope8 .single-team-area .tlp-overlay .social-icons a,$selector .tlp-social,$selector .single-team-area .social-icons a,$selector .layout18 .single-team-area .tlp-overlay .social-icons > a,$selector .social-icons a";

	// Size and weight are state-independent, so they keep the plain arms.
	if ( $social_iconcCss ) {
		$css .= "$social_icon_arms{ {$social_iconcCss} }";
	}

	/*
	 * Social Icon COLOUR is deliberately split off from size/weight and scoped to the
	 * resting state.
	 *
	 * It used to ride the arms above as `color:X !important` — which applies in EVERY
	 * state. `!important` there beats a layout's own `… .social-icons a:hover{color:…}`
	 * (0,4,1, no !important), so a redesigned card could never recolour its glyph on
	 * hover. On the layouts whose chip INVERTS on hover that is not a cosmetic loss but
	 * an invisible icon: layout7 and layout9 flip the chip to #fff, so a white icon
	 * colour left a white glyph on a white chip — measured at 1.00 contrast, i.e. gone.
	 * (carousel1 and layout2 land at 1.10 / 1.23 the same way.) It was masked only where
	 * the legacy per-network brand block happened to paint a dark box behind the glyph.
	 *
	 * `:not(:hover):not(:focus)` keeps the control authoritative at rest — still
	 * `!important`, still beating the legacy arms it was added for — and hands hover
	 * back to whichever layout has an opinion about it.
	 */
	if ( ! empty( $social_icon['color'] ) ) {
		$rest_arms = implode(
			',',
			array_map(
				static function ( $arm ) {
					return $arm . ':not(:hover):not(:focus)';
				},
				explode( ',', $social_icon_arms )
			)
		);
		$css .= "$rest_arms{color:{$social_icon['color']} !important;}";

		/*
		 * Persistence net. Without this, hovering on a layout that declares NO colour of
		 * its own would fall through to the theme's generic `a:hover{color:…}` (0,1,1) and
		 * the icon would change colour for no reason. `:where()` contributes nothing to
		 * specificity, so this arm is (0,2,1): above the theme, below every layout's own
		 * `.rt-team-container .<layout> .social-icons a:hover` (0,4,1). Layouts that care
		 * win; everywhere else the user's colour simply carries through hover as before.
		 */
		$css .= ":where($selector) .social-icons a:hover,:where($selector) .social-icons a:focus,:where($selector) .tlp-social:hover{color:{$social_icon['color']};}";
	}

	if ( ! empty( $social_icon['align'] ) ) {
		$css .= "$selector .social-icons,$selector .tlp-social, $selector .overlay .social-icons { text-align: {$social_icon['align']}; }";
	}
}

// Social Icon bg.
if ( $social_icon_bg ) {
	// `.single-team-area .social-icons a` (0,4,1) beats the legacy Elementor/pro
	// `.isotope-free .single-team-area .social-icons a{background:transparent}` rule (0,3,1).
	$css .= "$selector .single-team-area .social-icons a,$selector .social-icons a,$selector .layout18 .single-team-area .tlp-overlay .social-icons > a,$selector .layout17 .single-team-area .social-icons a,$selector .layout17 .single-team-area .icons-wrapper a.share-icon{background:{$social_icon_bg};}";
}

/*
 * Social Icon hover bg — the shortcode twin of Elementor's "Social → Hover → Background
 * Color" (which is `!important` for the same reason).
 *
 * `!important` is load-bearing, NOT belt-and-braces. This arm is (0,4,1), and a redesigned
 * card that draws its own chip hover reaches (0,5,1) or more — e.g.
 * `.layout5 .table .social-icons a:hover`, `.layout6 .rt-l6-media-social .social-icons a:hover`.
 * A specificity race here is unwinnable and, worse, silently re-broken by the NEXT layout
 * anyone redesigns: the card's own hover colour would quietly replace whatever the user
 * picked. `!important` makes the control unconditionally authoritative, which is what a
 * user-facing colour control means. Verified across 30 layouts by the sweep page harness.
 */
if ( $social_hover_bg ) {
	$css .= "$selector .single-team-area .social-icons a:hover,$selector .social-icons a:hover,$selector .layout18 .single-team-area .tlp-overlay .social-icons > a:hover,$selector .layout17 .single-team-area .social-icons a:hover,$selector .layout17 .single-team-area .icons-wrapper a.share-icon:hover{background:{$social_hover_bg} !important;}";
}

// Content bg.
if ( $content_bg ) {
	// carousel10 added: its spotlight card IS the content block (same role `.single-team-area`
	// plays on layouts 1/3/16), so without an arm here the control was simply dead on it.
	// isotope2's card surface is `.rt-iso2-card` — it has no `.single-team-area` or
	// `.tlp-content`, so without its own arm the Content Background Color control is dead
	// there (this list is a hardcoded per-layout allow-list, not a generic selector).
	// layout8 / isotope5 (and carousel3, via the shared `layout8` class) draw their card
	// surface on `.single-team-area`; without an arm here the control was offered on the
	// Styling tab — which has no per-layout conditions — and did nothing.
	$css .= "$selector .layout17 .single-team-area .tlp-content,$selector .layout1 .single-team-area,$selector .layout16 .single-team-area,$selector .layout3 .single-team-area,$selector .layout10 .tlp-team-item,$selector .carousel10 .single-team-area,$selector .isotope2 .rt-iso2-card,$selector .layout8 .single-team-area,$selector .isotope5 .single-team-area,$selector .layout18 .single-team-area .tlp-content,$selector .layout18 .single-team-area .tlp-content:after{background:{$content_bg};}";
}

/*
 * Popup background colour.
 *
 * The frontend runtime also applies this inline, but only ever reached one element per
 * popup type — the shell behind the single popup's card (invisible), and the multi
 * viewer's top bar rather than its stage. Every redesigned popup paints the surfaces it
 * owns from --rttm-pop-surface, so writing the token on the shell recolours the card,
 * the stage, the thumbnail strip and the drawer together.
 */
if ( ! empty( $popupBg ) ) {
	$css .= "#tlp-modal.tlp-modal-{$scID},
			#tlp-popup-wrap.tlp-popup-wrap-{$scID},
			#rt-smart-modal-container.rt-modal-{$scID}{--rttm-pop-surface:{$popupBg};}";
}

if ( ! empty( $popupTextColor ) ) {
	/*
	 * All body copy in the three redesigned popups reads these tokens, so one rule
	 * covers name, tag line, department, bio, content, the contact rows, the
	 * extra-curriculum blocks, skill names and section headings. The legacy arms below
	 * are kept for the pre-redesign markup; on their own they left this control dead on
	 * the multi popup (it had no arm at all) and patchy on the other two.
	 */
	$css .= "#tlp-modal.tlp-modal-{$scID},
			#tlp-popup-wrap.tlp-popup-wrap-{$scID},
			#rt-smart-modal-container.rt-modal-{$scID}{--rttm-pop-text:{$popupTextColor};--rttm-pop-heading:{$popupTextColor};--rttm-pop-muted:{$popupTextColor};}";
	$css .= "#rt-smart-modal-container.rt-modal-$scID .member-details,#tlp-modal.tlp-modal-$scID .md-content{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .md-content .tlp-md-content-holder > .md-header h4, #tlp-modal.tlp-modal-$scID .md-content .tlp-md-content-holder > .md-header h3 {color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .md-content button.md-close{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .social-icons a{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .rt-team-container .contact-info ul li a{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .rt-team-container .contact-info ul li{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .rt-team-container .contact-info i{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .md-content .author-latest-post li a{color:{$popupTextColor};}";
	$css .= "#tlp-modal.tlp-modal-$scID .rt-team-container h3{color:{$popupTextColor};}";
	$css .= "#rt-smart-modal-container.rt-modal-$scID .rt-team-container .tlp-position,#rt-smart-modal-container.rt-modal-$scID .rt-smart-modal-main .rt-smart-modal .rt-smart-modal-main-content-wrapper .rt-smart-modal-main-content .rt-team-container .member-details h3{color:{$popupTextColor};}";
}

// Overlay.
if ( ! empty( $mObg ) ) {
	if ( ! empty( $mObg['color'] ) && ! empty( $mObg['opacity'] ) ) {
		$css .= "$selector .single-team-area .overlay,$selector .single-team-area:hover .tlp-overlay,$selector .single-team-area:hover .tlp-overlay,$selector .layout7 figcaption:hover,$selector .isotope8 .tlp-overlay,$selector .layout13 .tlp-overlay,$selector .layout8 .tlp-title,$selector .isotope5 .tlp-title,$selector .isotope1 .rt-grid-item:hover .overlay,$selector .isotope4 figcaption:hover,$selector .layout11 .single-team-area .tlp-title,$selector .layout15 .single-team-area .ttp-member-title,$selector .isotope10 .single-team-area .ttp-member-title,$selector .carousel11 .single-team-area .ttp-member-title {";
		$css .= 'background:' . Fns::TLPhex2rgba(
			$mObg['color'],
			( $mObg['opacity'] ? $mObg['opacity'] : .8 )
		) . ';';
		$css .= '}';
	}
}

// Overlay item padding.
if ( $itemP ) {
	// isotope1 added: it HAS `.overlay .overlay-element`, but its card is `.team-member` — there
	// is no `.single-team-area` ancestor, so the generic arm never matched and the control was
	// dead on the one layout whose overlay is its whole hover reveal.
	/*
	 * layout8 / isotope5 (and carousel3, whose slides carry the `layout8` class) need
	 * their own arm on `.tlp-overlay`, exactly like layout9: their name sits in
	 * `.tlp-title`, OUTSIDE the overlay, so the generic `.single-team-area:hover h3`
	 * arm aimed at the wrong element — and could never have worked anyway, because
	 * `.rt-team-container h3 { padding: 0 !important }` earlier in tlpteam.css beats
	 * any non-important rule regardless of specificity. Padding the hover overlay is
	 * what the control name promises and mirrors the Elementor twin.
	 */
	/*
	 * layout7 / isotope4 are padded WITHOUT `:hover`. Their figcaption is in normal flow and
	 * drives the card height (content-driven, `min-height: 300px`), unlike every other arm
	 * here, whose overlay is absolutely positioned. Padding it only on hover therefore grew
	 * the card -- 5% of a ~280px card is 14px -- which reflowed the whole grid and shifted
	 * the page under the cursor; the pointer then fell outside the card, hover dropped, the
	 * card shrank back, and the two states oscillated as a visible page shake.
	 *
	 * The figcaption is `opacity: 0` at rest, so padding it there is invisible and simply
	 * reserves the height up front, leaving the card the same size in both states.
	 */
	$css .= "$selector .single-team-area .overlay .overlay-element,$selector .isotope1 .team-member .overlay .overlay-element,$selector .single-team-area:hover h3,$selector .isotope3 .single-team-area:hover h3,$selector .layout7 figcaption,$selector .isotope4 figcaption,$selector .layout8 .tlp-overlay,$selector .isotope5 .tlp-overlay, $selector .layout9 .tlp-overlay,$selector .isotope6 .tlp-overlay,$selector .layout10 .tlp-overlay,$selector .isotope7 .tlp-overlay{";
	$css .= 'padding-top:' . $itemP . '%; ';
	$css .= '}';
}

if ( $css ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo wp_strip_all_tags( $css );
}
