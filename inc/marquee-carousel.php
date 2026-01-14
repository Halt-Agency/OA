<?php
/**
 * Marquee Carousel Shortcode
 * Usage: [marquee_carousel repeater="test_repeater" image="image" speed="30"]
 */
function dt_marquee_carousel_shortcode($atts) {
    $atts = shortcode_atts(array(
        'repeater' => 'test_repeater',
        'image' => 'image',
        'speed' => '30',
        'max_width' => '200',
    ), $atts);
    
    $repeater = get_field($atts['repeater']);
    $image_field = $atts['image'];
    
    if (!$repeater || !is_array($repeater)) {
        return '';
    }
    
    ob_start();
    ?>
    <style>
    .marquee-container-<?php echo esc_attr($atts['repeater']); ?> {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 40px 0;
    }

    .marquee-wrapper-<?php echo esc_attr($atts['repeater']); ?> {
        display: flex;
        width: 200%;
    }

    .marquee-track-<?php echo esc_attr($atts['repeater']); ?> {
        display: flex;
        gap: 30px;
        animation: marquee-scroll-<?php echo esc_attr($atts['repeater']); ?> <?php echo esc_attr($atts['speed']); ?>s linear infinite;
        width: 50%;
    }

    .marquee-track-duplicate-<?php echo esc_attr($atts['repeater']); ?> {
        display: flex;
        gap: 30px;
        width: 50%;
    }

    .marquee-track-<?php echo esc_attr($atts['repeater']); ?>:hover,
    .marquee-track-duplicate-<?php echo esc_attr($atts['repeater']); ?>:hover {
        animation-play-state: paused;
    }

    .marquee-item-<?php echo esc_attr($atts['repeater']); ?> {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .marquee-item-<?php echo esc_attr($atts['repeater']); ?> img {
        max-width: <?php echo esc_attr($atts['max_width']); ?>px;
        height: auto;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .marquee-item-<?php echo esc_attr($atts['repeater']); ?> img:hover {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }

    @keyframes marquee-scroll-<?php echo esc_attr($atts['repeater']); ?> {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-100%);
        }
    }

    @media (max-width: 768px) {
        .marquee-item-<?php echo esc_attr($atts['repeater']); ?> img {
            max-width: 150px;
        }
        
        .marquee-track-<?php echo esc_attr($atts['repeater']); ?> {
            gap: 20px;
        }
    }
    </style>

    <div class="marquee-container-<?php echo esc_attr($atts['repeater']); ?>">
        <div class="marquee-wrapper-<?php echo esc_attr($atts['repeater']); ?>">
            <div class="marquee-track-<?php echo esc_attr($atts['repeater']); ?>">
                <?php 
                foreach ($repeater as $row) {
                    if (isset($row[$image_field])) {
                        $image = $row[$image_field];
                        // Handle both array format and ID format
                        if (is_array($image) && isset($image['url'])) {
                            $image_url = $image['url'];
                            $image_alt = isset($image['alt']) ? $image['alt'] : '';
                            $image_title = isset($image['title']) ? $image['title'] : '';
                        } elseif (is_numeric($image)) {
                            $image_url = wp_get_attachment_image_url($image, 'full');
                            $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true);
                            $image_title = get_the_title($image);
                        } else {
                            continue;
                        }
                        ?>
                        <div class="marquee-item-<?php echo esc_attr($atts['repeater']); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr($image_alt); ?>"
                                 title="<?php echo esc_attr($image_title); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="marquee-track-duplicate-<?php echo esc_attr($atts['repeater']); ?>">
                <?php 
                // Duplicate for seamless loop
                foreach ($repeater as $row) {
                    if (isset($row[$image_field])) {
                        $image = $row[$image_field];
                        if (is_array($image) && isset($image['url'])) {
                            $image_url = $image['url'];
                            $image_alt = isset($image['alt']) ? $image['alt'] : '';
                            $image_title = isset($image['title']) ? $image['title'] : '';
                        } elseif (is_numeric($image)) {
                            $image_url = wp_get_attachment_image_url($image, 'full');
                            $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true);
                            $image_title = get_the_title($image);
                        } else {
                            continue;
                        }
                        ?>
                        <div class="marquee-item-<?php echo esc_attr($atts['repeater']); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr($image_alt); ?>"
                                 title="<?php echo esc_attr($image_title); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('marquee_carousel', 'dt_marquee_carousel_shortcode');
