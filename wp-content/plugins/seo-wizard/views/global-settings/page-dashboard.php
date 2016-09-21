<style>
    .z-tabs.nested-tabs > .z-container > .z-content > .z-content-inner {padding-top: 10px;}
</style>
<script>
    jQuery(document).ready(function ($) {
        /* jQuery activation and setting options for parent tabs with id selector*/
        $("#tabbed-nav").zozoTabs({
            rounded: false,
            multiline: true,
            theme: "white",
            size: "medium",
            responsive: true,
            animation: {
                effects: "slideH",
                easing: "easeInOutCirc",
                duration: 500
            },
            defaultTab: "<?php echo $first_tab;?>"
        });

        /* jQuery activation and setting options for all nested tabs with class selector*/
        $(".nested-tabs").zozoTabs({
            position: "top-left",
            theme: "red",
            style: "underlined",
            rounded: false,
            shadows: false,
            defaultTab: "tab1",
            animation: {
                easing: "easeInOutCirc",
                effects: "slideV",
                duration: 350
            },
            size: "medium"
        });
        jQuery("#dashboard-page").show();
    });

</script>

<script>
    // Save settings for Global.
    function build_sitemap()
    {

        if(jQuery('#chk_make_sitemap').attr('checked')){
            var data = {
                action:'wsw_build_sitemap'
            };

            jQuery.post(ajax_object.ajax_url, data, function(respond) {
                jQuery("#wsw-build-sitemap-view").show();
            });

        }
    }
