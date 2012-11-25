<?php namespace Photog;

require 'Config.php';

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

