<?php
// This file is part of EasyTest. It is subject to the license terms in the
// LICENSE.txt file found in the top-level directory of this distribution.
// No part of this project, including this file, may be copied, modified,
// propagated, or distributed except according to the terms contained in the
// LICENSE.txt file.

namespace easytest;


//
// Interface
//

interface Target
{
    public function name();
    public function subtargets();
}



/**
 * @param string[] $args
 * @param ?string $error
 * @return array{string, Target[]}|array{null, null}
 */
function process_user_targets(array $args, &$error)
{
    if (!$args)
    {
        $cwd = \getcwd();
        // @todo what to get if getcwd fails?
        \assert($cwd !== false);
        $args[] = $cwd;
    }
    $args = new _TargetArgs($args);
    $targets = array();
    $root = $error = null;
    namespace\_parse_targets($args, $targets, $root, $error);

    if ($error)
    {
        return array(null, null);
    }

    $keys = \array_keys($targets);
    \sort($keys, \SORT_STRING);
    $key = \current($keys);
    while ($key !== false)
    {
        if (\is_dir($key))
        {
            $keylen = \strlen($key);
            $next = \next($keys);
            while (
                $next !== false
                && 0 === \substr_compare($next, $key, 0, $keylen))
            {
                unset($targets[$next]);
                $next = \next($keys);
            }
            $key = $next;
        }
        else
        {
            $key = \next($keys);
        }
    }
    return array($root, $targets);
}


//
// Implementation
//

//
// Test target specifiers are parsed per the following grammar:
//
// targets: (path-target)*
//
// path-target:
//    dir-name
//  | file-name (function-target|class-target)*
//
//
//  function-target: '--function=' function-list
//
//  function-list: function-name (',' function-name)*
//
//  function-name: ns-name? ID
//
//
//  class-target: '--class=' class-list
//
//  class-list: class-and-methods (';' class-and-methods)*
//
//  class-and-methods: class-name ('::' method-list)?
//
//  class-name: ns-name? ID
//
//
//  method-list: method-name (',' method-name)
//
//  method-name: ID
//
//
//  ns-name : ID '\' (ID '\')*
//


const _TARGET_CLASS    = '--class=';
const _TARGET_FUNCTION = '--function=';
const _TARGET_IDENTIFIER = '~
    \\G                         # anchor to start of offset
    [a-zA-Z_\\x80-\\xff]        # first character of identifier (no digits)
    [a-zA-Z0-9_\\x80-\\xff]*    # additional identifier characters
    ~x';
const _TARGET_NAMESPACE = '~
    \\G                             # anchor to start of offset
    (?:                             # non-capturing subpattern
        [a-zA-Z_\\x80-\\xff]        # first character of namespace (no digits)
        [a-zA-Z0-9_\\x80-\\xff]*    # additional namespace characters
        \\\\                        # namespace separator
    )+                              # subpattern can occur multiple times
    ~x';


final class _Target extends struct implements Target
{
    public $name;
    public $subtargets = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function subtargets()
    {
        return $this->subtargets;
    }
}


final class _TargetArgs extends struct
{
    /**
     * @param string[] $args
     */
    public function __construct(array $args)
    {
        \assert((bool)$args);

        $this->args = $args;
        $this->arg_count = \count($args);
        $this->arg_pos = 0;

        $this->arg = $this->args[$this->arg_pos];
        $this->len = \strlen($this->arg);
        $this->pos = 0;
    }

    /**
     * @return bool
     */
    public function has_arg()
    {
        return $this->arg_pos < $this->arg_count;
    }

    /**
     * @return string
     */
    public function eat_arg()
    {
        $arg = $this->args[$this->arg_pos++];

        if ($this->arg_pos < $this->arg_count)
        {
            $this->arg = $this->args[$this->arg_pos];
            $this->len = \strlen($this->arg);
        }
        else
        {
            $this->arg = null;
            $this->len = 0;
        }
        $this->pos = 0;

        return $arg;
    }

