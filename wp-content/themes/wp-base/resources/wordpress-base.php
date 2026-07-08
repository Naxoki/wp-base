<?php
// ----------------------------------------
// Helpers booleanos
// ----------------------------------------

// Check login page
function is_wplogin() {
    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF']== '/wp-login.php');
}

// ----------------------------------------
// Helpers de retorno
// ----------------------------------------

// Get image data from custom field or taxonomy
function get_image_data($image_id, $size = 'full', $field = null)
{
	$image_data = wp_get_attachment_image_src($image_id, $size, false); 
    
    switch($field)
    {
        // Base
        case 'url':
            return $image_data[0]; // $image['url']
            break;
        case 'width':
            return $image_data[1];
            break;
        case 'height':
            return $image_data[2];
            break;
        // Custom
        case 'pos-x':
            $image_x = get_post_meta($image_id, 'imagen_alineacion_horizontal', true);
            return $image_x.(!empty($image_x) ? '%' : 'center');
            break;
        case 'pos-y':
            $image_y = get_post_meta($image_id, 'imagen_alineacion_vertical', true);    
            return $image_y.(!empty($image_y) ? '%' : 'center');
            break;
        case 'zoom':
            $image_z_type = get_post_meta($image_id, 'imagen_relleno_contenedor', true);
            $image_z_zoom = get_post_meta($image_id, 'imagen_relleno_zoom', true);
            return $image_z_type == 'zoom' ? $image_z_zoom.(!empty($image_z_zoom) ? '%' : '') : $image_z_type;
            break;
        default: 
            return get_post_meta($image_id, $field, true);
            break;
    }
}

// Get the slug inside post
function get_the_slug($id = null)
{
	if(empty($id))
	{
		global $post;
		if(empty($post))
		{
			return ''; // No global $post var available.
		}
		$id = $post->ID;
	}
	$slug = basename(get_permalink($id));
	return $slug;
}

// Get the id by slug
function get_id_by_slug($slug)
{
	global $wpdb;
	$id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$slug."'");
	return $id;
}

// ----------------------------------------
// Helpers de filtro
// ----------------------------------------

// Custom login logo URL
function login_logo_url()
{
    return home_url();
}

add_filter('login_headerurl', 'login_logo_url');
add_filter('login_headertitle', 'login_logo_url');

// ----------------------------------------
// Helpers de accion
// ----------------------------------------

// Add custom CSS & JS to admin
function add_custom_admin() 
{
	echo '<link href="'.get_bloginfo('template_url').'/css/admin.css" rel="stylesheet">';
	echo '<script src="'.get_bloginfo('template_url').'/js/admin.js"></script>';
}

if((is_user_logged_in() && is_admin()) || is_wplogin())
{
	add_action('admin_footer', 'add_custom_admin');
	add_action('login_footer', 'add_custom_admin');
	add_action('wp_footer', 'add_custom_admin');
}
