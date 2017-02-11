<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/29/17
 * Time: 6:38 PM
 */

namespace Bookmarker\Db\Repositories;

use Bookmarker\Db\Entities\User;
use Bookmarker\Registry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserRepository
 * @package Bookmarker\Db\Repositories
 */
class UserRepository extends Repository
{
    /**
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function add(array $params)
    {
        $userEntity = new User();
        $app = Registry::get('app');
        $em = $this->getEntityManager();
        if (array_key_exists('email', $params)) {
            $userEntity->setEmail($params['email']);
        }
        if (array_key_exists('password', $params)) {
            $hash = $app['security.encoder_factory']
                ->getEncoder($userEntity)
                ->encodePassword($params['password'], null);
            if (!$hash) {
                throw new \Exception('Cannot compute a hash based received password');
            }
            $userEntity->setPassword($hash);
            $userEntity->addRole($em->find('doctrine:Role', 'ROLE_USER'));
        }
        $em->persist($userEntity);
        $em->flush();
        return $userEntity->getId();
    }

    /**
     * @param User $userEntity
     * @param array $params
     */
    public function update(User $userEntity, array $params)
    {
        $em = $this->getEntityManager();
        if (array_key_exists('email', $params)) {
            $userEntity->setEmail($params['email']);
        }
        if (array_key_exists('role_id', $params)) {
            $role = $em->find('doctrine:Role', $params['role_id']);
            if (!$role) {
                throw new NotFoundHttpException(sprintf('Role_id %d is not exist', $params['role_id']));
            }
            $userEntity->setRole($role);
        }
        if (array_key_exists('name', $params)) {
            $userEntity->setName($params['name']);
        }
        if (array_key_exists('surname', $params)) {
            $userEntity->setSurname($params['surname']);
        }

        $em->persist($userEntity);
        $em->flush();
    }

    /**
     * @param User $userEntity
     */
    public function delete(User $userEntity)
    {
        $em = $this->getEntityManager();
        $em->remove($userEntity);
        $em->flush();
    }

    /**
     * @param $pass
     * @return bool|string
     */
    protected function encryptPass($pass)
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }
}