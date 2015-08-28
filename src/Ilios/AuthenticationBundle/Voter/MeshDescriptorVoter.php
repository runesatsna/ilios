<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\UserInterface;

/**
 * Class MeshDescriptorVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class MeshDescriptorVoter extends MeshVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Ilios\CoreBundle\Entity\MeshDescriptorInterface');
    }
}