    /**
     * @return string
     */
    public function peek_arg()
    {
        \assert(isset($this->arg));
        return $this->arg;
    }

    /**
     * @return bool
     */
    public function has_char()
    {
        \assert(isset($this->arg));
        return $this->pos < $this->len;
    }

    /**
     * @param string $chars
     * @return string
     */
    public function eat_chars($chars)
    {
        return $this->match_chars($chars, true);
    }

    /**
     * @param string $chars
     * @return string
     */
    public function peek_chars($chars)
    {
        return $this->match_chars($chars, false);
    }

    /**
     * @param string $pattern
     * @return string
     */
    public function eat_pattern($pattern)
    {
        \assert(isset($this->arg));
        if ($this->pos >= $this->len)
        {
            $result = '';
        }
        elseif (\preg_match($pattern, $this->arg, $matches, 0, $this->pos))
        {
            $result = $matches[0];
            $this->pos += \strlen($result);
        }
        else
        {
            $result = '';
        }
        return $result;
    }


    /**
     * @param string $chars
     * @param bool $advance
     * @return string
     */
    private function match_chars($chars, $advance)
    {
        \assert(isset($this->arg));
        $len = \strlen($chars);
        if ($this->pos >= $this->len)
        {
            $result = '';
        }
        elseif (0 === \substr_compare($this->arg, $chars, $this->pos, $len))
        {
            if ($advance)
            {
                $this->pos += $len;
            }
            $result = $chars;
        }
        else
        {
            $result = '';
        }
        return $result;
    }

    /** @var string[] */
    private $args;
    /** @var int */
    private $arg_count;
    /** @var int */
    private $arg_pos;

    /** @var ?string */
    private $arg;
    /** @var int */
    private $len;
    /** @var int */
    private $pos;
}



/**
 * @param array<string, _Target> $targets
 * @param ?string $root
 * @param ?string $error
 * @return void
 */
function _parse_targets(_TargetArgs $args, array &$targets, &$root, &$error)
{
    while ($args->has_arg() && !$error)
    {
        namespace\_parse_path_target($args, $targets, $root, $error);
    }
}


/**
 * @param array<string, _Target> $targets
 * @param ?string $root
 * @param ?string $error
 * @return void
 */
function _parse_path_target(_TargetArgs $args, array &$targets, &$root, &$error)
{
    $path = $args->eat_arg();
    $realpath = \realpath($path);
    if (!$realpath)
    {
        $error = \sprintf('Argument \'%s\' must be a valid file path', $path);
        return;
    }

    if (\is_dir($realpath))
    {
        $realpath .= \DIRECTORY_SEPARATOR;
    }

    // The test root directory is not itself a test directory, so it shouldn't
    // have any arguments specified for it
    if (!isset($root))
    {
        $root = namespace\_determine_test_root($realpath);
        if ($root === $realpath)
        {
            return;
        }
    }
    elseif ($root === $realpath)
    {
        return;
    }
    elseif (0 !== \substr_compare($realpath, $root, 0, \strlen($root)))
    {
        $error = \sprintf(
            'File path \'%s\' is outside the test root directory \'%s\'',
            $path, $root);
        return;
    }

    if (isset($targets[$realpath]))
    {
        $target = $targets[$realpath];
        $duplicates = !$target->subtargets;
    }
    else
    {
        // @todo ensure path is a valid test path
        $target = new _Target($realpath);
        $targets[$realpath] = $target;
        $duplicates = false;
    }

    if (\is_file($realpath))
    {
        $no_subtargets = true;
        while ($args->has_arg() && !$error)
        {
            $args->peek_arg();
            if ($args->peek_chars(namespace\_TARGET_CLASS))
            {
                $no_subtargets = false;
                namespace\_parse_class_target($args, $target->subtargets, $error);
            }
            elseif ($args->peek_chars(namespace\_TARGET_FUNCTION))
            {
                $no_subtargets = false;
                namespace\_parse_function_target($args, $target->subtargets, $error);
            }
            else
            {
                break;
            }
        }
        if ($duplicates || $no_subtargets)
        {
            $target->subtargets = array();
        }
    }
}


