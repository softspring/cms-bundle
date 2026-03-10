<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Manager\ContentManager;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentType extends AbstractType
{
    public function __construct(protected EntityManagerInterface $sfsContentEm, protected ContentManager $contentManager, protected TranslatorInterface $translator)
    {
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => ContentInterface::class,
            'em' => $this->sfsContentEm,
            'required' => false,
            'choice_label' => function (ContentInterface $content) {
                return $content->getName();
            },
            'group_by' => function (ContentInterface $content) {
                return $this->translator->trans("{$this->contentManager->getType($content)}.name", [], 'sfs_cms_contents');
            },
        ]);
    }
}
