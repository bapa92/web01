<?php
/**
 * The main class. Provides plugin-level functionality.
 * 
 * @since 0.1
 */
define('WSW_MODULE_ENABLED',  __('Enabled', 'seo-wizard'));
define('WSW_MODULE_SILENCED', __('Silenced', 'seo-wizard'));
define('WSW_MODULE_HIDDEN',  __('Hidden', 'seo-wizard'));
define('WSW_MODULE_DISABLED', __('Disabled', 'seo-wizard'));
include_once(ABSPATH.WPINC.'/feed.php');

class SEO_Update {
	
	/********** VARIABLES **********/
	
	/**
	 * Stores all module class instances.
	 * 
	 * @since 0.1
	 * @var array
	 */
	var $modules = array();
	
	/**
	 * Stores the names of disabled modules.
	 * 
	 * @since 0.1
	 * @var array
	 */
	var $disabled_modules = array();
	

	var $default_menu_module = 'modules';
	

	var $plugin_file_path;
	

	var $plugin_file_url;
	

	var $plugin_dir_path;
	

	var $plugin_dir_url;
	

	var $plugin_basename = '';
	

	var $hit = array();
	

	var $hit_redirect_trigger = '';

	function __construct($plugin_file) {
		

		//Save hit data
		add_action('shutdown', array(&$this, 'save_hit'));
		
		/********** CLASS CONSTRUCTION **********/
		
		//Load data about the plugin file itself into the class
		$this->load_plugin_data($plugin_file);

		/********** INITIALIZATION **********/
		
	    //Load plugin modules. Must be called *after* load_plugin_data()
		$this->load_modules();
		
		
		/********** PLUGIN EVENT HOOKS **********/
		
		//If we're activating the plugin, then call the activation function
		register_activation_hook($this->plugin_file_path, array(&$this, 'activate'));
		
		//If we're deactivating the plugin, then call the deactivation function
		register_deactivation_hook($this->plugin_file_path, array(&$this, 'deactivate'));
		
		
		/********** ACTION & FILTER HOOKS **********/
		
		//Initializes modules at WordPress initialization
		add_action('init', array(&$this, 'load_textdomain'), 0); //Run before widgets_init hook (wp-includes/default-widgets.php)
		add_action('init', array(&$this, 'init'));
		
		//Hook to output all <head> code
		add_action('wp_head', array(&$this, 'template_head'), 1);
		
		//Log this visitor!
		if ($this->get_setting('log_hits', true, 'settings')) {
			add_filter('redirect_canonical', array(&$this, 'log_redirect_canonical'));
			add_filter('wp_redirect', array(&$this, 'log_redirect'), 10, 2);
			add_filter('status_header', array(&$this, 'log_hit'), 10, 2);
		}

		
		//Admin-only hooks
		if (is_admin()) {

			//Hook to include JavaScript and CSS

            add_action('admin_enqueue_scripts', array(&$this, 'admin_includes'));
			
			//Hook to add plugin notice actions
			add_action('admin_head', array(&$this, 'plugin_page_notices'));
			
			//Hook to remove other plugins' notices from our admin pages
			add_action('admin_head', array(&$this, 'remove_admin_notices'));
			
			/*if (!get_option('blog_public')) {
				//Add admin-wide notice
				add_action('admin_notices', array(&$this, 'private_blog_admin_notice'));
			}*/
			
			add_action('admin_init', array(&$this, 'admin_init'));

			add_action('admin_menu', array(&$this, 'add_blog_admin_menus'), 10);

			add_action('admin_head', array(&$this, 'admin_help'), 11);

			add_action('do_meta_boxes', array(&$this, 'add_postmeta_box'));
			add_action('save_post',  array(&$this, 'save_postmeta_box'), 10, 2);

		//	add_action("in_plugin_update_message-{$this->plugin_basename}", array(&$this, 'plugin_update_info'), 10, 2);
		//	add_filter('transient_update_plugins', array(&$this, 'add_plugin_upgrade_notice'));

		//	add_filter("plugin_action_links_{$this->plugin_basename}", array(&$this, 'plugin_action_links'));
		//	add_filter("network_admin_plugin_action_links_{$this->plugin_basename}", array(&$this, 'plugin_action_links'));

			add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta_filter'), 10, 2);
			
			//JLSuggest AJAX
			add_action('wp_ajax_su-jlsuggest-autocomplete', array(&$this, 'jlsuggest_autocomplete'));
			
			// add dashboart widget
			add_action( 'wp_dashboard_setup', array(&$this, 'WSW_add_dashboard_widgets'));
		}
	}
	
	
	/********** PLUGIN EVENT FUNCTIONS **********/
	
	/**
	 * This will be called if the plugin is being run for the first time.
	 * 
	 * @since 0.1
	 */
	function install() { }


	/**
	 * WordPress will call this when the plugin is activated, as instructed by the register_activation_hook() call in {@link __construct()}.
	 * 
	 * @since 0.1
	 */
	function activate() {		
		foreach ($this->modules as $key => $module) {
			$this->modules[$key]->activate();
		}
	}
	
	/**
	 * WordPress will call this when the plugin is deactivated, as instructed by the register_deactivation_hook() call in {@link __construct()}.
	 * 
	 * @since 0.1
	 */
	function deactivate() {
		
		//Let modules run deactivation tasks
		foreach ($this->modules as $key => $module) {
			$this->modules[$key]->deactivate();
		}
		
		//Unschedule all cron jobs		
		$this->remove_cron_jobs(true);
		
		//Delete all cron job records, since the jobs no longer exist
		$psdata = (array)get_option('seo_update', array());
		unset($psdata['cron']);
		update_option('seo_update', $psdata);
	}
	
	/**
	 * Calls module deactivation/uninstallation functions and deletes all database data.
	 * 
	 * @since 0.1
	 */
	function uninstall() {
		
		//Deactivate modules and cron jobs
		$this->deactivate();
		
		//Let modules run uninstallation tasks
	//	do_action('WSW_uninstall');
		
		//Delete module data
		$psdata = (array)get_option('seo_update', array());
		if (!empty($psdata['modules'])) {
			$module_keys = array_keys($psdata['modules']);
			foreach ($module_keys as $module)
				delete_option("seo_update_module_$module");
		}
		
		//Delete plugin data
		delete_option('seo_update');
	}
	
	
	/********** INITIALIZATION FUNCTIONS **********/
	
	/**
	 * Fills class variables with information about where the plugin is located.
	 * 
	 * @since 0.1
	 * @uses $plugin_file_path
	 * @uses $plugin_file_url
	 * @uses $plugin_dir_path
	 * @uses $plugin_dir_url
	 * 
	 * @param string $plugin_path The path to the "official" plugin file.
	 */
	function load_plugin_data($plugin_path) {
		
		//Load plugin path/URL information
		$this->plugin_basename  = plugin_basename($plugin_path);
		$this->plugin_dir_path  = trailingslashit(dirname(trailingslashit(WP_PLUGIN_DIR).$this->plugin_basename));
		$this->plugin_file_path = trailingslashit(WP_PLUGIN_DIR).$this->plugin_basename;
		$this->plugin_dir_url   = trailingslashit(plugins_url(dirname($this->plugin_basename)));
		$this->plugin_file_url  = trailingslashit(plugins_url($this->plugin_basename));
	}
	
