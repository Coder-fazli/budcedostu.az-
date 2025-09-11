<?php
/*
Template Name: Valyuta Rates
*/

get_header(); ?>

<div class="valyuta-page-container">
    <div class="container">
        <?php
        // Display the valyuta rates using shortcode
        echo do_shortcode('[valyuta_rates currency="USD" show_cash="true"]');
        ?>
        
        <!-- Page Content -->
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php if (get_the_content()): ?>
                <div class="page-content">
                    <div class="content-wrapper">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.valyuta-page-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 40px 0;
}

.valyuta-page-container .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-top: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.content-wrapper {
    color: #495057;
    line-height: 1.6;
}

.content-wrapper h1,
.content-wrapper h2,
.content-wrapper h3 {
    color: #212529;
    margin-top: 0;
}

.content-wrapper p {
    margin-bottom: 16px;
}

@media (max-width: 768px) {
    .valyuta-page-container {
        padding: 20px 0;
    }
    
    .valyuta-page-container .container {
        padding: 0 15px;
    }
    
    .page-content {
        padding: 20px;
        margin-top: 20px;
    }
}
</style>

<?php get_footer(); ?>