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
function (\Bluz\View\View $view, $module, $controller, array $params = array())
{
    $routerContainer = $view->getApplication()->getRouter();
    return $routerContainer->url($module, $controller, $params);
};