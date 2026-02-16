<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
<main class="ob-archive">
  <h1 class="ob-archive__title"><?php post_type_archive_title(); ?></h1>
  <div class="ob-archive__content">
    <?php echo do_shortcode('[overlijdensberichten]'); ?>
  </div>
</main>
<?php get_footer(); ?>
