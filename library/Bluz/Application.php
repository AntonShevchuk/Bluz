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

use Bluz\Acl\Acl as Acl;
use Bluz\Auth\Auth as Auth;
use Bluz\Cache\Cache as Cache;
use Bluz\Config\Config as Config;
use Bluz\Db\Db as Db;
use Bluz\Registry\Registry as Registry;
use Bluz\Request as Request;
use Bluz\Router\Container as RouterContainer;
use Bluz\Session\Session as Session;
use Bluz\View\View as View;

/**
 * Application
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
 * @created  06.07.11 16:25
 */
class Application
{
    /**
     * @var string
     */
    protected $_environment;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var Loader
     */
    protected $_loader;

    /**
     * @var Request\AbstractRequest
     */
    protected $_request;

    /**
     * @var Router
     */
    protected $_router;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Cache
     */
    protected $_cache;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var Auth
     */
    protected $_auth;

    /**
     * @var Acl
     */
    protected $_acl;

    /**
     * @var Db
     */
    protected $_db;

    /**
     * @var View
     */
    protected $_view;

    /**
     * Use layout flag
     * @var boolean
     */
    protected $_layout = true;

    /**
     * JSON response flag
     * @var boolean
     */
    protected $_json = false;

    /**
     * Messages
     * @var array
     */
    protected $_messages = array(
        'notice' => array(),
        'warning' => array(),
        'error' => array(),
    );

    /**
     * Widgets closures
     * @var array
     */
    protected $_widgets = array();

    /**
     * Constructor
     *
     * @access  public
     */
    public function __construct()
    {
    }

    /**
     * init
     *
     * @param string $environment Array format only!
     * @return Application
     */
    public function init($environment = '')
    {
        $this->_environment = $environment;

        try {
            $this->getConfig($environment);
            $this->getLoader();

            $this->log(__METHOD__);

            $this->getCache();
            $this->getRegistry();
            $this->getSession();
            $this->getAuth();
            //$this->getAcl();
            $this->getDb();
        } catch (Exception $e) {
            throw new Exception("Application can't be loaded: ". $e->getMessage());
        }
        return $this;
    }

    /**
     * log message, working with profiler
     *
     * @param  string $message
     * @return void
     */
    public function log($message)
    {
        if ($this->getConfigData('profiler')) {
            \Bluz\Profiler::log($message);
        }
    }

    /**
     * load config file
     *
     * @param string|null $environment
     * @return Config
     */
    public function getConfig($environment = null)
    {
        if (!$this->_config) {
            $this->_config = new Config();
            $this->_config->load($environment);
        }
        return $this->_config;
    }

    /**
     * config
     *
     * @param string|null $section of config
     * @param string|null $subsection of config
     * @return array
     */
    public function getConfigData($section = null, $subsection = null)
    {
        return $this->getConfig()->get($section, $subsection);
    }

    /**
     * getLoader
     *
     * @return Loader
     */
    public function getLoader()
    {
        if (!$this->_loader) {
            $this->_loader = Loader::getLoader();

            $conf = $this->getConfigData('loader');
            if (isset($conf['namespaces'])) {
                foreach ($conf['namespaces'] as $ns => $path) {
                    $this->_loader -> registerNamespace($ns, $path);
                }
            }
            if (isset($conf['prefixes'])) {
                foreach ($conf['prefixes'] as $prefix => $path) {
                    $this->_loader -> registerPrefix($prefix, $path);
                }
            }
        }
        return $this->_loader;
    }

    /**
     * getAcl
     *
     * @todo Cache ACL object to memcache
     * @todo Move initialize process inside Acl
     * @return Acl
     */
    public function getAcl()
    {
        if (!$this->_acl && $conf = $this->getConfigData('acl')) {
            if ($acl = $this->getCache()->get('Acl')) {
                $this->_acl = $acl;
            } else {
                $this->_acl = new Acl($conf);

                $roles = $this->getDb()->fetchGroup('SELECT * FROM acl_roles');
                $this->_acl->setRoles($roles);

                $actions = $this->getDb()->fetchGroup('SELECT sid, `action`, access FROM acl_actions');
                $this->_acl->setActions($actions);

                $links = $this->getDb()->fetchColumnGroup('SELECT sid, psid FROM acl_links');
                $this->_acl->setLinks($links);

                $this->_acl->process();

//                $rules = new \Application\Rules\Table();
                $this->getCache()->set('Acl', $this->_acl);
            }

            $this->_acl->setApplication($this);
        }
        return $this->_acl;
    }

