<?php
/**
 * Template Name: vacature-categorie-template
 * Description: Template voor de bedrijven pagina
 */

get_header();
?>

<style>
.hero-section {
    position: relative;
    background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');
    background-size: cover;
    background-position: center;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
}

.hero-overlay {
    background: rgba(0, 0, 0, 0.5);
    padding: 40px;
    border-radius: 8px;
}

.hero-buttons a {
    display: inline-block;
    background-color: #FF8C2C;
    color: white;
    padding: 12px 20px;
    margin: 10px;
    border-radius: 5px;
    font-weight: 600;
    text-decoration: none;
}

.hero-buttons a:hover {
    background-color: #e07722;
}

.content-section {
    max-width: 1000px;
    margin: 60px auto;
    padding: 0 20px;
}

.content-blocks {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-top: 60px;
}

.content-block {
    flex: 1 1 300px;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.content-block img {
    max-width: 100%;
    border-radius: 8px;
    margin-bottom: 15px;
}

.content-block h3 {
    font-size: 20px;
    margin-bottom: 10px;
}

.content-block p {
    font-size: 14px;
    color: #555;
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-overlay">
        <h1><?php the_title(); ?></h1>
        <div class="hero-buttons">
            <a href="/job-alerts">📬 Job Alerts</a>
            <a href="/vacature-plaatsen">📢 Vacature plaatsen</a>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="content-section">
    <?php while (have_posts()) : the_post(); ?>
        <div class="page-content">
            <?php the_content(); ?> <!-- [jobs] shortcode wordt hier geladen -->
        </div>
    <?php endwhile; ?>
</section>

<!-- Extra Content Blocks -->
<section class="content-section">
    <div class="content-blocks">
        <div class="content-block">
            <img src="https://via.placeholder.com/400x200" alt="Foto 1">
            <h3>Werken aan een duurzame toekomst</h3>
            <p>Lees hoe jij kunt bijdragen aan de energietransitie met jouw talenten.</p>
        </div>
        <div class="content-block">
            <img src="https://via.placeholder.com/400x200" alt="Foto 2">
            <h3>Projecten in jouw regio</h3>
            <p>Ontdek duurzame vacatures bij organisaties bij jou in de buurt.</p>
        </div>
        <div class="content-block">
            <img src="https://via.placeholder.com/400x200" alt="Foto 3">
            <h3>Tips voor werkzoekenden</h3>
            <p>Handige artikelen en advies voor wie zoekt naar impactvol werk.</p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
