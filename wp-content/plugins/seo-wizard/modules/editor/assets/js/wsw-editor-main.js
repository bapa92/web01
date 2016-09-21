/**
 * jquery for module editor
 */
jQuery(document).ready(function ($) {
    $("#tabbed-nav-editor").show();
    $("#tabbed-nav-editor").zozoTabs({
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
    if($('#wsw_create_robots').length)
    {
        $('#wsw_create_robots').on('click' , function(){
            var data = {
                action:'wsw_create_robots_text',
                value: '1',
                security:$('#wsw_robots_update_ajax_nonce').val()
            };
            $.post(ajax_object.ajax_url, data, function(respond) {
                $('#wsw_create_robots').hide();
                $('#wsw_robots_exist').html('You can edit your robots.txt file here.');
                $('.wsw-robots-textarea').show('fast');
                $('#wsw_robots_text').val(respond);
            });
        });
    }
    $('#wsw_robots_save').live('click' , function(){
       var data = {
           action: 'wsw_update_content_robots',
           content: $('#wsw_robots_text').val(),
           security: $('#wsw_robots_update_ajax_nonce').val()
       };
        $.post(ajax_object.ajax_url, data, function(respond){
           if(respond == 'success')
           {
               $('.wsw-edit-success').show('slow');
           }
            else if(respond == 'failed')
            {
                $('.wsw-edit-failure').show('slow');
            }
        })
    });
    $('#wsw_htaccess_save').on('click', function(){
       var data = {
           action: 'wsw_edit_htaccess_file',
           content: $('#wsw_htaccess_content').val(),
           security: $('#wsw_access_update_ajax_nonce').val()
       };
        $.post(ajax_object.ajax_url, data, function(respond){
            if(respond == 'success')
            {
                $('.wsw-htaccess-success').show('slow');
            }
            else if(respond == 'failed')
            {
                $('.wsw-htaccess-failure').show('slow');
            }
        });
    });
});