import { ModuleEditProps } from '@divi/module-library';
import {
  FormatBreakpointStateAttr,
  InternalAttrs,
  type Element,
  type Module,
} from '@divi/types';

export interface ClientLogosMarqueeCssAttr extends Module.Css.AttributeValue {}

export type ClientLogosMarqueeCssGroupAttr = FormatBreakpointStateAttr<ClientLogosMarqueeCssAttr>;

export interface ClientLogosMarqueeAttrs extends InternalAttrs {
  css?: ClientLogosMarqueeCssGroupAttr;
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
  settings?: {
    innerContent?: {
      desktop?: {
        value?: {
          logoVariant?: string;
          filterMode?: string;
          taxonomy?: string;
          taxonomyTerms?: string;
          speed?: string;
          direction?: string;
          grayscale?: boolean;
        };
      };
    };
  };
}

export type ClientLogosMarqueeEditProps = ModuleEditProps<ClientLogosMarqueeAttrs>;
