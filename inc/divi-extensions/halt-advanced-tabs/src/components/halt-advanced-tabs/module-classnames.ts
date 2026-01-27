import { textOptionsClassnames } from '@divi/module';

import { HaltAdvancedTabsAttrs } from './types';

import type { ModuleClassnamesParams } from '@divi/module';

export const moduleClassnames = ({
  classnamesInstance,
  attrs,
}: ModuleClassnamesParams<HaltAdvancedTabsAttrs>): void => {
  classnamesInstance.add(textOptionsClassnames(attrs?.module?.advanced?.text));
};
