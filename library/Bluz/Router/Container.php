<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 3/21/12
 * Time: 3:02 AM
 * To change this template use File | Settings | File Templates.
 */

/**
 * @namespace
 */
namespace Bluz\Router;

use Bluz\Package;

class Container extends Package
{
    protected $_routes = array();
    protected $_routesKey = array();

    /**
     * Add route
     *
     * @param RouterInterface $route
     * @return Container
     */
    public function addRoute(RouterInterface $route)
    {
        $this->_routes[] = $route;
        $this->_routesKey[$route->getName()] = sizeof($this->_routes) - 1;

        return $this;
    }

    /**
     * Get route
     *
     * @param string $name
     * @return null|Router
     */
    public function getRoute($name)
    {
        return (!isset($this->_routesKey[$name])) ? null : $this->_routes[$this->_routesKey[$name]];
    }

    /**
     * Проверяет все роутеры на соответствие
     * Возвращает <b>false</b> при неудачи или
     * <b>array('routeName' => ...</b>
     *
     * @param  $path
     * @return array|false
     */
    public function match($path)
    {
        for ($x = sizeof($this->_routes); $x--; $x >= 0) {
            $route = $this->_routes[$x];
            if (null !== $route && false !== ($params = $route->match($path))) {
                $params['routeName'] = $route->getName();

                return $params;
            }
        }

        return false;
    }

    /**
     * @return \Bluz\Request\AbstractRequest
     */
    public function process()
    {
        $request = $this->getApplication()->getRequest();
        $params = $this->match($request->getRequestUri());

        if (is_array($params) && sizeof($params)) {
            foreach ($params as $param => $value) {
                switch ($param) {
                    case 'module':
                    case 'controller':

                        $request->$param($value);
                        break;

                    default:
                        $request->{$param} = $value;
                        break;
                }
            }
        }

        return $request;
    }

    /**
     * Remove route
     *
     * @param string $name
     * @return Container
     */
    public function removeRoute($name)
    {
        if (isset($this->_routesKey[$name])) {
            $this->_routes[$this->_routesKey[$name]] = null;
            unset($this->_routesKey[$name]);
        }

        return $this;
    }

    /**
     * Add routes
     *
     * @throws \Exception
     * @param array $routes
     * @return Container
     */
    public function addRoutes(array $routes)
    {
        if (!is_array($routes)) throw new \Exception('$routes can be array, givin "' . gettype($routes) . '"');

        foreach ($routes as $route => $routeData) {
            $this->addRoute(new Router($route, $routeData));
        }

        return $this;
    }

    public function url($module = 'index', $controller = 'index', $params = array())
    {
        $name = $module . '-' . $controller;
        $router = $this->getRoute($name);

        //@todo Вставить проверку на acl

        if ($router instanceof \Bluz\Router\RouterInterface) {
            return $router->url($params);
        }

        throw new \Exception("Router '{$name}' not found");
    }
}