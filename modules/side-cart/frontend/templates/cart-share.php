<?php
/**
 * Cart Share Template
 *
 * @package ShopGlut
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get settings safely
$options = ShopGlut_Side_Cart::options();
$button_text = isset($options['cart-share-button-text']) && !is_array($options['cart-share-button-text']) ? $options['cart-share-button-text'] : __('Share Cart', 'shopglut');
?>

<div class="shopglut-cart-share-wrapper">
    <button class="shopglut-cart-share-button">
        <?php echo ShopGlut_Side_Cart::icon('share-nodes'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <span><?php echo esc_html($button_text); ?></span>
    </button>

    <div class="shopglut-cart-share-form">
        <input type="text"
               name="sender_name"
               placeholder="<?php echo esc_attr__('Your Name', 'shopglut'); ?>"
               required />

        <input type="email"
               name="sender_email"
               placeholder="<?php echo esc_attr__('Your Email', 'shopglut'); ?>"
               required />

        <input type="email"
               name="recipient_email"
               placeholder="<?php echo esc_attr__('Recipient Email', 'shopglut'); ?>"
               required />

        <textarea name="message"
                  placeholder="<?php echo esc_attr__('Optional message...', 'shopglut'); ?>"></textarea>

        <button type="button" class="shopglut-cart-share-send">
            <?php echo esc_html__('Send Email', 'shopglut'); ?>
        </button>
    </div>
</div>
