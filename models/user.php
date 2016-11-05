<?php
class UserModel extends Model {

public  $id = null,
		$username = null,
		$email = null,
		$joined = null,
		$timezone = null,
		$password = null,
		$salt = null,
		$privilege = null,
		$bio = null,
		$firstname = null,
		$lastname = null,
    $fullname = null,
		$table = "users";

  public function _toString() {
    return $this;
  }
}
