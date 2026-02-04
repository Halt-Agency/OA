import { addFilter } from '@wordpress/hooks';
import {
  moduleStatic,
} from './icons';

// Add module icons to the icon library.
addFilter('divi.iconLibrary.icon.map', 'haltAdvancedTabs', (icons) => {
  return {
    ...icons, // This is important. Without this, all other icons will be overwritten.
    [moduleStatic.name]:  moduleStatic,
  };
});
