<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/7/17
 * Time: 8:04 PM
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
 * Class Genre
 * @package Bookmarker\Resources
 */
class Genre extends Resource
{

    /**
     * @SWG\Get(
     *     path="/genre",
     *     summary="Retrieve all genres",
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
     *         description="genres set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Genre")
     *         )
     *     ),
     * )
     * @param Application $app
     * @return Response
     */
    public function listGenres(Application $app)
    {
        try {
            $genres = $app['orm.em']->getRepository('doctrine:Genre')->findLimited(
                $this->getPage(),
                $this->getLimit(),
                $this->getOrdering()
            );
            return new Response($app['serializer']->serialize($genres, RESPONSE_FORMAT), 200);
        } catch(\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/genre/{id}",
     *     summary="Retrieve a genre by id",
     *      @SWG\Parameter(
     *         description="ID of genre to fetch",
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
     *         description="genre data",
     *         @SWG\Schema(ref="#/definitions/Genre")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Genre not found by id",
     *     ),
     * )
     * @param Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function get(Application $app, $id)
    {
        $genre = $app['orm.em']->find('doctrine:Genre', $id);
        if (!$genre instanceof Entities\Genre) {
            return new ErrorResponse('Genre not found', 404);
        }
        return new Response($app['serializer']->serialize($genre, RESPONSE_FORMAT), 200);
    }

    /**
     * @SWG\Post(
     *     path="/genre",
     *     operationId="add",
     *     summary="Creates a new genre",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Genre object that needs to be added to the system",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string"
     *                  )
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="genre is created successfully",
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
     * @return CreatedResponse|ErrorResponse
     */
    public function add(Application $app, Request $req)
    {
        try {
            $data = json_decode($req->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg());
            }
            $id = $app['orm.em']->getRepository('doctrine:Genre')->addGenre($data);
        } catch(\Exception $e) {
            return new ErrorResponse('Failed to create new genre');
        }
        return new CreatedResponse("/genre/$id");
    }

    /**
     * @SWG\Put(
     *     path="/genre/{id}",
     *     operationId="replace",
     *     summary="Updates a genre",
     *     @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the genre that needs to be update",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Genre object that needs to be updated",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string"
     *                  )
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid Data received",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Genre not found by id",
     *     ),
     *     @SWG\Response(response=200, description="success")
     * )
     * @param Application $app
     * @param Request $req
     * @param $id
     * @return ErrorResponse|Response
     */
    public function replace(Application $app, Request $req, $id)
    {
        try {
            $data = $this->getNotEmptyBody($req);
            $genre = $app['orm.em']->find('doctrine:Genre', $id);
            if (!$genre instanceof Entities\Genre) {
                throw new NotFoundHttpException('Requested genre not found');
            }
            $app['orm.em']->getRepository('doctrine:Genre')->updateGenre($genre, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch(\Exception $e) {
            return new ErrorResponse('Failed to create new genre');
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Delete(path="/genre/{id}",
     *   summary="Delete genre by ID",
     *   operationId="remove",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the genre that needs to be deleted",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Response(response=400, description="Invalid ID supplied"),
     *   @SWG\Response(response=404, description="Genre not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function remove(Application $app, $id)
    {
        try {
            $genre = $app['orm.em']->find('doctrine:Genre', $id);
            if (!$genre instanceof Entities\Genre) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:Genre')->deleteGenre($genre);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Get(
     *     path="/genre/count",
     *     summary="Get count of genres",
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