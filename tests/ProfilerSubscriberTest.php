<?php

declare(strict_types=1);

namespace App\Tests;

use App\Kernel;
use App\ProfilerSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(ProfilerSubscriber::class)]
final class ProfilerSubscriberTest extends TestCase
{
    #[DataProvider('provideInvalidRequests')]
    public function testAjaxReplaceHeaderNotSet(int $requestType, bool $debug, bool $isXmlHttpRequest): void
    {
        $request = new Request();
        if ($isXmlHttpRequest) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }

        $response = new Response();
        $event = new ResponseEvent($this->createMock(Kernel::class), new Request(), $requestType, $response);

        $subscriber = new ProfilerSubscriber($debug);
        $subscriber->onKernelResponse($event);

        self::assertFalse($response->headers->has('Symfony-Debug-Toolbar-Replace'));
    }

    /**
     * @return iterable<array{int, bool, bool}>
     */
    public static function provideInvalidRequests(): iterable
    {
        yield 'sub request, not debug, not XHR' => [HttpKernelInterface::SUB_REQUEST, false, false];
        yield 'sub request, not debug, XHR' => [HttpKernelInterface::SUB_REQUEST, false, true];
        yield 'sub request, debug, XHR' => [HttpKernelInterface::SUB_REQUEST, true, true];
        yield 'main request, not debug, not XHR' => [HttpKernelInterface::MAIN_REQUEST, false, false];
        yield 'main request, debug, not XHR' => [HttpKernelInterface::MAIN_REQUEST, true, false];
    }

    public function testAjaxReplaceHeaderOnEnabledAndXHR(): void
    {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $response = new Response();
        $event = new ResponseEvent($this->createMock(Kernel::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $subscriber = new ProfilerSubscriber(true);
        $subscriber->onKernelResponse($event);

        self::assertEquals('1', $response->headers->get('Symfony-Debug-Toolbar-Replace'));
    }

    public function testSubscriberIsSubscribedToResponseEvent(): void
    {
        self::assertArrayHasKey(ResponseEvent::class, ProfilerSubscriber::getSubscribedEvents());
        self::assertIsString(ProfilerSubscriber::getSubscribedEvents()[ResponseEvent::class]);
    }
}
