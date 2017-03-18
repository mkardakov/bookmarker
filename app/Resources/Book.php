<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 1/4/17
 * Time: 4:00 PM
 */

namespace Bookmarker\Resources;

use Bookmarker\Db\Repositories\VotesRepository;
use Bookmarker\FileDrivers\LocalDriver;
use Bookmarker\Jobs\Tasks\ConvertTask;
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
        try {
            if (!$req->files->has('book')) {
                throw new \Exception('No file received');
            }
            $fileBag = $req->files->get('book');
            if (!$fileBag instanceof UploadedFile) {
                throw new \Exception('No file received');
            }
            $id = $app['orm.em']->getRepository('doctrine:Book')->addBook($fileBag);
            return new CreatedResponse("/book/$id");
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
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
            $data = $this->getNotEmptyBody($req);
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
            return $app->stream(function () use ($descriptor) {
                echo stream_get_contents($descriptor);
            }, 200, array("Content-Type" => $mime));

        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/book/{id}/covers",
     *     summary="Retrieve all book covers for particular book",
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID of the target book",
     *        required=true,
     *        type="integer",
     *        format="int64",
     *        minimum=1.0
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="book covers set",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/BookCover")
     *         )
     *     ),
     *     @SWG\Response(response=404, description="Book not found"),
     * )
     * @param \Silex\Application $app
     * @param int $id
     * @return Response
     */
    public function listBookCovers(\Silex\Application $app, $id)
    {
        $book = $app['orm.em']->find('doctrine:Book', $id);
        if (!$book instanceof Entities\Book) {
            return new ErrorResponse('', 404);
        }
        $bookCovers = $book->getBookCovers();
        return new Response($app['serializer']->serialize($bookCovers, RESPONSE_FORMAT), 200);
    }

    /**
     * @SWG\Post(
     *     path="/book/{id}/covers",
     *     operationId="addBookCover",
     *     summary="Creates a new book cover based on received file",
     *     produces={"application/json"},
     *     consumes={"multipart/form-data"},
     *     @SWG\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID of the target book",
     *        required=true,
     *        type="integer",
     *        format="int64",
     *        minimum=1.0
     *     ),
     *     @SWG\Parameter(
     *          description="book to upload",
     *         in="formData",
     *         name="cover",
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
     *     @SWG\Response(response=404, description="Book not found"),
     *     @SWG\Response(
     *         response="400",
     *         description="bad request"
     *     )
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param int $id
     * @return Response
     */
    public function addCover(\Silex\Application $app, Request $req, $id)
    {
        $book = $app['orm.em']->find('doctrine:Book', $id);
        if (!$book instanceof Entities\Book) {
            return new ErrorResponse('', 404);
        }
        if ($req->files->has('cover')) {
            $fileBag = $req->files->get('cover');
            try {
                if (!$fileBag instanceof UploadedFile) {
                    throw new \Exception('No file received');
                }
                $bookCover = $app['orm.em']->getRepository('doctrine:Book')->addBookCover($book, $fileBag);
                if (!$bookCover instanceof Entities\BookCovers) {
                    throw new \Exception('Failed to add new book cover');
                }
            } catch (\Exception $e) {
                return new ErrorResponse($e->getMessage());
            }
        }
        return new CreatedResponse(sprintf("/book/%d/covers/%d", $bookCover->getBook()->getId(), $bookCover->getId()));
    }

    /**
     * @SWG\Get(
     *     path="/book/{id}/covers/{filename}",
     *     summary="Show or Download book cover",
     *     @SWG\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID of the target book",
     *        required=true,
     *        type="integer",
     *        format="int64",
     *        minimum=1.0
     *     ),
     *      @SWG\Parameter(
     *         description="name of the book cover to download",
     *         format="string",
     *         in="path",
     *         name="filename",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="book cover file",
     *         @SWG\Schema(type="file")
     *     ),
     *     @SWG\Response(response=400, description="bad request"),
     *     @SWG\Response(response=404, description="Book cover not found"),
     * )
     * @param \Silex\Application $app
     * @param int $id
     * @param string $file
     * @param string $ext
     * @return ErrorResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadCover(\Silex\Application $app, $id, $file, $ext)
    {
        $book = $app['orm.em']->find('doctrine:Book', $id);
        if (!$book instanceof Entities\Book) {
            return new ErrorResponse('', 404);
        }
        return $this->download($app, $file, $ext);
    }

    /**
     * @SWG\Put(
     *     path="/book/{id}/covers/{cover_id}",
     *     operationId="replaceBookCover",
     *     summary="Update an existiting book cover meta-data",
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID of the target book",
     *        required=true,
     *        type="integer",
     *        format="int64",
     *        minimum=1.0
     *     ),
     *     @SWG\Parameter(
     *        name="cover_id",
     *        in="path",
     *        description="ID of the target book cover",
     *        required=true,
     *        type="integer",
     *        format="int64",
     *        minimum=1.0
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Book object that needs to be added to the store",
     *         required=true,
     *         minimum=1.0,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="is_main",
     *                      type="boolean"
     *                  )
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid Data received",
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Book or Book cover not found by id",
     *     ),
     *     @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param int $id
     * @param int $cover_id
     * @return Response
     */
    public function replaceCover(\Silex\Application $app, Request $req, $id, $cover_id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            $bookCover = $app['orm.em']->find('doctrine:BookCovers', $cover_id);
            if (!($book instanceof Entities\Book && $bookCover instanceof Entities\BookCovers)) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $data = $this->getNotEmptyBody($req);
            $app['orm.em']->getRepository('doctrine:Book')->updateBookCover($bookCover, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Delete(path="/book/{id}/covers/{cover_id}",
     *   summary="Delete book cover by ID",
     *   operationId="removeCover",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the book that needs to be deleted",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Parameter(
     *     name="cover_id",
     *     in="path",
     *     description="ID of the target book cover",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book or Book cover not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function removeCover(\Silex\Application $app, $id, $cover_id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            $bookCover = $app['orm.em']->find('doctrine:BookCovers', $cover_id);
            if (!($book instanceof Entities\Book && $bookCover instanceof Entities\BookCovers)) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:Book')->deleteBookCover($bookCover);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Get(
     *     path="/book/{id}/votes",
     *     summary="Compute rating of book based on user votes",
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
     *         description="vote scalar value",
     *         @SWG\Schema(
     *             type="float"
     *         )
     *     ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book or Book cover not found"),
     * )
     * @param \Silex\Application $app
     * @param int $id
     * @return ErrorResponse|Response
     */
    public function getRating(\Silex\Application $app, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $rating = $app['orm.em']->getRepository('doctrine:Votes')->getRating($book);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response($app['serializer']->serialize($rating, RESPONSE_FORMAT), 200);
    }

    /**
     * @SWG\Put(
     *     path="/book/{id}/votes",
     *     operationId="replace",
     *     summary="Allows user to re-vote for book",
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Vote object with values 1..5",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="vote",
     *                      type="integer"
     *                  )
     *         ),
     *     ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param $id
     * @return ErrorResponse|Response
     */
    public function voteForBook(\Silex\Application $app, Request $req, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $data = $this->getNotEmptyBody($req);
            $result = $app['orm.em']->getRepository('doctrine:Votes')->voteForBook($book, $data);
            $status = ($result === VotesRepository::VOTE_CREATED) ? 201 : 200;
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', $status);
    }

    /**
     * @SWG\Delete(path="/book/{id}/votes",
     *   summary="Delete user`s vote only",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the book that needs to be deleted",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function deleteVote(\Silex\Application $app, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:Votes')->deleteVote($book);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Post(
     *     path="/book/{id}/comments",
     *     operationId="addComment",
     *     summary="Add new comment to the book",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Text which will be added as a comment",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="text",
     *                      type="string"
     *                  )
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="comment is created successfully",
     *         @SWG\Schema(
     *           type="object",
     *           additionalProperties={
     *            "contentUri":"string"
     *           }
     *         ),
     *     ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book not found"),
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param $id
     * @return CreatedResponse|ErrorResponse
     */
    public function addComment(\Silex\Application $app, Request $req, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $data = $this->getNotEmptyBody($req);
            $comment = $app['orm.em']->getRepository('doctrine:Comments')->addComment($book, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new CreatedResponse(sprintf("/book/%d/comments/%d", $book->getId(), $comment->getId()));
    }

    /**
     * @SWG\Delete(path="/book/{id}/comments/{comment_id}",
     *   summary="Delete comment by ID",
     *   operationId="removeComment",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of the book that needs to be deleted",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Parameter(
     *     name="comment_id",
     *     in="path",
     *     description="ID of the comment",
     *     required=true,
     *     type="integer",
     *     format="int64",
     *     minimum=1.0
     *   ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book or comment not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param $id
     * @param $comment_id
     * @return ErrorResponse|Response
     */
    public function deleteComment(\Silex\Application $app, $id, $comment_id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            $comment = $app['orm.em']->find('doctrine:Comments', $comment_id);
            if (!($book instanceof Entities\Book && $comment instanceof Entities\Comments)) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $app['orm.em']->getRepository('doctrine:Comments')->deleteComment($comment);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Put(
     *     path="/book/{id}/comments/{comment_id}",
     *     operationId="replaceComment",
     *     summary="Allows user to change his comment",
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *      @SWG\Parameter(
     *         description="ID of comment to fetch",
     *         format="int64",
     *         in="path",
     *         name="comment_id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Comment to replace",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="text",
     *                      type="string"
     *                  )
     *         ),
     *     ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book or comment not found"),
     *   @SWG\Response(response=200, description="success")
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param int $id
     * @param int $comment_id
     * @return CreatedResponse|ErrorResponse
     */
    public function updateComment(\Silex\Application $app, Request $req, $id, $comment_id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            $comment = $app['orm.em']->find('doctrine:Comments', $comment_id);
            if (!($book instanceof Entities\Book && $comment instanceof Entities\Comments)) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $data = $this->getNotEmptyBody($req);
            $app['orm.em']->getRepository('doctrine:Comments')->updateComment($comment, $data);
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 200);
    }

    /**
     * @SWG\Get(
     *     path="/book/{id}/comments",
     *     summary="Retrieve all comments belong to particular book",
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID of the target book",
     *        required=true,
     *        type="integer",
     *        format="int64",
     *        minimum=1.0
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="book comments",
     *         @SWG\Schema(
     *           type="array",
     *           @SWG\Items(ref="#/definitions/Comments")
     *         )
     *     ),
     *     @SWG\Response(response=404, description="Book not found"),
     * )
     * @todo limitation of comments
     * @param \Silex\Application $app
     * @param $id
     * @return ErrorResponse|Response
     */
    public function listComments(\Silex\Application $app, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $comments = $book->getComments();
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response($app['serializer']->serialize($comments, RESPONSE_FORMAT), 200);
    }

    /**
     * @SWG\Get(
     *     path="/book/{id}/comments/{comment_id}",
     *     summary="Compute rating of book based on user votes",
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *      @SWG\Parameter(
     *         description="ID of comment to fetch",
     *         format="int64",
     *         in="path",
     *         name="comment_id",
     *         required=true,
     *         type="integer"
     *     ),
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Response(
     *         response=200,
     *         description="comment info",
     *         @SWG\Schema(ref="#/definitions/Comments")
     *     ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book or Book cover not found"),
     * )
     * @param \Silex\Application $app
     * @param int $id
     * @param int $comment_id
     * @return ErrorResponse|Response
     */
    public function getComment(\Silex\Application $app, $id, $comment_id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            $comment = $app['orm.em']->find('doctrine:Comments', $comment_id);
            if (!($book instanceof Entities\Book && $comment instanceof Entities\Comments)) {
                throw new NotFoundHttpException('Requested resource not found');
            }
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response($app['serializer']->serialize($comment, RESPONSE_FORMAT), 200);
    }

    /**
     * @SWG\Post(
     *     path="/book/{id}/convert",
     *     summary="Convert book to requested format: MOBI, TXT, PDF, EPUB",
     *      @SWG\Parameter(
     *         description="ID of book to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Requested format",
     *         required=true,
     *         @SWG\Schema(
     *                  @SWG\Property(
     *                      property="to",
     *                      type="string"
     *                  )
     *         ),
     *     ),
     *     produces={
     *          "application/json"
     *     },
     *     @SWG\Response(
     *         response=202,
     *         description="Conversion task accepted by the server"
     *     ),
     *   @SWG\Response(response=400, description="Unexpected error occurred"),
     *   @SWG\Response(response=404, description="Book not found"),
     * )
     * @param \Silex\Application $app
     * @param Request $req
     * @param $id
     * @return ErrorResponse|Response
     */
    public function convert(\Silex\Application $app, Request $req, $id)
    {
        try {
            $book = $app['orm.em']->find('doctrine:Book', $id);
            if (!$book instanceof Entities\Book) {
                throw new NotFoundHttpException('Requested resource not found');
            }
            $data = $this->getNotEmptyBody($req);
            $task = new ConvertTask();
            $task->setBook($book)
                ->setDataFormat($data);
            $task->send();
        } catch (NotFoundHttpException $ne) {
            return new ErrorResponse($ne->getMessage(), 404);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new Response('', 202);
    }

}