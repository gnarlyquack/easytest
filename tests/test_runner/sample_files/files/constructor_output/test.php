<?php

namespace constructor_output;
use easytest;


function test_one() {
    easytest\assert_true(true);
}


class test {
    public function __construct() {
        echo '.';
    }


    public function test_method() {
        easytest\assert_true(true);
    }
}


function test_two() {
    easytest\assert_true(true);
}
