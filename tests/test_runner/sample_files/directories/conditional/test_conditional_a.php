<?php

namespace condition;

if (true) {

    class TestA {
        public function test() {
            \easytest\assert_true(true);
        }
    }

}
elseif (false) {

    class TestA {
        public function __construct() {
            trigger_error('I should not have been discovered!');
        }
    }


    class TestB {
        public function __construct() {
            trigger_error('I should not have been discovered!');
        }
    }


    class TestC {
        public function __construct() {
            trigger_error('I should not have been discovered!');
        }
    }

}
