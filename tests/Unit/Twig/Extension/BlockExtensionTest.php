<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Twig\Extension;

use Doctrine\ORM\EntityRepository;
use Exception;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Softspring\CmsBundle\Twig\Extension\BlockExtension;
use Twig\Node\Node;

class BlockExtensionTest extends TestCase
{
    public function testItRegistersBlockFunctionsAsHtmlSafe(): void
    {
        $extension = $this->createExtension();
        $functions = [];
        foreach ($extension->getFunctions() as $function) {
            $functions[$function->getName()] = $function;
        }

        $args = new Node();

        self::assertSame(['html'], $functions['sfs_cms_block']->getSafe($args));
        self::assertSame(['html'], $functions['sfs_cms_block_by_type']->getSafe($args));
        self::assertSame(['html'], $functions['sfs_cms_block_by_id']->getSafe($args));
        self::assertSame(['html'], $functions['sfs_cms_block_find']->getSafe($args));
    }

    public function testItFindsBlocksByStringIdAndRendersThem(): void
    {
        $block = new Block();
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 'footer'], [])
            ->willReturn($block);

        $blockManager = $this->createStub(BlockManagerInterface::class);
        $blockManager->method('getRepository')->willReturn($repository);

        $blockRenderer = $this->createMock(BlockRenderer::class);
        $blockRenderer->expects($this->once())
            ->method('renderBlock')
            ->with($block, 'es')
            ->willReturn('<section>Footer</section>');

        $extension = new BlockExtension($blockManager, $blockRenderer);

        self::assertSame('<section>Footer</section>', $extension->renderBlockById('footer', 'es'));
    }

    public function testItReturnsHtmlCommentWhenBlockDoesNotExist(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $blockManager = $this->createStub(BlockManagerInterface::class);
        $blockManager->method('getRepository')->willReturn($repository);

        $extension = new BlockExtension($blockManager, $this->createStub(BlockRenderer::class));

        self::assertSame('<!-- block missing not found -->', $extension->renderBlockById('missing'));
    }

    public function testFindOneByRequiresStringOrArrayCriteria(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid criteria');

        $this->createExtension()->findOneBy(123);
    }

    private function createExtension(): BlockExtension
    {
        return new BlockExtension(
            $this->createStub(BlockManagerInterface::class),
            $this->createStub(BlockRenderer::class)
        );
    }
}
