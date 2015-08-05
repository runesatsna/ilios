<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\Manager\CourseManagerInterface;
use Ilios\CoreBundle\Entity\Manager\PermissionManagerInterface;
use Ilios\CoreBundle\Entity\Manager\ProgramManagerInterface;
use Ilios\CoreBundle\Entity\Manager\ProgramYearManagerInterface;
use Ilios\CoreBundle\Entity\Manager\ProgramYearStewardManagerInterface;
use Ilios\CoreBundle\Entity\Manager\SessionManagerInterface;
use Ilios\CoreBundle\Entity\PublishEventInterface;
use Ilios\CoreBundle\Entity\UserInterface;


/**
 * Class PublishEventVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class PublishEventVoter extends AbstractVoter
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var ProgramManagerInterface
     */
    protected $programManager;

    /**
     * @var ProgramYearManagerInterface
     */
    protected $programYearManager;

    /**
     * @var CourseManagerInterface
     */
    protected $courseManager;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var ProgramYearStewardManagerInterface
     */
    protected $stewardManager;

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return array(self::CREATE, self::VIEW, self::DELETE);
    }

    /**
     * @param PermissionManagerInterface $permissionManager
     * @param ProgramManagerInterface $programManager
     * @param ProgramYearManagerInterface $programYearManager
     * @param CourseManagerInterface $courseManager
     * @param SessionManagerInterface $sessionManager
     * @param ProgramYearStewardManagerInterface $stewardManager
     */
    public function __construct(
        PermissionManagerInterface $permissionManager,
        ProgramManagerInterface $programManager,
        ProgramYearManagerInterface $programYearManager,
        CourseManagerInterface $courseManager,
        SessionManagerInterface $sessionManager,
        ProgramYearStewardManagerInterface $stewardManager
    ) {
        $this->permissionManager = $permissionManager;
        $this->programManager = $programManager;
        $this->programYearManager = $programYearManager;
        $this->courseManager = $courseManager;
        $this->sessionManager = $sessionManager;
        $this->stewardManager = $stewardManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Ilios\CoreBundle\Entity\PublishEventInterface');
    }

    /**
     * @param string $attribute
     * @param PublishEventInterface $event
     * @param UserInterface|null $user
     * @return bool
     */
    protected function isGranted($attribute, $event, $user = null)
    {
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // For the sake of keeping it fast and simple,
                // any authenticated user can see all publish events.
                return true;
                break;
            case self::CREATE:
            case self::DELETE:
                // here we go again...
                // There are four types of entities right now that can be
                // published (publish-event is created) or unpublished (publish event is deleted).
                // These are
                // a) program
                // b) program year
                // c) course
                // d) session
                // All of which provide their own context and rules for granting perms to the given publish event.
                // Identify the type of publish event and then grant access based on that.
                switch($event->getTableName()) {
                    case 'program':
                        return $this->isGrantedCreateOrDeleteForProgramPublishEvent($event, $user);
                        break;
                    case 'program_year':
                        return $this->isGrantedCreateOrDeleteForProgramYearPublishEvent($event, $user);
                        break;
                    case 'course':
                        return $this->isGrantedCreateOrDeleteForCoursePublishEvent($event, $user);
                        break;
                    case 'session':
                        return $this->isGrantedCreateOrDeleteForSessionPublishEvent($event, $user);
                        break;
                }
                break;
        }

        return false;
    }

    /**
     * @param PublishEventInterface $event
     * @param UserInterface $user
     * @return bool
     * @see ProgramVoter::isGranted()
     */
    protected function isGrantedCreateOrDeleteForProgramPublishEvent($event, $user)
    {
        $program = $this->programManager->findProgramBy(['id' => $event->getTableRowId()]);
        if (empty($program)) {
            return false; // ¯\_(ツ)_/¯
        }

        // copied and pasted straight out of ProgramVoter::isGranted().
        // TODO: consolidate [ST 2015/08/05]
        return (
            ($this->userHasRole($user, ['Course Director', 'Developer'])
                && ($program->getOwningSchool()->getId() === $user->getPrimarySchool()->getId()
                    || $this->permissionManager->userHasWritePermissionToSchool(
                        $user,
                        $program->getOwningSchool()
                    )
                )
            )
            || $this->permissionManager->userHasWritePermissionToProgram($user, $program)
        );
    }

    /**
     * @param PublishEventInterface $event
     * @param UserInterface $user
     * @return bool
     *
     * @see ProgramYearVoter::isGranted()
     */
    protected function isGrantedCreateOrDeleteForProgramYearPublishEvent($event, $user)
    {
        $programYear = $this->programYearManager->findProgramYearBy(['id' => $event->getTableRowId()]);

        if (empty($programYear)) {
            return false;
        }

        // copied and pasted straight out of ProgramYearVoter::isGranted().
        // TODO: consolidate [ST 2015/08/05]
        return (
            ($this->userHasRole($user, ['Course Director', 'Developer'])
                && ($programYear->getProgram()->getOwningSchool()->getId()
                    === $user->getPrimarySchool()->getId()
                    || $this->permissionManager->userHasWritePermissionToSchool(
                        $user,
                        $programYear->getProgram()->getOwningSchool()
                    )
                    || $this->stewardManager->schoolIsStewardingProgramYear(
                        $user->getPrimarySchool(),
                        $programYear
                    )
                )
            )
            || $this->permissionManager->userHasWritePermissionToProgram($user, $programYear->getProgram())
        );
    }

    /**
     * @param PublishEventInterface $event
     * @param UserInterface $user
     * @return bool
     *
     * @see CourseVoter::isGranted()
     */
    protected function isGrantedCreateOrDeleteForCoursePublishEvent($event, $user)
    {
        $course = $this->courseManager->findCourseBy(['id' => $event->getTableRowId()]);

        if (empty($course)) {
            return false;
        }

        // copied and pasted from CourseManager::isGranted()
        // TODO: consolidate [ST 2015/08/05]
        return (
            $this->userHasRole($user, ['Faculty', 'Course Director', 'Developer'])
            && ($course->getOwningSchool()->getId() === $user->getPrimarySchool()->getId()
                || $this->permissionManager->userHasWritePermissionToSchool($user, $course->getOwningSchool())
            )
            || $this->permissionManager->userHasWritePermissionToCourse($user, $course)
        );
    }

    /**
     * @param PublishEventInterface $event
     * @param UserInterface $user
     * @return bool
     *
     * @see CourseVoter::isGranted()
     */
    protected function isGrantedCreateOrDeleteForSessionPublishEvent($event, $user)
    {
        $session = $this->sessionManager->findSessionBy(['id' => $event->getTableRowId()]);

        if (empty($session)) {
            return false;
        }

        $course = $session->getCourse();

        // copied and pasted from CourseManager::isGranted()
        // TODO: consolidate [ST 2015/08/05]
        return (
            $this->userHasRole($user, ['Faculty', 'Course Director', 'Developer'])
            && ($course->getOwningSchool()->getId() === $user->getPrimarySchool()->getId()
                || $this->permissionManager->userHasWritePermissionToSchool($user, $course->getOwningSchool())
            )
            || $this->permissionManager->userHasWritePermissionToCourse($user, $course)
        );
    }
}
