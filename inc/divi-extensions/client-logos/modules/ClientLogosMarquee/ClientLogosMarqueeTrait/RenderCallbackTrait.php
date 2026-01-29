<?php
/**
 * ClientLogosMarquee::render_callback().
 */

namespace OA\Modules\ClientLogosMarquee\ClientLogosMarqueeTrait;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Framework\Utility\HTMLUtility;
use OA\Modules\ClientLogosMarquee\ClientLogosMarquee;

trait RenderCallbackTrait {
    public static function render_callback( $attrs, $content, $block, $elements ) {
        $settings = $attrs['settings']['innerContent']['desktop']['value'] ?? [];
        if ( empty( $settings ) && isset( $attrs['settings']['innerContent']['value'] ) ) {
            $settings = $attrs['settings']['innerContent']['value'];
        }
        if ( empty( $settings ) && isset( $attrs['settings']['innerContent'] ) && is_array( $attrs['settings']['innerContent'] ) ) {
            $settings = $attrs['settings']['innerContent'];
        }
        $filter_mode_raw = $settings['filterMode'] ?? 'all';
        $logo_variant_raw = $settings['logoVariant'] ?? 'white';
        $direction_raw = $settings['direction'] ?? 'left';
        $taxonomy_raw = $settings['taxonomy'] ?? 'client_category';

        $filter_mode_map = [ 'all', 'taxonomy' ];
        $logo_variant_map = [ 'white', 'colour' ];
        $direction_map = [ 'left', 'right' ];

        if ( is_numeric( $filter_mode_raw ) ) {
            $filter_mode_raw = $filter_mode_map[ (int) $filter_mode_raw ] ?? 'all';
        }
        if ( is_numeric( $logo_variant_raw ) ) {
            $logo_variant_raw = $logo_variant_map[ (int) $logo_variant_raw ] ?? 'white';
        }
        if ( is_numeric( $direction_raw ) ) {
            $direction_raw = $direction_map[ (int) $direction_raw ] ?? 'left';
        }
        if ( is_numeric( $taxonomy_raw ) ) {
            $taxonomy_raw = 'client_category';
        }

        $filter_mode    = strtolower( trim( (string) $filter_mode_raw ) );
        $taxonomy       = sanitize_key( (string) $taxonomy_raw );
        $terms_raw      = isset( $settings['taxonomyTerms'] ) ? (string) $settings['taxonomyTerms'] : '';
        $taxonomy_terms = [];
        if ( $terms_raw !== '' ) {
            $parts = array_map( 'trim', explode( ',', $terms_raw ) );
            foreach ( $parts as $part ) {
                if ( $part !== '' ) {
                    $taxonomy_terms[] = sanitize_title( $part );
                }
            }
        }
        if ( $filter_mode !== 'taxonomy' && ! empty( $taxonomy_terms ) ) {
            $filter_mode = 'taxonomy';
        }

        $speed = isset( $settings['speed'] ) ? (float) $settings['speed'] : 30;
        if ( $speed <= 0 ) {
            $speed = 30;
        }

        $direction = strtolower( trim( (string) $direction_raw ) );
        $direction_css = $direction === 'right' ? 'reverse' : 'normal';
        $grayscale = isset( $settings['grayscale'] ) ? (bool) $settings['grayscale'] : true;
        $logo_variant = strtolower( trim( (string) $logo_variant_raw ) );
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            $debug_payload = [
                'filterMode'     => $filter_mode,
                'taxonomy'       => $taxonomy,
                'taxonomyTerms'  => $taxonomy_terms,
                'logoVariant'    => $logo_variant,
                'settings'       => $settings,
            ];
            echo '<script>console.log("OA Client Logos debug", ' . wp_json_encode( $debug_payload ) . ');</script>';
        }

        $query_args = [
            'post_type'       => 'clients',
            'post_status'     => 'publish',
            'posts_per_page'  => -1,
            'orderby'         => [
                'menu_order' => 'ASC',
                'title'      => 'ASC',
            ],
        ];