</script>
<div id="dashboard-page" class="box-border-box col-md-9" style="float: left; display:none; margin-right: 0px;margin-top: 0px;">
<strong><font color="red"><font size="3">To see the full potential of Seo Wizard edit a page or a post.</font></font></strong><br />
<strong><font color="green"><font size="3"><a href="http://www.seowizard.org/c-seo-wizard" target="_blank">Do you need better search engine rankings? The Seo Wizards can help you, visit our website!</a></font></font></strong><hr />

    <form action="admin.php?page=wsw_dashboard_page" id="wsw_log_404_form" method="post" class="form-horizontal" role="form">
       <!-- Zozo Tabs Start-->
        <div id="tabbed-nav">
            <!-- Tab Navigation Menu -->
            <ul>
                <li><a>Global<span></span></a></li>
                <li><a>Advanced<span></span></a></li>
                <li><a>API Key<span></span></a></li>
                <li><a>XML Sitemap<span></span></a></li>

            </ul>

            <!-- Content container -->
            <div>
                <!-- Global -->
                <div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_keyword_to_titles" <?php echo ($chk_keyword_to_titles =='1')?'checked':''?>>
                                <input type="hidden" name="wsw-global-ajax-nonce" id="wsw-global-ajax-nonce" value="<?php echo( wp_create_nonce( 'wsw-global-ajax-nonce' ) );?>" />
                                Allow SEO Wizard to automatically add the keyword in Posts titles.

                            </label>
                         </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_nofollow_in_external" <?php echo ($chk_nofollow_in_external =='1')?'checked':''?>>
                                Allow SEO Wizard to automatically add rel="nofollow" to external links.
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_nofollow_in_image" <?php echo ($chk_nofollow_in_image =='1')?'checked':''?>>
                                Allow SEO Wizard to automatically add rel="nofollow" to image links.
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_tweak_permalink" <?php echo ($chk_tweak_permalink =='1')?'checked':''?>>
                                Allow SEO Wizard to Strip the category base (usually /category/) from the category URL.
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_use_richsnippets" <?php echo ($chk_use_richsnippets =='1')?'checked':''?>>
                                Enable Rich Snippets in posts Settings.
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_use_facebook" <?php echo ($chk_use_facebook =='1')?'checked':''?>>
                                Enable Social SEO/Facebook in posts Settings.
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_use_twitter" <?php echo ($chk_use_twitter =='1')?'checked':''?>>
                                Enable Social SEO/Twitter in posts Settings.
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_use_meta_robot" <?php echo ($chk_use_meta_robot =='1')?'checked':''?>>
                                Enable Meta Robot Tag in posts Settings.
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-1 control-label"></label>
                        <div class="col-sm-11">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_author_linking" name="chk_author_linking" <?php echo ($chk_author_linking =='1')?'checked':''?>>
                                Enable Author credit link.
                            </label>
                        </div>
                    </div>

                </div>
                <!-- Advanced -->

                <div>

                    <!-- Zozo Tabs nested (Subscribe) Start-->
                    <div class="nested-tabs">
                        <ul>
                            <!------------- for dynamic homepage -------------------->
                            <li ><a>Home Page Settings</a></li>
                            <!-------------------------------------------------------->
                            <li><a>Keyword Decorate</a></li>
                            <li><a>Image Settings</a></li>
                            <li><a>Tags</a></li>
                            <li><a>Page Block</a></li>
                            <!-------------  for webmaster tools ------------------------>
                            <li><a>Webmaster Verification</a></li>
                            <!----------------------------------------------------------->

                         </ul>
                        <div>
                            <!--  for dynamic front page-->
                            <div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" id="chk-static-front">Use Static Front Page</label>
                                    <input type="checkbox"  id="chk_homepage_static" <?php echo ($chk_homepage_static =='1')?'checked':''?>>
                                </div>
                                <div class="form-group" id="home-dynamic-settings"  style="display: none;">
                                    <div class="col-sm-9 home-settings">
                                        <label class="col-sm-3 control-label"> Home page title</label>
                                        <textarea id="wsw_homepage_title" class="home-setting-text" maxlength="60"><?php echo $wsw_homepage_title; ?> </textarea>

                                        <div class="left-numbers">
                                            <input type="text" readonly id="title_left_numbers" class="count-numbers">
                                            characters left. You should use a maximum 60 chars for the homepage title.
                                        </div>
                                    </div>
                                    <div class="col-sm-9 home-settings">
                                        <label class="col-sm-3 control-label"> Home page description</label>
                                        <textarea id="wsw_homepage_desc" class="home-setting-text" maxlength="160"><?php echo $wsw_homepage_desc; ?> </textarea>

                                        <div class="left-numbers">
                                            <input type="text" readonly id="desc_left_numbers" class="count-numbers" >
                                            characters left. You should use a maximum 160 chars for the homepage
                                            description.
                                        </div>
                                    </div>
                                    <div class="col-sm-9 home-settings">
                                        <label class="col-sm-3 control-label"> Home page keywords</label>
                                        <textarea id="wsw_homepage_keywords"
                                                  class="home-setting-text"><?php echo $wsw_homepage_keywords; ?> </textarea>

                                        <div class="left-numbers">
                                            You should separate the keywords with comma(,).
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <!------------------------------------------------------------->
                            <div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Decorate your keyword with bold</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="checkbox" id="chk_keyword_decorate_bold" <?php echo ($chk_keyword_decorate_bold =='1')?'checked':''?>>
                                            Allow SEOWizard to automatically decorate your keyword with bold.
                                        </label>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_bold_type" value="0" <?php echo ($opt_keyword_decorate_bold_type =='0')?'checked':''?>> &lt;b&gt; - &lt;/b&gt;	</label><br>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_bold_type" value="1" <?php echo ($opt_keyword_decorate_bold_type =='1')?'checked':''?>> &lt;strong&gt; - &lt;/strong&gt;	</label>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_bold_type" value="2" <?php echo ($opt_keyword_decorate_bold_type =='2')?'checked':''?>> style - font-weight: bold	</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Decorate your keyword with italic</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="checkbox" id="chk_keyword_decorate_italic" <?php echo ($chk_keyword_decorate_italic =='1')?'checked':''?>>
                                            Allow SEOWizard to automatically decorate your keyword with italic.
                                        </label>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_italic_type" value="0" <?php echo ($opt_keyword_decorate_italic_type =='0')?'checked':''?>> &lt;i&gt; - &lt;/i&gt;	</label><br>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_italic_type" value="1" <?php echo ($opt_keyword_decorate_italic_type =='em')?'checked':''?>> &lt;em&gt; - &lt;/em&gt;	</label>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_italic_type" value="2" <?php echo ($opt_keyword_decorate_italic_type =='style')?'checked':''?>> style - font-style: italic		</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Decorate your keyword with underline</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="checkbox" id="chk_keyword_decorate_underline" <?php echo ($chk_keyword_decorate_underline =='1')?'checked':''?>>
                                            Allow SEOWizard to automatically decorate your keyword with underline.
                                        </label>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_underline_type" value="0" <?php echo ($opt_keyword_decorate_underline_type =='underline')?'checked':''?>> &lt;u&gt; - &lt;/u&gt;	</label><br>
                                        <label style="width: 100%;"><input type="radio" name="opt_keyword_decorate_underline_type" value="1" <?php echo ($opt_keyword_decorate_underline_type =='style')?'checked':''?>> style - text-decoration: underline		</label>
                                    </div>
                                </div>

                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Alternate text attribute</label>
                                    <div class="col-sm-9">

                                        <label style="width: 100%;"><input type="radio" name="opt_image_alternate_type" value="empty" <?php echo ($opt_image_alternate_type =='empty')?'checked':''?>>
                                            Allow SEO Wizard to automatically add alt="value" to all images in the content that do not have an alt tag value.	</label><br>
                                        <label style="width: 100%;"><input type="radio" name="opt_image_alternate_type" value="all" <?php echo ($opt_image_alternate_type =='all')?'checked':''?>>
                                            Override all images alt values.	</label>
                                        <label style="width: 100%;"><input type="radio" name="opt_image_alternate_type" value="non" <?php echo ($opt_image_alternate_type =='non')?'checked':''?>>
                                            Don't automatically decorate alt images values.</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Alternate text structure</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="text" id="txt_image_alternate" value="<?php echo $txt_image_alternate; ?>" style="width: 50%;">
                                        </label><br>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Title attribute</label>
                                    <div class="col-sm-9">

                                        <label style="width: 100%;"><input type="radio" name="opt_image_title_type" value="empty" <?php echo ($opt_image_title_type =='empty')?'checked':''?>>
                                            Allow SEO Wizard to automatically add the title attribute to all images in the content that do not have a image title value.	</label><br>
                                        <label style="width: 100%;"><input type="radio" name="opt_image_title_type" value="all" <?php echo ($opt_image_title_type =='all')?'checked':''?>>
                                            Override all images title values.	</label>
                                        <label style="width: 100%;"><input type="radio" name="opt_image_title_type" value="none" <?php echo ($opt_image_title_type =='none')?'checked':''?>>
                                            Don't automatically decorate title images values.</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Title structure</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="text" id="txt_image_title" value="<?php echo $txt_image_title; ?>" style="width: 50%;">
                                        </label><br>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Enable tagging</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="checkbox" id="chk_tagging_using_google" <?php echo ($chk_tagging_using_google =='1')?'checked':''?>>
                                            Allow SEOWizard to automatically add related keywords in Posts tags.
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label"></label>
                                    <div class="col-sm-9">

                                        <label style="width: 100%;">
                                            <input type="text" id="txt_generic_tags" value="<?php echo $txt_generic_tags?>" style="width: 50%;">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label"></label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%;">
                                            <input type="checkbox" id="chk_block_login_page" <?php echo ($chk_block_login_page =='1')?'checked':''?>>
                                            Login pages have no SEO value, block them.
                                        </label>
                                        <label style="width: 100%;">
                                            <input type="checkbox" id="chk_block_admin_page" <?php echo ($chk_block_admin_page =='1')?'checked':''?>>
                                            Admin pages have no SEO value, block them.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!---------------- for Webmaster verification ----------------------->
                            <div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Google Webmaster Tools</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%">
                                            <input type="text" id="wsw_webmaster_content" value="<?php echo $wsw_webmaster_content;?>"/>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Bing Webmaster Center</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%">
                                            <input type="text" id="wsw_bing_webmaster" value="<?php echo $wsw_bing_webmaster;?>"/>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Pinterest Site Verification</label>
                                    <div class="col-sm-9">
                                        <label style="width: 100%">
                                            <input type="text" id="wsw_pinterest_verify" value="<?php echo $wsw_pinterest_verify;?>"/>
                                        </label>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <!-- Zozo Tabs nested (Subscribe) End-->
                </div>
                <!-- API -->
                <div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Bing API Key</label>
                        <div class="col-sm-7">

                            <input type="text" style="width: 100%;" id="lsi_bing_api_key" value="<?php echo $lsi_bing_api_key; ?>">

                        </div>
                    </div>
                </div>



                <!-- XML Sitemap -->
                <div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"></label>
                        <div class="col-sm-9">
                            <label style="width: 100%;">
                                <input type="checkbox" id="chk_make_sitemap" <?php echo ($chk_make_sitemap == '1')?'checked':''?>>
                                Check this box to enable XML sitemap functionality.
                            </label>

                            <button type="button" onclick="build_sitemap();">Build Sitemap Now</button>
                            <a href="<?php echo home_url() . '/sitemap.xml';?>"><?php echo home_url() . '/sitemap.xml';?></a>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- Zozo Tabs End-->
		<br />


