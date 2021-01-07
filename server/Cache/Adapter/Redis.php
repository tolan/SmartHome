<?php

namespace SmartHome\Cache\Adapter;

use Predis\Client;
use SmartHome\Cache\Exception;
use Throwable;

/**
 * This file defines class for redis adapter
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Redis implements IAdapter {

    /**
     * Redis client
     *
     * @var Client
     */
    private $_client;

    /**
     * Construct method for inject dependencies
     *
     * @param Client $client Redis client
     */
    public function __construct(Client $client) {
        $this->_client = $client;
    }

    /**
     * Gets value by key
     *
     * @param string $key     Key
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function get($key, $default = null) {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->_client->get($key);
    }

    /**
     * Sets value by key
     *
     * @param string $key   Key
     * @param mixed  $value Value
     * @param int    $ttl   TTL
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool {
        try {
            $this->_client->set($key, $value);
            if ($ttl) {
                $this->_client->expire($key, $ttl);
            }

            return true;
        } catch (Throwable $ex) {
            return false;
        }
    }

    /**
     * Deletes value by key
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function delete($key): bool {
        try {
            if (substr($key, -1) === '*') {
                $keys = $this->_client->keys($key);
                $this->deleteMultiple($keys);
            } else {
                $this->_client->del($key);
            }

            return true;
        } catch (Throwable $ex) {
            return false;
        }
    }

    /**
     * Don't clear all!
     *
     * @return bool
     *
     * @throws Exception
     */
    public function clear(): bool {
        throw Exception('Don\'t clear all.');
    }

    /**
     * Returns multiples keys
     *
     * @param array $keys    Set of keys
     * @param mixed $default Default value
     *
     * @return iterable
     */
    public function getMultiple($keys, $default = null): iterable {
        $values = $this->_client->mget($keys);
        $result = [];
        foreach ($values as $key => $value) {
            $result[$keys[$key]] = ($value ?? $default);
        }

        return $result;
    }

    /**
     * Sets multiple values
     *
     * @param array $values Array with [key => value]
     * @param int   $ttl    TTL
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        try {
            $this->_client->mset($values, $ttl);
            if ($ttl) {
                foreach ($values as $key => $value) {
                    $this->_client->expire($key, $ttl);
                }
            }

            return true;
        } catch (Throwable $ex) {
            return false;
        }
    }

    /**
     * Deletes multiple keys
     *
     * @param array $keys Keys
     *
     * @return bool
     */
    public function deleteMultiple($keys): bool {
        try {
            $this->_client->del(...$keys);
            return true;
        } catch (Throwable $ex) {
            return false;
        }
    }

    /**
     * Returns that the key exists
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function has($key): bool {
        return $this->_client->exists($key);
    }

}
