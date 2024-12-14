<?php

declare(strict_types=1);

namespace App\Rag\Twig;

use App\Rag\Chat\Rag;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('rag')]
final class RagComponent
{
    use DefaultActionTrait;

    public function __construct(
        private readonly Rag $chat,
    ) {
    }

    public function getMessages(): MessageBag
    {
        return $this->chat->loadMessages()->withoutSystemMessage();
    }

    #[LiveAction]
    public function submit(#[LiveArg] string $message): void
    {
        $this->chat->submitMessage($message);
    }

    #[LiveAction]
    public function reset(): void
    {
        $this->chat->reset();
    }
}
