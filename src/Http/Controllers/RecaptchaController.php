<?php
/**
 * @author İsa Eken <hello@isaeken.com.tr>
 * @version 1.0
 * @license MIT
 */


namespace IsaEken\Recaptcha\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use IsaEken\Recaptcha\Recaptcha;

/**
 * Class RecaptchaController
 * @package IsaEken\Recaptcha\Http\Controllers
 */
class RecaptchaController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function image(Request $request)
    {
        $recaptcha = new Recaptcha;
        $recaptcha->draw();
        $recaptcha->setSession();
        return $recaptcha->response();
    }
}
