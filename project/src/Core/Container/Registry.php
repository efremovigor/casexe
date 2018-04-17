<?php
/**
 * Created by PhpStorm.
 * User: igore
 * Date: 16.04.18
 * Time: 14:46
 */

namespace Core\Container;


final class Registry
{
    public const ENV = 'environment';
    public const LOGGER = 'logger';
    public const SOCKET = 'socket';
    public const YML_PARSER = 'yml_parser';
    public const CONF_MANAGER = 'conf_manager';
    public const SERIALIZER = 'serializer';
}