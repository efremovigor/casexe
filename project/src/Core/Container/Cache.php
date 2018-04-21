<?php
/**
 * Created by PhpStorm.
 * User: igore
 * Date: 19.04.18
 * Time: 14:46
 */

namespace Core\Container;


use Core\Container\Cache\FileCachePool;
use Core\Container\Cache\MysqlCachePool;
use Core\Container\Cache\RedisCachePool;
use Psr\Cache\CacheItemPoolInterface;

class Cache
{

    /**
     * @var RedisCachePool
     */
    private $redisPool;

    /**
     * @var FileCachePool
     */
    private $filePool;

    /**
     * @var MysqlCachePool
     */
    private $mysqlPool;

    /**
     * @return CacheItemPoolInterface
     */
    public function getRedis(): CacheItemPoolInterface
    {
        if ($this->redisPool === null) {
            $this->redisPool = new RedisCachePool();
        }
        return $this->redisPool;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getFile(): CacheItemPoolInterface
    {
        if ($this->filePool === null) {
            $this->filePool = new FileCachePool();
        }
        return $this->filePool;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getMysql(): CacheItemPoolInterface
    {
        if ($this->mysqlPool === null) {
            $this->mysqlPool = new MysqlCachePool();
        }
        return $this->mysqlPool;
    }
}