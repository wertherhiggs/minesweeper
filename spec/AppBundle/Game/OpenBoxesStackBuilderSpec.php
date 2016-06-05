<?php

namespace spec\AppBundle\Game;

use AppBundle\Exception\OpenBoxesStackBuilderException;
use AppBundle\Game\Box;
use AppBundle\Game\MinedBox;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OpenBoxesStackBuilderSpec extends ObjectBehavior
{
    function let()
    {
        $schemeDouble = $this->getSchemeDouble();
        $this->beConstructedWith($schemeDouble);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\OpenBoxesStackBuilder');
    }

    function it_returns_a_schema()
    {
        $this->getScheme()->shouldBeArray();
    }

    function it_stacks_up_boxes()
    {
        $schemeDouble = $this->getSchemeDouble();
        $this->addBox(0, 0, $schemeDouble[0][0]);
        $this->addBox(1, 1, $schemeDouble[1][1]);

        $stackedBoxes = $this->getStackedBoxes();
        $stackedBoxes->shouldBeArray();
        $stackedBoxes->shouldHaveCount(2);
    }

    function it_updates_scheme()
    {
        $schemeDouble = $this->getSchemeDouble();
        $box = $schemeDouble[0][0];
        $box->open();
        $this->addBox(0, 0, $box);

        $updatedSchemeDouble = $this->getScheme();
        $updatedSchemeDouble[0][0]->isOpen()->shouldBeEqualTo(true);
    }

    function it_throws_an_exception_if_add_an_out_of_range_box()
    {
        $this->shouldThrow(OpenBoxesStackBuilderException::class)->duringAddBox(-1, -1, new Box(0));
    }

    /**
     * @return array
     */
    private function getSchemeDouble()
    {
        return [
            [new Box(0), new Box(0), new Box(1), new Box(1), new Box(1), new Box(0), new Box(0), new Box(0)],
            [new Box(0), new Box(0), new Box(1), new MinedBox(), new Box(2), new Box(1), new Box(1), new Box(0)],
            [new Box(1), new Box(1), new Box(3), new Box(3), new Box(4), new MinedBox(), new Box(1), new Box(0)],
            [new Box(1), new MinedBox(), new Box(2), new MinedBox(), new MinedBox(), new Box(3), new Box(2), new Box(0)],
            [new Box(1), new Box(1), new Box(3), new Box(3), new Box(4), new MinedBox(), new Box(1), new Box(0)],
            [new Box(1), new Box(1), new Box(1), new MinedBox(), new Box(2), new Box(1), new Box(2), new Box(1)],
            [new MinedBox(), new Box(1), new Box(1), new Box(1), new Box(1), new Box(0), new Box(1), new MinedBox()],
            [new MinedBox(), new Box(2), new Box(0), new Box(0), new Box(0), new Box(0), new Box(1), new Box(1)],
        ];
    }
}
