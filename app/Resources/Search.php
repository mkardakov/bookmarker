<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 4/4/17
 * Time: 8:08 PM
 */

namespace Bookmarker\Resources;

use Bookmarker\Responses\ErrorResponse;
use Doctrine\Common\Collections\Criteria;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Search
 * @package Bookmarker\Resources
 */
class Search extends Resource
{

    /**
     * @SWG\Get(
     *     path="/search",
     *     summary="Search books using query string",
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
     *     @SWG\Parameter(
     *         name="title",
     *         in="query",
     *         description="Search by book by title. Pattern: %title%",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="author_id",
     *         in="query",
     *         description="Search by known author ID",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="author_surname",
     *         in="query",
     *         description="Search by Author`s surname. Pattern: surname%",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="genre_id",
     *         in="query",
     *         description="Search by known genre ID",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="lang",
     *         in="query",
     *         description="Search by book language",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="books set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Book")
     *         )
     *     ),
     *   @SWG\Response(response=400, description="Shit happens"),
     * )
     * @param Application $app
     * @param Request $req
     * @return ErrorResponse|Response
     */
    public function search(Application $app, Request $req)
    {
        try {
            $params = $req->query->all();
            $books = $app['orm.em']->getRepository('doctrine:Book')
                ->search($params, $this->getPage(), $this->getLimit(), $this->getOrdering());
            return new Response($app['serializer']->serialize($books, RESPONSE_FORMAT), 200);
        } catch(\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/search/count",
     *     summary="Get count of found books. See search query params",
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
     * @param Application $app
     * @param Request $req
     * @return int|ErrorResponse
     */
    public function count(\Silex\Application $app, Request $req)
    {
        try {
            $params = $req->query->all();
            $res = $app['orm.em']->getRepository('doctrine:Book')
                ->searchCount($params);
            if (null === $res) {
                throw new \Exception('Total number of search results cannot be computed');
            }
            return $res;
        } catch(\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}