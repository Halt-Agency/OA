#!/usr/bin/env python3
import argparse
import json
from pathlib import Path


def pascal_case(name: str) -> str:
    parts = [p for p in name.replace('_', '-').split('-') if p]
    return ''.join(p.capitalize() for p in parts)


def main() -> int:
    parser = argparse.ArgumentParser(description='Create a new Divi 5 module skeleton.')
    parser.add_argument('slug', help='kebab-case module slug, e.g. landing-hero')
    parser.add_argument('--title', default=None, help='Human title, e.g. Landing Hero')
    parser.add_argument('--namespace', default='oa', help='Module namespace (default: oa)')
    args = parser.parse_args()

    slug = args.slug.strip()
    if not slug:
        raise SystemExit('Module slug is required.')

    title = args.title or pascal_case(slug).replace(' ', '')
    class_name = pascal_case(slug)
    namespace = args.namespace.strip() or 'oa'
    module_name = f"{namespace}/{slug}"

    repo_root = Path(__file__).resolve().parents[1]
    ext_root = repo_root / 'inc' / 'divi-extensions' / 'client-logos'

    src_dir = ext_root / 'src' / 'components' / slug
    json_dir = ext_root / 'modules-json' / slug
    php_dir = ext_root / 'modules' / class_name
    php_trait_dir = php_dir / f"{class_name}Trait"

    src_dir.mkdir(parents=True, exist_ok=True)
    json_dir.mkdir(parents=True, exist_ok=True)
    php_trait_dir.mkdir(parents=True, exist_ok=True)

    module_json = {
        "name": module_name,
        "d4Shortcode": "",
        "title": title,
        "titles": title,
        "moduleIcon": "example/module-static",
        "moduleClassName": slug.replace('-', '_'),
        "moduleOrderClassName": slug.replace('-', '_'),
        "category": "module",
        "attributes": {
            "module": {
                "type": "object",
                "selector": "{{selector}}",
                "default": {
                    "meta": {
                        "adminLabel": {
                            "desktop": {
                                "value": title
                            }
                        }
                    }
                },
                "settings": {
                    "meta": {
                        "adminLabel": {}
                    },
                    "advanced": {
                        "link": {},
                        "text": {},
                        "htmlAttributes": {}
                    },
                    "decoration": {
                        "background": {},
                        "bodyFont": {},
                        "sizing": {},
                        "spacing": {},
                        "border": {},
                        "boxShadow": {},
                        "filters": {},
                        "transform": {},
                        "animation": {},
                        "overflow": {},
                        "disabledOn": {},
                        "transition": {},
                        "position": {},
                        "zIndex": {},
                        "scroll": {},
                        "sticky": {}
                    }
                }
            },
            "settings": {
                "type": "object",
                "default": {
                    "innerContent": {
                        "desktop": {
                            "value": {}
                        }
                    }
                },
                "settings": {
                    "innerContent": {
                        "groupType": "group-items",
                        "items": {}
                    }
                }
            }
        },
        "settings": {
            "content": "auto",
            "design": "auto",
            "advanced": "auto",
            "groups": {}
        }
    }

    default_attrs = {
        "module": {
            "meta": {
                "adminLabel": {
                    "desktop": {
                        "value": title
                    }
                }
            },
            "advanced": {},
            "decoration": {}
        },
        "settings": {
            "innerContent": {
                "desktop": {
                    "value": {}
                }
            }
        }
    }

    def write_json(path: Path, data: dict):
        path.write_text(json.dumps(data, indent=2) + "\n", encoding='utf-8')

    write_json(src_dir / 'module.json', module_json)
    write_json(json_dir / 'module.json', module_json)
    write_json(src_dir / 'module-default-render-attributes.json', default_attrs)
    write_json(src_dir / 'module-default-printed-style-attributes.json', default_attrs)
    write_json(json_dir / 'module-default-render-attributes.json', default_attrs)

    (src_dir / 'style.scss').write_text('', encoding='utf-8')
    (src_dir / 'module.scss').write_text(f".{slug.replace('-', '_')} {{\n}}\n", encoding='utf-8')

    (src_dir / 'custom-css.ts').write_text('export const cssFields = {}\n', encoding='utf-8')

    (src_dir / 'types.ts').write_text(
        """import { ModuleEditProps } from '@divi/module-library';\n"
        "import { FormatBreakpointStateAttr, InternalAttrs, type Element, type Module } from '@divi/types';\n\n"
        "export interface ModuleCssAttr extends Module.Css.AttributeValue {}\n\n"
        "export type ModuleCssGroupAttr = FormatBreakpointStateAttr<ModuleCssAttr>;\n\n"
        "export interface ModuleAttrs extends InternalAttrs {\n"
        "  css?: ModuleCssGroupAttr;\n"
        "  module?: {\n"
        "    meta?: Element.Meta.Attributes;\n"
        "    advanced?: {\n"
        "      link?: Element.Advanced.Link.Attributes;\n"
        "      htmlAttributes?: Element.Advanced.IdClasses.Attributes;\n"
        "      text?: Element.Advanced.Text.Attributes;\n"
        "    };\n"
        "    decoration?: Element.Decoration.PickedAttributes<\n"
        "      'animation' | 'background' | 'border' | 'boxShadow' | 'disabledOn' |\n"
        "      'filters' | 'overflow' | 'position' | 'scroll' | 'sizing' | 'spacing' |\n"
        "      'sticky' | 'transform' | 'transition' | 'zIndex'\n"
        "    > & { attributes?: any };\n"
        "  };\n"
        "  settings?: { innerContent?: { desktop?: { value?: Record<string, unknown> } } };\n"
        "}\n\n"
        "export type ModuleEditProps = ModuleEditProps<ModuleAttrs>;\n",
        encoding='utf-8'
    )

    (src_dir / 'module-classnames.ts').write_text(
        """import { ModuleClassnamesParams, textOptionsClassnames } from '@divi/module';\n\n"
        "import { ModuleAttrs } from './types';\n\n"
        "export const moduleClassnames = ({ classnamesInstance, attrs }: ModuleClassnamesParams<ModuleAttrs>): void => {\n"
        "  classnamesInstance.add(textOptionsClassnames(attrs?.module?.advanced?.text));\n"
        "};\n",
        encoding='utf-8'
    )

    (src_dir / 'styles.tsx').write_text(
        """import React, { ReactElement } from 'react';\n\n"
        "import { CssStyle, StyleContainer, StylesProps } from '@divi/module';\n\n"
        "import { ModuleAttrs } from './types';\n"
        "import { cssFields } from './custom-css';\n\n"
        "export const ModuleStyles = ({ attrs, elements, settings, orderClass, mode, state, noStyleTag }: StylesProps<ModuleAttrs>): ReactElement => (\n"
        "  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>\n"
        "    {elements.style({\n"
        "      attrName: 'module',\n"
        "      styleProps: {\n"
        "        disabledOn: { disabledModuleVisibility: settings?.disabledModuleVisibility },\n"
        "      },\n"
        "    })}\n"
        "    <CssStyle selector={orderClass} attr={attrs.css} cssFields={cssFields} />\n"
        "  </StyleContainer>\n"
        ");\n",
        encoding='utf-8'
    )

    (src_dir / 'module-script-data.tsx').write_text(
        """import React, { Fragment, ReactElement } from 'react';\n\n"
        "import { ModuleScriptDataProps } from '@divi/module';\n\n"
        "import { ModuleAttrs } from './types';\n\n"
        "export const ModuleScriptData = ({ elements }: ModuleScriptDataProps<ModuleAttrs>): ReactElement => (\n"
        "  <Fragment>\n"
        "    {elements.scriptData({ attrName: 'module' })}\n"
        "  </Fragment>\n"
        ");\n",
        encoding='utf-8'
    )

    (src_dir / 'edit.tsx').write_text(
        f"""import React, {{ ReactElement }} from 'react';\n\n"
        "import { ModuleContainer } from '@divi/module';\n\n"
        "import { ModuleEditProps } from './types';\n"
        "import { ModuleStyles } from './styles';\n"
        "import { moduleClassnames } from './module-classnames';\n"
        "import { ModuleScriptData } from './module-script-data';\n\n"
        "export const ModuleEdit = (props: ModuleEditProps): ReactElement => {\n"
        "  const { attrs, elements, id, name } = props;\n\n"
        "  return (\n"
        "    <ModuleContainer\n"
        "      attrs={attrs}\n"
        "      elements={elements}\n"
        "      id={id}\n"
        "      name={name}\n"
        "      stylesComponent={ModuleStyles}\n"
        "      classnamesFunction={moduleClassnames}\n"
        "      scriptDataComponent={ModuleScriptData}\n"
        "    >\n"
        "      {elements.styleComponents({ attrName: 'module' })}\n"
        "      <div className=\"{slug.replace('-', '_')}__placeholder\">{title}</div>\n"
        "    </ModuleContainer>\n"
        "  );\n"
        "};\n",
        encoding='utf-8'
    )

    (src_dir / 'placeholder-content.ts').write_text(
        f"""export const placeholderContent = {{\n"
        "  module: {\n"
        "    meta: {\n"
        "      adminLabel: {\n"
        "        desktop: {\n"
        f"          value: '{title}',\n"
        "        },\n"
        "      },\n"
        "    },\n"
        "  },\n"
        "};\n",
        encoding='utf-8'
    )

    (src_dir / 'index.ts').write_text(
        """import { omit } from 'lodash';\n\n"
        "import type { Metadata, ModuleLibrary } from '@divi/types';\n\n"
        "import metadata from './module.json';\n"
        "import defaultRenderAttributes from './module-default-render-attributes.json';\n"
        "import defaultPrintedStyleAttributes from './module-default-printed-style-attributes.json';\n"
        "import { ModuleEdit } from './edit';\n"
        "import { ModuleAttrs } from './types';\n"
        "import { placeholderContent } from './placeholder-content';\n\n"
        "import './style.scss';\n"
        "import './module.scss';\n\n"
        "export const moduleDefinition: ModuleLibrary.Module.RegisterDefinition<ModuleAttrs> = {\n"
        "  metadata: metadata as Metadata.Values<ModuleAttrs>,\n"
        "  defaultAttrs: defaultRenderAttributes as Metadata.DefaultAttributes<ModuleAttrs>,\n"
        "  defaultPrintedStyleAttrs: defaultPrintedStyleAttributes as Metadata.DefaultAttributes<ModuleAttrs>,\n"
        "  placeholderContent,\n"
        "  renderers: { edit: ModuleEdit },\n"
        "};\n",
        encoding='utf-8'
    )

    # PHP module class + traits
    (php_dir / f"{class_name}.php").write_text(
        f"""<?php\n/**\n * {title} module.\n */\n\n"
        f"namespace OA\\Modules\\{class_name};\n\n"
        "if ( ! defined( 'ABSPATH' ) ) {\n    die( 'Direct access forbidden.' );\n}\n\n"
        f"require_once __DIR__ . '/{class_name}Trait/RenderCallbackTrait.php';\n"
        f"require_once __DIR__ . '/{class_name}Trait/ModuleClassnamesTrait.php';\n"
        f"require_once __DIR__ . '/{class_name}Trait/ModuleStylesTrait.php';\n"
        f"require_once __DIR__ . '/{class_name}Trait/ModuleScriptDataTrait.php';\n\n"
        f"class {class_name} {{\n"
        f"    use {class_name}Trait\\RenderCallbackTrait;\n"
        f"    use {class_name}Trait\\ModuleClassnamesTrait;\n"
        f"    use {class_name}Trait\\ModuleStylesTrait;\n"
        f"    use {class_name}Trait\\ModuleScriptDataTrait;\n"
        "}\n",
        encoding='utf-8'
    )

    (php_trait_dir / 'ModuleClassnamesTrait.php').write_text(
        """<?php\n/**\n * Module::module_classnames().\n */\n\n"
        f"namespace OA\\Modules\\{class_name}\\{class_name}Trait;\n\n"
        "if ( ! defined( 'ABSPATH' ) ) {\n    die( 'Direct access forbidden.' );\n}\n\n"
        "use ET\\Builder\\Packages\\Module\\Options\\Text\\TextClassnames;\n\n"
        "trait ModuleClassnamesTrait {\n"
        "    public static function module_classnames( $args ) {\n"
        "        $classnames_instance = $args['classnamesInstance'];\n"
        "        $attrs               = $args['attrs'];\n\n"
        "        $text_options_classnames = TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] );\n"
        "        if ( $text_options_classnames ) {\n"
        "            $classnames_instance->add( $text_options_classnames, true );\n"
        "        }\n"
        "    }\n"
        "}\n",
        encoding='utf-8'
    )

    (php_trait_dir / 'ModuleStylesTrait.php').write_text(
        """<?php\n/**\n * Module::module_styles().\n */\n\n"
        f"namespace OA\\Modules\\{class_name}\\{class_name}Trait;\n\n"
        "if ( ! defined( 'ABSPATH' ) ) {\n    die( 'Direct access forbidden.' );\n}\n\n"
        "use ET\\Builder\\FrontEnd\\Module\\Style;\n\n"
        "trait ModuleStylesTrait {\n"
        "    public static function module_styles( $args ) {\n"
        "        $elements = $args['elements'];\n"
        "        $settings = $args['settings'] ?? [];\n\n"
        "        Style::add(\n"
        "            [\n"
        "                'id'            => $args['id'],\n"
        "                'name'          => $args['name'],\n"
        "                'orderIndex'    => $args['orderIndex'],\n"
        "                'storeInstance' => $args['storeInstance'],\n"
        "                'styles'        => [\n"
        "                    $elements->style(\n"
        "                        [\n"
        "                            'attrName'   => 'module',\n"
        "                            'styleProps' => [\n"
        "                                'disabledOn' => [\n"
        "                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,\n"
        "                                ],\n"
        "                            ],\n"
        "                        ]\n"
        "                    ),\n"
        "                ],\n"
        "            ]\n"
        "        );\n"
        "    }\n"
        "}\n",
        encoding='utf-8'
    )

    (php_trait_dir / 'ModuleScriptDataTrait.php').write_text(
        """<?php\n/**\n * Module::module_script_data().\n */\n\n"
        f"namespace OA\\Modules\\{class_name}\\{class_name}Trait;\n\n"
        "if ( ! defined( 'ABSPATH' ) ) {\n    die( 'Direct access forbidden.' );\n}\n\n"
        "use ET\\Builder\\Packages\\Module\\Layout\\Components\\MultiView\\MultiViewScriptData;\n"
        "use ET\\Builder\\Packages\\Module\\Options\\Element\\ElementScriptData;\n\n"
        "trait ModuleScriptDataTrait {\n"
        "    public static function module_script_data( $args ) {\n"
        "        $id             = $args['id'] ?? '';\n"
        "        $selector       = $args['selector'] ?? '';\n"
        "        $attrs          = $args['attrs'] ?? [];\n"
        "        $store_instance = $args['storeInstance'] ?? null;\n\n"
        "        $module_decoration_attrs = $attrs['module']['decoration'] ?? [];\n\n"
        "        ElementScriptData::set(\n"
        "            [\n"
        "                'id'            => $id,\n"
        "                'selector'      => $selector,\n"
        "                'attrs'         => array_merge(\n"
        "                    $module_decoration_attrs,\n"
        "                    [\n"
        "                        'link' => $attrs['module']['advanced']['link'] ?? [],\n"
        "                    ]\n"
        "                ),\n"
        "                'storeInstance' => $store_instance,\n"
        "            ]\n"
        "        );\n\n"
        "        MultiViewScriptData::set(\n"
        "            [\n"
        "                'id'            => $id,\n"
        "                'name'          => $args['name'] ?? '',\n"
        "                'hoverSelector' => $selector,\n"
        "                'setContent'    => [],\n"
        "            ]\n"
        "        );\n"
        "    }\n"
        "}\n",
        encoding='utf-8'
    )

    (php_trait_dir / 'RenderCallbackTrait.php').write_text(
        """<?php\n/**\n * Module::render_callback().\n */\n\n"
        f"namespace OA\\Modules\\{class_name}\\{class_name}Trait;\n\n"
        "if ( ! defined( 'ABSPATH' ) ) {\n    die( 'Direct access forbidden.' );\n}\n\n"
        "use ET\\Builder\\FrontEnd\\BlockParser\\BlockParserStore;\n"
        "use ET\\Builder\\Packages\\Module\\Module;\n"
        "use ET\\Builder\\Packages\\Module\\Options\\Element\\ElementComponents;\n"
        "use ET\\Builder\\Framework\\Utility\\HTMLUtility;\n"
        f"use OA\\Modules\\{class_name}\\{class_name};\n\n"
        "trait RenderCallbackTrait {\n"
        "    public static function render_callback( $attrs, $content, $block, $elements ) {\n"
        "        $markup = HTMLUtility::render([\n"
        "            'tag' => 'div',\n"
        "            'attributes' => [ 'class' => 'oa-module-placeholder' ],\n"
        "            'children' => 'Module markup goes here.',\n"
        "        ]);\n\n"
        "        if ( ! is_object( $elements ) || ! method_exists( $elements, 'render' ) || ! class_exists( '\\\\ET\\\\Builder\\\\Packages\\\\Module\\\\Module' ) ) {\n"
        "            return $markup;\n"
        "        }\n\n"
        "        $parsed_block  = is_object( $block ) ? ( $block->parsed_block ?? [] ) : [];\n"
        "        $block_id      = $parsed_block['id'] ?? '';\n"
        "        $store_instance = $parsed_block['storeInstance'] ?? '';\n"
        "        $order_index    = $parsed_block['orderIndex'] ?? 0;\n\n"
        "        if ( $block_id === '' || $store_instance === '' ) {\n"
        "            return $markup;\n"
        "        }\n\n"
        "        $parent       = BlockParserStore::get_parent( $block_id, $store_instance );\n"
        "        $parent_attrs = $parent->attrs ?? [];\n\n"
        "        return Module::render(\n"
        "            [\n"
        "                'orderIndex'          => $order_index,\n"
        "                'storeInstance'       => $store_instance,\n"
        "                'attrs'               => $attrs,\n"
        "                'elements'            => $elements,\n"
        "                'id'                  => $block_id,\n"
        "                'name'                => $block->block_type->name,\n"
        "                'moduleCategory'      => $block->block_type->category,\n"
        f"                'classnamesFunction'  => [ {class_name}::class, 'module_classnames' ],\n"
        f"                'stylesComponent'     => [ {class_name}::class, 'module_styles' ],\n"
        f"                'scriptDataComponent' => [ {class_name}::class, 'module_script_data' ],\n"
        "                'parentAttrs'         => $parent_attrs,\n"
        "                'parentId'            => $parent->id ?? '',\n"
        "                'parentName'          => $parent->blockName ?? '',\n"
        "                'children'            => [\n"
        "                    ElementComponents::component(\n"
        "                        [\n"
        "                            'attrs'         => $attrs['module']['decoration'] ?? [],\n"
        "                            'id'            => $block_id,\n"
        "                            'orderIndex'    => $order_index,\n"
        "                            'storeInstance' => $store_instance,\n"
        "                        ]\n"
        "                    ),\n"
        "                    $markup,\n"
        "                ],\n"
        "            ]\n"
        "        );\n"
        "    }\n"
        "}\n",
        encoding='utf-8'
    )

    print(f"Created module skeleton for {module_name} at {src_dir}")
    print("Next: register in src/index.ts, client-logos-extension.php, and Modules.php")
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
