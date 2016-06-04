<?php

namespace AppBundle\Entity;

use AppBundle\Exception\GameException;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GameRepository")
 * @ORM\Table(name="game")
 */
class Game
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_STARTED = 1;
    const STATUS_CLEARED = 2;
    const STATUS_FAILED = 3;

    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startStamp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endStamp;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_NOT_STARTED;

    /**
     * @ORM\Column(type="array")
     */
    private $scheme;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->startStamp = new \DateTime();
        $this->endStamp = null;
        $this->status = self::STATUS_STARTED;
        $this->scheme = [];
    }

    /**
     * Get id
     *
     * @return guid
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get startStamp
     *
     * @return \DateTime
     */
    public function getStartStamp()
    {
        return $this->startStamp;
    }


    /**
     * Get endStamp
     *
     * @return \DateTime
     */
    public function getEndStamp()
    {
        return $this->endStamp;
    }


    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param array $scheme
     *
     * @return Game
     */
    public function setScheme(array $scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get scheme
     *
     * @return array
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param $status
     *
     * @return \DateInterval
     *
     * @throws GameException
     */
    public function end($status)
    {
        if (!$this->isStatusValid($status)) {
            throw new GameException("This status is not a valid one");
        }

        if (!$this->canBeEnded()) {
            throw new GameException(sprintf("Game cannot be ended. Current status: %s", $this->getStatus()));
        }

        $this->endStamp = new \DateTime();
        $this->status = $status;

        return $this->startStamp->diff($this->endStamp);
    }

    /**
     * @param $status
     *
     * @return bool
     */
    private function isStatusValid($status)
    {
        return in_array($status, $this->getAllAvailableStatus());
    }

    private function canBeEnded()
    {
        return $this->status == self::STATUS_STARTED;
    }

    /**
     * @return array
     */
    private function getAllAvailableStatus()
    {
        return [
            self::STATUS_NOT_STARTED,
            self::STATUS_STARTED,
            self::STATUS_CLEARED,
            self::STATUS_FAILED,
        ];
    }
}
