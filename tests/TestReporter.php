<?php

class TestReporter {
    private $reporter;
    private $ob_level;

    public function setup() {
        $this->reporter = new easytest\Reporter();
        $this->ob_level = ob_get_level();
        ob_start();
    }

    public function teardown() {
        while (ob_get_level() > $this->ob_level) {
            ob_end_clean();
        }
    }

    // helper assertions

    private function assert_report($expected) {
        $this->reporter->render_report();
        $actual = ob_get_clean();
        assert('$expected === $actual');
    }

    // tests

    public function test_blank_report() {
        $this->assert_report("Tests: 0\n");
    }

    public function test_report_success() {
        $this->reporter->report_success();
        $this->assert_report("Tests: 1\n");
    }

    public function test_report_error() {
        $this->reporter->report_error('source', 'message');
        $expected = <<<OUT
=============================     Errors     ==============================

1) source
message


Tests: 0, Errors: 1\n
OUT;
        $this->assert_report($expected);
    }

    public function test_report_failure() {
        $this->reporter->report_failure('source', 'message');
        $expected = <<<OUT
============================     Failures     =============================

1) source
message


Tests: 1, Failures: 1\n
OUT;
        $this->assert_report($expected);
    }

    public function test_combined_report() {
        $this->reporter->report_success();
        $this->reporter->report_failure('fail1', 'failure 1');
        $this->reporter->report_error('error1', 'error 1');
        $this->reporter->report_success();
        $this->reporter->report_error('error2', 'error 2');
        $this->reporter->report_failure('fail2', 'failure 2');
        $this->reporter->report_error('error3', 'error 3');

        $expected = <<<OUT
=============================     Errors     ==============================

1) error1
error 1


2) error2
error 2


3) error3
error 3


============================     Failures     =============================

1) fail1
failure 1


2) fail2
failure 2


Tests: 4, Errors: 3, Failures: 2\n
OUT;
        $this->assert_report($expected);
    }
}