    /**
     * getAuth
     *
     * @return Auth
     */
    public function getAuth()
    {
        if (!$this->_auth && $conf = $this->getConfigData('auth')) {
            $this->_auth = new Auth($conf);
            $this->_auth->setApplication($this);
        }
        return $this->_auth;
    }

    /**
     * getCache
     *
     * @return Cache
     */
    public function getCache()
    {
        if (!$this->_cache) {
            $this->_cache = new Cache($this->getConfigData('cache'));
            $this->_cache->setApplication($this);
        }
        return $this->_cache;
    }


    /**
     * getDb
     *
     * @return Db
     */
    public function getDb()
    {
        if (!$this->_db && $conf = $this->getConfigData('db')) {
            $this->_db = new Db($conf);
            $this->_db->setApplication($this);
        }
        return $this->_db;
    }

    /**
     * getRegistry
     *
     * @return Registry
     */
    public function getRegistry()
    {
        if (!$this->_registry && $conf = $this->getConfigData('registry')) {
            $this->_registry = new Registry($conf);
            $this->_registry->setApplication($this);
        }
        return $this->_registry;
    }

    /**
     * getRequest
     *
     * @return Request\AbstractRequest
     */
    public function getRequest()
    {
        if (!$this->_request) {
            if ('cli' == PHP_SAPI) {
                $this->_request = new Request\CliRequest($this->getConfigData('request'));
            } else {
                $this->_request = new Request\HttpRequest($this->getConfigData('request'));
            }

            $this->_request->setApplication($this);

            if ($this->_request->isXmlHttpRequest()) {
                $this->useLayout(false);
            }
        }
        return $this->_request;
    }

    /**
     * getRouter
     *
     * @return \Bluz\Router\Container
     */
    public function getRouter()
    {
        if (!$this->_router) {
            //$this->_router = new Router($this->getConfigData('router'));
            $this->_router = new RouterContainer();
            $this->_router->addRoutes($this->getConfigData('routers'));
            $this->_router->setApplication($this);
        }
        return $this->_router;
    }

    /**
     * getSession
     *
     * @return Session
     */
    public function getSession()
    {
        if (!$this->_session) {
            $this->_session = new Session($this->getConfigData('session'));
            $this->_session->setApplication($this);
            $this->_session->start();

            if ($this->_session->_messages) {
                $this->_messages = $this->_session->_messages;
                $this->_session->_messages = null;
            }
        }
        return $this->_session;
    }

    /**
     * getView
     *
     * @return View
     */
    public function getView()
    {
        if (!$this->_view && $conf = $this->getConfigData('view')) {
            $this->_view = new View($conf);
            $this->_view->setApplication($this);
        }
        return $this->_view;
    }

    /**
     * add notice
     *
     * @param string $text
     * @return Application
     */
    public function addNotice($text)
    {
        $this->_messages['info'][] = $text;
        return $this;
    }

    /**
     * add success
     *
     * @param string $text
     * @return Application
     */
    public function addSuccess($text)
    {
        $this->_messages['success'][] = $text;
        return $this;
    }

    /**
     * add error
     *
     * @param string $text
     * @return Application
     */
    public function addError($text)
    {
        $this->_messages['error'][] = $text;
        return $this;
    }

    /**
     * process
     *
     * @return Application
     */
    public function process()
    {
        $this->log(__METHOD__);

        $this->getRequest();

        $this->getRouter()
             ->process();

        if ($this->_request->getParam('json')) {
            $this->useJson(true);
        }
        $view = $this->getView();

        /* @var View $ControllerView */
        try {
            $controllerView = $this->dispatch($this->_request->module(), $this->_request->controller());
            if (!$this->_layout) {
                // move vars from layout to view instance
                // TODO: or remove this is code?
                if ($controllerView instanceof View) {
                    $controllerView -> setData($this->_view->toArray());
                    $controllerView -> setParent(null);
                }

                $this->_view = $view = $controllerView;
            } else {
                if ($controllerView instanceof View) {
                    $controllerView = $controllerView->render();
                }
                $view->content = $controllerView;
            }
        } catch (Exception $e) {
            $controllerView = $this->dispatch('error', 'error', array(
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ));
            $view->content = $controllerView;
        }

        if (!$view instanceof \Closure) {
            $view->_messages = $this->_messages;
        }
        return $this;
    }

