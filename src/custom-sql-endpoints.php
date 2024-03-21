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
	 
    if( function_exists('\have_rows') && \have_rows('custom_sql_endpoints', 'options') ) {
    
        while( \have_rows('custom_sql_endpoints', 'options') ) : \the_row();
    
            if ( \get_sub_field('custom_endpoint_enabled') ) {
                
                $users = \get_sub_field('custom_endpoint_user');
                $slug = \get_sub_field('custom_endpoint_slug');
                $format = \get_sub_field('custom_endpoint_format');
                $query = \get_sub_field('custom_sql_query');
                
                \register_rest_route( CUSTOM_NAMESPACE, '/'.$slug, array(
                    'methods' => 'GET',
                    'callback' => function ( \WP_REST_Request $request ) use ($slug, $format, $query)  {             
                        global $wpdb;
                        $wpdb->show_errors();
                        
                        $params = $request->get_params();
                        
                        if (preg_match('/{(.+)}/', $query, $matches)) {
                           for ($i=1; $i<count($matches); $i++) {
                                if (array_key_exists($matches[$i], $params)) {
                                   $query = preg_replace("/{{$matches[$i]}}/", \esc_sql($params[$matches[$i]]), $query);
                                } else {
                                    // emit missing parameter error
                                }
                           } 
                        }        
                        
                        return $wpdb->get_results( $wpdb->prepare( $query ), $format );
                    },
                    'permission_callback' => function() use ($users) {
                        return $users ? in_array(\get_current_user_id(), $users) : true;
                    }
                ) );
                
            }
    
        endwhile;

    }
    
} );