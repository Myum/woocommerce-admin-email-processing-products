<?php
/**
 * Interface that defines the needed methods to implement the mail sending in woocommerce.
 * It specifies properties like mail origin, mail direction, MYME type... etc.
 *
 * @package    woocommerce-admin-email-processing-products
 * @subpackage woocommerce-admin-email-processing-products/includes
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class Waepp_Interface_Email {

	/** @var string Payment method ID. */
	var $id;

	/** @var string Payment method title. */
	var $title;

	/** @var string Description for the gateway. */
	var $description;

	/** @var string html template path */
	var $template_html;

	/** @var string recipients for the email */
	var $recipient;

	/** @var string heading for the email content */
	var $heading;

	/** @var string subject for the email */
	var $subject;

	/** @var object this email is for, for example a customer, product, or email */
	var $object;

	/** @var bool true when email is being sent */
	var $sending;
	
	/** @var bool true when there are processing commands */
	var $are_there_commands;

	/**
     *  List of preg* regular expression patterns to search for,
     *  used in conjunction with $replace.
     *  https://raw.github.com/ushahidi/wp-silcc/master/class.html2text.inc
     *
     *  @var array $search
     *  @access public
     *  @see $replace
     */
    var $plain_search = array(
        "/\r/",                                  // Non-legal carriage return
        '/&(nbsp|#160);/i',                      // Non-breaking space
        '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
		                                         // Double quotes
        '/&(apos|rsquo|lsquo|#8216|#8217);/i',   // Single quotes
        '/&gt;/i',                               // Greater-than
        '/&lt;/i',                               // Less-than
        '/&(amp|#38);/i',                        // Ampersand
        '/&(copy|#169);/i',                      // Copyright
        '/&(trade|#8482|#153);/i',               // Trademark
        '/&(reg|#174);/i',                       // Registered
        '/&(mdash|#151|#8212);/i',               // mdash
        '/&(ndash|minus|#8211|#8722);/i',        // ndash
        '/&(bull|#149|#8226);/i',                // Bullet
        '/&(pound|#163);/i',                     // Pound sign
        '/&(euro|#8364);/i',                     // Euro sign
        '/&[^&;]+;/i',                           // Unknown/unhandled entities
        '/[ ]{2,}/'                              // Runs of spaces, post-handling
    );

    /**
     *  List of pattern replacements corresponding to patterns searched.
     *
     *  @var array $replace
     *  @access public
     *  @see $search
     */
    var $plain_replace = array(
        '',                                     // Non-legal carriage return
        ' ',                                    // Non-breaking space
        '"',                                    // Double quotes
        "'",                                    // Single quotes
        '>',
        '<',
        '&',
        '(c)',
        '(tm)',
        '(R)',
        '--',
        '-',
        '*',
        '£',
        'EUR',                                  // Euro sign. € ?
        '',                                     // Unknown/unhandled entities
        ' '                                     // Runs of spaces, post-handling
    );

	/**
	 * get_subject function.
	 *
	 * @access public
	 * @return string
	 */
	function get_subject() {
		return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->subject, $this->object );
	}

	/**
	 * get_heading function.
	 *
	 * @access public
	 * @return string
	 */
	function get_heading() {
		return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->heading, $this->object );
	}

	/**
	 * get_recipient function.
	 *
	 * @access public
	 * @return string
	 */
	function get_recipient() {
		return apply_filters( 'woocommerce_email_recipient_' . $this->id, $this->recipient, $this->object );
	}

	/**
	 * get_headers function.
	 *
	 * @access public
	 * @return string
	 */
	function get_headers() {
		return apply_filters( 'woocommerce_email_headers', "Content-Type: " . $this->get_content_type() . "\r\n", $this->id, $this->object );
	}

	/**
	 * get_attachments function.
	 *
	 * @access public
	 * @return string
	 */
	function get_attachments() {
		return apply_filters( 'woocommerce_email_attachments', '', $this->id, $this->object );
	}

	/**
	 * get_content_type function.
	 *
	 * @access public
	 * @return void
	 */
	function get_content_type() {
		return 'text/html';
	}

	/**
	 * get_blogname function.
	 *
	 * @access public
	 * @return void
	 */
	function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * get_content function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content() {

		$this->sending = true;
		$email_content = $this->style_inline( $this->get_content_html() );
		return $email_content;
	}

	/**
	 * Apply inline styles to dynamic content.
	 *
	 * @access public
	 * @param mixed $content
	 * @return void
	 */
	function style_inline( $content ) {

		if ( ! class_exists( 'DOMDocument' ) )
			return $content;

		$dom = new DOMDocument();
		@$dom->loadHTML( $content );

		$nodes = $dom->getElementsByTagName('img');

		foreach( $nodes as $node )
			if ( ! $node->hasAttribute( 'style' ) )
				$node->setAttribute( "style", "display:inline; border:none; font-size:14px; font-weight:bold; height:auto; line-height:100%; outline:none; text-decoration:none; text-transform:capitalize;" );

		$nodes_h1 = $dom->getElementsByTagName('h1');
		$nodes_h2 = $dom->getElementsByTagName('h2');
		$nodes_h3 = $dom->getElementsByTagName('h3');

		foreach( $nodes_h1 as $node )
			if ( ! $node->hasAttribute( 'style' ) )
				$node->setAttribute( "style", "color: " . get_option( 'woocommerce_email_text_color' ) . "; display:block; font-family:Arial; font-size:34px; font-weight:bold; margin-top: 10px; margin-right:0; margin-bottom:10px; margin-left:0; text-align:left; line-height: 150%;" );

		foreach( $nodes_h2 as $node )
			if ( ! $node->hasAttribute( 'style' ) )
				$node->setAttribute( "style", "color: " . get_option( 'woocommerce_email_text_color' ) . "; display:block; font-family:Arial; font-size:30px; font-weight:bold; margin-top: 10px; margin-right:0; margin-bottom:10px; margin-left:0; text-align:left; line-height: 150%;" );

		foreach( $nodes_h3 as $node )
			if ( ! $node->hasAttribute( 'style' ) )
				$node->setAttribute( "style", "color: " . get_option( 'woocommerce_email_text_color' ) . "; display:block; font-family:Arial; font-size:26px; font-weight:bold; margin-top: 10px; margin-right:0; margin-bottom:10px; margin-left:0; text-align:left; line-height: 150%;" );

		$nodes = $dom->getElementsByTagName('a');

		foreach( $nodes as $node )
			if ( ! $node->hasAttribute( 'style' ) )
				$node->setAttribute( "style", "color: " . get_option( 'woocommerce_email_text_color' ) . "; font-weight:normal; text-decoration:underline;" );

		$content = $dom->saveHTML();

		return $content;
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return void
	 */
	abstract function get_content_html();

	/**
	 * Get from name for email.
	 *
	 * @access public
	 * @return string
	 */
	function get_from_name() {
		return wp_specialchars_decode( esc_html( get_option( 'woocommerce_email_from_name' ) ) );
	}

	/**
	 * Get from email address.
	 *
	 * @access public
	 * @return string
	 */
	function get_from_address() {
		return sanitize_email( get_option( 'woocommerce_email_from_address' ) );
	}

	/**
	 * Send the email.
	 *
	 * @access public
	 * @param mixed $to
	 * @param mixed $subject
	 * @param mixed $message
	 * @param string $headers
	 * @param string $attachments
	 * @return void
	 */
	function send( $to, $subject, $message, $headers, $attachments ) {
		if( $this->are_there_commands == false)
			return false;
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		
		wp_mail( $to, $subject, $message, $headers, $attachments );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		
		return true;
	}
	
}
