<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Request;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Request\FlashNotifier;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashNotifierTest extends TestCase
{
    public function testItAddsRawAndTranslatedFlashMessages(): void
    {
        $session = new Session(new MockArraySessionStorage());

        $request = new Request();
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')
            ->with('message.key', ['%name%' => 'Jane'], 'admin', 'es')
            ->willReturn('Translated message');

        $notifier = new FlashNotifier($requestStack, $translator);
        $notifier->add('success', 'Saved');
        $notifier->addTrans('error', 'message.key', ['%name%' => 'Jane'], 'admin', 'es');

        self::assertSame(['Saved'], $session->getFlashBag()->peek('success'));
        self::assertSame(['Translated message'], $session->getFlashBag()->peek('error'));
    }
    public function testSessionlessRequestsDoNotCreateFlashMessages(): void
    {
        $requests = new RequestStack();
        $notifier = new FlashNotifier($requests, $this->createStub(TranslatorInterface::class));
        $notifier->add('success', 'Without a request');
        $request = new Request();
        $requests->push($request);
        $notifier->add('success', 'Without a session');

        self::assertFalse($request->hasSession());
    }
}