        if ( $filter_mode === 'taxonomy' && $taxonomy && ! empty( $taxonomy_terms ) ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $taxonomy_terms,
                ],
            ];
        }

        $query = new \WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            return '';
        }

        $items = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $field_name = $logo_variant === 'colour' ? 'client_logo_colour' : 'client_logo';
            $logo = function_exists( 'get_field' ) ? get_field( $field_name, get_the_ID() ) : null;
            if ( empty( $logo ) && $field_name !== 'client_logo' ) {
                $logo = function_exists( 'get_field' ) ? get_field( 'client_logo', get_the_ID() ) : null;
            }
            if ( is_array( $logo ) && isset( $logo['url'] ) ) {
                $items[] = [
                    'url'   => $logo['url'],
                    'alt'   => isset( $logo['alt'] ) ? $logo['alt'] : '',
                    'title' => isset( $logo['title'] ) ? $logo['title'] : '',
                ];
            } elseif ( is_numeric( $logo ) ) {
                $items[] = [
                    'url'   => wp_get_attachment_image_url( $logo, 'full' ),
                    'alt'   => get_post_meta( $logo, '_wp_attachment_image_alt', true ),
                    'title' => get_the_title( $logo ),
                ];
            }
        }
        wp_reset_postdata();

        if ( empty( $items ) ) {
            if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
                return '<script>console.warn("OA Client Logos: no client_logo items found for clients CPT.");</script>';
            }
            return '';
        }

        $loop_items = $items;

        $uid = 'oa-client-marquee-' . wp_generate_uuid4();

        $grayscale_css = $grayscale
            ? '.%1$s .marquee-item img{width:100%%;max-width:100%%;max-height:80px;height:auto;object-fit:contain;filter:grayscale(100%%);opacity:.7;transition:all .3s ease;}'
            : '.%1$s .marquee-item img{width:100%%;max-width:100%%;max-height:80px;height:auto;object-fit:contain;transition:all .3s ease;}';
        $grayscale_hover_css = $grayscale
            ? '.%1$s .marquee-item img:hover{filter:grayscale(0%%);opacity:1;transform:scale(1.05);}'
            : '.%1$s .marquee-item img:hover{transform:scale(1.05);}';

        $style = HTMLUtility::render(
            [
                'tag'               => 'style',
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => sprintf(
                    '.%1$s{width:100%%;overflow:hidden;position:relative;padding:40px 0;}' .
                    '.%1$s .marquee-wrapper{display:flex;width:max-content;animation:%1$s-scroll %2$ss linear infinite;animation-direction:%3$s;}' .
                    '.%1$s .marquee-track,.%1$s .marquee-track-duplicate{display:flex;gap:30px;width:max-content;}' .
                    '.%1$s .marquee-wrapper:hover{animation-play-state:paused;}' .
                    '.%1$s .marquee-item{flex:0 0 auto;display:flex;align-items:center;justify-content:center;padding:0 10px;box-sizing:border-box;width:clamp(90px, 12vw, 160px);}' .
                    '%4$s' .
                    '%5$s' .
                    '@keyframes %1$s-scroll{0%%{transform:translateX(0);}100%%{transform:translateX(-50%%);}}' .
                    '@media (max-width:768px){.%1$s .marquee-item{width:clamp(70px, 24vw, 120px);padding:0 8px}.%1$s .marquee-item img{max-height:60px}.%1$s .marquee-track,.%1$s .marquee-track-duplicate{gap:20px;}}',
                    esc_attr( $uid ),
                    esc_attr( $speed ),
                    esc_attr( $direction_css ),
                    $grayscale_css,
                    $grayscale_hover_css
                ),
            ]
        );

        $track = '';
        foreach ( $loop_items as $item ) {
            $track .= HTMLUtility::render(
                [
                    'tag'               => 'div',
                    'attributes'        => [ 'class' => 'marquee-item' ],
                    'childrenSanitizer' => 'et_core_esc_previously',
                    'children'          => HTMLUtility::render(
                        [
                            'tag'        => 'img',
                            'attributes' => [
                                'src'   => esc_url( $item['url'] ),
                                'alt'   => esc_attr( $item['alt'] ),
                                'title' => esc_attr( $item['title'] ),
                            ],
                        ]
                    ),
                ]
            );
        }

        $marquee_html = HTMLUtility::render(
            [
                'tag'               => 'div',
                'attributes'        => [
                    'class' => $uid,
                    'style' => '',
                ],
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => $style . HTMLUtility::render(
                    [
                        'tag'               => 'div',
                        'attributes'        => [ 'class' => 'marquee-wrapper' ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children'          => HTMLUtility::render(
                            [
                                'tag'               => 'div',
                                'attributes'        => [ 'class' => 'marquee-track' ],
                                'childrenSanitizer' => 'et_core_esc_previously',
                                'children'          => $track,
                            ]
                        ) . HTMLUtility::render(
                            [
                                'tag'               => 'div',
                                'attributes'        => [ 'class' => 'marquee-track-duplicate' ],
                                'childrenSanitizer' => 'et_core_esc_previously',
                                'children'          => $track,
                            ]
                        ),
                    ]
                ),
            ]
        );

        if ( ! is_object( $elements ) || ! method_exists( $elements, 'render' ) || ! class_exists( '\ET\Builder\Packages\Module\Module' ) ) {
            return $marquee_html;
        }

        $parsed_block  = is_object( $block ) ? ( $block->parsed_block ?? [] ) : [];
        $block_id      = $parsed_block['id'] ?? '';
        $store_instance = $parsed_block['storeInstance'] ?? '';
        $order_index    = $parsed_block['orderIndex'] ?? 0;

        if ( $block_id === '' || $store_instance === '' ) {
            return $marquee_html;
        }

        $parent       = BlockParserStore::get_parent( $block_id, $store_instance );
        $parent_attrs = $parent->attrs ?? [];

        return Module::render(
            [
                'orderIndex'          => $order_index,
                'storeInstance'       => $store_instance,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block_id,
                'name'                => $block->block_type->name,
                'moduleCategory'      => $block->block_type->category,
                'classnamesFunction'  => [ ClientLogosMarquee::class, 'module_classnames' ],
                'stylesComponent'     => [ ClientLogosMarquee::class, 'module_styles' ],
                'scriptDataComponent' => [ ClientLogosMarquee::class, 'module_script_data' ],
                'parentAttrs'         => $parent_attrs,
                'parentId'            => $parent->id ?? '',
                'parentName'          => $parent->blockName ?? '',
                'children'            => [
                    ElementComponents::component(
                        [
                            'attrs'         => $attrs['module']['decoration'] ?? [],
                            'id'            => $block_id,
                            'orderIndex'    => $order_index,
                            'storeInstance' => $store_instance,
                        ]
                    ),
                    $marquee_html,
                ],
            ]
        );
    }
}
