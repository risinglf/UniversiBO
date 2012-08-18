<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit                                                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2003 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestFailure.php,v 1.1.2.2 2008-01-23 09:37:33 evaimitico Exp $
//

/**
 * A TestFailure collects a failed test together with the caught exception.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit
 */
class PHPUnit_TestFailure
{
    /**
    * @var    object
    * @access private
    */
    public $_failedTest;

    /**
    * @var    string
    * @access private
    */
    public $_thrownException;

    /**
    * Constructs a TestFailure with the given test and exception.
    *
    * @param  object
    * @param  string
    * @access public
    */
    public function PHPUnit_TestFailure(&$failedTest, &$thrownException)
    {
        $this->_failedTest      = $failedTest;
        $this->_thrownException = $thrownException;
    }

    /**
    * Gets the failed test.
    *
    * @return object
    * @access public
    */
    function &failedTest() {
        return $this->_failedTest;
    }

    /**
    * Gets the thrown exception.
    *
    * @return object
    * @access public
    */
    function &thrownException() {
        return $this->_thrownException;
    }

    /**
    * Returns a short description of the failure.
    *
    * @return string
    * @access public
    */
    public function toString()
    {
        return sprintf(
          "TestCase %s->%s() failed: %s\n",

          get_class($this->_failedTest),
          $this->_failedTest->getName(),
          $this->_thrownException
        );
    }
}