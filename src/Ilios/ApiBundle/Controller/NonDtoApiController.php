<?php

namespace Ilios\ApiBundle\Controller;

use Ilios\CoreBundle\Entity\Manager\BaseManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NonDtoApiController extends ApiController
{
    public function getAction($version, $object, $id)
    {
        $manager = $this->getManager($object);
        $entity = $manager->findOneBy(['id' => $id]);

        if (! $entity) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $this->resultsToResponse([$entity], $object, Response::HTTP_OK);
    }

    public function getAllAction($version, $object, Request $request)
    {
        $parameters = $this->extractParameters($request);
        $manager = $this->getManager($object);
        $result = $manager->findBy(
            $parameters['criteria'],
            $parameters['orderBy'],
            $parameters['limit'],
            $parameters['offset']
        );

        return $this->resultsToResponse($result, $object, Response::HTTP_OK);
    }

    /**
     * @param string $object
     * @return BaseManager
     */
    protected function getManager($object)
    {
        $singularName = $this->getSingularObjectName($object);
        $name = "ilioscore.{$singularName}.manager";
        if (!$this->container->has($name)) {
            throw new NotFoundHttpException(sprintf(
                    'The endpoint \'%s\' does not exist.', $object)
            );
        }

        /** @var BaseManager $manager */
        $manager = $this->container->get($name);

        return $manager;
    }
}
