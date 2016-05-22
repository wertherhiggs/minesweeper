<?php

namespace spec\AppBundle\Game;

use AppBundle\Exception\OpeningMineBoxException;
use AppBundle\Game\Box;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BoxSpec extends ObjectBehavior
{
    const RANDOM_VALUE = 1;

    function let()
    {
        $this->beConstructedWith(false, 1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\Box');
    }

    function it_checks_if_it_is_a_mine()
    {
        $this->beConstructedWith(true, Box::MINED_BOX_VALUE);
        $this->isMine()->shouldBeEqualTo(true);
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

    function it_returns_a_non_valid_value_if_is_a_mine()
    {
        $this->beConstructedWith(true, Box::MINED_BOX_VALUE);
        $this->getValue()->shouldBeEqualTo(BOX::MINED_BOX_VALUE);
    }

    function it_opens_a_non_mine()
    {
        $this->open();
        $this->isOpen()->shouldBeEqualTo(true);
    }

    function it_throws_an_exception_if_try_to_open_a_mine()
    {
        $this->beConstructedWith(true, Box::MINED_BOX_VALUE);
        $this->shouldThrow(OpeningMineBoxException::class)->duringOpen();
    }
}
