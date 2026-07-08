<?php
// Get the main utilities
require_once('resources/wordpress-base.php');

// ----------------------------------------
// Funciones generales
// ----------------------------------------

// Posts data based on content type
function custom_posts_per_page($query)
{
	if (!is_admin() && isset($query->query_vars['post_type']))
	{
        switch ($query->query_vars['post_type'])
        {
			/*
			case 'news':
                $query->query_vars['posts_per_page'] = 6;
                $query->query_vars['order'] = 'DESC';
                $query->query_vars['orderby'] = 'date';
                break;
            case 'blog':
                $query->query_vars['posts_per_page'] = 4;
                $query->query_vars['order'] = 'DESC';
                $query->query_vars['orderby'] = 'date';
                break;
			*/
        }
        return $query;
    }
}

add_filter('pre_get_posts', 'custom_posts_per_page');

// Prueba temporal
ini_set('zlib.output_compression', '0');