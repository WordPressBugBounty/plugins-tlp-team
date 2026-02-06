<?php
use RT\Team\Controllers\Admin\Notices\BlackFriday;
?>
<div class="tlp-promo-container">
    <div class="tlp-promo-inner">
        <div class="promo-image">
            <img src="<?php echo esc_url( rttlp_team()->assets_url() . 'images/team-banner.png' ); ?>" alt="Team Plugin">
        </div>
        <div class="promo-features">
            <h2 class="promo-title">
                Most Powerful Team Members Plugin for WordPress
            </h2>
            <ul>
                <li>Multiple Layout (Grid, List, Slider,Isotope Filter)</li>
                <li>Elementor Addon</li>
                <li>AJAX Pagination</li>
                <li>Taxonomy Filter</li>
                <li>Team Member Single Page Builder With Elementor</li>
                <li>Single Page Popup</li>
                <li>Drag & Drop Ordering</li>
                <li>Team Member Import and Export</li>
            </ul>
            <?php
                $black_friday = BlackFriday::get_black_friday_time();
                if($black_friday) {
            ?>
                <div class="offer">
                    <a href="https://www.radiustheme.com/downloads/tlp-team-pro-for-wordpress/?utm_source=wp_dashboard&utm_medium=side_banner&utm_campaign=free" target="_blank">
                        <img style="width:100%" src="<?php echo esc_url( rttlp_team()->assets_url() . 'images/offerx.png' ); ?>" alt="The post grid">
                    </a>
                </div>
            <?php } ?>
            <a class="rt-admin-btn" href="https://www.radiustheme.com/downloads/tlp-team-pro-for-wordpress/?utm_source=wp_dashboard&utm_medium=side_banner&utm_campaign=free" target="_blank">
                Get The Deal!
            </a>
        </div>
    </div>
</div>