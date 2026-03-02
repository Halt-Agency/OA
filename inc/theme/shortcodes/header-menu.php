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
    if (!is_array($menu_items) || empty($menu_items)) {
        return '';
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
            <?php foreach ($menu_items as $index => $item) :
                $label = isset($item['label']) ? trim((string) $item['label']) : '';
                $link = isset($item['link']) && is_array($item['link']) ? $item['link'] : null;
                $style = isset($item['style']) ? (string) $item['style'] : 'default';
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
                ?>
                <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" data-oa-menu-item>
                    <div class="oa-header-menu__trigger-wrap">
                        <?php
                        $trigger_text = esc_html($label);
                        if ($has_submenu) {
                            ?>
                            <button
                                class="oa-header-menu__trigger"
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
                                class="oa-header-menu__link"
                                href="<?php echo esc_url($link['url']); ?>"
                                <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>"<?php endif; ?>
                            >
                                <?php echo $trigger_text; ?>
                            </a>
                            <?php
                        } else {
                            ?>
                            <span class="oa-header-menu__link oa-header-menu__link--static"><?php echo $trigger_text; ?></span>
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
                        $left_heading = isset($item['mega_left_heading']) ? trim((string) $item['mega_left_heading']) : '';
                        $left_view_more = isset($item['mega_left_view_more']) && is_array($item['mega_left_view_more']) ? $item['mega_left_view_more'] : null;
                        $left_cards = !empty($item['mega_left_cards']) && is_array($item['mega_left_cards']) ? $item['mega_left_cards'] : [];
                        $contact_image = isset($item['mega_contact_image']) ? $item['mega_contact_image'] : null;
                        $contact_button = isset($item['mega_contact_button']) && is_array($item['mega_contact_button']) ? $item['mega_contact_button'] : null;
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

                                    <?php if ($left_mode === 'contact') : ?>
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
                                    <?php foreach ($item['mega_columns'] as $column_row) :
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
