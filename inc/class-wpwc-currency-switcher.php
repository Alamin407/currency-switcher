<?php

class WPWC_Currency_Swithcer{

    private static $instance = null;

    public static function init(){
        if(self::$instance === null){
            self::$instance = new self;
        }
    }

    public function __construct(){
        
    }

}