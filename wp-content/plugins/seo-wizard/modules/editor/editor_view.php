<?php
/**
 *view for editor module.
 */
class WSW_editor_page
{
    public $robots_file;
    public $htaccess_file;

    public function __construct()
    {
        $this->robots_file = get_home_path() . 'robots.txt';
        $this->htaccess_file = get_home_path() . '.htaccess';
    }

    public function display()
    {
        ?>

        <div class="box-border-box col-md-9">
            <h2 id="wsw-title">File Edit</h2>
            <p>You should have some knowledge about robots.txt file and .htaccess file.</p>
            <div id="tabbed-nav-editor" style="display: none;">
                <ul>
                    <li><a>Robots.txt<span></span></a></li>
                    <li><a>htaccess file<span></span></a></li>
                </ul>
                <div>
                    <div>
                        <div id="tab-robot">
                            <?php
                              if(!file_exists($this ->robots_file))
                              {
                                  if(is_writable( get_home_path() ) ) {
                                      ?>
                                      <p id="wsw_robots_exist">Your site doesn't have robot.txt file.
                                          Please create the robots.txt file here.
                                      </p>
                                      <input type="button" class="btn btn-primary" id="wsw_create_robots" value="Create Robots.txt">
                                      <input type="hidden" name="wsw-robots-ajax-nonce" id="wsw_robots_ajax_nonce" value="<?php echo( wp_create_nonce( 'wsw-robots-ajax-nonce' ) );?>" />

                                  <?php
                                  }
                                  else
                                  {
                                     echo "<p>Your site doesn't allow to write file.</p>";
                                  }
                              }
                            else{
                                echo('<p>You can edit your robots.txt file here</p>');
                                $f = fopen( $this ->robots_file, 'r' );

                                $content = '';
                                if ( filesize( $this ->robots_file ) > 0 ) {
                                    $content = fread( $f, filesize( $this ->robots_file ) );
                                }
                                $robots_txt_content = esc_textarea( $content );
                            }
                            ?>
                            <div class="wsw-robots-textarea" id="wsw_robots_textarea" <?php  if(!file_exists($this ->robots_file)) echo "style='display: none;'"?>>
                                <textarea id="wsw_robots_text" class="wsw-textarea"><?php if(isset($robots_txt_content)) echo trim($robots_txt_content);?></textarea>
                                <input type="button" class="btn btn-primary" id="wsw_robots_save" value="Update robots text">
                                <input type="hidden" name="wsw-robots-update-ajax-nonce" id="wsw_robots_update_ajax_nonce" value="<?php echo( wp_create_nonce( 'wsw-robots-update-ajax-nonce' ) );?>" />
                                <div class="wsw-edit-success notice updated" style="display: none;">
                                    <p>Congratulation! Updated successfully.</p>
                                </div>
                                <div class="notice error wsw-edit-failure" style="display: none;">
                                    <p>Failed!! Please check your site configuration.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div id="tab-htaccess">
                            <h3>.htaccess file contents</h3>
                            <?php
                            if ( ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) === false ) && file_exists(  $this->htaccess_file ) )
                            {
                                $f = fopen($this->htaccess_file, 'r');

                                $contents = '';
                                if (filesize($this->htaccess_file) > 0) {
                                    $contents = fread($f, filesize($this->htaccess_file));
                                }
                                $contents = esc_textarea($contents);
                                if (is_writable($this->htaccess_file)) {
                                    ?>
                                    <div class="wsw-robots-textarea" id="wsw-htaccess-textarea">
                                    <textarea id="wsw_htaccess_content" class="wsw-textarea"><?php
                                    if (isset($contents)) echo trim($contents);
                                        ?>
                                        </textarea>
                                        <input type="button" class="btn btn-primary" id="wsw_htaccess_save" value="Update htaccess file">
                                        <input type="hidden" name="wsw-htaccess-update-ajax-nonce" id="wsw_access_update_ajax_nonce" value="<?php echo( wp_create_nonce( 'wsw-access-update-ajax-nonce' ) );?>" />
                                        <div class="wsw-htaccess-success notice updated" style="display: none;">
                                            <p>Congratulation! Updated successfully.</p>
                                        </div>
                                        <div class="notice error wsw-htaccess-failure" style="display: none;">
                                            <p>Failed!! Please check your site configuration.</p>
                                        </div>
                                    </div>
                                    <?php
                                }
                                else
                                {
                                    echo "<p class='notice error'>The htaccess file isn't writable.</p>";
                                }

                            }
                            elseif(( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) === false ) && !file_exists(  $this->htaccess_file ))
                            {
                                echo"<p class='notice error'>There isn't any htaccess file in this server. Please check your server.</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <?php
    }
}