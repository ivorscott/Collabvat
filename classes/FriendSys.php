<?php

/**
 * Created by PhpStorm.
 * User: ivorscott
 * Date: 12/24/15
 * Time: 11:49 PM
 */
class FriendSys {

  public $otherUser, $user;

  public function __construct($loggedInUser) {
    // current user logged in
    $this->user = $loggedInUser->model->id;
  }
  public function friends($otherUser) {
    $q = $this->doTask('findAllFriends',$otherUser);

    if($q->rowCount()) {
      $q->setFetchMode(PDO::FETCH_ASSOC);
      while ($data = $q->fetch()) {
        $results[] = $data;
      }
    }

    $q->closeCursor();
    $q = null;

    return $results;
  }

  public function doTask($task,$otherUser = null) {
    $db = DB::getInstance();
    if($otherUser) {
      $this->otherUser = $otherUser->id;
    }
    $sql = $this->buildQuery($task);
    return $db->query($sql);
  }
  public function buildQuery($action) {
    if($action == 'send') {
      $sql = "INSERT INTO frnd_req VALUES('','{$this->user}','{$this->otherUser}')";
    }
    if($action == 'cancel') {
      $sql = "DELETE FROM `frnd_req` WHERE `from`='{$this->user}' AND `to`='{$this->otherUser}'";
    }
    if($action == 'accept') {
      $sql = "DELETE FROM `frnd_req` WHERE `from`='{$this->otherUser}' AND `to`='{$this->user}';
              INSERT INTO frnds VALUES('','{$this->otherUser}','{$this->user}')";
    }
    if($action == 'unfriend') {
      $sql = "DELETE FROM frnds WHERE (user_one='{$this->user}' AND user_two='{$this->otherUser}') OR (user_one='{$this->otherUser}' AND user_two='{$this->user}')";
    }
    if($action == 'findAllFriends') {
      $sql = "SELECT user_one, user_two FROM frnds WHERE user_one='{$this->otherUser}' OR user_two='{$this->otherUser}'";
    }
    return $sql;
  }
}

