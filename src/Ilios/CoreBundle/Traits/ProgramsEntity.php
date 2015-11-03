<?php

namespace Ilios\CoreBundle\Traits;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Ilios\CoreBundle\Entity\ProgramInterface;

/**
 * Class ProgramsEntity
 * @package Ilios\CoreBundle\Traits
 */
trait ProgramsEntity
{
    /**
     * @param Collection $programs
     */
    public function setPrograms(Collection $programs)
    {
        $this->programs = new ArrayCollection();

        foreach ($programs as $program) {
            $this->addProgram($program);
        }
    }

    /**
     * @param ProgramInterface $program
     */
    public function addProgram(ProgramInterface $program)
    {
        $this->programs->add($program);
    }

    /**
    * @return ProgramInterface[]|ArrayCollection
    */
    public function getPrograms()
    {
        return $this->programs;
    }
}
