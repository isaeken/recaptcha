# Laravel Recaptcha

## installation
````bash
composer require isaeken/recaptcha
````

## usage

### frontend

#### blade
````blade
<form action="?" method="GET">
    @csrf
    <img src="{{ route('isaeken.recaptcha.image') }}" />
    <input type="text" name="value">
    <button submit>Check</button>
</form>
````

#### your own
````php
public function index(Request $request)
{
    $recaptcha = new Recaptcha;
    $recaptcha->draw();
    $recaptcha->setSession();
    $recaptcha->dark(); // optional
    return $recaptcha->response();
}
````

### backend
````php
public function index(Request $request)
{
    $request->validate([
        'value' => 'required|recaptcha'
    ]);
    return 'ok';
}
````

#### alternative
````php
public function index(Request $request)
{
    $validate = IsaEken\Recaptcha\Recaptcha::validate(
        $request->get('value'),
        0, // tolerance,
        false // ignore uppercase or lowercase
    );

    // or

    $validate = IsaEken\Recaptcha\Recaptcha::validateOnce(
        $request->get('value'),
        0, // tolerance,
        false // ignore uppercase or lowercase
    );
}
````
