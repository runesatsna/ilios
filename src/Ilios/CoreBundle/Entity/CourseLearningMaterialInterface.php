<?php

namespace Ilios\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Ilios\CoreBundle\Traits\IdentifiableEntityInterface;
use Ilios\CoreBundle\Traits\MeshDescriptorsEntityInterface;
use Ilios\CoreBundle\Traits\SortableEntityInterface;

/**
 * Interface CourseLearningMaterialInterface
 */
interface CourseLearningMaterialInterface extends
    IdentifiableEntityInterface,
    LoggableEntityInterface,
    MeshDescriptorsEntityInterface,
    SortableEntityInterface
{
    /**
     * @param string $notes
     */
    public function setNotes($notes);

    /**
     * @return string
     */
    public function getNotes();

    /**
     * @param boolean $required
     */
    public function setRequired($required);

    /**
     * @return boolean
     */
    public function isRequired();

    /**
     * @param boolean $publicNotes
     */
    public function setPublicNotes($publicNotes);

    /**
     * @return boolean
     */
    public function hasPublicNotes();

    /**
     * @param CourseInterface $course
     */
    public function setCourse(CourseInterface $course);

    /**
     * @return CourseInterface|null
     */
    public function getCourse();

    /**
     * @param LearningMaterialInterface $learningMaterial
     */
    public function setLearningMaterial(LearningMaterialInterface $learningMaterial);

    /**
     * @return LearningMaterialInterface
     */
    public function getLearningMaterial();

    /**
     * @return \DateTime|null
     */
    public function getStartDate();

    /**
     * @param \DateTime|null $startDate
     */
    public function setStartDate(\DateTime $startDate = null);

    /**
     * @return \DateTime|null
     */
    public function getEndDate();

    /**
     * @param \DateTime|null $endDate
     */
    public function setEndDate(\DateTime $endDate = null);
}
