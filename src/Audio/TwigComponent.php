<?php

declare(strict_types=1);

namespace App\Audio;

use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('audio')]
final class TwigComponent
{
    use DefaultActionTrait;

    public function __construct(
        private readonly Chat $chat,
    ) {
    }

    /**
     * @return MessageInterface[]
     */
    public function getMessages(): array
    {
        return $this->chat->loadMessages()->withoutSystemMessage()->getMessages();
    }

    #[LiveAction]
    public function submit(#[LiveArg] string $audio): void
    {
        $this->chat->say($audio);
    }

    #[LiveAction]
    public function reset(): void
    {
        $this->chat->reset();
    }
}
