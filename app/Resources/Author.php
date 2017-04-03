<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/8/17
 * Time: 12:58 PM
 */

namespace Bookmarker\Resources;

use Bookmarker\Responses\CreatedResponse;
use Bookmarker\Responses\ErrorResponse;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Bookmarker\Db\Entities;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Author extends Resource
{

    /**
     * @SWG\Get(
     *     path="/author/{id}",
     *     summary="Retrieve an author by id",
     *      @SWG\Parameter(
     *         description="ID of author to fetch",
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
     *         description="author data",
     *         @SWG\Schema(ref="#/definitions/Author")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Author not found by id",
     *     ),
     * )
     * @param Application $app
     * @param $id
     * @return Response
     */
    public function get(Application $app, $id)
    {
        $author = $app['orm.em']->find('doctrine:Author', $id);
        if (!$author instanceof Entities\Author) {
            return new ErrorResponse('', 404);
        }
        $jsonContent = $app['serializer']->serialize($author, RESPONSE_FORMAT);
        return new Response($jsonContent, 200);
    }

    /**
     * @SWG\Get(
     *     path="/author",
     *     summary="Retrieve all authors",
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
     *         description="authors set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Author")
     *         )
     *     ),
     * )
     * @param Application $app
     * @return Response
     */
    public function listAuthors(Application $app)
    {
        try {
            $authors = $app['orm.em']->getRepository('doctrine:Author')->findLimited(
                $this->getPage(),
                $this->getLimit(),
                $this->getOrdering()
            );
            return new Response($app['serializer']->serialize($authors, RESPONSE_FORMAT), 200);
        } catch(\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Post(
     *     path="/author",
     *     operationId="add",
     *     summary="Creates a new author",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Author object that will be created",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="surname",
     *                      type="string"
     *                  )
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="author is added successfully",
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
            $data = $this->getNotEmptyBody($req);
            $id = $app['orm.em']->getRepository('doctrine:Author')->addAuthor($data);
        } catch(\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new CreatedResponse("/author/$id");
    }

    /**
     * @SWG\Put(
     *     path="/author/{id}",
     *     operationId="replace",
     *     summary="Updates an author",
     *     @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the author that needs to be update",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Author object that needs to be updated",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="surname",
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
     *         description="Author not found by id",
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
            $author = $app['orm.em']->find('doctrine:Author', $id);
            if (!$author instanceof Entities\Author) {
                throw new NotFoundHttpException('Requested author not found');
            }
            $app['orm.em']->getRepository('doctrine:Author')->updateAuthor($author, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch(\Exception $e) {
            return new ErrorResponse('Failed to create new author');
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Delete(path="/author/{id}",
     *   summary="Delete author by ID",
     *   operationId="remove",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the author that needs to be deleted",
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
            $author = $app['orm.em']->find('doctrine:Author', $id);
            if (!$author instanceof Entities\Author) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:Author')->deleteAuthor($author);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Get(
     *     path="/author/count",
     *     summary="Get count of authors",
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