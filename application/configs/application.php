<?php
/**
 * Application config
 *
 * @author   Anton Shevchuk
 * @created  08.07.11 12:14
 * @return   array
 */
return array(
    "profiler" => false,
    "loader" => array(
        "namespaces" => array(
            'Bluz'        => PATH_LIBRARY,
            'Application' => PATH_APPLICATION .'/models'
        ),
        "prefixes" => array(

        ),
    ),
    "cache" => array(
        "cache" => false,
        "servers" => array(
            array("memcached", 11211, 1),
        ),
    ),
    "acl" => array(
        "identityField" => "sid"
    ),
    "auth" => array(
        "adapter" => array(
            "name" => "equals",
            "options" => array(
                "encryptFunction" => function($password) {
                    return md5(md5($password) . 'salt');
                }
            )
        )
    ),
    "db" => array(
        "connect" => array(
            "type" => "mysql",
            "host" => "hippo",
            "name" => "p_bluz",
            "user" => "p_bluz",
            "pass" => "bluz_pass",
        ),
        'defaultAdapter' => true
    ),
    "view" => array(
        "path" => PATH_APPLICATION .'/layouts',
        "template" => 'index.phtml',
        "cache" => true,
        "cachePath" => PATH_DATA .'/cache',
        "data" => array(
            "title" => "PHP 5.3 Examples",
        ),
        "viewHelpersPath" => array(PATH_APPLICATION .'/layouts/helpers'),
        "viewHelpers" => array(
        ),
    ),
    "subview" => array(
        "cache" => true,
        "cachePath" => PATH_DATA .'/cache'
    ),
    "session" => array(
        "store" => "session",
        "options" => array(
            "savepath" => PATH_DATA .'/sessions'
        )
    ),
    "request" => array(
        "baseUrl" => '/',
    ),
    "routers" => array(
        array(
            "route" => "/",
            "defaults" => array("module" => "index", "controller" => "index")
        ),
        array(
            "route" => "/test/controls",
            "defaults" => array("module" => "test", "controller" => "controls")
        ),
        array(
            "route" => "/test/markup",
            "defaults" => array("module" => "test", "controller" => "markup")
        ),
        array(
            "route" => "/users/login",
            "defaults" => array("module" => "users", "controller" => "login"),
        ),
        array(
            "route" => "/users/logout",
            "defaults" => array("module" => "users", "controller" => "logout"),
        ),
        array(
            "route" => "/users/profile",
            "defaults" => array("module" => "users", "controller" => "profile"),
        ),
    )
);
