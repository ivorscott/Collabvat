<?php
class ErrorController extends Controller
{    
    public function __construct($action, $url) {
        parent::__construct($action, $url);
    }
    
    protected function error() {
        $this->view->output($this->model);
    }
}
