<?php

namespace SmartHome\Cache\Adapter;

use Predis\Client;
use SmartHome\Cache\Exception;
use Throwable;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Redis implements IAdapter {

    /**
     *
     * @var Client
     */
    private $_client;

    public function __construct (Client $client) {
        $this->_client = $client;
    }

    public function get ($key, $default = null) {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->_client->get($key);
    }

    public function set ($key, $value, $ttl = null): bool {
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

    public function delete ($key): bool {
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

    public function clear (): bool {
        throw Exception('Don\'t clear all.');
    }

    public function getMultiple ($keys, $default = null): iterable {
        $values = $this->_client->mget($keys);
        $result = [];
        foreach ($values as $key => $value) {
            $result[$keys[$key]] = $value ?? $default;
        }

        return $result;
    }

    public function setMultiple ($values, $ttl = null): bool {
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

    public function deleteMultiple ($keys): bool {
        try {
            $this->_client->del(...$keys);
            return true;
        } catch (Throwable $ex) {
            return false;
        }
    }

    public function has ($key): bool {
        return $this->_client->exists($key);
    }

}
