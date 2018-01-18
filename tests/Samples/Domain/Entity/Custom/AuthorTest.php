<?php

namespace Test\Samples\Domain\Entity\Custom;

use Samples\Domain\Entity\Custom\Author;
use Test\Samples\Domain\Entity\AuthorTest as BaseAuthorTest;

/**
 * Test of class Samples\Domain\Entity\Author
 *
 * @package Test\Samples\Domain\Entity\Custom
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Author_Custom
 * @group Test_Samples_Domain_Entity_Author
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class AuthorTest extends BaseAuthorTest
{
    /**
     * Creates the author object
     *
     * @param array $data
     * @return Author
     */
    protected function createAuthor(array $data)
    {
        return Author::create($data);
    }
}
