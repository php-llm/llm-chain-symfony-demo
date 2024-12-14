<?php

declare(strict_types=1);

namespace App\Rag\Blog;

use Symfony\Component\Uid\Uuid;

final readonly class Post
{
    public function __construct(
        public Uuid $id,
        public string $title,
        public string $link,
        public string $description,
        public string $content,
        public string $author,
        public \DateTimeImmutable $date,
    ) {
    }

    public function toString(): string
    {
        return <<<TEXT
            Title: {$this->title}
            From: {$this->author} on {$this->date->format('Y-m-d')}
            Description: {$this->description}
            {$this->content}
            TEXT;
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     link: string,
     *     description: string,
     *     content: string,
     *     author: string,
     *     date: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
            'title' => $this->title,
            'link' => $this->link,
            'description' => $this->description,
            'content' => $this->content,
            'author' => $this->author,
            'date' => $this->date->format('Y-m-d'),
        ];
    }
}
