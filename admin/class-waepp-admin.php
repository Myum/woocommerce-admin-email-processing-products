<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    woocommerce-admin-email-processing-products
 * @subpackage woocommerce-admin-email-processing-products/admin
 */
class Waepp_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Instance of Waepp_Email class
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private $waepp_email;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();
	}
	
	/*
	 * loads the class for sending mails
	 *
	 * @since    1.0.0
	 */
	public function load_dependencies()	{
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-waepp-email.php';
		$this->waepp_email = new Waepp_Email();
	}
	
	/**
	 * Content of the admin dashboard screen
	 *
	 * @since    1.0.0
	 */	
	function waepp_settings()	{
		if ( !current_user_can( 'manage_options' ) )  {	
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'waepp' ) );
		}
		/**
		 * as this plugin is an extension of WooCommerce
		 * it can't work without it
		 */
		if ( !class_exists( 'WooCommerce' ) )	{	
			_e( 'this plugin is an extension of WooCommerce, so you must first install WooCommerce' , 'waepp' );
			die;
		}
		$hidden_field_name = 'waepp_submit_hidden';
		?>
		<div class="wrap">
		<?php
			if( isset($_POST[ $hidden_field_name ]) ) {
		?>
				<?php
					$recipient = sanitize_email($_POST[ 'mailreceiver' ]);
					$subject = '[' . get_option('blogname') . '] Waepp';
					$this->waepp_email->set_subject( $subject );
					if ( $recipient ) $this->waepp_email->set_recipient( $recipient );			
					$is_mail_sent = $this->waepp_email->trigger();
					if($is_mail_sent)	{
						echo '<div class="updated"><p><strong>';
						echo _e('Mail sent.', 'waepp' );
						echo '</strong></p></div>';
					}
					else	{
						echo '<div class="updated" style="border-left: 4px solid #E70E0E;"><p><strong>';
						echo _e('Mail not sent: You don\'t have products to process yet', 'waepp' );
						echo '</strong></p></div>';
					}
			}
				?>			
			<h2>Woocommerce admin email processing products</h2><br>
			<p><big><b>Waepp</b></big><?php _e( ' - Designed to improve the management of your e-commerce stock.' , 'waepp' );?><br>
							<?php _e( 'This tool allows you to receive an email with the summary of the active orders that needs to be managed.' , 'waepp');?><br>
			</p>
			<br><hr><br>
			<form name="waepp_admin_form" method="post" action="">
				<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
				<label for="mailreceiver" style="font-size: 14px;color:#222;font-weight: 600;"><?php _e( 'Mail receiver: ' , 'waepp' );?></label><input name="mailreceiver" type="email" id="mailreceiver" value="<?php echo esc_attr(get_option( 'admin_email' ));?>" class="regular-text ltr">
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'send mail' , 'waepp') ?>" />
				</p>
			</form>
		</div>
			<?php
	}
	/** 
	 * add plugin tab to tools menu 
	 * since 1.0.0
	 */
	public function waepp_menu() {
		add_management_page( 'Waepp Options', 'Waepp', 'manage_options', 'Waepp', array( $this, 'waepp_settings') );
	}
	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in waepp_Admin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The waepp_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_style( $this->waepp, plugin_dir_url( __FILE__ ) . 'css/waepp-admin.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in waepp_Admin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The waepp_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_script( $this->waepp, plugin_dir_url( __FILE__ ) . 'js/waepp-admin.js', array( 'jquery' ), $this->version, false );
	}
}