function _determine_test_root($path)
{
    // The test root directory is the first directory above $path whose
    // case-insensitive name does not begin with 'test'. If $path is a
    // directory, this could be $path itself. This is done to ensure that
    // directory fixtures are properly discovered when testing individual
    // subpaths within a test suite; discovery will begin at the root directory
    // and descend towards the specified path.
    if (\is_dir($path))
    {
        $path = \rtrim($path, \DIRECTORY_SEPARATOR);
    }
    else
    {
        $path = \dirname($path);
    }

    while (0 === \substr_compare(\basename($path), 'test', 0, 4, true))
    {
        $path = \dirname($path);
    }
    return $path . \DIRECTORY_SEPARATOR;
}


/**
 * @param array<string, _Target> $targets
 * @param ?string $error
 * @return void
 */
function _parse_class_target(_TargetArgs $args, array &$targets, &$error)
{
    $args->eat_chars(namespace\_TARGET_CLASS);

    do
    {
        namespace\_parse_class_and_methods($args, $targets, $error);
        if ($error)
        {
            return;
        }
    } while ($args->eat_chars(';'));

    if ($args->has_char())
    {
        $error = \sprintf(
            'Class target \'%s\' has one or more invalid class or method names',
            $args->peek_arg());
        return;
    }
    $args->eat_arg();
}


/**
 * @param array<string, _Target> $targets
 * @param ?string $error
 * @return void
 */
function _parse_class_and_methods(_TargetArgs $args, array &$targets, &$error)
{
    $class = namespace\_parse_class_name($args, $error);
    if ($error)
    {
        return;
    }

    if (isset($targets[$class]))
    {
        $target = $targets[$class];
        $process_methods = (bool)$target->subtargets;
    }
    else
    {
        $target = new _Target($class);
        $targets[$class] = $target;
        $process_methods = true;
    }

    if ($args->eat_chars('::'))
    {
        do
        {
            $method = namespace\_parse_method_name($args, $error);
            if ($error)
            {
                return;
            }
            if ($process_methods && !isset($targets[$method]))
            {
                $target->subtargets[$method] = new _Target($method);
            }
        } while ($args->eat_chars(','));
    }
    else
    {
        $target->subtargets = array();
    }
}


/**
 * @param ?string $error
 * @return ?string
 */
function _parse_class_name(_TargetArgs $args, &$error)
{
    $ns = $args->eat_pattern(namespace\_TARGET_NAMESPACE);
    $class = $args->eat_pattern(namespace\_TARGET_IDENTIFIER);

    if (!$class)
    {
        $error = \sprintf(
            'Class target \'%s\' is missing one or more class names',
            $args->peek_arg());
        return null;
    }

    // @todo validate that class name is a valid test class
    // functions and classes with identical names can coexist!
    // @todo compress this into a common validation function
    // discovery also does this to disambiguate class and function names
    return "class {$ns}{$class}";
}


/**
 * @param ?string $error
 * @return ?string
 */
function _parse_method_name(_TargetArgs $args, &$error)
{
    $method = $args->eat_pattern(namespace\_TARGET_IDENTIFIER);

    if (!$method)
    {
        $error = \sprintf(
            'Class target \'%s\' is missing one or more method names',
            $args->peek_arg());
        return null;
    }

    // @todo validate that method name is a valid test method
    // @todo pass the method name through a common validator/normalizer?
    // Even though we don't need to normalize the method name (as compared to
    // function and class names), we may still want to for consistency and for
    // future-proofing against new requirements
    return $method;
}


/**
 * @param array<string, _Target> $targets
 * @param ?string $error
 * @return void
 */
