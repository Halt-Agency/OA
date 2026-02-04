<?php
/**
 * Halt Advanced Tabs module.
 */

namespace OA\Modules\HaltAdvancedTabs;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

require_once __DIR__ . '/HaltAdvancedTabsTrait/RenderCallbackTrait.php';
require_once __DIR__ . '/HaltAdvancedTabsTrait/ModuleClassnamesTrait.php';
require_once __DIR__ . '/HaltAdvancedTabsTrait/ModuleStylesTrait.php';
require_once __DIR__ . '/HaltAdvancedTabsTrait/ModuleScriptDataTrait.php';

class HaltAdvancedTabs {
    use HaltAdvancedTabsTrait\RenderCallbackTrait;
    use HaltAdvancedTabsTrait\ModuleClassnamesTrait;
    use HaltAdvancedTabsTrait\ModuleStylesTrait;
    use HaltAdvancedTabsTrait\ModuleScriptDataTrait;
}
