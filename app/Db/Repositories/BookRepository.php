<?php

namespace Bookmarker\Db\Repositories;

use Bookmarker\MetadataProcessor\MetadataFactory;
use Bookmarker\FileDrivers\LocalDriver;
use Bookmarker\Db\Entities;
use Bookmarker\Registry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class BookRepository
 * @package Bookmarker\Db\Repository
 */
class BookRepository extends Repository
{

    /**
     * @param UploadedFile $uploadedFile
     * @return int
     * @throws \ErrorException
     */
    public function addBook(UploadedFile $uploadedFile)
    {
        $metadataFactory = new MetadataFactory();
        $metadata = $metadataFactory($uploadedFile);
        $em = $this->getEntityManager();
        $isStored = $metadata->setFileDriver(new LocalDriver($uploadedFile->getRealPath(), true))->storeFile();
        if (!$isStored) {
            throw new \ErrorException('Cannot store file');
        }
        $fileInfo = $metadata->getFileDriver()->getFileInfo();
        $bookEntity = new Entities\Book();
        $app = Registry::get('app');
        $user = $app['security.token_storage']->getToken()->getUser();
        $bookEntity->setExt($fileInfo['extension'])
            ->setFilePath($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['basename'])
            ->setTitle($fileInfo['filename'])
            ->setMime($metadata->getFileDriver()->getMimeType())
            ->setUser($user);
        $em->persist($bookEntity);
        $em->flush();
        return $bookEntity->getId();
    }

    /**
     * @param Entities\Book $bookEntity
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function updateBook(Entities\Book $bookEntity, array $params = array())
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $this->fillBookEntity($bookEntity, $params);
            $em->persist($bookEntity);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param Entities\Book $bookEntity
     * @throws \Exception
     */
    public function deleteBook(Entities\Book $bookEntity)
    {
        try {
            $delCallback = function($fileName) {
                try {
                    $fileDriver = new LocalDriver($fileName, true);
                    $fileDriver->delete();
                    return true;
                } catch(\Exception $e) {
                    return false;
                }
            };
            $delCallback($bookEntity->getFilePath());
            $bookEntity->getBookCovers()->forAll(function($index, Entities\BookCovers $cover) use ($delCallback) {
                return $delCallback($cover->getImagePath());
            });
        } catch(\Exception $e) {
            throw $e;
        } finally {
            $em = $this->getEntityManager();
            $em->remove($bookEntity);
            $em->flush();
        }

    }

    /**
     * @param Entities\Book $book
     * @param array $params
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function fillBookEntity(Entities\Book $book, array $params = array())
    {
        $em = $this->getEntityManager();
        if (array_key_exists('title', $params)) {
            $book->setTitle($params['title']);
        }
        if (array_key_exists('year', $params)) {
            $book->setYear($params['year']);
        }
        if (array_key_exists('lang', $params)) {
            $book->setLang($params['lang']);
        }
        if (isset($params['genre_id']) && $params['genre_id'] > 0) {
            $genreEntity = $em->find('doctrine:Genre', $params['genre_id']);
            if ($genreEntity instanceof Entities\Genre) {
                $book->setGenre($genreEntity);
            } else {
                throw new \InvalidArgumentException('genre_id is not exist');
            }
        }
        if (isset($params['author_ids']) && is_array($params['author_ids'])) {
            // remove exist relations
            foreach ($book->getAuthors() as $existAuthor) {
                $book->removeAuthor($existAuthor);
            }
            $authorEntities = $em->getRepository('doctrine:Author')->findById($params['author_ids']);
            if (count($authorEntities) < count($params['author_ids'])) {
                throw new \InvalidArgumentException('some author_ids are not exist');
            }
            foreach ($authorEntities as $authorEntity) {
                $book->addAuthor($authorEntity);
            }
        }
        $app = Registry::get('app');
        $book->setUser($app['security.token_storage']->getToken()->getUser()->getUserEntity());
    }

    /**
     * @param Entities\Book $book
     * @param UploadedFile $uploadedFile
     * @return Entities\BookCovers
     * @throws \Exception
     */
    public function addBookCover(Entities\Book $book, UploadedFile $uploadedFile)
    {
        $driver = new LocalDriver($uploadedFile->getRealPath(), true);
        if (!$driver->store()) {
            throw new \Exception('Failed to store book cover');
        }
        $info = $driver->getFileInfo();
        $bookCover = new Entities\BookCovers();
        $bookCover->setBook($book)
            ->setImagePath($info['dirname'] . '/' . $info['basename']);
        $this->getEntityManager()->persist($bookCover);
        $this->getEntityManager()->flush();
        return $bookCover;
    }

    /**
     * @param Entities\BookCovers $cover
     * @throws \Exception
     */
    public function deleteBookCover(Entities\BookCovers $cover)
    {
        $em = $this->getEntityManager();
        try {
            $fileName = basename($cover->getImagePath());
            $fileDriver = new LocalDriver($fileName);
            $fileDriver->delete();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $em->remove($cover);
            $em->flush();
        }
    }

    /**
     * @param Entities\BookCovers $bookCover
     * @param array $params
     */
    public function updateBookCover(Entities\BookCovers $bookCover, array $params)
    {
        $em = $this->getEntityManager();
        if (array_key_exists('is_main', $params)) {
            $bookCover->setIsMain($params['is_main']);
            $em->persist($bookCover);
            $em->flush();
        }
    }

}