<?php
/*
Plugin Name: Shipping rate csv importer
Plugin URI: 
Description: 
Version: 0.1
Author: Incloud Marketing
Author URI: https://www.incloudmarketing.co/
*/

// Create a new table
function plugin_table(){

   global $wpdb;

   $tablename = $wpdb->prefix."shipping_kg_rate";

   $sql = "CREATE TABLE $tablename (
     id mediumint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
     department varchar(80) NOT NULL,
     citie varchar(80) NOT NULL,
     weight decimal(10,2) NOT NULL,
     kg_rate decimal(10,2) NOT NULL
   ) ";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

}
register_activation_hook( __FILE__, 'plugin_table' );

// Add menu
function plugin_menu() {
   add_menu_page("Costos envío importar x peso y ciudad ", "Costos envío x peso importar","manage_options", "Tarifas de Envío por Peso y Ciudad", "displayList",plugins_url('/shipping-rate-csv-importer/images/shipping-rate-icon.png'));
}
add_action("admin_menu", "plugin_menu");

function displayList(){
   include "shipping_rate_list.php";
}

add_filter( 'woocommerce_package_rates', 'incloud_woocommerce_set_shipping_rate', 9999, 2 );
    
function incloud_woocommerce_set_shipping_rate( $rates, $package ) {
   global $wpdb;

   $state_destination = $package['destination']['state'];
   $city_destination  = $package['destination']['city'];

   $tablename = $wpdb->prefix."shipping_kg_rate";
   $shipping_value = 0;    
   $contents_weight = WC()->cart->get_cart_contents_weight();
   $query = "SELECT kg_rate FROM $tablename WHERE TRIM(LOWER(department)) = TRIM(LOWER('$state_destination')) AND  TRIM(LOWER(citie)) = TRIM(LOWER('$city_destination')) AND CAST(weight AS DECIMAL(10,2)) = CAST($contents_weight AS DECIMAL(10,2))";
   $result = $wpdb->get_row( $query, ARRAY_A );
   
   if($contents_weight != null && $result != null && $result["kg_rate"] != null){
       //$shipping_value = floatval(WC()->cart->get_cart_contents_weight()) * floatval($result["kg_rate"]);    
       $shipping_value = floatval($result["kg_rate"]);    
   }else{
      $_POST['delivery_method'] ="blank";
      wc_add_notice( '<strong>Please select a Delivery method</strong>', 'error' );
      // Set the default shipping method on load: "Standard" flat rate
     //WC()->session->set('chosen_shipping_methods', array('flat_rate:9'));
     WC()->session->set('chosen_shipping_methods', null);
     return false;
   }

   // Loop through each shipping rate
   foreach ( $rates as $rate ) {
       // Set new rate cost
       $rate->cost=$shipping_value;
   }
   return $rates;
}