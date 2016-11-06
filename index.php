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

define('SCRIPT_BASE', realpath(dirname(__FILE__)));
echo SCRIPT_BASE,"\n";
// Ensure library is on include_path
set_include_path(
    get_include_path() . PATH_SEPARATOR . SCRIPT_BASE
);
require_once  SCRIPT_BASE . "/config.php";
echo SCRIPT_BASE . "/config.php","\n";
require_once  SCRIPT_BASE . "/functions.php";
echo SCRIPT_BASE . "/functions.php","\n";

function autoload($class) {
  echo SCRIPT_BASE . '/classes/' . $class . '.php',"\n";
  if(file_exists( SCRIPT_BASE . '/classes/' . $class . '.php')) {
    require_once  SCRIPT_BASE . '/classes/' . $class . '.php';
  } else {
    if(strstr($class,'Model')) {
      echo "before explode class name is ", $class,"\n";
      $name = explode('Model', $class);
      $class = $name[0];
      echo "after explode class name is ", $class,"\n";
    }
    echo SCRIPT_BASE . '/models/' . strtolower($class) . '.php',"\n";
    require_once SCRIPT_BASE . '/models/' . strtolower($class) . '.php';
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
