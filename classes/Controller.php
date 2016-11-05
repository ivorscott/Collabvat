<?php
abstract class Controller {
    
protected   $action,
            $model,
            $view,
            $url;
    
    public function __construct($action, $url) {

        $this->action = $action;
        $this->view = new View(get_class($this), $action);
        $this->url = $url;
        $this->setModel();
    }

    public function setModel() {
        
        $model['title'] = ''; 

        if($this->action == "index"){
            $model['title'] = $this->url['controller'];
        } else {
            $model['title'] = $this->action;
        }
        $model['url'] =  $this->url;
        $this->model = $model;
    }
     
    public function execute() {
        
        return $this->{$this->action}();
    }
}
