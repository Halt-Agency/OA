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
        $exclude_raw = isset( $settings['excludeSlugs'] ) ? (string) $settings['excludeSlugs'] : '';
        $exclude = [];
        if ( $exclude_raw !== '' ) {
            $parts = array_map( 'trim', explode( ',', $exclude_raw ) );
            foreach ( $parts as $part ) {
                if ( $part !== '' ) {
                    $exclude[] = sanitize_title( $part );
                }
            }
        }

        $speed = isset( $settings['speed'] ) ? (float) $settings['speed'] : 30;
        if ( $speed <= 0 ) {
            $speed = 30;
        }

        $direction = isset( $settings['direction'] ) ? strtolower( trim( (string) $settings['direction'] ) ) : 'left';
        $direction_css = $direction === 'right' ? 'reverse' : 'normal';
        $grayscale = isset( $settings['grayscale'] ) ? (bool) $settings['grayscale'] : true;
        $columns = 5;
        if ( $columns < 2 ) {
            $columns = 2;
        }
        if ( $columns > 10 ) {
            $columns = 10;
        }

        $query = new \WP_Query(
            [
                'post_type'        => 'clients',
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'post_name__not_in' => $exclude,
                'orderby'          => [
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ],
            ]
        );

        if ( ! $query->have_posts() ) {
            return '';
        }

        $items = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $logo = function_exists( 'get_field' ) ? get_field( 'client_logo', get_the_ID() ) : null;
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
                    '.%1$s .marquee-wrapper{display:flex;width:200%%;animation:%1$s-scroll %2$ss linear infinite;animation-direction:%3$s;}' .
                    '.%1$s .marquee-track,.%1$s .marquee-track-duplicate{display:flex;gap:30px;width:50%%;}' .
                    '.%1$s .marquee-wrapper:hover{animation-play-state:paused;}' .
                    '.%1$s .marquee-item{flex:0 0 auto;display:flex;align-items:center;justify-content:center;width:calc(100%%/var(--oa-columns, %6$d));padding:0 10px;box-sizing:border-box;}' .
                    '%4$s' .
                    '%5$s' .
                    '@keyframes %1$s-scroll{0%%{transform:translateX(0);}100%%{transform:translateX(-50%%);}}' .
                    '@media (max-width:768px){.%1$s .marquee-item{width:calc(100%%/3);padding:0 8px}.%1$s .marquee-item img{max-height:60px}.%1$s .marquee-track,.%1$s .marquee-track-duplicate{gap:20px;}}',
                    esc_attr( $uid ),
                    esc_attr( $speed ),
                    esc_attr( $direction_css ),
                    $grayscale_css,
                    $grayscale_hover_css,
                    $columns
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
                    'style' => sprintf( '--oa-columns:%d;', $columns ),
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

        $parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
        $parent_attrs = $parent->attrs ?? [];

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
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
                            'id'            => $block->parsed_block['id'],
                            'orderIndex'    => $block->parsed_block['orderIndex'],
                            'storeInstance' => $block->parsed_block['storeInstance'],
                        ]
                    ),
                    $marquee_html,
                ],
            ]
        );
    }
}
