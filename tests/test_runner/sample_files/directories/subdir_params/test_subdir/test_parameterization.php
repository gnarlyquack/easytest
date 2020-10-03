<?php

namespace subdir_params\subdir;

use easytest;


function setup_functions($one, $two) {
    return array($one, $two, $one * $two);
}



function test_function($one, $two, $three) {
    echo "$one $two $three";
    easytest\assert_true(true);
}


class TestClass {
    private $one;
    private $two;

    public function __construct($one, $two) {
        $this->one = $one;
        $this->two = $two;
    }


    function test() {
        echo "{$this->one} {$this->two}";
        easytest\assert_true(true);
    }
}
