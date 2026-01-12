<?php
/**
 * Template Name: Company page template
 * Template Post Type: page
 */
get_header();
?>

<main class="fondsen-page">
    <div class="container">
        <?php while ( have_posts() ) : the_post(); ?>

            <?php
            // Huidige pagina ID
            $post_id = get_the_ID();

            // Waarden uit custom fields halen
            $left_url  = get_post_meta( $post_id, 'header_left_url', true );
            $left_text = get_post_meta( $post_id, 'header_left_text', true );

            $right_url  = get_post_meta( $post_id, 'header_right_url', true );
            $right_text = get_post_meta( $post_id, 'header_right_text', true );

            // Fallbacks als er niets is ingevuld op de pagina
            if ( empty( $left_url ) ) {
                $left_url = '/#vacatures';
            }
            if ( empty( $left_text ) ) {
                $left_text = 'Vacatures';
            }
            if ( empty( $right_url ) ) {
                $right_url = '/job-alerts/';
            }
            if ( empty( $right_text ) ) {
                $right_text = 'Job alerts';
            }
            ?>

            <div class="header">

                <div class="header-content">

                    <div class="header-text">
                        <?php
                        if ( function_exists( 'yoast_breadcrumb' ) ) {
                            yoast_breadcrumb( '<p class="breadcrumbs">','</p>' );
                        }
                        ?>
                        <h2 class="header-title"><?php the_title(); ?></h2>
                        <p><?php the_excerpt(); ?></p>

                        <div class="buttons">
                            <a href="<?php echo esc_url( $left_url ); ?>" class="header-button-left">
                                <?php echo esc_html( $left_text ); ?>
                            </a>
                            <a href="<?php echo esc_url( $right_url ); ?>" class="header-button-right">
                                <?php echo esc_html( $right_text ); ?>
                            </a>
                        </div>

                    </div>

                </div>
            </div>

            <section class="">
                <?php the_content(); ?>
            </section>

        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>


<style>

.header {
    display: flex;
    margin: 0 auto;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 500px;
    background-color: #0884CC; 
}

.header-title {
    font-size: 36px;
    margin-bottom: 20px;
    font-family: Balgin-Bold;
    font-weight: 700;
}

.header-image {
    flex: 1;
    overflow: hidden;
    clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
    height: 500px;
    width: 40%;
}


.header-image img{
  width:100%;
  height:100%;
  object-fit:cover;
}


.header-content {
    flex: 1;
    padding: 20px;
    background-color: #0884CC; 
    height: 400px; 
    width: 60%;
}

.header-text {
    color: white;
    max-width: 80%;
    margin: 0 auto;
    padding-top: 60px;
}

.header-text p {
    font-size: 15px;
    line-height: 1.6;
    font-family: Poppins; 
    font-weight: 300;
}

.buttons {
    margin-top: 30px;
    display: flex;
    gap: 20px;
}

.header-button-left {
    background-color: #2ECC71 !important;
     color: white !important; 
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 0px;
    font-family: Balgin-Bold !important; 
    width: 200px;
    text-align: center;
}

.header-button-right {
    background-color: #FF8C2C !important;
    color: white !important;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 0px;
    font-family: Balgin-Bold !important; 
    width: 200px;
    text-align: center;
}


.breadcrumbs a{
    color: white !important;
}



</style>

<style>

@media (max-width: 767px) {

    .header {
        padding: 24px 18px;
        gap: 20px;
        border-radius: 0;             /* als je ‘m full-width wilt laten doorlopen */
        margin: 0 -16px 24px;         /* optioneel: naar schermrand trekken */
    }

    .header-text {
        text-align: left;
    }

    .header-title  {
        font-size: 1.6rem;
    }

    .header-text p {
        font-size: 0.95rem;
    }

    /* Afbeelding verbergen op mobiel */
    .header-image {
        display: none;
    }

    /* Buttons onder elkaar op mobiel */
    .buttons {
        flex-direction: column;
        width: 100%;
    }

    .header-button-left {
        width: 100%;
        text-align: center;
    }

     .header-button-right {
        width: 100%;
        text-align: center;
    }
}
</style>