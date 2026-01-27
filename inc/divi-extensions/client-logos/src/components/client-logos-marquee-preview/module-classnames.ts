import { ModuleClassnamesParams, textOptionsClassnames } from '@divi/module';

import { ClientLogosMarqueeAttrs } from './types';

export const moduleClassnames = ({
  classnamesInstance,
  attrs,
}: ModuleClassnamesParams<ClientLogosMarqueeAttrs>): void => {
  classnamesInstance.add(textOptionsClassnames(attrs?.module?.advanced?.text));
};
