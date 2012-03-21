<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 3/21/12
 * Time: 3:00 AM
 * To change this template use File | Settings | File Templates.
 */

/**
 * @namespace
 */
namespace Bluz\Router;

interface RouterInterface
{
    /**
     * Match url as route
     *
     * @abstract
     * @param string $path
     * @return false|array
     */
    public function match($path);

    /**
     * Get route name
     *
     * @abstract
     * @return string
     */
    public function getName();

    /**
     * Set route name
     *
     * @abstract
     * @param string $name
     * @return RouteInterface
     */
    public function setName($name);

    /**
     * Set default variable
     *
     * @param array $defaults
     */
    public function setDefaults(array $defaults);

    /**
     * Get default variable
     *
     * @return array
     */
    public function getDefaults();

    /**
     * Using class as function
     *
     * @param $path
     * @return array|false
     */
    public function __invoke($path);
}