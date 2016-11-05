<?php
class User {

private $_db,
		$_sessionName = null,
		$_cookieName = null,
		$_isLoggedIn = false;

public  $model,
		$session,
		$group;

	public function __construct($user = null) {

		$this->model = new UserModel();
		$this->session = new UserSession();
		$this->group = new GroupModel();
		
		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');

		if(Session::exists($this->_sessionName) && !$user) {

			$user = Session::get($this->_sessionName);

			if($this->find($user)) {
				$this->group->find("name",$this->model->username);
				$this->_isLoggedIn = true;
			} else {
				$this->logout();
			}
		} else {
			$this->find($user);
			$this->group->find("name",$this->model->username);
		}
	}

	public function exists() {
		return (is_numeric($this->model->id)) ? true : false;
	}

	public function find($user = null) {

		(is_numeric($user)) ? $key = "id" : $key = "username";

		return ($this->model->find($key,$user)) ? true : false;
	}

	public function create($data = array()) {

		if(!empty($data)){
	       
			$this->model->bind($data); 
			$this->model->store();
			$this->model->find('username',$data['username']);
			$this->login();
		}
	}

	public function update($data = array()) {

		if(!empty($data) && $this->isLoggedIn()) {

			$this->model->bind($data);
			$this->model->store();
		}
	}

	public function login($username = null, $password = null, $remember = false) {

		if(!$username && !$password && $this->exists()) {

			Session::put($this->_sessionName, $this->model->id);

		} else {

			$user = $this->find($username);

			if($user) {
				
				if($this->model->password === Hash::make($password, $this->model->salt)) {
					
					Session::put($this->_sessionName, $this->model->id);

					if($remember) {
					
						$hash = Hash::unique(); 

						$hashCheck = $this->session->find("user_id", $this->model->id);

						if(!$hashCheck) {

							$this->session->bind( 
								array(
									'user_id' => $this->model->id,
									'hash' => $hash
								)
							);

							$this->session->store();

						} else {

							$hash = $this->session->hash;
						}

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}
				}
			}
		}

		return false;
	}

	public function hasPermission($key) {

		$permissions = json_decode($this->group->permissions, true);

		if($permissions[$key] === 1) {

			return true;
		}

		return false;
	}

	public function isLoggedIn() {
		return $this->_isLoggedIn;
	}

	public function logout() {

		$this->session->drop();

		Cookie::delete($this->_cookieName);
		Session::delete($this->_sessionName);
	}
}