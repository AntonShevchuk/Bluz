<?php

/**
 * Copyright (c) 2011 by Bluz PHP Team
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @namespace
 */
namespace Bluz\View;

use Bluz\Package;
use Bluz\Options;

/**
 * View
 *
 * @category Bluz
 * @package  View
 *
 * @author   Anton Shevchuk
 * @created  08.07.11 11:49
 *
 * @property mixed _messages
 * @property mixed content
 */
class View extends Package
{
    /**
     * @var View
     */
    protected $_parent;

    /**
     * @var Application
     */
    protected $_application;

    /**
     * @var string
     */
    protected $_baseUrl;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var array
     */
    protected static $_viewHelpers = array();

    /**
     * @var array
     */
    protected static $_viewHelpersPath = array();

    /**
     * @var array
     */
    protected $_head = array();

    /**
     * @var string path to template
     */
    protected $_path;

    /**
     * Global cache flag
     *
     * @var boolean
     */
    protected $_cache = false;

    /**
     * @var string
     */
    protected $_cachePath;

    /**
     * @var string
     */
    protected $_cacheFile;

    /**
     * @var boolean
     */
    protected $_cacheFlag = false;

    /**
     * @var string
     */
    protected $_template;

    /**
     * Get a variable
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        } elseif ($this->_parent) {
            return $this->_parent->{$key};
        } else {
            return null;
        }
    }

    /**
     * Call
     *
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $lmethod = strtolower($method);
        if (isset(self::$_viewHelpers[$lmethod])
            && self::$_viewHelpers[$lmethod] instanceof \Closure) {
            array_unshift($args, $this->getApplication()->getView());
            return call_user_func_array(self::$_viewHelpers[$lmethod], $args);
        }
        if (self::$_viewHelpersPath) {
            foreach(self::$_viewHelpersPath as $helperPath) {
                $helperPath = realpath($helperPath . '/' . ucfirst($method) . '.php');
                if ($helperPath) {
                    self::$_viewHelpers[strtolower($method)] = include $helperPath;

                    return $this->__call($method, $args);
                }
            }
        }

        throw new \Exception("View helper '$method' not found");
    }

    /**
     * Is set a variable
     *
     * @param string $key
     * @return mixed
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Assign a variable
     *
     * A $value of null will unset the $key if it exists
     *
     * @param string $key
     * @param mixed $value
     * @return View
     */
    public function __set($key, $value)
    {
        $key = (string) $key;

        if ((null === $value) && isset($this->_data[$key])) {
            unset($this->_data[$key]);
        } elseif (null !== $value) {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * set data from array
     *
     * @param array $data
     * @return View
     */
    public function setData($data = array())
    {
        $this->_data = $this->_mergeArrays($this->_data, $data);
        return $this;
    }

    /**
     * @param $array1
     * @param $array2
     * @return array
     */
    protected function _mergeArrays($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (array_key_exists($key, $array1) && is_array($value)) {
                $array1[$key] = $this->_mergeArrays($array1[$key], $array2[$key]);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    /**
     * is callable
     *
     * @return void
     */
    public function __invoke()
    {
        $this->render();
    }

    /**
     * render like string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * setCache
     *
     * @param boolean $flag
     * @return View
     */
    public function setCache($flag)
    {
        $this->_cache = (boolean) $flag;
        return $this;
    }

    /**
     * setCachePath
     *
     * @param string $path
     * @return View
     */
    public function setCachePath($path)
    {
        if (!is_dir($path) or !is_writable($path)) {
            throw new ViewException('View: Cache path is not writable');
        }
        $this->_cachePath = $path;
        return $this;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->_data as $key => $value) {
            if ($value instanceof self) {
                $value = $value->toArray();
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * setup path
     *
     * @param string $path
     * @return View
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    /**
     * Set view helpers
     *
     * @param array $viewHelpers
     * @return View
     */
    public function setViewHelpers($viewHelpers)
    {
        foreach ($viewHelpers as $name => $function) {
            self::$_viewHelpers[strtolower($name)] = $function;
        }
        return $this;
    }

    /**
     * Set view helpers path
     *
     * @param string|array $viewHelpersPath
     * @return View
     */
    public function setViewHelpersPath($viewHelpersPath)
    {
        $this->setViewHelperPathDefault();

        if (is_array($viewHelpersPath)) {
            foreach ($viewHelpersPath as $path) {
                $this->addViewHelperPath((string) $path);
            }
        } else {
            $this->addViewHelperPath((string) $viewHelpersPath);
        }

        return $this;
    }

    /**
     * Add view helper path
     *
     * @param string $path
     * @return View
     */
    public function addViewHelperPath($path)
    {
        $path = rtrim(realpath($path), '/');
        if (false !== $path && !in_array($path, self::$_viewHelpersPath)) {
            self::$_viewHelpersPath [] = $path;
        }

        return $this;
    }

    /**
     * Set default view helper path
     *
     * @return View
     */
    public function setViewHelperPathDefault()
    {
        self::$_viewHelpersPath = array(rtrim(realpath(__DIR__ . '/Helper/'), '/'));
        return $this;
    }

    /**
     * setup template
     *
     * @param string $file
     * @return View
     */
    public function setTemplate($file)
    {
        $this->_template = $file;
        return $this;
    }

    /**
     * setParent
     *
     * @param View $View
     * @return View
     */
    public function setParent($View = null)
    {
        $this->_parent = $View;
        return $this;
    }

    /**
     * return a parent view (if it exists)
     *
     * @throw ViewException
     * @return View
     */
    public function getParent()
    {
        if ($this->_parent) {
            return $this->_parent;
        } else {
            throw new ViewException('Parent of current View is not defined');
        }
    }

    /**
     * Get identity
     *
     * @return \Bluz\Auth\AbstractEntity
     */
    public function getIdentity()
    {
        return $this->getApplication()->getAuth()->getIdentity();
    }

    /**
     * A href url
     *
     * @param string $name
     * @param string $module
     * @param string $controller
     * @param array $params
     * @param array $attributes
     * @return string
     */
    /*public function ahref($name, $module = 'index', $controller = 'index', $params = array(), $attributes = array())
    {
        if (!$this->getApplication()->isAllowedController($module, $controller)) {
            return '';
        }

        $href = $this->getApplication()->getRouter()->url($module, $controller, $params);

        if ($href == $this->getApplication()->getRequest()->getRequestUri()) {
            if (isset($attributes['class'])) {
                $attributes['class'] .= ' on';
            } else {
                $attributes['class'] = 'on';
            }
        }
        $attrs = array();

        foreach ($attributes as $attr => $value) {
            $attrs[] = $attr .'="'.$value.'"';
        }

        return '<a href="'.$href.'" '.join(' ', $attrs).'>'.$name.'</a>';
    }*/


    /**
     * build url
     *
     * @param string $module
     * @param string $controller
     * @param array $params
     * @return string
     */
    /*public function url($module = null, $controller = null, $params = null)
    {
        return $this->getApplication()->getRouter()->url($module, $controller, $params);
    }*/

    /**
     * Returns navigation container instance.
     *
     * @param string $section Navigation section
     * @return \Bluz\Navigation\Container
     */
    public function nav($section)
    {
        $config = $this->getApplication()->getConfig('nav');

        if (!$config || !isset($config[$section])) {
            return null;
        }

        $container = new \Bluz\Navigation\Container($config[$section]);

        return $container;
    }

    /**
     * baseUrl
     *
     * TODO: realization
     * @param string $file
     * @return string
     */
    public function baseUrl($file)
    {
        // setup baseUrl
        if (!$this->_baseUrl) {
            $this->_baseUrl = $this->getApplication()
                ->getRequest()
                ->getBaseUrl()
            ;
            // clean script name
            if (isset($_SERVER['SCRIPT_NAME'])
                && ($pos = strripos($this->_baseUrl, basename($_SERVER['SCRIPT_NAME']))) !== false) {
                $this->_baseUrl = substr($this->_baseUrl, 0, $pos);
            }
        }

        // Remove trailing slashes
        if (null !== $file) {
            $file = ltrim($file, '/\\');
        }

        return rtrim($this->_baseUrl, '/') .'/'. $file;
    }

    /**
     * dispatch
     *
     * @param string $module
     * @param string $controller
     * @param array $params
     * @return View
     */
    public function dispatch($module, $controller, $params = array())
    {
        $application = $this->getApplication();
        try {
            $view = $application->dispatch($module, $controller, $params);
            $view();
        } catch (\Bluz\Acl\AclException $e) {
            // nothing for Acl exception
        }
    }

    /**
     * widget
     *
     * @param string $module
     * @param string $widget
     * @param array $params
     * @return View
     */
    public function widget($module, $widget, $params = array())
    {
        $application = $this->getApplication();
        try {
            $widgetClosure = $application->widget($module, $widget);
            call_user_func_array($widgetClosure, $params);
        } catch (\Bluz\Acl\AclException $e) {
            // nothing for Acl exception
        }
    }

    /**
     * start cache
     *
     * @param integer $ttl in minutes
     * @param array $keys
     * @return View
     */
    public function cache($ttl, $keys = array())
    {
        if (!$this->_cache) {
            return false;
        }

        $path = rtrim($this->_cachePath, '/') .'/'.
                $this->_template;
        $key = md5(serialize($keys)) .'.html';

        $this->_cacheFile = $path.'/'.$key;

        if (is_file($this->_cacheFile) && (filemtime($this->_cacheFile) > (time() - $ttl*60))) {
            $this->_cacheFlag = true;
            return true;
        } else {
            // create new cache
            // clean old cache
            if (is_file($this->_cacheFile)) {
                @unlink($this->_cacheFile);
            }
            return false;
        }
    }

    /**
     * @return void
     */
    private function _createCacheDir()
    {
        $path = substr($this->_cacheFile, 0, strrpos($this->_cacheFile, '/'));

        // create subpath from file name
        $subdirs = preg_split('/\//', $path);

        if (sizeof($subdirs) > 1) {
            $spath = rtrim($this->_cachePath, '/') . '/';
            foreach ($subdirs as $subdir) {
                $spath = $spath .'/'. $subdir;
                is_dir($spath) || mkdir($spath);
                @chmod($spath, 0777);
            }
        }

        // create path
        is_dir($path) || mkdir($path);
        @chmod($path, 0777);
    }

    /**
     * @param $content
     * @return void
     */
    private function _writeCacheFile($content)
    {
        file_put_contents($this->_cacheFile, $content);
        @chmod($this->_cacheFile, 0777);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {
        // no need render
        if ($this->_template === null) {
            return;
        }

        // cache exists and valid
        if ($this->_cacheFlag) {
            require $this->_cacheFile;
            return;
        }

        ob_start();

        // check need cache or not
        if ($this->_cache && $this->_cacheFile) {
            $this->_createCacheDir();
        }

        /*
        if (!file_exists($this->_path .'/'. $this->_template)) {
            throw new ViewException('Template "'.$this->_template.'" not found');
        }
        */

        if ($this->_parent) {
            extract($this->getParent()->toArray());
        }
        extract($this->_data);

        require $this->_path .'/'. $this->_template;

        $content = ob_get_clean();
        // save cache
        if ($this->_cache && $this->_cacheFile) {
            $this->_writeCacheFile($content);
        }
        return (string) $content;
    }
}
