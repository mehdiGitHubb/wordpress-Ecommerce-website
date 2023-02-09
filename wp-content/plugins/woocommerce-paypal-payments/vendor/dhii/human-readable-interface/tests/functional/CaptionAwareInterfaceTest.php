<?php

namespace Dhii\Util\String\FuncTest;

use Dhii\Util\String\CaptionAwareInterface as Subject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CaptionAwareInterfaceTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @return Subject|MockObject
     */
    public function createInstance()
    {
        $mock = $this->getMockBuilder(Subject::class)
                     ->getMockForAbstractClass();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInstanceOf(
            Subject::class, $subject,
            'Subject is not a valid instance'
        );
    }
}
