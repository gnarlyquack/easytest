<?php

namespace test_output;
use easytest;


function test() {
    echo '.';
    easytest\assert_true(true);
}

class test {
    public function test_method() {
        echo '.';
        easytest\assert_true(true);
    }
}
