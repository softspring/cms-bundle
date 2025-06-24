<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Helper\LocaleHelper;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Render\SectionVersionRenderer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected CmsConfig $cmsConfig,
        protected SectionVersionRenderer $sectionVersionRenderer,
        protected RequestStack $requestStack,
        protected readonly LocaleHelper $localeHelper,
    ) {
    }

    public function getBlockPrefix(): string
    {
        return 'section';
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => SectionInterface::class,
            'em' => $this->em,
            'required' => false,
            'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('b'),
            'choice_label' => function (SectionInterface $section) {
                $label = $section->getName();

                if (!$section->getPublishedVersion()) {
                    $label .= ' (draft)';
                }

                return $label;
            },
            'choice_filter' => function (?SectionInterface $section = null) {
                $currentSection = $this->requestStack->getCurrentRequest()?->attributes->get('section');

                return !$currentSection || $currentSection->getId() !== $section?->getId();
            },
            'choice_attr' => function (?SectionInterface $section) {
                $attr = [
                    'data-section-preview' => '',
                ];

                if ($section) {
                    // $sectionConfig = $this->cmsConfig->getSection($section->getType());
                    // $sectionConfig['esi'] && $attr['data-section-esi'] = '';
                    // $sectionConfig['singleton'] && $attr['data-section-singleton'] = '';
                    // $sectionConfig['schedulable'] && $attr['data-section-schedulable'] = '';
                    // $sectionConfig['cache_ttl'] && $attr['data-section-cache-ttl'] = '';

                    if ('draft' == $section->getStatus()) {
                        $attr['data-section-draft'] = '';
                    }

                    if ($section->getExtra('ttl', false)) {
                        $attr['data-section-ttl'] = $section->getExtra('ttl');
                    }

                    $attr['data-section-preview'] = '';

                    foreach ($this->localeHelper->getEnabledLocales() as $locale) {
                        $request = new Request();
                        $request->setLocale($locale);
                        $attr['data-section-preview'] .= '<div data-lang="'.$locale.'" class="section-preview">'
                            .$this->sectionVersionRenderer->render(
                                $section->getPublishedVersion() ?: $section->getLastVersion(),
                                $request,
                            )
                            .'</div>';
                    }
                }

                return $attr;
            },
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['section_preview'] = ''; // $options['section_attr'];

        /** @var ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            if ($view->vars['value'] == $choice->value) {
                $view->vars['section_preview'] = $choice->attr['data-section-preview'];
            }
        }
    }
}