	/**
	 * Finds and loads all modules. Runs the activation functions of newly-uploaded modules.
	 * Updates the modules list and saves it in the database. Removes the cron jobs of deleted modules.*/
	function load_modules() {
		
		$this->disabled_modules = array();
		$this->modules = array();
		
		$psdata = (array)get_option('seo_update', array());
		
		//The plugin_dir_path variable must be set before calling this function!
		if (!$this->plugin_dir_path) return false;
		
		//If no modules list is found, then create a new, empty list.
		if (!isset($psdata['modules']))
			$psdata['modules'] = array();
		
		//Get the modules list from last time the plugin was loaded.
		$oldmodules = $psdata['modules'];
		
		//The modules are in the "modules" subdirectory of the plugin folder.
		$dirpath = $this->plugin_dir_path.'modules';
		$dir = opendir($dirpath);
		
		//This loop will be repeated as long as there are more folders to inspect
		while ($folder = readdir($dir)) {
			
			//If the item is a folder...
			if (suio::is_dir($folder, $dirpath)) {
				
				//Open the subfolder
				$subdirpath = $dirpath.'/'.$folder;
				$subdir = opendir($subdirpath);

				while ($file = readdir($subdir)) {
					
					//Modules are non-directory files with the .php extension
					//We need to exclude index.php or else we'll get 403s galore
					if (suio::is_file($file, $subdirpath, 'php') && $file != 'index.php') {
						
						$filepath = $subdirpath.'/'.$file;
						
						//Figure out the module's array key and class name
						$module = strval(strtolower(substr($file, 0, -4)));
						$class = 'WSW_'.str_replace(' ', '', ucwords(str_replace('-', ' ', $module)));

						//Load the module's code
						include_once $filepath;
						
						//If this is actually a module...
						if (class_exists($class)) {

							if (($module_parent = call_user_func(array($class, 'get_parent_module'))) && !call_user_func(array($class, 'is_independent_module')))
								$module_disabled = (isset($oldmodules[$module_parent]) && $oldmodules[$module_parent] == WSW_MODULE_DISABLED);
							else
								$module_disabled = (isset($oldmodules[$module]) && $oldmodules[$module] == WSW_MODULE_DISABLED);
							
							if (!isset($oldmodules[$module]) && call_user_func(array($class, 'get_default_status')) == WSW_MODULE_DISABLED)
								$module_disabled = true;
							
							if (in_array($module, $this->get_invincible_modules())) {
								$module_disabled = false;
								$oldmodules[$module] = WSW_MODULE_ENABLED;
							}
							
							//If this module is disabled...
							if ($module_disabled) {
								
								$this->disabled_modules[$module] = $class;
								
							} else {
								
								//Create an instance of the module's class and store it in the array
								$this->modules[$module] = new $class;
								//We must tell the module what its key is so that it can save settings
								$this->modules[$module]->module_key = $module;
								
								//Tell the module what its URLs are
								$this->modules[$module]->module_dir_rel_url = $mdirrelurl = "modules/$folder/";
								$this->modules[$module]->module_rel_url = $mdirrelurl . $file;
								$this->modules[$module]->module_dir_url = $mdirurl = $this->plugin_dir_url . $mdirrelurl;
								$this->modules[$module]->module_url		= $mdirurl . $file;

								//Give the module this plugin's object by reference
								$this->modules[$module]->plugin =& $this;
								
								//Call post-construction function
								$this->modules[$module]->load();
							}
						} //If this isn't a module, then the file will simply be included as-is
					}
				}
			}
		}
		
		//If the loop above found modules, then sort them with our special sorting function
		//so they appear on the admin menu in the right order
		if (count($this->modules) > 0)
			uasort($this->modules, array(&$this, 'module_sort_callback'));
		
		//Now we'll compare the current module set with the one from last time.
		
		//Construct the new modules list that'll go in the database.
		//This code block will add/activate new modules, keep existing ones, and remove (i.e. not add) deleted ones.
		foreach ($this->modules as $key => $module) {
			if (isset($oldmodules[$key])) {
				$newmodules[$key] = $oldmodules[$key];
			} else {
				$this->modules[$key]->activate();
				$newmodules[$key] = $this->modules[$key]->get_default_status();
			}
		}
		
		foreach ($this->modules as $key => $module) {
			if (($module_parent = $this->modules[$key]->get_parent_module()) && !$this->modules[$key]->is_independent_module())
				$newmodules[$key] = $newmodules[$module_parent];
		}
		
		//Register disabled modules as such
		foreach ($this->disabled_modules as $key => $name) {
			$newmodules[$key] = WSW_MODULE_DISABLED;
		}
		
		//Save the new modules list
		$psdata['modules'] = $newmodules;
		if ($newmodules != $oldmodules) update_option('seo_update', $psdata);
		
		//Remove the cron jobs of deleted modules
		$this->remove_cron_jobs();
		
		//Tell the modules what their plugin page hooks are
		foreach ($this->modules as $key => $module) {
			$menu_parent_hook = $this->modules[$key]->get_menu_parent_hook();
			
			if ($this->modules[$key]->is_menu_default())
				$this->modules[$key]->plugin_page_hook = $plugin_page_hook = "toplevel_page_$menu_parent_hook";
			elseif ('options-general.php' == $menu_parent_hook)
				$this->modules[$key]->plugin_page_hook = $plugin_page_hook = 'settings_page_' .
					$this->key_to_hook($this->modules[$key]->get_module_or_parent_key());
			else
				$this->modules[$key]->plugin_page_hook = $plugin_page_hook = $menu_parent_hook . '_page_' .
					$this->key_to_hook($this->modules[$key]->get_module_or_parent_key());
			
			add_action("load-$plugin_page_hook", array($this->modules[$key], 'load_hook'));
		}
		
		if (!$this->module_exists($this->default_menu_module)) {
			foreach ($this->modules as $key => $module) {
				if ($this->modules[$key]->get_menu_parent() === 'seo' && $this->modules[$key]->get_parent_module() == false) {
					$this->default_menu_module = $key;
					break;
				}
			}
		}
	}
	
	/**
	 * Runs during WordPress's init action.
	 * Loads the textdomain and calls modules' initialization functions.
	 * 
	 * @since 0.1
	 * @uses $plugin_file_path
	 * @uses WSW_Module::load_default_settings()
	 * @uses WSW_Module::init()
	 */
	function init() {
		
		//Load default module settings and run modules' init tasks
		foreach ($this->modules as $key => $module) {
			//Accessing $module directly causes problems when the modules use the &$this reference
			$this->modules[$key]->load_default_settings();
			$this->modules[$key]->load_child_modules();
		}
		
		//Only run init tasks after all other init functions are completed for all modules
		foreach ($this->modules as $key => $module) {
			if (count($this->modules[$key]->get_children_admin_page_tabs()))
				$this->modules[$key]->admin_page_tabs_init();
			if (defined('WSW_UPGRADE'))
				$this->modules[$key]->upgrade();
			$this->modules[$key]->init();
		}
		
		global $pagenow;
		if ('post.php' == $pagenow || 'post-new.php' == $pagenow) {
			add_action('admin_enqueue_scripts', array(&$this, 'postmeta_box_tabs_init'));
		}
	}
	
	/**
	 * @since 6.9.7
	 */
	function load_textdomain() {
		load_plugin_textdomain('seo-wizard', '', trailingslashit(plugin_basename($this->plugin_dir_path)) . 'translations');
	}
	
	/**
	 * Attached to WordPress' admin_init hook.
	 * Calls the admin_page_init() function of the current module(s).
	 * 
	 * @since 6.0
	 * @uses $modules
	 * @uses WSW_Module::is_module_admin_page()
	 * @uses WSW_Module::admin_page_init()
	 */
	function admin_init() {
		global $pagenow;
		
		foreach ($this->modules as $key => $x_module) {
			if ('post.php' == $pagenow || 'post-new.php' == $pagenow)
				$this->modules[$key]->editor_init();
			elseif ($this->modules[$key]->is_module_admin_page())
				$this->modules[$key]->admin_page_init();
		}
	}
	
	/********** MODULE FUNCTIONS **********/
	
	/**
	 * @since 7.2.5
	 */
	function get_invincible_modules() {
		$ims = array('modules');
		
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		
		if (is_multisite() && is_plugin_active_for_network($this->plugin_basename))
			$ims[] = 'settings';
		
		return $ims;
	}
	
	/********** SETTINGS FUNCTIONS **********/
	
