<?php
/**
 * Implements the mail interface Waepp_Interface_Email.
 * Includes the trigger function that does the sending action and
 * the get_content_html that generates all the mail content
 *
 * @package    woocommerce-admin-email-processing-products
 * @subpackage woocommerce-admin-email-processing-products/includes
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-waepp-interface-email.php';


class Waepp_Email extends Waepp_Interface_Email{
	/** @var string recipients for the email */
	var $recipient;

	/** @var string heading for the email content */
	var $heading;

	/** @var string subject for the email */
	var $subject;

	/**
	 * Constructor
	 */
	function __construct() {
		// Define email settings
		$this->heading 			= 'heading';
		$this->email_type     	= 'html' ;
		$this->subject      	= 'subject';	
		$this->are_there_commands = false;
		
		//parent::__construct();
		if ( ! $this->recipient )
			$this->recipient = get_option( 'admin_email' );
	}
	
	/**
	 * triggers the sending email php function 
	 *
	 * @since    1.0.0
	 */
	function trigger() {
		return $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		/* uses phpmailer class and the function that uses @mail  mail_passthru */
	}
	
	/**
	 * generates all the mail content.
	 *
	 * @return string 	html content
	 */
	 function get_content_html() {
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/waepp-order.php';
		$order = new Waepp_order();
		/* 
		 * if there are no processing products, it exits with are_there_commands
		 * in the send() in mail_interface, so the mail is not sent
		 */
		if (empty($order->products)) 	{
			$this->are_there_commands = false;
			return;
		}

		$this->are_there_commands = true;
		ob_start();
		include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/template-waepp-email.php';
		return ob_get_clean();
	 }
	
	function set_subject($subject)	{
		$this->subject = $subject;
	}
	function set_recipient($recipient)	{
		$this->recipient = $recipient;
	}
	function get_are_there_commands()	{
		return $this->are_there_commands;
	}
}