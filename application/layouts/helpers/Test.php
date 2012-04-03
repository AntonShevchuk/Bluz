<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 4/3/12
 * Time: 12:42 PM
 * To change this template use File | Settings | File Templates.
 */

class Test extends \Bluz\View\Helper\HelperAbstract
{
    public function toString($message)
    {
        return 'TEST HELPER {' . $message . '}';
    }
}