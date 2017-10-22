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
    /**
     * Test Book::serialize() function
     */
    public function testSerialize()
    {
        $publisher = new Publisher(null, "_the_publisher_");
        $author = new Author(null, "_the_author_name_", "_the_author_surname_");
        $book = new Book(null, "_the_book_name_", $publisher, [ $author ], $date);
        $date = new \DateTime();

        $expected = [
            'uuid' => $book->uuid()->toString(),
            'name' => $book->name(),
            'releaseDate' => $date->format(\DateTime::RFC3339),
            'publisher' => [
                'uuid' => $publisher->uuid()->toString(),
                'name' => $publisher->name()
            ],
            'authors' => [[
                'uuid' => $author->uuid()->toString(),
                'firstName' => $author->firstName(),
                'lastName' => $author->lastName()
            ]]
        ];

        $result = $book->serialize();
        $this->assertEquals($expected, $result);
    }
}
