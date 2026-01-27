<?php
/**
 * Register module dependencies.
 */

namespace OA\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use OA\Modules\HaltAdvancedTabs\HaltAdvancedTabs;

add_action(
    'divi_module_library_modules_dependency_tree',
    function ( $dependency_tree ) {
        $dependency_tree->add_dependency( new HaltAdvancedTabs() );
    }
);
