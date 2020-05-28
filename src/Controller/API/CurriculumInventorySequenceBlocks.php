<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\CurriculumInventorySequenceBlock;
use App\Entity\CurriculumInventorySequenceBlockInterface;
use App\Entity\Manager\CurriculumInventorySequenceBlockManager;
use App\RelationshipVoter\AbstractVoter;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;
use RuntimeException;

/**
 * @Route("/api/{version<v1|v2>}/curriculuminventorysequenceblocks")
 */
class CurriculumInventorySequenceBlocks extends ReadWriteController
{
    public function __construct(CurriculumInventorySequenceBlockManager $manager)
    {
        parent::__construct($manager, 'curriculuminventorysequenceblocks');
    }

    /**
     * Handles POST which creates new data in the API
     * @Route("", methods={"POST"})
     */
    public function post(
        string $version,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        $class = $this->manager->getClass() . '[]';

        $entities = $requestParser->extractEntitiesFromPostRequest($request, $class, $this->endpoint);

        foreach ($entities as $entity) {
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
            }
            if (! $authorizationChecker->isGranted(AbstractVoter::CREATE, $entity)) {
                throw new AccessDeniedException('Unauthorized access!');
            }

            $this->reorderBlocksInSequenceOnOrderChange(
                0,
                $entity
            );
            $this->manager->update($entity, false);
        }
        $this->manager->flush();

        return $builder->buildResponseForPostRequest($this->endpoint, $entities, Response::HTTP_CREATED, $request);
    }

    /**
     * Modifies a single object in the API.  Can also create and
     * object if it does not yet exist.
     * @Route("/{id}", methods={"PUT"})
     */
    public function put(
        string $version,
        string $id,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        /** @var CurriculumInventorySequenceBlockInterface $entity */
        $entity = $this->manager->findOneBy(['id' => $id]);

        if ($entity) {
            $code = Response::HTTP_OK;
            $permission = AbstractVoter::EDIT;
        } else {
            $entity = $this->manager->create();
            $code = Response::HTTP_CREATED;
            $permission = AbstractVoter::CREATE;
        }
        $oldChildSequenceOrder = $entity->getChildSequenceOrder();
        $oldOrderInSequence = $entity->getOrderInSequence();

        /** @var CurriculumInventorySequenceBlockInterface $entity */
        $entity = $requestParser->extractEntityFromPutRequest($request, $entity, $this->endpoint);

        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
        }
        if (! $authorizationChecker->isGranted($permission, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        $this->reorderChildrenOnChildSequenceOrderChange(
            $oldChildSequenceOrder,
            $entity
        );
        $this->reorderBlocksInSequenceOnOrderChange(
            $oldOrderInSequence,
            $entity
        );

        $this->manager->update($entity, true, false);

        return $builder->buildResponseForPutRequest($this->endpoint, $entity, $code, $request);
    }


    /**
     * Handles DELETE requests to remove an element from the API
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(
        string $version,
        string $id,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        /** @var CurriculumInventorySequenceBlockInterface $entity */
        $entity = $this->manager->findOneBy(['id' => $id]);

        if (! $entity) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        if (! $authorizationChecker->isGranted(AbstractVoter::DELETE, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        try {
            $this->reorderSiblingsOnDeletion($entity);
            $this->manager->delete($entity);
            $this->manager->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            throw new RuntimeException("Failed to delete entity: " . $exception->getMessage());
        }
    }

    /**
     * Reorders siblings of the sequence block being deleted.
     */
    protected function reorderSiblingsOnDeletion(
        CurriculumInventorySequenceBlockInterface $block
    ) {
        $parent = $block->getParent();
        if (! $parent || $parent->getChildSequenceOrder() !== CurriculumInventorySequenceBlockInterface::ORDERED) {
            return;
        }

        $siblings = $parent->getChildren()->toArray();
        /* @var CurriculumInventorySequenceBlockInterface[] $siblingsWithHigherSortOrder */
        $siblingsWithHigherSortOrder = array_values(array_filter($siblings, function ($sibling) use ($block) {
            /* @var CurriculumInventorySequenceBlockInterface $sibling */
            return ($sibling->getOrderInSequence() > $block->getOrderInSequence());
        }));
        for ($i = 0, $n = count($siblingsWithHigherSortOrder); $i < $n; $i++) {
            $orderInSequence = $siblingsWithHigherSortOrder[$i]->getOrderInSequence();
            $siblingsWithHigherSortOrder[$i]->setOrderInSequence($orderInSequence - 1);
            $this->manager->update($block, false, false);
        }
    }

    /**
     * Reorders child sequence blocks if the parent's child sequence order changes.
     */
    protected function reorderChildrenOnChildSequenceOrderChange(
        ?int $oldValue,
        CurriculumInventorySequenceBlockInterface $block
    ) {
        /* @var CurriculumInventorySequenceBlockInterface[] $children */
        $children = $block->getChildren()->toArray();
        if (empty($children)) {
            return;
        }

        $newValue = $block->getChildSequenceOrder();

        if ($newValue === $oldValue) {
            return;
        }

        switch ($newValue) {
            case CurriculumInventorySequenceBlockInterface::ORDERED:
                usort($children, [CurriculumInventorySequenceBlock::class, 'compareSequenceBlocksWithDefaultStrategy']);
                for ($i = 0, $n = count($children); $i < $n; $i++) {
                    $children[$i]->setOrderInSequence($i + 1);
                    $this->manager->update($children[$i], false);
                }
                break;
            case CurriculumInventorySequenceBlockInterface::UNORDERED:
            case CurriculumInventorySequenceBlockInterface::PARALLEL:
                if ($oldValue === CurriculumInventorySequenceBlockInterface::ORDERED) {
                    for ($i = 0, $n = count($children); $i < $n; $i++) {
                        $children[$i]->setOrderInSequence(0);
                        $this->manager->update($children[$i], false);
                    }
                }
                break;
            default:
                // do nothing
        }
    }

    /**
     * Reorder the entire sequence if one of the blocks changes position.
     */
    protected function reorderBlocksInSequenceOnOrderChange(
        ?int $oldValue,
        CurriculumInventorySequenceBlockInterface $block
    ) {
        $parent = $block->getParent();
        if (! $parent) {
            return;
        }
        if ($parent->getChildSequenceOrder() !== CurriculumInventorySequenceBlockInterface::ORDERED) {
            return;
        }

        $newValue = $block->getOrderInSequence();

        $blocks = $parent->getChildrenAsSortedList();

        $blocks = array_filter($blocks, function (CurriculumInventorySequenceBlockInterface $sibling) use ($block) {
            return $sibling->getId() !== $block->getId();
        });
        $blocks = array_values($blocks);

        $minRange = 1;
        $maxRange = count($blocks) + 1;
        if ($newValue < $minRange || $newValue > $maxRange) {
            throw new \OutOfRangeException(
                "The given order-in-sequence value {$newValue} falls outside the range {$minRange} - {$maxRange}."
            );
        }

        if ($oldValue === $newValue) {
            return;
        }

        array_splice($blocks, $block->getOrderInSequence() - 1, 0, [$block]);
        for ($i = 0, $n = count($blocks); $i < $n; $i++) {
            /* @var CurriculumInventorySequenceBlockInterface $current */
            $current = $blocks[$i];
            $j = $i + 1;
            if ($current->getId() !== $block && $current->getOrderInSequence() !== $j) {
                $current->setOrderInSequence($j);
                $this->manager->update($current, false, false);
            }
        }
    }
}
