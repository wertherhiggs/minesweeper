<?php

namespace spec\AppBundle\Game;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BoxSpec extends ObjectBehavior
{
    const RANDOM_VALUE = 1;

    function let()
    {
        $this->beConstructedWith(1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\Box');
    }

    function it_check_if_it_is_not_a_mine()
    {
        $this->isMine()->shouldBeEqualTo(false);
    }

    function it_returns_unopen_state_after_construct()
    {
        $this->isOpen()->shouldBeEqualTo(false);
    }

    function it_returns_value_if_is_not_a_mine()
    {
        $this->getValue()->shouldBeEqualTo(self::RANDOM_VALUE);
    }

    function it_opens()
    {
        $this->open()->shouldBeInteger();
        $this->isOpen()->shouldBeEqualTo(true);
    }
}
