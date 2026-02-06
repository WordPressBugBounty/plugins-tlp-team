<?php
/**
 * Settings view.
 */

use RT\Team\Helpers\Fns;
use RT\Team\Helpers\Options;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
?>

<div class="tlp-wrap">
	<div id="tlp-team-setting-wrapper">
		<?php Fns::render_view( 'settings-header' ); ?>
		<div class="tlp-team-setting-container">
			<form id="tlp-team-settings">
				<?php
				wp_nonce_field( Fns::nonceText(), Fns::nonceID() );
				$html  = null;
				$html .= '<div id="settings-tabs" class="tlp-tabs rt-tab-container">';
                $html .= '<div class="rt-settings-sidebar">';
				$html .= '<ul class="tab-nav rt-tab-nav">
								<li class="active"><a href="#general-settings"><i class="dashicons dashicons-admin-settings"></i>' . esc_html__( 'General Settings', 'tlp-team' ) . '</a></li>
								<li><a href="#detail-field-selection"><i class="dashicons dashicons-editor-table"></i>' . esc_html__( 'Detail page field selection', 'tlp-team' ) . '</a></li>
								' . apply_filters( 'rttm_license_tab', '' ) . '
							</ul>';
                $html .= '</div>';

                $html .= '<div class="rt-settings-content">';
				$html .= '<div id="general-settings" class="rt-tab-content" style="display: block;">';
				$html .= Fns::rtFieldGenerator( Options::tlpTeamGeneralSettingFields() );
				$html .= '</div>';

				$html .= '<div id="detail-field-selection" class="rt-tab-content">';
				$html .= Fns::rtFieldGenerator( Options::tlpTeamDetailFieldSelection() );
				$html .= '</div>';
				$html .= apply_filters( 'rttm_license_tab_content', '' );
                $html .= '<p class="rt-submit"><input type="submit" name="submit" id="tlpSaveButton" class="button button-primary rt-admin-btn" value="Save Changes"></p>';
				$html .= '</div>';
                if (! rttlp_team()->has_pro() ) {
                    $html .= Fns::render_view( 'settings-promo',[],true );
                }
                $html .= '</div>';
				$html .= '</div>';

				Fns::print_html( $html, true );
				?>


				<?php wp_nonce_field( Fns::nonceText(), Fns::nonceID() ); ?>
			</form>
			<div id="rt-response"></div>
		</div>
	</div>

</div>