function _parse_function_target(_TargetArgs $args, array &$targets, &$error)
{
    $args->eat_chars(namespace\_TARGET_FUNCTION);

    do
    {
        $function = namespace\_parse_function_name($args, $error);
        if ($error)
        {
            return;
        }
        if (!isset($targets[$function]))
        {
            $targets[$function] = new _Target($function);
        }
    } while ($args->eat_chars(','));

    if ($args->has_char())
    {
        $error = \sprintf(
            'Function target \'%s\' has one or more invalid function names',
            $args->peek_arg());
        return;
    }
    $args->eat_arg();
}


/**
 * @param ?string $error
 * @return ?string
 */
function _parse_function_name(_TargetArgs $args, &$error)
{
    $ns = $args->eat_pattern(namespace\_TARGET_NAMESPACE);
    $function = $args->eat_pattern(namespace\_TARGET_IDENTIFIER);

    if (!$function)
    {
        $error = \sprintf(
            'Function target \'%s\' is missing one or more function names',
            $args->peek_arg());
        return null;
    }

    // @todo validate that function name is a valid test function
    // functions and classes with identical names can coexist!
    // @todo compress this into a separate function
    // discovery also does this to disambiguate class and function names
    return "function {$ns}{$function}";
}


function find_directory_targets(Logger $logger, DirectoryTest $test, array $targets) {
    $error = false;
    $result = array();
    $current = null;
    $testnamelen = \strlen($test->name);
    foreach ($targets as $target) {
        if ($target->name === $test->name) {
            \assert(!$result);
            \assert(!$error);
            break;
        }

        $i = \strpos($target->name, \DIRECTORY_SEPARATOR, $testnamelen);
        if (false === $i) {
            $childpath = $target->name;
        }
        else {
            $childpath = \substr($target->name, 0, $i + 1);
        }

        if (!isset($test->tests[$childpath])) {
            $error = true;
            $logger->log_error(
                $target->name,
                'This path is not a valid test ' . (\is_dir($target->name) ? 'directory' : 'file')
            );
        }
        elseif ($childpath === $target->name) {
            $result[] = $target;
            $current = null;
        }
        else {
            if (!isset($current) || $current->name !== $childpath) {
                $current = new _Target($childpath);
                $result[] = $current;
            }
            $current->subtargets[] = $target;
        }
    }
    return array($error, $result);
}


function find_file_targets(Logger $logger, FileTest $test, array $targets) {
    $error = false;
    $result = array();
    foreach ($targets as $target) {
        if (isset($test->tests[$target->name])) {
            $result[] = $target;
        }
        else {
            $error = true;
            $logger->log_error(
                $target->name,
                "This identifier is not a valid test in {$test->name}"
            );
        }
    }
    return array($error, $result);
}


function find_class_targets(Logger $logger, ClassTest $test, array $targets) {
    $error = false;
    $result = array();
    foreach ($targets as $target) {
        if (\method_exists($test->name, $target->name)) {
            $result[] = $target->name;
        }
        else {
            $error = true;
            $logger->log_error(
                $target->name,
                "This identifier is not a valid test method in class {$test->name}"
            );
        }
    }
    return array($error, $result);
}


function build_targets_from_dependencies(array $dependencies) {
    $targets = array();
    $current_file = $current_class = null;
    foreach ($dependencies as $dependency) {
        if (!isset($current_file) || $current_file->name !== $dependency->file) {
            $current_class = null;
            $current_file = new _Target($dependency->file);
            $targets[] = $current_file;
        }
        if ($dependency->class) {
            $class = "class {$dependency->class}";
            if (!isset($current_class) || $current_class->name !== $class) {
                $current_class = new _Target($class);
                $current_file->subtargets[] = $current_class;
            }
            $current_class->subtargets[] = new _Target($dependency->function);
        }
        else {
            $current_class = null;
            $function = "function {$dependency->function}";
            $current_file->subtargets[] = new _Target($function);
        }
    }
    return $targets;
}
