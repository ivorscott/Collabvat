<?php
class Loader {

protected   $url = array('controller'=>'','action'=>'','id'=>'','id_2'=>''),
            $visitorActions = array('login','register'),
            $controllerClass,
            $user;

    public function __construct() {
      // allow undefined index values
      $this->url['controller'] = @strtolower(htmlentities($_GET['controller']));
      $this->url['action'] = @strtolower(htmlentities($_GET['action']));
      $this->url['id'] = @strtolower(htmlentities($_GET['id']));
      $this->url['id_2'] = @strtolower(htmlentities($_GET['id_2']));

      $this->user = new User();

      if ($this->user->isLoggedIn()) {
        // Default Controller after login
        if ($this->url['controller'] == '') {
          $this->url['controller'] = 'chat';
          $this->controllerClass = 'ChatController';
        } else {
          $this->controllerClass = ucfirst($_GET['controller']) . "Controller";
        }
        if ($this->url['action'] == '') {
          $this->url['action'] = 'index';
        }
      } else {
        ($this->url['controller'] == '') ? $this->url['controller'] = 'login' : false;

        if (in_array($this->url['controller'], $this->visitorActions)) {
          $this->url['action'] = $this->url['controller'];
          $this->url['controller'] = 'user';
          $this->controllerClass = 'UserController';
        }
        if ($this->url['controller'] == 'api') {
          $this->controllerClass = 'ApiController';
        }
      }
    }

    public function createController() {
        if(file_exists('controllers/' . $this->url['controller'] . '.php')) {
            require_once('controllers/' . $this->url['controller'] . '.php');
        } else {
            require_once('controllers/error.php');
            return new ErrorController('error',$this->url);
        }
        if(class_exists($this->controllerClass)) {
            $parents = class_parents($this->controllerClass);

            if(in_array('Controller',$parents)) {

                // bypass method_exists() if existing user page

                if($this->url['controller'] === 'user') {

                  if($this->user->find($this->url['action'])) {
                    return new $this->controllerClass('index', $this->url);
                  }
                }
                if(method_exists($this->controllerClass,$this->url['action'])) {
                    return new $this->controllerClass($this->url['action'], $this->url);
                } else {
                    require_once('controllers/error.php');
                    return new ErrorController('error',$this->url);
                }
            } else {
                require_once('controllers/error.php');
                return new ErrorController("error",$this->url);
            }
        } else {
            require_once('controllers/error.php');
            return new ErrorController('error',$this->url);
        }
    }
}
