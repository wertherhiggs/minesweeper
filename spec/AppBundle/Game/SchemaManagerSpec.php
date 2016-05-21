<?php

namespace spec\AppBundle\Game;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SchemaManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\SchemaManager');
    }

    function it_creates_a_schema()
    {
        $this->createSchema(16, 30, 99)->shouldBeArray();
    }
}
