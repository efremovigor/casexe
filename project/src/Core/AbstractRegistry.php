<?php

namespace Core;

use Core\Container\ContainerItemInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractRegistry implements ContainerInterface
{

    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * @return array
     */
    abstract protected function getList(): array;

    /**
     * @param mixed $id
     * @return mixed
     */
    abstract protected function createInstance($id);

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id): ?ContainerItemInterface
    {
        if (array_key_exists($id, static::$instances)) {
            return static::$instances[$id];
        }

        if (!$this->has($id)) {
            return null;
        }

        static::$instances[$id] = $this->createInstance($id);

        return static::$instances[$id];
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->getList());
    }
}