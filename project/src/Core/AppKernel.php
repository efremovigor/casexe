<?php
/**
 * Created by PhpStorm.
 * User: igore
 * Date: 12.04.18
 * Time: 10:57
 */

namespace Core;

abstract class AppKernel
{
    /**
     * @return string
     */
    public static function getConfDir(): string
    {
        //todo think maybe better use path as parameter - self::getRootDir('/app/config') ??
        return self::getRootDir().'/app/config';
    }

    /**
     * @return string
     */
    public static function getRootDir(): string
    {
        return  __DIR__ . '/../..';
    }

    /**
     * @return string
     */
    public static function getCacheDir(): string
    {
        return self::getRootDir() . '/var/cache';
    }

    /**
     * @return string
     */
    public static function getLogDir(): string
    {
        return self::getRootDir() . '/var/logs';
    }

}