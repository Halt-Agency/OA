import type { Metadata, ModuleLibrary } from '@divi/types';

import metadata from './module.json';
import defaultRenderAttributes from './module-default-render-attributes.json';
import defaultPrintedStyleAttributes from './module-default-printed-style-attributes.json';
import { HaltAdvancedTabsEdit } from './edit';
import { HaltAdvancedTabsAttrs } from './types';

import './style.scss';
import './module.scss';

export const haltAdvancedTabs: ModuleLibrary.Module.RegisterDefinition<HaltAdvancedTabsAttrs> = {
  metadata: metadata as Metadata.Values<HaltAdvancedTabsAttrs>,
  defaultAttrs: defaultRenderAttributes as Metadata.DefaultAttributes<HaltAdvancedTabsAttrs>,
  defaultPrintedStyleAttrs: defaultPrintedStyleAttributes as Metadata.DefaultAttributes<HaltAdvancedTabsAttrs>,
  renderers: {
    edit: HaltAdvancedTabsEdit,
  },
};
