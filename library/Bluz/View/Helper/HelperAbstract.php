<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 3/29/12
 * Time: 3:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Bluz\View\Helper;

abstract class HelperAbstract
{
    public function __invoke()
    {
        return $this->__toString();
    }
}