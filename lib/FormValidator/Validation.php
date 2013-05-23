<?php

namespace FormValidator;

class Validation {
    /**
     * The validation rules that can be used
     */
    const CUSTOM           = -1;
    const DO_NOTHING       = -2;
    const EMAIL            = -3;
    const IN_DATA_LIST     = -4;
    const LENGTH           = -5;
    const MUST_MATCH_FIELD = -6;
    const MUST_MATCH_REGEX = -7;
    const NOT_EMPTY        = -8;
    const NUMBER           = -9;
    const STRING           = -10;
    const TIMEZONE         = -11;
    const UPLOAD           = -12;
    const URL              = -13;

}
