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
use Silex\Application;
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
     *         name="max_record_number",
     *         in="query",
     *         description="limitation param",
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
     * @param Request $req
     * @return Response
     */
    public function listUsers(Application $app, Request $req)
    {
        $actual = $this->getMaxRowsNumber($req);
        $users = $app['orm.em']->getRepository('doctrine:User')->findBy(array(), array(), $actual);
        return new Response($app['serializer']->serialize($users, RESPONSE_FORMAT), 200);
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
            $data = $this->getBody($req);
            $id = $app['orm.em']->getRepository('doctrine:User')->add($data);
        } catch(\Exception $e) {
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
            $data = $this->getBody($req);
            $user = $app['orm.em']->find('doctrine:User', $id);
            if (!$user instanceof Entities\User) {
                throw new NotFoundHttpException('Requested user not found');
            }
            $app['orm.em']->getRepository('doctrine:User')->update($user, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch(\Exception $e) {
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

}