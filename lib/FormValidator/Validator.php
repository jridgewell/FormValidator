<?php

namespace FormValidator;

use \FormValidator\Validatation;

class Validator {

	/**
	 * validates the element against the passed validation rules
	 * @param Mixed $rule
	 * @param Mixed $value
	 * @param String $name
	 * @param Form $name
	 * @return Mixed errorCode
	 */
	static public function isElementValid($rule, $value, $name, $form) {
		// Due to the format of the rules, some rules can have parameters, so we strip that into a seperate variable
		if (is_array($rule)) {
			$params = $rule;
			$rule = array_shift($rule);
		} else {
			$params = array();
		}
		switch($rule) {
			case Validation::DO_NOTHING:		return true;
			case Validation::NOT_EMPTY:		return (self::isNotEmpty($value) ? true : Validation::NOT_EMPTY);
			case Validation::NUMBER:			return (self::isNumber($value) ? true : Validation::NUMBER);
			case Validation::STRING:			return (self::isString($value) ? true : Validation::STRING);
			case Validation::EMAIL:			return (self::isEmail($value) ? true : Validation::EMAIL);
			case Validation::TIMEZONE:			return (self::isValidTimeZone($value) ? true : Validation::TIMEZONE);
			case Validation::URL:				return (self::isValidUrl($value) ? true : Validation::URL);
			case Validation::MUST_MATCH_FIELD:	return (self::isValueSameAs($value, $params) ? true : Validation::MUST_MATCH_FIELD);
			case Validation::MUST_MATCH_REGEX:	return (self::doesRegexMatch($value, $params) ? true : Validation::MUST_MATCH_REGEX);
            case Validation::LENGTH:			return (self::isValidLength($value, $params) ? true : Validation::LENGTH);
			case Validation::CUSTOM:
				if (!call_user_func(array($form, $params['callback']), $value, $params)) {
					$errorCode = (array_key_exists('errorCode', $params)) ? $params['errorCode'] : Validation::CUSTOM;
					return $errorCode;
				} else {
					return true;
				}
				break;
			case Validation::IN_DATA_LIST:
				$list = null;
				if (isset($params['list'])) {
					$list = $params['list'];
				} else {
					$list = $form->getListData($name);
				}
				if (isset($list) && is_array($list)) {
					$check = $list;
					if (isset($params['useKeys']) && $params['useKeys']) {
						$check = array_keys($list);
					}
					if (in_array($value,  $check)) {
						return true;
					}
				}
				return Validation::IN_DATA_LIST;
				break;
		}
	}


	/**
	 * Checks if the value is a valid email
	 * @param String $value
	 * @return Boolean
	 */
	static private function isEmail($value) {
		return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
	}

	/**
	 * Checks if the value is empty
	 * @param array $value
	 * @return Boolean
	 */
	static private function isNotEmpty($value) {
		return (strlen($value) > 0);
	}

	/**
	 * Checks if the value is a number
	 * @param int $value
	 * @return Boolean
	 */
	static private function isNumber($value) {
		return (filter_var($value, FILTER_VALIDATE_INT) !== false);
	}

	/**
	 * Checks if the value is a string
	 * @param String $value
	 * @return Boolean
	 */
	static private function isString($value) {
		return true;
	}

	/**
	 * Checks if the value is a specified length
	 * @param String $value
	 * @param Map $params
	 * @return Boolean
	 */
	static private function isValidLength($value, $params) {
		if (isset($params['min'])) {
			if (strlen($value) < $params['min']) {
				return false;
			}
		}
		if (isset($params['max'])) {
			if (strlen($value) > $params['max']) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks if the value is timezone
	 * @param String $value
	 * @return Boolean
	 */
	static private function isValidTimeZone($value) {
		return in_array($value, timezone_identifiers_list());
	}

	/**
	 * Checks if the value is a url
	 * @param String $url
	 * @return Boolean
	 */
	static private function isValidUrl($value) {
		return (filter_var($value, FILTER_VALIDATE_URL) !== false);
	}

	/**
	 * Checks if the value is the same as another field (value2).
	 * @param String $value
	 * @param String $value2
	 * @param Array $params
	 * @return Boolean
	 */
	static private function isValueSameAs($value, $value2) {
		return ($value === $value2['field']);
	}
	
	/**
	 * Checks if the value matches a provided regex.
	 * @param String $value
	 * @param Array $params
	 * @return Boolean
	 */
	static private function doesRegexMatch($value, $params) {
		return preg_match($params['regex'], $value);
	}
}
