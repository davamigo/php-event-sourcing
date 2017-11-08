<?php

namespace Test\Samples\Domain\Entity\Auto;

use Samples\Domain\Entity\Auto\Book;
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
