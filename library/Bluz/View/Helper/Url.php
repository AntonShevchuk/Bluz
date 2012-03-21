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
function (\Bluz\View\View $view, array $params, $name)
{
    $routerContainer = $view->getApplication()->getRouter();
    $router = $routerContainer->getRoute($name);

    return $router->url($params);
};