<?php
/*
Plugin Name: Seo Wizard
Plugin URI: http://www.seowizard.org/
Description: SEO Wizard is an all-in-one seo solution. View post/page analysis, integrate social media, deep link juggernaut, auto linking, 404 monitor, redirect manager, robots.txt & htaccess editor & more!
Version: 4.0.2
Author: theseowizards
Author URI: http://www.seowizard.org/

LICENSE
    Copyright Seo UK Team 
	(email : support@seowizard.org) 
	(website : http://www.seowizard.org)
*/

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}

define( 'WSW_NAME',  'WP SEO Wizard' );
define( 'WSW_REQUIRED_PHP_VERSION', '5.3' );
define( 'WSW_REQUIRED_WP_VERSION',  '3.1' );

// Include Files
    $files = array(
        '/classes/wp-module',
        '/classes/wsw-main',
        '/classes/wsw-dashboard',
        '/classes/wsw-show',
        '/classes/wsw-setting',
        '/classes/wsw-calc',
        '/includes/admin-notice-helper/admin-notice-helper',
        '/lib/bootstrap',
        '/lib/Youtube/YoutubeInterface',
        '/lib/Youtube/YoutubeVideo',
        '/lib/LSI/lsi',
        '/lib/Self/keywords',
        '/lib/Self/html_styles',
        '/model/model-log',

        '/includes/jlfunctions/jlfunctions',
        '/includes/jlwp/jlwp',
        '/plugin/seo-functions',
        '/plugin/seo-update',
        '/modules/seo-module',
        '/modules/seo-importmodule'
    );

    foreach ($files as $file) {
        require_once plugin_dir_path( __FILE__ ).$file.'.php';
    }

// Init Plugin
    if ( class_exists( 'WSW_Main' ) ) {

        $GLOBALS['wp-seo-wizard'] = WSW_Main::get_instance();
        global $seo_update;
        $seo_update=new SEO_Update(__FILE__);
        register_activation_hook(   __FILE__, array( $GLOBALS['wp-seo-wizard'], 'activate' ) );
        register_deactivation_hook( __FILE__, array( $GLOBALS['wp-seo-wizard'], 'deactivate' ) );
    }
