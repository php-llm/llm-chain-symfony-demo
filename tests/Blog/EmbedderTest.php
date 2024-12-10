<?php

declare(strict_types=1);

namespace App\Tests\Blog;

use App\Blog\Embedder;
use App\Blog\Loader;
use App\Blog\Post;
use Codewithkyrian\ChromaDB\Client;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use PhpLlm\LlmChain\PlatformInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

#[CoversClass(Embedder::class)]
#[UsesClass(Loader::class)]
#[UsesClass(Post::class)]
final class EmbedderTest extends TestCase
{
    public function testEmbedBlog(): void
    {
        $response = MockResponse::fromFile(__DIR__.'/fixtures/blog.rss');
        $client = new MockHttpClient([$response, $response]);
        $loader = new Loader($client);
        $platform = $this->createMock(PlatformInterface::class);
        $chromaClient = $this->createMock(Client::class);
        $posts = $loader->load();
        $vectors = [
            new Vector([0.1, 0.2, 0.3]),
            new Vector([0.4, 0.5, 0.6]),
            new Vector([0.7, 0.8, 0.9]),
            new Vector([1.0, 1.1, 1.2]),
            new Vector([1.3, 1.4, 1.5]),
            new Vector([1.6, 1.7, 1.8]),
            new Vector([1.9, 2.0, 2.1]),
            new Vector([2.2, 2.3, 2.4]),
            new Vector([2.5, 2.6, 2.7]),
            new Vector([2.8, 2.9, 3.0]),
        ];
        $platform
            ->method('request')
            ->willReturn($this->createAsyncResponse($vectors));

        $collection = $this->createMock(CollectionResource::class);
        $chromaClient
            ->expects($this->once())
            ->method('getOrCreateCollection')
            ->with('symfony_blog')
            ->willReturn($collection);

        $collection
            ->expects($this->once())
            ->method('upsert')
            ->with(
                array_map(fn (Post $post) => $post->id, $posts),
                array_map(fn (Vector $vector) => $vector->getData(), $vectors),
                $posts,
            );

        $embedder = new Embedder($loader, $platform, $chromaClient);
        $embedder->embedBlog();
    }

    /**
     * @param Vector[] $vectors
     */
    private function createAsyncResponse(array $vectors): AsyncResponse
    {
        $converter = new class($vectors) implements ResponseConverter {
            /**
             * @param Vector[] $vectors
             */
            public function __construct(private readonly array $vectors)
            {
            }

            public function supports(Model $model, object|array|string $input): bool
            {
                return true;
            }

            public function convert(HttpResponse $response, array $options = []): LlmResponse
            {
                return new VectorResponse(...$this->vectors);
            }
        };

        return new AsyncResponse($converter, new MockResponse());
    }
}
