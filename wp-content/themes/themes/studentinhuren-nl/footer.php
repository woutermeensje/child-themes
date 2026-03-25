<?php
/**
 * Child theme footer.php — vervangt Hello Elementor's footer.php volledig.
 */
if (!defined('ABSPATH')) exit;

// Toon onze custom footer ALLEEN als Elementor Pro geen eigen footer heeft ingesteld.
if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('footer')) {
    include get_stylesheet_directory() . '/template-parts/footer.php';
}
?>

<?php wp_footer(); ?>

</body>
</html>
