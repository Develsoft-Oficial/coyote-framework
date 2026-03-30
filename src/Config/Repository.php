<?php

namespace Coyote\Config;

class Repository
{
    protected $items = [];
    
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->items;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return value($default);
            }
            
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            $this->items = array_merge($this->items, $key);
        } else {
            $keys = explode('.', $key);
            $array = &$this->items;
            
            while (count($keys) > 1) {
                $key = array_shift($keys);
                
                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = [];
                }
                
                $array = &$array[$key];
            }
            
            $array[array_shift($keys)] = $value;
        }
        
        return $this;
    }
    
    public function has($key)
    {
        $keys = explode('.', $key);
        $value = $this->items;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            
            $value = $value[$segment];
        }
        
        return true;
    }
    
    public function all()
    {
        return $this->items;
    }
}

// Helper function
if (!function_exists('value')) {
    function value($value, ...$args)
    {
        return $value instanceof \Closure ? $value(...$args) : $value;
    }
}