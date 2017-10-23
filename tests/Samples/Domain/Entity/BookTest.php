<?php

namespace Samples\Domain\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Test of class Samples\Domain\Entity\Book
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Book
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class BookTest extends TestCase
{
    /** @var \DateTime */
    private $date;

    /** @var Publisher */
    private $publisher;

    /** @var Author */
    private $author;

    /** @var Book */
    private $book;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->date = new \DateTime();
        $this->publisher = new Publisher(null, "_the_publisher_");
        $this->author = new Author(null, "_the_author_name_", "_the_author_surname_");
        $this->book = new Book(null, "_the_book_name_", $this->publisher, [ $this->author ], $this->date);
    }

    /**
     * Test Book::__construct() function
     */
    public function testConstructorAndGetters()
    {
        $this->assertEquals($this->publisher, $this->book->publisher());
        $this->assertContains($this->author, $this->book->authors());
        $this->assertEquals($this->date, $this->book->releaseDate());
    }

    /**
     * Test Book::serialize() function
     */
    public function testSerialize()
    {
        $expected = [
            'uuid' => $this->book->uuid()->toString(),
            'name' => $this->book->name(),
            'releaseDate' => $this->date->format(\DateTime::RFC3339),
            'publisher' => [
                'uuid' => $this->publisher->uuid()->toString(),
                'name' => $this->publisher->name()
            ],
            'authors' => [[
                'uuid' => $this->author->uuid()->toString(),
                'firstName' => $this->author->firstName(),
                'lastName' => $this->author->lastName()
            ]]
        ];

        $result = $this->book->serialize();
        $this->assertEquals($expected, $result);
    }
}
