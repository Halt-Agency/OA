import React, { ReactElement } from 'react';

import type { ModuleScriptDataProps } from '@divi/module';

import { HaltAdvancedTabsAttrs } from './types';

export const ModuleScriptData = ({ elements }: ModuleScriptDataProps<HaltAdvancedTabsAttrs>): ReactElement => (
  <>
    {elements.scriptData({
      attrName: 'module',
    })}
  </>
);
