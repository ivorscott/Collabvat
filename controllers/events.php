<?php
class EventsController extends Controller {

  public function __construct($action, $url) {
    parent::__construct($action, $url);
    $this->user = new User();
  }

  public function index() {
    $this->view->output($this->model);
  }
}
