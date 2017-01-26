<?php

namespace Bookmarker\Db\Repositories;

use Bookmarker\MetadataProcessor\MetadataFactory;
use Bookmarker\FileDrivers\LocalDriver;
use Bookmarker\Db\Entities;
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
        $bookEntity->setExt($fileInfo['extension'])
            ->setFilePath($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['basename'])
            ->setTitle($fileInfo['filename'])
            ->setMime($metadata->getFileDriver()->getMimeType());
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
        } catch(\Exception $e) {
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
            $fileName = basename($bookEntity->getFilePath());
            $fileDriver = new LocalDriver($fileName);
            $fileDriver->delete();
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
    }

}