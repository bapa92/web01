<?php
/**
 * 404 Monitor Module
 * 
 * @since 0.4
 */

if (class_exists('SEO_Module')) {

class WSW_Fofs extends SEO_Module
{
    static function get_module_title()
    {
        return __('404 Monitor', 'seo-wizard');
    }

    function get_menu_title()
    {
        return __('404 Monitor', 'seo-wizard');
    }

    static function has_menu_count()
    {
        return true;
    }

    function admin_page_contents()
    {
        $this->children_admin_page_tabs();

    }
}
}
?>