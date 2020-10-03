<?php

/*
class TestComment {}
*/


// function test_comment() {}

function test_one() {
    easytest\assert_true(true);
}


class Test1 {
    function // Comments between the 'function' keyword
             /* and the test method! */ TestMe() {
        easytest\assert_true(true);
    }
}


$test_variable = null;


<<<STRING
class TestString {}
STRING;


function TestTwo () {
    easytest\assert_true(true);
}

class TestTwo
{
    public // visibility
        function /* as opposed to a non-function ? */ test1() {
        easytest\assert_true(true);
    }

    public function test2() {
        easytest\assert_true(true);
    }

    public function test3() {
        easytest\assert_true(true);
    }
}


class NotATest {}


function some_helper_function() {}


class // valid tokens between the 'class' keyword and the test name
      /* should be handled correctly */
    test // and also comments between the class name
    /* and the opening brace */
{
    private function test_one() {}

    public function test_two() {
        easytest\assert_true(true);
    }

    protected function test_three() {}
}


function test() {
    easytest\assert_true(true);
}
