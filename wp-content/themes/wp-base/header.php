<!DOCTYPE html>
<html lang="<?php echo get_bloginfo('language'); ?>">
<head>
	<!-- Main title -->
	<title><?php echo get_option('blogname').' '.wp_title('&raquo;'); ?></title>
	<!-- HTML Charset -->
	<meta charset="<?php echo get_option('blog_charset'); ?>">
	<!-- Mobile Enable -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2, shrink-to-fit=no">
	<!-- Nav Bar Mobile Color -->
	<meta name="theme-color" content="#7840a2">
	<meta name="msapplication-navbutton-color" content="#7840a2">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<!-- Meta Details -->
	<meta name="description" content="<?php echo get_option('blogdescription'); ?>">
	<meta name="keywords" content="html, html5, javascript, php, responsive, css3, bootstrap, css, wordpress">
	<meta name="author" content="WordPress Base">
	<!-- Favicon -->
	<link href="<?php echo get_bloginfo('template_url'); ?>/assets/img/favicon.png" rel="icon">
	<link href="<?php echo get_bloginfo('template_url'); ?>/assets/img/favicon-touch.png" rel="apple-touch-icon">
	<!-- Scripts & Stylesheets -->
	<link href="<?php echo get_bloginfo('template_url'); ?>/resources/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo get_bloginfo('template_url'); ?>/resources/fontawesome/css/all.min.css" rel="stylesheet">
	<link href="<?php echo get_bloginfo('template_url'); ?>/resources/glightbox/css/glightbox.min.css" rel="stylesheet">
	<link href="<?php echo get_bloginfo('template_url'); ?>/assets/css/style.css" rel="stylesheet">
	
	<?php
	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
	?>
</head>
<body>
<!-- Header -->
<header>
	<!-- Header Container  -->
	<div class="container">
		Header Content
	</div>
	<!-- Header Container (End) -->
</header>
<!-- Header (End) -->