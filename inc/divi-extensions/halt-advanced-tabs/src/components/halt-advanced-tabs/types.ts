import { ModuleEditProps } from '@divi/module-library';
import {
  FormatBreakpointStateAttr,
  InternalAttrs,
  type Element,
  type Module,
} from '@divi/types';

export interface HaltAdvancedTabsCssAttr extends Module.Css.AttributeValue {}

export type HaltAdvancedTabsCssGroupAttr = FormatBreakpointStateAttr<HaltAdvancedTabsCssAttr>;

export interface HaltAdvancedTabsAttrs extends InternalAttrs {
  css?: HaltAdvancedTabsCssGroupAttr;
  module?: {
    meta?: Element.Meta.Attributes;
    advanced?: {
      link?: Element.Advanced.Link.Attributes;
      htmlAttributes?: Element.Advanced.IdClasses.Attributes;
      text?: Element.Advanced.Text.Attributes;
    };
    decoration?: Element.Decoration.PickedAttributes<
      'animation' |
      'background' |
      'border' |
      'boxShadow' |
      'disabledOn' |
      'filters' |
      'overflow' |
      'position' |
      'scroll' |
      'sizing' |
      'spacing' |
      'sticky' |
      'transform' |
      'transition' |
      'zIndex'
    > & {
      attributes?: any;
    };
  };
  button?: {
    decoration?: Element.Decoration.PickedAttributes<
      'button' |
      'background' |
      'border' |
      'boxShadow' |
      'font' |
      'spacing'
    >;
  };
  tabTitle?: {
    decoration?: Element.Decoration.PickedAttributes<'font'>;
  };
  contentHeading?: {
    decoration?: Element.Decoration.PickedAttributes<'font'>;
  };
  contentBody?: {
    decoration?: Element.Decoration.PickedAttributes<'font'>;
  };
  settings?: {
    innerContent?: {
      desktop?: {
        value?: {
          tab1_title?: string;
          tab1_content?: string;
          tab1_button1_text?: string;
          tab1_button1_url?: string;
          tab1_button2_text?: string;
          tab1_button2_url?: string;
          tab2_title?: string;
          tab2_content?: string;
          tab2_button1_text?: string;
          tab2_button1_url?: string;
          tab2_button2_text?: string;
          tab2_button2_url?: string;
          tab3_title?: string;
          tab3_content?: string;
          tab3_button1_text?: string;
          tab3_button1_url?: string;
          tab3_button2_text?: string;
          tab3_button2_url?: string;
          tab4_title?: string;
          tab4_content?: string;
          tab4_button1_text?: string;
          tab4_button1_url?: string;
          tab4_button2_text?: string;
          tab4_button2_url?: string;
          tab5_title?: string;
          tab5_content?: string;
          tab5_button1_text?: string;
          tab5_button1_url?: string;
          tab5_button2_text?: string;
          tab5_button2_url?: string;
          tabs_container_bg?: string;
          tabs_container_border?: string;
          panels_container_bg?: string;
          panels_container_border?: string;
          tabs_bg?: string;
          tabs_bg_active?: string;
        };
      };
    };
  };
}

export type HaltAdvancedTabsEditProps = ModuleEditProps<HaltAdvancedTabsAttrs>;
