<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Traits\UsersEntity;
use AppBundle\Annotation as IS;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;

use AppBundle\Traits\IdentifiableEntity;
use AppBundle\Traits\TitledEntity;
use AppBundle\Traits\StringableIdEntity;
use AppBundle\Entity\UserInterface;

/**
 * Class UserRole
 *
 * @ORM\Table(name="user_role")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRoleRepository")
 *
 * @IS\Entity
 */
class UserRole implements UserRoleInterface
{
    use TitledEntity;
    use StringableIdEntity;
    use IdentifiableEntity;
    use UsersEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="user_role_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Assert\Type(type="integer")
     *
     * @IS\Expose
     * @IS\Type("integer")
     * @IS\ReadOnly
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=60)
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 1,
     *      max = 60
     * )
     *
     * @IS\Expose
     * @IS\Type("string")
    */
    protected $title;

     /**
      * @var ArrayCollection|UserInterface[]
      *
      * @ORM\ManyToMany(targetEntity="User", mappedBy="roles")
      * @ORM\OrderBy({"id" = "ASC"})
      *
      * Don't put users in the UserRole API it takes too long to load
      * @IS\Type("entityCollection")
      */
    protected $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @param UserInterface $user
     */
    public function addUser(UserInterface $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addRole($this);
        }
    }

    /**
     * @param UserInterface $user
     */
    public function removeUser(UserInterface $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeRole($this);
        }
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return 'ROLE_' . $this->title;
    }
}
