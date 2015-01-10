<?php
/**
 * This is the responsible to generate the data dinamicaly searching in the database
 *
 * @package    woocommerce-admin-email-processing-products
 * @subpackage woocommerce-admin-email-processing-products/includes
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class waepp_order{
	/**
	 * Array where key = post_id of all products that are on_hold
	 * 			   value = total quantity of that product that is on_hold
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $products    array( post_id => quantity )
	 */
	public $products;
	/**
	 * The order_item_id related to the order_id in oreder_items table
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $comandes    order_item_id
	 */
	public $comandes;
	/*
	 * Boolean true when at least exists one variation beetwen the processing products group
	 * @since    1.0.0
	 * @access   private
	 * @var      bool    $exists_variations    true when variation exists
	 */
	private $exists_variations;

	/*
	 * Array storing all the possible variable options of the same product
	 * @since    1.0.0
	 * @access   private
	 * @var      Array    $variations    
	 */
	private $variations;
	/*
	 * Array that keeps the association between the quantity of each variable product
	 * with the attributes that defines the variation
	 * @since    1.0.0
	 * @access   public
	 * @var      Array    $cantidades_variacion    
	 */
	public $cantidades_variacion;

	function __construct()	{
		$this->comandes = Array();
		$this->products = Array();
		$this->cantidades_variacion = Array();
		$this->variations = Array();
		$this->prepare_comandes();
	}
	
	/*
	 * prepares the data to be submited
	 *
	 * @since    1.0.0
	 */
	function prepare_comandes()	{
		$this->exists_variations = false;
		global $wpdb;
		/*
		 * gets the ID(s) of the Post that reffers the commanda
		 */
		$term_id_onhold = $wpdb->get_col( 
		$wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s" 
			, 'shop_order','wc-on-hold')
		);
		/*
		 * gets the order_item_id of the pending orders
		 */
		foreach ( $term_id_onhold as $comanda ){
			$order_item_id = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT order_item_id FROM wp_woocommerce_order_items WHERE order_id = %d AND order_item_type = %s" 
				, $comanda , 'line_item')
			);
			if(count($order_item_id) == 1 ) {		// when only 1 product demanded
				$this->comandes[] = $order_item_id[0];
			}
			else if(count($order_item_id) > 1) {		// when 2 or more different products demanded
				$array_orders_items_id = Array();
				foreach($order_item_id as $orders_items_id){
					$this->comandes[] = $orders_items_id;
				}
			}
		}
		/*
		 * Go over all the commands and organizes them to avoid repetitions and
		 * count the total quantity of each product
		 */
		foreach ( $this->comandes as $comanda){
			$prod_id = $wpdb->get_var(		// id in WP_POSTS of the original product post
				$wpdb->prepare(
					"SELECT meta_value FROM wp_woocommerce_order_itemmeta WHERE meta_key = %s AND order_item_id = %d" 
					, '_product_id' , $comanda)
				);

			$prod_qty = $wpdb->get_var(		// amount of each product
				$wpdb->prepare(
					"SELECT meta_value FROM wp_woocommerce_order_itemmeta WHERE meta_key = %s AND order_item_id = %d" 
					, '_qty' , $comanda)
				);

			$prod_variety = $wpdb->get_var(		// variety: 0 if doesn't has
				$wpdb->prepare(
					"SELECT meta_value FROM wp_woocommerce_order_itemmeta WHERE meta_key = %s AND order_item_id = %d" 
					, '_variation_id' , $comanda)
				);
				
			if ( $prod_variety != 0 ){	// variable products must be treated apart
				$this->exists_variations = true;

				$attribute_list = $this->variation_name($prod_variety);	// torna els noms dels atributs
				$item_variations = Array();
				foreach( $attribute_list as $atts ){	
					$attr_name = str_replace( 'attribute_', '', $atts ) ;	
					$atributes = $wpdb->get_var(	// returns the value of each attribute
						$wpdb->prepare(
							"SELECT meta_value FROM wp_woocommerce_order_itemmeta WHERE meta_key = %s AND order_item_id = %d" 
							, $attr_name , $comanda)
					);
					$item_variations[$attr_name] = $atributes;
				}

				if(!array_key_exists($prod_id, $this->products) ) {	
					$this->variations[$prod_id][] =  $item_variations; /**/ 
					$this->products[$prod_id] = 0; /**/
					$qty_var = Array();
					$qty_var_aux = Array();
					$qty_var[$prod_qty] = $item_variations;
					$qty_var_aux[] = $qty_var;
					$this->cantidades_variacion[$prod_id] = $qty_var_aux; /**/
				}
				else
				{
					$fore;
					$already_have = false;
					foreach($this->variations[$prod_id] as $variaciones_item){							
						if( !is_array($variaciones_item))
						{		
							$fore = $this->variations[$prod_id];	
						}
						else
						{
							$fore = $variaciones_item;	
						}			
						$number_variations = count($fore);
						$counter_variations_coincidence = 0;	
						$fore_keys = array_keys($fore);									
						foreach($fore_keys as $variation_name){
							if($fore[$variation_name] == $item_variations[$variation_name])
							{
								$counter_variations_coincidence++;
							}
						}
						if( $counter_variations_coincidence == $number_variations )
						{
							$already_have = true;
						}
					}
					if (!$already_have)
					{
						$this->variations[$prod_id][] = $item_variations;
						$qty_var = Array();
						$qty_var[$prod_qty] = $item_variations;
						$aux = Array();
						$aux = $this->cantidades_variacion[$prod_id];
						array_push($aux,$qty_var);
						$this->cantidades_variacion[$prod_id] = $aux;
					}
					else
					{	
						$var_id = 0;
						foreach ( $this->cantidades_variacion[$prod_id] as $elm )
						{
							$ar_key = array_keys($elm);
							$old_qty = $ar_key[0]; 
							$old_var = $elm[$ar_key[0]]; 
							$number_variations = count($old_var);
							$counter_variations_coincidence = 0;	
							foreach($old_var as $attr_name => $s)
							{
								if($old_var[$attr_name] == $item_variations[$attr_name])
								{
									$counter_variations_coincidence++;
								}
							}
								if( $counter_variations_coincidence == $number_variations )
								{
									$new_qty = $prod_qty + $old_qty;
									$new_value = Array($new_qty => $this->cantidades_variacion[$prod_id][$var_id][$old_qty]);
									$this->cantidades_variacion[$prod_id][$var_id] = $new_value;
								}
							$var_id++;
						}
					}
				}
			}
			else if ( !array_key_exists($prod_id, $this->products) ) {
				$this->products[$prod_id]=(int)$prod_qty;
			}
			else{
				$this->products[$prod_id] = $this->products[$prod_id] + (int)$prod_qty ;
			}
		}

		return $this->products;
	}
	
	/*
	 * Returns the name of all the attributes that a variable product has 
	 *
	 * @var      array    $attr_name    names of the attributes
	 * @since    1.0.0
	 */
	public function variation_name($post_id){	
		global $wpdb;
		$pr = 'attribute_%';
		$attr_name = Array(); 
		$attr_name = $wpdb->get_col(	
			$wpdb->prepare(
				"SELECT meta_key FROM wp_postmeta WHERE meta_key LIKE %s AND post_id = %d"
				, $pr , $post_id )
			);		
		return $attr_name;		
	}
	
	public function has_variations($post_id){	
		if($this->products[$post_id] == 0 )return true;
		return false;
	}
	
	/*
	 * Returns the name original product post name
	 *
	 * @var      string    $origin    name of the original product
	 * @since    1.0.0
	 */
	public function prod_origin($post_id)	{	
		global $wpdb;
		$origin = $wpdb->get_var(	
			$wpdb->prepare(
				"SELECT post_title FROM wp_posts WHERE ID = %d"
				, $post_id)
			);	
		return $origin;	
	}
	
	/*
	 * gets the name and redirects it to the page product
	 *
	 * @var      string    $name    name of the product
	 * @var      string    $url    url of the product page
	 * @since    1.0.0
	 */
	public function get_name($prod_id)	{
		global $wpdb;
		$results = $wpdb->get_results(	
			$wpdb->prepare(
				"SELECT post_title,guid FROM wp_posts WHERE ID = %d" 
				, $prod_id)
			);
		$name;
		$url;
		foreach( $results as $key => $row) {
			foreach( $row as $row_name => $row_value )	{
				if( $row_name == "post_title" ) $name = $row_value;
				else if( $row_name == "guid" ) $url = $row_value;
			}
		}
		$result = '<a href="' . $url . '">' . $name . '</a>';
		return $result;
	}
	
	/*
	 * @since    1.0.0
	 */
	public function get_exists_variations()	{
		return $this->exists_variations;
	}
}
?>
