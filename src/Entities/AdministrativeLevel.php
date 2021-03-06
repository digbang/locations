<?php
namespace Digbang\Locations\Entities;


use Digbang\Locations\Util\Identity;

class AdministrativeLevel
{
    use Identity;

    /** @var int */
    private $level;

    /** @var string */
    private $name;

    /** @var string */
    private $code;

    /**
     * @param int    $level
     * @param string $name
     * @param string $code
     */
    public function __construct(int $level, string $name, string $code)
    {
        $this->level = $level;
        $this->name = $name;
        $this->code = $code;

        $this->initializeId();
    }

    /**
     * Returns the administrative level
     * @return int Level number [1,5]
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Returns the administrative level name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the administrative level short name.
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns a string with the administrative level name.
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
