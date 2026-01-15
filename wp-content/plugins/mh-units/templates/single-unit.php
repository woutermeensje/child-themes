<?php if (!defined('ABSPATH')) exit; ?>

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
                    Vraag vrijblijvend een offerte aan of neem contact met ons op
                    voor beschikbaarheid, levering en transport.
                </p>

                <a class="mh-btn mh-btn-primary"
                   href="/contact/?unit=<?php echo esc_attr(get_the_ID()); ?>">
                    Offerte aanvragen
                </a>
            </div>
        </aside>

    </div>

    <!-- Content -->
    <div class="mh-unit-single-content">
        <?php the_content(); ?>
    </div>

</article>

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
  font-size: 34px;
  line-height: 1.2;
  margin: 0;
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
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
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
  border-radius: 12px;
  padding: 24px;
  background: #fff;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
  position: sticky;
  top: 24px;
}

.mh-unit-sidebar-box h3{
  margin-top: 0;
  margin-bottom: 12px;
  font-size: 20px;
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
  border-radius: 8px;
  border: 1px solid #ddd;
  text-decoration: none;
  font-size: 15px;
}

.mh-btn-primary{
  background: #0884CC;
  color: #fff;
  border-color: #0884CC;
}

.mh-btn-primary:hover{
  opacity: .9;
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
