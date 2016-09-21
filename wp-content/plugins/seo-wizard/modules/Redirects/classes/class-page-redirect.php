<?php

/**
 * @package Premium\Redirect
 */
class WSW_Page_Redirect {

	/**
	 * Function that outputs the redirect page
	 */
	public static function display() {
		// Check if there's an old URL set
		$old_url = '';
		if ( isset( $_GET['old_url'] ) ) {
			$old_url = urldecode( $_GET['old_url'] );
		}

		// Get the redirect types
		$redirect_types = WSW_Redirect_Manager::get_redirect_types();

		// Admin header


            /**
             * Display the updated/error messages
             * Only needed as our settings page is not under options, otherwise it will automatically be included
             * @see settings_errors()
             */?>
        <script>
            jQuery(document).ready(function ($) {
                $("#tabbed-nav").show();
                $("#tabbed-nav").zozoTabs({
                    rounded: false,
                    multiline: true,
                    theme: "white",
                    size: "medium",
                    responsive: true,
                    animation: {
                        effects: "slideH",
                        easing: "easeInOutCirc",
                        duration: 1000
                    }
                });
                $('#seo_redirect_submit').on('click' , function(event){
                    event.preventDefault();
                    var disable_php_redirect = $('#disable_php_redirect').is(":checked") ? 'on' : 'off';
                    var separate_file = $('#wsw_separate_file').is(':checked') ? 'on' : 'off';
                    $.post(
                        ajaxurl,
                        {
                            action: 'wsw_save_redirect_opton',
                            ajax_nonce: $( '.wsw_redirects_ajax_nonce' ).val(),
                            disable_php_redirect: disable_php_redirect,
                            separate_file: separate_file
                        },
                        function( response ) {
                            location.reload();
                        }
                    );

                })
            });
            </script>
            <div class="box-border-box col-md-9" >
            <?php
           require_once( ABSPATH . 'wp-admin/options-head.php' );
            ?>
            <h2 id="wsw-title"><?php echo (esc_html( get_admin_page_title() ));?></h2>
            <div id="tabbed-nav" style="display: none;">
            <ul>
               <li><a>Redirects<span></span></a></li>
               <li><a>Regex Redirects<span></span></a></li>
               <li><a>Settings<span></span></a></li>
            </ul>
            <div>
               <div>
                   <div id="tab-url" class="redirect-table-tab">
                       <form class='wsw-new-redirect-form' method='post'>
                           <div class='wsw_redirects_new'>
							<h2><?php echo( __( 'Add New Redirect', 'seo-wizard' )) ;?></h2>
                           <label class='textinput' for='wsw_redirects_new_old'> <?php echo( __( 'Old URL', 'seo-wizard' ));?></label>
                           <input type='text' class='textinput' name='wsw_redirects_old' id='wsw_redirects_old' value='<?php echo(esc_url( $old_url ));?>'/>
                           <br class='clear'/>
                           <label class='textinput' for='wsw_redirects_new_new'><?php echo( __( 'New URL', 'seo-wizard' ));?></label>
                           <input type='text' class='textinput' name='wsw_redirects_new' id='wsw_redirects_new' value='' />
                           <br class='clear'/>
                           <label class='textinput' for='wsw_redirects_new_type'> <?php echo( _x( 'Type', 'noun', 'seo-wizard' ));?></label>
                           <select name='wsw_redirects_new_type' id='wsw_redirects_new_type' class='select'>

				<?php if ( count( $redirect_types ) > 0 ) {
					foreach ( $redirect_types as $type => $desc ) {
						echo "<option value='" . $type . "'>" . $desc . "</option>" . PHP_EOL;
					}

				}
        ?>

                           </select>
                           <br />
                           <br />
                           <p class="seo-redirect-desc">The redirect type is the HTTP response code send to the browser telling the browser what type of redirect is served.</p>
                           <br class='clear'/>
                           <a href='javascript:;' id='wsw_create_redirect' class='btn btn-primary'><?php echo( __( 'Add Redirect', 'seo-wizard' ) );?></a>
                       </div>
                   </form>
                   <form id='url' class='wsw-redirects-table-form' method='post' action=''>
                       <input type='hidden' class='wsw_redirects_ajax_nonce' value='<?php echo ( wp_create_nonce( 'wsw-redirects-ajax-security' )); ?>' />
                 <?php
				$list_table = new WSW_Redirect_Table( 'URL' );
				$list_table->prepare_items();
				$list_table->search_box( __( 'Search', 'seo-wizard' ), 'wsw-redirect-search' );
				$list_table->display();
                 ?>
				</form>
               </div>
            </div>
                <div>
                    <div id="tab-regex" class="redirect-table-tab">
                        <p>Regex Redirects are extremely powerful redirects. You should only use them if you know what you are doing. <br /> </p>
                        <form class='wsw-new-redirect-form' method='post'>
                            <div class='wsw_redirects_new'>
                                <label class='textinput' for='wsw_redirects_new_old'><?php echo(__( 'Regular Expression', 'seo-wizard' ));?></label>
                                <input type='text' class='textinput' name='wsw_redirects_old' id='wsw_redirects_old' value='<?php echo($old_url);?>' />
                                <br class='clear'/>
                                <label class='textinput' for='wsw_redirects_new_new'> <?php echo( __( 'URL', 'seo-wizard' ) );?></label>
                                <input type='text' class='textinput' name='wsw_redirects_new' id='wsw_redirects_new' value='' />
                                <br class='clear'/>


                                <label class='textinput' for='wsw_redirects_new_type'><?php echo(_x( 'Type', 'noun', 'seo-wizard' ));?></label>
                                <select name='wsw_redirects_new_type' id='wsw_redirects_new_type' class='select'>
        <?php
        if ( count( $redirect_types ) > 0 ) {
					foreach ( $redirect_types as $key => $desc ) {
						echo "<option value='" . $key . "'>" . $desc . "</option>" . PHP_EOL;
					}

				}
        ?>
                                </select>
                                <br />
                                <br />
                                <p class="seo-redirect-desc">The redirect type is the HTTP response code send to the browser telling the browser what type of redirect is served. </p>
                                <br class='clear'/>
                                <a href='javascript:;' class='btn btn-primary'><?php echo(__( 'Add Redirect', 'seo-wizard' ) );?></a>
                            </div>
                        </form>
                        <p class='desc'>&nbsp;</p>
                        <form id='regex' class='wsw-redirects-table-form' method='post' action=''>
                            <input type='hidden' class='wsw_redirects_ajax_nonce' value='<?php echo ( wp_create_nonce( 'wsw-redirects-ajax-security' )); ?>' />
                            <?php	// The list table
                            $list_table = new WSW_Redirect_Table( 'REGEX' );
                            $list_table->prepare_items();
                            $list_table->search_box( __( 'Search', 'seo-wizard' ), 'wsw-redirect-search' );
                            $list_table->display();
                            ?>
                        </form>
                </div>
        </div>
        <div>
        <div>
        <?php
				// Get redirect options
				$redirect_options = WSW_Redirect_Manager::get_options();

				// Do file checks
				if ( 'on' == $redirect_options['disable_php_redirect'] ) {

					$file_write_error = false;

						if ( 'on' == $redirect_options['separate_file'] ) {
							if ( file_exists( WSW_Redirect_File_Manager::get_file_path() ) ) {
								echo '<div style="margin: 5px 0; padding: 3px 10px; background-color: #ffffe0; border: 1px solid #E6DB55; border-radius: 3px">';
								echo '<p>' . __( "As you're on Apache, you should add the following include to the website httpd config file:", 'seo-wizard' ) . '</p>';
								echo '<pre>Include ' . WSW_Redirect_File_Manager::get_file_path() . '</pre>';
								echo '</div>';
							}
							else {
								$file_write_error = true;
							}
						}
						else {
							if ( ! is_writable( WSW_Redirect_File_Manager::get_htaccess_file_path() ) ) {
								/* translators: %s: '.htaccess' file name */
								echo "<div class='error'><p><b>" . sprintf( __( 'We\'re unable to save the redirects to your %s file. Please make the file writable.', 'seo-wizard' ), '<code>.htaccess</code>' ) . "</b></p></div>\n";
							}
						}

                    if ( $file_write_error ) {
						echo "<div class='error'><p><b>" . __( sprintf( "We're unable to save the redirect file to %s", WSW_Redirect_File_Manager::get_file_path() ), 'seo-wizard' ) . "</b></p></div>\n";
					}

				}
        ?>
        <h2>Redirect Settings</h2>
    <form action="" method="post">
        <div class="form-group">
            <label class="col-sm-1 control-label"></label>
            <div class="col-sm-11">
                <label style="width: 100%;">
                    <input type="checkbox" id="disable_php_redirect" name="disable_php_redirect" <?php echo ($redirect_options['disable_php_redirect'] =='on') ? 'checked':''?>>
                    Disable PHP redirects
                </label>
                <p>Write redirects to the<code>.htacces</code> file. Make sure the <code>.htacces</code> file is writable.</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"></label>
            <div class="col-sm-11">
                <label style="width: 100%;">
                    <input type="checkbox" id="wsw_separate_file" name = 'separate_file' <?php echo ($redirect_options['separate_file'] =='on') ? ' checked':''?>>
                    Generate a separate redirect file
                </label>
                <p>By default we write the redirects to your <code>.htaccess</code>file, check this if you want the redirects written to a separate file. Only check this option if you know what you are doing!</p>
            </div>
        </div>
        <input type='hidden' class='wsw_redirects_ajax_nonce' value='<?php echo ( wp_create_nonce( 'wsw-redirects-ajax-security' )); ?>' />
        <p class="submit"><input type="submit" name="submit" id="seo_redirect_submit" class="btn btn-primary" value="<?php echo(__( 'Save Changes', 'seo-wizard' ));?>"></p>
    </form>
        </div>
        </div>
                <br class="clear">
            </div>
            </div>

<?php
    }

	/**
	 * Function that is triggered when the redirect page loads
	 */
	public static function page_load() {
		add_action( 'admin_enqueue_scripts', array( 'WSW_Page_Redirect', 'page_scripts' ) );
	}

	/**
	 * Load the admin redirects scripts
	 */
	public static function page_scripts() {
				add_screen_option( 'per_page', array(
			'label'   => __( 'Redirects per page', 'seo-wizard' ),
			'default' => 25,
			'option'  => 'redirects_per_page'
		) );
	}

	/**
	 * Catch redirects_per_page
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set_screen_option( $status, $option, $value ) {
		if ( 'redirects_per_page' == $option ) {
			return $value;
		}
	}

}