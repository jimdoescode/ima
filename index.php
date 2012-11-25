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
    if(!array_key_exists('src', $_GET))
        $app->halt(400, 'No source image specified.');

    $remote = $_GET['src'];
    $path = \Photog\cache($remote);

    if(is_null($path))
        $app->halt(400, 'Could not locate this image.');

    $image = new \Photog\Image($path);
    if($image->has_errors())
    {
        \Photog\uncache($remote);
        $app->halt(400, $image->get_error());
    }

    $response = $app->response();
    $response['Content-Type'] = 'image/jpeg';

    $image->operate(function($raw, $meta) use($dims)
    {
        $dims = \Photog\Image::parse_dims($dims, $meta[0], $meta[1]);
        $new_image = imagecreatetruecolor($dims[0], $dims[1]);
        imagecopyresampled($new_image, $raw, 0, 0, 0, 0, $dims[0], $dims[1], $meta[0], $meta[1]);

        return $new_image;
    });

    if($image->has_errors())
    {
        $response['Content-Type'] = 'text/html';
        $app->halt(400, $image->get_error());
    }

})->conditions(['dims' => '\d+x\d+|\d+x|x\d+|'.\Photog\implode_aliases('|')]);


$app->get('/rotate(/:deg)', function($deg = -90) use($app)
{
    if(!array_key_exists('src', $_GET))
        $app->halt(400, 'No source image specified.');

    $remote = $_GET['src'];
    $path = \Photog\cache($remote);
    if(is_null($path))
        $app->halt(400, 'Could not locate this image.');

    $image = new \Photog\Image($path);
    if($image->has_errors())
    {
        \Photog\uncache($remote);
        $app->halt(400, $image->get_error());
    }

    $response = $app->response();
    $response['Content-Type'] = 'image/jpeg';

    $image->operate(function($raw, $meta) use($deg)
    {
        return imagerotate($raw, $deg, 0);
    });

    if($image->has_errors())
    {
        $response['Content-Type'] = 'text/html';
        $app->halt(400, $image->get_error());
    }

})->conditions(['deg' => '\d+|\-\d+']);

$app->get('/crop/:topleft(/:botright)', function($topleft, $botright = null) use($app)
{
    if(!array_key_exists('src', $_GET))
        $app->halt(400, 'No source image specified.');

    $remote = $_GET['src'];
    $path = \Photog\cache($remote);
    if(is_null($path))
        $app->halt(400, 'Could not locate this image.');

    $image = new \Photog\Image($path);
    if($image->has_errors())
    {
        \Photog\uncache($remote);
        $app->halt(400, $image->get_error());
    }

    $response = $app->response();
    $response['Content-Type'] = 'image/jpeg';

    $image->operate(function($raw, $meta) use($topleft, $botright)
    {
        $tl = \Photog\Image::parse_point($topleft, 0, 0);
        $br = \Photog\Image::parse_point($botright, $meta[0], $meta[1]);

        $width = $br[0] - $tl[0];
        $height = $br[1] - $tl[1];

        if($width > 0 && $height > 0)
        {
            $new_image = imagecreatetruecolor($width, $height);
            imagecopyresampled($new_image, $raw, 0, 0, $tl[0], $tl[1], $width, $height, $width, $height);

            return $new_image;
        }
        return null;
    });

    if($image->has_errors())
    {
        $response['Content-Type'] = 'text/html';
        $app->halt(400, $image->get_error());
    }

});//->conditions(['topleft'  => '^\d+,\d+$']); //This doesn't match for some reason

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
