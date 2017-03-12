<?php
/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 3/8/17
 * Time: 9:48 PM
 */

namespace Bookmarker\Jobs\Tasks;


use Bookmarker\Db\Entities\Book;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Pheanstalk\Pheanstalk;

class ConvertTask implements Task
{

    /**
     * @var string
     */
    protected $format;

    /**
     * @var Book
     */
    protected $book;

    /**
     * @param array $data
     * @return $this
     */
    public function setDataFormat(array $data)
    {
        if (!isset($data['to'])) {
            throw new InvalidArgumentException('Incorrect format received');
        }
        $this->format = $data['to'];
        return $this;
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function setBook(Book $book)
    {
        $this->book = $book;
        return $this;
    }

    /**
     * @throws \ErrorException
     */
    public function send()
    {
        $filesSet = $this->book->getFiles();
        if ($filesSet->isEmpty()) {
            throw new \ErrorException('There are no files available for this book');
        }

        $pheanstalk = new Pheanstalk('172.17.0.1');
        $pheanstalk
            ->useTube('testtube')
            ->put($this->buildCommand());
    }

    /**
     * @param $from
     * @param $to
     * @return string
     */
    protected function buildCommand()
    {
        return json_encode(array(
            'book_id' => $this->book->getId(),
            'to' => $this->format
        ));
    }
}