	/**
	 * Gets the value of a module setting.
	 * 
	 * @since 1.0
	 * @uses $modules
	 * @uses WSW_Module::get_setting()
	 * 
	 * @param string $key The name of the setting to retrieve.
	 * @param mixed $default What should be returned if the setting does not exist. Optional.
	 * @param string|null $module The module to which the setting belongs. Defaults to the current module. Optional.
	 * @return mixed The value of the setting, or the $default variable.
	 */
	function get_setting($key, $default, $module) {
		if (isset($this->modules[$module]))
			return $this->modules[$module]->get_setting($key, $default);
		else
			return $default;
	}

	/********** LOGGING FUNCTIONS **********/
	
	/**
	 * Saves the hit data to the database if so instructed by a module.
	 * 
	 * @since 0.9
	 * @uses $hit
	 */
	function save_hit() {
		
		if (!empty($this->hit) && $this->get_setting('log_hits', true, 'settings'))
			do_action('WSW_save_hit', $this->hit);
	}
	
	/**
	 * Saves information about the current hit into an array, which is later saved to the database.
	 * 
	 * @since 0.1
	 * @uses get_current_url()
	 * @uses $hit_id
	 * 
	 * @param string $status_header The full HTTP status header. Unused and returned as-is.
	 * @param int $status_code The numeric HTTP status code.
	 * @param string $redirect_url The URL to which the visitor is being redirected. Optional.
	 * @return string Returns the $status_header variable unchanged.
	 */
	function log_hit($status_header, $status_code, $redirect_url = '') {
		
		//Only log hits from non-logged-in users
		if (!is_user_logged_in()) {
			
			//Get the current URL
			$url = suurl::current();
			
			//Put it all into an array
			$data = array(
				  'time' => time()
				, 'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
				, 'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
				, 'url' => $url
				, 'redirect_url' => $redirect_url
				, 'redirect_trigger' => $this->hit_redirect_trigger
				, 'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
				, 'status_code' => $status_code
			);
			
			//We don't want to overwrite a redirect URL if it's already been logged
			if (!empty($this->hit['redirect_url']))
				$data['redirect_url'] = $this->hit['redirect_url'];
			
			//Put the hit data into our variable.
			//We'll save it to the database later, since the hit data may change as we gather further information
			//(e.g. when the redirect URL is discovered).
			$this->hit = $data;
		}
		
		//This function can be used as a WordPress filter, so we return the needed variable.
		return $status_header;
	}
	
	/**
	 * A wp_redirect WordPress filter that logs the URL to which the visitor is being redirected.
	 * 
	 * @since 0.2
	 * @uses log_hit()
	 * 
	 * @param string $redirect_url The URL to which the visitor is being redirected.
	 * @param int $status_code The numeric HTTP status code.
	 * @return string The unchanged $redirect_url parameter.
	 */
	function log_redirect($redirect_url, $status_code) {
		if (empty($this->hit_redirect_trigger)) $this->hit_redirect_trigger = 'wp_redirect';
		$this->log_hit(null, $status_code, $redirect_url); //We call log_hit() again so we can pass along the redirect URL
		return $redirect_url;
	}
	
	/**
	 * A redirect_canonical WordPress filter that logs the fact that a canonical redirect is being issued.
	 * 
	 * @since 0.3
	 * @uses log_hit()
	 * 
	 * @param string $redirect_url The URL to which the visitor is being redirected.
	 * @return string The unchanged $redirect_url parameter.
	 */
	function log_redirect_canonical($redirect_url) {
		if (empty($this->hit_redirect_trigger)) $this->hit_redirect_trigger = 'redirect_canonical';
		return $redirect_url;
	}
	
	
	/********** ADMIN MENU FUNCTIONS **********/
	
	/**
	 * Constructs the "SEO" menu and its subitems.
	 * 
	 * @since 0.1
	 * @uses $modules
	 * @uses get_module_count_code()
	 * @uses WSW_Module::get_menu_count()
	 * @uses WSW_Module::get_menu_pos()
	 * @uses WSW_Module::get_menu_title()
	 * @uses WSW_Module::get_page_title()
	 * @uses key_to_hook()
	 */
	function add_menus($admin_scope = 'blog') {
		
		$psdata = (array)get_option('seo_update', array());
		
		//If subitems have numeric bubbles, then add them up and show the total by the main menu item
		$count = 0;
		foreach ($this->modules as $key => $module) {
			if (	(empty($psdata['modules']) || $psdata['modules'][$key] > WSW_MODULE_SILENCED)
					&& $module->get_menu_count() > 0
					&& $module->get_menu_parent() == 'seo'
					&& $module->is_independent_module()
					&& $module->belongs_in_admin($admin_scope)
					)
				$count += $module->get_menu_count();
		}
		$main_count_code = $this->get_menu_count_code($count);
		
		$added_main_menu = false;
		
		//Add all the subitems
		foreach ($this->modules as $key => $x_module) {
			$module =& $this->modules[$key];
			
			//Show a module on the menu only if it provides a menu title, it belongs in the current admin scope (blog/network/user), and it doesn't have an enabled parent module
			if ($module->get_menu_title()
					&& $module->belongs_in_admin($admin_scope)
					&& (!$module->get_parent_module() || !$this->module_exists($module->get_parent_module()))
					) {
				
				//If the module is hidden, put the module under a non-existent menu parent
				//(this will let the module's admin page be loaded, but it won't show up on the menu)
				if (empty($psdata['modules']) || $psdata['modules'][$key] > WSW_MODULE_HIDDEN)
					$parent = $module->get_menu_parent();
				else
					$parent = 'su-hidden-modules';
				
				if (empty($psdata['modules']) || $psdata['modules'][$key] > WSW_MODULE_SILENCED)
					$count_code = $this->get_menu_count_code($module->get_menu_count());
				else
					$count_code = '';
				
				$hook = $this->key_to_hook($key);
				
				if ($parent == 'seo' && !$added_main_menu) {

					//Translations and count codes will mess up the admin page hook, so we need to fix it manually.
					global $admin_page_hooks;
					$admin_page_hooks['seo'] = 'seo';
					
					$added_main_menu = true;
				}
				
				add_submenu_page('wsw_dashboard_page', $module->get_page_title(), $module->get_menu_title().$count_code,
					'manage_options', $hook, array($module, 'admin_page'));
				
				//Support for the "Ozh' Admin Drop Down Menu" plugin
				//add_filter("ozh_adminmenu_icon_$hook", array(&$this, 'get_admin_menu_icon_url'));
			}
		}
	}
	
	/**
	 * @since 7.2.5
	 */
	function add_blog_admin_menus() {
		$this->add_menus('blog');
	}
	
	/**
	 * @since 7.2.5
	 */
/*	function add_network_admin_menus() {
		$this->add_menus('network');
	}*/
	
	/**
	 * Compares two modules to determine which of the two should be displayed first on the menu.
	 * Sorts by menu position first, and title second.
	 * Works as a uasort() callback.
	 * 
	 * @since 0.1
	 * @uses WSW_Module::get_menu_pos()
	 * @uses WSW_Module::get_menu_title()
	 * 
	 * @param WSW_Module $a The first module to compare.
	 * @param WSW_Module $b The second module to compare.
	 * @return int This will be -1 if $a comes first, or 1 if $b comes first.
	 */
	function module_sort_callback($a, $b) {
		if ($a->get_menu_pos() == $b->get_menu_pos()) {
			return strcmp($a->get_menu_title(), $b->get_menu_title());
		}
		
		return ($a->get_menu_pos() < $b->get_menu_pos()) ? -1 : 1;
	}
	
	/**
	 * If the bubble alert count parameter is greater than zero, then returns the HTML code for a numeric bubble to display next to a menu item.
	 * Otherwise, returns an empty string.
	 * 
	 * @since 0.1
	 * 
	 * @param int $count The number that should appear in the bubble.
	 * @return string The string that should be added to the end of the menu item title.
	 */
	function get_menu_count_code($count) {
	
		//If we have alerts that need a bubble, then return the bubble HTML.
		if ($count > 0)
			return "<span class='update-plugins count-$count'><span class='plugin-count'>".number_format_i18n($count)."</span></span>";
		else
			return '';
	}
	
