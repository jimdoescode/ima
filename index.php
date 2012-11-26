<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim.php';
require 'Photog/Photog.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, and `Slim::delete`
 * is an anonymous function.
 */
$app->get('/resize(/:dims)', function($dims = 'default') use($app)
{
    \Photog\run($app, function($raw, $meta) use($dims)
    {
        $dims = \Photog\Image::parse_dims($dims, $meta[0], $meta[1]);
        $new_image = imagecreatetruecolor($dims[0], $dims[1]);
        imagecopyresampled($new_image, $raw, 0, 0, 0, 0, $dims[0], $dims[1], $meta[0], $meta[1]);

        return $new_image;
    });

})->conditions(['dims' => '\d+x\d+|\d+x|x\d+|'.\Photog\implode_config_params('dimension_aliases', '|')]);


$app->get('/rotate(/:deg)', function($deg = -90) use($app)
{
    \Photog\run($app, function($raw, $meta) use($deg)
    {
        return imagerotate($raw, $deg, 0);
    });

})->conditions(['deg' => '\d+|\-\d+']);


$app->get('/crop/:tl(/:br)', function($tl, $br = null) use($app)
{
    \Photog\run($app, function($raw, $meta) use($tl, $br)
    {
        $tl = \Photog\Image::parse_point($tl, 0, 0);
        $br = \Photog\Image::parse_point($br, $meta[0], $meta[1]);

        $width  = $br[0] - $tl[0];
        $height = $br[1] - $tl[1];

        if($width > 0 && $height > 0)
        {
            $new_image = imagecreatetruecolor($width, $height);
            imagecopyresampled($new_image, $raw, 0, 0, $tl[0], $tl[1], $width, $height, $width, $height);

            return $new_image;
        }
        return null;
    });

})->conditions(['tl'=>'\d+,\d+', 'br'=>'\d+,\d+']); //This doesn't match for some reason


$app->get('/filter/:type', function($type) use($app)
{
    \Photog\run($app, function($raw, $meta) use($type)
    {
        $new_image = imagecreatetruecolor($meta[0], $meta[1]);
        imagecopyresampled($new_image, $raw, 0, 0, 0, 0, $meta[0], $meta[1], $meta[0], $meta[1]);
        imagefilter($new_image, \Photog\Config::main('filters')[$type]);

        return $new_image;
    });

})->conditions(['type' => \Photog\implode_config_params('filters', '|')]);;

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
