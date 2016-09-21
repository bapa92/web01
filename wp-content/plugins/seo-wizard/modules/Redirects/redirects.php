<?php
/**
 * @package Premium

*/

if ( ! defined( 'WSW_REDIRECTS_PATH' ) ) {
	define( 'WSW_REDIRECTS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WSW_REDIRECTS_FILE' ) ) {
	define( 'WSW_REDIRECTS_FILE', __FILE__ );
}
/*
 * Class  WSW_Redirects
 */
if (class_exists('SEO_Module')) {
    class WSW_Redirects extends SEO_Module
    {
        public static function install()
        {
            // Load the Redirect File Manager
            require_once(WSW_REDIRECTS_PATH . 'classes/class-redirect-file-manager.php');
            // Create the upload directory
            WSW_Redirect_File_Manager::create_upload_dir();
        }

        /**
         * WSW_Redirects Constructor
         */
        public function __construct()
        {
            if(is_admin())
            {
                $this ->install();
                add_action('admin_menu', array($this, 'add_submenu_pages'));
            }

            $this->setup();
        }

        /**
         * Setup the Redirects SEO_WIZARD plugin
         */
        private function setup()
        {
            // include all classes
            require_once(dirname(__FILE__) . '/classes/class-seo-redirects-autoloader.php');
            $autoloader = new WSW_Redirect_Autoloader();
            spl_autoload_register(array($autoloader, 'load'));
            $this->instantiate_redirects();

            if (is_admin()) {
                // Post to Get on search
                add_action('admin_init', array($this, 'list_table_search_post_to_get'));
                // Settings
                add_action('admin_init', array($this, 'register_settings'));
                // Check if we need to save files after updating options
                add_action('update_option_wsw_redirect', array($this, 'save_redirect_files'), 10, 2);
                // Catch option save
                add_action('admin_init', array($this, 'catch_option_redirect_save'));
                add_action('admin_enqueue_scripts', array($this, 'enqueue_overview_script'));

            } else {
                // Add 404 redirect link to WordPress toolbar
                add_action('admin_bar_menu', array($this, 'admin_bar_menu'), 96);

                add_filter('redirect_canonical', array($this, 'redirect_canonical_fix'), 1, 2);
            }
        }



        /**
         * Instantiate all the needed redirect functions
         */
        private function instantiate_redirects()
        {
            $normal_redirect_manager = new WSW_URL_Redirect_Manager();
            $regex_redirect_manager = new WSW_REGEX_Redirect_Manager();

            if (is_admin()) {
                // Check if php redirects are disabled
                if (defined('WSW_DISABLE_PHP_REDIRECTS') && true === WSW_DISABLE_PHP_REDIRECTS) {

                    // Change the normal redirect autoload option
                    $normal_redirect_manager->redirects_change_autoload(false);

                    // Change the regex redirect autoload option
                    $regex_redirect_manager->redirects_change_autoload(false);

                } else {
                    $options = WSW_Redirect_Manager::get_options();

                    // If the disable_php_redirect option is not enabled we should enable auto loading redirects
                    if ('off' == $options['disable_php_redirect']) {
                        // Change the normal redirect autoload option
                        $normal_redirect_manager->redirects_change_autoload(true);

                        // Change the regex redirect autoload option
                        $regex_redirect_manager->redirects_change_autoload(true);
                    }
                }
            }
            if (defined('DOING_AJAX') && DOING_AJAX) {
                // Normal Redirect AJAX
                add_action('wp_ajax_wsw_save_redirect_url', array(
                    $normal_redirect_manager,
                    'ajax_handle_redirect_save',
                ));
                add_action('wp_ajax_wsw_delete_redirect_url', array(
                    $normal_redirect_manager,
                    'ajax_handle_redirect_delete',
                ));
                add_action('wp_ajax_wsw_create_redirect_url', array(
                    $normal_redirect_manager,
                    'ajax_handle_redirect_create',
                ));

                // Regex Redirect AJAX
                add_action('wp_ajax_wsw_save_redirect_regex', array(
                    $regex_redirect_manager,
                    'ajax_handle_redirect_save',
                ));
                add_action('wp_ajax_wsw_delete_redirect_regex', array(
                    $regex_redirect_manager,
                    'ajax_handle_redirect_delete',
                ));
                add_action('wp_ajax_wsw_create_redirect_regex', array(
                    $regex_redirect_manager,
                    'ajax_handle_redirect_create',
                ));
                add_action('wp_ajax_wsw_save_redirect_opton' , array($this, 'wsw_save_redirect_opton'));

                // Add URL reponse code check AJAX
                add_action('wp_ajax_wsw_check_url', array('WSW_Url_Checker', 'check_url'));
            } else {
                // Catch redirect
                add_action('template_redirect', array($normal_redirect_manager, 'do_redirects'), -999);

                // Catch regex redirects
                add_action('template_redirect', array($regex_redirect_manager, 'do_redirects'), -999);
            }
        }

        /**
         * Hooks into the `redirect_canonical` filter to catch ongoing redirects and move them to the correct spot
         *
         * @param string $redirect_url
         * @param string $requested_url
         *
         * @return string
         */
        function redirect_canonical_fix($redirect_url, $requested_url)
        {
            $redirects =  get_option('seo-wizard-redirects', array());
            $path = parse_url($requested_url, PHP_URL_PATH);
            if (isset($redirects[$path])) {
                $redirect_url = $redirects[$path]['url'];
                if ('/' === substr($redirect_url, 0, 1)) {
                    $redirect_url = home_url($redirect_url);
                }
                wp_redirect($redirect_url, $redirects[$path]['type']);
                exit;
            } else {
                return $redirect_url;
            }
        }

        function wsw_save_redirect_opton()
        {
            $seo_redirect = array();
            check_ajax_referer( 'wsw-redirects-ajax-security', 'ajax_nonce' );
            if( isset($_POST['disable_php_redirect']) && isset($_POST['separate_file']))
            {
                $seo_redirect['disable_php_redirect'] = $_POST['disable_php_redirect'];
                $seo_redirect['separate_file'] = $_POST['separate_file'];
                update_option('wsw_redirect' , $seo_redirect);
                echo 'success';
                die();

            }

        }


        /**
         * Enqueue post en term overview script
         *
         * @param string $hook
         */
        public function enqueue_overview_script($hook)
        {
            self::enqueue();
        }

        /**
         * Enqueues the do / undo redirect scripts
         */
        public static function enqueue()
        {
            wp_enqueue_script('wsw-Redirects-admin-overview', plugin_dir_url(WSW_REDIRECTS_FILE) . '/assets/js/wsw-redirects-admin-overview.js', array('jquery'));
            wp_localize_script('wsw-Redirects-admin-overview', 'wsw_redirects_strings', WSW_Redirects_Javascript_Strings::strings());
            wp_enqueue_script('wsw-Redirects-admin', plugin_dir_url(WSW_REDIRECTS_FILE) . '/assets/js/wsw-admin-redirects.js', array('jquery'));
            wp_localize_script('wsw-Redirects-admin', 'wsw_redirects_strings', WSW_Redirects_Javascript_Strings::strings());
            wp_enqueue_style('wsw-redirects-admin-style' , plugin_dir_url(WSW_REDIRECTS_FILE).'/assets/wsw-redirects-style.css' , array());
            wp_enqueue_script('wsw-zozo-js' );
            wp_enqueue_script('wsw-bootstrap-js' );
            wp_enqueue_script( 'wsw-colorpicker-js');
            wp_enqueue_style( 'wsw-bootstrap-css' );
            wp_enqueue_style( 'wsw-zozo-tab-css' );
            wp_enqueue_style('wsw-zozo-tab-flat-css' );
            wp_enqueue_style( 'wsw-colorpicker-css');
            wp_enqueue_style( 'wsw-admin-css' );
        }

        /**
         * Add 'Create Redirect' option to admin bar menu on 404 pages
         */
        public function admin_bar_menu()
        {

            if (is_404()) {
                global $wp, $wp_admin_bar;

                $parsed_url = parse_url(home_url(add_query_arg(null, null)));

                if (false !== $parsed_url) {
                    $old_url = $parsed_url['path'];

                    if (isset($parsed_url['query']) && $parsed_url['query'] != '') {
                        $old_url .= '?' . $parsed_url['query'];
                    }

                    $old_url = urlencode($old_url);

                    $wp_admin_bar->add_menu(array(
                        'id' => 'wsw-Redirects-create-redirect',
                        'title' => __('Create Redirect', 'seo-wizard'),
                        'href' => admin_url('admin.php?page=wsw_redirects&old_url=' . $old_url)
                    ));
                }
            }
        }


        public function add_submenu_pages()
        {
            require_once(dirname(__FILE__) . '/classes/class-page-redirect.php');
            add_submenu_page('wsw_dashboard_page' , 'Redirects' , 'Redirects' , 'manage_options' ,'wsw_redirect', array('WSW_Page_Redirect', 'display') );

        }

        /**
         * Register the Redirects settings
         */
        public function register_settings()
        {
            register_setting('seo_wizard_redirect_options', 'wsw_redirect');
        }

        /**
         * Hook that runs after the 'wpseo_redirect' option is updated
         *
         * @param array $old_value
         * @param array $value
         */
        public function save_redirect_files($old_value, $value)
        {
            // Check if we need to remove the WPSEO redirect entries from the .htacccess file
            $remove_htaccess_entries = false;

            // Check if the 'disable_php_redirect' option set to true/on
            if (null != $value && isset($value['disable_php_redirect']) && 'on' == $value['disable_php_redirect']) {

                // Remove .htaccess entries if the 'separate_file' option is set to true
                if (isset($value['separate_file']) && 'on' == $value['separate_file']) {
                    $remove_htaccess_entries = true;
                }

                // The 'disable_php_redirect' option is set to true(on) so we need to generate a file.
                // The Redirect Manager will figure out what file needs to be created.
                $redirect_manager = new WSW_URL_Redirect_Manager();
                $redirect_manager->save_redirect_file();

            } else {
                // No settings are set so we should also strip the .htaccess redirect entries in this case
                $remove_htaccess_entries = true;
            }

            // Check if we need to remove the .htaccess redirect entries
            if ($remove_htaccess_entries) {
                // Remove the .htaccess redirect entries
                $redirect_manager = new WSW_URL_Redirect_Manager();
                $redirect_manager->clear_htaccess_entries();
            }

        }

        /**
         * Do custom action when the redirect option is saved
         */
        public function catch_option_redirect_save()
        {
            if (isset ($_POST['disable_php_redirect'])) {

                if (current_user_can('manage_options')) {
                    $enable_autoload = (isset ($_POST['disable_php_redirect'])) ? false : true;
                    // Change the normal redirect autoload option
                    $normal_redirect_manager = new WSW_URL_Redirect_Manager();
                    $normal_redirect_manager->redirects_change_autoload($enable_autoload);

                    // Change the regex redirect autoload option
                    $regex_redirect_manager = new WSW_REGEX_Redirect_Manager();
                    $regex_redirect_manager->redirects_change_autoload($enable_autoload);
                }
            }
        }

        /**
         * Catch the redirects search post and redirect it to a search get
         */
        public function list_table_search_post_to_get()
        {
            if (isset($_POST['s']) && trim($_POST['s']) != '') {

                // Check if the POST is on one of our pages
                if (!isset ($_GET['page']) || ($_GET['page'] != 'wsw_redirects') ){
                    return;
                }

                // Check if there isn't a bulk action post, bulk action post > search post
                if (isset ($_POST['create_redirects']) || isset($_POST['wsw_redirects_bulk_delete'])) {
                    return;
                }

                // Base URL
                $url = get_admin_url() . 'admin.php?page=' . $_GET['page'];

                // Add search or reset it
                if ($_POST['s'] != '') {
                    $url .= '&s=' . $_POST['s'];
                }


                // Orderby
                if (isset($_GET['orderby'])) {
                    $url .= '&orderby=' . $_GET['orderby'];
                }

                // Order
                if (isset($_GET['order'])) {
                    $url .= '&order=' . $_GET['order'];
                }

                // Do the redirect
                wp_redirect($url);
                exit;
            }
        }

        /**
         * Output admin css in admin head
         */
        public function admin_css()
        {
            echo "<style type='text/css'>#wsw_content_top{ padding-left: 0; margin-left: 0; }</style>";
        }

        /**
         * Load textdomain
         */

    }
}
