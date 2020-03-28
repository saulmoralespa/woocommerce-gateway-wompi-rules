<?php
/*
Plugin Name: Plugin WooCommerce de Wompi Rules
Description: Reglas de precio para el plugin Wompi.
Version: 1.0.0
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; //Exit if accessed directly
}

if(!defined('WOO_WOMPI_RULES_VERSION')){
    define('WOO_WOMPI_RULES_VERSION', '1.0.0');
}

add_action( 'plugins_loaded', 'woo_gateway_wompi_rules_init');

function woo_gateway_wompi_rules_init(){
    if(!woo_gateway_wompi_rules_requirements()) return;
    woo_gateway_wompi_rules()->run_wompi_rules();

}

function woo_gateway_wompi_rules_notices( $notice ) {
    ?>
    <div class="error notice">
        <p><?php echo esc_html( $notice ); ?></p>
    </div>
    <?php
}

function woo_gateway_wompi_rules_requirements(){

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    woo_gateway_wompi_rules_notices( 'Plugin WooCommerce de Wompi Rules: Requiere que se encuentre instalado y activo el plugin: Woocommerce' );
                }
            );
        }
        return false;
    }

    if (!class_exists('WC_Gateway_Wompi')) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    woo_gateway_wompi_rules_notices( 'Plugin WooCommerce de Wompi Rules: Requiere que se encuentre instalado y activo: Plugin WooCommerce de Wompi' );
                }
            );
        }
        return false;
    }

    return true;
}

function woo_gateway_wompi_rules(){
    static $plugin;
    if (!isset($plugin)){
        require_once ('includes/class-gateway-wompi-rules-plugin.php');
        $plugin = new Gateway_Wompi_Rules_Plugin(__FILE__, WOO_WOMPI_RULES_VERSION);
    }

    return $plugin;
}