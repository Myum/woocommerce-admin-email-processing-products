<?php
/**
 * Template for the html content that will be viewed from the mail inbox
 *
 * @package    woocommerce-admin-email-processing-products
 * @subpackage woocommerce-admin-email-processing-products/templates
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
	function waepp_wc_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
	    //return ( hexdec( $color ) > 0xffffff / 2 ) ? $dark : $light;
	    $hex = str_replace( '#', '', $color );

		$c_r = hexdec( substr( $hex, 0, 2 ) );
		$c_g = hexdec( substr( $hex, 2, 2 ) );
		$c_b = hexdec( substr( $hex, 4, 2 ) );
		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return $brightness > 155 ? $dark : $light;
	}

	function waepp_wc_rgb_from_hex( $color ) {
		$color = str_replace( '#', '', $color );
		// Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF"
		$color = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color );

		$rgb['R'] = hexdec( $color{0}.$color{1} );
		$rgb['G'] = hexdec( $color{2}.$color{3} );
		$rgb['B'] = hexdec( $color{4}.$color{5} );
		return $rgb;
	}

	function waepp_wc_hex_darker( $color, $factor = 30 ) {
		$base = waepp_wc_rgb_from_hex( $color );
		$color = '#';

		foreach ($base as $k => $v) :
	        $amount = $v / 100;
	        $amount = round($amount * $factor);
	        $new_decimal = $v - $amount;

	        $new_hex_component = dechex($new_decimal);
	        if(strlen($new_hex_component) < 2) :
	        	$new_hex_component = "0".$new_hex_component;
	        endif;
	        $color .= $new_hex_component;
		endforeach;

		return $color;
	}
	function waepp_wc_hex_lighter( $color, $factor = 30 ) {
		$base = waepp_wc_rgb_from_hex( $color );
		$color = '#';

	    foreach ($base as $k => $v) :
	        $amount = 255 - $v;
	        $amount = $amount / 100;
	        $amount = round($amount * $factor);
	        $new_decimal = $v + $amount;

	        $new_hex_component = dechex($new_decimal);
	        if(strlen($new_hex_component) < 2) :
	        	$new_hex_component = "0".$new_hex_component;
	        endif;
	        $color .= $new_hex_component;
	   	endforeach;

	   	return $color;
	}
?>
<?php
$waepp_bg 		= get_option( 'woocommerce_email_background_color' );
$waepp_body		= get_option( 'woocommerce_email_body_background_color' );
$waepp_base 		= get_option( 'woocommerce_email_base_color' );
$waepp_base_text 	= waepp_wc_light_or_dark( $waepp_base, '#202020', '#ffffff' );
$waepp_text 		= get_option( 'woocommerce_email_text_color' );

$waepp_bg_darker_10 = woocommerce_hex_darker( $waepp_bg, 10 );
$waepp_base_lighter_20 = woocommerce_hex_lighter( $waepp_base, 20 );
$waepp_text_lighter_20 = woocommerce_hex_lighter( $waepp_text, 20 );

$waepp_wrapper = "
	background-color: " . esc_attr( $waepp_bg ) . ";
	width:100%;
	-webkit-text-size-adjust:none !important;
	margin:0;
	padding: 70px 0 70px 0;
";
$waepp_template_container = "
	-webkit-box-shadow:0 0 0 3px rgba(0,0,0,0.025) !important;
	box-shadow:0 0 0 3px rgba(0,0,0,0.025) !important;
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
	background-color: " . esc_attr( $waepp_body ) . ";
	border: 1px solid $waepp_bg_darker_10;
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
";
$waepp_template_header = "
	background-color: " . esc_attr( $waepp_base ) .";
	color: $waepp_base_text;
	-webkit-border-top-left-radius:6px !important;
	-webkit-border-top-right-radius:6px !important;
	border-top-left-radius:6px !important;
	border-top-right-radius:6px !important;
	border-bottom: 0;
	font-family:Arial;
	font-weight:bold;
	line-height:100%;
	vertical-align:middle;
";
$waepp_body_content = "
	background-color: " . esc_attr( $waepp_body ) . ";
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
";
$waepp_body_content_inner = "
	color: $waepp_text_lighter_20;
	font-family:Arial;
	font-size:14px;
	line-height:150%;
	text-align:left;
";
$waepp_header_content_h1 = "
	color: " . esc_attr( $waepp_base_text ) . ";
	margin:0;
	padding: 28px 24px;
	text-shadow: 0 1px 0 $waepp_base_lighter_20;
	display:block;
	font-family:Arial;
	font-size:30px;
	font-weight:bold;
	text-align:left;
	line-height: 150%;
";
$waepp_attribute_value = "
	color: " . esc_attr( $waepp_base ) . ";
";
$waepp_title_wrapper = "
	background-color: " . esc_attr( $waepp_bg ) . ";
	text-align:center;
	border: 2px solid " . esc_attr( $waepp_base ) . ";
";
	
?>
<?php // footer includes

$waepp_base_lighter_40 = wc_hex_lighter( $waepp_base, 40 );

// For gmail compatibility, including CSS styles in head/body are stripped out therefore styles need to be inline. These variables contain rules which are added to the template inline.
$waepp_template_footer = "
	border-top:0;
	-webkit-border-radius:6px;
";

$waepp_credit = "
	border:0;
	color: $waepp_base_lighter_40;
	font-family: Arial;
	font-size:12px;
	line-height:125%;
	text-align:center;
";
?>
<html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
	</head>  
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    	<div style="<?php echo $waepp_wrapper; ?>">
        	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            	<tr>
                	<td align="center" valign="top">
                    	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="<?php echo $waepp_template_container; ?>">
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Header -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style="<?php echo $waepp_template_header; ?>" bgcolor="<?php echo $waepp_base; ?>">
                                        <tr>
                                            <td>
                                            	<h1 style="<?php echo $waepp_header_content_h1; ?>"> [<?php echo  get_option('blogname' );?>]	<?php echo  _e( 'orders to be processed' , 'waepp' );?></h1>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Header -->
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Body -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                    	<tr>
                                            <td valign="top" style="<?php echo $waepp_body_content; ?>">
                                                <!-- Content -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top">
                                                            <div style="<?php echo $waepp_body_content_inner; ?>"> 
															<!--CONTINGUT-->
															<tr>
																<th scope="col" style="<?php echo $waepp_title_wrapper; ?>"><tt><big><?php echo _e( 'Product' , 'waepp' );?></big></tt></th>
																<?php if($order->get_exists_variations())	{
																		echo '<th scope="col" style="' . $waepp_title_wrapper . '"><tt><big>';
																		echo _e( 'Variation' , 'waepp' );
																		echo '</big></tt></th>';
																	}
																?>
																<th scope="col" style="<?php echo $waepp_title_wrapper; ?>"><tt><big><?php echo _e( 'Amount' , 'waepp' );?></big></tt></th>
															</tr>
															<?php 
																foreach ( $order->products as $prod_id=>$qty )	{
																	$variation = Array();
																	if($order->has_variations($prod_id)) {	
																		foreach($order->cantidades_variacion[$prod_id] as $variaciones){
															?>
																		<tr>
																		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo $order->get_name($prod_id);?></th>
																		<th scope="col" style="text-align:left; border: 1px solid #eee;"><ul>
															<?php
																			$qty = array_keys($variaciones);
																			foreach($variaciones[$qty[0]] as $attr=>$attr_value){
																				echo "<li>" . $attr. ": <span style='". $waepp_attribute_value . "'>" . $attr_value . "</span></li>";
																			}
															?>
																			</ul></th>
																			<th scope="col" style="text-align:center; border: 1px solid #eee;font-family: 'Lobster';font-size: x-large;"><?php echo $qty[0];?></th>
																			</tr>
															<?php
																			}
															?>
																		<tr>
															<?php
																	}else{ 
															?>
																		<tr>
																			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php echo $order->get_name($prod_id);?></th>
																			<?php if($order->get_exists_variations()) echo '<th scope="col" style="text-align:left; border: 0px solid #eee;"></th>';?>
																			<th scope="col" style="text-align:center; border: 1px solid #eee; font-family: 'Lobster'; font-size: x-large;"><?php echo $qty;?></th>
																		</tr>
																	<?php
																	}
																}	
															?>
														<!--FI CONTINGUT-->
														</div>
													</td>
												</tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Footer -->
                                	<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer" style="<?php echo $waepp_template_footer; ?>">
                                    	<tr>
                                        	<td valign="top">
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td colspan="2" valign="middle" id="credit" style="<?php echo $waepp_credit; ?>">
                                                        	<?php echo get_option( 'blogname' );?> - eCommerce
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Footer -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
