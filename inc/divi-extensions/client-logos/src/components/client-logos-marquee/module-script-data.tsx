import React, { Fragment, ReactElement } from 'react';

import { ModuleScriptDataProps } from '@divi/module';

import { ClientLogosMarqueeAttrs } from './types';

export const ModuleScriptData = ({
  elements,
}: ModuleScriptDataProps<ClientLogosMarqueeAttrs>): ReactElement => (
  <Fragment>
    {elements.scriptData({
      attrName: 'module',
    })}
  </Fragment>
);
