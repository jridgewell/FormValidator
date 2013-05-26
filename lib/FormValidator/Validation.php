<?php

namespace FormValidator;

class Validation {
    public static function anything() {
        // No need for anything fancy
        return function() {
            return true;
        };
    }

    public static function acceptance($options = array()) {
        if (array_key_exists('optional', $options)) {
            $options['optional'] = false;
        }
        $accept = (static::option('accept', $options)) ?: true;
        return static::validateOrMessage(function($val) use ($accept) {
            return ($val == $accept);
        }, 'must be accepted', $options);
    }

    public static function presence($options = array()) {
        if (array_key_exists('optional', $options)) {
            $options['optional'] = false;
        }
        return static::validateOrMessage(function($val) {
            return (strlen($val) > 0);
        }, "can't be blank", $options);
    }

    // Non-ActiveRecord Validation
    public static function url($options = array()) {
        return static::validateOrMessage(function($val) {
            return (filter_var($val, FILTER_VALIDATE_URL) !== false);
        }, 'must be a url', $options);
    }

    // Non-ActiveRecord Validation
    public static function email($options = array()) {
        return static::validateOrMessage(function($val) {
            return (filter_var($val, FILTER_VALIDATE_EMAIL) !== false);
        }, 'must be an email', $options);
    }

    public static function length($options = array()) {
        $checks = array();

        if ($x = static::option('is', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return (strlen($val) >= $x);
            }, "is the wrong length (should be ${x} characters)");
        }
        if ($x = static::option('minimum', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return (strlen($val) >= $x);
            }, "is too short (minimum is ${x} characters)");
        }
        if ($x = static::option('maximum', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return (strlen($val) <= $x);
            }, "is too long (maximum is ${x} characters)");
        }

        return static::validateOrMessage(function($val) use ($checks) {
            foreach ($checks as $check) {
                $valid = $check($val);
                if ($valid !== true) {
                    return $valid;
                }
            }
            return true;
        }, null, $options);
    }

    public static function numericality($options = array()) {
        $checks = array();
        $filter = (static::option('only_integer', $options) === true) ? FILTER_VALIDATE_INT : FILTER_VALIDATE_FLOAT;

        $checks[] = static::validateOrMessage(function($val) use ($filter) {
            return (filter_var($val, $filter) !== false);
        }, 'is not a number');

        if (static::option('odd', $options)) {
            $checks[] = static::validateOrMessage(function($val) {
                return ($val % 2 === 1);
            }, 'must be odd');
        }
        if (static::option('even', $options)) {
            $checks[] = static::validateOrMessage(function($val) {
                return ($val % 2 === 0);
            }, 'must be even');
        }
        if ($x = static::option('equal_to', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return ($val == $x);
            }, "must be equal to ${x}");
        }
        if ($x = static::option('less_than', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return ($val < $x);
            }, "must be less than ${x}");
        }
        if ($x = static::option('less_than_or_equal_to', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return ($val <= $x);
            }, "must be less than or equal to ${x}");
        }
        if ($x = static::option('greater_than', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return ($val > $x);
            }, "must be greater than ${x}");
        }
        if ($x = static::option('greater_than_or_equal_to', $options)) {
            $checks[] = static::validateOrMessage(function($val) use ($x) {
                return ($val >= $x);
            }, "must be greater than or equal to ${x}");
        }

        return static::validateOrMessage(function($val) use ($checks) {
            foreach ($checks as $check) {
                $valid = $check($val);
                if ($valid !== true) {
                    return $valid;
                }
            }
            return true;
        }, null, $options);
    }



    ################################################################################
    ### Validations Require a Parameter ############################################
    ################################################################################

    public static function format($regex, $options = array()) {
        return static::validateOrMessage(function($val) use ($regex) {
            return (preg_match($regex, $val) === 1);
        }, 'is invalid', $options);
    }

    public static function confirmation($other_field_func, $options = array()) {
        return static::validateOrMessage(function($val) use ($other_field_func) {
            return ($val === call_user_func($other_field_func));
        }, "doesn't match confirmation", $options);
    }

    public static function inclusion($list, $options = array()) {
        return static::validateOrMessage(function($val) use ($list) {
            return in_array($val, $list);
        }, 'is not included in the list', $options);
    }

    public static function exclusion($list, $options = array()) {
        return static::validateOrMessage(function($val) use ($list) {
            return !in_array($val, $list);
        }, 'is reserved', $options);
    }

    public static function validate_with($func, $options = array()) {
        return static::validateOrMessage($func, null, $options);
    }



    ################################################################################
    ### Private Methods ############################################################
    ################################################################################

    private static function option($name, $options) {
        return (array_key_exists($name, $options)) ? $options[$name] : null;
    }

    private static function validateOrMessage($validation_func, $default_msg, $options = array()) {
        $msg = (static::option('message', $options)) ?: $default_msg;
        $blank = (static::option('optional', $options)) ?: false;
        return function($val) use ($validation_func, $msg, $blank) {
            if ($blank && strlen($val) === 0) {
                return true;
            }
            $ret = call_user_func($validation_func, $val);
            if ($ret === true) {
                return true;
            }
            return ($msg) ?: $ret;
        };
    }

}
