<?php

declare(strict_types=1);

namespace App\Tests\Rag\Blog;

use App\Rag\Blog\Post;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Post::class)]
final class PostTest extends TestCase
{
    public function testPostToString(): void
    {
        $post = new Post(
            Uuid::v4(),
            'Hello, World!',
            'https://example.com/hello-world',
            'This is a test description.',
            'This is a test post.',
            'John Doe',
            new \DateTimeImmutable('2024-12-08 09:39:00'),
        );

        $expected = <<<TEXT
            Title: Hello, World!
            From: John Doe on 2024-12-08
            Description: This is a test description.
            This is a test post.
            TEXT;

        self::assertSame($expected, $post->toString());
    }

    public function testPostToArray(): void
    {
        $id = Uuid::v4();
        $post = new Post(
            $id,
            'Hello, World!',
            'https://example.com/hello-world',
            'This is a test description.',
            'This is a test post.',
            'John Doe',
            new \DateTimeImmutable('2024-12-08 09:39:00'),
        );

        $expected = [
            'id' => $id->toRfc4122(),
            'title' => 'Hello, World!',
            'link' => 'https://example.com/hello-world',
            'description' => 'This is a test description.',
            'content' => 'This is a test post.',
            'author' => 'John Doe',
            'date' => '2024-12-08',
        ];

        self::assertSame($expected, $post->toArray());
    }
}
