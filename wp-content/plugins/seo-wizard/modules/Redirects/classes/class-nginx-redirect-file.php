<?php
class WSW_Nginx_Redirect_File extends WSW_Redirect_File {

	/**
	 * Format URL redirect
	 *
	 * @param $old_url
	 * @param $new_url
	 * @param $type
	 *
	 * @return string
	 */
	public function format_url_redirect( $old_url, $new_url, $type ) {
		return 'location ' . $old_url . ' { add_header X-Redirect-By \"Seo Wizard\"; return ' . $type . ' ' . $new_url . '; }';
	}

	/**
	 * Format REGEX redirect
	 *
	 * @param $regex
	 * @param $url
	 * @param $type
	 *
	 * @return string
	 */
	public function format_regex_redirect( $regex, $url, $type ) {
		return 'location ~ ' . $regex . ' { return ' . $type . ' ' . $url . '; }';
	}

}