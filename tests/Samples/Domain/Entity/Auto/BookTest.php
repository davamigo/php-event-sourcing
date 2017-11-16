<?php

namespace Test\Samples\Domain\Entity\Auto;

use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidObj;
use Samples\Domain\Entity\Auto\Author;
use Samples\Domain\Entity\Auto\Book;
use Samples\Domain\Entity\Auto\Publisher;
use Test\Samples\Domain\Entity\BookTest as BaseBookTest;

/**
 * Test of class Samples\Domain\Entity\Book
 *
 * @package Test\Samples\Domain\Entity\Auto
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Book_Auto
 * @group Test_Samples_Domain_Entity_Book
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class BookTest extends BaseBookTest
{
    /**
     * Test Book::__construct() function
     */
    public function testEmptyConstructor()
    {
        $book = new Book();
        $this->assertNull($book->name());
        $this->assertInstanceOf(Uuid::class, $book->uuid());
        $this->assertInstanceOf(Publisher::class, $book->publisher());
        $this->assertInstanceOf(\DateTime::class, $book->releaseDate());
        $this->assertCount(1, $book->authors());
        $this->assertInstanceOf(Author::class, reset($book->authors()));
    }

    /**
     * Test Book::__construct() function
     */
    public function testNonEmptyConstructor()
    {
        $uuid = UuidObj::create();
        $publisher = new Publisher();
        $dateTime = new \DateTime();
        $author = new Author();

        $book = new Book(
            $uuid,
            '_author_name_',
            $publisher,
            $dateTime,
            [ $author ]
        );
        $this->assertEquals($uuid, $book->uuid());
        $this->assertEquals('_author_name_', $book->name());
        $this->assertEquals($publisher, $book->publisher());
        $this->assertEquals($dateTime, $book->releaseDate());
        $this->assertCount(1, $book->authors());
        $this->assertEquals($author, reset($book->authors()));
    }

    /**
     * Creates the Book object
     *
     * @param array $data
     * @return Book
     */
    protected function createBook(array $data)
    {
        return Book::create($data);
    }
}
