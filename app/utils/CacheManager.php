<?php
namespace App\Utils;

class CacheManager {
    private $redis;
    private $prefix;
    private $defaultTTL;

    public function __construct() {
        $config = require APP_PATH . '/config/cache.php';
        $this->prefix = $config['prefix'];
        $this->defaultTTL = $config['ttl'];

        if ($config['default'] === 'redis') {
            $this->redis = new \Redis();
            $redisConfig = $config['redis']['default'];
            $this->redis->connect($redisConfig['host'], $redisConfig['port']);
            if ($redisConfig['password']) {
                $this->redis->auth($redisConfig['password']);
            }
            $this->redis->select($redisConfig['database']);
        }
    }

    public function get($key, $default = null) {
        $fullKey = $this->prefix . ':' . $key;
        $value = $this->redis ? $this->redis->get($fullKey) : null;
        return $value !== false ? unserialize($value) : $default;
    }

    public function set($key, $value, $ttl = null) {
        $fullKey = $this->prefix . ':' . $key;
        $ttl = $ttl ?? $this->defaultTTL;
        $serialized = serialize($value);
        return $this->redis ? $this->redis->setex($fullKey, $ttl, $serialized) : false;
    }

    public function delete($key) {
        $fullKey = $this->prefix . ':' . $key;
        return $this->redis ? $this->redis->del($fullKey) : false;
    }

    public function flush() {
        return $this->redis ? $this->redis->flushDB() : false;
    }

    public function has($key) {
        $fullKey = $this->prefix . ':' . $key;
        return $this->redis ? $this->redis->exists($fullKey) : false;
    }

    public function remember($key, $ttl, callable $callback) {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    public function tags($tags) {
        return $this; // Implementar tag support
    }
}
