<?php
/**
 * Elementor Style Class.
 *
 * This class contains all the controls for Style tab.
 *
 * @package RT_Team
 */

namespace RT\Team\Widgets\Elementor\Sections;

use RT\Team\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Style Class.
 */
class Style {

	/**
	 * Tab name.
	 *
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $tab = \Elementor\Controls_Manager::TAB_STYLE;

	/**
	 * Color Scheme section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function colorScheme( $obj ) {
		$obj->startSection( 'color_scheme_section', esc_html__( 'Color Scheme', 'tlp-team' ), self::$tab );

		$obj->elControls = Fns::filter( $obj->elPrefix . 'color_scheme', $obj );

		$obj->endSection();

		return new static();
	}

	/**
	 * Wrapper Style Section
	 *
	 * Border, radius and shadow for the member card itself. Shared by the Grid,
	 * Slider and Isotope widgets, so the selector carries an arm for every card
	 * root across all three families.
	 *
	 * `.single-team-area` is the card on every grid layout, all 11 carousels, 9 of
	 * the 11 isotope layouts and List 1. The five exceptions each get their own arm:
	 *   layout-el-6 -> .rt-inner-content
	 *   special01   -> .rt-sp1-thumb   (its repeated unit is the thumbnail cell)
	 *   isotope1    -> the bare <figure> (Isotope owns the item transform, so the
	 *                  card is drawn on the figure, not on a wrapper div)
	 *   isotope2    -> .rt-iso2-card
	 *   layout5     -> .table-responsive (the table layout has no per-member card)
	 *
	 * `{{WRAPPER}} .rt-elementor-container <card>` is (0,5,0), so these beat the
	 * per-layout defaults, which sit at (0,3,0).
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function wrapper( $obj ) {
		$card  = '{{WRAPPER}} .rt-elementor-container .single-team-area';
		$card .= ', {{WRAPPER}} .rt-elementor-container .layout-el-6 .rt-inner-content';
		$card .= ', {{WRAPPER}} .rt-elementor-container .special01 .rt-sp1-thumb';
		$card .= ', {{WRAPPER}} .rt-elementor-container .isotope1 .team-member > figure';
		$card .= ', {{WRAPPER}} .rt-elementor-container .isotope2 .rt-iso2-card';
		/*
		 * layout5 is the table layout in the List widget: no per-member card element at
		 * all, so the whole table box is the wrapper, and `.table-responsive` is what
		 * carries the layout's own border/radius/shadow on the Elementor path.
		 *
		 * Kept OUT of $card because $card is the set we are free to clip: this element is
		 * the table's horizontal scroll container (`overflow-x: auto`), and an
		 * `overflow: hidden` shorthand on it kills the scrollbar and truncates every
		 * column past the fold. It takes the border and shadow, and the radius without
		 * the clip -- `overflow-x: auto` already establishes one.
		 */
		$table = ', {{WRAPPER}} .rt-elementor-container .layout5 .table-responsive';

		/*
		 * Several layouts do not paint the card's corners on the card root -- an inner
		 * box carries the background/clip and its own radius, so rounding the root alone
		 * leaves the visible corners untouched (setting the control to 0 looked dead).
		 * These arms are the elements measured at ~100% of the card box:
		 *   .single-team        -> layout-el-4
		 *   .tlp-team-item      -> layout7 / carousel-el-2 (hardcodes .layout7) / isotope4,
		 *                          layout-el-10 / carousel5 (hardcodes .layout10) / isotope7
		 *   .rt-l12-face        -> layout12 / carousel7 (hardcodes .layout12), both flip faces
		 *   .tlp-portfolio-thum -> layout-el-8 / carousel3 (hardcodes .layout8) / isotope5,
		 *                          where the portrait box IS the card's outer edge
		 *   .tlp-content        -> layout18's white card body
		 * Scoped per layout on purpose: .tlp-portfolio-thum is the *photo* on layout17/18,
		 * which the Image Style radius already owns, and must not follow the wrapper.
		 */
		$inner  = '{{WRAPPER}} .rt-elementor-container .layout-el-4 .single-team';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout7 .tlp-team-item';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .isotope4 .tlp-team-item';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout-el-10 .tlp-team-item';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout10 .tlp-team-item';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .isotope7 .tlp-team-item';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout12 .rt-l12-face';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-portfolio-thum';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout8 .tlp-portfolio-thum';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .isotope5 .tlp-portfolio-thum';
		$inner .= ', {{WRAPPER}} .rt-elementor-container .layout18 .tlp-content';
		$inner .= $table;


		/*
		 * Radius only: Image Style's radius control also targets the card box on a few
		 * layouts (`[data-layout="layout3"] .single-team-area`, `.layout11/.carousel6
		 * .single-team-area`, `.layout10/.layout-el-10/.carousel5 .tlp-team-item`) at
		 * (0,6,0), and it is emitted after this section -- so at equal weight it won and
		 * the Wrapper radius looked dead. Repeating the container class lifts these to
		 * (0,7,0)+ so the Wrapper owns the CARD and Image Style owns the PHOTO.
		 *
		 * Not applied to the border or shadow selectors: nothing competes with them there
		 * -- layout defaults live in the static stylesheets without {{WRAPPER}}, so they
		 * sit at (0,3,0)-(0,4,0) and the plain (0,5,0) selectors already win. Note that
		 * this also means a user-set Wrapper shadow replaces a layout's :hover shadow,
		 * so the card keeps one shadow across the hover transition.
		 */
		// Border and shadow are safe on every box, the table wrapper included.
		$box = $card . $table;

		$boost  = '.rt-elementor-container.rt-elementor-container.rt-elementor-container';
		$cardR  = str_replace( '.rt-elementor-container', $boost, $card );
		$innerR = str_replace( '.rt-elementor-container', $boost, $inner );

		$obj->startSection( 'wrapper_style_section', esc_html__( 'Wrapper', 'tlp-team' ), self::$tab );

