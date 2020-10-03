<?php

namespace targets;
use easytest;


function test_one() {
    easytest\assert_true(true);
}

function test_two() {
    easytest\assert_true(true);
}

function test_three() {}


class test {
    public function test_one() {}

    public function test_two() {
        easytest\assert_true(true);
    }

    public function test_three() {}
}
