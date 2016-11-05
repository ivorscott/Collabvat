<?php
class Redirect {
	
	public static function to($location = null) {
		if($location) {
      if(strtolower($location) == 'go back') {
        $location = $_SERVER['HTTP_REFERER'];
      }
      header('Location: ' . $location);
		}
	}
}