		$obj->elControls[] = [
			'mode'      => 'group',
			'type'      => 'border',
			'id'        => $obj->elPrefix . 'wrapper',
			'selector'  => $box,
			'separator' => 'after',
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'wrapper_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-team' ),
			'size_units' => [ 'px', '%' ],
			'separator'  => 'after',
			'selectors'  => [
				// `overflow: hidden` rides along with the radius so the card actually clips
				// to the curve. Without it a full-bleed photo, or a hover overlay, keeps its
				// square corners and pokes through the rounded edge. Verified across all 39
				// layouts of all three families at rest and on hover: neutral or an
				// improvement everywhere.
				$cardR  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				// Radius only -- these already clip, and forcing overflow on them would
				// cut off decorations they deliberately let escape (layout18's fold).
				$innerR => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'box-shadow',
			'id'       => $obj->elPrefix . 'wrapper_shadow',
			'selector' => $box,
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Name section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function name( $obj,$conditional = true ) {
        if ( $conditional ) {
            $condition = [ $obj->elPrefix . 'team_name' => [ 'yes' ] ];
        }else{
            $condition = [];
        }

		$obj->startSection( 'name_section', esc_html__( 'Name', 'tlp-team' ), self::$tab, [], $condition );
		$obj->elHeading( $obj->elPrefix . 'name_typography_note', __( 'Typography', 'tlp-team' ) );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => $obj->elPrefix . 'name_typography',
			'selector' => '{{WRAPPER}} .rt-elementor-container .layout9 .tlp-label-name, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title h3, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'name_alignment',
			'type'      => 'choose',
			'label'     => __( 'Alignment', 'tlp-team' ),
			'options'   => [
				'left'   => [
					'title' => __( 'Left', 'tlp-team' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => __( 'Center', 'tlp-team' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => __( 'Right', 'tlp-team' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title h3, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title' => 'text-align: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'name_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'name_color_tabs' );
		$obj->startTab( 'name_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout9 .tlp-label-name, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title h3, {{WRAPPER}} .rt-elementor-container .layout13 .single-team-area .tlp-overlay h3, {{WRAPPER}} .rt-elementor-container .layout14 .rt-grid-item .tlp-overlay h3, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row h3, {{WRAPPER}} .rt-elementor-container .carousel8 .rt-grid-item .tlp-overlay h3, {{WRAPPER}} .rt-elementor-container .carousel9 .single-team-area .tlp-overlay h3,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area .tlp-title h3 a,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area .tlp-title h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_top_color',
			'label'     => esc_html__( 'Top Title Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'layout' => [ 'layout-el-10', 'carousel5', 'isotope7' ] ],
			// Repointed to `.ov-name`: the redesigned Layout 10 card (shared by carousel5)
			// puts the name in an overlay pill, so the old `.tlp-overlay .tlp-title h3`
			// element no longer exists on either layout and these controls did nothing.
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout-el-10 .ov-name, {{WRAPPER}} .rt-elementor-container .carousel5 .ov-name, {{WRAPPER}} .rt-elementor-container .isotope7 .ov-name' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_top_bg_color',
			'label'     => esc_html__( 'Top Title Background Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'layout' => [ 'layout-el-10', 'carousel5', 'isotope7' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout-el-10 .ov-name, {{WRAPPER}} .rt-elementor-container .carousel5 .ov-name, {{WRAPPER}} .rt-elementor-container .isotope7 .ov-name' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_inner_bg_color',
			'label'     => esc_html__( 'Inner Background Color', 'tlp-team' ),
			// isotope-el-6 removed: it now shares Grid Layout 9's card, which has no boxed
			// `h3 .team-name` label — the name sits in the hover overlay. layout9 is not in
			// this list, so offering the control here made the two disagree and it did nothing.
			'condition' => [ $obj->elPrefix . 'layout' => [ 'layout12', 'carousel7' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout12 .single-team-area h3 .team-name' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'name_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .single-team-area h3:hover, {{WRAPPER}} .rt-elementor-container h3:hover, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title h3:hover, {{WRAPPER}} .rt-elementor-container .layout13 .single-team-area .tlp-overlay h3:hover, {{WRAPPER}} .rt-elementor-container .layout14 .rt-grid-item .tlp-overlay h3:hover, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row h3:hover, {{WRAPPER}} .rt-elementor-container .carousel8 .rt-grid-item .tlp-overlay h3:hover, {{WRAPPER}} .rt-elementor-container .carousel9 .single-team-area .tlp-overlay h3:hover,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area:hover .tlp-title h3 a,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area:hover .tlp-title h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .single-team-area h3:hover, {{WRAPPER}} .rt-elementor-container h3:hover, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title h3:hover,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_top_hover_color',
			// Was conditioned on `layout10`, which is the SHORTCODE key — the Elementor
			// grid uses `layout-el-10` (AdminFilter::scLayout()), so the control only
			// ever appeared for carousel5. Corrected so both share it.
			'condition' => [ $obj->elPrefix . 'layout' => [ 'layout-el-10', 'carousel5', 'isotope7' ] ],
			'label'     => esc_html__( 'Top Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout-el-10 .ov-name:hover, {{WRAPPER}} .rt-elementor-container .carousel5 .ov-name:hover, {{WRAPPER}} .rt-elementor-container .isotope7 .ov-name:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_top_hover_bg_color',
			'label'     => esc_html__( 'Top Hover Background Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'layout' => [ 'layout-el-10', 'carousel5', 'isotope7' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout-el-10 .ov-name:hover, {{WRAPPER}} .rt-elementor-container .carousel5 .ov-name:hover, {{WRAPPER}} .rt-elementor-container .isotope7 .ov-name:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'name_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'label'    => esc_html__( 'Border', 'tlp-team' ),
			'id'       => $obj->elPrefix . 'name_border',
			'selector' => '{{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'name_border_hover_color',
			'label'     => __( 'Hover Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'name_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .single-team-area h3:hover, {{WRAPPER}} .rt-elementor-container h3:hover,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'name_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'name_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'name_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3, {{WRAPPER}} .rt-elementor-container .layout1 .single-team-area h3 a, {{WRAPPER}} .single-team-area h3, {{WRAPPER}} .rt-elementor-container h3,{{WRAPPER}} .rttmp-builder-team-details .tlp-member-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'name_top_padding',
			'label'      => esc_html__( 'Top Title Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'condition'  => [ $obj->elPrefix . 'layout' => [ 'layout-el-10', 'carousel5', 'isotope7' ] ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout-el-10 .ov-name, {{WRAPPER}} .rt-elementor-container .carousel5 .ov-name, {{WRAPPER}} .rt-elementor-container .isotope7 .ov-name' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
			],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Designation section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function designation( $obj ) {
		$condition = [
			$obj->elPrefix . 'team_designation' => [ 'yes' ],
			// $obj->elPrefix . 'layout!'          => [ 'layout11' ],
		];

		$obj->startSection( 'designation_section', esc_html__( 'Designation', 'tlp-team' ), self::$tab, [], $condition );
		$obj->elHeading( $obj->elPrefix . 'designation_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

		/*
		 * The three `.rt-content-loader` selectors in front are a layout-agnostic
		 * specificity boost, not a new target. A control emits `{{WRAPPER}} .X`
		 * (~0,3,0), but the per-layout designation defaults in tlp-el-team-pro.css are
		 * `.rt-elementor-container .layoutN .tlp-position` (0,3,0) and, on layout5 /
		 * 17 / 18 / -el-10 / layout2, 0,4,0 — so the bare selector tied or lost on
		 * every layout that had not been given its own boosted variant below.
		 * `.rt-content-loader` sits on the row element in all three Elementor views
		 * (GridView:117, IsotopeView:103, SliderView:86) alongside the layout class,
		 * so this reaches 0,5,0 for every layout at once. The per-layout entries that
		 * follow are kept: they still carry the deeper overlay/title targets.
		 */
		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => $obj->elPrefix . 'designation_typography',
			'selector' => '{{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-position, {{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-position a, {{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-label-role, {{WRAPPER}} .rt-elementor-container .layout9 .tlp-label-role, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position, {{WRAPPER}} .layout2 .rttm-content-area .tlp-position, {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout9 .single-team-area:hover .tlp-position, {{WRAPPER}} .rt-elementor-container .layout13 .single-team-area .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row .tlp-position, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position, {{WRAPPER}} .rt-elementor-container .special01 .rt-el-special-wrapper .rt-row .tlp-position',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'designation_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-team' ),
			'options'   => [
				'left'   => [
					'title' => esc_html__( 'Left', 'tlp-team' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'tlp-team' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => esc_html__( 'Right', 'tlp-team' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position, {{WRAPPER}} .rt-elementor-container .layout-el-4 .single-team-area .overlay .overlay-element .tlp-content2 > *, {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position, {{WRAPPER}} .rt-elementor-container .carousel9 .single-team-area .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout-el-4 .single-team-area .overlay .overlay-element .tlp-content2>.tlp-position, {{WRAPPER}} .rt-elementor-container .isotope4 .caption-inner-content>.tlp-position, {{WRAPPER}} .rt-elementor-container .layout7 .caption-inner-content>.tlp-position' => 'text-align: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'designation_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'designation_color_tabs' );
		$obj->startTab( 'designation_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		// Same specificity boost as the Typography control above.
		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'designation_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-position, {{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-position a, {{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-label-role, {{WRAPPER}} .rt-elementor-container .layout9 .tlp-label-role, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position,{{WRAPPER}} .tlp-position a, {{WRAPPER}} .layout2 .rttm-content-area .tlp-position, {{WRAPPER}} .tlp-overlay1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout3 .tlp-content .tlp-position, {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout12 .tlp-position span, {{WRAPPER}} .rt-elementor-container .layout13 .single-team-area .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout14 .rt-grid-item .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row .tlp-position, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position, {{WRAPPER}} .rt-elementor-container .carousel8 .rt-grid-item .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .carousel9 .single-team-area .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .special01 .rt-el-special-wrapper .rt-row .tlp-position,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area .tlp-position a,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area .tlp-position' => 'color: {{VALUE}}',
			],
		];

		// Same specificity boost as the Typography control above.
		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'designation_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-position, {{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-position a, {{WRAPPER}} .rt-elementor-container .rt-content-loader .tlp-label-role, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position, {{WRAPPER}} .layout2 .rttm-content-area .tlp-position, {{WRAPPER}} .tlp-overlay1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout3 .tlp-content .tlp-position, {{WRAPPER}} .rt-elementor-container .layout12 .tlp-position span, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'designation_inner_bg_color',
			'label'     => esc_html__( 'Inner Background Color', 'tlp-team' ),
			// isotope-el-6 removed for the same reason as the Name twin above: Grid Layout 9's
			// card has no `.tlp-position span` box — the role is a pill at rest and plain text
			// in the overlay.
			'condition' => [ $obj->elPrefix . 'layout' => [ 'layout12', 'carousel7' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout12 .tlp-position span' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'designation_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'designation_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position:hover, {{WRAPPER}} .tlp-position a:hover,{{WRAPPER}} .layout2 .rttm-content-area .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout3 .tlp-content .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-overlay .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout12 .tlp-position:hover span, {{WRAPPER}} .rt-elementor-container .layout13 .single-team-area .tlp-overlay .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout14 .rt-grid-item .tlp-overlay .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .special01 .rt-special-wrapper .rt-row .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .carousel8 .rt-grid-item .tlp-overlay .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .carousel9 .single-team-area .tlp-overlay .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .special01 .rt-el-special-wrapper .rt-row .tlp-position:hover,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area:hover .tlp-position a,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area:hover .tlp-position' => 'color: {{VALUE}} !important',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'designation_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position:hover, {{WRAPPER}} .layout2 .rttm-content-area .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout3 .tlp-content .tlp-position:hover, {{WRAPPER}} .rt-elementor-container .layout12 .tlp-position:hover span, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'designation_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => $obj->elPrefix . 'designation_border',
			'label'    => esc_html__( 'Border', 'tlp-team' ),
			'selector' => '{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'designation_border_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'designation_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'designation_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'designation_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position, {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout9 .single-team-area:hover .tlp-position, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'designation_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .tlp-position, {{WRAPPER}} .rt-elementor-container .layout1 .tlp-position a, {{WRAPPER}} .tlp-position, {{WRAPPER}} .rt-elementor-container .layout-el-8 .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout13 .single-team-area .tlp-overlay .tlp-position, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area .tlp-title .tlp-position' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Department section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function department( $obj ) {
		$condition = [
			$obj->elPrefix . 'team_department' => [ 'yes' ],
			// carousel10 removed: the "Show Department?" toggle has always been offered for it
			// (Settings.php:326 does not exclude it), and the redesigned spotlight card now
			// prints `.tlp-department` on its meta line — so hiding the Style section left a
			// field that could be switched on but never styled.
			// isotope5 removed: it now shares Grid Layout 8's card, which prints the
			// department — and `layout-el-8` is not excluded here, so the Department
			// style section has to be offered for both or they disagree.
			$obj->elPrefix . 'layout!'         => [ 'layout5', 'layout9', 'layout10', 'layout11', 'layout12', 'layout13', 'layout14', 'layout15', 'carousel2', 'carousel4', 'carousel5', 'carousel6', 'carousel6', 'carousel7', 'carousel8', 'carousel9', 'carousel11', 'isotope1', 'isotope2', 'isotope-el-6', 'isotope8', 'isotope9', 'isotope10', 'carousel3' ],
		];

		$obj->startSection( 'department_section', esc_html__( 'Department', 'tlp-team' ), self::$tab, [], $condition );
		$obj->elHeading( $obj->elPrefix . 'department_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => $obj->elPrefix . 'department_typography',
			'selector' => '{{WRAPPER}} .tlp-department',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'department_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-team' ),
			'options'   => [
				'left'   => [
					'title' => esc_html__( 'Left', 'tlp-team' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'tlp-team' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => esc_html__( 'Right', 'tlp-team' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .tlp-department, {{WRAPPER}} .rt-elementor-container .layout-el-4 .single-team-area .overlay .overlay-element .tlp-content2>.tlp-department, {{WRAPPER}} .rt-elementor-container .isotope4 .caption-inner-content>.tlp-department, {{WRAPPER}} .rt-elementor-container .layout7 .caption-inner-content>.tlp-department' => 'text-align: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'department_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'department_color_tabs' );
		$obj->startTab( 'department_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'department_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .tlp-department' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'department_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .tlp-department' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'department_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'department_hover_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .tlp-department:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'department_hover_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .tlp-department:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'department_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => $obj->elPrefix . 'department_border',
			'selector' => '{{WRAPPER}} .tlp-department',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'department_border_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'department_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .tlp-department:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'department_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'department_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .tlp-department' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'department_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .tlp-department' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Contact section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function contact( $obj ) {
		$obj->elControls = Fns::filter( $obj->elPrefix . 'style_contact_section', $obj );

		return new static();
	}

	/**
	 * Skills section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function skills( $obj ) {
		$obj->elControls = Fns::filter( $obj->elPrefix . 'style_skills_section', $obj );

		return new static();
	}

	/**
	 * Social section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function social( $obj ) {
		$obj->elControls = Fns::filter( $obj->elPrefix . 'style_socials_section', $obj );

		return new static();
	}

	/**
	 * Short Biography section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function short_biography( $obj ) {

		$condition = [
			$obj->elPrefix . 'team_short_bio' => [ 'yes' ],
			$obj->elPrefix . 'layout!'        => [ 'layout5', 'layout-el-8', 'layout14', 'layout15', 'carousel3', 'carousel6', 'carousel8', 'carousel9', 'carousel11', 'isotope5', 'isotope8', 'isotope9', 'isotope10' ],
		];

		$obj->startSection( 'short_biography_section', esc_html__( 'Short Biography', 'tlp-team' ), self::$tab, [], $condition );
		$obj->elHeading( $obj->elPrefix . 'short_biography_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => $obj->elPrefix . 'short_biography_typography',
			'selector' => '{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio, {{WRAPPER}} .rt-elementor-container .layout9 .single-team-area .short-bio, {{WRAPPER}} .rt-elementor-container .layout10 .tlp-overlay .short-bio, {{WRAPPER}} .rt-elementor-container .layout12 .single-team-area .short-bio',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'short_biography_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-team' ),
			'options'   => [
				'left'    => [
					'title' => esc_html__( 'Left', 'tlp-team' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center'  => [
					'title' => esc_html__( 'Center', 'tlp-team' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'   => [
					'title' => esc_html__( 'Right', 'tlp-team' ),
					'icon'  => 'eicon-text-align-right',
				],
				'justify' => [
					'title' => esc_html__( 'Justify', 'tlp-team' ),
					'icon'  => 'eicon-text-align-justify',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio, {{WRAPPER}} .rt-elementor-container .layout-el-4 .single-team-area .overlay .overlay-element .tlp-content2 > .short-bio, {{WRAPPER}} .rt-elementor-container .layout7 .tlp-team-item .short-bio, {{WRAPPER}} .rt-elementor-container .layout12 .single-team-area .short-bio, {{WRAPPER}} .rt-elementor-container .layout-el-4 .single-team-area .overlay .overlay-element .tlp-content2>.short-bio, {{WRAPPER}} .rt-elementor-container .isotope4 .caption-inner-content>.short-bio, {{WRAPPER}} .rt-elementor-container .layout7 .caption-inner-content>.short-bio' => 'text-align: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'short_biography_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'short_biography_color_tabs' );
		$obj->startTab( 'short_biography_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'short_biography_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio, {{WRAPPER}} .rt-elementor-container .layout10 .tlp-overlay .short-bio' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'short_biography_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'short_biography_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'short_biography_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio:hover, {{WRAPPER}} .rt-elementor-container .layout10 .tlp-overlay .short-bio:hover, {{WRAPPER}} .rt-elementor-container .short-bio a:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'short_biography_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'short_biography_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => $obj->elPrefix . 'short_biography_border',
			'selector' => '{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'short_biography_border_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'short_biography_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'short_biography_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'short_biography_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio, {{WRAPPER}} .rt-elementor-container .layout10 .tlp-overlay .short-bio, {{WRAPPER}} .rt-elementor-container .layout12 .single-team-area .short-bio' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'short_biography_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .layout1 .short-bio, {{WRAPPER}} .rt-elementor-container .layout1 .short-bio p, {{WRAPPER}} .short-bio, {{WRAPPER}} .rt-elementor-container .layout12 .single-team-area .short-bio' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}


    /**
     * Read More section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function read_more_btn( $obj ) {

        $condition = [
            // layout14 / carousel8 / isotope9 removed: their redesigned card reveals the
            // Read More, Resume and Hire Me buttons inside the expanding panel, so these
            // style sections must be offered. While excluded, Elementor generated no CSS
            // for the button controls at all, no matter what was set.
            $obj->elPrefix . 'team_readmore_button' => [ 'yes' ],
            // isotope5 removed: it now shares Grid Layout 8's card, whose hover
            // overlay reveals Read More / Resume / Hire Me, so the button style
            // section has to be offered — `layout-el-8` is not excluded here.
            $obj->elPrefix . 'layout!'        => [ 'layout5', 'layout15', 'carousel3', 'carousel6', 'carousel9', 'carousel11', 'isotope10' ],
        ];

        $obj->startSection( 'read_more_btn_section', esc_html__( 'Read More Button', 'tlp-team' ), self::$tab, [], $condition );
        $obj->elHeading( $obj->elPrefix . 'read_more_btn_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'typography',
            'id'       => $obj->elPrefix . 'read_more_btn_typography',
            'selector' => '{{WRAPPER}} .readmore-btn .rt-ream-me-btn',
        ];

        $obj->elHeading( $obj->elPrefix . 'read_more_btn_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

        $obj->startTabGroup( 'read_more_btn_color_tabs' );
        $obj->startTab( 'read_more_btn_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'read_more_btn_color',
            'label'     => esc_html__( 'Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn' => 'color: {{VALUE}}',
            ],
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'read_more_btn_bg_color',
            'label'     => esc_html__( 'Background Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn' => 'background: {{VALUE}}',
            ],
        ];

        $obj->endTab();
        $obj->startTab( 'read_more_btn_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'read_more_btn_hover_color',
            'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn:hover' => 'color: {{VALUE}}',
            ],
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'read_more_btn_hover_bg_color',
            'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn:hover' => 'background: {{VALUE}}',
            ],
        ];

        $obj->endTab();
        $obj->endTabGroup();

        $obj->elHeading( $obj->elPrefix . 'read_more_btn_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'border',
            'id'       => $obj->elPrefix . 'read_more_btn_border',
            'selector' => '{{WRAPPER}} .readmore-btn .rt-ream-me-btn',
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'read_more_btn_border_hover_color',
            'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
            'condition' => [ $obj->elPrefix . 'read_more_btn_border_border!' => [ '' ] ],
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn:hover' => 'border-color: {{VALUE}}',
            ],
        ];

        $obj->elHeading( $obj->elPrefix . 'read_more_btn_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

        $obj->elControls[] = [
            'mode'       => 'responsive',
            'type'       => 'dimensions',
            'id'         => $obj->elPrefix . 'read_more_btn_padding',
            'label'      => esc_html__( 'Padding', 'tlp-team' ),
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ];

        $obj->elControls[] = [
            'mode'       => 'responsive',
            'type'       => 'dimensions',
            'id'         => $obj->elPrefix . 'read_more_btn_margin',
            'label'      => esc_html__( 'Margin', 'tlp-team' ),
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .readmore-btn .rt-ream-me-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ];

        $obj->endSection();
        return new static();
    }

    /**
     * Resume section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function resume_btn( $obj ) {

        $condition = [
            $obj->elPrefix . 'team_resume_button' => [ 'yes' ],
            // isotope5 removed: it now shares Grid Layout 8's card, whose hover
            // overlay reveals Read More / Resume / Hire Me, so the button style
            // section has to be offered — `layout-el-8` is not excluded here.
            $obj->elPrefix . 'layout!'        => [ 'layout5', 'layout15', 'carousel3', 'carousel6', 'carousel9', 'carousel11', 'isotope8', 'isotope10' ],
        ];

        $obj->startSection( 'resume_btn_section', esc_html__( 'Resume Button', 'tlp-team' ), self::$tab, [], $condition );
        $obj->elHeading( $obj->elPrefix . 'resume_btn_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'typography',
            'id'       => $obj->elPrefix . 'resume_btn_typography',
            'selector' => '{{WRAPPER}} .readmore-btn .rt-resume-btn',
        ];

        $obj->elHeading( $obj->elPrefix . 'resume_btn_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

        $obj->startTabGroup( 'resume_btn_color_tabs' );
        $obj->startTab( 'resume_btn_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'resume_btn_color',
            'label'     => esc_html__( 'Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn' => 'color: {{VALUE}}',
            ],
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'resume_btn_bg_color',
            'label'     => esc_html__( 'Background Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn' => 'background: {{VALUE}}',
            ],
        ];

        $obj->endTab();
        $obj->startTab( 'resume_btn_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'resume_btn_hover_color',
            'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn:hover' => 'color: {{VALUE}}',
            ],
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'resume_btn_hover_bg_color',
            'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn:hover' => 'background: {{VALUE}}',
            ],
        ];

        $obj->endTab();
        $obj->endTabGroup();

        $obj->elHeading( $obj->elPrefix . 'resume_btn_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'border',
            'id'       => $obj->elPrefix . 'resume_btn_border',
            'selector' => '{{WRAPPER}} .readmore-btn .rt-resume-btn',
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'resume_btn_border_hover_color',
            'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
            'condition' => [ $obj->elPrefix . 'resume_btn_border_border!' => [ '' ] ],
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn:hover' => 'border-color: {{VALUE}}',
            ],
        ];

        $obj->elHeading( $obj->elPrefix . 'resume_btn_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

        $obj->elControls[] = [
            'mode'       => 'responsive',
            'type'       => 'dimensions',
            'id'         => $obj->elPrefix . 'resume_btn_padding',
            'label'      => esc_html__( 'Padding', 'tlp-team' ),
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ];

        $obj->elControls[] = [
            'mode'       => 'responsive',
            'type'       => 'dimensions',
            'id'         => $obj->elPrefix . 'resume_btn_margin',
            'label'      => esc_html__( 'Margin', 'tlp-team' ),
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .readmore-btn .rt-resume-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ];

        $obj->endSection();
        return new static();
    }

    /**
     * Hire Me section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function hire_me_btn( $obj ) {

        $condition = [
            $obj->elPrefix . 'team_hireme_button' => [ 'yes' ],
            // isotope5 removed: it now shares Grid Layout 8's card, whose hover
            // overlay reveals Read More / Resume / Hire Me, so the button style
            // section has to be offered — `layout-el-8` is not excluded here.
            $obj->elPrefix . 'layout!'        => [ 'layout5', 'layout15', 'carousel3', 'carousel6', 'carousel9', 'carousel11', 'isotope10' ],
        ];

        $obj->startSection( 'hire_me_btn_section', esc_html__( 'Hire Me Button', 'tlp-team' ), self::$tab, [], $condition );
        $obj->elHeading( $obj->elPrefix . 'hire_me_btn_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'typography',
            'id'       => $obj->elPrefix . 'hire_me_btn_typography',
            'selector' => '{{WRAPPER}} .readmore-btn .rt-hire-btn',
        ];

        $obj->elHeading( $obj->elPrefix . 'hire_me_btn_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

        $obj->startTabGroup( 'hire_me_btn_color_tabs' );
        $obj->startTab( 'hire_me_btn_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'hire_me_btn_color',
            'label'     => esc_html__( 'Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn' => 'color: {{VALUE}}',
            ],
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'hire_me_btn_bg_color',
            'label'     => esc_html__( 'Background Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn' => 'background: {{VALUE}}',
            ],
        ];

        $obj->endTab();
        $obj->startTab( 'hire_me_btn_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'hire_me_btn_hover_color',
            'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn:hover' => 'color: {{VALUE}}',
            ],
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'hire_me_btn_hover_bg_color',
            'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn:hover' => 'background: {{VALUE}}',
            ],
        ];

        $obj->endTab();
        $obj->endTabGroup();

        $obj->elHeading( $obj->elPrefix . 'hire_me_btn_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'border',
            'id'       => $obj->elPrefix . 'hire_me_btn_border',
            'selector' => '{{WRAPPER}} .readmore-btn .rt-hire-btn',
        ];

        $obj->elControls[] = [
            'type'      => 'color',
            'id'        => $obj->elPrefix . 'hire_me_btn_border_hover_color',
            'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
            'condition' => [ $obj->elPrefix . 'hire_me_btn_border_border!' => [ '' ] ],
            'selectors' => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn:hover' => 'border-color: {{VALUE}}',
            ],
        ];

        $obj->elHeading( $obj->elPrefix . 'hire_me_btn_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

        $obj->elControls[] = [
            'mode'       => 'responsive',
            'type'       => 'dimensions',
            'id'         => $obj->elPrefix . 'hire_me_btn_padding',
            'label'      => esc_html__( 'Padding', 'tlp-team' ),
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ];

        $obj->elControls[] = [
            'mode'       => 'responsive',
            'type'       => 'dimensions',
            'id'         => $obj->elPrefix . 'hire_me_btn_margin',
            'label'      => esc_html__( 'Margin', 'tlp-team' ),
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .readmore-btn .rt-hire-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ];

        $obj->endSection();
        return new static();
    }


	/**
	 * Buttons section
	 *
	 * @param object $obj Reference object.
	 * @param string $conditions Condition.
	 * @return static
	 */
	public static function buttons( $obj ) {
		$conditions = [
			'relation' => 'or',
			'terms'    => [
				[
					'name'     => $obj->elPrefix . 'slider_nav',
					'operator' => '==',
					'value'    => 'yes',
				],
				[
					'name'     => $obj->elPrefix . 'slider_pagi',
					'operator' => '==',
					'value'    => 'yes',
				],
			],
		];

		$arrow_condition = [ $obj->elPrefix . 'slider_nav' => [ 'yes' ] ];
		$dot_condition   = [ $obj->elPrefix . 'slider_pagi' => [ 'yes' ] ];

		$obj->startSection( 'buttons_section', esc_html__( 'Slider Buttons', 'tlp-team' ), self::$tab, $conditions );
		$obj->elHeading( $obj->elPrefix . 'buttons_typography_note', esc_html__( 'Arrow Size', 'tlp-team' ), 'null', [], $arrow_condition );

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'arrow_size',
			'label'     => esc_html__( 'Arrow Size', 'tlp-team' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 16,
			],
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'arrow_width',
			'label'     => esc_html__( 'Arrow Width', 'tlp-team' ),
			'range'     => [
				'px'  => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
				'em'  => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
				'rem' => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 30,
			],
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow' => 'width: {{SIZE}}{{UNIT}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'arrow_height',
			'label'     => esc_html__( 'Arrow Height', 'tlp-team' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 30,
			],
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow' => 'height: {{SIZE}}{{UNIT}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elHeading( $obj->elPrefix . 'dot_size_note', esc_html__( 'Dot Size', 'tlp-team' ), 'before', [], $dot_condition );

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'dot_width',
			'label'     => esc_html__( 'Dot Width', 'tlp-team' ),
			'range'     => [
				'px'  => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
				'em'  => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
				'rem' => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 10,
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .rt-carousel-holder.swiper .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}',
			],
			'condition' => $dot_condition,
		];

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'dot_height',
			'label'     => esc_html__( 'Dot Height', 'tlp-team' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 10,
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .rt-carousel-holder.swiper .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}}',
			],
			'condition' => $dot_condition,
		];

		$obj->elHeading( $obj->elPrefix . 'buttons_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'button_color_tabs' );
		$obj->startTab( 'button_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow' => 'color: {{VALUE}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => $obj->elPrefix . 'button_border',
			'selector' => '{{WRAPPER}} .rt-carousel-holder .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet',
		];

		$obj->endTab();
		$obj->startTab( 'button_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow:hover' => 'color: {{VALUE}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow:hover, {{WRAPPER}} .rt-elementor-container .rt-carousel-holder .swiper-pagination-bullet:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_hover_border_color',
			'label'     => esc_html__( 'Hover Border Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow:hover, .rt-elementor-container .rt-carousel-holder .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'button_active_color_tab', esc_html__( 'Active', 'tlp-team' ), [], $dot_condition );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_active_bg_color',
			'label'     => esc_html__( 'Active Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .rt-carousel-holder .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_active_border_color',
			'label'     => esc_html__( 'Active Border Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .rt-carousel-holder .swiper-pagination-bullet-active' => 'border-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'buttons_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'buttons_wrapper_padding',
						// Emits `margin`, not padding -- label matches the CSS. The id keeps the
			// historical `_wrapper_padding` spelling so saved values are not orphaned.
			'label'      => esc_html__( 'Wrapper Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-carousel-holder.top-nav .swiper-nav, {{WRAPPER}} .rt-elementor-container .rt-carousel-holder .swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'buttons_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'buttons_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-carousel-holder .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Grid button section.
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function oldgridButtons( $obj ) {
		self::buttons( $obj, self::buttonConditions( $obj, 'grid' ) );

		return new static();
	}

	/**
	 * Slider button section.
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function sliderButtons( $obj ) {
		self::buttons( $obj, self::buttonConditions( $obj, 'slider' ) );

		return new static();
	}

	/**
	 * Button Controls Condition.
	 *
	 * @param object $obj Reference object.
	 * @param string $condition Condition.
	 * @return array
	 */
	private static function buttonConditions( $obj, $condition = null ) {
		$conditions = [];

		switch ( $condition ) {
			case 'grid':
				$conditions = [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => $obj->elPrefix . 'show_pagination',
							'operator' => '==',
							'value'    => 'yes',
						],
						[
							'name'     => $obj->elPrefix . 'tax_filter',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				];
				break;

			case 'slider':
				$conditions = [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => $obj->elPrefix . 'slider_nav',
							'operator' => '==',
							'value'    => 'yes',
						],
						[
							'name'     => $obj->elPrefix . 'slider_pagi',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				];
				break;

			case 'isotope':
				$conditions = [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => $obj->elPrefix . 'show_pagination',
							'operator' => '==',
							'value'    => 'yes',
						],
						[
							'name'     => $obj->elPrefix . 'enable_isotope_button',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				];
				break;
		}

		return $conditions;
	}

	/**
	 * Image Sytle Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function imageStyle( $obj ) {
		$condition = [ $obj->elPrefix . 'show_featured_image' => [ 'yes' ] ];

		$obj->startSection( 'image_style_section', esc_html__( 'Image Style', 'tlp-team' ), self::$tab, [], $condition );

		$obj->elControls[] = [
			'mode'      => 'group',
			'type'      => 'border',
			'id'        => $obj->elPrefix . 'image',
			// isotope2 has no `.single-team-area` — its photo box is the bare <figure> inside
			// `.rt-iso2-card` — so without its own arm the whole Image Style section is dead
			// there. This is a hardcoded per-layout allow-list, not a generic selector.
			'selector'  => '{{WRAPPER}} .single-team-area figure,{{WRAPPER}} .rt-elementor-container .isotope2 figure,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area .tlp-portfolio-thum, {{WRAPPER}} .rt-elementor-container .layout18 .single-team-area .tlp-portfolio-thum,{{WRAPPER}} .rt-elementor-container .layout5 .table figure, {{WRAPPER}} .rt-elementor-container .carousel10 .profile-img-wrap img',
			'separator' => 'after',
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'image_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-team' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => '%',
				'isLinked' => true,
			],
			'separator'  => 'after',
			'selectors'  => [
				'{{WRAPPER}} .single-team-area figure, {{WRAPPER}} .rt-elementor-container .isotope2 figure, {{WRAPPER}} .rt-elementor-container .layout5 .table figure, {{WRAPPER}} .rt-elementor-container .carousel10 .profile-img-wrap img,{{WRAPPER}} .rt-elementor-container .layout17 .single-team-area .tlp-portfolio-thum, {{WRAPPER}} .rt-elementor-container .layout18 .single-team-area .tlp-portfolio-thum, {{WRAPPER}} .rt-elementor-container .carousel-el-1 .rttm-slide-media, {{WRAPPER}} .rt-elementor-container .isotope-free .rttm-iso-media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				/*
				 * Layout 3 is deliberately NOT in the arm above. Its photo is a square
				 * <figure> near the top of a much taller card, not a full-bleed image, so
				 * `[data-layout="layout3"] .single-team-area` rounded the whole CARD -- at
				 * 50% the card itself became an ellipse. The generic
				 * `{{WRAPPER}} .single-team-area figure` arm already rounds its image.
				 */
				/*
				 * Full-bleed photo cards: the <figure> is clipped by an ancestor that has its
				 * OWN border-radius + overflow:hidden, so rounding the figure alone is sliced
				 * off at the card corners and the control looks dead. Round the clipper too so
				 * the photo edge and the card edge curve together.
				 *   layout 10 / layout-el-10 / carousel5 -> photo box .image-container,
				 *                                           clipper  .tlp-team-item
				 *   layout 11 / carousel6                -> .single-team-area is both
				 */
				'{{WRAPPER}} .rt-elementor-container .layout10 .image-container, {{WRAPPER}} .rt-elementor-container .layout10 .tlp-team-item, {{WRAPPER}} .rt-elementor-container .layout-el-10 .image-container, {{WRAPPER}} .rt-elementor-container .layout-el-10 .tlp-team-item, {{WRAPPER}} .rt-elementor-container .carousel5 .image-container, {{WRAPPER}} .rt-elementor-container .carousel5 .tlp-team-item, {{WRAPPER}} .rt-elementor-container .layout11 .single-team-area, {{WRAPPER}} .rt-elementor-container .carousel6 .single-team-area' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls = Fns::filter( $obj->elPrefix . 'end_of_image_style_section', $obj );

		$obj->endSection();

		return new static();
	}

	/**
	 * Gutter Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function gutter( $obj ) {
		$obj->startSection( 'gutter_section', esc_html__( 'Gutter', 'tlp-team' ), self::$tab );

		$obj->elControls = Fns::filter( $obj->elPrefix . 'end_of_gutter_section', $obj );

		$obj->endSection();

		return new static();
	}

	/**
	 * Pagination Style Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function pagination( $obj ) {
		$condition = [
			$obj->elPrefix . 'show_pagination' => [ 'yes' ],
			// $obj->elPrefix . 'pagination_type!'        => [ 'load_more', 'load_on_scroll' ],
			// $obj->elPrefix . 'pagination_type_filter!' => [ 'load_more', 'load_on_scroll' ],
		];

		$activeCondition = [
			// $obj->elPrefix . 'pagination_type!'        => [ 'load_more', 'load_on_scroll' ],
			// $obj->elPrefix . 'pagination_type_filter!' => [ 'load_more', 'load_on_scroll' ],
		];

		$obj->startSection( 'buttons_section', esc_html__( 'Pagination Style', 'tlp-team' ), self::$tab, [], $condition );
		$obj->elHeading( $obj->elPrefix . 'buttons_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => $obj->elPrefix . 'buttons_typography',
			'exclude'  => [ 'font_family', 'word_spacing', 'letter_spacing', 'text_transform', 'font_style', 'text_decoration' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			'selector' => '{{WRAPPER}} .rt-elementor-container .pagination span, {{WRAPPER}} .rt-elementor-container .pagination a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'buttons_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-team' ),
			'options'   => [
				'flex-start' => [
					'title' => esc_html__( 'Left', 'tlp-team' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center'     => [
					'title' => esc_html__( 'Center', 'tlp-team' ),
					'icon'  => 'eicon-text-align-center',
				],
				'flex-end'   => [
					'title' => esc_html__( 'Right', 'tlp-team' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap' => 'justify-content: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'buttons_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'button_color_tabs' );
		$obj->startTab( 'button_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_color',
			'label'     => esc_html__( 'Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'button_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > a:hover, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a:hover, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > a:hover, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a:hover, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'button_active_color_tab', esc_html__( 'Active', 'tlp-team' ), [], $activeCondition );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_active_color',
			'label'     => esc_html__( 'Active Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > .active > span, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li.active > a' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_active_bg_color',
			'label'     => esc_html__( 'Active Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > .active > span, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li.active > a' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab( [], $activeCondition );
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'buttons_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => $obj->elPrefix . 'button_border',
			'selector' => '{{WRAPPER}} .rt-elementor-container .pagination > li > a, {{WRAPPER}} .rt-elementor-container .pagination > .active > span, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_hover_border_color',
			'label'     => __( 'Hover Border Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > a:hover, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a:hover, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'button_active_border_color',
			'label'     => esc_html__( 'Active Border Color', 'tlp-team' ),
			'condition' => [
				$obj->elPrefix . 'button_border_border!' => [ '' ],
				// $obj->elPrefix . 'pagination_type!'        => [ 'load_more', 'load_on_scroll' ],
				// $obj->elPrefix . 'pagination_type_filter!' => [ 'load_more', 'load_on_scroll' ],
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li.active > span, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li.active > a' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'button_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-team' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => 'px',
				'isLinked' => true,
			],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > a, {{WRAPPER}} .rt-elementor-container .pagination > li > span, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'buttons_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'buttons_wrapper_padding',
						// Emits `margin`, not padding -- label matches the CSS. The id keeps the
			// historical `_wrapper_padding` spelling so saved values are not orphaned.
			'label'      => esc_html__( 'Wrapper Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .pagination, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'buttons_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > span, {{WRAPPER}} .rt-elementor-container .pagination > li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'buttons_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .pagination > li > span, {{WRAPPER}} .rt-elementor-container .pagination > li > a, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-page-numbers .paginationjs .paginationjs-pages ul li, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Ajax Filter Style Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function filterStyle( $obj ) {
		$obj->elControls = Fns::filter( $obj->elPrefix . 'ajax_filter_style_section', $obj );

		return new static();
	}

	/**
	 * Isotope Filter Style Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function filterButtons( $obj ) {
		$obj->startSection(
			'filter_buttons_section',
			esc_html__( 'Filter Button', 'tlp-team' ),
			\Elementor\Controls_Manager::TAB_STYLE,
			[],
			[ $obj->elPrefix . 'enable_isotope_button' => [ 'yes' ] ]
		);

		$obj->elHeading( $obj->elPrefix . 'filter_buttons_typography_note', esc_html__( 'Typography', 'tlp-team' ) );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => $obj->elPrefix . 'filter_buttons_typography',
			'selector' => '{{WRAPPER}} .rt-elementor-container .button-group button',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => $obj->elPrefix . 'filter_buttons_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-team' ),
			'options'   => [
				'left'   => [
					'title' => esc_html__( 'Left', 'tlp-team' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'tlp-team' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => esc_html__( 'Right', 'tlp-team' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group' => 'text-align: {{VALUE}}',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'filter_buttons_colors_note', esc_html__( 'Colors', 'tlp-team' ), 'before' );

		$obj->startTabGroup( 'filter_button_color_tabs' );
		$obj->startTab( 'filter_button_color_tab', esc_html__( 'Normal', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_color',
			'label'     => esc_html__( 'Button Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group button' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_bg_color',
			'label'     => esc_html__( 'Button Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group button' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'filter_button_hover_color_tab', esc_html__( 'Hover', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group button:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group button:hover' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'filter_button_active_color_tab', esc_html__( 'Active', 'tlp-team' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_active_color',
			'label'     => esc_html__( 'Active Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group .selected' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_active_bg_color',
			'label'     => esc_html__( 'Active Background Color', 'tlp-team' ),
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group .selected' => 'background-color: {{VALUE}}',
				// The redesigned isotope filter bar paints the active pill with a sliding glider
				// (`.rttm-iso-glider`) and clears the `.selected` button to transparent, so this
				// control must recolour the glider or it does nothing on that layout. Use the
				// `background` shorthand (not background-color) so it also clears the accent
				// gradient image the glider carries by default — otherwise the gradient paints
				// over the chosen colour.
				'{{WRAPPER}} .ttp-isotope-buttons .rttm-iso-glider' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( $obj->elPrefix . 'filter_buttons_border_note', esc_html__( 'Border', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => $obj->elPrefix . 'filter_button_border',
			'selector' => '{{WRAPPER}} .rt-elementor-container .button-group button',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_hover_border_color',
			'label'     => esc_html__( 'Hover Border Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'filter_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group button:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => $obj->elPrefix . 'filter_button_active_border_color',
			'label'     => esc_html__( 'Active Border Color', 'tlp-team' ),
			'condition' => [ $obj->elPrefix . 'filter_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .rt-elementor-container .button-group .selected' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'filter_button_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-team' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => 'px',
				'isLinked' => true,
			],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .button-group button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elHeading( $obj->elPrefix . 'filter_buttons_spacing_note', esc_html__( 'Spacing', 'tlp-team' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'filter_buttons_wrapper_padding',
						// Emits `margin`, not padding -- label matches the CSS. The id keeps the
			// historical `_wrapper_padding` spelling so saved values are not orphaned.
			'label'      => esc_html__( 'Wrapper Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .button-group' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'filter_buttons_padding',
			'label'      => esc_html__( 'Padding', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .button-group button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => $obj->elPrefix . 'filter_buttons_margin',
			'label'      => esc_html__( 'Margin', 'tlp-team' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .rt-elementor-container .button-group button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}
}
