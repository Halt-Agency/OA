<?php
/**
 * HaltAdvancedTabs::render_callback().
 */

namespace OA\Modules\HaltAdvancedTabs\HaltAdvancedTabsTrait;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Framework\Utility\HTMLUtility;
use OA\Modules\HaltAdvancedTabs\HaltAdvancedTabs;

trait RenderCallbackTrait {
    public static function render_callback( $attrs, $content, $block, $elements ) {
        $settings = $attrs['settings']['innerContent']['desktop']['value'] ?? [];
        if ( empty( $settings ) && isset( $attrs['settings']['innerContent']['value'] ) ) {
            $settings = $attrs['settings']['innerContent']['value'];
        }
        if ( empty( $settings ) && isset( $attrs['settings']['innerContent'] ) && is_array( $attrs['settings']['innerContent'] ) ) {
            $settings = $attrs['settings']['innerContent'];
        }

        $tabs = [];
        for ( $i = 1; $i <= 5; $i++ ) {
            $title_key = "tab{$i}_title";
            $content_key = "tab{$i}_content";
            $button1_text_key = "tab{$i}_button1_text";
            $button1_url_key = "tab{$i}_button1_url";
            $button2_text_key = "tab{$i}_button2_text";
            $button2_url_key = "tab{$i}_button2_url";

            $title = isset( $settings[ $title_key ] ) ? trim( (string) $settings[ $title_key ] ) : '';
            $body = isset( $settings[ $content_key ] ) ? (string) $settings[ $content_key ] : '';
            $button1_text = isset( $settings[ $button1_text_key ] ) ? trim( (string) $settings[ $button1_text_key ] ) : '';
            $button1_url = isset( $settings[ $button1_url_key ] ) ? trim( (string) $settings[ $button1_url_key ] ) : '';
            $button2_text = isset( $settings[ $button2_text_key ] ) ? trim( (string) $settings[ $button2_text_key ] ) : '';
            $button2_url = isset( $settings[ $button2_url_key ] ) ? trim( (string) $settings[ $button2_url_key ] ) : '';

            if ( $title === '' && $body === '' ) {
                continue;
            }

            $tabs[] = [
                'title' => $title === '' ? "Tab {$i}" : $title,
                'content' => $body,
                'buttons' => [
                    [ 'text' => $button1_text, 'url' => $button1_url ],
                    [ 'text' => $button2_text, 'url' => $button2_url ],
                ],
            ];
        }

        if ( empty( $tabs ) ) {
            return '';
        }

        $uid = 'halt-advanced-tabs-' . wp_generate_uuid4();

        $style = HTMLUtility::render(
            [
                'tag'               => 'style',
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => sprintf(
                    '.%1$s{display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:24px;align-items:stretch;}' .
                    '.%1$s .halt-tabs__list{display:flex;flex-direction:column;gap:16px;}' .
                    '.%1$s .halt-tabs__tab{background:rgba(0,0,0,.15);border-radius:14px;padding:16px 18px;font-weight:600;color:#fff;cursor:pointer;transition:all .2s ease;border:1px solid transparent;text-align:left;}' .
                    '.%1$s .halt-tabs__tab.is-active{background:#7e5df6;border-color:rgba(255,255,255,.35);}' .
                    '.%1$s .halt-tabs__panels{height:100%%;}' .
                    '.%1$s .halt-tabs__panel{display:none;background:rgba(0,0,0,.2);border-radius:18px;padding:24px;color:#fff;min-height:100%%;}' .
                    '.%1$s .halt-tabs__panel.is-active{display:block;height:100%%;}' .
                    '.%1$s .halt-tabs__panel h3{margin:0 0 12px;font-size:22px;}' .
                    '.%1$s .halt-tabs__content{line-height:1.6;}' .
                    '.%1$s .halt-tabs__buttons{display:flex;flex-wrap:wrap;gap:12px;margin-top:20px;}' .
                    '.%1$s .halt-tabs__button{display:inline-flex;align-items:center;justify-content:center;padding:10px 18px;border-radius:999px;border:1px solid rgba(255,255,255,.45);color:#fff;text-decoration:none;font-weight:600;}' .
                    '@media (max-width:980px){.%1$s{grid-template-columns:1fr;}}',
                    esc_attr( $uid )
                ),
            ]
        );

        $tab_buttons = '';
        $tab_panels = '';
        foreach ( $tabs as $index => $tab ) {
            $is_active = $index === 0;
            $tab_buttons .= HTMLUtility::render(
                [
                    'tag'               => 'button',
                    'attributes'        => [
                        'class' => $is_active ? 'halt-tabs__tab is-active' : 'halt-tabs__tab',
                        'type'  => 'button',
                        'data-tab' => (string) $index,
                    ],
                    'childrenSanitizer' => 'et_core_esc_previously',
                    'children'          => esc_html( $tab['title'] ),
                ]
            );

            $buttons_html = '';
            foreach ( $tab['buttons'] as $button ) {
                if ( $button['text'] === '' || $button['url'] === '' ) {
                    continue;
                }
                $buttons_html .= HTMLUtility::render(
                    [
                        'tag'               => 'a',
                        'attributes'        => [
                            'class' => 'halt-tabs__button',
                            'href'  => esc_url( $button['url'] ),
                        ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children'          => esc_html( $button['text'] ),
                    ]
                );
            }

            $panel_content = '';
            if ( $tab['content'] !== '' ) {
                $panel_content = HTMLUtility::render(
                    [
                        'tag'               => 'div',
                        'attributes'        => [ 'class' => 'halt-tabs__content' ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children'          => wp_kses_post( $tab['content'] ),
                    ]
                );
            }

            $tab_panels .= HTMLUtility::render(
                [
                    'tag'               => 'div',
                    'attributes'        => [
                        'class' => $is_active ? 'halt-tabs__panel is-active' : 'halt-tabs__panel',
                        'data-tab' => (string) $index,
                    ],
                    'childrenSanitizer' => 'et_core_esc_previously',
                    'children'          => HTMLUtility::render(
                        [
                            'tag'               => 'h3',
                            'childrenSanitizer' => 'et_core_esc_previously',
                            'children'          => esc_html( $tab['title'] ),
                        ]
                    ) . $panel_content . ( $buttons_html !== '' ? HTMLUtility::render(
                        [
                            'tag'               => 'div',
                            'attributes'        => [ 'class' => 'halt-tabs__buttons' ],
                            'childrenSanitizer' => 'et_core_esc_previously',
                            'children'          => $buttons_html,
                        ]
                    ) : '' ),
                ]
            );
        }

        $script = HTMLUtility::render(
            [
                'tag'               => 'script',
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => sprintf(
                    '(function(){var root=document.querySelector(".%1$s");if(!root){return;}var tabs=root.querySelectorAll(".halt-tabs__tab");var panels=root.querySelectorAll(".halt-tabs__panel");tabs.forEach(function(tab){tab.addEventListener("click",function(){var id=tab.getAttribute("data-tab");tabs.forEach(function(t){t.classList.remove("is-active")});panels.forEach(function(p){p.classList.remove("is-active")});tab.classList.add("is-active");root.querySelector(".halt-tabs__panel[data-tab=\""+id+"\"]").classList.add("is-active")})});})();',
                    esc_attr( $uid )
                ),
            ]
        );

        $markup = HTMLUtility::render(
            [
                'tag'               => 'div',
                'attributes'        => [ 'class' => $uid ],
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => $style . HTMLUtility::render(
                    [
                        'tag'               => 'div',
                        'attributes'        => [ 'class' => 'halt-tabs__list' ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children'          => $tab_buttons,
                    ]
                ) . HTMLUtility::render(
                    [
                        'tag'               => 'div',
                        'attributes'        => [ 'class' => 'halt-tabs__panels' ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children'          => $tab_panels,
                    ]
                ) . $script,
            ]
        );

        if ( ! is_object( $elements ) || ! method_exists( $elements, 'render' ) || ! class_exists( '\\ET\\Builder\\Packages\\Module\\Module' ) ) {
            return $markup;
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
                'classnamesFunction'  => [ HaltAdvancedTabs::class, 'module_classnames' ],
                'stylesComponent'     => [ HaltAdvancedTabs::class, 'module_styles' ],
                'scriptDataComponent' => [ HaltAdvancedTabs::class, 'module_script_data' ],
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
                    $markup,
                ],
            ]
        );
    }
}
