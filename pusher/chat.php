<?php

define('SCRIPT_BASE', realpath(dirname(__FILE__)));

require_once SCRIPT_BASE . "../config.php";

function autoload($class) {
  if(file_exists(SCRIPT_BASE . '../classes/' . $class . '.php')) {
    require_once SCRIPT_BASE . '../classes/' . $class . '.php';
  } else if(strstr($class, 'Controller')) {
    require_once SCRIPT_BASE . '../controllers/' . strtolower(strstr($class, 'Controller', true)) . '.php';
  } else {
    if(strstr($class,'Model')) {
      $name = explode('Model', $class);
      $class = $name[0];
    }
    require_once SCRIPT_BASE . '../models/' . strtolower($class) . '.php';
  }
}
spl_autoload_register('autoload');
require_once(SCRIPT_BASE . './vendor/autoload.php');
require_once(SCRIPT_BASE . 'Activity.php');
require_once(SCRIPT_BASE . 'config.php');

$chat_info = $_POST['chat_info'];
$room_id =  $_POST['chat_info']['room'];
date_default_timezone_set($chat_info['timezone']);
$channel_name = null;

if( !isset($_POST['chat_info']) ){
  header("HTTP/1.0 400 Bad Request");
  echo('chat_info must be provided');
}

if( !isset($_SERVER['HTTP_REFERER']) ) {
  header("HTTP/1.0 400 Bad Request");
  echo('channel name could not be determined from HTTP_REFERER');
}

$channel_name = get_channel_name($_SERVER['HTTP_REFERER']);
$options = sanitise_input($chat_info);
$activity = new Activity('chat-message', $options['text'], $options);
$pusher = new Pusher(APP_KEY, APP_SECRET, APP_ID);
$data = $activity->getMessage();
$data[] = $room_id;
ChatController::saveMessageActivity($data);
$response = $pusher->trigger($channel_name, 'chat_message', $data, null, true);
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
$result = array('activity' => $data, 'pusherResponse' => $response);
echo(json_encode($result));

function get_channel_name($http_referer) {
  // not allowed :, / % #
  $pattern = "/(\W)+/";
  $channel_name = preg_replace($pattern, '-', $http_referer);
  return $channel_name;
}

function sanitise_input($chat_info) {
  $email = isset($chat_info['email'])?$chat_info['email']:'';
  $options = array();
  $options['displayName'] = substr(htmlspecialchars($chat_info['nickname']), 0, 30);
  $options['text'] = substr(htmlspecialchars($chat_info['text']), 0, 300);
  $options['email'] = substr(htmlspecialchars($email), 0, 100);
  $options['timezone'] = $chat_info['timezone'];
  $options['get_gravatar'] = true;
  return $options;
}
?>