    /**
     * useLayout
     *
     * @param boolean|string $flag
     * @return Application
     */
    public function useLayout($flag = true)
    {
        if (is_string($flag)) {
            $this->getView()->setTemplate($flag);
            $this->_layout = true;
        } else {
            $this->_layout = $flag;
        }
        return $this;
    }

    /**
     * useJson
     *
     * @param boolean $flag
     * @return Application
     */
    public function useJson($flag = true)
    {
        if ($flag) {
            $this->useLayout(false);
        }
        $this->_json = $flag;
        return $this;
    }

    /**
     * render
     *
     * @return Application
     */
    public function render()
    {
        $this->log(__METHOD__);

        $view = $this->getView();

        if ('cli' == PHP_SAPI) {
            $data = $view->toArray();
            foreach ($data as $key => $value) {
                if (strpos($key, '_') === 0) {
                    echo "\033[1;31m$key\033[m:\n";
                } else {
                    echo "\033[1;33m$key\033[m:\n";
                }
                var_dump($value);
                echo "\n";
            }
        } else {
            if ($this->_json) {
                header('Content-type: application/json');
                echo json_encode($view->toArray());
            } else {
                echo ($view instanceof \Closure) ? $view(): $view;
            }
        }
        return $this;
    }

    /**
     * reflection for anonymous function
     *
     * @param string  $uid
     * @param closure $closure
     * @return array
     */
    public function reflection($uid, $closure)
    {
        // cache for reflection data
        if (!$data = $this->getCache()->get('Reflection: '.$uid)) {
            $reflection = new \ReflectionFunction($closure);

            // check and normalize params by doc comment
            $docComment = $reflection->getDocComment();
            preg_match_all('/\s*\*\s*\@param\s+(bool|boolean|int|integer|float|string|array)\s+\$([a-z0-9_]+)/i', $docComment, $matches);

            // init data
            $data = array();

            // rebuild array
            $data['types'] = array();
            foreach ($matches[1] as $i => $type) {
                $data['types'][$matches[2][$i]] = $type;
            }

            $data['params'] = $reflection->getParameters();

            if (preg_match('/\s*\*\s*\@cache\s+([0-9\.]+).*/i', $docComment, $matches)) {
                $data['cache'] = $matches[1];
            };
            if (preg_match('/\s*\*\s*\@privilege\s+(.*)/i', $docComment, $matches)) {

                $privilege = explode('$', $matches[1]);

                $data['privilege'] = array_shift($privilege);
                $data['resources'] = $privilege;
            };
            if (preg_match_all('/\s*\*\s*\@atom\s+([0-9a-zA-Z.-_ ]+)/i', $docComment, $matches)) {
                $data['atom'] = $matches[1];
            };
            $this->getCache()->set('Reflection: '.$uid, $data);
        }
        return $data;
    }

    /**
     * process params
     *
     * @param $data
     * @return array
     */
    private function params($data)
    {
        $request = $this->getRequest();
        $params = array();
        foreach ($data['params'] as $param) {
            /* @var \ReflectionParameter $param */
            if (isset($data['types'][$param->name]) && $type = $data['types'][$param->name]) {
                switch ($type) {
                    case 'bool':
                    case 'boolean':
                        $params[] = (bool) $request->{$param->name};
                        break;
                    case 'int':
                    case 'integer':
                        $params[] = (int) $request->{$param->name};
                        break;
                    case 'float':
                        $params[] = (float) $request->{$param->name};
                        break;
                    case 'string':
                        $params[] = (string) $request->{$param->name};
                        break;
                    case 'array':
                        $params[] = (array) $request->{$param->name};
                        break;
                }
            } else {
                $params[] = $request->{$param->name};
            }
        }
        return $params;
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
        $this->log(__METHOD__.": ".$module.'/'.$controller);

        $app = $this;

        $request = $this->getRequest();
        $request -> setParams($params);

        $view = new View($this->getConfigData('subview'));
        $view -> setPath(PATH_APPLICATION .'/modules/'. $module .'/views');
        $view -> setTemplate($controller .'.phtml');
        $view -> setParent($this->getView());
        $view -> setApplication($this);

        $bootstrapPath = PATH_APPLICATION .'/modules/' . $module .'/bootstrap.php';

        /**
         * @var closure $bootstrap
         */
        if (file_exists($bootstrapPath)) {
            $bootstrap = require $bootstrapPath;
        } else {
            $bootstrap = function () use ($app) {
                return $app;
            };
        }

        /**
         * @var closure $controllerClosure
         */
        $controllerClosure = require $this->getControllerFile($module, $controller);

        if (!is_callable($controllerClosure)) {
            throw new Exception("Controller is not callable '$module/$controller'");
        }

        $data = $this->reflection($module.'/controllers/'.$controller, $controllerClosure);

        // check acl
        if (!$this->isAllowedController($module, $controller, $params)) {
            throw new Exception('You don\'t have permissions', 403);
        }


        $params = $this->params($data);

        // load html from cache file
        if (isset($data['cache'])) {
            if ($view->cache($data['cache'], $params)) {
                return $view;
            }
        };

        $result = call_user_func_array($controllerClosure, $params);

        // return false is equal to disable layout
        if ($result === false) {
            $result = function(){};
        }

        if ($result) {
            if (!is_callable($result)) {
                throw new Exception("Controller result is not callable '$module/$controller'");
            }
            return $result;
        } else {
            return $view;
        }
    }

