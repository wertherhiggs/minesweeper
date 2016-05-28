<?php

namespace spec\AppBundle\Game;

use AppBundle\Exception\OpeningMineBoxException;
use AppBundle\Exception\SchemaManagerException;
use AppBundle\Game\Box;
use AppBundle\Game\BoxInterface;
use AppBundle\Game\MinedBox;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SchemaManagerSpec extends ObjectBehavior
{
    const DEFAULT_ROWS_NUMBER = 16;

    const DEFAULT_COLUMNS_NUMBER = 30;

    const DEFAULT_NUMBER_OF_MINES = 99;

    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Game\SchemaManager');
    }

    function it_creates_a_schema()
    {
        $schema = $this->createSchema(self::DEFAULT_ROWS_NUMBER, self::DEFAULT_COLUMNS_NUMBER, self::DEFAULT_NUMBER_OF_MINES);
        $schema->shouldBeArray();
        $schema->shouldHaveOnlyBoxes(self::DEFAULT_NUMBER_OF_MINES);
    }

    function it_throws_a_schema_manager_exception_if_open_a_non_existent_box()
    {
        $schema = $this->createSchema(self::DEFAULT_ROWS_NUMBER, self::DEFAULT_COLUMNS_NUMBER, self::DEFAULT_NUMBER_OF_MINES);
        $this->shouldThrow(SchemaManagerException::class)->duringOpenBox(-1, -1, $schema);
    }

    function it_throws_a_opening_mine_box_exception_if_open_a_mined_box()
    {
        $schema = $this->createSchema(self::DEFAULT_ROWS_NUMBER, self::DEFAULT_COLUMNS_NUMBER, self::DEFAULT_NUMBER_OF_MINES);
        list($rowIndex, $columnIndex) = $this->findBoxFromInstance(MinedBox::class, $schema->getWrappedObject());
        $this->shouldThrow(OpeningMineBoxException::class)->duringOpenBox($rowIndex, $columnIndex, $schema);
    }

    function it_opens_a_non_mined_box()
    {
        $schema = $this->createSchema(self::DEFAULT_ROWS_NUMBER, self::DEFAULT_COLUMNS_NUMBER, self::DEFAULT_NUMBER_OF_MINES);
        list($rowIndex, $columnIndex) = $this->findBoxFromInstance(Box::class, $schema->getWrappedObject());
        $this->openBox($rowIndex, $columnIndex, $schema)->shouldBeArray();
    }

    public function getMatchers()
    {
        return [
            'haveOnlyBoxes' => [$this, 'haveOnlyBoxes'],
        ];
    }

    /**
     * @param array $schema
     * @param integer $numberOfMines
     *
     * @return bool
     *
     * @throws FailureException
     */
    public function haveOnlyBoxes(array $schema, $numberOfMines)
    {
        $originalNumberOfMines = $numberOfMines;

        foreach ($schema as $row => $rows) {
            foreach ($rows as $column => $box) {
                if (!$box instanceof BoxInterface) {
                    throw new FailureException("Result array should contains only BoxInterfaces");
                }

                if ($box->isMine()) {
                    $numberOfMines--;
                } else {
                    $this->checkBoxValue($box, $schema, $row, $column);
                }
            }
        }

        if ($numberOfMines != 0) {
            throw new FailureException(sprintf(
                "Number of mines created is not exact. Expected %s, created %s",
                $originalNumberOfMines,
                $originalNumberOfMines-$numberOfMines
            ));
        }

        return true;
    }

    /**
     * @param BoxInterface $box
     * @param array $schema
     * @param integer $row
     * @param integer $column
     *
     * @throws FailureException
     */
    private function checkBoxValue(BoxInterface $box, array $schema, $row, $column)
    {
        $boxValue = $box->getValue();
        $mines = 0;

        for ($rowCycleIndex = -1; $rowCycleIndex <= 1; $rowCycleIndex++) {
            for ($columnCycleIndex = -1; $columnCycleIndex <= 1; $columnCycleIndex++) {
                if ($rowCycleIndex == 0 && $columnCycleIndex == 0) {
                    continue;
                }

                if (!isset($schema[$row+$rowCycleIndex][$column+$columnCycleIndex])) {
                    continue;
                }

                if (!$schema[$row+$rowCycleIndex][$column+$columnCycleIndex] instanceof MinedBox) {
                    continue;
                }

                $mines++;
            }
        }

        if ($boxValue != $mines) {
            throw new FailureException(sprintf(
                "Schema not created correctly at row %s column %s . Expected value of %s, got %s",
                $row,
                $column,
                $mines,
                $boxValue
            ));
        }
    }

    /**
     * @param string $boxFQCN
     * @param array $schema
     *
     * @return BoxInterface
     */
    private function findBoxFromInstance($boxFQCN, array $schema)
    {
        foreach ($schema as $rowIndex => $row) {
            foreach ($row as $columnIndex => $box) {
                if ($box instanceof $boxFQCN) {
                    return [$rowIndex, $columnIndex];
                }
            }
        }
    }
}
