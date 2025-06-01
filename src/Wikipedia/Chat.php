<?php

declare(strict_types=1);

namespace App\Wikipedia;

use PhpLlm\LlmChain\Chain\ChainInterface;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

final class Chat
{
    private const SESSION_KEY = 'wikipedia-chat';

    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'llm_chain.chain.wikipedia')]
        private readonly ChainInterface $chain,
    ) {
    }

    public function loadMessages(): MessageBag
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, new MessageBag());
    }

    public function submitMessage(string $message): void
    {
        $messages = $this->loadMessages();

        $messages->add(Message::ofUser($message));
        $response = $this->chain->call($messages);

        assert($response instanceof TextResponse);

        $messages->add(Message::ofAssistant($response->getContent()));

        $this->saveMessages($messages);
    }

    public function reset(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    private function saveMessages(MessageBag $messages): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $messages);
    }
}
