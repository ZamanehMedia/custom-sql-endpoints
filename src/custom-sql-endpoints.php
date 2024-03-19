<?php
/**
 * Custom SQL Endpoints
 *
 * @package           CustomSQLEndpoints
 * @author            Marcel Oomens
 * @copyright         2024 Zamaneh Media Foundation
 * @license           MIT
 *
 * @wordpress-plugin
 * Plugin Name:       Custom SQL Endpoints
 * Plugin URI:        https://451.tools/utilities/custom-sql-endpoints
 * Description:       Description of the plugin.
 * Version:           1.0.0
 * Requires at least: 6.1
 * Requires PHP:      7.2
 * Author:            Marcel Oomens
 * Author URI:        https://451.tools
 * Text Domain:       plugin-slug
 * License:           MIT
 * License URI:       https://spdx.org/licenses/MIT.html
 * Update URI:        https://example.com/my-plugin/
 */

define( 'CUSTOM_NAMESPACE', 'custom-sql/v1' );

\add_action( 'rest_api_init', function() {
	
    \register_rest_route( CUSTOM_NAMESPACE, '/signature-distribution', array(
        'methods' => 'GET',
        'callback' => function ( \WP_REST_Request $request ) {
            
            global $wpdb;
            $wpdb->show_errors();
            
            $params = $request->get_params();
            
            $query = "SELECT m.meta_value, count(*) FROM wp_comments AS c INNER JOIN wp_commentmeta AS m ON c.comment_ID = m.comment_id WHERE c.comment_post_ID IN ({petitions}) AND m.meta_key = 'dk_signature_location' GROUP BY m.meta_value";
            
            if (preg_match('/{(.+)}/', $query, $matches)) {
               for ($i=1; $i<count($matches); $i++) {
                    if (array_key_exists($matches[$i], $params)) {
                       $query = preg_replace("/{{$matches[$i]}}/", \esc_sql($params[$matches[$i]]), $query);
                    } else {
                        // emit missing parameter error
                    }
               } 
            }        
            
            $result = $wpdb->get_results( $wpdb->prepare( $query ), ARRAY_N );
            
            return array( $result );
        },
        'permission_callback' => '__return_true'
    ) );
    
} );