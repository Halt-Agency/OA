<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render OA header menu from ACF options.
 */
function oa_get_header_menu_markup($atts = []) {
    if (!function_exists('get_field')) {
        return '';
    }

    $atts = shortcode_atts([
        'class' => '',
    ], $atts, 'oa_header_menu');

    $menu_items = get_field('header_menu_items', 'option');
    $end_buttons = get_field('header_menu_end_buttons', 'option');
    if (!is_array($menu_items)) {
        $menu_items = [];
    }
    if (empty($menu_items) && empty($end_buttons)) {
        return '';
    }
    if (!is_array($end_buttons)) {
        $end_buttons = [];
    }

    $render_items = [];
    foreach ($menu_items as $menu_item) {
        if (!is_array($menu_item)) {
            continue;
        }
        $menu_item['__end_button_position'] = '';
        $render_items[] = $menu_item;
    }
    foreach (array_slice($end_buttons, 0, 2) as $button_index => $button_item) {
        if (!is_array($button_item)) {
            continue;
        }
        $button_item['__end_button_position'] = $button_index === 0 ? 'first' : 'second';
        $render_items[] = $button_item;
    }

    $wrapper_classes = ['oa-header-menu'];
    if (!empty($atts['class']) && is_string($atts['class'])) {
        $wrapper_classes[] = sanitize_html_class($atts['class']);
    }

    ob_start();
    ?>
    <nav class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" aria-label="Header menu" data-oa-header-menu>
        <button class="oa-header-menu__toggle" type="button" aria-expanded="false" data-oa-header-toggle>
            Menu
        </button>

        <ul class="oa-header-menu__items" data-oa-header-items>
            <?php foreach ($render_items as $index => $item) :
                $label = isset($item['label']) ? trim((string) $item['label']) : '';
                $link = isset($item['link']) && is_array($item['link']) ? $item['link'] : null;
                $style = isset($item['style']) ? (string) $item['style'] : 'default';
                $end_button_position = isset($item['__end_button_position']) ? (string) $item['__end_button_position'] : '';
                $is_end_button = $end_button_position !== '';
                if ($is_end_button) {
                    $style = 'default';
                }
                $submenu_type = isset($item['submenu_type']) ? (string) $item['submenu_type'] : 'none';
                $has_dropdown = $submenu_type === 'dropdown' && !empty($item['dropdown_links']) && is_array($item['dropdown_links']);
                $has_mega = $submenu_type === 'mega' && !empty($item['mega_columns']) && is_array($item['mega_columns']);
                $has_submenu = $has_dropdown || $has_mega;
                $item_id = 'oa-header-item-' . (int) $index;

                if ($label === '') {
                    continue;
                }

                $item_classes = ['oa-header-menu__item'];
                $item_classes[] = 'oa-header-menu__item--' . sanitize_html_class($style);
                if ($has_submenu) {
                    $item_classes[] = 'has-submenu';
                }
                if ($has_mega) {
                    $item_classes[] = 'has-mega';
                }
                if ($is_end_button) {
                    $item_classes[] = 'oa-header-menu__item--end-button';
                    $item_classes[] = 'oa-header-menu__item--end-button-' . sanitize_html_class($end_button_position);
                }
                ?>
                <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" data-oa-menu-item>
                    <div class="oa-header-menu__trigger-wrap">
                        <?php
                        $trigger_text = esc_html($label);
                        $trigger_classes = $has_submenu ? ['oa-header-menu__trigger'] : ['oa-header-menu__link'];
                        if ($is_end_button) {
                            $trigger_classes[] = 'oa-header-menu__end-button';
                            $trigger_classes[] = 'oa-header-menu__end-button--' . sanitize_html_class($end_button_position);
                        }
                        if ($has_submenu) {
                            ?>
                            <button
                                class="<?php echo esc_attr(implode(' ', $trigger_classes)); ?>"
                                type="button"
                                aria-expanded="false"
                                aria-controls="<?php echo esc_attr($item_id); ?>"
                                data-oa-menu-trigger
                            >
                                <span><?php echo $trigger_text; ?></span>
                                <span class="oa-header-menu__caret" aria-hidden="true">&#9662;</span>
                            </button>
                            <?php
                        } elseif (!empty($link['url'])) {
                            ?>
                            <a
                                class="<?php echo esc_attr(implode(' ', $trigger_classes)); ?>"
                                href="<?php echo esc_url($link['url']); ?>"
                                <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>"<?php endif; ?>
                            >
                                <?php echo $trigger_text; ?>
                            </a>
                            <?php
                        } else {
                            $trigger_classes[] = 'oa-header-menu__link--static';
                            ?>
                            <span class="<?php echo esc_attr(implode(' ', $trigger_classes)); ?>"><?php echo $trigger_text; ?></span>
                            <?php
                        }
                        ?>
                    </div>

                    <?php if ($has_dropdown) : ?>
                        <div class="oa-header-menu__panel oa-header-menu__panel--dropdown" id="<?php echo esc_attr($item_id); ?>" data-oa-menu-panel>
                            <ul class="oa-header-menu__dropdown-list">
                                <?php foreach ($item['dropdown_links'] as $dropdown_row) :
                                    $dropdown_link = isset($dropdown_row['link']) && is_array($dropdown_row['link']) ? $dropdown_row['link'] : null;
                                    if (empty($dropdown_link['url'])) {
                                        continue;
                                    }
                                    ?>
                                    <li class="oa-header-menu__dropdown-item">
                                        <a
                                            href="<?php echo esc_url($dropdown_link['url']); ?>"
                                            <?php if (!empty($dropdown_link['target'])) : ?>target="<?php echo esc_attr($dropdown_link['target']); ?>"<?php endif; ?>
                                        >
                                            <?php echo esc_html(!empty($dropdown_link['title']) ? $dropdown_link['title'] : $dropdown_link['url']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($has_mega) :
                        $left_mode = isset($item['mega_left_mode']) ? (string) $item['mega_left_mode'] : 'cards';
                        $is_resources_layout = $left_mode === 'resources';
                        $left_heading = isset($item['mega_left_heading']) ? trim((string) $item['mega_left_heading']) : '';
                        $left_view_more = isset($item['mega_left_view_more']) && is_array($item['mega_left_view_more']) ? $item['mega_left_view_more'] : null;
                        $left_cards = !empty($item['mega_left_cards']) && is_array($item['mega_left_cards']) ? $item['mega_left_cards'] : [];
                        $contact_image = isset($item['mega_contact_image']) ? $item['mega_contact_image'] : null;
                        $contact_button = isset($item['mega_contact_button']) && is_array($item['mega_contact_button']) ? $item['mega_contact_button'] : null;
                        $resources_taxonomy = isset($item['mega_resources_taxonomy']) ? (string) $item['mega_resources_taxonomy'] : 'category';
                        $resources_term_value = $resources_taxonomy === 'post_tag'
                            ? ($item['mega_resources_tag'] ?? 0)
                            : ($item['mega_resources_category'] ?? 0);
                        if (is_array($resources_term_value)) {
                            $resources_term_value = reset($resources_term_value);
                        }
                        $resources_term_id = (int) $resources_term_value;
                        $resources_cards = [];

                        if ($is_resources_layout && $resources_term_id > 0 && taxonomy_exists($resources_taxonomy)) {
                            $resources_query = new WP_Query([
                                'post_type'           => 'post',
                                'post_status'         => 'publish',
                                'posts_per_page'      => 2,
                                'orderby'             => 'date',
                                'order'               => 'DESC',
                                'ignore_sticky_posts' => true,
                                'no_found_rows'       => true,
                                'tax_query'           => [
                                    [
                                        'taxonomy' => $resources_taxonomy,
                                        'field'    => 'term_id',
                                        'terms'    => [$resources_term_id],
                                    ],
                                ],
                            ]);

                            if ($resources_query->have_posts()) {
                                while ($resources_query->have_posts()) {
                                    $resources_query->the_post();
                                    $post_id = get_the_ID();
                                    $post_image = function_exists('get_field') ? get_field('post_image', $post_id) : null;
                                    $image_url = '';
                                    $image_alt = '';

                                    if (is_array($post_image) && !empty($post_image['url'])) {
                                        $image_url = (string) $post_image['url'];
                                        $image_alt = isset($post_image['alt']) ? (string) $post_image['alt'] : '';
                                    } elseif (is_numeric($post_image)) {
                                        $post_image_id = (int) $post_image;
                                        $image_url = wp_get_attachment_image_url($post_image_id, 'large') ?: '';
                                        $image_alt = (string) get_post_meta($post_image_id, '_wp_attachment_image_alt', true);
                                    } elseif (is_string($post_image) && $post_image !== '') {
                                        $image_url = $post_image;
                                    }

                                    if ($image_url === '') {
                                        $image_id = get_post_thumbnail_id($post_id);
                                        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
                                        $image_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
                                    }

                                    $resources_cards[] = [
                                        'url'   => get_permalink($post_id),
                                        'title' => get_the_title($post_id),
                                        'image' => [
                                            'url' => $image_url ?: '',
                                            'alt' => is_string($image_alt) ? $image_alt : '',
                                        ],
                                    ];
                                }
                                wp_reset_postdata();
                            }
                        }

                        $columns_to_render = !empty($item['mega_columns']) && is_array($item['mega_columns']) ? $item['mega_columns'] : [];
                        if ($is_resources_layout && !empty($columns_to_render)) {
                            $columns_to_render = array_slice($columns_to_render, 0, 1);
                        }
                        ?>
                        <div class="oa-header-menu__panel oa-header-menu__panel--mega" id="<?php echo esc_attr($item_id); ?>" data-oa-menu-panel>
                            <div class="oa-header-menu__mega-grid">
                                <div class="oa-header-menu__mega-left oa-header-menu__mega-left--<?php echo esc_attr(sanitize_html_class($left_mode)); ?>">
                                    <?php if ($left_heading !== '') : ?>
                                        <div class="oa-header-menu__mega-head">
                                            <h4><?php echo esc_html($left_heading); ?></h4>
                                            <?php if (!empty($left_view_more['url'])) : ?>
                                                <a
                                                    href="<?php echo esc_url($left_view_more['url']); ?>"
                                                    <?php if (!empty($left_view_more['target'])) : ?>target="<?php echo esc_attr($left_view_more['target']); ?>"<?php endif; ?>
                                                >
                                                    <?php echo esc_html(!empty($left_view_more['title']) ? $left_view_more['title'] : 'View more'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($is_resources_layout) : ?>
                                        <?php if (!empty($resources_cards)) : ?>
                                            <div class="oa-header-menu__cards">
                                                <?php foreach ($resources_cards as $resource_card) :
                                                    $resource_image = isset($resource_card['image']) && is_array($resource_card['image']) ? $resource_card['image'] : null;
                                                    $resource_title = isset($resource_card['title']) ? trim((string) $resource_card['title']) : '';
                                                    $resource_url = isset($resource_card['url']) ? (string) $resource_card['url'] : '';
                                                    ?>
                                                    <a class="oa-header-menu__card" href="<?php echo esc_url($resource_url); ?>">
                                                        <?php if (!empty($resource_image['url'])) : ?>
                                                            <img src="<?php echo esc_url($resource_image['url']); ?>" alt="<?php echo esc_attr(isset($resource_image['alt']) ? $resource_image['alt'] : ''); ?>">
                                                        <?php endif; ?>
                                                        <?php if ($resource_title !== '') : ?>
                                                            <span><?php echo esc_html($resource_title); ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif (!empty($left_cards)) : ?>
                                            <div class="oa-header-menu__cards">
                                                <?php foreach ($left_cards as $card_row) :
                                                    $card_image = isset($card_row['image']) && is_array($card_row['image']) ? $card_row['image'] : null;
                                                    $card_title = isset($card_row['title']) ? trim((string) $card_row['title']) : '';
                                                    $card_link = isset($card_row['link']) && is_array($card_row['link']) ? $card_row['link'] : null;
                                                    $card_tag = !empty($card_link['url']) ? 'a' : 'div';
                                                    ?>
                                                    <<?php echo $card_tag; ?>
                                                        class="oa-header-menu__card"
                                                        <?php if ($card_tag === 'a') : ?>
                                                            href="<?php echo esc_url($card_link['url']); ?>"
                                                            <?php if (!empty($card_link['target'])) : ?>target="<?php echo esc_attr($card_link['target']); ?>"<?php endif; ?>
                                                        <?php endif; ?>
                                                    >
                                                        <?php if (!empty($card_image['url'])) : ?>
                                                            <img src="<?php echo esc_url($card_image['url']); ?>" alt="<?php echo esc_attr(isset($card_image['alt']) ? $card_image['alt'] : ''); ?>">
                                                        <?php endif; ?>
                                                        <?php if ($card_title !== '') : ?>
                                                            <span><?php echo esc_html($card_title); ?></span>
                                                        <?php endif; ?>
                                                    </<?php echo $card_tag; ?>>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($left_mode === 'contact') : ?>
                                        <?php if (is_array($contact_image) && !empty($contact_image['url'])) : ?>
                                            <div class="oa-header-menu__contact-image-wrap">
                                                <img src="<?php echo esc_url($contact_image['url']); ?>" alt="<?php echo esc_attr(isset($contact_image['alt']) ? $contact_image['alt'] : ''); ?>">
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($contact_button['url'])) : ?>
                                            <a
                                                class="oa-header-menu__contact-button"
                                                href="<?php echo esc_url($contact_button['url']); ?>"
                                                <?php if (!empty($contact_button['target'])) : ?>target="<?php echo esc_attr($contact_button['target']); ?>"<?php endif; ?>
                                            >
                                                <?php echo esc_html(!empty($contact_button['title']) ? $contact_button['title'] : 'Contact us'); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <?php if (!empty($left_cards)) : ?>
                                            <div class="oa-header-menu__cards">
                                                <?php foreach ($left_cards as $card_row) :
                                                    $card_image = isset($card_row['image']) && is_array($card_row['image']) ? $card_row['image'] : null;
                                                    $card_title = isset($card_row['title']) ? trim((string) $card_row['title']) : '';
                                                    $card_link = isset($card_row['link']) && is_array($card_row['link']) ? $card_row['link'] : null;
                                                    $card_tag = !empty($card_link['url']) ? 'a' : 'div';
                                                    ?>
                                                    <<?php echo $card_tag; ?>
                                                        class="oa-header-menu__card"
                                                        <?php if ($card_tag === 'a') : ?>
                                                            href="<?php echo esc_url($card_link['url']); ?>"
                                                            <?php if (!empty($card_link['target'])) : ?>target="<?php echo esc_attr($card_link['target']); ?>"<?php endif; ?>
                                                        <?php endif; ?>
                                                    >
                                                        <?php if (!empty($card_image['url'])) : ?>
                                                            <img src="<?php echo esc_url($card_image['url']); ?>" alt="<?php echo esc_attr(isset($card_image['alt']) ? $card_image['alt'] : ''); ?>">
                                                        <?php endif; ?>
                                                        <?php if ($card_title !== '') : ?>
                                                            <span><?php echo esc_html($card_title); ?></span>
                                                        <?php endif; ?>
                                                    </<?php echo $card_tag; ?>>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="oa-header-menu__mega-columns">
                                    <?php foreach ($columns_to_render as $column_row) :
                                        $column_title = isset($column_row['title']) ? trim((string) $column_row['title']) : '';
                                        $column_links = !empty($column_row['links']) && is_array($column_row['links']) ? $column_row['links'] : [];
                                        ?>
                                        <div class="oa-header-menu__mega-column">
                                            <?php if ($column_title !== '') : ?>
                                                <h5><?php echo esc_html($column_title); ?></h5>
                                            <?php endif; ?>
                                            <?php if (!empty($column_links)) : ?>
                                                <ul>
                                                    <?php foreach ($column_links as $column_link_row) :
                                                        $column_link = isset($column_link_row['link']) && is_array($column_link_row['link']) ? $column_link_row['link'] : null;
                                                        if (empty($column_link['url'])) {
                                                            continue;
                                                        }
                                                        ?>
                                                        <li>
                                                            <a
                                                                href="<?php echo esc_url($column_link['url']); ?>"
                                                                <?php if (!empty($column_link['target'])) : ?>target="<?php echo esc_attr($column_link['target']); ?>"<?php endif; ?>
                                                            >
                                                                <?php echo esc_html(!empty($column_link['title']) ? $column_link['title'] : $column_link['url']); ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php

    return ob_get_clean();
}

function oa_render_header_menu_shortcode($atts) {
    return oa_get_header_menu_markup($atts);
}
add_shortcode('oa_header_menu', 'oa_render_header_menu_shortcode');
