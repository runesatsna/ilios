<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Manager\AuthenticationManager;
use App\Entity\Manager\UserManager;
use App\RelationshipVoter\AbstractVoter;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use App\Service\SessionUserProvider;
use App\Entity\AuthenticationInterface;
use App\Entity\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;
use RuntimeException;

/**
 * Class AuthController
 * Authentication uses 'user' as the primary key and
 * needs to encode passwords
 * so we have to handle that specially.
 */
class Authentication
{
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var SessionUserProvider
     */
    protected $sessionUserProvider;

    /**
     * @var AuthenticationManager
     */
    protected $manager;
    /**
     * @var UserManager
     */
    protected $userManager;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        SessionUserProvider $sessionUserProvider,
        AuthenticationManager $manager,
        UserManager $userManager,
        SerializerInterface $serializer
    ) {
        $this->sessionUserProvider = $sessionUserProvider;
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
        $this->userManager = $userManager;
        $this->serializer = $serializer;
    }

    public function get($version, $userId, ApiResponseBuilder $builder)
    {
        $dto = $this->manager->findDTOBy(['user' => $userId]);

        if (! $dto) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $userId));
        }

        return $builder->buildPluralResponse('authentications', [$dto], Response::HTTP_OK);
    }

    /**
     * Along with taking input this also encodes the passwords
     * so they can be stored safely in the database
     */
    public function post(
        string $version,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ) {
        $class = $this->manager->getClass() . '[]';
        $arr = $requestParser->extractPostDataFromRequest($request, 'authentications');

        $needingHashedPassword = array_filter($entities, function ($obj) {
            return (!empty($obj->password) && !empty($obj->user));
        });
        $userIdsForHashing = array_map(function ($obj) {
            return $obj->user;
        }, $needingHashedPassword);
        //prefetch all the users we need for hashing
        $users = [];
        /** @var UserInterface $user */
        foreach ($this->userManager->findBy(['id' => $userIdsForHashing]) as $user) {
            $users[$user->getId()] = $user;
        }

        $encodedPasswords = [];
        foreach ($arr as $obj) {
            if (!empty($obj->password) && !empty($obj->user)) {
                $user = $users[$obj->user];
                if ($user) {
                    $sessionUser = $this->sessionUserProvider->createSessionUserFromUser($user);
                    $encodedPassword = $this->passwordEncoder->encodePassword($sessionUser, $obj->password);
                    $encodedPasswords[$user->getId()] = $encodedPassword;
                }
            }
            //unset the password here in case it is NULL and didn't satisfy the above condition
            unset($obj->password);
        }
        $json = json_encode($arr);
        $entities = $this->serializer->deserialize($json, $class, 'json');

        foreach ($entities as $entity) {
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
            }
            if (! $authorizationChecker->isGranted(AbstractVoter::CREATE, $entity)) {
                throw new AccessDeniedException('Unauthorized access!');
            }
        }

        $entitiesByUserId = [];
        /** @var AuthenticationInterface $authentication */
        foreach ($entities as $authentication) {
            $entitiesByUserId[$authentication->getUser()->getId()] = $authentication;
        }

        foreach ($encodedPasswords as $userId => $password) {
            $entitiesByUserId[$userId]->setPasswordHash($password);
        }
        $entities = array_values($entitiesByUserId);

        foreach ($entities as $entity) {
            $this->manager->update($entity, false);
        }
        $this->manager->flush();

        return $builder->buildPluralResponse('authentications', $entities, Response::HTTP_CREATED);
    }

    /**
     * Along with taking user input, this also encodes passwords so they
     * can be stored safely in the database
     */
    public function put(
        string $version,
        string $userId,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ) {
        $entity = $this->manager->findOneBy(['user' => $userId]);

        if ($entity) {
            $code = Response::HTTP_OK;
            $permission = AbstractVoter::EDIT;
        } else {
            $entity = $this->manager->create();
            $code = Response::HTTP_CREATED;
            $permission = AbstractVoter::CREATE;
        }
        $authObject = $requestParser->extractPutDataFromRequest($request, 'authentications');
        if (!empty($authObject->password) && !empty($authObject->user)) {
            /** @var UserInterface $user */
            $user = $this->userManager->findOneBy(['id' => $authObject->user]);
            if ($user) {
                //set the password to null to reset the encoder
                //so we don't use the legacy one
                $entity->setPasswordSha256(null);
                $sessionUser = $this->sessionUserProvider->createSessionUserFromUser($user);
                $encodedPassword = $this->passwordEncoder->encodePassword($sessionUser, $authObject->password);
            }
        }
        unset($authObject->password);

        $json = json_encode($authObject);
        $this->serializer->deserialize(
            $json,
            get_class($entity),
            'json',
            ['object_to_populate' => $entity]
        );
        if (isset($encodedPassword)) {
            $entity->setPasswordHash($encodedPassword);
        }

        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
        }
        if (! $authorizationChecker->isGranted($permission, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        $this->manager->update($entity, true, false);

        return $builder->buildSingularResponse('authentications', $entity, $code);
    }

    /**
     * Deletes a record by userId
     */
    public function delete(
        string $version,
        string $userId,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        $entity = $this->manager->findOneBy(['user' => $userId]);

        if (! $entity) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        if (! $authorizationChecker->isGranted(AbstractVoter::DELETE, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        try {
            $this->manager->delete($entity);

            return new Response('', Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            throw new RuntimeException("Failed to delete entity: " . $exception->getMessage());
        }
    }
}
