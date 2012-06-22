<?php
/**
 * @author Matt Labrum <matt@labrum.me>
 * @license Beerware
 * @link url
 */

/* does nothing. useful when you want a input included in the checking array, but not checked */
define("VALIDATE_DO_NOTHING", -14);

/* input is still valid if empty */
define("VALIDATE_EMPTY", -13);

/* valid if input is not empty */
define("VALIDATE_NOT_EMPTY", -12);

/* valid if input is a number */
define("VALIDATE_NUMBER", -11);

/* valid if input is a string */
define("VALIDATE_STRING", -10);

/* valid if input is an email */
define("VALIDATE_EMAIL", -9);

/* valid if input is a valid timezone */
define("VALIDATE_TIMEZONE", -8);

/* valid if input is a url */
define("VALIDATE_URL", -7);

/*
    if not specified in a param then Form::addListData is used to find the list, if you havent use that function your yourForm::getListData is called
    Params:
    Array list
 */
define("VALIDATE_IN_DATA_LIST", -6);

/*
    allows you to define a custom checking function, see above examples for an example of this
    Params:
    Callback callback
    mixed errorCode

    your callback will be called with the following params

    $value //the value to check
    $params //an array of params that you have defined

    if you return false then an error will be raised with the errorCode you defined

 */
define("VALIDATE_CUSTOM", -5);

/*
    Input must be a certain length

    Params: (both optional)
    int Min
    int Max
 */
define("VALIDATE_LENGTH", -4);



define("VALIDATE_MUSTMATCHFIELD", -3);
define("VALIDATE_MUSTMATCHREGEX", -2);

define("VALIDATE_UPLOAD", -1);


/*
    Standard error codes use these when calling the Form::error function
 */

define("VALIDATE_ERROR_EMAIL", "notemail");
define("VALIDATE_ERROR_EMPTY", "empty");
define("VALIDATE_ERROR_CUSTOM", "custom");
define("VALIDATE_ERROR_NOT_NUMBER", "number");
define("VALIDATE_ERROR_NOT_STRING", "string");


define("VALIDATE_ERROR_TOOSHORT", "stringshort");
define("VALIDATE_ERROR_TOOLONG", "stringlong");

define("VALIDATE_ERROR_NOT_MATCH_FIELD", "nomatchfield");
define("VALIDATE_ERROR_NOT_MATCH_REGEX", "nomatchregexfield");

define("VALIDATE_ERROR_TIMEZONE", "timezone");
define("VALIDATE_ERROR_NOTINLIST", "notinlist");

define("VALIDATE_ERROR_NOT_URL", "noturl");


class Validator{


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
        case VALIDATE_EMPTY:			return empty($value) ? VALIDATE_EMPTY : true;

        case VALIDATE_NUMBER: 			return self::isNumber($value) ? true : VALIDATE_ERROR_NOT_NUMBER;
        case VALIDATE_STRING: 			return self::isString($value) ? true : VALIDATE_ERROR_NOT_STRING;
        case VALIDATE_EMAIL: 			return self::isEmail($value) ? true : VALIDATE_ERROR_EMAIL;

        case VALIDATE_TIMEZONE: 		return self::isValidTimeZone($value) ? true : VALIDATE_ERROR_TIMEZONE;

        case VALIDATE_URL:				return FormValidator::isValidUrl($value) ? true : VALIDATE_ERROR_NOT_URL;

        case VALIDATE_LENGTH :			return FormValidator::isValidLength($value, $params);


        case VALIDATE_MUSTMATCHFIELD: 	return ($value == $form->data[$params['field']]) ? true : VALIDATE_ERROR_NOT_MATCH_FIELD;
        case VALIDATE_MUSTMATCHREGEX:	return preg_match($params['regex'], $value) ? true : VALIDATE_ERROR_NOT_MATCH_REGEX;

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
            return VALIDATE_ERROR_NOTINLIST;
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
                return VALIDATE_ERROR_TOOSHORT;
            }
        }

        if (isset($params['max'])) {
            if (strlen($value) > $params['max']) {
                return VALIDATE_ERROR_TOOLONG;
            }
        }
        return true;
    }


}


?>
