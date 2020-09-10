<?php
// This file is part of EasyTest. It is subject to the license terms in the
// LICENSE.txt file found in the top-level directory of this distribution.
// No part of this project, including this file, may be copied, modified,
// propagated, or distributed except according to the terms contained in the
// LICENSE.txt file.

namespace test\diff\formatting;

use easytest;

// Tests to see if diffs between various types are formatted correctly


// helper functions

function get_oid($object) {
    return \function_exists('spl_object_id')
        ? \spl_object_id($object)
        : \spl_object_hash($object);
}


// helper assertions

function assert_diff(&$from, &$to, $expected) {
    $expected = "- from\n+ to\n\n" . $expected;
    $actual = easytest\diff($from, $to, 'from', 'to');
    easytest\assert_identical($expected, $actual);
}


// tests

function test_formats_scalar() {
    $value = 1;
    $expected = '  1';
    namespace\assert_diff($value, $value, $expected);
}


function test_formats_single_line_string() {
    $value = "The water's sound";
    $expected = "  'The water\'s sound'";
    namespace\assert_diff($value, $value, $expected);
}


function test_formats_multi_line_string() {
    $value = <<<'VALUE'
An old pond
A frog jumps in
The water's sound
VALUE;
    $expected = <<<'EXPECTED'
  'An old pond
  A frog jumps in
  The water\'s sound'
EXPECTED;
    namespace\assert_diff($value, $value, $expected);
}


function test_formats_array() {
    $value = array(
        0,
        null,
        false,
        '',
        array(1, 2, 3),
        "An old pond\nA frog jumps in\nThe water's sound",
        (object)array('one' => 1, 'two' => 2, 'three' => 3),
    );

    $oid = namespace\get_oid($value[6]);
    $expected = <<<EXPECTED
  array(
      0 => 0,
      1 => NULL,
      2 => false,
      3 => '',
      4 => array(
          0 => 1,
          1 => 2,
          2 => 3,
      ),
      5 => 'An old pond
  A frog jumps in
  The water\\'s sound',
      6 => stdClass #{$oid} {
          \$one = 1;
          \$two = 2;
          \$three = 3;
      },
  )
EXPECTED;
    namespace\assert_diff($value, $value, $expected);
}


function test_formats_object() {
    $value = new \stdClass;
    $value->one = 0;
    $value->two = null;
    $value->three = false;
    $value->four = '';
    $value->five = array(1, 2, 3);
    $value->six = "An old pond\nA frog jumps in\nThe water's sound";
    $value->seven = (object)array('one' => 1, 'two' => 2, 'three' => 3);

    $oid1 = namespace\get_oid($value);
    $oid2 = namespace\get_oid($value->seven);
    $expected = <<<EXPECTED
  stdClass #{$oid1} {
      \$one = 0;
      \$two = NULL;
      \$three = false;
      \$four = '';
      \$five = array(
          0 => 1,
          1 => 2,
          2 => 3,
      );
      \$six = 'An old pond
  A frog jumps in
  The water\\'s sound';
      \$seven = stdClass #{$oid2} {
          \$one = 1;
          \$two = 2;
          \$three = 3;
      };
  }
EXPECTED;
    namespace\assert_diff($value, $value, $expected);
}


function test_formats_resource() {
    $value = \fopen(__FILE__, 'rb');
    $expected = '  ' . easytest\_format_resource($value);
    namespace\assert_diff($value, $value, $expected);
}


function test_diffs_different_scalar_values() {
    $a = null;
    $b = false;
    $actual = easytest\diff($a, $b, 'a', 'b');
    $expected = <<<'EXPECTED'
- a
+ b

- NULL
+ false
EXPECTED;
    easytest\assert_identical($expected, $actual);
}


