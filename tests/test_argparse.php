<?php
// This file is part of EasyTest. It is subject to the license terms in the
// LICENSE.txt file found in the top-level directory of this distribution.
// No part of this project, including this file, may be copied, modified,
// propagated, or distributed except according to the terms contained in the
// LICENSE.txt file.

class TestArgParse {
    public function test_parses_empty_argv() {
        // argv[0] is always the current executable name
        $argv = array('foo');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => false),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_identical(
            array(),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_parses_arguments() {
        // argv[0] is always the current executable name
        $argv = array('foo', 'one', 'two');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => false),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_equal(
            array('one', 'two'),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_parses_long_option() {
        // argv[0] is always the current executable name
        $argv = array('foo', '--verbose');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => easytest\LOG_VERBOSE),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_identical(
            array(),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_parses_short_option() {
        // argv[0] is always the current executable name
        $argv = array('foo', '-v');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => easytest\LOG_VERBOSE),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_identical(
            array(),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_parses_multiple_short_options() {
        // argv[0] is always the current executable name
        $argv = array('foo', '-vqv');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => easytest\LOG_VERBOSE),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_identical(
            array(),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_ends_parsing_on_first_argument() {
        // argv[0] is always the current executable name
        $argv = array('foo', 'bar', '--verbose');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => false),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_equal(
            array('bar', '--verbose'),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_treats_single_dash_as_an_argument() {
        // argv[0] is always the current executable name
        $argv = array('foo', '-', '--verbose');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => false),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_equal(
            array('-', '--verbose'),
            $args,
            "Arguments weren't parsed correctly"
        );
    }


    public function test_ends_parsing_on_double_dash() {
        // argv[0] is always the current executable name
        $argv = array('foo', '--', '--verbose');
        list($opts, $args) = easytest\_parse_arguments(count($argv), $argv);

        easytest\assert_identical(
            array('verbose' => false),
            $opts,
            "Options weren't parsed correctly"
        );
        easytest\assert_identical(
            array('--verbose'),
            $args,
            "Arguments weren't parsed correctly"
        );
    }
}
