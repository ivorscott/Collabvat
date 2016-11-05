<?php
class UserController extends Controller {
  public function __construct($action, $url) {
      parent::__construct($action, $url);

      $this->loggedInUser = new User();
      $this->fsys = new FriendSys($this->loggedInUser);
      $this->userModel = new UserModel();
  }
  public function all() {
    $this->view->output($this->model);
  }
  public function register() {
    $this->view->output($this->model);
  }
  public function login() {
      $this->view->output($this->model);
  }
  public function logout() {
      $this->loggedInUser->logout();
      Redirect::to("/login");
  }
  public function index() {
      $this->view->output($this->model);
  }

/**
 * Hook into the Friends System Class to handle Friendships
 */
  public function request() {

    if(!empty($this->url['id']) && !empty($this->url['id_2'])) {
      $task = $this->url['id'];
      $allowableTasks = array('send','cancel','accept','unfriend');
      $this->userModel->find('id',$this->url['id_2']);

      if(in_array($this->url['id'], $allowableTasks)) {
        $this->fsys->doTask($task, $this->userModel);
        Redirect::to("go back");
      }
    }
  }
}