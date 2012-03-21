<?php
/**
 * Application config
 * 
 * @author   Anton Shevchuk
 * @created  08.07.11 12:14
 */
return array(
    "cache" => array(
        "cache" => false
    ),
    "view" => array(
        "cache" => false,
    ),
    "session" => array(
        "store" => "array"
    ),
    "routers" => array(
        "default" => array(
            "route" => "/",
            "defaults" => array("module" => "index", "controller" => "index")
        ),
        "test" => array(
            "route" => "/test/:controller",
            "defaults" => array("module" => "test", "controller" => "controls")
        ),
        "users" => array(
            "route" => "/users/:controller",
            "defaults" => array("module" => "users", "controller" => "login"),
            "reqs" => array("controller" => "login|logout|profile")
        ),
    )
);
