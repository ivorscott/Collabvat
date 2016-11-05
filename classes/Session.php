<?php
class Session {

	public static function exists($name) {
		return isset($_SESSION[$name]);
	}

	public static function get($name) {
		if(isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}
	}
	
	public static function put($name, $value) {
		return $_SESSION[$name] = $value;
	}

	public static function delete($name) {
		if(self::exists($name)) unset($_SESSION[$name]);
	}

	public static function flash($name, $string = null) {
		
		if(self::exists($name)) {
			
			$session = self::get($name);
			self::delete($name);
			return $session;

		} else if ($string) {
			self::put($name, $string);
		}
	}
}