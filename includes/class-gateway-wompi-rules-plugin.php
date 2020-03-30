<?php


class Gateway_Wompi_Rules_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * assets plugin.
     *
     * @var string
     */
    public $assets;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * @var bool
     */
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->assets = $this->plugin_url . trailingslashit('assets');
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
    }

    public function run_wompi_rules()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Plugin WooCommerce de Wompi Rules can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    shipping_servientrega_rules_wc_srs_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {

        add_action( 'woocommerce_cart_calculate_fees', 'percentage_increase_total_wompi',  20, 1 );
        add_action( 'wp_footer', 'wompi_custom_checkout_jqscript' );
        add_filter( 'wc_wompi_settings', 'wc_wompi_settings_filter', 10, 1 );

        function percentage_increase_total_wompi($cart_object) {

            if ( is_admin() && ! defined( 'DOING_AJAX' ) )
                return;

            $payment_method = 'wompi';

            $percent =  empty(WC_Wompi::$settings['percentage_increase']) ? 0 : WC_Wompi::$settings['percentage_increase'];

            $cart_total = $cart_object->subtotal_ex_tax;

            $increase =  $cart_total / 100 * $percent;

            $chosen_payment_method = WC()->session->get('chosen_payment_method');  //Get the selected payment method

            if( $payment_method == $chosen_payment_method )
                $cart_object->add_fee( '', +$increase, false );
            return;
        }

        function wompi_custom_checkout_jqscript() {
            if ( ! ( is_checkout() && ! is_wc_endpoint_url() ) )
                return;
            ?>
            <script type="text/javascript">
                jQuery( function($){
                    let paymentNew = '';
                    $('form.checkout').on('change', 'input[name="payment_method"]', function(){
                        let paymentActual = $('form[name="checkout"] input[name="payment_method"]:checked').val();
                       if (paymentActual !== paymentNew){
                           $(document.body).trigger('update_checkout');
                       }
                        paymentNew = $('form[name="checkout"] input[name="payment_method"]:checked').val();
                    });
                });
            </script>
            <?php
        }

        function wc_wompi_settings_filter(array $settings){

            $settings['percentage_increase'] = [
                'title'       => __( 'Porcentaje de incremento', 'woocommerce-gateway-wompi' ),
                'type'        => 'number',
                'description' => __( 'El porcentaje de incremento sobre el total de la orden, (se aplica cuando se elige Wompi como medio de pago)', 'woocommerce-gateway-wompi' ),
                'default'     => '0',
                'desc_tip'    => true
            ];
            return $settings;
        }
    }
}