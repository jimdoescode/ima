<?php namespace Photog;

require 'Config.php';

/**
 * @param $app \Slim\Slim
 * @param $operation
 */
function run($app, $operation)
{
    if(!array_key_exists('src', $_GET))
        $app->halt(400, 'No source image specified.');

    $remote = $_GET['src'];
    $path = cache($remote);

    if(is_null($path))
        $app->halt(400, 'Could not locate this image.');

    $image = new Image($path);
    if($image->has_errors())
    {
        uncache($remote);
        $app->halt(400, $image->get_error());
    }

    $response = $app->response();
    $response['Content-Type'] = 'image/jpeg';

    $image->operate($operation);

    if($image->has_errors())
    {
        $response['Content-Type'] = 'text/html';
        $app->halt(400, $image->get_error());
    }
}

function implode_aliases($glue)
{
    return Config::main('dimension_aliases')->alter(function($items) use($glue)
    {
        $keys = array_keys($items);
        return implode($glue, $keys);
    });
}

function configured_path($config, $filename)
{
    return Config::main($config)->alter(function($items) use($filename)
    {
        return $items[0] . '/' . $filename;
    });
}

function cache($remote)
{
    $path = configured_path('cache_directory', md5($remote));

    if(file_exists($path))
        return $path;

    $image = file_get_contents($remote);
    if($image === false)
        return null;

    $fp = fopen($path, 'w');
    fwrite($fp, $image);
    fclose($fp);

    return $path;
}

function uncache($remote)
{
    $path = configured_path('cache_directory', md5($remote));

    if(file_exists($path))
        unlink($path);
}
