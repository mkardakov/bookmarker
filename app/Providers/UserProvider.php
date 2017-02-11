<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 2/5/17
 * Time: 3:26 PM
 */

namespace Bookmarker\Providers;


use Bookmarker\Db\Entities\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 * @package Bookmarker\Providers
 */
class UserProvider implements UserProviderInterface
{


    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $username email of user
     * @param string $username
     * @throws UsernameNotFoundException
     * @return User
     */
    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository('doctrine:User')->findOneBy(array('email' => $username));
        if (!$user instanceof User) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getEmail());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Bookmarker\Db\Entities\User';
    }
}