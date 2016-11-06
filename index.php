<?php
/*
 * project: collabvat
 * author: ivorscott
 * version: 1
 */

session_start();

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

require_once  __DIR__ . "/config.php";
require_once  __DIR__ . "/functions.php";

function autoload($class) {
  if(file_exists( __DIR__ . '/classes/' . $class . '.php')) {
    require_once  __DIR__ . '/classes/' . $class . '.php';
  } else {
    if(strstr($class,'Model')) {
      $name = explode('Model', $class);
      $class = $name[0];
    }
    require_once __DIR__ . '/models/' . strtolower($class) . '.php';
  }
}

spl_autoload_register('autoload');

if(Cookie::exists(Config::get('remember/cookie_name'))) {

  $hash = Cookie::get(Config::get('remember/cookie_name'));
  $session = new UserSession();
  $hashCheck = $session->find("hash", $hash);

  if($hashCheck) {
    $user = new User($session->user_id);
    $user->login();
  }
}

$load = new Loader();
$controller = $load->createController();
@$controller->execute();
