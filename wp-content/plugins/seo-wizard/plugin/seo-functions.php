<?php
/**
 * Non-class functions.
 */

/********** INDEPENDENTLY-OPERABLE FUNCTIONS **********/

/**
 * Returns the plugin's User-Agent value.
 * Can be used as a WordPress filter.
 * 
 * @since 0.1
 * @uses WSW_USER_AGENT
 * 
 * @return string The user agent.
 */
function WSW_get_user_agent() {
	return WSW_USER_AGENT;
}

/**
 * Records an event in the debug log file.
 * Usage: WSW_debug_log(__FILE__, __CLASS__, __FUNCTION__, __LINE__, "Message");
 * 
 * @since 0.1
 * @uses WSW_VERSION
 * 
 * @param string $file The value of __FILE__
 * @param string $class The value of __CLASS__
 * @param string $function The value of __FUNCTION__
 * @param string $line The value of __LINE__
 * @param string $message The message to log.
 */
function WSW_debug_log($file, $class, $function, $line, $message) {
	global $seo_update;
	if (isset($seo_update->modules['settings']) && $seo_update->modules['settings']->get_setting('debug_mode') === true) {
	
		$date = date("Y-m-d H:i:s");
		$version = WSW_VERSION;
		$message = str_replace("\r\n", "\n", $message);
		$message = str_replace("\n", "\r\n", $message);

	}
}

/**
 * Joins strings into a natural-language list.
 * Can be internationalized with gettext or the WSW_lang_implode filter.
 * 
 * @since 1.1
 * 
 * @param array $items The strings (or objects with $var child strings) to join.
 * @param string|false $var The name of the items' object variables whose values should be imploded into a list.
	If false, the items themselves will be used.
 * @param bool $ucwords Whether or not to capitalize the first letter of every word in the list.
 * @return string|array The items in a natural-language list.
 */
function WSW_lang_implode($items, $var=false, $ucwords=false) {
	
	if (is_array($items) ) {
		
		if (strlen($var)) {
			$_items = array();
			foreach ($items as $item) $_items[] = $item->$var;
			$items = $_items;
		}
		
		if ($ucwords) $items = array_map('ucwords', $items);
		
		switch (count($items)) {
			case 0: $list = ''; break;
			case 1: $list = $items[0]; break;
			case 2: $list = sprintf(__('%s and %s', 'seo-update'), $items[0], $items[1]); break;
			default:
				$last = array_pop($items);
				$list = implode(__(', ', 'seo-update'), $items);
				$list = sprintf(__('%s, and %s', 'seo-update'), $list, $last);
				break;
		}
		
		return apply_filters('WSW_lang_implode', $list, $items);
	}

	return $items;
}

/**
 * Escapes an attribute value and removes unwanted characters.
 * 
 * @since 0.8
 * 
 * @param string $str The attribute value.
 * @return string The filtered attribute value.
 */
function WSW_esc_attr($str) {
	if (!is_string($str)) return $str;
	$str = str_replace(array("\t", "\r\n", "\n"), ' ', $str);
	$str = esc_attr($str);
	return $str;
}

/**
 * Escapes HTML.
 * 
 * @since 2.1
 */
function WSW_esc_html($str) {
	return esc_html($str);
}

/**
 * Escapes HTML. Double-encodes existing entities (ideal for editable HTML).
 * 
 * @since 1.5
 * 
 * @param string $str The string that potentially contains HTML.
 * @return string The filtered string.
 */
function WSW_esc_editable_html($str) {
	return _wp_specialchars($str, ENT_QUOTES, false, true);
}


?>