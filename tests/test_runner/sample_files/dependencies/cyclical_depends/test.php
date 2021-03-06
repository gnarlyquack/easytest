<?php

namespace cyclical_depends;
use easytest;


function test_one(easytest\Context $context) {
    $context->depend_on('test_two');
}

function test_two(easytest\Context $context) {
    $context->depend_on('test_three');
}

function test_three(easytest\Context $context) {
    $context->depend_on('test_four');
}

function test_four(easytest\Context $context) {
    $context->depend_on('test_five');
}

function test_five(easytest\Context $context) {
    $context->depend_on('test_three');
}


class test {
    public function test_one(easytest\Context $context) {
        $context->depend_on('test_two');
    }

    public function test_two(easytest\Context $context) {
        $context->depend_on('test_three');
    }

    public function test_three(easytest\Context $context) {
        $context->depend_on('test_four');
    }

    public function test_four(easytest\Context $context) {
        $context->depend_on('test_five');
    }

    public function test_five(easytest\Context $context) {
        $context->depend_on('test_three');
    }
}
