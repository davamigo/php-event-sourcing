<?php

namespace Test\Samples\Domain\Command;

use Samples\Domain\Entity\Author as AuthorBase;
use Samples\Domain\Entity\Custom\Author as AuthorCustom;
use Samples\Domain\Command\CreateAuthor;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Samples\Domain\Command\CreateAuthor
 *
 * @package Test\Samples\Domain\Command
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Command_CreateAuthor
 * @group Test_Samples_Domain_Command
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class CreateAuthorTest extends TestCase
{
    /**
     * Test empty constructor of CreateAuthor command
     */
    public function testEmptyConstructor()
    {
        $command = new CreateAuthor();

        $this->assertEquals('command', $command->type());
        $this->assertEquals(CreateAuthor::class, $command->name());
        $this->assertInstanceOf(AuthorBase::class, $command->payload());
    }

    /**
     * Test basic constructor of CreateAuthor command
     */
    public function testBasicConstructor()
    {
        $author = new AuthorCustom();
        $command = new CreateAuthor($author);

        $this->assertEquals($author, $command->payload());
    }

    /**
     * Test serialize of CreateAuthor command
     */
    public function testSerialize()
    {
        $author = new AuthorCustom(
            '068332f0-9465-47c4-a7c2-402e9ccabfdc',
            'author_first_name',
            'author_last_name'
        );

        $command = new CreateAuthor(
            $author,
            [ 'routing_key' => CreateAuthor::class ],
            \DateTime::createFromFormat('YmdHis', '20100102100001'),
            '6d0350c9-888c-4234-8410-afc516cd82a0'
        );

        $expected = [
            'uuid'      => '6d0350c9-888c-4234-8410-afc516cd82a0',
            'type'      => 'command',
            'name'      => CreateAuthor::class,
            'createdAt' => '2010-01-02T10:00:01+00:00',
            'payload'   => [
                'uuid'      => '068332f0-9465-47c4-a7c2-402e9ccabfdc',
                'firstName' => 'author_first_name',
                'lastName'  => 'author_last_name'
            ],
            'metadata'  => [
                'routing_key' => CreateAuthor::class
            ]
        ];

        $result = $command->serialize();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test create of CreateAuthor command
     */
    public function testCreate()
    {
        $data = [
            'uuid'      => '910fde66-84f7-46f2-83aa-eded2dbd593b',
            'type'      => 'command',
            'name'      => CreateAuthor::class,
            'createdAt' => '2004-02-04T09:00:00+00:00',
            'payload'   => [
                'uuid'      => '8b171bf9-f84a-474d-8bb1-0e0e8d988ef7',
                'firstName' => 'J.R.R',
                'lastName'  => 'Tolkien'
            ],
            'metadata'  => [
                'entity_class' => AuthorCustom::class,
                'command_class'  => CreateAuthor::class
            ]
        ];

        /** @var CreateAuthor $command */
        $command = CreateAuthor::create($data);

        $this->assertEquals('910fde66-84f7-46f2-83aa-eded2dbd593b', $command->uuid()->toString());
        $this->assertEquals('command', $command->type());
        $this->assertEquals(CreateAuthor::class, $command->name());
        $this->assertEquals('20040204090000', $command->createdAt()->format('YmdHis'));

        /** @var AuthorBase $entity */
        $entity = $command->payload();
        $this->assertEquals('8b171bf9-f84a-474d-8bb1-0e0e8d988ef7', $entity->uuid()->toString());
        $this->assertEquals('J.R.R', $entity->firstName());
        $this->assertEquals('Tolkien', $entity->lastName());

        $metadata = $command->metadata();
        $this->assertCount(2, $metadata);
        $this->assertEquals(['entity_class', 'command_class'], array_keys($metadata));
        $this->assertEquals([AuthorCustom::class, CreateAuthor::class], array_values($metadata));
    }
}
