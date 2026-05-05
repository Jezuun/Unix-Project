<?php
// Redis Cache Helper Class
class RedisCache {
    private $redis;
    private $host;
    private $port;
    
    public function __construct($host = 'redis', $port = 6379) {
        $this->host = $host;
        $this->port = $port;
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->redis = new Redis();
            $this->redis->connect($this->host, $this->port);
        } catch (Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->redis = null;
        }
    }
    
    public function set($key, $value, $ttl = 3600) {
        if (!$this->redis) return false;
        
        try {
            return $this->redis->setex($key, $ttl, serialize($value));
        } catch (Exception $e) {
            error_log("Redis set failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function get($key) {
        if (!$this->redis) return null;
        
        try {
            $value = $this->redis->get($key);
            return $value ? unserialize($value) : null;
        } catch (Exception $e) {
            error_log("Redis get failed: " . $e->getMessage());
            return null;
        }
    }
    
    public function delete($key) {
        if (!$this->redis) return false;
        
        try {
            return $this->redis->del($key);
        } catch (Exception $e) {
            error_log("Redis delete failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function exists($key) {
        if (!$this->redis) return false;
        
        try {
            return $this->redis->exists($key);
        } catch (Exception $e) {
            error_log("Redis exists check failed: " . $e->getMessage());
            return false;
        }
    }
}
?>
