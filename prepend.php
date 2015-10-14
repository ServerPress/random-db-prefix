<?php
global $ds_runtime;
if ( !$ds_runtime->is_localhost ) return;
if ( $ds_runtime->last_ui_event === false ) return;
if ( $ds_runtime->last_ui_event->action !== "site_created" ) return;

include_once( $ds_runtime->htdocs_dir . '/classes/string.php' );
include_once( $ds_runtime->htdocs_dir . '/classes/class-ds-utils.php' );
include_once( $ds_runtime->htdocs_dir . '/classes/class-ds-config-file.php' );

// Implement Random Database Table Prefix 
if ( ! class_exists( 'DS_Random_DB_Prefix' ) ) {

    class DS_Random_DB_Prefix {
        static function reset() {
        	global $ds_runtime;
        	
            $siteName = $ds_runtime->last_ui_event->info[0];
            $wpconfig = $ds_runtime->preferences->sites->{$siteName}->sitePath . '/wp-config.php';

            // Backup the config file
            if ( file_exists( $wpconfig ) ) {
                copy( $wpconfig, $wpconfig . '-sav' );
            }

            // Collect needed configuration info
            $config_file = new DS_ConfigFile( $wpconfig );
            $db_user = $config_file->get_key( 'DB_USER' );
            $db_password = $config_file->get_key( 'DB_PASSWORD' );
            
            $config_file->set_type( 'php-variable' );
            $currentPrefix = $config_file->get_key( 'table_prefix' );
            
            
            // Randomize the table prefix
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $random_string_length = 3;
            
            $newPrefix = '';
 				for ($i = 0; $i < $random_string_length; $i++) {
      		$newPrefix .= $characters[rand(0, strlen($characters) - 1)];
 			}
 			$newPrefix = $newPrefix . '_';
	
            // Set the configuration info in the new wp-config
            $source = dirname( __FILE__ ) . '/lib/wp-config-sample.php';
            $clean_config_file = new DS_ConfigFile( $source );
            $clean_config_file->set_key( 'DB_USER', $db_user );
            $clean_config_file->set_key( 'DB_NAME', $db_user );
            $clean_config_file->set_key( 'DB_PASSWORD', $db_password );
	        $clean_config_file->set_key( 'DB_HOST', '127.0.0.1' );

            // Set the salts
            $clean_config_file->set_key( 'AUTH_KEY', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'SECURE_AUTH_KEY', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'LOGGED_IN_KEY', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'NONCE_KEY', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'AUTH_SALT', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'SECURE_AUTH_SALT', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'LOGGED_IN_SALT', DS_Utils::random_salt() );
            $clean_config_file->set_key( 'NONCE_SALT', DS_Utils::random_salt() );

            $clean_config_file->set_type( 'php-variable' );
            $clean_config_file->set_key( 'table_prefix', $newPrefix );

            $clean_config_file->save( $wpconfig );
            
        }
    }
    DS_Random_DB_Prefix::reset();
}

