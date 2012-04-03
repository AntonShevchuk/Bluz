<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 3/29/12
 * Time: 3:00 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * @namespace
 */
namespace Bluz\View\Helper;


abstract class HelperAbstract
{
    /**
     * @var \Bluz\View\View
     */
    protected $_view = null;

    public function setView(\Bluz\View\View $view)
    {
        $this->_view = $view;
        return $this;
    }

    public function getView()
    {
        if (null === $this->_view) {
            $this->_view = new \Bluz\View\View();
        }
        return $this->_view;
    }

    public function toString()
    {
        return '';
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function __invoke()
    {
        return $this->toString();
    }
}