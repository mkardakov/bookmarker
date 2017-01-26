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
     *         name="max_record_number",
     *         in="query",
     *         description="limitation param",
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
     * @param Request $req
     * @return Response
     */
    public function listAuthors(Application $app, Request $req)
    {
        $actual = $this->getMaxRowsNumber($req);
        $authors = $app['orm.em']->getRepository('doctrine:Author')->findBy(array(), array(), $actual);
        return new Response($app['serializer']->serialize($authors, RESPONSE_FORMAT), 200);
    }

    /**
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
            $id = $app['orm.em']->getRepository('doctrine:Author')->addAuthor($data);
        } catch(\Exception $e) {
            return new ErrorResponse('Failed to create new author');
        }
        return new CreatedResponse("/author/$id");
    }

    /**
     * @param Application $app
     * @param Request $req
     * @param $id
     * @return ErrorResponse|Response
     */
    public function replace(Application $app, Request $req, $id)
    {
        try {
            $data = json_decode($req->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error_msg());
            }
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

}