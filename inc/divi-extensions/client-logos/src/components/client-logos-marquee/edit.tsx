import React, { ReactElement } from 'react';

import { ModuleContainer } from '@divi/module';

import { ClientLogosMarqueeEditProps } from './types';
import { ModuleStyles } from './styles';
import { moduleClassnames } from './module-classnames';
import { ModuleScriptData } from './module-script-data';

export const ClientLogosMarqueeEdit = (props: ClientLogosMarqueeEditProps): ReactElement => {
  const {
    attrs,
    elements,
    id,
    name,
  } = props;

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      name={name}
      stylesComponent={ModuleStyles}
      classnamesFunction={moduleClassnames}
      scriptDataComponent={ModuleScriptData}
    >
      {elements.styleComponents({
        attrName: 'module',
      })}
      <div className="oa_client_logos_marquee__placeholder">
        Client Logos Marquee
      </div>
    </ModuleContainer>
  );
};
