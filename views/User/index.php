<?php
  // logged in user
  $my_id = $user->model->id;
  // current user profile
  $thisUser = new UserModel();
  $username = $model['url']['action'];
  if($username == 'index') {
    $username = $user->model->username;
  }
?>

<div class="container">

  <h1><?=$username;?></h1>

<?php

if($thisUser->find('username',$username)){

  $db = DB::getInstance();

  //Send Request, Cancel, Unfriend

  if($thisUser->id != $user->model->id) {

    $sql = "SELECT id FROM frnds
			WHERE (user_one='$my_id' AND user_two='{$thisUser->id}')
			OR (user_one='{$thisUser->id}' AND user_two='$my_id') ";
    // echo $sql;
    $check_frnd_query = $db->query($sql);

    if($check_frnd_query->rowCount()) {
      echo "<a href='#' class='box'>Already friends</a> |
		  	  <a href='/user/request/unfriend/{$thisUser->id}' class='box'>Unfriend {$thisUser->username}</a>";
    } else {

      $sql = "SELECT `id` FROM `frnd_req`
				WHERE `from`={$thisUser->id} AND `to`='$my_id'";
      $from_query =  $db->query($sql);

      $sql = "SELECT `id` FROM `frnd_req`
		 		WHERE `from`=$my_id AND `to`='{$thisUser->id}'";
      $to_query = $db->query($sql);

      if($from_query->rowCount()) {
        echo "<a href='#' class='box'>Ignore</a> | <a href='/user/request/accept/{$thisUser->id}' class='box'>Accept</a>";
      } else if ($to_query->rowCount()) {
        echo "<a href='/user/request/cancel/{$thisUser->id}' class='box'>Cancel Request</a>";
      } else {
        echo "<a href='/user/request/send/{$thisUser->id}' class='box '>Send Friend Request</a>";
      }
    }
  }
}

?>

  <h2>Friends<h2>

<?php
 //list friends
$thisUser->find('username',$username);
$fsys = new FriendSys();
$r = '';

$results = $fsys->friends($thisUser);

foreach ($results as $friend) {
  $user_one = $friend['user_one'];
  $user_two = $friend['user_two'];

  if ($user->model->username == $username) {
    if($user_one == $user->model->id) {
      $thisUser->find('id',$user_two);
    } else {
      $thisUser->find('id',$user_one);
    }
  } else {
    if($user_one == $thisUser->id) {
      $thisUser->find('id',$user_two);
    } else {
      $thisUser->find('id',$user_one);
    }
  }
  $r .= '<p><a class="user" href="/user/' .
    $thisUser->username . '">'. $thisUser->username . '</a></p>';

  $thisUser->find('username',$username); // reset
}
echo $r;

?>

</div>
