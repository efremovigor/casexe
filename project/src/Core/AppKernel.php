<?php
/**
 * Created by PhpStorm.
 * User: igore
 * Date: 12.04.18
 * Time: 10:57
 */

namespace Core;

use Core\Container\ContainerRegistry;
use Core\Service\CoreServiceConst;
use Core\Service\RequestHandler;

abstract class AppKernel extends Kernel
{


    /**
     * @var ContainerRegistry
     */
    private $container;

    /**
     * AppKernel constructor.
     */
    public function __construct()
    {
        $this->container = new ContainerRegistry();
    }

    public function run(): void
    {
        $this->getRequestHandler()->process();
    }

    /**
     * @return RequestHandler
     */
    public function getRequestHandler(): RequestHandler
    {
        return $this->container->get(CoreServiceConst::REQUEST_HANDLER);
    }

}