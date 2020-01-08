<?php

if (!class_exists('WOOCCM_Field_Additional')) {

  class WOOCCM_Field_Additional extends WOOCCM_Field {

    protected static $_instance;
    protected $prefix = 'additional';
    protected $option_name = 'wooccm_additional';

    public static function instance() {
      if (is_null(self::$_instance)) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }

  }

}
