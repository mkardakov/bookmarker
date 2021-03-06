<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/29/17
 * Time: 6:01 PM
 */

namespace Bookmarker\Resources;

use Bookmarker\Responses\CreatedResponse;
use Bookmarker\Responses\ErrorResponse;
use Doctrine\Common\Collections\Criteria;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Bookmarker\Db\Entities;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/**
 * Class User
 * @package Bookmarker\Resources
 */
class User extends Resource
{

    const CURRENT_USER_ALIAS = 'me';

    /**
     * @SWG\Get(
     *     path="/user/{id}",
     *     summary="Retrieve a user by id",
     *      @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Response(
     *         response=200,
     *         description="user info",
     *         @SWG\Schema(ref="#/definitions/User")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found by id",
     *     ),
     * )
     * @param Application $app
     * @param $id
     * @return Response
     */
    public function get(Application $app, $id)
    {
        $user = $app['orm.em']->find('doctrine:User', $id);
        if (!$user instanceof Entities\User) {
            return new ErrorResponse('', 404);
        }
        $jsonContent = $app['serializer']->serialize($user, RESPONSE_FORMAT);
        return new Response($jsonContent, 200);
    }

    /**
     * @SWG\Get(
     *     path="/user",
     *     summary="Retrieve all users",
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Parameter(
     *         name="order",
     *         in="query",
     *         description="sort book Objects, Example: ?order=id/DESC,name/ASC",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit number of returned data. Positive value > 0",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Limit number of returned data. Positive value > 0",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="users set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Users")
     *         )
     *     ),
     * )
     * @param Application $app
     * @return Response
     */
    public function listUsers(Application $app)
    {
        try {
            $users = $app['orm.em']->getRepository('doctrine:User')->findLimited(
                $this->getPage(),
                $this->getLimit(),
                $this->getOrdering()
            );
            return new Response($app['serializer']->serialize($users, RESPONSE_FORMAT), 200);
        } catch(\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Post(
     *     path="/user",
     *     operationId="add",
     *     summary="Creates a new user",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="User object that needs to be added to the system",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/User"),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="user was created",
     *         @SWG\Schema(
     *           type="object",
     *           additionalProperties={
     *            "contentUri":"string"
     *           }
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="bad request"
     *     )
     * )
     * @param Application $app
     * @param Request $req
     * @return Response
     */
    public function add(Application $app, Request $req)
    {
        try {
            $data = $this->getNotEmptyBody($req);
            $id = $app['orm.em']->getRepository('doctrine:User')->add($data);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new CreatedResponse("/user/$id");
    }

    /**
     * @SWG\Put(
     *     path="/user/{id}",
     *     operationId="replace",
     *     summary="Updates a user",
     *     @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the user that needs to be update",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="User object that needs to be updated",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/User")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid Data received",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found by id",
     *     ),
     *     @SWG\Response(response=200, description="success")
     * )
     * @param Application $app
     * @param Request $req
     * @param int $id
     * @return Response
     */
    public function replace(Application $app, Request $req, $id)
    {
        try {
            $data = $this->getNotEmptyBody($req);
            $user = $app['orm.em']->find('doctrine:User', $id);
            if (!$user instanceof Entities\User) {
                throw new NotFoundHttpException('Requested user not found');
            }
            $app['orm.em']->getRepository('doctrine:User')->update($user, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse('Failed to update a user');
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Delete(path="/user/{id}",
     *   summary="Delete user by ID",
     *   operationId="remove",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the user that needs to be deleted",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Response(response=400, description="Invalid ID supplied"),
     *   @SWG\Response(response=404, description="User not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function remove(Application $app, $id)
    {
        try {
            $user = $app['orm.em']->find('doctrine:User', $id);
            if (!$user instanceof Entities\User) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:User')->delete($user);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Get(
     *     path="/user/{id}/book",
     *     summary="Retrieve all books of specified user",
     *     produces={
     *          "application/json"
     *     },
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the requested user. Use id = me to access current user",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *     @SWG\Parameter(
     *         name="order",
     *         in="query",
     *         description="sort book Objects, Example: ?order=id/DESC,name/ASC",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit number of returned data. Positive value > 0",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Limit number of returned data. Positive value > 0",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="books set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Book")
     *         )
     *     ),
     *   @SWG\Response(response=400, description="Invalid ID supplied"),
     *   @SWG\Response(response=404, description="User not found"),
     * )
     * @SWG\Get(
     *     path="/user/book",
     *     @SWG\Response(response=301, description="Permanent redirect to current user`s space /user/me/book")
     * )
     * @param Application $app
     * @param mixed $id
     * @return ErrorResponse|RedirectResponse|Response
     */
    public function getBooks(Application $app, $id = null)
    {
        try {
            if (is_null($id)) {
                return new RedirectResponse(sprintf('/user/%s/book', self::CURRENT_USER_ALIAS), 301);
            }
            if ($id === User::CURRENT_USER_ALIAS) {
                $user = $app['security.token_storage']->getToken()->getUser();
            } else {
                $user = $app['orm.em']->find('doctrine:User', $id);
            }
            if (!$user instanceof Entities\User) {
                throw new NotFoundHttpException('Requested user was not found');
            }
            $queryParamsCriteria = $app['orm.em']->getRepository('doctrine:User')->buildLimitedCriteria(
                $this->getPage(),
                $this->getLimit(),
                $this->getOrdering()
            );
            return new Response($app['serializer']->serialize(
                $user->getBooks()->matching($queryParamsCriteria),
                RESPONSE_FORMAT
            ), 200);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/user/{id}/book/count",
     *     summary="Get count of book comments",
     *     produces={
     *          "application/json"
     *     },
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns total number of rows",
     *         @SWG\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Internal error occurs",
     *     ),
     *     @SWG\Response(response=404, description="User not found")
     * )
     * @param Application $app
     * @param $id
     * @return ErrorResponse
     */
    public function countBooks(\Silex\Application $app, $id)
    {
        try {
            if ($id === User::CURRENT_USER_ALIAS) {
                $user = $app['security.token_storage']->getToken()->getUser();
            } else {
                $user = $app['orm.em']->find('doctrine:User', $id);
            }
            if (!$user instanceof Entities\User) {
                throw new NotFoundHttpException('Requested user was not found');
            }
            return parent::count($app, 'Book', Criteria::create()->where(
                Criteria::expr()->eq('user', $user)
            ));
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/user/count",
     *     summary="Get count of users",
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Response(
     *         response=200,
     *         description="Returns total number of rows",
     *         @SWG\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Internal error occurs",
     *     ),
     * )
     */
}