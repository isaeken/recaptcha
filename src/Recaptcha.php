<?php
/**
 * @author İsa Eken <hello@isaeken.com.tr>
 * @version 1.0
 * @license MIT
 */

namespace IsaEken\Recaptcha;

use Illuminate\Support\Facades\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Image;

/**
 * Class Recaptcha
 * @package IsaEken\Recaptcha
 */
class Recaptcha
{
    /**
     * Options
     *
     * @var array
     */
    private $options;

    /**
     * Printed characters count
     *
     * @var int
     */
    private $printedCharacters = 0;

    /**
     * Get value
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->options)) return $this->options[$name];
        return $this->$name;
    }

    /**
     * Set value
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->options)) $this->options[$name] = $value;
        else $this->$name = $value;
    }

    /**
     * Recaptcha ImageManager
     *
     * @var ImageManager
     */
    public $manager;

    /**
     * Recaptcha canvas
     *
     * @var Image
     */
    public $canvas;

    /**
     * Generated characters
     *
     * @var array
     */
    public $characters = [];

    /**
     * Recaptcha constructor.
     *
     * @param string $driver
     */
    public function __construct($driver = 'gd')
    {
        // create manager and set driver
        $this->manager = new ImageManager([
            'driver' => $driver
        ]);

        // set default values
        // DO NOT "REMOVE" ANY ITEMS
        $this->options = array(
            'width' => 'auto',
            'height' => 'auto',
            'background' => 'random',
            'foreground' => 'random',
            'characters' => 'ABCDEFGHIJKLMNOPRSTUVYZabcdefghijklmnoprstuvyz0123456789',
            'characterMargin' => 5,
            'length' => 5,
            'fontFile' => __DIR__.'/Font/RobotoMono-Light.ttf',
            'fontSize' => 24,
            'verticalInstability' => 4,
            'horizontalInstability' => 4,
            'verticalMargin' => 0,
            'horizontalMargin' => 4,
            'angleInstability' => 12,
            'circles' => 3,
            'circleBorderInstability' => 2,
            'lines' => 10,
        );
    }

    /**
     * Create random color
     *
     * @param bool $dark
     * @param int $transparency
     * @return string
     */
    private function randomColor(bool $dark = false, $transparency = null) : string
    {
        $color = (object) array( 'r', 'g', 'b' );

        $palette = (object) array(
            'dark' => (object) array(
                'r' => (object) array( 'min' => 0, 'max' => 120 ),
                'g' => (object) array( 'min' => 0, 'max' => 120 ),
                'b' => (object) array( 'min' => 0, 'max' => 120 ),
            ),
            'light' => (object) array(
                'r' => (object) array( 'min' => 200, 'max' => 255 ),
                'g' => (object) array( 'min' => 200, 'max' => 255 ),
                'b' => (object) array( 'min' => 200, 'max' => 255 ),
            ),
        );

        if ($dark)
        {
            $color->r = rand($palette->dark->r->min, $palette->dark->r->max);
            $color->g = rand($palette->dark->r->min, $palette->dark->r->max);
            $color->b = rand($palette->dark->r->min, $palette->dark->r->max);
        }
        else
        {
            $color->r = rand($palette->light->r->min, $palette->light->r->max);
            $color->g = rand($palette->light->r->min, $palette->light->r->max);
            $color->b = rand($palette->light->r->min, $palette->light->r->max);
        }

        if ($transparency == null) return 'rgb('.$color->r.', '.$color->g.', '.$color->b.')';
        else return 'rgba('.$color->r.', '.$color->g.', '.$color->b.', '.$transparency.')';
    }

    /**
     * Create recaptcha canvas
     *
     * @return $this
     */
    public function createCanvas() : Recaptcha
    {
        // create random color
        if ($this->__get('background') == 'random') $this->__set('background', $this->randomColor(false));

        // calculate minimum width
        $minWidth = ($this->__get('fontSize') * ($this->__get('length') + 1)) + $this->__get('horizontalInstability') + ($this->__get('horizontalMargin') * 2) + ($this->__get('characterMargin') * $this->__get('length'));

        // calculate minimum height
        $minHeight = ($this->__get('fontSize') * 2) + ($this->__get('fontSize') / 2) + ($this->__get('verticalMargin') * 2);

        // check width
        if ($this->__get('width') == 'auto') $this->__set('width', $minWidth);
        else if ($this->__get('width') < $minWidth) throw new \InvalidArgumentException('Your configs are required minimum width: '.$minWidth);

        // check height
        if ($this->__get('height') == 'auto') $this->__set('height', $minHeight);
        else if ($this->__get('height') < $minHeight) throw new \InvalidArgumentException('Your configs are required minimum height: '.$minHeight);

        // create canvas
        $this->canvas = $this->manager->canvas(
            $this->__get('width'),
            $this->__get('height'),
            $this->__get('background')
        );

        return $this;
    }

