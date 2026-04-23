<?php

if (! defined('ABSPATH')) {
    exit;
}

class Rocksound_Divi_News_Module extends ET_Builder_Module
{
    public $slug = 'rocksound_divi_news';
    public $vb_support = 'on';

    public function init(): void
    {
        $this->name = esc_html__('Rocksound - Actualités', 'rocksound-divi-news');
        $this->icon_path = '';

        $this->main_css_element = '%%order_class%% .rocksound-news';
    }

    public function get_fields(): array
    {
        return [
            'posts_count' => [
                'label' => esc_html__('Nombre d\'actualités', 'rocksound-divi-news'),
                'type' => 'range',
                'range_settings' => [
                    'min' => 1,
                    'max' => 12,
                    'step' => 1,
                ],
                'default' => 5,
                'tab_slug' => 'general',
                'toggle_slug' => 'main_content',
            ],
            'excerpt_lines' => [
                'label' => esc_html__('Lignes d\'extrait', 'rocksound-divi-news'),
                'type' => 'select',
                'default' => '3',
                'options' => [
                    '2' => '2 lignes',
                    '3' => '3 lignes',
                    '4' => '4 lignes',
                    '5' => '5 lignes',
                ],
                'tab_slug' => 'general',
                'toggle_slug' => 'main_content',
            ],
            'show_date' => [
                'label' => esc_html__('Afficher la date', 'rocksound-divi-news'),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => esc_html__('Oui', 'rocksound-divi-news'),
                    'off' => esc_html__('Non', 'rocksound-divi-news'),
                ],
                'default' => 'on',
                'tab_slug' => 'general',
                'toggle_slug' => 'main_content',
            ],
            'category_slug' => [
                'label' => esc_html__('Slug catégorie (optionnel)', 'rocksound-divi-news'),
                'type' => 'text',
                'tab_slug' => 'general',
                'toggle_slug' => 'main_content',
                'description' => esc_html__('Exemple : actu-rock', 'rocksound-divi-news'),
            ],
        ];
    }

    public function render($attrs, $content = null, $render_slug = ''): string
    {
        $posts_count = isset($this->props['posts_count']) ? (int) $this->props['posts_count'] : 5;
        $excerpt_lines = isset($this->props['excerpt_lines']) ? (int) $this->props['excerpt_lines'] : 3;
        $show_date = $this->props['show_date'] ?? 'on';
        $category_slug = $this->props['category_slug'] ?? '';

        return do_shortcode(sprintf(
            '[rocksound_news posts="%d" lines="%d" show_date="%s" category="%s"]',
            max(1, min(12, $posts_count)),
            max(2, min(5, $excerpt_lines)),
            esc_attr($show_date === 'off' ? 'off' : 'on'),
            esc_attr(sanitize_title($category_slug))
        ));
    }
}
