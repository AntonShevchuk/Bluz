<?php
/**
 * Logout proccess
 *
 * @author   Anton Shevchuk
 * @created  20.07.11 18:39
 * @return closure
 */
namespace Application;
use Bluz;
return
/**
 * @param $login
 * @param $password
 * @return \closure
 */
function() use ($view) {
    /**
     * @var Bluz\Application $this
     */
    $this->getAuth()->clearIdentity();
    $this->getMessages()->addNotice('You are signout');
    $this->redirectTo('index', 'index');
};