<?php
/*
Plugin Name: Text & Image Protector
Plugin URI: http://imaprogrammer.wordpress.com/2011/01/07/tiprotector/
Description: Protects the text and images of your posts
Version: 1.0
Author: Moein Akbarof
Author URI: http://imaprogrammer.wordpress.com/
License: GPLv2
*/
?>
<?php
//The function make some changes to the content to protect the content
function protect_content($content){

	//This plugin use simple_html_dom class -> url for this class : http://simplehtmldom.sourceforge.net/index.htm
	require_once dirname( __FILE__ ) . '/simple_html_dom.php';
	//Create DOM from the content
	$html = str_get_html($content);
	$id = get_the_ID();
	if (get_option('TIP_protect_images') == 2 || (get_option('TIP_protect_images') == 1 && get_post_meta($id, '_TIP_protect_images_post', true) == 'checked')){
		//We loop through the images and replace them with the protected image
		foreach($html->find('img') as $element){
			$src = $element->src;
			$width = $element->width;
			$width_p = $width + 2;
			$height = $element->height;
			$height_p = $height + 2;
			$element->outertext = '
			<div style="overflow: hidden; position: absolute;  width:'.$width.'px; height:'.$height.'px; z-index:0; background: url('."'".$src."'".');"></div>
			<div style="overflow: hidden; position: absolute;  width:'.$width_p.'px; height:'.$height_p.'px; z-index:1;">
			<img title="Image Protector" src="'.dirname( __FILE__ ).'/protector.gif" border="0" alt="" width="1" height="1" />
			</div>
			<div style="overflow: hidden; width:1px; height:'.$height.'px; z-index: 1;">
			</div>';
		}
	}
	if (get_option('TIP_protect_text') == 2 || (get_option('TIP_protect_text') == 1 && get_post_meta($id, '_TIP_protect_text_post', true) == 'checked'))
		//Add a parent div node to protect the text
		return '<div onselectstart="return false;" unselectable="on;" style="-moz-user-select: none;">'.$html.'</div>';
	else
		//Rtuern the same content
		return $html;
}

//Call protect content function for the content
add_filter('the_content', 'protect_content');

//Set the options when the plugin is activated
function set_TIProtector_options() {
	add_option('TIP_protect_images', '1');
	add_option('TIP_protect_text', '1');
}
//Hook the set function
register_activation_hook (__FILE__, 'set_TIProtector_options');
//Add the meta box to the post pages
function TIP_modify_post_page() {
    global $wp_version;
    if( ! $wp_version || $wp_version >= '2.7' )
		add_meta_box('TIP_protect_images_post', 'Protect The Content', 'print_TIP_post_options', 'post', 'side');
	else
		add_meta_box('TIP_protect_images_post', 'Protect The Content', 'print_TIP_post_options', 'post');
}

//Add the meta box to the post page
add_action ('admin_menu', 'TIP_modify_post_page');

//Update the value of the options on posting or editing a post
function update_TIP_post_options($post_id) {
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !wp_verify_nonce( $_POST['TIProtector_nonce'], 'TIProtector' ))
		return $post_id;

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;


	// Check permissions
	if ( 'page' == $_POST['post_type'] )
		return $post_id;
	else
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

	// OK, we're authenticated: we need to find and save the data
		if ($_REQUEST['TIP_protect_images_post'])
			update_post_meta($post_id, '_TIP_protect_images_post', 'checked');
		else
			update_post_meta($post_id, '_TIP_protect_images_post', 'unchecked');
			
		if ($_REQUEST['TIP_protect_text_post'])
			update_post_meta($post_id, '_TIP_protect_text_post', 'checked');
		else
			update_post_meta($post_id, '_TIP_protect_text_post', 'unchecked');
	return $post_id;
}

//Update the options on posting or editng a post
add_action('save_post', 'update_TIP_post_options');

//Layout of the meta box
function print_TIP_post_options() {
	$nonce = wp_create_nonce('TIProtector');
	$id = $_REQUEST['post'];
	$TIP_protect_images_value = get_post_meta($id, '_TIP_protect_images_post', true);
	$TIP_protect_text_value = get_post_meta($id, '_TIP_protect_text_post', true);
	echo '
	<label for="TIP_protect_images_post">
		<input type="checkbox" name="TIP_protect_images_post" value="1" '.$TIP_protect_images_value.' />
		Protect Images
	</label><br />
	<label for="TIP_protect_text_post">
		<input type="checkbox" name="TIP_protect_text_post" value="1" '.$TIP_protect_text_value.' />
		Protect text
	</label>
	<input type="hidden" value="'.$nonce.'" name="TIProtector_nonce" />';
}

////////////////This part for the option page
//Layout of the option page
function print_TIP_option_page() {
	echo '
		<div class="wrap">
			<h2>Text & Image Protector</h2>';
	if ($_POST['submit']) {
		echo '<br />';
		update_option('TIP_protect_text', $_POST['text_protection']);
		update_option('TIP_protect_images', $_POST['image_protection']);
		
		if ($_POST['text_protection'] == 2) 
			echo '<font color="green">You have activated Text Protection for all of your posts</font><br />';
		elseif ($_POST['text_protection'] == 1)
			echo '<font color="blue">You have Activated Text Protection for the posts that you chose to be protected</font><br />';
		else
			echo '<font color="red">You have disactivated Text Protection for all of your posts</font><br />';
		
		if ($_POST['image_protection'] == 2) 
			echo '<font color="green">You have activated Image Protection for all of your posts</font><br />';
		elseif ($_POST['image_protection'] == 1)
			echo '<font color="blue">You have Activated Image Protection for the posts that you chose to be protected</font><br />';
		else
			echo '<font color="red">You have disactivated Image Protection for all of your posts</font><br />';
		
	}
	switch (get_option('TIP_protect_images')) {
		case 0:
			$image_none_post = "selected";
			break;
		case 1:
			$image_selected_posts = "selected";
			break;
		case 2:
			$image_all_posts = "selected";
			break;
	}
	switch (get_option('TIP_protect_text')){
		case 0:
			$text_none_post = "selected";
			break;
		case 1:
			$text_selected_posts = "selected";
			break;
		case 2:
			$text_all_posts = "selected";
			break;
	}	
	echo '
			<form method="post">
				<fieldset class="options">
					<table class="form-table">
						<tr valign="top">
							<td>
								<h3>Text Protection : </h3>
								<select name="text_protection">
									<option value="2" '.$text_all_posts.'>All posts</option>
									<option value="1" '.$text_selected_posts.'>Selected posts</option>
									<option value="0" '.$text_none_post.'>None of the posts</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<h3>Image Protection : </h3>
								<select name="image_protection">
									<option value="2" '.$image_all_posts.'>All posts</option>
									<option value="1" '.$image_selected_posts.'>Selected posts</option>
									<option value="0" '.$image_none_post.'>None of the posts</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<input type="submit" name="submit" value="update" />
							</td>
						</tr>
					</table>
					<h2 style="text-align: right">Powered By <a href="http://www.imaprogrammer.wordpress.com" target="_blank">Moein</a></h2>
				</fieldset>
			</form>
		</div>';
}

//The function for modifying the admin menu
function TIP_modify_admin_menu() {
	add_options_page('Text & Image Protector', 'Text & Image Protector', 'manage_options', basename(__FILE__), 'print_TIP_option_page');
}

//Modify options menu
add_action('admin_menu', 'TIP_modify_admin_menu');





?>
