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
    {elements.style({
      attrName: 'button',
      styleProps: {
        selector: `${orderClass} .halt-tabs__button`,
        type: 'button',
      },
    })}
    {elements.style({
      attrName: 'tabTitle',
      styleProps: {
        selector: `${orderClass} .halt-tabs__tab`,
        type: 'element',
      },
    })}
    {elements.style({
      attrName: 'contentHeading',
      styleProps: {
        selector: `${orderClass} .halt-tabs__panel h3`,
        type: 'element',
      },
    })}
    {elements.style({
      attrName: 'contentBody',
      styleProps: {
        selector: `${orderClass} .halt-tabs__content`,
        type: 'element',
      },
    })}
    <CssStyle selector={orderClass} attr={attrs?.css} cssFields={cssFields} />
  </StyleContainer>
);
