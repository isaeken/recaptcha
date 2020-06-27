<?php
/**
 * @author İsa Eken <hello@isaeken.com.tr>
 * @version 1.0
 * @license MIT
 */

namespace IsaEken\Recaptcha\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Recaptcha
 * @package IsaEken\Recaptcha\Facades
 */
class Recaptcha extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'recaptcha';
    }
}
