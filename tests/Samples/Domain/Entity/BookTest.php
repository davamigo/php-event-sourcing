<?php

namespace Test\Samples\Domain\Entity;

use PHPUnit\Framework\TestCase;
use Samples\Domain\Entity\Book;

/**
 * Test of class Samples\Domain\Entity\Book
 *
 * @package Test\Samples\Domain\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Book
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
abstract class BookTest extends TestCase
{
    /** @var array */
    private $publisherData;

    /** @var array */
    private $author1Data;

    /** @var array */
    private $author2Data;

    /** @var array */
    private $bookData;

    /**
     * Creates the author object
     *
     * @param array $data
     * @return Book
     */
    protected abstract function createBook(array $data);

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->publisherData = [
            'uuid' => 'ddac7ace-c497-11e7-9d07-db8449c8f4e1',
            'name' => 'Harper Collins'
        ];

        $this->author1Data = [
            'uuid'      => '6a90ec18-c498-11e7-b7b2-c554a13e247b',
            'firstName' => 'J. R. R',
            'lastName'  => 'Tolkien'
        ];

        $this->author2Data = [
            'uuid'      => '6a90feb0-c498-11e7-a6b1-f1664fa8b655',
            'firstName' => 'Rob',
            'lastName'  => 'Inglis'
        ];

        $this->bookData = [
            'uuid'          => 'ddac4fc2-c497-11e7-b1b6-051f92d45a9c',
            'name'          => 'The Lord of the Rings',
            'publisher'     => $this->publisherData,
            'releaseDate'   => '2000-01-01T10:00:00+00:00',
            'authors'       => [
                $this->author1Data,
                $this->author2Data,
            ]
        ];
    }

    /**
     * Test Book::__construct() function
     */
    public function testConstructorAndGetters()
    {
        $book = $this->createBook($this->bookData);

        $this->assertEquals($this->bookData['uuid'], $book->uuid()->toString());
        $this->assertEquals($this->bookData['name'], $book->name());
        $this->assertEquals($this->bookData['releaseDate'], $book->releaseDate()->format(\DateTime::RFC3339));

        $this->assertEquals($this->publisherData['uuid'], $book->publisher()->uuid()->toString());
        $this->assertEquals($this->publisherData['name'], $book->publisher()->name());

        $this->assertCount(2, $book->authors());

        $authors = $book->authors();
        $author1 = $authors[0];
        $author2 = $authors[1];

        $this->assertEquals($this->author1Data['uuid'], $author1->uuid()->toString());
        $this->assertEquals($this->author1Data['firstName'], $author1->firstName());
        $this->assertEquals($this->author1Data['lastName'], $author1->lastName());

        $this->assertEquals($this->author2Data['uuid'], $author2->uuid()->toString());
        $this->assertEquals($this->author2Data['firstName'], $author2->firstName());
        $this->assertEquals($this->author2Data['lastName'], $author2->lastName());
    }

    /**
     * Test Book::serialize() function
     */
    public function testSerialize()
    {
        $book = $this->createBook($this->bookData);

        $result = $book->serialize();
        $this->assertEquals($this->bookData, $result);
    }
}
