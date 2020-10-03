<?php

namespace ns01\ns1 {

class TestNamespaces {
    public function test() {
        \easytest\assert_true(true);
    }
}

/* ensure the namespace operator isn't confused for a namespace declaration */
const BAR = 'bar';
$bar = namespace\BAR;

}

namespace
    ns01 // parent namespace
    \   // namespace separator
    ns2 // sub namespace
{

class TestNamespaces {
    public function test() {
        \easytest\assert_true(true);
    }
}

const BAR = 'bar';
$bar = namespace\BAR;

}

namespace { // global namespace

class TestNamespaces {
    public function test() {
        \easytest\assert_true(true);
    }
}

const BAR = 'bar';
$bar = namespace\BAR;

}
