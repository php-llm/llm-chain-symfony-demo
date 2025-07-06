<?php

declare(strict_types=1);

namespace App\Blog;

use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Indexer;

final readonly class Embedder
{
    public function __construct(
        private FeedLoader $loader,
        private Indexer $indexer,
    ) {
    }

    public function embedBlog(): void
    {
        $documents = [];
        foreach ($this->loader->load() as $post) {
            $documents[] = new TextDocument($post->id, $post->toString(), new Metadata($post->toArray()));
        }

        $this->indexer->index($documents);
    }
}
