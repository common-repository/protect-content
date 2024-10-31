<?php 
/**
 * @author Moein 
 * @copyright 2011
 *
 * The uninstallation script.
 */

if( defined( 'ABSPATH') && defined('WP_UNINSTALL_PLUGIN') ) {
	//Unset the options when the plugin is deactivated
	delete_option('TIP_protect_images');
	delete_option('TIP_protect_images');
	$allposts = get_posts('numberposts=-1&post_type=post&post_status=any');
	//Remove all the post_meta that the plugin has been created
	foreach( $allposts as $postinfo) {
		delete_post_meta($postinfo->ID, '_TIP_protect_images_post');
		delete_post_meta($postinfo->ID, '_TIP_protect_text_post');
	}
}
?>