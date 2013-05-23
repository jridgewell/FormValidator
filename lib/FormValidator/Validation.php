<?php

namespace FormValidator;

class Validation {
    /**
     * The validation rules that can be used
     */

    public static function anything() {
        return function() {
            return true;
        };
    }

    public static function presence() {
        return function($val) {
            return strlen($val) > 0;
        };
    }

    public static function length($options) {
        $max = (self::option('max', $options)) ?: PHP_INT_MAX;
        $min = (self::option('min', $options)) ?: 0;
        return function($val) use ($max, $min) {
            $len = strlen($val);
            return ($min <= $len && $len <= $max);
        };
    }

    public static function numericality($options) {
        $filter = (self::option('only_integer')) ? FILTER_VALIDATE_INT : FILTER_VALIDATE_FLOAT;
        $filter_function = function($val) use ($filter) {
            return (filter_var($val, $filter) !== false);
        };

        if (self::option('allow_nil', $options)) {
            return $filter_function;
        }

        $checks = Array();
        if (self::option('odd', $options)) {
            $checks[] = function($val) {
                return ($val % 2 === 1);
            };
        }
        if (self::option('even', $options)) {
            $checks[] = function($val) {
                return ($val % 2 === 0);
            };
        }

        return function($val) use ($filter_function, $checks) {
            foreach ($checks as &$check) {
                if ($check($val) === false) {
                    return false;
                }
            }
            return $filter_function($val);
        };
    }

    public static function url() {
        return function($val) {
            return (filter_var($val, FILTER_VALIDATE_URL) !== false);
        };
    }

    public static function email() {
        return function($val) {
            return (filter_var($val, FILTER_VALIDATE_EMAIL) !== false);
        };
    }

    public static function format($regex) {
        return function($val) use ($regex) {
            return preg_match($regex, $value);
        };
    }

    public static function confirmation($other_field_func) {
        return function($val) use ($other_field_func) {
            return $val === $other_field_func();
        };
    }

    public static function inclusion($options) {
        $list_key = (self::option('in', $options)) ? 'in' : 'within';
        $list = $options[$list_key];
        return function($val) use ($list) {
            return in_array($val, $list, true);
        };
    }

    private static function option($name, $options) {
        return ($options[$name] && $options[$name] === true);
    }

}
