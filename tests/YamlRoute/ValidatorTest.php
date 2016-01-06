<?php

namespace makallio85\YamlRoute\Test;

use makallio85\YamlRoute\Validator;

/**
 * Class ValidatorTest
 *
 * @package makallio85\YamlRoute\Test
 */
class ValidatorTest extends YamlRouteTest
{
    /**
     * @expectedException \makallio85\YamlRoute\Exception\ValidatorException
     */
    public function testEmptyRoute()
    {
        Validator::run(['file' => 'foo.bar']);
    }

    /**
     * @expectedException \makallio85\YamlRoute\Exception\ValidatorException
     */
    public function testRoutePathMissing()
    {
        $Validator = new Validator();
        $this->_invokeMethod($Validator, '_checkRoute', ['foo', ['bar'], false]);
    }

    /**
     * @expectedException \makallio85\YamlRoute\Exception\ValidatorException
     */
    public function testActionPresentButControllerNot()
    {
        $Validator = new Validator();
        $this->_invokeMethod($Validator, '_checkRoute', ['foo', ['path' => 'bar', 'config' => ['action' => 'fizz']], false]);
    }

    /**
     * @expectedException \makallio85\YamlRoute\Exception\ValidatorException
     */
    public function testRouteConfigMissing()
    {
        $Validator = new Validator();
        $this->_invokeMethod($Validator, '_checkRoute', ['foo', ['path' => 'bar'], false]);
    }
}