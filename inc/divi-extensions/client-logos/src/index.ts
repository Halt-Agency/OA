import { omit } from 'lodash';

import { addAction } from '@wordpress/hooks';

import { registerModule } from '@divi/module-library';

import { clientLogosMarquee } from './components/client-logos-marquee';

import './module-icons';

// Register modules.
addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'oaClientLogos', () => {
  registerModule(clientLogosMarquee.metadata, omit(clientLogosMarquee, 'metadata'));
});
