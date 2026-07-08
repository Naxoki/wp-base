<!-- Footer -->
<footer>
	<!-- Footer Container  -->
	<div class="container">
		Footer Content
	</div>
	<!-- Footer Container (End) -->
</footer>
<!-- Footer (End) -->

<!-- Scripts -->
<script src="<?php echo get_bloginfo('template_url'); ?>/resources/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo get_bloginfo('template_url'); ?>/resources/glightbox/js/glightbox.min.js"></script>
<script src="<?php echo get_bloginfo('template_url'); ?>/assets/js/app.js"></script>

<?php
/* Always have wp_footer() just before the closing </body>
 * tag of your theme, or you will break many plugins, which
 * generally use this hook to reference JavaScript files.
 */
wp_footer();
?>
</body>
</html>