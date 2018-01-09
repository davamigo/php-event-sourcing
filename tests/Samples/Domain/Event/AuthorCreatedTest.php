<?php

namespace Test\Samples\Domain\Event;

use Samples\Domain\Entity\Author as AuthorBase;
use Samples\Domain\Entity\Custom\Author as AuthorCustom;
use Samples\Domain\Event\AuthorCreated;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Samples\Domain\Event\AuthorCreated
 *
 * @package Test\Samples\Domain\Event
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Event_AuthorCreated
 * @group Test_Samples_Domain_Event
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class AuthorCreatedTest extends TestCase
{
    /**
     * Test empty constructor of AuthorCreated event
     */
    public function testEmptyConstructor()
    {
        $event = new AuthorCreated();

        $this->assertEquals('event', $event->type());
        $this->assertEquals(AuthorCreated::class, $event->name());
        $this->assertInstanceOf(AuthorBase::class, $event->payload());
    }

    /**
     * Test basic constructor of AuthorCreated event
     */
    public function testBasicConstructor()
    {
        $author = new AuthorCustom();
        $event = new AuthorCreated($author);

        $this->assertEquals($author, $event->payload());
    }

    /**
     * Test serialize of AuthorCreated event
     */
    public function testSerialize()
    {
        $author = new AuthorCustom(
            '068332f0-9465-47c4-a7c2-402e9ccabfdc',
            'author_first_name',
            'author_last_name'
        );

        $event = new AuthorCreated(
            $author,
            [],
            \DateTime::createFromFormat('YmdHis', '20100102100001'),
            '6d0350c9-888c-4234-8410-afc516cd82a0'
        );
        $event->setTopic('101');
        $event->setRoutingKey('102');

        $expected = [
            'uuid'          => '6d0350c9-888c-4234-8410-afc516cd82a0',
            'type'          => 'event',
            'action'        => 'insert',
            'name'          => AuthorCreated::class,
            'createdAt'     => '2010-01-02T10:00:01+00:00',
            'payload'       => [
                'uuid'          => '068332f0-9465-47c4-a7c2-402e9ccabfdc',
                'firstName'     => 'author_first_name',
                'lastName'      => 'author_last_name'
            ],
            'metadata'      => [
                'topic'         => '101',
                'routing_key'   => '102'
            ]
        ];

        $result = $event->serialize();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test create of AuthorCreated event
     */
    public function testCreate()
    {
        $data = [
            'uuid'          => '910fde66-84f7-46f2-83aa-eded2dbd593b',
            'type'          => 'event',
            'name'          => AuthorCreated::class,
            'createdAt'     => '2004-02-04T09:00:00+00:00',
            'payload'       => [
                'uuid'          => '8b171bf9-f84a-474d-8bb1-0e0e8d988ef7',
                'firstName'     => 'J.R.R',
                'lastName'      => 'Tolkien'
            ],
            'metadata'      => [
                'entityClass'   => AuthorCustom::class,
                'eventClass'    => AuthorCreated::class,
                'topic'         => AuthorBase::class,
                'routing_key'   => 'insert'
            ]
        ];

        /** @var AuthorCreated $event */
        $event = AuthorCreated::create($data);

        $this->assertEquals('910fde66-84f7-46f2-83aa-eded2dbd593b', $event->uuid()->toString());
        $this->assertEquals('event', $event->type());
        $this->assertEquals(AuthorCreated::class, $event->name());
        $this->assertEquals('20040204090000', $event->createdAt()->format('YmdHis'));

        /** @var AuthorBase $entity */
        $entity = $event->payload();
        $this->assertEquals('8b171bf9-f84a-474d-8bb1-0e0e8d988ef7', $entity->uuid()->toString());
        $this->assertEquals('J.R.R', $entity->firstName());
        $this->assertEquals('Tolkien', $entity->lastName());

        $expected = [
            'entityClass'   => AuthorCustom::class,
            'eventClass'    => AuthorCreated::class,
            'topic'         => AuthorBase::class,
            'routing_key'   => 'insert'
        ];

        $this->assertEquals($expected, $event->metadata());
    }
}
