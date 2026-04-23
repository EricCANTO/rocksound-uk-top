<?php
/**
 * Plugin Name: Rocksound - Module Divi Actualités
 * Description: Ajoute un module Divi pour afficher les actualités récentes avec image, extrait (2 à 5 lignes) et date.
 * Version: 1.0.0
 * Author: Rocksound
 * Text Domain: rocksound-divi-news
 */

if (! defined('ABSPATH')) {
    exit;
}

final class Rocksound_Divi_News_Plugin
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode('rocksound_news', [$this, 'render_shortcode']);
        add_action('et_builder_ready', [$this, 'register_divi_module']);
    }

    public function enqueue_assets(): void
    {
        wp_register_style(
            'rocksound-divi-news',
            plugin_dir_url(__FILE__) . 'assets/rocksound-news.css',
            [],
            '1.0.0'
        );
    }

    public function register_divi_module(): void
    {
        if (! class_exists('ET_Builder_Module')) {
            return;
        }

        require_once plugin_dir_path(__FILE__) . 'includes/class-rocksound-news-module.php';

        new Rocksound_Divi_News_Module();
    }

    public function render_shortcode(array $atts = []): string
    {
        $atts = shortcode_atts([
            'posts' => 5,
            'lines' => 3,
            'show_date' => 'on',
            'category' => '',
        ], $atts, 'rocksound_news');

        $posts_count = max(1, min(12, (int) $atts['posts']));
        $lines = max(2, min(5, (int) $atts['lines']));
        $show_date = $atts['show_date'] === 'off' ? 'off' : 'on';

        wp_enqueue_style('rocksound-divi-news');

        $query_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_count,
            'ignore_sticky_posts' => true,
        ];

        if (! empty($atts['category'])) {
            $query_args['category_name'] = sanitize_text_field($atts['category']);
        }

        $news_query = new WP_Query($query_args);

        if (! $news_query->have_posts()) {
            return '<div class="rocksound-news rocksound-news--empty">Aucune actualité disponible pour le moment.</div>';
        }

        ob_start();
        ?>
        <div class="rocksound-news" style="--rocksound-lines: <?php echo esc_attr((string) $lines); ?>;">
            <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                <article class="rocksound-news__item">
                    <?php if (has_post_thumbnail()) : ?>
                        <a class="rocksound-news__image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                            <?php the_post_thumbnail('medium_large', ['loading' => 'lazy']); ?>
                        </a>
                    <?php endif; ?>

                    <div class="rocksound-news__content">
                        <h3 class="rocksound-news__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="rocksound-news__excerpt"><?php echo esc_html(wp_strip_all_tags(get_the_excerpt())); ?></p>
                        <?php if ($show_date === 'on') : ?>
                            <time class="rocksound-news__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date()); ?>
                            </time>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php

        wp_reset_postdata();

        return (string) ob_get_clean();
    }
}

new Rocksound_Divi_News_Plugin();
