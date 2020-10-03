<?php

namespace multiple_depends;
use easytest;


function test_one(easytest\Context $context) {
    $context->depend_on('test::test_two', 'test_three', 'test::test_four');
}

function test_three(easytest\Context $context) {
    $context->depend_on('test::test_two', 'test_seven');
    easytest\assert_true(true);
}

function test_five(easytest\Context $context) {
    $context->depend_on('test::test_six', 'test::test_nine');
    easytest\assert_true(true);
}

function test_seven() {
    easytest\assert_true(true);
}

function test_ten() {
    easytest\assert_true(true);
}



class test {
    public function __construct() {
        echo '.';
    }

    public function test_two(easytest\Context $context) {
        $context->depend_on('::test_five', 'test_six');
        easytest\assert_true(true);
    }

    public function test_four(easytest\Context $context) {
        $context->depend_on('test_eight', 'test_nine');
    }

    public function test_six() {
        easytest\assert_true(true);
    }

    public function test_eight() {
        easytest\fail('I fail');
    }

    public function test_nine(easytest\Context $context) {
        $context->depend_on('::test_ten');
        easytest\assert_true(true);
    }
}
