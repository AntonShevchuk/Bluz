<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 3/21/12
 * Time: 8:06 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Bluz\View\Helper;
return

/**
 * @return \Closure
 */
function (\Bluz\View\View $view, array $params, $name = null)
{
    $routerContainer = $view->getApplication()->getRouter();

    if (null == $name) {
        $name = $params['module'] . ':' . $params['controller'];
    }

    $router = $routerContainer->getRoute($name);

    if ($router instanceof \Bluz\Router\RouterInterface) {
        return $router->url($params);
    } else {
        throw new \Exception("Router '{$name}' not found");
    }
};