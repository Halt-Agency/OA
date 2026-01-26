import { TextClassnames } from '@divi/module';

import { ClientLogosMarqueeAttrs } from './types';

export const moduleClassnames = ({
  classnamesInstance,
  attrs,
}: {
  classnamesInstance: { add: (value: string, replace: boolean) => void };
  attrs: ClientLogosMarqueeAttrs;
}): void => {
  const textOptionsClassnames = TextClassnames.text_options_classnames(
    attrs?.module?.advanced?.text || {}
  );

  if (textOptionsClassnames) {
    classnamesInstance.add(textOptionsClassnames, true);
  }
};
