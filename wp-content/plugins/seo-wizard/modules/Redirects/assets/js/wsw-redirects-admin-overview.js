/* global wpseo_premium_strings */
/* global wpseoMakeDismissible */
/* jshint -W097 */
/* jshint -W098 */
'use strict';

/**
 * Undoes a redirect
 *
 * @param {string} url
 * @param {string} nonce
 * @param {string} id
 */
function wsw_undo_redirect( url, nonce, id ) {
    jQuery.post(
        ajaxurl,
        {
            action: 'wsw_delete_redirect_url',
            ajax_nonce: nonce,
            redirect: { key: url },
            id: id
        },
        function( response ) {
            jQuery( '#' + response ).fadeOut( 'slow' );
        }
    );
}

/**
 * Creates a redirect
 *
 * @param {string} old_url
 * @param {string} nonce
 * @param {string} id
 */
function wsw_create_redirect( old_url, nonce, id ) {
    var new_url = window.prompt( wsw_redirects_strings.enter_new_url.replace( '%s', old_url ) );

    if ( null !== new_url ) {
        if ( '' !== new_url ) {
            jQuery.post(
                ajaxurl,
                {
                    action: 'wsw_create_redirect_url',
                    ajax_nonce: nonce,
                    old_url: old_url,
                    new_url: new_url,
                    id: id,
                    type: '301'
                },
                function( response ) {
                    var resp = JSON.parse( response );
                    jQuery( '#' + resp.id + ' p' ).html( wsw_redirects_strings.redirect_saved.replace( '%1$s', '<code>' + resp.old_url + '</code>' ).replace( '%2$s', '<code>' + resp.new_url + '</code>' ) );
                }
            );
        }
        else {
            window.alert( wsw_redirects_strings.error_new_url );
        }
    }
}

