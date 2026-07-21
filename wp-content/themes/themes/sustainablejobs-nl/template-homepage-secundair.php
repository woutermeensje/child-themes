<?php
/*
Template Name: template-homepage-secundair
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$homepage_secundair_css_path = get_stylesheet_directory() . '/css/homepage-secundair.css';
if ( file_exists( $homepage_secundair_css_path ) ) {
    wp_enqueue_style(
        'sj-homepage-secundair',
        get_stylesheet_directory_uri() . '/css/homepage-secundair.css',
        [ 'child-style' ],
        filemtime( $homepage_secundair_css_path )
    );
}

if ( ! function_exists( 'sj_homepage_secundair_get_named_upload_image_url' ) ) {
    function sj_homepage_secundair_get_named_upload_image_url( $basename ) {
        $basename = sanitize_title( $basename );
        if ( ! $basename ) {
            return '';
        }

        $attachment = get_page_by_path( $basename, OBJECT, 'attachment' );
        if ( $attachment instanceof WP_Post ) {
            $url = wp_get_attachment_image_url( $attachment->ID, 'full' );
            if ( $url ) {
                return $url;
            }
        }

        global $wpdb;
        $like_with_dir = '%/' . $wpdb->esc_like( $basename . '.' ) . '%';
        $like_filename = $wpdb->esc_like( $basename . '.' ) . '%';
        $attachment_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
                 WHERE meta_key = '_wp_attached_file'
                   AND (meta_value LIKE %s OR meta_value LIKE %s)
                 ORDER BY post_id DESC
                 LIMIT 1",
                $like_with_dir,
                $like_filename
            )
        );

        if ( $attachment_id ) {
            $url = wp_get_attachment_image_url( $attachment_id, 'full' );
            if ( $url ) {
                return $url;
            }
        }

        $upload_dir = wp_get_upload_dir();
        if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['basedir'] ) || empty( $upload_dir['baseurl'] ) ) {
            return '';
        }

        foreach ( [ 'jpg', 'jpeg', 'png', 'webp' ] as $extension ) {
            $relative_path = '2026/06/' . $basename . '.' . $extension;
            $absolute_path = trailingslashit( $upload_dir['basedir'] ) . $relative_path;
            if ( file_exists( $absolute_path ) ) {
                return trailingslashit( $upload_dir['baseurl'] ) . $relative_path;
            }
        }

        return '';
    }
}

$hero_image = sj_homepage_secundair_get_named_upload_image_url( 'sustainablejobs-nl-duurzame-vacaturesite' );
$hero_job_count = function_exists( 'sj_get_open_job_listing_count' ) ? sj_get_open_job_listing_count() : 0;

get_header();
?>

<main id="content" class="site-main sj-homepage-secundair">
    <section class="sj-job-hero"<?php if ( $hero_image ) : ?> style="background-image: url('<?php echo esc_url( $hero_image ); ?>');"<?php endif; ?>>
        <div class="sj-job-hero__inner">
            <span class="sj-job-hero__eyebrow">Sustainablejobs.nl</span>
            <div class="sj-job-hero__title-wrap">
                <h1 class="sj-job-hero__title">Vacaturesite binnen de duurzaamheid, energietransitie, ecologie en banen met een positieve impact.</h1>
            </div>
            <p class="sj-job-hero__subtitle">Bekijk alle <a href="<?php echo esc_url( home_url( '/vacatures/#job_listings' ) ); ?>" class="sj-job-hero__vacatures-link"><span class="sj-hero-job-count sj-job-hero__accent"><?php echo esc_html( number_format_i18n( $hero_job_count ) ); ?></span> vacatures</a> of meld je direct aan voor onze <a href="<?php echo esc_url( home_url( '/nieuwsbrief/' ) ); ?>" class="sj-job-hero__link">vacature nieuwsbrief</a>.</p>
        </div>
    </section>

    <div class="sj-homepage-secundair__content">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</main>

<?php
get_footer();
