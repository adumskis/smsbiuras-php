<?php

namespace Adumskis\SmsBiurasPhp;

/**
 * Class Config
 * @package Adumskis\SmsBiurasPhp
 */
class Config
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Config constructor.
     * @param $config
     */
    public function __construct($config)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Parameter config should be array');
        }
        $this->data = $config;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Parameter key should be string');
        }
        $this->data[$key] = $value;

        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Parameter key should be string');
        }
        return $this->exists($key) ? $this->data[$key] : $default;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Parameter key should be string');
        }
        return (bool) array_key_exists($key, $this->data);
    }
}