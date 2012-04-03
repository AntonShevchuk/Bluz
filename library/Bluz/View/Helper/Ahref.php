<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eaglemoor
 * Date: 3/21/12
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Bluz\View\Helper;
return

function (\Bluz\View\View $view, $text, $href, array $attributes = array())
{
    if (null === $href) return '';

    if ($href == $view->getApplication()->getRequest()->getRequestUri()) {
        if (isset($attributes['class'])) {
            $attributes['class'] .= ' on';
        } else {
            $attributes['class'] = 'on';
        }
    }
    $attrs = array();

    foreach ($attributes as $attr => $value) {
        $attrs[] = $attr . '="' . $value . '"';
    }

    return '<a href="' . $href . '" ' . join(' ', $attrs) . '>' . $text . '</a>';
};