	/**
	 * Converts a module key to a menu hook.
	 * (Makes the "Module Manager" module load when the "SEO" parent item is clicked.)
	 * 
	 * @since 0.1
	 * 
	 * @param string $key The module key.
	 * @return string The menu hook.
	 */
	function key_to_hook($key) {
		switch ($key) {
			case $this->default_menu_module: return 'seo'; break;
			case 'settings': return 'seo-wizard'; break;
			default: return "su-$key"; break;
		}
	}
	
	/**
	 * Converts a menu hook to a module key.
	 * (If the "SEO" parent item is clicked, then the Module Manager is being shown.)
	 * 
	 * @since 0.1
	 * 
	 * @param string $hook The menu hook.
	 * @return string The module key.
	 */
	function hook_to_key($hook) {
		switch ($hook) {
			case 'seo': return $this->default_menu_module; break;
			case 'seo-wizard': return 'settings'; break;
			default: return substr($hook, 3); break;
		}
	}
	
	/**
	 * Returns the icon for one of the plugin's admin menu items.
	 * Used to provide support for the Ozh' Admin Drop Down Menu plugin.
	 * 
	 * @since 1.0
	 * 
	 * @param string $hook The menu item for which an icon is needed.
	 * @return string The absolute URL of the menu icon.
	 */
	function get_admin_menu_icon_url($hook) {
		$key = $this->hook_to_key($hook);
		if (isset($this->modules[$key])) {
			if (strlen($image = $this->modules[$key]->get_menu_icon_filename()))
				return $this->modules[$key]->module_dir_url.$image;
		}
		
		return $hook;
	}
	
	
	/********** OTHER ADMIN FUNCTIONS **********/
	
	/**
	 * Returns a boolean indicating whether the user is currently viewing an admin page generated by this plugin.
	 * 
	 * @since 0.1
	 * 
	 * @return bool Whether the user is currently viewing an admin page generated by this plugin.
	 */
	function is_plugin_admin_page() {
		if (is_admin()) {
			global $plugin_page;
			
			foreach ($this->modules as $key => $module) {
				if (strcmp($plugin_page, $this->key_to_hook($key)) == 0) return true;
			}
		}
		
		return false;
	}	
	 
	/**
	* Tests if the SDF Theme is the active theme
	* @since 7.6.3
	* @return bool
	*/
	public function is_sdf_active() {
		$current_theme = wp_get_theme();
		$required_theme = wp_get_theme( 'seodesign' );
		 
		// If SDF is installed and current theme is a SDF child theme or SDF is active theme
		if ( $required_theme->exists() && ( ( $this->is_sdf_child() ) || ( 'seodesign' == $current_theme ) ) ) {
			return true;
		}
		return false;
	}
	 
	/**
	* Tests if current theme is a child theme of SDF
	* @since 7.6.3
	* @return bool
	*/
	public function is_sdf_child() {
		$current_theme = wp_get_theme();
		if ( 'seodesign' !== $current_theme->get_template() ) {
			return false;
		}
		return true;
	}
	 
	/**
	* Tests if required version of SDF is available
	* @since 7.6.3
	* @return bool
	*/
	public function is_sdf_version_compatible() {
		$version_required = '1.0.0';
		$theme = wp_get_theme( 'seodesign' );
		$version_installed = $theme->Version;
		if ( version_compare( $version_installed, $version_required, '<' ) ) {
			return false;
		}
		return true;
	}
	
