import React, { ReactElement } from 'react';

import { ModuleContainer } from '@divi/module';

import { HaltAdvancedTabsEditProps } from './types';
import { ModuleStyles } from './styles';
import { moduleClassnames } from './module-classnames';
import { ModuleScriptData } from './module-script-data';

export const HaltAdvancedTabsEdit = (props: HaltAdvancedTabsEditProps): ReactElement => {
  const {
    attrs,
    elements,
    id,
    name,
  } = props;

  const tabTitles = [
    attrs?.settings?.innerContent?.desktop?.value?.tab1_title || 'Tab One',
    attrs?.settings?.innerContent?.desktop?.value?.tab2_title || 'Tab Two',
    attrs?.settings?.innerContent?.desktop?.value?.tab3_title || 'Tab Three',
    attrs?.settings?.innerContent?.desktop?.value?.tab4_title || 'Tab Four',
    attrs?.settings?.innerContent?.desktop?.value?.tab5_title || 'Tab Five',
  ].filter(Boolean);

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
      <div className="halt_advanced_tabs__placeholder">
        <div className="halt_advanced_tabs__placeholder-tabs">
          {tabTitles.map((title, index) => (
            <div
              key={`${title}-${index}`}
              className={
                index === 0
                  ? 'halt_advanced_tabs__placeholder-tab is-active'
                  : 'halt_advanced_tabs__placeholder-tab'
              }
            >
              {title}
            </div>
          ))}
        </div>
        <div className="halt_advanced_tabs__placeholder-panel">
          <div className="halt_advanced_tabs__placeholder-heading">
            {tabTitles[0] || 'Tab Content'}
          </div>
          <div className="halt_advanced_tabs__placeholder-body">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Update the tab content
            fields to control this panel.
          </div>
          <div className="halt_advanced_tabs__placeholder-buttons">
            <span className="halt_advanced_tabs__placeholder-button">Button One</span>
            <span className="halt_advanced_tabs__placeholder-button">Button Two</span>
          </div>
        </div>
      </div>
    </ModuleContainer>
  );
};
