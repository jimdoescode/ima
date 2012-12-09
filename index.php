<?php

ini_set('max_execution_time', 360);

/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

require 'IMA/IMA.php';

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();

//Add middleware that will see if the url has already
//been processed and thusly we have an image for it.
$app->add(new \IMA\CacheMiddleware());

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, and `Slim::delete`
 * is an anonymous function.
 */
$app->get('/', function() use($app)
{
    $app->render('home.php');
});

/**
 * Route to resize an image
 * Optional: Dimensions to resize the image to.
 */
$app->get('/resize(/:dims)', function($dims = 'default') use($app)
{
    \IMA\run($app, function(&$raw, $meta) use($dims)
    {
        $dims = \IMA\Image::parse_dims($dims, $meta[0], $meta[1]);
        $raw->resizeImage($dims[0], $dims[1], Imagick::FILTER_LANCZOS, 0);
    });

})->conditions(array('dims' => '\d+x\d+|\d+x|x\d+|'.implode('|', array_keys(\IMA\Config::resize('dimension_aliases')->raw()))));

/**
 * Route to rotate an image
 * Optional: Rotation amount in degrees
 */
$app->get('/rotate(/:deg)', function($deg = -90) use($app)
{
    \IMA\run($app, function(&$raw, $meta) use($deg)
    {
        $raw->rotateImage(new ImagickPixel('#00000000'), $deg);
    });

})->conditions(array('deg' => '\d+|\-\d+'));

/**
 * Route to crop an image
 * Required: Top left corner to start crop.
 * Optional: Bottom right corner to stop crop.
 */
$app->get('/crop/:tl(/:br)', function($tl, $br = null) use($app)
{
    \IMA\run($app, function(&$raw, $meta) use($tl, $br)
    {
        $tl = \IMA\Image::parse_point($tl, 0, 0);
        $br = \IMA\Image::parse_point($br, $meta[0], $meta[1]);

        $width  = $br[0] - $tl[0];
        $height = $br[1] - $tl[1];

        if($width > 0 && $height > 0)
            $raw->cropImage($width, $height, $tl[0], $tl[1]);
    });

})->conditions(array('tl'=>'\d+,\d+', 'br'=>'\d+,\d+')); //This doesn't match for some reason

/**
 * Route to filter an image.
 * Required: The type of filter to apply to the image. Available filters can be found in the config
 */
$app->get('/filter/:type(/:params)', function($type, $params = null) use($app)
{
    \IMA\run($app, function(&$raw, $meta) use($type, $params)
    {
        $args = \IMA\Image::parse_point($params, 0, 1);
        $filters = \IMA\Config::filter('types');
        call_user_func_array(array($raw, $filters[$type]), $args);
    });

})->conditions(array('type' => implode('|', array_keys(\IMA\Config::filter('types')->raw()))));;

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
