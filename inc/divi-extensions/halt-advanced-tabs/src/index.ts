import { omit } from 'lodash';

import { addAction } from '@wordpress/hooks';

import { registerModule } from '@divi/module-library';

import { haltAdvancedTabs } from './components/halt-advanced-tabs';

import './module-icons';

// Register modules.
addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'haltAdvancedTabs', () => {
  registerModule(haltAdvancedTabs.metadata, omit(haltAdvancedTabs, 'metadata'));
});
