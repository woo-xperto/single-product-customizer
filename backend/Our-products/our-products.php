<?php
/**
 * Our Products Section
 * Displays all free products available on WordPress.org
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define products with their details (WordPress.org free plugins)
$products = [
    [
        'id' => 'giveaway-lottery',
        'name' => __('Giveaway Lottery for WooCommerce', 'single-product-customizer'),
        'description' => __('Run engaging giveaways, contests, and lotteries on your WooCommerce store. Increase customer engagement and grow your email list with powerful giveaway tools.', 'single-product-customizer'),
        'features' => [
            __('Multiple entry methods (purchase, social share, email signup)', 'single-product-customizer'),
            __('Free ticket and reward points system', 'single-product-customizer'),
            __('Automated random winner selection', 'single-product-customizer'),
            __('Email notifications for participants and winners', 'single-product-customizer'),
            __('Shortcode support for easy placement', 'single-product-customizer'),
            __('Fully responsive design', 'single-product-customizer')
        ],
        'icon' => 'dashicons-admin-customizer',
        'badge' => __('Popular', 'single-product-customizer'),
        'color' => '#9b59b6',
        'link' => 'https://wordpress.org/plugins/giveaway-lottery/',
        'install_link' => admin_url('plugin-install.php?s=Giveaway%2520Lottery%2520for%2520WooCommerce%2520webcartisan&tab=search&type=term'),
        'price' => __('Free', 'single-product-customizer'),
        'rating' => 5,
        'installs' => '70+',
        'wporg_slug' => 'giveaway-lottery',
        'is_woocommerce' => true
    ],
    [
        'id' => 'GiftRocket-for-WooCommerce',
        'name' => __('GiftRocket – Ultimate Gift Cards for WooCommerce', 'single-product-customizer'),
        'description' => __('Easily create, manage, and deliver digital gift cards by email. Shoppers redeem them at cart and checkout. WooCommerce Blocks are supported.', 'single-product-customizer'),
        'features' => [
            __('Digital Gift Cards — Custom amounts & messages.', 'single-product-customizer'),
            __('Email & Scheduled Delivery — Send instantly or later.', 'single-product-customizer'),
            __('PDF Gift Cards — QR, barcode & PDF417 support.', 'single-product-customizer'),
            __('Apple & Google Wallet — Mobile wallet support.', 'single-product-customizer'),
            __('CSV Import — Bulk import codes & balances.', 'single-product-customizer'),
            __('Easy Redemption — Redeem at cart & checkout.', 'single-product-customizer'),
            __('Admin Dashboard — Manage cards, balances & transactions.', 'single-product-customizer'),
        ],
        'icon' => 'dashicons-admin-customizer',
        'badge' => __('Popular', 'single-product-customizer'),
        'color' => '#633bba',
        'link' => 'https://wordpress.org/plugins/wallet-ready-gift-cards-for-woocommerce/',
        'install_link' => admin_url('plugin-install.php?s=Wallet%2520Ready%2520Gift%2520Cards%2520for%2520WooCommerce%2520webcartisan&tab=search&type=term'),
        'price' => __('Free', 'single-product-customizer'),
        'rating' => 5,
        'installs' => '70+',
        'wporg_slug' => 'wallet-ready-gift-cards-for-woocommerce',
        'is_woocommerce' => true
    ],
    [
        'id' => 'variation-monster',
        'name' => __('Variation Monster for WooCommerce', 'single-product-customizer'),
        'description' => __('Transform your WooCommerce variable products with beautiful swatches, galleries, and quick-view features. Enhance user experience and boost conversions.', 'single-product-customizer'),
        'features' => [
            __('Color, image, and radio variation swatches', 'single-product-customizer'),
            __('Advanced variation tables', 'single-product-customizer'),
            __('Advanced variation gallery', 'single-product-customizer'),
            __('Quick view for products', 'single-product-customizer'),
            __('Shop page variation swatches', 'single-product-customizer'),
            __('Tooltip support', 'single-product-customizer'),
            __('Mobile-optimized interface', 'single-product-customizer'),
            __('Easy setup and configuration', 'single-product-customizer')
        ],
        'icon' => 'dashicons-format-gallery',
        'badge' => __('Essential', 'single-product-customizer'),
        'color' => '#3498db',
        'link' => 'https://wordpress.org/plugins/variation-monster/',
        'install_link' => admin_url('plugin-install.php?s=variation%20monster%20for%20WooCommerce%20webcartisan&tab=search&type=term'),
        'price' => __('Free', 'single-product-customizer'),
        'rating' => 5,
        'installs' => '10+',
        'wporg_slug' => 'variation-monster',
        'is_woocommerce' => true
    ],
    [
        'id' => 'automatic-teachable',
        'name' => __('Automatic Teachable Enrollment for WooCommerce', 'single-product-customizer'),
        'description' => __('Automatically enroll WooCommerce customers into Teachable courses. Seamlessly connect your e-commerce store with your online courses.', 'single-product-customizer'),
        'features' => [
            __('Sell courses at woocommerce', 'single-product-customizer'),
            __('Enroll students at Teachable automatically', 'single-product-customizer'),
            __('Unlimited course selling', 'single-product-customizer'),
            __('Increase sells', 'single-product-customizer'),
            __('Easy setup and configuration', 'single-product-customizer'),
            __('Refund handling with automatic unenrollment', 'single-product-customizer')
        ],
        'icon' => 'dashicons-welcome-learn-more',
        'badge' => __('Popular', 'single-product-customizer'),
        'color' => '#2ecc71',
        'link' => 'https://wordpress.org/plugins/automatic-teachable-student-enrollment-for-woocommerce/',
        'install_link' => admin_url('plugin-install.php?s=Automatic%20Teachable%20Enrollment%20for%20WooCommerce&tab=search&type=term'),
        'price' => __('Free', 'single-product-customizer'),
        'rating' => 5,
        'installs' => '90+',
        'wporg_slug' => 'automatic-teachable-enrollment',
        'is_woocommerce' => true
    ],
    [
        'id' => 'epass-card',
        'name' => __('EpassCard', 'single-product-customizer'),
        'description' => __('Smartest Card Solution For Google Wallet and Apple Wallet and EpassCard.', 'single-product-customizer'),
        'features' => [
            __('Create and manage google and apple wallet passes', 'single-product-customizer'),
            __('Compatible with Apple Wallet and Google Wallet formats', 'single-product-customizer'),
            __('Integrates with Gift Card for WooCommerce via extension plugin', 'single-product-customizer'),
            __('Automatically generates Epasscard pass when gift card is issued', 'single-product-customizer'),
            __('Customizable pass design and metadata', 'single-product-customizer'),
            __('Easy to use', 'single-product-customizer'),
        ],
        'icon' => 'dashicons-awards',
        'badge' => __('New', 'single-product-customizer'),
        'color' => '#e67e22',
        'link' => 'https://wordpress.org/plugins/epasscard/',
        'install_link' => admin_url('plugin-install.php?s=EpassCard&tab=search&type=term'),
        'price' => __('Free', 'single-product-customizer'),
        'rating' => 5,
        'installs' => '300+',
        'wporg_slug' => 'epasscard',
        'is_woocommerce' => true
    ]
];

// CSS styles for the products section
?>
<style>
    .wx-products-section {
        padding: 20px;
    }

    .wx-products-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 20px;
    }

    .wx-products-header h2 {
        color: #d35400;
        font-size: 32px;
        margin-bottom: 10px;
        font-weight: 700;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }

    .wx-products-header p {
        color: #7d6608;
        font-size: 16px;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .wx-products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .wx-product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 15px rgba(199, 199, 199, 0.1);
        transition: all 0.3s ease;
        border: 1px solid rgba(230, 126, 34, 0.1);
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .wx-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(158, 158, 158, 0.15);
    }

    .wx-product-badge {
        position: absolute;
        top: 15px;
        right: -30px;
        background: var(--product-color, #e67e22);
        color: white;
        padding: 5px 30px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transform: rotate(45deg);
        z-index: 2;
    }

    .wx-product-header {
        padding: 25px 25px 15px;
        background: linear-gradient(135deg, var(--product-color, #e67e22) 0%, rgba(230, 126, 34, 0.9) 100%);
        color: white;
        position: relative;
    }

    .wx-product-icon {
        display: inline-block;
        margin-bottom: 15px;
        background: rgba(255,255,255,0.2);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wx-product-icon .dashicons {
        font-size: 30px;
        width: 30px;
        height: 30px;
    }

    .wx-product-title {
        font-size: 22px;
        margin: 0 0 5px 0;
        font-weight: 700;
        line-height: 1.3;
    }

    .wx-product-price {
        font-size: 24px;
        font-weight: 800;
        opacity: 0.95;
        margin: 5px 0 0 0;
        color: #ffeaa7;
    }

    .wx-product-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        font-size: 13px;
        opacity: 0.9;
    }

    .wx-product-tags {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .wx-plugin-tag {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .wx-plugin-tag.wc {
        background: #96588a;
    }

    .wx-product-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .wx-product-stars {
        color: #f1c40f;
        font-size: 14px;
        letter-spacing: 1px;
    }

    .wx-product-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .wx-product-description {
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
        font-size: 14px;
        flex-grow: 1;
    }

    .wx-product-features {
        list-style: none;
        padding: 0;
        margin: 0 0 25px 0;
    }

    .wx-product-features li {
        padding: 6px 0;
        color: #555;
        position: relative;
        padding-left: 25px;
        font-size: 13px;
        line-height: 1.4;
    }

    .wx-product-features li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: var(--product-color, #e67e22);
        font-weight: bold;
    }

    .wx-product-buttons {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }

    .wx-product-button {
        display: block;
        width: 100%;
        padding: 12px;
        background: var(--product-color, #e67e22);
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 13px;
    }

    .wx-product-button:hover {
        background: #d35400;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .wx-product-button-secondary {
        background: rgba(230, 126, 34, 0.1);
        color: var(--product-color, #e67e22);
        border: 2px solid var(--product-color, #e67e22);
    }

    .wx-product-button-secondary:hover {
        background: var(--product-color, #e67e22);
        color: white;
    }

    .wx-products-footer {
        text-align: center;
        margin-top: 50px;
        padding: 25px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        border: 1px solid rgba(139, 139, 139, 0.2);
    }

    .wx-products-footer h3 {
        color: #000000;
        margin-bottom: 15px;
    }

    .wx-products-footer p {
        color: #000000;
        max-width: 600px;
        margin: 0 auto 20px;
    }

    .wx-org-button {
        display: inline-block;
        padding: 15px 35px;
        background: white;
        color: #e67e22;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 10px;
        border: 2px solid #e67e22;
    }

    .wx-org-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        background: #e67e22;
        color: white;
    }

    .wx-org-button-secondary {
        background: #2c3e50;
        color: white;
        border: 2px solid #2c3e50;
    }

    .wx-org-button-secondary:hover {
        background: #34495e;
        color: white;
    }

    .wx-button-group {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .wx-products-grid {
            grid-template-columns: 1fr;
        }

        .wx-product-card {
            max-width: 400px;
            margin: 0 auto;
        }

        .wx-product-buttons {
            flex-direction: column;
        }

        .wx-button-group {
            flex-direction: column;
            align-items: center;
        }

        .wx-org-button {
            width: 100%;
            max-width: 300px;
            text-align: center;
        }

        .wx-product-stats {
            flex-wrap: wrap;
            gap: 10px;
        }
    }
</style>

<div class="wx-products-section">
    <div class="wx-products-header">
        <h2><?php esc_html_e('Our Free WordPress Plugins', 'single-product-customizer'); ?></h2>
        <p><?php esc_html_e('Discover our collection of free plugins available on WordPress.org. Each plugin is carefully crafted, regularly updated, and supported by our team.', 'single-product-customizer'); ?></p>
    </div>

    <div class="wx-products-grid">
        <?php foreach ($products as $product): ?>
            <div class="wx-product-card" style="--product-color: <?php echo esc_attr($product['color']); ?>">
                <?php if ($product['badge']): ?>
                    <div class="wx-product-badge"><?php echo esc_html($product['badge']); ?></div>
                <?php endif; ?>

                <div class="wx-product-header">
                    <div class="wx-product-icon">
                        <span class="dashicons <?php echo esc_attr($product['icon']); ?>"></span>
                    </div>
                    <h3 class="wx-product-title"><?php echo esc_html($product['name']); ?></h3>
                    <div class="wx-product-price"><?php echo esc_html($product['price']); ?></div>

                    <div class="wx-product-stats">
                        <div class="wx-product-tags">
                            <?php if (isset($product['is_woocommerce']) && $product['is_woocommerce']): ?>
                                <span class="wx-plugin-tag wc">WooCommerce</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wx-product-rating">
                        <span class="wx-product-stars">
                            <?php
                            $rating = $product['rating'];
                            $full_stars = floor($rating);
                            $half_star = ($rating - $full_stars) >= 0.5;

                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '★';
                            }
                            if ($half_star) {
                                echo '½';
                            }
                            for ($i = $full_stars + ($half_star ? 1 : 0); $i < 5; $i++) {
                                echo '☆';
                            }
                            ?>
                        </span>
                            <span><?php echo number_format($product['rating'], 1); ?></span>
                        </div>
                    </div>
                </div>

                <div class="wx-product-body">
                    <p class="wx-product-description"><?php echo esc_html($product['description']); ?></p>

                    <ul class="wx-product-features">
                        <?php foreach (array_slice($product['features'], 0, 6) as $feature): ?>
                            <li><?php echo esc_html($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="wx-product-buttons">
                        <a href="<?php echo esc_url(isset($product['install_link']) ? $product['install_link'] : $product['link']); ?>" class="wx-product-button">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Install Now', 'single-product-customizer'); ?>
                        </a>
                        <a href="<?php echo esc_url($product['link']); ?>" target="_blank" class="wx-product-button wx-product-button-secondary">
                            <span class="dashicons dashicons-external"></span>
                            <?php esc_html_e('View Details', 'single-product-customizer'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wx-products-footer">
        <h3><?php esc_html_e('Want to See More?', 'single-product-customizer'); ?></h3>
        <p><?php esc_html_e('Visit our WordPress.org profile to see all our plugins, read reviews, and get support.', 'single-product-customizer'); ?></p>

        <div class="wx-button-group">
            <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=Webcartisan&tab=search&type=term' ) ); ?>" class="wx-org-button">
                <span class="dashicons dashicons-wordpress"></span>
                <?php esc_html_e('View All Plugins', 'single-product-customizer'); ?>
            </a>
            <a href="https://wa.me/01926167151" target="_blank" class="wx-org-button wx-org-button-secondary">
                <span class="dashicons dashicons-sos"></span>
                <?php esc_html_e('Get Support', 'single-product-customizer'); ?>
            </a>
        </div>
    </div>
</div>