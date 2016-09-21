<?php
/**
 * module for customizing the robot.txt and .htaccess files
 */
if (class_exists('SEO_Module')) {
    class WSW_Editor extends SEO_Module
    {
        public function __construct()
        {
            if(is_admin())
            {
                add_action('admin_menu', array($this, 'add_submenu_pages'));
            }

            $this->setup();
        }
        public function setup()
        {
            require_once(dirname(__FILE__) . '/editor_view.php');
            add_action('admin_enqueue_scripts', array($this, 'enqueue_overview_script'));
            if (defined('DOING_AJAX') && DOING_AJAX)
            {
                add_action('wp_ajax_wsw_create_robots_text' , array($this, 'wsw_create_robots_text'));
                add_action('wp_ajax_wsw_update_content_robots' , array($this, 'wsw_update_content_robots'));
                add_action('wp_ajax_wsw_edit_htaccess_file' , array($this, 'wsw_edit_htaccess_file'));
            }
        }
        public function enqueue_overview_script(){
            wp_enqueue_script('wsw-editor-admin-js', plugin_dir_url(__FILE__) . '/assets/js/wsw-editor-main.js', array('jquery'));
            wp_enqueue_style('wsw-editor-admin-style' , plugin_dir_url(__FILE__).'/assets/css/wsw-editor-main.css' , array());
            wp_enqueue_script('wsw-zozo-js' );
            wp_enqueue_script('wsw-bootstrap-js' );
            wp_enqueue_script( 'wsw-colorpicker-js');
            wp_enqueue_style( 'wsw-bootstrap-css' );
            wp_enqueue_style( 'wsw-zozo-tab-css' );
            wp_enqueue_style('wsw-zozo-tab-flat-css' );
            wp_enqueue_style( 'wsw-colorpicker-css');
        }
        public function add_submenu_pages()
        {
            add_submenu_page('wsw_dashboard_page' , 'File Edit' , 'File Edit' , 'manage_options' ,'wsw_editor', array(new WSW_editor_page , 'display'));
        }
        public function wsw_create_robots_text()
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                die( __( 'You cannot create a robots.txt file.', 'seo-wizard' ) );
            }
            check_ajax_referer('wsw-robots-update-ajax-nonce' , 'security' , false);
            if(isset($_POST['value']) && $_POST['value'] == '1')
            {
                $robots_file = get_home_path() . 'robots.txt';
                ob_start();
                error_reporting( 0 );
                do_robots();
                $robots_content = ob_get_clean();
                $f = fopen( $robots_file, 'x' );
                fwrite( $f, $robots_content );
                echo $robots_content;
                wp_die();
            }
        }
        public function wsw_update_content_robots()
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                die( __( 'You cannot edit the robots.txt file.', 'seo-wizard' ) );
            }
            check_ajax_referer('wsw-robots-update-ajax-nonce' , 'security' , false);
            $robots_file = get_home_path() . 'robots.txt';
            if ( file_exists( $robots_file ) ) {
                $content = stripslashes( $_POST['content'] );
                if ( is_writable( $robots_file ) ) {
                    $f = fopen( $robots_file, 'w+' );
                    fwrite( $f, $content );
                    fclose( $f );
                    $output = 'success';
                    echo $output;
                    wp_die();
                }
            }
            echo 'failed';
            wp_die();
        }
        public function wsw_edit_htaccess_file()
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                die( __( 'You cannot edit the .htaccess file.', 'seo-wizard' ) );
            }
            check_ajax_referer('wsw-access-update-ajax-nonce' , 'security' , false);
            $htaccess_file = get_home_path() . '.htaccess';
            if ( file_exists( $htaccess_file ) ) {
                $contents = stripslashes( $_POST['content'] );
                if ( is_writeable( $htaccess_file ) ) {
                    $f = fopen( $htaccess_file, 'w+' );
                    fwrite( $f, $contents );
                    fclose( $f );
                    echo 'success';
                    wp_die();
                }
            }
            echo 'failed';
            wp_die();
        }
    }
}