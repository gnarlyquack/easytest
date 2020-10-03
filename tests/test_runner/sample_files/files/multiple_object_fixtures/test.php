<?php

namespace multiple_object_fixtures;
use easytest;


function setup_file() {
    echo '.';
}

function teardown_file() {
    echo '.';
}


function setup_function() {
    echo '.';
}

function teardown_function() {
    echo '.';
}


function test_one() {
    easytest\assert_true(true);
}



class test {
    public function setup_object() {
        echo '.';
    }

    public function SetUpObject() {
        echo '.';
    }

    public function teardown_object() {
        echo '.';
    }

    public function TearDownObject() {
        echo '.';
    }

    public function setup() {
        echo '.';
    }

    public function teardown() {
        echo '.';
    }

    public function test_one() {}

    public function test_two() {}
}


function test_two() {
    easytest\assert_true(true);
}
