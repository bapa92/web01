<?php
class WSW_Htaccess_Redirect_File extends WSW_Apache_Redirect_File {

	/**
	 * Save the redirect file
	 *
	 * @return bool
	 */
	public function save_file() {

		// Generate file content
		$file_content = $this->generate_file_content();

		if ( null == $file_content ) {
			return false;
		}

		$file_path = WSW_Redirect_File_Manager::get_htaccess_file_path();

		// Read current htaccess
		$htaccess = '';
		if ( file_exists( $file_path ) ) {
			$htaccess = file_get_contents( $file_path );
		}

		$htaccess = preg_replace( "`# BEGIN SEO_WIZARD REDIRECTS.*# END SEO_WIZARD REDIRECTS" . PHP_EOL . "`is", "", $htaccess );

		// New Redirects
		$file_content = "# BEGIN SEO_WIZARD REDIRECTS" . PHP_EOL . "<IfModule mod_rewrite.c>" . PHP_EOL . "RewriteEngine On" . PHP_EOL . $file_content . "</IfModule>" . PHP_EOL . "# END SEO_WIZARD REDIRECTS" . PHP_EOL;

		// Prepend our redirects to htaccess file
		$htaccess = $file_content . $htaccess;

		// Update the .htaccess file
		if( is_writable( $file_path ) ) {
			$return = (bool) file_put_contents( $file_path, $htaccess );

			chmod($file_path, FS_CHMOD_FILE);

			return $return;
		}

		return false;

	}

}