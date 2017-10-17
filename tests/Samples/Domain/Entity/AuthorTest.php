<?php

namespace Test\Samples\Domain\Entity;

use Davamigo\Domain\Core\UuidObj;
use PHPUnit\Framework\TestCase;
use Samples\Domain\Entity\Author;

/**
 * Test of class Samples\Domain\Entity\Author
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Author
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class AuthorTest extends TestCase
{
    /** @var array */
    private $authorData;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->authorData = [
            'uuid' => UuidObj::create(),
            'firstName' => 'Hello',
            'lastName' => 'World'
        ];
    }

    /**
     * Test Author::create() function
     */
    public function testAuthorCreate()
    {
        $author = Author::create($this->authorData);

        $this->assertEquals($this->authorData['uuid'], $author->uuid());
        $this->assertEquals($this->authorData['firstName'], $author->firstName());
        $this->assertEquals($this->authorData['lastName'], $author->lastName());
    }

    /**
     * Test Author::serialize() function
     */
    public function testAuthorSerialize()
    {
        $author = Author::create($this->authorData);

        $expected = $this->authorData;
        $expected['uuid'] = $expected['uuid']->toString();

        $this->assertEquals($expected, $author->serialize());
    }
}