function test_diffs_different_arrays() {
    $from = array(
        1,
        'foo',
        array(),
        array(2, 3),
        "An old pond\nA frog jumps in",
        (object)array('one' => 1, 'two' => 2, 'three' => 3),
    );
    $to = array(
        1,
        'bar',
        array(2, 3),
        2,
        array(1 => 3, 2 => 4, 3 => array(5, 6)),
        "An old pond\nA frog jumps in\nThe water's sound",
        (object)array('two' => 2, 'three' => 4),
    );
    $expected = <<<'EXPECTED'
  array(
      0 => 1,
-     1 => 'foo',
-     2 => array(),
+     1 => 'bar',
-     3 => array(
+     2 => array(
          0 => 2,
          1 => 3,
      ),
+     3 => 2,
+     4 => array(
+         1 => 3,
+         2 => 4,
+         3 => array(
+             0 => 5,
+             1 => 6,
+         ),
+     ),
-     4 => 'An old pond
+     5 => 'An old pond
  A frog jumps in
+ The water\'s sound',
-     5 => stdClass {
+     6 => stdClass {
-         $one = 1;
          $two = 2;
-         $three = 3;
+         $three = 4;
      },
  )
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_equal_objects() {
    $value = array('one' => 1, 'two' => 2, 'three' => 3);
    $from = (object)$value;
    $to = (object)$value;

    $oid1 = namespace\get_oid($from);
    $oid2 = namespace\get_oid($to);
    $expected = <<<'EXPECTED'
  stdClass {
      $one = 1;
      $two = 2;
      $three = 3;
  }
EXPECTED;

    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_different_objects() {
    $from = (object)array(
        'one' => 1,
        'two' => 'foo',
        'three' => array(),
        'four'=> array(2, 3),
        'five'=> "An old pond\nA frog jumps in",
        'six'=> (object)array('one' => 1, 'two' => 2, 'three' => 3),
    );
    $to = (object)array(
        'one' => 1,
        'two' => 'bar',
        'three' => array(2, 3),
        'four' => 2,
        'five' => array(1 => 3, 2 => 4, 3 => array(5, 6)),
        'six' => "An old pond\nA frog jumps in\nThe water's sound",
        'seven' => (object)array('two' => 2, 'three' => 4),
    );
    $expected = <<<'EXPECTED'
  stdClass {
      $one = 1;
-     $two = 'foo';
-     $three = array();
+     $two = 'bar';
-     $four = array(
+     $three = array(
          0 => 2,
          1 => 3,
      );
+     $four = 2;
+     $five = array(
+         1 => 3,
+         2 => 4,
+         3 => array(
+             0 => 5,
+             1 => 6,
+         ),
+     );
-     $five = 'An old pond
+     $six = 'An old pond
  A frog jumps in
+ The water\'s sound';
-     $six = stdClass {
+     $seven = stdClass {
-         $one = 1;
          $two = 2;
-         $three = 3;
+         $three = 4;
      };
  }
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_different_resources() {
    $from = \fopen(__FILE__, 'rb');
    $to = \fopen(__FILE__, 'rb');
    $expected = '- ' . easytest\_format_resource($from)
            . "\n+ " . easytest\_format_resource($to);

    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_single_line_string_to_multiline_string() {
    $from = 'A frog jumps in';
    $to = "An old pond\nA frog jumps in\nThe water's sound";
    $expected = <<<'EXPECTED'
+ 'An old pond
  A frog jumps in
+ The water\'s sound'
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_scalar_to_multiline_string() {
    $from = null;
    $to = "An old pond\nA frog jumps in\nThe water's sound";
    $expected = <<<'EXPECTED'
- NULL
+ 'An old pond
+ A frog jumps in
+ The water\'s sound'
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_multiline_string_to_scalar() {
    $from = "An old pond\nA frog jumps in\nThe water's sound";
    $to = null;
    $expected = <<<'EXPECTED'
- 'An old pond
- A frog jumps in
- The water\'s sound'
+ NULL
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_empty_array_to_array() {
    $from = array();
    $to = array(1, 2);
    $expected = <<<'EXPECTED'
  array(
+     0 => 1,
+     1 => 2,
  )
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_scalar_to_array() {
    $from = null;
    $to = array(1, 2, 3);
    $expected = <<<'EXPECTED'
- NULL
+ array(
+     0 => 1,
+     1 => 2,
+     2 => 3,
+ )
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_array_to_scalar() {
    $from = array(1, 2, 3);
    $to = null;
    $expected = <<<'EXPECTED'
- array(
-     0 => 1,
-     1 => 2,
-     2 => 3,
- )
+ NULL
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_scalar_to_object() {
    $from = null;
    $to = (object)array('one' => 1, 'two' => 2, 'three' => 3);
    $oid = namespace\get_oid($to);
    $expected = <<<EXPECTED
- NULL
+ stdClass #{$oid} {
+     \$one = 1;
+     \$two = 2;
+     \$three = 3;
+ }
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}


function test_diffs_object_to_scalar() {
    $from = (object)array('one' => 1, 'two' => 2, 'three' => 3);
    $to = null;
    $oid = namespace\get_oid($from);
    $expected = <<<EXPECTED
- stdClass #{$oid} {
-     \$one = 1;
-     \$two = 2;
-     \$three = 3;
- }
+ NULL
EXPECTED;
    namespace\assert_diff($from, $to, $expected);
}
