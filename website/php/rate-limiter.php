<?php
require_once __DIR__ . '/cache.php';

class RateLimiter {
    private $cache;
    private $maxAttempts = 5;
    private $decayMinutes = 15;

    public function __construct($cache) {
        $this->cache = $cache;
    }

    public function tooManyAttempts($key) {
        $attempts = $this->cache->get($key);
        return $attempts !== null && $attempts >= $this->maxAttempts;
    }

    public function hit($key) {
        $attempts = $this->cache->get($key);
        $attempts = $attempts !== null ? $attempts + 1 : 1;

        $this->cache->set($key, $attempts, $this->decayMinutes * 60);
        return $attempts;
    }

    public function clear($key) {
        return $this->cache->delete($key);
    }

    public function availableIn($key) {
        return $this->decayMinutes * 60;
    }

    public function getRemainingAttempts($key) {
        $attempts = $this->cache->get($key);
        return max(0, $this->maxAttempts - ($attempts !== null ? $attempts : 0));
    }
}
?>
