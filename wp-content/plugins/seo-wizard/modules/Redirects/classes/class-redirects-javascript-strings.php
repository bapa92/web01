<?php

class WSW_Redirects_Javascript_Strings {
	private static $strings = null;

	private static function fill() {
		self::$strings = array(
			'error_circular'        => __( 'You can\'t redirect a URL to itself.', 'seo-wizard' ),
			'error_old_url'         => __( 'The old URL field can\'t be empty.', 'seo-wizard' ),
			'error_regex'           => __( 'The Regular Expression field can\'t be empty.', 'seo-wizard' ),
			'error_new_url'         => __( 'The new URL field can\'t be empty.', 'seo-wizard' ),
			'error_saving_redirect' => __( 'Error while saving this redirect', 'seo-wizard' ),
			'error_new_type'        => __( 'New type can\'t be empty.', 'seo-wizard' ),
			'unsaved_redirects'     => __( 'You have unsaved redirects, are you sure you want to leave?', 'seo-wizard' ),
			/* translator note: %s is replaced with the URL that will be deleted */
			'enter_new_url'         => __( 'Please enter the new URL for %s', 'seo-wizard' ),
			/* translator note: variables will be replaced with from and to URLs */
			'redirect_saved'        => __( 'Redirect created from %1$s to %2$s!', 'seo-wizard' ),
			'redirect_possibly_bad' => __( 'Possibly bad redirect.', 'seo-wizard' ),
			'redirect_not_ok'       => __( 'The URL you entered returned a HTTP code different than 200(OK).', 'seo-wizard' ),
		);
	}

	public static function strings() {
		if ( self::$strings === null ) {
			self::fill();
		}

		return self::$strings;
	}
}