<p><a href="http://www.seowizard.org/c-seo-wizard" target="_blank"><img src="<?php echo (WSW_Main::$plugin_url.'/../images/seo-banner.gif');?>" /></a></p>
        <div class="wsw-global-save-view">
        <button type="button"  class="btn btn-primary" onclick="save_global_settings();">Save Settings</button>
        </div>
    </form>
</div>
<div id="wrap-right-side" class="box-border-box  col-md-3" style="float: left;">

    <div class="panel panel-default text-left box-border-box  small-content">
        <div class="panel-heading"><strong>About SEO Wizard</strong></div>
        <div class="panel-body">
            <p>SEO Wizard is an Wordpress Plugin software that allows you to optimizes your Wordpress better for top SEO ranking.</p>
            <ul class="sib-widget-menu">
                <li>
                    <a href="http://www.seowizard.org/" target="_blank"><i class="fa fa-angle-right"></i> &nbsp;Plugin Features</a>
                </li>
                <li>
                    <a href="http://www.seowizard.org/c-seo-wizard" target="_blank"><i class="fa fa-angle-right"></i> &nbsp;<font color="red">Boost Search Engine Rankings >></font></a>
                </li>
            </ul>
        </div>

    </div>
    <div class="panel panel-default text-left box-border-box  small-content">
        <div class="panel-heading"><strong>Need Help ?</strong></div>
        <div class="panel-body">
            <p>You have a question or need more information ?</p>
            <ul class="sib-widget-menu">
                <li><a href="http://www.seowizard.org/" target="_blank"><i class="fa fa-angle-right"></i> &nbsp;Tutorials</a></li>
                <li><a href="http://www.seowizard.org/support/" target="_blank"><i class="fa fa-angle-right"></i> &nbsp;Plugin Support</a></li>
            </ul>
        </div>
    </div>
    <div class="panel panel-default text-left box-border-box  small-content">
        <div class="panel-heading"><strong>Recommended this plugin</strong></div>
        <div class="panel-body">
            <p>You like this plugin? Let everybody knows and review it</p>
            <ul class="sib-widget-menu">
                <li><a href="http://wordpress.org/support/view/plugin-reviews/seo-wizard" target="_blank"><i class="fa fa-angle-right"></i> &nbsp;Review this plugin</a></li>
            </ul>
        </div>
    </div>
</div>
