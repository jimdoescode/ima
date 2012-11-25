<?php namespace Photog;

class Config
{
    private static $files = [];

    /**
     * @param $name
     * @param $params
     * @return null|ConfigCollection
     */
    public static function __callStatic($name, $params)
    {
        if(!array_key_exists($name, self::$files))
            self::$files[$name] = require_once(__DIR__."/config/{$name}.php");

        if(!empty($params))
        {
            $values = self::$files[$name];
            $result = [];
            foreach($params as $param)
                $result[] = $values[$param];

            return new ConfigCollection(count($result) > 1 ? $result : $result[0]);
        }
        return array_key_exists($name, self::$files) ? new ConfigCollection(self::$files[$name]) : null;
    }
}

class ConfigCollection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private $items = [];

    public function __construct($items)
    {
        if(is_array($items))
            $this->items = $items;
        else
            $this->items[] = $items;
    }

    public function count()
    {
        return count($this->items);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function alter($func)
    {
        return $func($this->items);
    }

    public function __toString()
    {
        return implode('', $this->items);
    }

    public function raw()
    {
        return $this->items;
    }
}
