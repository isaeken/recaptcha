<?php
/**
 * @author İsa Eken <hello@isaeken.com.tr>
 * @version 1.0
 * @license MIT
 */

Route::middleware('web')->get('/isaeken/recaptcha/image', 'IsaEken\Recaptcha\Http\Controllers\RecaptchaController@image')->name('isaeken.recaptcha.image');
