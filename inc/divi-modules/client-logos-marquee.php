<?php
if (!defined('ABSPATH')) {
    exit;
}

function dt_register_client_logos_marquee_module() {
    if (!class_exists('ET_Builder_Module')) {
        return;
    }

    class DT_Client_Logos_Marquee extends ET_Builder_Module {
        public $slug = 'dt_client_logos_marquee';
        public $vb_support = 'on';

        public function init() {
            $this->name = esc_html__('Client Logos Marquee', 'oa');
        }

        public function get_fields() {
            return array(
                'exclude_slugs' => array(
                    'label'           => esc_html__('Exclude Slugs', 'oa'),
                    'type'            => 'text',
                    'option_category' => 'basic_option',
                    'description'     => esc_html__('Comma-separated client slugs to exclude.', 'oa'),
                ),
                'speed' => array(
                    'label'           => esc_html__('Speed (seconds)', 'oa'),
                    'type'            => 'range',
                    'option_category' => 'basic_option',
                    'range_settings'  => array(
                        'min'  => 5,
                        'max'  => 120,
                        'step' => 1,
                    ),
                    'default'         => 30,
                ),
                'direction' => array(
                    'label'           => esc_html__('Direction', 'oa'),
                    'type'            => 'select',
                    'option_category' => 'basic_option',
                    'options'         => array(
                        'left'  => esc_html__('Left', 'oa'),
                        'right' => esc_html__('Right', 'oa'),
                    ),
                    'default'         => 'left',
                ),
            );
        }

        public function render($attrs, $content, $render_slug) {
            $exclude_raw = isset($this->props['exclude_slugs']) ? $this->props['exclude_slugs'] : '';
            $exclude = array();
            if (!empty($exclude_raw)) {
                $parts = array_map('trim', explode(',', $exclude_raw));
                foreach ($parts as $part) {
                    if ($part !== '') {
                        $exclude[] = sanitize_title($part);
                    }
                }
            }

            $speed = isset($this->props['speed']) ? (float) $this->props['speed'] : 30;
            if ($speed <= 0) {
                $speed = 30;
            }
            $direction = isset($this->props['direction']) ? $this->props['direction'] : 'left';
            $direction = $direction === 'right' ? 'right' : 'left';

            $query = new WP_Query(array(
                'post_type'      => 'clients',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post_name__not_in' => $exclude,
                'orderby'        => array(
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ),
            ));

            if (!$query->have_posts()) {
                return '';
            }

            $items = array();
            while ($query->have_posts()) {
                $query->the_post();
                $logo = function_exists('get_field') ? get_field('client_logo', get_the_ID()) : null;
                if (is_array($logo) && isset($logo['url'])) {
                    $items[] = array(
                        'url' => $logo['url'],
                        'alt' => isset($logo['alt']) ? $logo['alt'] : '',
                        'title' => isset($logo['title']) ? $logo['title'] : '',
                    );
                } elseif (is_numeric($logo)) {
                    $items[] = array(
                        'url' => wp_get_attachment_image_url($logo, 'full'),
                        'alt' => get_post_meta($logo, '_wp_attachment_image_alt', true),
                        'title' => get_the_title($logo),
                    );
                }
            }
            wp_reset_postdata();

            if (empty($items)) {
                return '';
            }

            $uid = 'dt-client-marquee-' . wp_generate_uuid4();
            $direction_css = $direction === 'right' ? 'reverse' : 'normal';

            ob_start();
            ?>
            <style>
            .<?php echo esc_attr($uid); ?> {
                width: 100%;
                overflow: hidden;
                position: relative;
                padding: 40px 0;
            }

            .<?php echo esc_attr($uid); ?> .marquee-wrapper {
                display: flex;
                width: 200%;
            }

            .<?php echo esc_attr($uid); ?> .marquee-track,
            .<?php echo esc_attr($uid); ?> .marquee-track-duplicate {
                display: flex;
                gap: 30px;
                width: 50%;
            }

            .<?php echo esc_attr($uid); ?> .marquee-track {
                animation: <?php echo esc_attr($uid); ?>-scroll <?php echo esc_attr($speed); ?>s linear infinite;
                animation-direction: <?php echo esc_attr($direction_css); ?>;
            }

            .<?php echo esc_attr($uid); ?> .marquee-track:hover,
            .<?php echo esc_attr($uid); ?> .marquee-track-duplicate:hover {
                animation-play-state: paused;
            }

            .<?php echo esc_attr($uid); ?> .marquee-item {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .<?php echo esc_attr($uid); ?> .marquee-item img {
                max-width: 200px;
                height: auto;
                object-fit: contain;
                filter: grayscale(100%);
                opacity: 0.7;
                transition: all 0.3s ease;
            }

            .<?php echo esc_attr($uid); ?> .marquee-item img:hover {
                filter: grayscale(0%);
                opacity: 1;
                transform: scale(1.05);
            }

            @keyframes <?php echo esc_attr($uid); ?>-scroll {
                0% {
                    transform: translateX(0);
                }
                100% {
                    transform: translateX(-100%);
                }
            }

            @media (max-width: 768px) {
                .<?php echo esc_attr($uid); ?> .marquee-item img {
                    max-width: 150px;
                }
                .<?php echo esc_attr($uid); ?> .marquee-track,
                .<?php echo esc_attr($uid); ?> .marquee-track-duplicate {
                    gap: 20px;
                }
            }
            </style>

            <div class="<?php echo esc_attr($uid); ?>">
                <div class="marquee-wrapper">
                    <div class="marquee-track">
                        <?php foreach ($items as $item) : ?>
                            <div class="marquee-item">
                                <img src="<?php echo esc_url($item['url']); ?>"
                                     alt="<?php echo esc_attr($item['alt']); ?>"
                                     title="<?php echo esc_attr($item['title']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="marquee-track-duplicate">
                        <?php foreach ($items as $item) : ?>
                            <div class="marquee-item">
                                <img src="<?php echo esc_url($item['url']); ?>"
                                     alt="<?php echo esc_attr($item['alt']); ?>"
                                     title="<?php echo esc_attr($item['title']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    }

    new DT_Client_Logos_Marquee();
}
add_action('et_builder_ready', 'dt_register_client_logos_marquee_module');
