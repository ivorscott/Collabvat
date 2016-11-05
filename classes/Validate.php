<?php
class Validate {
	private $_passed = false,
			$_errors = array(),
			$_db = null;

	public function __construct() {}

	public function check($source, $strategy = array()) {

		foreach($strategy as $field => $rules) {
			foreach($rules as $rule => $rule_value) {

				$input = trim($source[$field]);

				if($rule === 'required' && $rule_value === true && empty($input)) {
					
					if($field === 'password_again') {
						
						$this->addError("Please re-type your password.");
					} else {
						$this->addError("The {$field} field is required.");
					}
				} else if (!empty($input)) {

					switch($rule) {
						case 'min':
							if(strlen($input) < $rule_value) {
								$this->addError("Your {$field} must be a minimum of {$rule_value} characters.");
							}
						break;
						case 'max':
							if(strlen($input) > $rule_value) {
								$this->addError("The {$field} field must be a maximum of {$rule_value} characters.");
							}
						break;
						case 'matches':
							if($input != $source[$rule_value]) {
								$this->addError("The passwords must match.");
							}
						break;
						case 'unique':
							$model = new $rule_value();
							$check = $model->find($field, $input);
							if($check){
								$this->addError("This {$field} is already taken.");
							}
						break;
					}
				}
			}
		}

		if(empty($this->_errors)) {
			$this->_passed = true;
		}
	}

	protected function addError($error) {
		$this->_errors[] = $error;
	}

	public function passed() {
		return $this->_passed;
	}

	public function errors() {
		return $this->_errors;
	}
}