    /**
     * widget
     *
     * @param string $module
     * @param string $widget
     * @param array $params
     * @return \Closure
     */
    public function widget($module, $widget, $params = array())
    {
        $this->log(__METHOD__.": ".$module.'/'.$widget);

        $app = $this;

        /**
         * Cachable widgets
         * @var \Closure $widgetClosure
         */
        if (isset($this->_widgets[$module])
            && isset($this->_widgets[$module][$widget])) {
            $widgetClosure = $this->_widgets[$module][$widget];
        } else {
            $widgetClosure = require $this->getWidgetFile($module, $widget);

            if (!isset($this->_widgets[$module])) {
                $this->_widgets[$module] = array();
            }
            $this->_widgets[$module][$widget] = $widgetClosure;
        }

        if (!is_callable($widgetClosure)) {
            throw new Exception("Widget is not callable '$module/$widget'");
        }

        // TODO: check acl and other docs information
//        $data = $this->reflection($module."/widgets/".$widget, $widgetClosure);
        return $widgetClosure;
    }

    /**
     * Is allowed controller
     *
     * @param string $module
     * @param string $controller
     * @param array  $params
     * @return boolean
     */
    public function isAllowedController($module, $controller, array $params = array())
    {
        return true;

//        $controllerClosure = $this->getControllerFile($module, $controller);
//
//        $data = $this->reflection($module, $controller, $controllerClosure);
//
//        return $this->getAcl()->isAllowed($data['privilege'], $this->getAuth()->getIdentity(), $data['resources']);
    }

    /**
     * Get controller file
     *
     * @param  string $module
     * @param  string $controller
     * @return \Closure
     * @throws Exception
     */
    public function getControllerFile($module, $controller)
    {
        $controllerPath = PATH_APPLICATION . '/modules/' . $module
                        .'/controllers/' . $controller .'.php';

        if (!file_exists($controllerPath)) {
            throw new Exception("Controller not found '$module/$controller'");
        }

        return $controllerPath;
    }

    /**
     * Get widget file
     *
     * @param  string $module
     * @param  string $widget
     * @return \Closure
     * @throws Exception
     */
    public function getWidgetFile($module, $widget)
    {
        $widgetPath = PATH_APPLICATION . '/modules/' . $module
                        .'/widgets/' . $widget .'.php';

        if (!file_exists($widgetPath)) {
            throw new Exception("Widget not found '$module/$widget'");
        }

        return $widgetPath;
    }

    /**
     * redirect
     *
     * @param string $url
     * @return void
     */
    public function redirect($url)
    {
        if (!headers_sent($file, $line)) {
            // save notification to session
            $this->getSession()->_messages = $this->_messages;

            header('Location: '.$url);
            exit();
        } else {
            throw new Exception("Headers already sent by $file:$line");
        }
    }

    /**
     * redirect
     *
     * @param string $module
     * @param string $controller
     * @param array $params
     * @return void
     */
    public function redirectTo($module = 'index', $controller = 'index', $params = array())
    {
        $url = $this->getRouter()->url($module, $controller, $params);
        $this->redirect($url);
    }
}
