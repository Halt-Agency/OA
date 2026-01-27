import React, { ReactElement } from 'react';

import {
  CssStyle,
  StyleContainer,
  StylesProps,
} from '@divi/module';

import { ClientLogosMarqueeAttrs } from './types';
import { cssFields } from './custom-css';

export const ModuleStyles = ({
  attrs,
  elements,
  settings,
  orderClass,
  mode,
  state,
  noStyleTag,
}: StylesProps<ClientLogosMarqueeAttrs>): ReactElement => {
  return (
    <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
      {elements.style({
        attrName: 'module',
        styleProps: {
          disabledOn: {
            disabledModuleVisibility: settings?.disabledModuleVisibility,
          },
        },
      })}
      <CssStyle selector={orderClass} attr={attrs.css} cssFields={cssFields} />
    </StyleContainer>
  );
};
