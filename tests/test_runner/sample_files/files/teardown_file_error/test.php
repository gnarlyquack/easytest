<?php

namespace teardown_file_error;
use easytest;


function setup_file() {
    echo '.';
}

function teardown_file() {
    easytest\skip('Skip me');
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

    public function teardown_object() {
        echo '.';
    }

    public function setup() {
        echo '.';
    }

    public function teardown() {
        echo '.';
    }

    public function test_one() {
        easytest\assert_true(true);
    }

    public function test_two() {
        easytest\assert_true(true);
    }
}


function test_two() {
    easytest\assert_true(true);
}
