<?php
class View {
    
  protected  $viewFile, $user;
 
  public function __construct($controllerClass, $action) {
      $controllerName = str_replace( "Controller", "", $controllerClass );
      $this->viewFile = "views/" . $controllerName . "/" . $action . ".php";
  }

  public function output( $model = null, $template = 'layout' ) {
      $templateFile = "views/".$template.".php";

      if(file_exists($this->viewFile)) {
          if(file_exists($templateFile)) {
              require_once( $templateFile );
          } else {
              require_once( $this->viewFile );
          }
      }
  }
}