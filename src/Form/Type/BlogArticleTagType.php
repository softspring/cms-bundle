<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Helper\BlogArticleHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogArticleTagType extends AbstractType
{
    public function __construct(
        protected BlogArticleHelper $blogArticleHelper,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
            'placeholder' => 'block_articles_related.form.tag.placeholder',
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    protected function getChoices(): array
    {
        $choices = [];

        foreach ($this->blogArticleHelper->getAvailableTags() as $tag) {
            $choices[$tag] = $tag;
        }

        return $choices;
    }
}
