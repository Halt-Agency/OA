<?php
/**
 * Client Logos Marquee module.
 */

namespace OA\Modules\ClientLogosMarquee;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class ClientLogosMarquee implements DependencyInterface {
    use ClientLogosMarqueeTrait\RenderCallbackTrait;
    use ClientLogosMarqueeTrait\ModuleClassnamesTrait;
    use ClientLogosMarqueeTrait\ModuleStylesTrait;
    use ClientLogosMarqueeTrait\ModuleScriptDataTrait;

    public function load() {
        $module_json_folder_path = OA_CLIENT_LOGOS_EXTENSION_JSON_PATH . 'client-logos-marquee/';

        add_action(
            'init',
            function() use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ ClientLogosMarquee::class, 'render_callback' ],
                    ]
                );
            }
        );
    }
}
