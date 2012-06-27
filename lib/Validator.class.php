<?php
/**
 * @author Matt Labrum <matt@labrum.me>
 * @license Beerware
 * @link url
 */

define('VALIDATE_DO_NOTHING', -13);
define('VALIDATE_NOT_EMPTY', -12);
define('VALIDATE_NUMBER', -11);
define('VALIDATE_STRING', -10);
define('VALIDATE_EMAIL', -9);
define('VALIDATE_TIMEZONE', -8);
define('VALIDATE_URL', -7);
define('VALIDATE_IN_DATA_LIST', -6);
define('VALIDATE_CUSTOM', -5);
define('VALIDATE_LENGTH', -4);
define('VALIDATE_MUST_MATCH_FIELD', -3);
define('VALIDATE_MUST_MATCH_REGEX', -2);
define('VALIDATE_UPLOAD', -1);


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
            case VALIDATE_DO_NOTHING:       return true;
            case VALIDATE_NOT_EMPTY:        return self::isNotEmpty($value)                     ? true : VALIDATE_NOT_EMPTY;
            case VALIDATE_NUMBER:           return self::isNumber($value)                       ? true : VALIDATE_NUMBER;
            case VALIDATE_STRING:           return self::isString($value)                       ? true : VALIDATE_STRING;
            case VALIDATE_EMAIL:            return self::isEmail($value)                        ? true : VALIDATE_EMAIL;
            case VALIDATE_TIMEZONE:         return self::isValidTimeZone($value)                ? true : VALIDATE_TIMEZONE;
            case VALIDATE_URL:              return self::isValidUrl($value)                     ? true : VALIDATE_URL;
            case VALIDATE_MUST_MATCH_FIELD: return self::valueIsSameAs($value, $form, $params)  ? true : VALIDATE_MUST_MATCH_FIELD;
            case VALIDATE_LENGTH:           return self::isValidLength($value, $params)         ? true : VALIDATE_LENGTH;
            case VALIDATE_MUST_MATCH_REGEX: return preg_match($params['regex'], $value)         ? true : VALIDATE_MUST_MATCH_REGEX;
            case VALIDATE_CUSTOM:
                 if (!call_user_func(array($form, $params['callback']), $value, $params)) {
                    return $params['errorCode'];
                 } else {
                    return true;
                 }
                 break;
            case VALIDATE_IN_DATA_LIST:
                $list = null;
                if (isset($params['list'])) {
                    $list = $params['list'];
                } else {
                    $list = $form->getListData($name);
                }
                if (isset($list) && is_array($list)) {
                    if (isset($params['useKeys']) && $params['useKeys']) {
                        if (in_array($value, array_keys($list))) {
                            return true;
                        }
                    } else if (in_array($value,  $list)) {
                        return true;
                    }
                }
                return VALIDATE_IN_DATA_LIST;
                break;
        }
    }


    /**
     * Checks if the value is empty
     * @param array $value
     * @return Boolean
     */
    static public function isNotEmpty($value) {
        return (strlen($value) > 0);
    }

    /**
     * Checks if the value is a number
     * @param int $value
     * @return Boolean
     */
    static public function isNumber($value) {
        return (filter_var($value, FILTER_VALIDATE_INT) !== false);
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
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
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
        return (filter_var($value, FILTER_VALIDATE_URL) !== false);
    }


    /**
     * Checks if the value is the same as another field.
     * @param String    $value
     * @param Object    $form
     * @param Array     $params
     */
    static public function valueIsSameAs($value, $form, $params) {
        return ($value === $form->getDataForName($params['field'], $form->data));
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
                return VALIDATE_LENGTH;
            }
        }

        if (isset($params['max'])) {
            if (strlen($value) > $params['max']) {
                return VALIDATE_LENGTH;
            }
        }
        return true;
    }


}
