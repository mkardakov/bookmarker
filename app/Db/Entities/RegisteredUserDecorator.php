<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/5/17
 * Time: 3:41 PM
 */

namespace Bookmarker\Db\Entities;


use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @todo extends User entity and make it single for authorization process
 * Class RegisteredUserDecorator
 * @package Bookmarker\Db\Entities
 */
class RegisteredUserDecorator implements UserInterface
{

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $roles = [];
        foreach ($this->user->getRoles() as $role) {
            $roles[] = $role->getId();
        }
        return $roles;
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
        return $this->user->getEmail();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
       // $this->user->setPassword('');
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->user->getPassword();
    }

    /**
     * @return User
     */
    public function getUserEntity()
    {
        return $this->user;
    }
}