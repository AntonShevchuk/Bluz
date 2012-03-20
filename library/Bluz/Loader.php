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
namespace Bluz;

/**
 * Loader
 *
 * @category Bluz
 * @package  Loader
 *
 * <code>
 *
 *
 * </code>
 *
 * @author   Anton Shevchuk
 * @created  06.07.11 16:07
 */
class Loader
{
    /**
     * Array of namespaces
     * for 5.3 libraries
     *
     * @var array
     */
    private $_namespaces = array();
    /**
     * Array of prefixes
     * for old libraries
     *
     * @var array
     */
    private $_prefixes = array();

    protected static $_implement = null;

    /**
     * @static
     * @return Loader
     */
    public static function getLoader()
    {
        if (null === self::$_implement) {
            self::$_implement = new self();
        }
        return self::$_implement;
    }

    /**
     * <code>
     *
     * </code>
     *
     * @param string $namespace
     * @param mixed $paths
     * @return Loader
     */
    public function registerNamespace($namespace, $paths)
    {
        $this->_namespaces[$namespace] = (array) $paths;
        return $this;
    }

    /**
     * <code>
     *
     * </code>
     *
     * @param string $prefix
     * @param mixed $paths
     * @return Loader
     */
    public function registerPrefix($prefix, $paths)
    {
        $this->_prefixes[$prefix] = (array) $paths;
        return $this;
    }

    /**
     * Register our autoload method
     *
     * @return bool
     */
    public function register()
    {
        return spl_autoload_register(array($this, 'load'), true, false);
    }

    /**
     * Autoloader
     *
     * @param  string $class
     * @return void
     */
    public function load($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return true;
        }

        if ($file = $this->_find($class)) {
            require_once $file;
        }

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            throw new \Exception("File '$file' does not exist or class '$class' was not found in the file");
        }
    }

    /**
     * Try to find class file
     *
     * @param  string $class
     * @return bool|string
     */
    protected function _find($class)
    {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // it's namespace
            $namespace = substr($class, 0, $pos);
            foreach ($this->_namespaces as $ns => $dirs) {
                foreach ($dirs as $dir) {
                    if (0 === strpos($namespace, $ns)) {
                        $className = substr($class, $pos + 1);
                        $file = $dir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        } else {
            // it's plain class
            foreach ($this->_prefixes as $prefix => $dirs) {
                foreach ($dirs as $dir) {
                    if (0 === strpos($class, $prefix)) {
                        $file = $dir.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        }
        return false;
    }
}
