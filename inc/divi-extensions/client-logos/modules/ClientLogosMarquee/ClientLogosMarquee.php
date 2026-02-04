<?php
/**
 * Client Logos Marquee module.
 */

namespace OA\Modules\ClientLogosMarquee;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

require_once __DIR__ . '/ClientLogosMarqueeTrait/RenderCallbackTrait.php';
require_once __DIR__ . '/ClientLogosMarqueeTrait/ModuleClassnamesTrait.php';
require_once __DIR__ . '/ClientLogosMarqueeTrait/ModuleStylesTrait.php';
require_once __DIR__ . '/ClientLogosMarqueeTrait/ModuleScriptDataTrait.php';

class ClientLogosMarquee {
    use ClientLogosMarqueeTrait\RenderCallbackTrait;
    use ClientLogosMarqueeTrait\ModuleClassnamesTrait;
    use ClientLogosMarqueeTrait\ModuleStylesTrait;
    use ClientLogosMarqueeTrait\ModuleScriptDataTrait;

}
