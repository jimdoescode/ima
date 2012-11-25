<?php namespace Photog;

class Image
{
    private $meta;
    private $raw;
    private $errors = [];

    public function __construct($path)
    {
        $this->meta = getimagesize($path);
        if($this->meta === false)
            $this->errors[] = 'Could not get image meta data.';
        else
        {
            $this->raw = $this->create_raw_image($this->meta[2], $path);
            if(is_null($this->raw))
                $this->errors[] = 'Could not grab image.';
        }
    }

    private function create_raw_image($type, $path)
    {
        $image = null;
        switch($type)
        {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($path);
                break;
        }

        return $image;
    }

    public function has_errors()
    {
        return !empty($this->errors);
    }

    public function get_error()
    {
        return $this->has_errors() ? $this->errors[0] : null;
    }

    public function operate($operation)
    {
        $image = $operation($this->raw, $this->meta);

        if(is_null($image))
            $this->errors[] = 'Could not perform operation.';

        else
        {
            imagejpeg($image);
            imagedestroy($image);
        }
    }

    public static function parse_dims($dimstr, $origwidth, $origheight)
    {
        $aliases = Config::main('dimension_aliases');
        //NOTE: There is a bug in PHP 5.4 where array_key_exists
        //does not return the correct result for ArrayAccess objects
        if(isset($aliases[$dimstr]))
            $dimstr = $aliases[$dimstr];

        if(is_null($dimstr))
            $dims = [$origwidth, $origheight];
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
            return [$defx, $defy];

        return explode(',',$ptstr);
    }
}