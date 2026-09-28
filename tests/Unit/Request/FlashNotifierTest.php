<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Request;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Request\FlashNotifier;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashNotifierTest extends TestCase
{
    public function testItAddsRawAndTranslatedFlashMessages(): void
    {
        $session = new Session(new MockArraySessionStorage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

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
}
