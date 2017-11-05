<?php

namespace Test\Samples\Domain\Entity;

use PHPUnit\Framework\TestCase;
use Samples\Domain\Entity\Author;

/**
 * Test of class Samples\Domain\Entity\Author
 *
 * @package Test\Samples\Domain\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Author
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
abstract class AuthorTest extends TestCase
{
    /** @var array */
    private $data;

    /** @var Author */
    private $author;

    /**
     * Creates the author object
     *
     * @param array $data
     * @return Author
     */
    protected abstract function createAuthor(array $data);

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->data = [
            'uuid'      => '0c486638-c252-11e7-adf5-99bfc89b6d8c',
            'firstName' => 'Hello',
            'lastName'  => 'World'
        ];

        $this->author = $this->createAuthor($this->data);
    }

    /**
     * Test Author::create() function
     */
    public function testAuthorCreate()
    {
        $this->assertEquals($this->data['uuid'], $this->author->uuid()->toString());
        $this->assertEquals($this->data['firstName'], $this->author->firstName());
        $this->assertEquals($this->data['lastName'], $this->author->lastName());
    }

    /**
     * Test Author::serialize() function
     */
    public function testAuthorSerialize()
    {
        $this->assertEquals($this->data, $this->author->serialize());
    }
}
