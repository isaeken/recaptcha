<?php
/**
 * @author İsa Eken <hello@isaeken.com.tr>
 * @version 1.0
 * @license MIT
 */

namespace IsaEken\Recaptcha;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * Class RecaptchaServiceProvider
 * @package IsaEken\Recaptcha
 */
class RecaptchaServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->bootValidator();
    }

    /**
     *
     */
    public function register()
    {

    }

    /**
     *
     */
    protected function bootValidator()
    {
        Validator::extend('recaptcha', function ($attribute, $value, $parameters, $validator) {
            return Recaptcha::validateOnce($value);
        });

        Validator::replacer('recaptcha', function ($message, $attribute, $rule, $parameters) {
            return ($message == 'validation.recaptcha' ? __('Invalid captcha') : $message);
        });
    }
}