	/**
	 * Includes the plugin's CSS and JavaScript in the header.
	 * Also includes a module's CSS/JavaScript on its administration page.
	 * 
	 * @todo Link to global plugin includes only when on plugin pages.
	 * 
	 * @since 0.1
	 * @uses $modules
	 * @uses $plugin_file_url
	 * @uses $plugin_dir_url
	 * @uses hook_to_key()
	 */
	function admin_includes() {
		
		
		//Global CSS/JS
		$this->queue_css('plugin', 'global');
		$this->queue_js('plugin', 'global');
			
		//SDF Styling
		if ($this->is_plugin_admin_page()) {
			$sdf_ads_localized_data = array(
				'sdf_banners_url' => $this->plugin_dir_url.'modules/sdf-ads/banners/'
			);
			$this->queue_js('modules/sdf-ads', 'sdf-ads', '', $sdf_ads_localized_data);
			$this->queue_css('modules/sdf-ads', 'sdf-ads');
			// white background
			$this->queue_css('plugin/sdf', 'sdf.wp');
			// overwrite submenu on wp 3.8+ with "light" color style
			global $_wp_admin_css_colors, $wp_version;
			if ( $wp_version >= 3.8 ) {
				$color_scheme = get_user_option( 'admin_color' );	
				if ( empty( $_wp_admin_css_colors[ $color_scheme ] ) ) {
					$color_scheme = 'light';
				}
				if ( 'light' == $color_scheme ) {
					$this->queue_css('plugin/sdf', 'sdf.wp-color');
				}
			}
		}
		
		//load if SDF is not active
		global $pagenow;
		$current = (isset($_GET['page'])) ? $_GET['page'] : '';
		$post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : '';
		$pages = array( 'edit.php' );
		$sdf_admin_pages = array('sdf','sdf-settings','sdf-silo','sdf-silo-manual-builder','sdf-header','sdf-layout','sdf-shortcode','sdf-styles','revslider','sdf-footer','seo', 'su-fofs', 'su-misc', 'su-user-code', 'su-autolinks', 'su-files', 'su-internal-link-aliases', 'su-meta-descriptions', 'su-meta-keywords', 'su-meta-robots', 'su-opengraph', 'seo-wizard', 'su-wp-settings', 'su-titles', 'su-sds-blog');
		if( in_array( $current, $sdf_admin_pages) || in_array( $pagenow, $pages ) || in_array( $pagenow, array( 'post.php', 'post-new.php' )) ) {
			// admin styles
			wp_register_style('sdf-bootstrap-admin', $this->plugin_dir_url.'plugin/sdf/bootstrap/css/bootstrap.admin.css', array(), null, 'screen');
			wp_register_style('sdf-bootstrap-admin-theme', $this->plugin_dir_url.'plugin/sdf/bootstrap/css/bootstrap-theme.admin.css', array(), null, 'screen');		
			wp_register_style('sdf-font-awesome', 'https://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), null, 'screen');
			wp_enqueue_style('sdf-bootstrap-admin');
			wp_enqueue_style('sdf-bootstrap-admin-theme');
			wp_enqueue_style('sdf-font-awesome');
		
			wp_register_script('sdf_bs_js_admin', $this->plugin_dir_url.'plugin/sdf/bootstrap/js/bootstrap.js', array('jquery'), null, true);	
			wp_register_script('media_upload_js', $this->plugin_dir_url.'plugin/sdf/sdf.media.upload.js', array('jquery'), '');
			wp_enqueue_script('sdf_bs_js_admin');
			wp_enqueue_script('media_upload_js');
			wp_enqueue_media();
		}
		
		wp_register_style('seo-css-admin',  $this->plugin_dir_url.'plugin/seo.admin.css', array(), null, 'screen');
		wp_enqueue_style('seo-css-admin');
		
		// load dashboard widget
		$sdf_ads_localized_data = array(
			'sdf_banners_url' => $this->plugin_dir_url.'modules/sdf-ads/banners/'
		);
		if($pagenow == 'index.php') $this->queue_js('modules/sdf-ads', 'sdf-ads', '', $sdf_ads_localized_data);
		
		//Figure out what plugin admin page we're on
		global $plugin_page;
		$pp = $this->hook_to_key($plugin_page);
		
		if (strlen($pp)) {
			$outputted_module_files = false;
			
			foreach ($this->modules as $key => $module) {
				
				//Does the current admin page belong to this module?
				if (strcmp($key, $pp) == 0)
					//Output AJAX page var fix
					echo "\n<script type='text/javascript'>pagenow = '".WSW_esc_attr($module->plugin_page_hook)."';</script>\n";
				
				//Does the current admin page belong to this module or its parent?
				if (strcmp($key, $pp) == 0 || strcmp($module->get_parent_module(), $pp) == 0) {
					
					//We're viewing a module page, so print links to the CSS/JavaScript files loaded for all modules
					if (!$outputted_module_files) {
						$this->queue_css('modules', 'modules');
						$this->queue_js ('modules', 'modules', array('jquery'), array(
							'unloadConfirmMessage' => __("It looks like you made changes to the settings of this SEO Wizard module. If you leave before saving, those changes will be lost.", 'seo-wizard')
						));
						$outputted_module_files = true;
					}
					
					//Print links to the module's CSS and JavaScript.
					$this->queue_css($module->module_dir_rel_url, $module->get_module_key());
					$this->queue_js ($module->module_dir_rel_url, $module->get_module_key());
					
					//Queue up the module's columns, if any
					if (count($columns = $module->get_admin_table_columns()))
						register_column_headers($module->plugin_page_hook, $columns);
				}
			}
		}
	}
	
	/**
	 * Output an HTML <link> to a CSS file if the CSS file exists.
	 * Includes a version-based query string parameter to prevent caching old versions.
	 * 
	 * @since 2.1
	 * @uses $plugin_dir_path
	 * @uses $plugin_dir_url
	 * @uses WSW_VERSION
	 * 
	 * @param string $relurl The URL to the CSS file, relative to the plugin directory.
	 */
	function queue_css($reldir, $filename) {
		//$this->queue_file($reldir, $filename, '.css', 'wp_enqueue_style');
	}
	
	/**
	 * Output an HTML <script> tag if the corresponding JavaScript file exists.
	 * Includes a version-based query string parameter to prevent caching old versions.
	 * 
	 * @since 2.1
	 * @uses $plugin_dir_path
	 * @uses $plugin_dir_url
	 * @uses WSW_VERSION
	 * 
	 * @param string $relurl The URL to the JavaScript file, relative to the plugin directory.
	 */
	function queue_js($reldir, $filename, $deps=array(), $l10n=array()) {
	//	$this->queue_file($reldir, $filename, '.js', 'wp_enqueue_script', $deps, $l10n);
	}
	
	/**
	 * Queues a CSS/JS file with WordPress if the file exists.
	 * 
	 * @since 2.1
	 */

	
	/**
	 * Replaces WordPress's default contextual help with postmeta help when appropriate.
	 * 
	 * @since 0.1
	 * @uses $modules
	 * 
	 * @param string $text WordPress's default contextual help.
	 * @param string $screen The screen currently being shown.
	 * @return string The contextual help content that should be shown.
	 */
	function admin_help() {
		
		$screen = get_current_screen();
		if ('post' != $screen->base) //WP_Screen->base added in WP 3.3
			return;
		
		//Gather post meta help content
		$helparray = apply_filters('WSW_postmeta_help', array());
		
		if ($helparray) {
		
			$customhelp = '';
			foreach ($helparray as $line) {
				$customhelp .= "<p>$line</p>\n";
			}
			
			//WP_Screen->add_help_tab added in WP 3.3
			$screen->add_help_tab(array(
				  'id' => 'seo-wizard-post-meta-help'
				, 'title' => __('SEO Settings', 'seo-wizard')
				, 'content' => "<div class='su-help'>\n$customhelp\n</div>\n"
			));
		}
	}
	

	function plugin_page_notices() {
		
		global $pagenow;
		
		if ($pagenow == 'plugins.php') {
		
			$r_plugins = array(
				  'wordpress-seo/wp-seo.php'
			);
			
			$i_plugins = get_plugins();
			
			foreach ($r_plugins as $path) {
				if (isset($i_plugins[$path]))
					add_action("after_plugin_row_$path", array(&$this, 'plugin_page_notice'), 10, 3);
			}
		}
	}

	function plugin_page_notice($file, $data, $context) {
		if (is_plugin_active($file)) {
			echo "<tr class='plugin-update-tr su-plugin-notice'><td colspan='3' class='plugin-update colspanchange'><div class='update-message'>\n";
			printf(__('%1$s is known to cause conflicts with SEO Wizard. Please deactivate %1$s if you wish to continue using SEO Wizard.', 'seo-wizard'), $data['Name']);
			echo "</div></td></tr>\n";
		}
	}
	
	/**
	 * Displays new-version info in this plugin's update row on WordPress's plugin admin page.
	 * Hooked into WordPress's in_plugin_update_message-(file) action.
	 * 
	 * @since 0.1
	 * 
	 * @param array $plugin_data An array of this plugin's information. Unused.
	 * @param obejct $r The response object from the WordPress Plugin Directory.
	 */
	function plugin_update_info($plugin_data, $r) {
		//If a new version is available...
		if ($r && $r->new_version && !is_plugin_active('changelogger/changelogger.php'))
			//If info on the new version is available...
			if ($info = $this->get_plugin_update_info($r->new_version))
				//Output the new-version info
				echo "<span class='su-plugin-update-info'><br />$info</span>";
	}
	
	/**
	 * Loads new-version info and returns it as a string.
	 * 
	 * @since 2.1
	 * 
	 * @return string
	 */
	function get_plugin_update_info($nv) {
		
		$change_types = array(
			  'New Module' => 'module'
			, 'Feature' => 'feature'
			, 'SEO Feature' => 'feature'
			, 'Bugfix' => 'bugfix'
			, 'Improvement' => 'improvement'
			, 'Security Fix' => 'security'
			, 'New Translation' => 'new-lang'
			, 'Updated Translation' => 'updated-lang'
		);
		
		$change_labels = array(
			  'module'		=> array(__('new module', 'seo-wizard'), __('new modules', 'seo-wizard'))
			, 'feature'     => array(__('new feature', 'seo-wizard'), __('new features', 'seo-wizard'))
			, 'bugfix'      => array(__('bugfix', 'seo-wizard'), __('bugfixes', 'seo-wizard'))
			, 'improvement' => array(__('improvement', 'seo-wizard'), __('improvements', 'seo-wizard'))
			, 'security'    => array(__('security fix', 'seo-wizard'), __('security fixes', 'seo-wizard'))
			, 'new-lang'    => array(__('new language pack', 'seo-wizard'), __('new language packs', 'seo-wizard'))
			, 'updated-lang'=> array(__('language pack update', 'seo-wizard'), __('language pack updates', 'seo-wizard'))
		);
		
		$changes = array_fill_keys($change_types, 0);
		
		$versions = $this->download_changelog();
		if (!is_array($versions) || !count($versions)) return '';
		
		foreach ($versions as $version_title => $version_changelog) {
			if (preg_match('|Version ([0-9.]{3,9}) |', $version_title, $matches)) {
				$version = $matches[1];
				
				//If we're running the same version or a newer version, continue
				if (version_compare(WSW_VERSION, $version, '>=')) continue;
				
				$version_changes = explode('</li>', $version_changelog);
				foreach ($version_changes as $change) {
					if (preg_match('|<li>([a-zA-Z ]+): |', $change, $matches2)) {
						$change_type_label = $matches2[1];
						if (isset($change_types[$change_type_label]))
							$changes[$change_types[$change_type_label]]++;
					}
				}
			}
		}
		
		if (!count($changes)) return '';
		
		$nlchanges = array();
		foreach ($changes as $change_type => $changes_count) {
			if (is_string($change_type) && $changes_count > 0)
				$nlchanges[] = sprintf(__('%d %s', 'seo-wizard'),
									number_format_i18n($changes_count),
									_n($change_labels[$change_type][0], $change_labels[$change_type][1], $changes_count, 'seo-wizard')
								);
		}
		
		return sprintf(__('Upgrade now to get %s. %s.', 'seo-wizard')
					, sustr::nl_implode($nlchanges)
					, '<a href="plugin-install.php?tab=plugin-information&amp;plugin=seo-wizard&amp;section=changelog&amp;TB_iframe=true&amp;width=640&amp;height=530" class="thickbox">' . __('View changelog', 'seo-wizard') . '</a>'
				);
	}
	
	/**
	 * Downloads the plugin's changelog.
	 * 
	 * @since 3.1
	 * 
	 * @return array An array of changelog headers {Version X.X (Month Day, Year)} => <ul> lists of changes.
	 */
	function download_changelog() {
		
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		
		$plugin = plugins_api('plugin_information', array('slug' => 'seo-wizard'));
		if (is_wp_error($plugin)) return false;
		$changelog = $plugin->sections['changelog'];
		
		$entries = explode('<h4>', $changelog);
		$versions = array();
		foreach ($entries as $entry) {
			$item = explode('</h4>', $entry, 2);
			if (count($item) == 2) $versions[$item[0]] = $item[1];
		}
		
		return $versions;
	}
	

	function add_plugin_upgrade_notice($current) {
		static $info;
		if (isset($current->response[$this->plugin_basename])) {
			if (!strlen($current->response[$this->plugin_basename]->upgrade_notice)) {
				if (!$info)
					$info = $this->get_plugin_update_info($current->response[$this->plugin_basename]->new_version);
				$current->response[$this->plugin_basename]->upgrade_notice = $info;
			}
		}
		return $current;
	}
	

	function plugin_action_links($actions) {
		$WSW_actions = array(
			  'uninstall' => __('Uninstall', 'seo-wizard')
		);
		
		foreach ($WSW_actions as $module => $anchor) {
			if ($this->module_exists($module) && $url = $this->modules[$module]->get_admin_url()) {
				$actions[] = "<a href='$url'>$anchor</a>";
			}
		}
		
		return $actions;
	}
	

	function plugin_row_meta_filter($plugin_meta, $plugin_file) {
		if ($plugin_file == $this->plugin_basename) {
			
			if (is_blog_admin())
				$title = __('Added Modules: ', 'seo-wizard');
			else
				$title = '';
			
			echo $this->get_module_links_list('<p id="seo-active-modules-list">'.$title, ' | ', '</p>');
		}
		
		return $plugin_meta;
	}
	
	/**
	 * Returns a list of links to active, independent modules.
	 * 
	 * @since 2.1
	 */
	function get_module_links_list($before = '', $between = ' | ', $after = '') {
		
		$list = '';
		
		if (count($this->modules)) {
			
			$modules = array();
			foreach ($this->modules as $key => $x_module) {
				$module =& $this->modules[$key];
				if (strcasecmp(get_parent_class($module), 'WSW_Module') == 0 && $module->is_independent_module()) {
					if ($url = $module->get_admin_url())
						$modules[$module->get_module_title()] = $url;
				}
			}
			
			ksort($modules);
			
			$list = $before;
			$first = true;
			foreach ($modules as $title => $url) {
				$url = WSW_esc_attr($url);
				$title = str_replace(' ', '&nbsp;', WSW_esc_html($title));
				if ($first) $first = false; else $list .= $between;
				$list .= "<a href='$url'>$title</a>";
			}
			$list .= $after;
		}
		
		return $list;
	}
	
	/**
	 * Removes the activation notices of All in One SEO Pack and Akismet from our admin pages.
	 * (It could be confusing for users to see another plugin's notices on our plugin's pages.)
	 * 
	 * @since 1.1
	 */
	function remove_admin_notices() {
		if ($this->is_plugin_admin_page()) {
			remove_action('admin_notices', 'aioseop_activation_notice');
			remove_action('admin_notices', 'akismet_warning');
		}
	}
	

	
	/********** MODULE FUNCTIONS ***********/
	
	/**
	 * Checks to see whether an instantiation of the specified module exists (i.e. whether the module is non-disabled).
	 * 
	 * @since 1.5
	 * 
	 * @param string $key The key of the module to check.
	 * @return boolean Whether the module is enabled (or silent or hidden).
	 */
	function module_exists($key) {
		return isset($this->modules[$key]);
	}
	
	/**
	 * Calls the function of a module.
	 * 
	 * @since 1.5
	 * 
	 * @param string $key The key of the module to which the function belongs.
	 * @param string $function The name of the function to call.
	 * @param mixed $result Passed by reference. Set to the result of the function.
	 * @return boolean Whether or not the function existed.
	 */
	function call_module_func($key, $function, &$result = null, $call_even_if_disabled=true) {
		
		//Wipe passed-by-reference variable clean
		$result = null;
		
		$args = func_get_args();
		$args = array_slice($args, 3);
		
		if (isset($this->modules[$key]))
			$obj =& $this->modules[$key];
		elseif (isset($this->disabled_modules[$key]) && $call_even_if_disabled)
			$obj = $this->disabled_modules[$key];
		else
			return false;
		
		if (is_callable($call = array($obj, $function))) {
			$result = call_user_func_array($call, $args);
			return true;
		}
		
		return false;
	}
	
	/**
	 * @since 7.6
	 */
	function get_module_var($key, $var, $default) {
		
		if (isset($this->modules[$key]) && property_exists($this->modules[$key], $var))
			return $this->modules[$key]->$var;
		
		return $default;
	}
	
	/**
	 * @since 6.4
	 */
	function set_module_var($key, $var, $value) {
		
		if (isset($this->modules[$key]) && property_exists($this->modules[$key], $var)) {
			$this->modules[$key]->$var = $value;
			return true;
		}
		return false;
	}
	
	/********** ADMIN POST META BOX FUNCTIONS **********/
	
	/**
	 * @since 7.3
	 */
	function get_postmeta_tabs() {
		return array(
			  'serp' => __('Search Engine Listing', 'seo-wizard')
			, 'opengraph' => __('Social Networks Listing', 'seo-wizard')
			, 'links' => __('Links', 'seo-wizard')
			, 'misc' => __('Miscellaneous', 'seo-wizard')
		);
	}
	
	/**
	 * Compiles the post meta box field array based on data provided by the modules.
	 * 
	 * @since 0.8
	 * @uses WSW_Module::postmeta_fields()
	 * 
	 * @param string $screen The admin screen currently being viewed (post, page).
	 * @return array An array structured like this: $data[tab ID][position #][field name] = HTML
	 */
	function get_postmeta_array($screen) {
		
		static $return = array();
		if (!empty($return[$screen]))
			return $return[$screen];
		
		$tabs = $this->get_postmeta_tabs();
		
		$module_fields = array();
		$fields = array();
		
		foreach ($this->modules as $key => $module) {
			
			$module_fields = $this->modules[$key]->postmeta_fields(array(), $screen);
			
			foreach ($module_fields as $tab => $tab_fields) {
				if (isset($tabs[$tab])) {
					if (!isset($fields[$tab])) $fields[$tab] = array();
					$fields[$tab] += $tab_fields;
				} else { //Backcompat
					if (strpos($tab, '|') === false) {
						if (!isset($fields['misc'][$tab])) $fields['misc'][$tab] = array();
						$fields['misc'][$tab] += $tab_fields;
					} else {
						list($pos, $keys) = explode('|', $tab, 2);
						$fields['misc'][$pos][$keys] = $tab_fields;
					}
				}
			}
		}
		
		foreach ($fields as $tab => $tab_poses) {
			ksort($fields[$tab]);
		}
		
		$return[$screen] = $fields;
		
		return $fields;
	}
	
	/**
	 * If we have post meta fields to display, then register our meta box with WordPress.
	 * 
	 * @since 0.1
	 * @uses get_postmeta_array()
	 */
	function add_postmeta_box() {
		
		//Add the metabox to posts and pages.
		$posttypes = get_post_types(array('public' => true), 'names');
		foreach ($posttypes as $screen) {
			
			if (strpos($screen, '"') !== false)
				continue;
			
			//Only show the meta box if there are fields to show.
			//if ($this->get_postmeta_array($screen))
			//	add_meta_box('WSW_postmeta', __('Link Settings', 'seo-update'), create_function('', 'global $seo_update; $seo_update->show_postmeta_box("'.$screen.'");'), $screen, 'normal', 'high');
		}
	}
	
	/**
	 * Displays the inner contents of the post meta box.
	 * 
	 * @since 0.1
	 * @uses get_postmeta_array()
	 * 
	 * @param string $screen The admin screen currently being viewed (post, page).
	 */
	function show_postmeta_box($screen) {
		
		//Begin box
		echo "<div id='su-postmeta-box' class='sdf-admin'>\n";
		wp_nonce_field('su-update-postmeta', '_WSW_wpnonce');
		
		//Output postmeta tabs
		$data = $this->get_postmeta_array($screen);
		$_tabs = $this->get_postmeta_tabs();
		$tabs = array();
		foreach ($_tabs as $tab_id => $tab_title) {
			if (isset($data[$tab_id]))
				$tabs[] = array('title' => $tab_title, 'id' => $tab_id, 'callback' => array('postmeta_tab', $tab_id, $screen));
		}
		$this->tabs($tabs);
		
		//Meta box footer
		if ( !$this->is_sdf_active() ) {
			echo '<p class="su-postmeta-box-footer">';
			printf(__('%1$s %2$s by %3$s', 'seo-wizard'),
				'<a href="'.WSW_PLUGIN_URI.'" target="_blank">'.__(WSW_PLUGIN_NAME, 'seo-wizard').'</a>',
				WSW_VERSION,
				'<a href="'.WSW_AUTHOR_URI.'" target="_blank">'.__(WSW_AUTHOR, 'seo-wizard').'</a>'
			);
			echo '</p>';
		}
		
		//End box
		echo "</div>\n";
	}
	
	/**
	 * @since 7.3
	 */
	function postmeta_tab($tab, $screen) {
		echo "\n<table>\n";
		
		$data = $this->get_postmeta_array($screen);
		foreach ($data[$tab] as $tab_pos) {
			foreach ($tab_pos as $pos_field) {
				echo $pos_field;
			}
		}
		
		echo "\n</table>\n";
	}
	
	/**
	 * Saves the values of the fields in the post meta box.
	 * 
	 * @since 0.1
	 * 
	 * @param int $post_id The ID of the post being saved.
	 * @param object $post The post being saved.
	 */
	function save_postmeta_box($post_id, $post) {
		
		//Sanitize
		$post_id = (int)$post_id;
		
		//Run preliminary permissions checks
		if ( !isset($_REQUEST['_WSW_wpnonce']) || !wp_verify_nonce($_REQUEST['_WSW_wpnonce'], 'su-update-postmeta') ) return;
		$post_type = isset($_POST['post_type']) ? $_POST['post_type'] : 'post';
		$post_type_object = get_post_type_object($post_type);
		if (!current_user_can($post_type_object->cap->edit_posts)) return;
		
		//Get an array of the postmeta fields
		$data = $this->get_postmeta_array($post_type);
		foreach ($data as $tab => $tab_poses) {
			foreach ($tab_poses as $tab_pos) {
				foreach ($tab_pos as $fields => $html) {
					$fields = explode('|', $fields);
					foreach ($fields as $field) {
						$metakey = "_WSW_$field";
						
						$value = isset($_POST[$metakey]) ? stripslashes_deep($_POST[$metakey]) : '';
						if (!apply_filters("WSW_custom_update_postmeta-$field", false, $value, $metakey, $post)) {
							if (empty($value))
								//Delete the old value
								delete_post_meta($post_id, $metakey);
							else
								//Add the new value
								update_post_meta($post_id, $metakey, $value);
						}
					}
				}
			}
		}
	}
	
	/**
	 * @since 7.3
	 */
	function postmeta_box_tabs_init() {
		wp_enqueue_script('jquery-ui-tabs');
	}
	
	
	/********** CRON FUNCTION **********/
	
	/**
	 * Can remove cron jobs for modules that no longer exist, or remove all cron jobs.
	 * 
	 * @since 0.1
	 * 
	 * @param bool $remove_all Whether to remove all cron jobs. Optional.
	 */
	function remove_cron_jobs($remove_all = false) {
		
		$psdata = (array)get_option('seo_update', array());
		
		if (isset($psdata['cron']) && is_array($psdata['cron'])) {
			$newcrondata = $crondata = $psdata['cron'];
			
			foreach ($crondata as $key => $crons) {
				if ($remove_all || !isset($this->modules[$key])) {
					foreach ($crons as $data) { wp_clear_scheduled_hook($data[0]); }
					unset($newcrondata[$key]);
				}
			}
			
			$psdata['cron'] = $newcrondata;
			
			update_option('seo_update', $psdata);
		}
	}
	
	
	/********** TEMPLATE OUTPUT FUNCTION **********/
	
	/**
	 * Outputs code into the template's <head> tag.
	 * 
	 * @since 0.1
	 */
	function template_head() {
        //Let modules output head code.
		do_action('WSW_head');
		
		//Make sure the blog is public. Telling robots what to do is a moot point if they aren't even seeing the blog.
		if (get_option('blog_public')) {
			$robots = implode(',', apply_filters('WSW_meta_robots', array()));
			$robots = WSW_esc_attr($robots);
			if ($robots) echo "\t<meta name=\"robots\" content=\"$robots\" />\n";
		}
		

	}
	

	function mark_code($code, $info = '', $info_only = false) {
		
		if (!strlen($code)) return '';
		
		if ($this->get_setting('mark_code', false, 'settings')) {
		
			if ($info_only)
				$start = $end = $info;
			else {
				if ($info) $info = " - $info";
				$start = sprintf('%s (%s)%s', WSW_PLUGIN_NAME, WSW_PLUGIN_URI, $info);
				$end = WSW_PLUGIN_NAME;
			}
			
			return "\n<!-- $start -->\n$code\n<!-- /$end -->\n\n";
		}
		return $code;
	}
	
	
	/********** README FUNCTIONS **********/
	
	/**
	 * Returns the full server path to the main readme.txt file.
	 * 
	 * @since 1.5
	 * @return string
	 */
	function get_readme_path() {
		return $this->plugin_dir_path.'readme.txt';
	}
	
	/********** JLSUGGEST **********/
	
	/**
	 * Outputs a JSON-encoded list of posts and terms on the blog.
	 * 
	 * @since 6.0
	 */
	function jlsuggest_autocomplete() {
		
		if ( !function_exists('json_encode') ) die();
		if ( !current_user_can( 'manage_options' ) ) die();
		
		$items = array();
		
		$include = empty($_GET['types']) ? array() : explode(',', $_GET['types']);
		
		if ((!$include || in_array('home', $include)) && sustr::ihas($_GET['q'], 'home')) {
			$items[] = array('text' => __('Home', 'seo-wizard'), 'isheader' => true);
			$items[] = array('text' => __('Blog Homepage', 'seo-wizard'), 'value' => 'obj_home', 'selectedtext' => __('Blog Homepage', 'seo-wizard'));
		}
		
		
		$posttypeobjs = get_post_types(array('public' => true), 'objects');
		foreach ($posttypeobjs as $posttypeobj) {
			
			if ($include && !in_array('posttype', $include) && !in_array('posttype_' . $posttypeobj->name, $include))
				continue;
			
			$stati = get_available_post_statuses($posttypeobj->name);
			suarr::remove_value($stati, 'auto-draft');
			$stati = implode(',', $stati);
			
			$posts = get_posts(array(
				  'orderby' => 'title'
				, 'order' => 'ASC'
				, 'post_status' => $stati
				, 'numberposts' => -1
				, 'post_type' => $posttypeobj->name
				, 'post_mime_type' => isset($_GET['post_mime_type']) ? $_GET['post_mime_type'] : ''
				, 'sentence' => 1
				, 's' => $_GET['q']
			));
			
			if (count($posts)) {
				
				$items[] = array('text' => $posttypeobj->labels->name, 'isheader' => true);
				
				foreach ($posts as $post)
					$items[] = array(
						  'text' => $post->post_title
						, 'value' => 'obj_posttype_' . $posttypeobj->name . '/' . $post->ID
						, 'selectedtext' => $post->post_title . '<span class="type">&nbsp;&mdash;&nbsp;'.$posttypeobj->labels->singular_name.'</span>'
					);
			}
		}
		
		$taxonomyobjs = suwp::get_taxonomies();
		foreach ($taxonomyobjs as $taxonomyobj) {
			
			if ($include && !in_array('taxonomy', $include) && !in_array('taxonomy_' . $posttypeobj->name, $include))
				continue;
			
			$terms = get_terms($taxonomyobj->name, array(
				'search' => $_GET['q']
			));
			
			if (count($terms)) {
				
				$items[] = array('text' => $taxonomyobj->labels->name, 'isheader' => true);
				
				foreach ($terms as $term)
					$items[] = array(
						  'text' => $term->name
						, 'value' => 'obj_taxonomy_' . $taxonomyobj->name . '/' . $term->term_id
						, 'selectedtext' => $term->name . '<span class="type"> &mdash; '.$taxonomyobj->labels->singular_name.'</span>'
					);
			}
		}
		
		if (!$include || in_array('author', $include)) {
			
			$authors = get_users(array(
				  'search' => $_GET['q']
				, 'fields' => array('ID', 'user_login')
			));
			
			if (count($authors)) {
				
				$items[] = array('text' => __('Author Archives', 'seo-wizard'), 'isheader' => true);
				
				foreach ($authors as $author)
					$items[] = array(
						  'text' => $author->user_login
						, 'value' => 'obj_author/' . $author->ID
						, 'selectedtext' => $author->user_login . '<span class="type"> &mdash; '.__('Author', 'seo-wizard').'</span>'
					);
			}
		}
		
		if ($this->module_exists('internal-link-aliases') && (!$include || in_array('internal-link-alias', $include))) {
			
			$aliases = $this->get_setting('aliases', array(), 'internal-link-aliases');
			$alias_dir = $this->get_setting('alias_dir', 'go', 'internal-link-aliases');
			
			if (is_array($aliases) && count($aliases)) {
				
				$header_outputted = false;
				foreach ($aliases as $alias_id => $alias) {
					
					if ($alias['to']) {
						
						$h_alias_to = WSW_esc_html($alias['to']);
						$to_rel_url = "/$alias_dir/$h_alias_to/";
						
						if ((strpos($alias['from'], $_GET['q']) !== false) || (strpos($to_rel_url, $_GET['q']) !== false)) {
							
							if (!$header_outputted) {
								$items[] = array('text' => __('Link Masks', 'seo-update'), 'isheader' => true);
								$header_outputted = true;
							}
							
							$items[] = array(
								  'text' => $to_rel_url
								, 'value' => 'obj_internal-link-alias/' . $alias_id
								, 'selectedtext' => $to_rel_url . '<span class="type"> &mdash; '.__('Link Mask', 'seo-update').'</span>'
							);
							
						}
					}
				}
			}
		}
		
		echo json_encode($items);
		die();
	}
	
	/********** TABS **********/
	
	function tabs($tabs=array(), $table=false, &$callback=null) {
		
		if ($callback == null)
			$callback = $this;
		
		if ($c = count($tabs)) {
			
			if ($c >= 1) {
				echo "\n\n<div class='seo-meta-wrap'>\n";
				echo "\n\n<ul class='nav nav-tabs' id='su-tabset'>\n";
			}
			
			foreach ($tabs as $tab) {
				
				if (isset($tab['title']))	$title	  = $tab['title'];	  else return;
				if (isset($tab['id']))		$id		  = $tab['id'];		  else return;
				if (isset($tab['callback']))$function = $tab['callback']; else return;
				
				if ($c >= 1) {
					$active = ( $tab === reset($tabs) ) ? " class='active'" : "";
					//$id = 'su-' . sustr::preg_filter('a-z0-9', strtolower($title));
					echo "<li$active><a href='#$id' data-toggle='tab'>$title</a></li>\n";
				}
			}
			
			if ($c >= 1) {
				echo "</ul>\n";
				echo "<div class='tab-content'>\n";
			}
			
			foreach ($tabs as $tab) {
				
				if (isset($tab['title']))	$title	  = $tab['title'];	  else return;
				if (isset($tab['id']))		$id		  = $tab['id'];		  else return;
				if (isset($tab['callback']))$function = $tab['callback']; else return;
				
				if ($c >= 1) {
					$active = ( $tab === reset($tabs) ) ? ' in active' : '';
					echo "<div class='tab-pane fade$active su-tab-contents' id='$id'>\n";
					echo "<div class='wpu-group'>\n";
					echo "<span class='wpu-meta-title'>$title</span>\n";
				}
				
				$call = $args = array();
				
				if (is_array($function)) {
					
					if (is_array($function[0])) {
						$call = array_shift($function);
						$args = $function;
					} elseif (is_string($function[0])) {
						$call = array_shift($function);
						$call = array($callback, $call);
						$args = $function;
					} else {
						$call = $function;
					}
				} else {
					$call = array($callback, $function);
				}
				if (is_callable($call)) call_user_func_array($call, $args);
				
				if ($c >= 1)
					echo "</div>\n";
					echo "</div>\n";
			}
			
			if ($c >= 1) {
				echo "\n\n</div>\n";
				echo "\n\n</div>\n";
              echo  '<p><a href="http://www.seowizard.org/c-seo-wizard" target="_blank"><img src="'.$this->plugin_dir_url.'images/seo-banner.gif" /></a></p>';
			}
		}
	}
	
	/**
	*
	* This function fully disables autosave
	*
	* @since 7.6.4
	*/
	function disableAutosave(){
		wp_deregister_script('autosave');
	}	
	
	/**
	* Add a widget to the wp dashboard.
	*
	* This function is hooked into the 'wp_dashboard_setup' action below.
	*
	* @since 7.6.2
	*/
	function WSW_add_dashboard_widgets() {

	wp_add_dashboard_widget( 'sdf_dashboard_widget', 'From the Creators of SEO Wizard', array(&$this, 'sdf_dashboard_widget_function') );
	
	// Globalize the metaboxes array, this holds all the widgets for wp-admin
	global $wp_meta_boxes;

	// Get the regular dashboard widgets array 
	// (which has our new widget already but at the end)
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	// Backup and delete our new dashboard widget from the end of the array
	$WSW_widget_backup = array( 'sdf_dashboard_widget' => $normal_dashboard['sdf_dashboard_widget'] );
	unset( $normal_dashboard['sdf_dashboard_widget'] );

	// Merge the two arrays together so our widget is at the beginning
	$sorted_dashboard = array_merge( $WSW_widget_backup, $normal_dashboard );

	// Save the sorted array back into the original metaboxes 
	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		
	}

	/**
	 * Create the function to output the contents of our Dashboard Widget.
	 */
	function sdf_dashboard_widget_function() {
     $rss = fetch_feed('http://seowizard.org/feed');
        $html = '<ul class = "seo_blogs">';
        if( !is_wp_error($rss)) {
            $maxitems = $rss->get_item_quantity(5);
            if($maxitems == 0)
            {
                $html .= '<li>There are not blogs yet.</li>';
            }
            else
            {
                $rss_items = $rss->get_items(0, $maxitems);
                foreach ($rss_items as $item) {
                    $html .= '<li><a href="'.esc_url($item ->get_permalink()).'" title = "Posted '.$item -> get_date().'" style = "margin-right:20px;">'.esc_html( $item ->get_title()).'</a>   '.$item ->get_date("j F Y").' </li>';
                }
            }


        }
        $html .= '</ul>';
        echo $html;
		// Display whatever it is you want to show in widget content.
		//echo "";
	} 
}
?>