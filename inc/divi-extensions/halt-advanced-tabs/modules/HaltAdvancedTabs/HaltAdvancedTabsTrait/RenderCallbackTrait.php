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

        $tabs_bg = isset( $settings['tabs_container_bg'] ) ? trim( (string) $settings['tabs_container_bg'] ) : '';
        $tabs_border = isset( $settings['tabs_container_border'] ) ? trim( (string) $settings['tabs_container_border'] ) : '';
        $panels_bg = isset( $settings['panels_container_bg'] ) ? trim( (string) $settings['panels_container_bg'] ) : '';
        $panels_border = isset( $settings['panels_container_border'] ) ? trim( (string) $settings['panels_container_border'] ) : '';
        $tab_bg = isset( $settings['tabs_bg'] ) ? trim( (string) $settings['tabs_bg'] ) : '';
        $tab_bg_active = isset( $settings['tabs_bg_active'] ) ? trim( (string) $settings['tabs_bg_active'] ) : '';

        $style_vars = '';
        if ( $tabs_bg !== '' ) {
            $style_vars .= '--halt-tabs-list-bg:' . esc_attr( $tabs_bg ) . ';';
        }
        if ( $tabs_border !== '' ) {
            $style_vars .= '--halt-tabs-list-border:' . esc_attr( $tabs_border ) . ';';
        }
        if ( $panels_bg !== '' ) {
            $style_vars .= '--halt-tabs-panel-bg:' . esc_attr( $panels_bg ) . ';';
        }
        if ( $panels_border !== '' ) {
            $style_vars .= '--halt-tabs-panel-border:' . esc_attr( $panels_border ) . ';';
        }
        if ( $tab_bg !== '' ) {
            $style_vars .= '--halt-tabs-tab-bg:' . esc_attr( $tab_bg ) . ';';
        }
        if ( $tab_bg_active !== '' ) {
            $style_vars .= '--halt-tabs-tab-bg-active:' . esc_attr( $tab_bg_active ) . ';';
        }

        $uid = 'halt-advanced-tabs-' . wp_generate_uuid4();

        $style = HTMLUtility::render(
            [
                'tag'               => 'style',
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => sprintf(
                    '.%1$s{display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:24px;align-items:stretch;}' .
                    '.%1$s .halt-tabs__list{display:flex;flex-direction:column;gap:16px;background:var(--halt-tabs-list-bg, transparent);border:1px solid var(--halt-tabs-list-border, transparent);border-radius:16px;padding:16px;}' .
                    '.%1$s .halt-tabs__nav,.%1$s .halt-tabs__nav-label{display:none;}' .
                    '.%1$s .halt-tabs__tab{background:var(--halt-tabs-tab-bg, rgba(0,0,0,.15));border-radius:14px;padding:16px 18px;cursor:pointer;transition:all .2s ease;border:1px solid transparent;text-align:left;font-family:"Zalando Sans SemiExpanded", sans-serif;font-size:22px;font-weight:600;color:#fff;}' .
                    '.%1$s .halt-tabs__tab.is-active{background:var(--halt-tabs-tab-bg-active, #7e5df6);border-color:rgba(255,255,255,.35);}' .
                    '.%1$s .halt-tabs__panels{height:100%%;}' .
                    '.%1$s .halt-tabs__panel{display:none;background:var(--halt-tabs-panel-bg, rgba(0,0,0,.2));border:1px solid var(--halt-tabs-panel-border, transparent);border-radius:18px;padding:24px;color:#fff;min-height:100%%;flex-direction:column;justify-content:space-between;}' .
                    '.%1$s .halt-tabs__panel.is-active{display:flex;height:100%%;}' .
                    '.%1$s .halt-tabs__panel h3{margin:0 0 12px;font-family:"Zalando Sans SemiExpanded", sans-serif;font-size:22px;font-weight:600;color:#fff;}' .
                    '.%1$s .halt-tabs__content{line-height:1.6;font-family:"Poppins", sans-serif;font-size:16px;font-weight:400;color:#fff;}' .
                    '.%1$s .halt-tabs__buttons{display:flex;flex-wrap:wrap;gap:12px;}' .
                    '.%1$s .halt-tabs__button{display:inline-flex;align-items:center;justify-content:center;padding:15px 25px;border-radius:10px;border:1px solid #8467FF;color:#fff;text-decoration:none;font-family:"Poppins", sans-serif;font-size:16px;font-weight:600;background:transparent;transition:background-color .2s ease,color .2s ease,border-color .2s ease;}' .
                    '.%1$s .halt-tabs__button:hover{color:#8467FF;background:rgba(255,255,255,0.1);}' .
                    '/* @media (max-width:980px){.%1$s{grid-template-columns:1fr;}} */' .
                    '@media (max-width:980px){.%1$s{grid-template-columns:1fr;}.%1$s .halt-tabs__list-wrap{display:flex;align-items:center;justify-content:center;gap:10px;width:100%%;min-width:0}.%1$s .halt-tabs__nav{border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.08);color:#fff;border-radius:999px;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex:0 0 auto}.%1$s .halt-tabs__nav:disabled{opacity:.4;cursor:default}.%1$s .halt-tabs__nav-label{display:inline-flex;align-items:center;justify-content:center;background:var(--halt-tabs-tab-bg-active, #7e5df6);border:1px solid rgba(255,255,255,.35);border-radius:14px;padding:12px 18px;font-family:"Zalando Sans SemiExpanded", sans-serif;font-size:18px;font-weight:600;color:#fff;max-width:70%%;text-align:center}.%1$s .halt-tabs__list{display:block;position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;visibility:hidden}.%1$s .halt-tabs__panels{height:auto}.%1$s .halt-tabs__panel{min-height:auto}.%1$s .halt-tabs__panel.is-active{height:auto}}',
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
                            'tag'               => 'div',
                            'attributes'        => [ 'class' => 'halt-tabs__panel-body' ],
                            'childrenSanitizer' => 'et_core_esc_previously',
                            'children'          => HTMLUtility::render(
                                [
                                    'tag'               => 'h3',
                                    'childrenSanitizer' => 'et_core_esc_previously',
                                    'children'          => esc_html( $tab['title'] ),
                                ]
                            ) . $panel_content,
                        ]
                    ) . ( $buttons_html !== '' ? HTMLUtility::render(
                        [
                            'tag'               => 'div',
                            'attributes'        => [ 'class' => 'halt-tabs__panel-actions' ],
                            'childrenSanitizer' => 'et_core_esc_previously',
                            'children'          => HTMLUtility::render(
                                [
                                    'tag'               => 'div',
                                    'attributes'        => [ 'class' => 'halt-tabs__buttons' ],
                                    'childrenSanitizer' => 'et_core_esc_previously',
                                    'children'          => $buttons_html,
                                ]
                            ),
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
                    '(function(){var root=document.querySelector(".%1$s");if(!root){return;}var tabs=Array.prototype.slice.call(root.querySelectorAll(".halt-tabs__tab"));var panels=Array.prototype.slice.call(root.querySelectorAll(".halt-tabs__panel"));var panelsWrap=root.querySelector(".halt-tabs__panels");var label=root.querySelector(".halt-tabs__nav-label");var wrap=root.querySelector(".halt-tabs__list-wrap");var prev=root.querySelector(".halt-tabs__nav--prev");var next=root.querySelector(".halt-tabs__nav--next");var isResponsive=function(){return window.innerWidth<=980;};var setPanelsHeight=function(){if(!panelsWrap){return;}if(!isResponsive()){panelsWrap.style.minHeight="";return;}panelsWrap.style.minHeight="";var maxHeight=0;var activePanel=root.querySelector(".halt-tabs__panel.is-active");panels.forEach(function(panel){panel.classList.add("is-active");var h=panel.scrollHeight;maxHeight=Math.max(maxHeight,h);panel.classList.remove("is-active");});if(activePanel){activePanel.classList.add("is-active");}if(maxHeight>0){panelsWrap.style.minHeight=maxHeight+"px";}};var setLabelWidth=function(){if(!label||tabs.length===0){return;}var maxWidth=0;tabs.forEach(function(tab){var w=tab.scrollWidth;maxWidth=Math.max(maxWidth,w);});if(maxWidth>0){var available=maxWidth; if(wrap&&prev&&next){var wrapWidth=wrap.clientWidth||0;var navWidth=(prev.offsetWidth||0)+(next.offsetWidth||0);var gap=10;available=Math.max(0,wrapWidth-navWidth-(gap*2));}var finalWidth=Math.min(maxWidth,available||maxWidth);label.style.minWidth=finalWidth+"px";label.style.maxWidth=finalWidth+"px";}};var updateLabel=function(tab){if(label&&tab){label.textContent=tab.textContent||"";}};var activateTab=function(tab){if(!tab){return;}var idx=tabs.indexOf(tab);if(idx<0){return;}tabs.forEach(function(t){t.classList.remove("is-active")});panels.forEach(function(p){p.classList.remove("is-active")});tab.classList.add("is-active");if(panels[idx]){panels[idx].classList.add("is-active");}updateLabel(tab);};tabs.forEach(function(tab){tab.addEventListener("click",function(){activateTab(tab)});});if(prev&&next){prev.addEventListener("click",function(){var current=root.querySelector(".halt-tabs__tab.is-active")||tabs[0];var idx=tabs.indexOf(current);var nextTab=tabs[Math.max(0,idx-1)];activateTab(nextTab);});next.addEventListener("click",function(){var current=root.querySelector(".halt-tabs__tab.is-active")||tabs[0];var idx=tabs.indexOf(current);var nextTab=tabs[Math.min(tabs.length-1,idx+1)];activateTab(nextTab);});}updateLabel(root.querySelector(".halt-tabs__tab.is-active")||tabs[0]);setPanelsHeight();setLabelWidth();window.addEventListener("resize",function(){setPanelsHeight();setLabelWidth();});})();',
                    esc_attr( $uid )
                ),
            ]
        );

        $markup = HTMLUtility::render(
            [
                'tag'               => 'div',
                'attributes'        => [
                    'class' => $uid,
                    'style' => $style_vars,
                ],
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => $style . HTMLUtility::render(
                    [
                        'tag'               => 'div',
                        'attributes'        => [ 'class' => 'halt-tabs__list-wrap' ],
                        'childrenSanitizer' => 'et_core_esc_previously',
                        'children'          => HTMLUtility::render(
                            [
                                'tag'        => 'button',
                                'attributes' => [
                                    'class' => 'halt-tabs__nav halt-tabs__nav--prev',
                                    'type'  => 'button',
                                    'aria-label' => 'Previous tabs',
                                ],
                                'children' => '&#8249;',
                            ]
                        ) . HTMLUtility::render(
                            [
                                'tag'        => 'div',
                                'attributes' => [ 'class' => 'halt-tabs__nav-label' ],
                                'children'   => '',
                            ]
                        ) . HTMLUtility::render(
                            [
                                'tag'               => 'div',
                                'attributes'        => [ 'class' => 'halt-tabs__list' ],
                                'childrenSanitizer' => 'et_core_esc_previously',
                                'children'          => $tab_buttons,
                            ]
                        ) . HTMLUtility::render(
                            [
                                'tag'        => 'button',
                                'attributes' => [
                                    'class' => 'halt-tabs__nav halt-tabs__nav--next',
                                    'type'  => 'button',
                                    'aria-label' => 'Next tabs',
                                ],
                                'children' => '&#8250;',
                            ]
                        ),
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

        $parsed_block  = is_object( $block ) ? ( $block->parsed_block ?? [] ) : [];
        $block_id      = $parsed_block['id'] ?? '';
        $store_instance = $parsed_block['storeInstance'] ?? '';
        $order_index    = $parsed_block['orderIndex'] ?? 0;

        if ( $block_id === '' || $store_instance === '' ) {
            return $markup;
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
                            'id'            => $block_id,
                            'orderIndex'    => $order_index,
                            'storeInstance' => $store_instance,
                        ]
                    ),
                    $markup,
                ],
            ]
        );
    }
}
