<?php
// Security check
if (!defined('ABSPATH')) {
    exit;
}

$sppcfw_sidebar_menus  = apply_filters( 'sppcfw_sidebar_menu_items', [
    [
        'type'       => 'link',
        'tab'        => 'basic',
        'icon'       => 'dashicons-admin-generic',
        'label'      => __('Basic Settings', 'single-product-customizer'),
    ],
    [
        'type'       => 'link',
        'tab'        => 'advance',
        'icon'       => 'dashicons-admin-settings',
        'label'      => __('Advance Settings', 'single-product-customizer'),
    ],
    [
        'type'       => 'link',
        'tab'        => 'our_products',
        'icon'       => 'dashicons-products',
        'label'      => __('Our Products', 'single-product-customizer'),
    ],
    [
        'type'       => 'link',
        'tab'        => 'quick_checkout',
        'icon'       => 'dashicons-cart',
        'label'      => __('Enable Quick Checkout', 'single-product-customizer'),
    ],
    [
        'type'       => 'link',
        'tab'        => 'support',
        'icon'       => 'dashicons-admin-site',
        'label'      => __('Support', 'single-product-customizer'),
    ],
    [
        'type'       => 'static',
        'icon'       => 'dashicons-admin-collapse',
        'label'      => __('Collapse', 'single-product-customizer'),
    ],
]);

if ( ! function_exists( 'sppcfw_sidebar_tab_url' ) ) {
    function sppcfw_sidebar_tab_url($tab) {
        return add_query_arg(
            [
                'page' => 'sppcfw-single-product-customizer',
                'tab'  => $tab,
            ],
            admin_url( 'admin.php' )
        );
    }
}

