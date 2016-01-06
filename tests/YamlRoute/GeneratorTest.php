<?php

namespace makallio85\YamlRoute\Test;

use makallio85\YamlRoute\Generator;

/**
 * Class GeneratorTest
 *
 * @package makallio85\YamlRoute\Test
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \makallio85\YamlRoute\Exception\GeneratorException
     */
    public function testProjectWrongFilePath()
    {
        Generator::getInstance()->run(true);
    }
}