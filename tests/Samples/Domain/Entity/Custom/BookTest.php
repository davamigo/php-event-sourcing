<?php

namespace Test\Samples\Domain\Entity\Custom;

use PHPUnit\Framework\TestCase;
use Samples\Domain\Entity\Custom\Author;
use Samples\Domain\Entity\Custom\Book;
use Samples\Domain\Entity\Custom\Publisher;

/**
 * Test of class Samples\Domain\Entity\Book
 *
 * @package Test\Samples\Domain\Entity\Custom
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
    private $author1;

    /** @var Author */
    private $author2;

    /** @var Book */
    private $book;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->date = new \DateTime();
        $this->publisher = new Publisher(null, '_the_publisher_');
        $this->author1 = new Author(null, '_the_author1_name_', '_the_author1_surname_');
        $this->author2 = new Author(null, '_the_author2_name_', '_the_author2_surname_');
        $this->book = new Book(
            null,
            '_the_book_name_',
            $this->publisher,
            $this->date,
            [ $this->author1, $this->author2 ]
        );
    }

    /**
     * Test Book::__construct() function
     */
    public function testConstructorAndGetters()
    {
        $this->assertEquals('_the_book_name_', $this->book->name());
        $this->assertEquals($this->publisher, $this->book->publisher());
        $this->assertEquals($this->date, $this->book->releaseDate());
        $this->assertContains($this->author1, $this->book->authors());
        $this->assertContains($this->author2, $this->book->authors());
    }

    /**
     * Test Book::serialize() function
     */
    public function testSerialize()
    {
        $expected = [
            'uuid' => $this->book->uuid()->toString(),
            'name' => '_the_book_name_',
            'releaseDate' => $this->date->format(\DateTime::RFC3339),
            'publisher' => [
                'uuid' => $this->publisher->uuid()->toString(),
                'name' => '_the_publisher_'
            ],
            'authors' => [[
                'uuid' => $this->author1->uuid()->toString(),
                'firstName' => '_the_author1_name_',
                'lastName' => '_the_author1_surname_'
            ], [
                'uuid' => $this->author2->uuid()->toString(),
                'firstName' => '_the_author2_name_',
                'lastName' => '_the_author2_surname_'
            ]]
        ];

        $result = $this->book->serialize();
        $this->assertEquals($expected, $result);
    }
}