$sppcfw_active_tab_current = isset( $active_tab ) ? $active_tab : 'basic';
?>
<div class="sppcfw_panel_sidebar">
    <div class="sppcfw_sidebar_header">
        <a href="<?php echo esc_url(sppcfw_sidebar_tab_url('basic')); ?>" class="plugin_logo">
            <span class="logo_text"><?php esc_html_e( 'Single Product Customizer', 'single-product-customizer' ); ?></span>
        </a>
    </div>

    <div class="sppcfw_sidebar_menu_wrapper">
        <ul class="sppcfw_sidebar_menu">
            <?php foreach ($sppcfw_sidebar_menus as $menu): ?>
                <?php if ($menu['type'] === 'link'): ?>
                    <li class="menu-item<?php echo ( $sppcfw_active_tab_current === $menu['tab'] ) ? ' active-item' : ''; ?>" data-tab="<?php echo esc_attr($menu['tab']); ?>">
                        <a href="<?php echo esc_url(sppcfw_sidebar_tab_url($menu['tab'])); ?>" class="item-link" onclick="opensppcfw(event, '<?php echo esc_js($menu['tab']); ?>')">
                            <span class="dashicons <?php echo esc_attr($menu['icon']); ?>"></span>
                            <span class="name"><?php echo esc_html($menu['label']); ?></span>
                        </a>
                    </li>
                <?php elseif ($menu['type'] === 'submenu'): ?>
                    <li class="menu-item<?php echo ! empty( $menu['pro_section'] ) ? ' menu-item--pro-section' : ''; ?>">
                        <a href="#" class="item-link">
                            <span class="dashicons <?php echo esc_attr($menu['icon']); ?>"></span>
                            <span class="name"><?php echo esc_html($menu['label']); ?></span>
                            <?php if ( ! empty( $menu['pro_section'] ) ) : ?>
                                <span class="sppcfw-label pro"><?php esc_html_e( 'Pro', 'single-product-customizer' ); ?></span>
                            <?php endif; ?>
                            <span class="dashicons dashicons-arrow-down-alt2 arrow-icon"></span>
                        </a>
                        <ul class="sppcfw_panel_submenu">
                            <?php foreach ($menu['sub_items'] as $sppcfw_sub): ?>
                                <?php
                                $sppcfw_sub_href = ! empty( $sppcfw_sub['href'] )
                                    ? $sppcfw_sub['href']
                                    : sppcfw_sidebar_tab_url( $sppcfw_sub['tab'] ?? 'basic' );
                                $sppcfw_sub_tab   = isset( $sppcfw_sub['tab'] ) ? (string) $sppcfw_sub['tab'] : '';
                                ?>
                                <li class="submenu-item">
                                    <a href="<?php echo esc_url( $sppcfw_sub_href ); ?>" class="item-link"<?php echo '' !== $sppcfw_sub_tab ? ' data-tab="' . esc_attr( $sppcfw_sub_tab ) . '"' : ''; ?> onclick="opensppcfw(event, '<?php echo esc_js($sppcfw_sub_tab); ?>')">
                                        <?php echo esc_html( $sppcfw_sub['label'] ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php elseif ($menu['type'] === 'static'): ?>
                    <li class="menu-item collapse-item">
                        <a href="#" class="item-link">
                            <span class="dashicons <?php echo esc_attr($menu['icon']); ?>"></span>
                            <span class="name"><?php echo esc_html($menu['label']); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="sppcfw-setup-help-notice">
        <?php
        $sppcfw_first_activated = get_option( 'sppcfw_first_activated_time' );
        if ( ! $sppcfw_first_activated ) {
            $sppcfw_first_activated = time();
            add_option( 'sppcfw_first_activated_time', $sppcfw_first_activated );
        }
        $sppcfw_total_free_days = 30;
        $sppcfw_days_passed     = (int) floor( ( time() - (int) $sppcfw_first_activated ) / DAY_IN_SECONDS );
        $sppcfw_days_left       = max( 0, $sppcfw_total_free_days - $sppcfw_days_passed );
        if ( $sppcfw_days_left > 0 ) {
            /* translators: %d: number of days left */
            $sppcfw_days_text = sprintf( esc_html__( '%d days left', 'single-product-customizer' ), $sppcfw_days_left );
        } else {
            $sppcfw_days_text = esc_html__( '0 days left', 'single-product-customizer' );
        }
        $sppcfw_whatsapp_url = 'https://www.webcartisan.com/onboard-chat-single-product-page-customizer';
        ?>
        <a href="<?php echo esc_url( $sppcfw_whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sppcfw-sidebar-setup-card">
            <div class="sppcfw-sidebar-setup-top">
                <div class="sppcfw-sidebar-avatar-wrap">
                    <svg width="38" height="38" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="sppcfw-sidebar-avatar-img">
                        <circle cx="40" cy="40" r="40" fill="#E2E8F0"/>
                        <path d="M16 68C16 56 26 48 40 48C54 48 64 56 64 68" fill="#475569"/>
                        <path d="M32 48L40 58L48 48" fill="#FFFFFF"/>
                        <path d="M34 40V46C34 49.3 36.7 52 40 52C43.3 52 46 49.3 46 46V40" fill="#FDBA74"/>
                        <circle cx="40" cy="32" r="14" fill="#FDBA74"/>
                        <path d="M26 28C26 20 32 16 40 16C48 16 54 20 54 28C54 28 50 24 40 24C30 24 26 28 26 28Z" fill="#334155"/>
                        <rect x="29" y="29" width="9" height="7" rx="2" fill="none" stroke="#1E293B" stroke-width="2"/>
                        <rect x="42" y="29" width="9" height="7" rx="2" fill="none" stroke="#1E293B" stroke-width="2"/>
                        <line x1="38" y1="32" x2="42" y2="32" stroke="#1E293B" stroke-width="2"/>
                        <path d="M36 38C36 40 38 41 40 41C42 41 44 40 44 38" stroke="#C2410C" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M24 32C24 23 31 16 40 16C49 16 56 23 56 32" stroke="#0F172A" stroke-width="3" stroke-linecap="round" fill="none"/>
                        <rect x="22" y="28" width="4" height="8" rx="2" fill="#0F172A"/>
                        <rect x="54" y="28" width="4" height="8" rx="2" fill="#0F172A"/>
                        <path d="M24 34C24 40 30 42 34 42" stroke="#0F172A" stroke-width="2" stroke-linecap="round" fill="none"/>
                        <circle cx="35" cy="42" r="2.5" fill="#22C55E"/>
                    </svg>
                    <span class="sppcfw-sidebar-status-dot"></span>
                </div>
                <div class="sppcfw-sidebar-badge">
                    <span><?php echo esc_html( $sppcfw_days_text ); ?></span>
                </div>
            </div>
            <div class="sppcfw-sidebar-setup-content">
                <h4 class="sppcfw-sidebar-setup-title"><?php esc_html_e( 'Get free setup help', 'single-product-customizer' ); ?></h4>
                <p class="sppcfw-sidebar-setup-desc"><?php esc_html_e( 'Get help from our experts at no cost', 'single-product-customizer' ); ?></p>
            </div>
        </a>
    </div>
</div>
