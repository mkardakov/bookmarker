<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 4:00 PM
 */

namespace Bookmarker\Resources;

use Bookmarker\FileDrivers\LocalDriver;
use Bookmarker\Responses\CreatedResponse;
use Bookmarker\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Bookmarker\Db\Entities;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;

/**
 * Class Book
 * @package Bookmarker\Resources
 */
class Book extends Resource
{

    /**
     * @SWG\Get(
     *     path="/book/{id}",
     *     summary="Retrieve a book by id",
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
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
     *         description="book data",
     *         @SWG\Schema(ref="#/definitions/Book")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Book not found by id",
     *     ),
     * )
     * @param \Silex\Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function get(\Silex\Application $app, $id)
    {
        $book = $app['orm.em']->find('doctrine:Book', $id);
        if (!$book instanceof Entities\Book) {
            return new ErrorResponse('', 404);
        }
        $jsonContent = $app['serializer']->serialize($book, RESPONSE_FORMAT);
        return new Response($jsonContent, 200);
    }

    /**
     * @SWG\Get(
     *     path="/book",
     *     summary="Retrieve all books",
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
     *         description="books set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Book")
     *         )
     *     ),
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @return Response
     */
    public function listBooks(\Silex\Application $app, Request $req)
    {
        $actual = $this->getMaxRowsNumber($req);
        $books = $app['orm.em']->getRepository('doctrine:Book')->findBy(array(), array(), $actual);
        return new Response($app['serializer']->serialize($books, RESPONSE_FORMAT), 200);
    }

    /**
     * @SWG\Post(
     *     path="/book",
     *     operationId="addBook",
     *     summary="Creates a new book based on received file",
     *     produces={"application/json"},
     *     consumes={"multipart/form-data"},
     *     @SWG\Parameter(
    *          description="book to upload",
     *         in="formData",
     *         name="book",
     *         required=true,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="book is uploaded successfully",
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
     * @param \Silex\Application $app
     * @param Request $req
     * @return Response
     */
    public function add(\Silex\Application $app, Request $req)
    {
        $id = 0;
        if ($req->files->has('book')) {
            $fileBag = $req->files->get('book');
            try {
                if (!$fileBag instanceof UploadedFile) {
                    throw new \Exception('No file received');
                }
                $id = $app['orm.em']->getRepository('doctrine:Book')->addBook($fileBag);
            } catch (\Exception $e) {
                return new ErrorResponse($e->getMessage());
            }
        }
        return new CreatedResponse("/book/$id");
    }

    /**
     * @SWG\Put(
     *     path="/book/{id}",
     *     operationId="replaceBook",
     *     summary="Update an existiting book",
     *     description="",
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the book that needs to be update",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Book object that needs to be added to the store",
     *         required=true,
     *         minimum=1.0,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="year",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="lang",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="genre_id",
     *                      type="integer",
     *                      minimum=1.0
     *                  ),
     *                  @SWG\Property(
     *                      property="author_ids",
     *                      type="array",
     *                      @SWG\Items(type="integer", minimum=1.0)
     *                  ),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid Data received",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Book not found by id",
     *     ),
     *     @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param int $id
     * @return Response
     */
    public function replace(\Silex\Application $app, Request $req, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $data = $this->getBody($req);
            $app['orm.em']->getRepository('doctrine:Book')->updateBook($book, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Delete(path="/book/{id}",
     *   summary="Delete book by ID",
     *   operationId="remove",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the book that needs to be deleted",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Response(response=400, description="Invalid ID supplied"),
     *   @SWG\Response(response=404, description="Book not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function remove(\Silex\Application $app, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:Book')->deleteBook($book);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Get(
     *     path="/book/{filename}",
     *     summary="Show or Download book",
     *      @SWG\Parameter(
     *         description="name of the book to download",
     *         format="string",
     *         in="path",
     *         name="filename",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="book data",
     *         @SWG\Schema(type="file")
     *     ),
     *     @SWG\Response(response=400, description="bad request"),
     *     @SWG\Response(response=404, description="Book file not found"),
     * )
     * @param \Silex\Application $app
     * @param string $file
     * @param string $ext
     * @return ErrorResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(\Silex\Application $app, $file, $ext)
    {
        try {
            $driver = new LocalDriver("$file.$ext");
            if (!($descriptor = $driver->getDownloadLink())) {
                throw new \Exception('path to file is incorrect');
            }
            $mime = $driver->getMimeType();
            return $app->stream(function() use ($descriptor) {
                echo stream_get_contents($descriptor);
            }, 200, array("Content-Type" => $mime));

        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}