    /**
     * Create character
     *
     * @param $character
     * @return $this
     */
    public function createCharacter($character) : Recaptcha
    {
        // set defaults
        $position = (object) array( 'x' => $this->__get('fontSize') - rand(0 - $this->__get('horizontalInstability'), $this->__get('horizontalInstability')), 'y' => 0 );

        // set y position from height - font size
        $position->y = $this->canvas->height() / 2;

        // set x position from font size
        foreach ($this->characters as $index) $position->x += $this->__get('fontSize');

        // set x position instability
        $position->x -= rand(0 - $this->__get('horizontalInstability'), $this->__get('horizontalInstability'));

        // add horizontal margin
        $position->x += $this->__get('horizontalMargin');

        // add character margin
        if ($this->printedCharacters > 0) $position->x += $this->__get('characterMargin') * $this->printedCharacters;

        // set y position instability
        $position->y -= rand(0, $this->__get('verticalInstability'));

        // print character to canvas
        $this->canvas->text($character, $position->x, $position->y, function ($font) {

            // set character font file
            $font->file($this->__get('fontFile'));

            // set character font size
            $font->size($this->__get('fontSize'));

            // set character angle
            $font->angle(rand(0 - $this->__get('angleInstability'), $this->__get('angleInstability')));

            // set character align
            $font->align('center');

            // set character vertical align
            $font->valign('center');

            // set character color
            if ($this->__get('foreground') == 'random') $font->color($this->randomColor(true));
            else $font->color($this->__get('foreground'));
        });

        // add character to character list
        array_push($this->characters, $character);

        // character printed
        $this->printedCharacters++;
        return $this;
    }

    /**
     * Create random circle
     *
     * @return $this
     */
    public function createCircle() : Recaptcha
    {
        $circle = (object) array(
            'x' => rand(0, $this->canvas->width()),
            'y' => rand(0, $this->canvas->height()),
            'radius' => rand(5, 120),
        );

        // draw circle
        $this->canvas->circle($circle->radius, $circle->x, $circle->y, function ($draw) {

            // create circle border
            $draw->border(

                // set border width
                rand(1, $this->__get('circleBorderInstability')),

                // set border color
                $this->randomColor(false, '0.75')
            );
        });

        return $this;
    }

    /**
     * Create random line
     *
     * @return $this
     */
    public function createLine() : Recaptcha
    {
        $line = (object) array(
            'x' => array(
                rand(0, $this->canvas->width()),
                rand(0, $this->canvas->width()),
            ),
            'y' => array(
                rand(0, $this->canvas->height()),
                rand(0, $this->canvas->height()),
            ),
        );

        // draw line
        $this->canvas->line($line->x[0], $line->y[0], $line->x[1], $line->y[1], function ($draw) {

            // set line color
            $draw->color($this->randomColor(true, '0.1'));
        });

        return $this;
    }

    /**
     * Draw recaptcha
     *
     * @return $this
     */
    public function draw() : Recaptcha
    {
        // first create canvas
        $this->createCanvas();

        // create circles
        for ($index = 0; $index < $this->__get('circles'); $index++) $this->createCircle();

        // create lines
        for ($index = 0; $index < $this->__get('lines'); $index++) $this->createLine();

        // convert characters to array
        $characters = str_split($this->__get('characters'));

        // create characters
        for ($index = 0; $index < $this->__get('length'); $index++) $this->createCharacter($characters[array_rand($characters)]);

        return $this;
    }

    /**
     * Reverse colors
     *
     * @return $this
     */
    public function dark() : Recaptcha
    {
        // reverse color of canvas
        $this->canvas->invert();
        return $this;
    }

    /**
     * Set captcha session
     *
     * @return $this
     */
    public function setSession() : Recaptcha
    {
        // set session to captcha value
        Request::session()->put('isaeken-recaptcha-value', $this->value());

        // save session
        Request::session()->save();

        return $this;
    }

    /**
     * Get captcha value
     *
     * @return string
     */
    public function value() : string
    {
        return implode($this->characters);
    }

    /**
     * Return's HTTP response
     *
     * @return mixed
     */
    public function response()
    {
        return $this->canvas->response();
    }

    /**
     * Validate recaptcha
     *
     * @param string $value
     * @param int $sensitivity
     * @param bool $ignoreUppercase
     * @return bool
     */
    public static function validate(string $value, int $sensitivity = 0, bool $ignoreUppercase = false) : bool
    {
        // get captcha value from session
        $key = Request::session()->get('isaeken-recaptcha-value');

        // check value length more than 0
        if (strlen($key) < 1) return false;

        // ignore uppercase or lowercase
        $key = ($ignoreUppercase ? strtolower($key) : $key);
        $value = ($ignoreUppercase ? strtolower($value) : $value);

        // check value
        return levenshtein($key, $value) <= $sensitivity;
    }

    /**
     * Validate once
     *
     * @param string $value
     * @param int $sensitivity
     * @param bool $ignoreUppercase
     * @return bool
     */
    public static function validateOnce(string $value, int $sensitivity = 0, bool $ignoreUppercase = false) : bool
    {
        // validate and get result
        $result = self::validate($value, $sensitivity, $ignoreUppercase);

        // remove recaptcha value from session
        Request::session()->put('isaeken-recaptcha-value', null);

        // save session
        Request::session()->save();

        return $result;
    }
}
