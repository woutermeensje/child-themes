<?php
if (!defined('ABSPATH')) exit;

get_header(); ?>

<div class="vpjobs-page" style="max-width:1200px;margin:0 auto;padding:20px;">

  <article class="vpjobs-single">
    <header class="vpjobs-single-header">
      <h1><?php the_title(); ?></h1>

      <div class="vpjobs-meta">
        <?php $loc = get_post_meta(get_the_ID(), '_vp_location', true); ?>
        <?php if ($loc): ?><span class="vpjobs-pill"><?php echo esc_html($loc); ?></span><?php endif; ?>

        <?php
          $types = wp_get_post_terms(get_the_ID(), 'vp_job_type', [ 'fields' => 'names' ]);
          foreach ($types as $t) echo '<span class="vpjobs-pill">' . esc_html($t) . '</span>';
        ?>
      </div>
    </header>

    <div class="vpjobs-single-content">
      <?php the_content(); ?>
    </div>

    <?php
      $apply_url   = get_post_meta(get_the_ID(), '_vp_apply_url', true);
      $apply_email = get_post_meta(get_the_ID(), '_vp_apply_email', true);
    ?>

    <footer class="vpjobs-single-footer" style="margin-top:20px;">
      <?php if ($apply_url): ?>
        <a class="vpjobs-apply" href="<?php echo esc_url($apply_url); ?>" target="_blank" rel="noopener">Solliciteer</a>
      <?php elseif ($apply_email): ?>
        <a class="vpjobs-apply" href="mailto:<?php echo antispambot(esc_attr($apply_email)); ?>">Solliciteer</a>
      <?php endif; ?>
    </footer>
  </article>

</div>

<style>

    .vpjobs-single {
        width: 900px; 
        border: 1px solid #E0E0E0;
        border-radius: 5px;
        padding: 24px;
        margin: 24px auto;
        background-color: var(--color-bg);
    }
</style>

<?php get_footer();

