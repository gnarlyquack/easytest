<?php

namespace anonymous;
use easytest;


function test_anonymous_class() {
    $class = new class {};
    easytest\assert_true(\is_object($class));
}

function test_i_am_a_function_name() {
    easytest\assert_true(true);
}


class test {
    public function test_anonymous_class() {
        $class = new class {};
        easytest\assert_true(\is_object($class));
    }

    private function test_i_am_a_method_name() {}
}
