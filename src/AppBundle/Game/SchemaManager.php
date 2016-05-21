<?php

namespace AppBundle\Game;

class SchemaManager
{
    /**
     * Will keep a mines schema populated after createMinesSchema() call.
     *
     * @var array
     */
    private $mines = [];

    /**
     * @param integer $rows
     * @param integer $columns
     * @param integer $minesNumber
     *
     * @return array
     */
    public function createSchema($rows, $columns, $minesNumber)
    {
        $this->createMinesSchema($rows, $columns, $minesNumber);

        $schema = [];
        for ($rowNumber = 0; $rowNumber < $rows; $rowNumber++) {
            for ($columnNumber = 0; $columnNumber < $columns; $columnNumber++) {
                $mine = $this->getBoxValue($rowNumber, $columnNumber);
                $schema[$rowNumber][$columnNumber] = $mine;
            }
        }

        return $schema;
    }

    /**
     * @param integer $rowsNumber
     * @param integer $columnsNumber
     * @param integer $minesNumber
     *
     * @return bool
     */
    protected function createMinesSchema($rowsNumber, $columnsNumber, $minesNumber)
    {
        $mines = [];
        while ($minesNumber) {
            $row = rand(0, $rowsNumber-1);
            $column = rand(0, $columnsNumber-1);

            if (!isset($mines[$row][$column])) {
                $mines[$row][$column] = true;
                $minesNumber--;
            }
        }

        $this->mines = $mines;

        return true;
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return int|string
     */
    protected function getBoxValue($rowNumber, $columnNumber)
    {
        // @todo: throw an exception if schema is not initialized?

        if ($this->checkIfGotMine($rowNumber, $columnNumber)) {
            $mine = 'X';
        } else {
            $mine = 0;
            if ($this->checktTopLeftBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkAboveBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkTopRightBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkLeftBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkRightBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkBottomLeftBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkBelowBox($rowNumber, $columnNumber)) {
                $mine++;
            }
            if ($this->checkBottomRightBox($rowNumber, $columnNumber)) {
                $mine++;
            }
        }

        return $mine;
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkIfGotMine($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber][$columnNumber]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checktTopLeftBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber-1][$columnNumber-1]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkAboveBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber-1][$columnNumber]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkTopRightBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber-1][$columnNumber+1]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkLeftBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber][$columnNumber-1]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkRightBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber][$columnNumber+1]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkBottomLeftBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber+1][$columnNumber-1]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkBelowBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber+1][$columnNumber]);
    }

    /**
     * @param integer $rowNumber
     * @param integer $columnNumber
     *
     * @return bool
     */
    protected function checkBottomRightBox($rowNumber, $columnNumber)
    {
        return isset($this->mines[$rowNumber+1][$columnNumber+1]);
    }
}
