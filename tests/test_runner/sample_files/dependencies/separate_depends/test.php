<?php

namespace separate_depends;
use easytest;


function test_one(easytest\Context $context) {
    $context->depend_on('test::test_two');
    $context->depend_on('test_three');
    $context->depend_on('test::test_four');
}

function test_three(easytest\Context $context) {
    $context->depend_on('test::test_two');
    $context->depend_on('test_seven');
    easytest\assert_true(true);
}

function test_five(easytest\Context $context) {
    $context->depend_on('test::test_six');
    $context->depend_on('test::test_nine');
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
        $context->depend_on('::test_five');
        $context->depend_on('test_six');
        easytest\assert_true(true);
    }

    public function test_four(easytest\Context $context) {
        $context->depend_on('test_eight');
        $context->depend_on('test_nine');
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
