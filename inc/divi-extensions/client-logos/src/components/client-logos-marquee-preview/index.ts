import { omit } from 'lodash';

import type { Metadata, ModuleLibrary } from '@divi/types';

import metadata from './module.json';
import defaultRenderAttributes from './module-default-render-attributes.json';
import defaultPrintedStyleAttributes from './module-default-printed-style-attributes.json';
import { ClientLogosMarqueeEdit } from './edit';
import { ClientLogosMarqueeAttrs } from './types';
import { placeholderContent } from './placeholder-content';

import './style.scss';
import './module.scss';

export const clientLogosMarqueePreview: ModuleLibrary.Module.RegisterDefinition<ClientLogosMarqueeAttrs> = {
  metadata: metadata as Metadata.Values<ClientLogosMarqueeAttrs>,
  defaultAttrs: defaultRenderAttributes as Metadata.DefaultAttributes<ClientLogosMarqueeAttrs>,
  defaultPrintedStyleAttrs: defaultPrintedStyleAttributes as Metadata.DefaultAttributes<ClientLogosMarqueeAttrs>,
  placeholderContent,
  renderers: {
    edit: ClientLogosMarqueeEdit,
  },
};
