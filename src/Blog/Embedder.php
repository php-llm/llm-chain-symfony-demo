<?php

declare(strict_types=1);

namespace App\Blog;

use Codewithkyrian\ChromaDB\Client;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\PlatformInterface;

final readonly class Embedder
{
    public function __construct(
        private Loader $loader,
        private PlatformInterface $platform,
        private Client $chromaClient,
    ) {
    }

    public function embedBlog(): void
    {
        $posts = $this->loader->load();
        $vectors = $this->createEmbeddings($posts);
        $this->pushToChromaDB($posts, $vectors);
    }

    /**
     * @param Post[] $posts
     *
     * @return Vector[]
     */
    private function createEmbeddings(array $posts): array
    {
        $texts = array_map(fn (Post $post) => $post->toString(), $posts);
        $response = $this->platform->request(new Embeddings(), $texts);

        assert($response instanceof AsyncResponse);
        $response = $response->unwrap();
        assert($response instanceof VectorResponse);

        return $response->getContent();
    }

    /**
     * @param Post[]   $posts
     * @param Vector[] $vectors
     */
    private function pushToChromaDB(array $posts, array $vectors): void
    {
        $collection = $this->chromaClient->getOrCreateCollection('symfony_blog');

        $ids = array_map(fn (Post $post) => $post->id, $posts);
        $vectors = array_map(fn (Vector $vector) => $vector->getData(), $vectors);

        $collection->upsert($ids, $vectors, $posts);
    }
}
