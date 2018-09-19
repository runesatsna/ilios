<?php
namespace App\Tests\RelationshipVoter;

use App\RelationshipVoter\AbstractVoter;
use App\RelationshipVoter\Authentication as Voter;
use App\Service\PermissionChecker;
use App\Entity\Authentication;
use App\Entity\School;
use App\Entity\User;
use App\Service\Config;
use Mockery as m;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AuthenticationTest extends AbstractBase
{
    public function setup()
    {
        $this->permissionChecker = m::mock(PermissionChecker::class);
        $this->voter = new Voter($this->permissionChecker);
    }

    public function testAllowsRootFullAccess()
    {
        $this->checkRootEntityAccess(m::mock(Authentication::class));
    }


    public function testCanView()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $token->getUser()->shouldReceive('performsNonLearnerFunction')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::VIEW]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "View allowed");
    }

    public function testCanNotView()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $token->getUser()->shouldReceive('performsNonLearnerFunction')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::VIEW]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "View denied");
    }

    public function testCanEdit()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $entity->shouldReceive('getId')->andReturn(1);
        $school = m::mock(School::class);
        $school->shouldReceive('getId')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getSchool')->andReturn($school);
        $entity->shouldReceive('getUser')->andReturn($user);
        $this->permissionChecker->shouldReceive('canUpdateUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::EDIT]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "Edit allowed");
    }

    public function testCanNotEdit()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $entity->shouldReceive('getId')->andReturn(1);
        $school = m::mock(School::class);
        $school->shouldReceive('getId')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getSchool')->andReturn($school);
        $entity->shouldReceive('getUser')->andReturn($user);
        $this->permissionChecker->shouldReceive('canUpdateUser')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::EDIT]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "Edit denied");
    }

    public function testCanDelete()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $entity->shouldReceive('getId')->andReturn(1);
        $school = m::mock(School::class);
        $school->shouldReceive('getId')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getSchool')->andReturn($school);
        $entity->shouldReceive('getUser')->andReturn($user);
        $this->permissionChecker->shouldReceive('canUpdateUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::DELETE]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "Delete allowed");
    }

    public function testCanNotDelete()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $entity->shouldReceive('getId')->andReturn(1);
        $school = m::mock(School::class);
        $school->shouldReceive('getId')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getSchool')->andReturn($school);
        $entity->shouldReceive('getUser')->andReturn($user);
        $this->permissionChecker->shouldReceive('canUpdateUser')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::DELETE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "Delete denied");
    }

    public function testCanCreate()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $school = m::mock(School::class);
        $school->shouldReceive('getId')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getSchool')->andReturn($school);
        $entity->shouldReceive('getUser')->andReturn($user);
        $this->permissionChecker->shouldReceive('canUpdateUser')->andReturn(true);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::CREATE]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $response, "Create allowed");
    }

    public function testCanNotCreate()
    {
        $token = $this->createMockTokenWithNonRootSessionUser();
        $entity = m::mock(Authentication::class);
        $school = m::mock(School::class);
        $school->shouldReceive('getId')->andReturn(1);
        $user = m::mock(User::class);
        $user->shouldReceive('getSchool')->andReturn($school);
        $entity->shouldReceive('getUser')->andReturn($user);
        $this->permissionChecker->shouldReceive('canUpdateUser')->andReturn(false);
        $response = $this->voter->vote($token, $entity, [AbstractVoter::CREATE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $response, "Create denied");
    }
}
