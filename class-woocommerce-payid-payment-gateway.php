<?php 

class WC_PayID_Payment_Gateway extends WC_Payment_Gateway{

	public function __construct(){
		$this->id = 'payid_payment';
		$this->method_title = __('PayID Payment','woocommerce-payid-payment-gateway');
		$this->title = __('PayID Payment','woocommerce-payid-payment-gateway');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->hide_text_box = $this->get_option('hide_text_box');

		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
	}
	public function init_form_fields(){
				$this->form_fields = array(
					'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'woocommerce-payid-payment-gateway' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable PayID Payment', 'woocommerce-payid-payment-gateway' ),
					'default' 		=> 'yes'
					),
					'title' => array(
						'title' 		=> __( 'PayID Details', 'woocommerce-payid-payment-gateway' ),
						'type' 			=> 'text',
						'description' 	=> __( 'This controls the title', 'woocommerce-payid-payment-gateway' ),
						'default'		=> __( 'PayID Payment Number: XXXXXXXXXXXXX', 'woocommerce-payid-payment-gateway' ),
						'desc_tip'		=> true,
					),
					'description' => array(
						'title' => __( 'Customer Message', 'woocommerce-payid-payment-gateway' ),
						'type' => 'textarea',
						'css' => 'width:500px;',
						'default' => 'Please send the invoice total to our PayID number listed above. We will email you our PayID details also',
						'description' 	=> __( 'The message which you want it to appear to the customer in the checkout page.', 'woocommerce-payid-payment-gateway' ),
					),
					'hide_text_box' => array(
						'title' 		=> __( 'Hide The Payment Field', 'woocommerce-payid-payment-gateway' ),
						'type' 			=> 'checkbox',
						'label' 		=> __( 'Hide', 'woocommerce-payid-payment-gateway' ),
						'default' 		=> 'no',
						'description' 	=> __( 'If you do not need to show the text box for customers at all, enable this option.', 'woocommerce-payid-payment-gateway' ),
					),

			 );
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'PayID Payment Settings', 'woocommerce-payid-payment-gateway' ); ?></h3>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<table class="form-table">
							<?php $this->generate_settings_html();?>
						</table><!--/.form-table-->
					</div>
					
				</div>
				<div class="clear"></div>
				<style type="text/css">
				.wpruby_button{
					background-color:#4CAF50 !important;
					border-color:#4CAF50 !important;
					color:#ffffff !important;
					width:100%;
					padding:5px !important;
					text-align:center;
					height:35px !important;
					font-size:12pt !important;
				}
				</style>
				<?php
	}
	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );
		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('on-hold', __( 'Awaiting payment', 'woocommerce-payid-payment-gateway' ));
		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );
		if(isset($_POST[ $this->id.'-admin-note']) && trim($_POST[ $this->id.'-admin-note'])!=''){
			$order->add_order_note(esc_html($_POST[ $this->id.'-admin-note']),1);
		}
		// Remove cart
		$woocommerce->cart->empty_cart();
		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);	
	}	
	
	public function payment_fields(){
		if($this->hide_text_box !== 'yes'){
	    ?>

		<fieldset>
			<p class="form-row form-row-wide">
				<label for="<?php echo $this->id; ?>-admin-note"><?php echo esc_attr($this->description); ?> <span class="required">*</span></label>
				<textarea id="<?php echo $this->id; ?>-admin-note" class="input-text" type="text" name="<?php echo $this->id; ?>-admin-note"></textarea>
			</p>						
			<div class="clear"></div>
		</fieldset>
		<?php
		}
	}
}