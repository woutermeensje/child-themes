<?php
if (!defined('ABSPATH')) exit;

get_header();

if (have_posts()) : while (have_posts()) : the_post();
?>

<article class="mh-unit-single">

    <!-- Header -->
    <header class="mh-unit-single-header">
        <h1 class="mh-unit-single-title"><?php the_title(); ?></h1>
    </header>

    <!-- Top section: image + sidebar -->
    <div class="mh-unit-single-top">

        <div class="mh-unit-single-image">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large'); ?>
            <?php endif; ?>
        </div>

        <aside class="mh-unit-single-sidebar">
            <div class="mh-unit-sidebar-box">
                <h3>Interesse in deze unit?</h3>

                <p>
                    Neem contact op voor een bezichtiging of vrijblijvende offerte. 
                </p>

                <a class="mh-btn mh-btn-primary"
                   href="/offerte-aanvragen/?unit=<?php echo esc_attr(get_the_ID()); ?>">
                    Informatie aanvragen
                </a>
            </div>
        </aside>

    </div>

    <!-- Content -->
    <div class="mh-unit-single-content">
        <?php the_content(); ?>
    </div>

</article>

<footer class="main-footer">

<div class="footer-contact-advisors">
  <div class="footer-contact-advisors-content">
    <p>Kom in contact met één van onze adviseurs.</p>
  </div>

  <div class="footer-contact-advisors-link">
    <a href="/advies/" class="contact-link">Contact opnemen</a>
  </div>
  </div>
</footer>


<style>

  .main-footer {
    background-color: #0456AB;
    width: 100%;
  }

  .footer-contact-advisors {
    background-color: #0456AB;
    padding: 20px 0;
    text-align: center;
    margin: 0 auto;
    display: flex; 
    align-items: center;
    justify-content: center;
    justify-content: space-between;
    gap: 20px;
    width: 50%;
  }

.footer-contact-advisors-content p {
    margin: 0;
    font-size: 16px;
    color: #fff;
    font-family: Balgin Bold; 
    font-weight: 500;
  }

  .footer-contact-advisors-link {
    border: 1px solid #80D424;
    display: inline-block;
    border-radius: 5px;
    background-color: #80D424;
    font-family: Balgin Bold; 
    padding: 10px 20px;
    color: white; 
  }

</style>

<style>
/* ===== SINGLE UNIT LAYOUT ===== */

.mh-unit-single{
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 16px;
  font-family: 'Poppins', sans-serif;
  color: #333;
}

/* Header */
.mh-unit-single-header{
  margin-bottom: 24px;
}

.mh-unit-single-title{
  font-size: 24px;
  line-height: 1.2;
  margin: 0;
  color: #333; 
}

/* Top section */
.mh-unit-single-top{
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  margin-bottom: 40px;
}

/* Image */
.mh-unit-single-image{
  width: 100%;
  border-radius: 5px;
  overflow: hidden;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  height: 425px; 
}

.mh-unit-single-image img{
  width: 100%;
  height: auto;
  display: block;
}

/* Sidebar */
.mh-unit-single-sidebar{
  position: relative;
}

.mh-unit-sidebar-box{
  border: 1px solid #e5e5e5;
  border-radius: 5px;
  padding: 24px;
  background: #fff;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.08);
  position: sticky;
  top: 24px;
}

.mh-unit-sidebar-box h3{
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 20px;
  font-family: Balgin Bold; 
}

.mh-unit-sidebar-box p{
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 20px;
}

/* Content */
.mh-unit-single-content{
  max-width: 800px;
  font-size: 16px;
  line-height: 1.7;
  border: 1px solid #EDEDED;
  border-radius: 5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

.mh-unit-single-content h2,
.mh-unit-single-content h3{
  margin-top: 32px;
}

.mh-unit-single-content p{
  margin-bottom: 16px;
}

/* Buttons */
.mh-btn{
  display: inline-block;
  padding: 12px 18px;
  border-radius: 5px;
  border: 1px solid #ddd;
  text-decoration: none;
  font-size: 15px;
  font-family: Balgin Bold; 
  color: white; 
}

.mh-btn-primary{
  background: #0884CC;
  color: #fff;
  border-color: #0884CC;
}



/* ===== Responsive ===== */

@media (max-width: 900px){
  .mh-unit-single-top{
    grid-template-columns: 1fr;
  }

  .mh-unit-single-title{
    font-size: 28px;
  }

  .mh-unit-sidebar-box{
    position: relative;
    top: auto;
  }
}

@media (max-width: 600px){
  .mh-unit-single{
    margin-top: 24px;
  }

  .mh-unit-single-title{
    font-size: 24px;
  }
}
</style>

<?php
endwhile; endif;

get_footer();
