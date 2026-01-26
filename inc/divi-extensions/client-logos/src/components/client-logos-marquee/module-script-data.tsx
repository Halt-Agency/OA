import React from 'react';

import {
  ElementScriptData,
  MultiViewScriptData,
} from '@divi/module';

import { ClientLogosMarqueeAttrs } from './types';

export const ModuleScriptData = ({
  id,
  name,
  selector,
  attrs,
  storeInstance,
}: {
  id: string;
  name: string;
  selector: string;
  attrs: ClientLogosMarqueeAttrs;
  storeInstance: unknown;
}) => {
  ElementScriptData.set({
    id,
    selector,
    attrs: {
      ...(attrs?.module?.decoration || {}),
      link: attrs?.module?.advanced?.link || {},
    },
    storeInstance,
  });

  MultiViewScriptData.set({
    id,
    name,
    hoverSelector: selector,
    setContent: [],
  });

  return null;
};
