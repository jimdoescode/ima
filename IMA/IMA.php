<?php namespace IMA;

require 'Config.php';
require 'CacheMiddleware.php';

/**
 * @param $app \Slim\Slim
 * @param $operation
 */
function run($app, $operation)
{
    if(!array_key_exists('src', $_GET) || empty($_GET['src']))
        $app->halt(400, 'Source image must be specified.');

    $remote = $_GET['src'];
    $path = remote_cache($remote);

    if(is_null($path))
        $app->halt(400, 'Could not locate this image.');

    $image = new Image($path);
    if($image->has_errors())
    {
        uncache('remote_cache_directory', $remote);
        $app->halt(400, $image->get_error());
    }

    $response = $app->response();
    $response['Content-Type'] = $image->content;

    $image->operate($operation, md5($app->request()->getPath().$remote));

    if($image->has_errors())
    {
        $response['Content-Type'] = 'text/html';
        $app->halt(400, $image->get_error());
    }
}

function configured_path($config, $filename)
{
    return Config::main($config)->alter(function($items) use($filename)
    {
        return $items[0] . '/' . $filename;
    });
}

function remote_cache($remote)
{
    $path = configured_path('remote_cache_directory', md5($remote));

    if(file_exists($path))
        return $path;

    $image = @file_get_contents($remote);
    if($image === false)
        return null;

    $fp = fopen($path, 'w');
    fwrite($fp, $image);
    fclose($fp);

    return $path;
}

function uncache($cache, $remote)
{
    $path = configured_path($cache, md5($remote));

    if(file_exists($path))
        unlink($path);
}
