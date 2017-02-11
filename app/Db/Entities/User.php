<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/29/17
 * Time: 6:10 PM
 */

namespace Bookmarker\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @package Bookmarker\Db\Entities
 * @ORM\Entity(repositoryClass="Bookmarker\Db\Repositories\UserRepository")
 * @ORM\Table(name="User",uniqueConstraints={@ORM\UniqueConstraint(name="fullname_ux", columns={"name","surname"})})
 * @ORM\Table(name="User",uniqueConstraints={@ORM\UniqueConstraint(name="email_ux", columns={"email"})})
 * @JMS\ExclusionPolicy("all")
 * @SWG\Definition(
 *   definition="User",
 *   type="object"
 * )
 */

class User implements UserInterface
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @JMS\Expose
     * @SWG\Property(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     * @SWG\Property(type="string")
     */
    protected $password;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, length=30)
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    protected $name = '';

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, length=30)
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    protected $surname = '';

    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @SWG\Property(type="string")
     */
    protected $email;

    /**
     * @var Role[]
     * @ORM\ManyToMany(targetEntity="Role", mappedBy="users")
     * @SWG\Property(type="string")
     * @JMS\Expose
     */
    protected $roles;

    /**
     * @var Books[]
     * @ORM\OneToMany(targetEntity="Book", mappedBy="user")
     */
    protected $books;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     *
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Add role
     *
     * @param \Bookmarker\Db\Entities\Role $role
     *
     * @return User
     */
    public function addRole(\Bookmarker\Db\Entities\Role $role)
    {
        $this->roles[] = $role;
        $role->addUser($this);
        return $this;
    }

    /**
     * Remove role
     *
     * @param \Bookmarker\Db\Entities\Role $role
     */
    public function removeRole(\Bookmarker\Db\Entities\Role $role)
    {
        $this->authors->removeElement($role);
        $role->removeUser($this);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRolesEntities()
    {
        return $this->roles;
    }

    /**
     * Required method for Security Provider
     * @return array
     */
    public function getRoles()
    {
        $rolesArr = [];
        foreach ($this->getRolesEntities() as $role) {
            $rolesArr[] = $role->getId();
        }
        return $rolesArr;
    }

    /**
     * Add book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     *
     * @return User
     */
    public function addBook(\Bookmarker\Db\Entities\Book $book)
    {
        $this->books[] = $book;

        return $this;
    }

    /**
     * Remove book
     *
     * @param \Bookmarker\Db\Entities\Book $book
     */
    public function removeBook(\Bookmarker\Db\Entities\Book $book)
    {
        $this->users->removeElement($book);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        $this->getEmail();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {

    }
}
