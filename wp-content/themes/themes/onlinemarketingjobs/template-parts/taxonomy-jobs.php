<?php
if (!defined('ABSPATH')) exit;

$term     = get_queried_object();
$taxonomy = $term->taxonomy ?? '';

$tax_attr_map = [
    'job_sector'       => 'job_sector',
    'job_listing_type' => 'job_listing_type',
    'organisatie_type' => 'organisatie_type',
];

$attr = $tax_attr_map[$taxonomy] ?? null;
?>

<div class="omj-tax-archive">
    <div class="omj-tax-archive__header">
        <h1 class="omj-tax-archive__title">
            <?php echo esc_html($term->name ?? ''); ?> vacatures
        </h1>
        <?php if (!empty($term->description)): ?>
            <p class="omj-tax-archive__description"><?php echo esc_html($term->description); ?></p>
        <?php endif; ?>
    </div>

    <div class="omj-tax-archive__listings">
        <?php
        if ($attr) {
            echo do_shortcode('[jobs ' . esc_attr($attr) . '="' . esc_attr($term->slug) . '" show_filters="true" per_page="20"]');
        }
        ?>
    </div>
</div>
