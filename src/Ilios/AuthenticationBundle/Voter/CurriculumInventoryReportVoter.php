<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\CurriculumInventoryReportInterface;
use Ilios\CoreBundle\Entity\UserInterface;

/**
 * Class CurriculumInventoryReportVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class CurriculumInventoryReportVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Ilios\CoreBundle\Entity\CurriculumInventoryReport');
    }

    /**
     * @param string $attribute
     * @param CurriculumInventoryReportInterface $report
     * @param UserInterface $user
     * @return bool
     */
    protected function isGranted($attribute, $report, $user = null)
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                if ($report->getProgram()->getOwningSchool()->getId() === $user->getPrimarySchool()->getId()) {
                    return $this->userHasRole($user, ['Course Director', 'Developer']);
                };
                break;
        }

        return false;
    }
}
