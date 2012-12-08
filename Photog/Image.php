<?php namespace Photog;

class Image
{
    private $meta;
    private $raw;
    private $errors = array();
    public $content = 'image/jpeg';

    public function __construct($path)
    {
        $this->meta = getimagesize($path);
        if($this->meta === false)
            $this->errors[] = 'Could not get image meta data.';
        else
            $this->raw = new \Imagick($path);
    }

    public function has_errors()
    {
        return !empty($this->errors);
    }

    public function get_error()
    {
        return $this->has_errors() ? $this->errors[0] : null;
    }

    public function operate($operation, $hash)
    {
        foreach($this->raw as $frame)
            $operation($frame, $this->meta);

        $this->content = $this->meta['mime'];

        if(strtolower($this->meta['mime']) === 'image/gif')
        {
            echo $this->raw->getimagesblob();
            $this->raw->writeimages(configured_path('processed_cache_directory', $hash), true);
        }
        else
        {
            echo $this->raw->getimageblob();
            $this->raw->writeimage(configured_path('processed_cache_directory', $hash));
        }
    }

    public static function parse_dims($dimstr, $origwidth, $origheight)
    {
        $aliases = Config::resize('dimension_aliases');
        //NOTE: There is a bug in PHP 5.4 where array_key_exists
        //does not return the correct result for ArrayAccess objects
        if(isset($aliases[$dimstr]))
            $dimstr = $aliases[$dimstr];

        if(is_null($dimstr))
            $dims = array($origwidth, $origheight);
        else
            $dims = explode('x', $dimstr);

        //If only one dimension is specified then scale the other appropriately.
        if(preg_match('/^x\d+$/', $dimstr) === 1)
            $dims[0] = floor(($dims[1] / $origheight) * $origwidth);
        elseif(preg_match('/^\d+x$/', $dimstr) === 1)
            $dims[1] = floor(($dims[0] / $origwidth) * $origheight);

        return $dims;
    }

    public static function parse_point($ptstr, $defx, $defy)
    {
        if(is_null($ptstr))
            return array($defx, $defy);

        return explode(',',$ptstr);
    }

}
