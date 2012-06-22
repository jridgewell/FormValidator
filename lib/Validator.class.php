<?php
/**
 * @author Matt Labrum <matt@labrum.me>
 * @license Beerware
 * @link url
 */

define("VALIDATE_DO_NOTHING", -14);
//define("VALIDATE_EMPTY", -13);
define("VALIDATE_NOT_EMPTY", -12);
define("VALIDATE_NUMBER", -11);
define("VALIDATE_STRING", -10);
define("VALIDATE_EMAIL", -9);
define("VALIDATE_TIMEZONE", -8);
define("VALIDATE_URL", -7);
define("VALIDATE_IN_DATA_LIST", -6);
define("VALIDATE_CUSTOM", -5);
define("VALIDATE_LENGTH", -4);
define("VALIDATE_MUST_MATCH_FIELD", -3);
define("VALIDATE_MUST_MATCH_REGEX", -2);
define("VALIDATE_UPLOAD", -1);


/*
    Standard error codes use these when calling the Form::error function
 */

define("VALIDATE_ERROR_NOT_EMAIL", "email");
define("VALIDATE_ERROR_EMPTY", "empty");
define("VALIDATE_ERROR_NOT_CUSTOM", "custom");
define("VALIDATE_ERROR_NOT_NUMBER", "number");
define("VALIDATE_ERROR_NOT_STRING", "string");
define("VALIDATE_ERROR_TOO_SHORT", "stringshort");
define("VALIDATE_ERROR_TOO_LONG", "stringlong");
define("VALIDATE_ERROR_NOT_MATCH_FIELD", "matchfield");
define("VALIDATE_ERROR_NOT_MATCH_REGEX", "matchregexfield");
define("VALIDATE_ERROR_NOT_TIMEZONE", "timezone");
define("VALIDATE_ERROR_NOT_IN_LIST", "inlist");
define("VALIDATE_ERROR_NOT_URL", "url");


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
            $params = Array();
        }




        switch($rule) {
            case VALIDATE_DO_NOTHING: 		return true;
            //case VALIDATE_EMPTY:			return empty($value) ? VALIDATE_EMPTY : true;
            case VALIDATE_NOT_EMPTY:		return self::isNotEmpty($value) ? true : VALIDATE_ERROR_EMPTY;
            case VALIDATE_NUMBER:			return self::isNumber($value) ? true : VALIDATE_ERROR_NOT_NUMBER;
            case VALIDATE_STRING:			return self::isString($value) ? true : VALIDATE_ERROR_NOT_STRING;
            case VALIDATE_EMAIL:			return self::isEmail($value) ? true : VALIDATE_ERROR_NOT_EMAIL;
            case VALIDATE_TIMEZONE:	        return self::isValidTimeZone($value) ? true : VALIDATE_ERROR_NOT_TIMEZONE;
            case VALIDATE_URL:				return self::isValidUrl($value) ? true : VALIDATE_ERROR_NOT_URL;
            case VALIDATE_LENGTH :			return self::isValidLength($value, $params);
            case VALIDATE_MUST_MATCH_FIELD:	return ($value == $form->data[$params['field']]) ? true : VALIDATE_ERROR_NOT_MATCH_FIELD;
            case VALIDATE_MUST_MATCH_REGEX:	return preg_match($params['regex'], $value) ? true : VALIDATE_ERROR_NOT_MATCH_REGEX;

            case VALIDATE_CUSTOM :
                if (isset($params['run_noerror']) && $params['run_noerror'] && $form->itemHasError($name)) {
                    return true;
                } else if (!call_user_func($params['callback'], $value, $params)) {
                    return $params['errorCode'];
                } else {
                    return true;
                }
                break;

            case VALIDATE_IN_DATA_LIST:
                if (($list = $form->getListData($name)) != false) {
                    if (in_array($value, $list)) {
                        return true;
                    }

                    if (isset($params["searchKeys"]) && $params["searchKeys"]) {
                        if (in_array($value, array_keys($list))) {
                            return true;
                        }
                    }
                } else if (isset($params['list'])) {
                    if (in_array($value,  $params['list'])) {
                        return true;
                    }

                    if (isset($params["searchKeys"]) && $params["searchKeys"]) {
                        if (in_array($value, array_keys($params['list']))) {
                            return true;
                        }
                    }
                }
                return VALIDATE_ERROR_NOT_IN_LIST;
                break;
        }
    }


    /**
     * Checks if the value is empty
     * @param Array $value
     * @return Boolean
     */
    static public function isNotEmpty($value) {
        return !empty($value);
    }

    /**
     * Checks if the value is a number
     * @param int $value
     * @return Boolean
     */
    static public function isNumber($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Checks if the value is a string
     * @param String $value
     * @return Boolean
     */
    static public function isString($value) {
        return true;
    }

    /**
     * Checks if the value is a valid email
     * @param String $value
     * @return Boolean
     */
    static public function isEmail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Checks if the value is timezone
     * @param String $value
     * @return Boolean
     */
    static public function isValidTimeZone($value) {
        return in_array($value, timezone_identifiers_list());
    }

    /**
     * Checks if the value is a url
     * @param String $url
     * @return Boolean
     */
    static public function isValidUrl($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Checks if the value is a specified length
     * @param String $value
     * @param Map $params
     * @return Boolean
     */
    static public function isValidLength($value, $params) {

        if (isset($params['min'])) {
            if (strlen($value) < $params['min']) {
                return VALIDATE_ERROR_TOO_SHORT;
            }
        }

        if (isset($params['max'])) {
            if (strlen($value) > $params['max']) {
                return VALIDATE_ERROR_TOO_LONG;
            }
        }
        return true;
    }


}
