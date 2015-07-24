<?php

namespace Ilios\CoreBundle\Tests\Fixture;

use Ilios\CoreBundle\Entity\LearningMaterial;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadLearningMaterialData extends AbstractFixture implements
    FixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('ilioscore.dataloader.learningMaterial')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new LearningMaterial();
            if (array_key_exists('id', $arr)) {
                $entity->setId($arr['id']);
            }
            $entity->setTitle($arr['title']);
            $entity->setDescription($arr['description']);
            $entity->setOriginalAuthor($arr['originalAuthor']);
            $entity->setCopyrightRationale($arr['copyrightRationale']);
            $entity->setCopyrightPermission($arr['copyrightPermission']);
            $entity->setUserRole($this->getReference('learningMaterialUserRoles' . $arr['userRole']));
            $entity->setStatus($this->getReference('learningMaterialStatus' . $arr['status']));
            $entity->setOwningUser($this->getReference('users' . $arr['owningUser']));
            if (array_key_exists('link', $arr)) {
                $entity->setLink($arr['link']);
            }
            if (array_key_exists('citation', $arr)) {
                $entity->setCitation($arr['citation']);
            }

            $manager->persist($entity);
            $this->addReference('learningMaterials' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            'Ilios\CoreBundle\Tests\Fixture\LoadLearningMaterialUserRoleData',
            'Ilios\CoreBundle\Tests\Fixture\LoadLearningMaterialStatusData',
            'Ilios\CoreBundle\Tests\Fixture\LoadUserData',
        );
    }
}
