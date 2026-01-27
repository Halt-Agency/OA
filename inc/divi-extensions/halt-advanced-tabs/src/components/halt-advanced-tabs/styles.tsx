import React, { ReactElement } from 'react';

import { CssStyle, StyleContainer } from '@divi/module';

import { HaltAdvancedTabsAttrs } from './types';

import type { StylesProps } from '@divi/module';

const cssFields = {};

export const ModuleStyles = ({
  attrs,
  elements,
  settings,
  orderClass,
  mode,
  state,
  noStyleTag,
}: StylesProps<HaltAdvancedTabsAttrs>): ReactElement => (
  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
    {elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    })}
    <CssStyle selector={orderClass} attr={attrs?.css} cssFields={cssFields} />
  </StyleContainer>
);
