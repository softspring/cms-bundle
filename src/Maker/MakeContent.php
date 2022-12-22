<?php

namespace Softspring\CmsBundle\Maker;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class MakeContent extends AbstractMaker
{
    protected string $contentName;
    protected string $entityClassName;

    private DoctrineHelper $doctrineHelper;
    private ?Inflector $inflector;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;

        if (class_exists(InflectorFactory::class)) {
            $this->inflector = InflectorFactory::create()->build();
        }
    }

    public static function getCommandName(): string
    {
        return 'make:sfs:cms:content';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new Softspring CMS content type';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('content-name', InputArgument::OPTIONAL, 'The name of the content type (e.g. <fg=yellow>article, product, ...</>)')
            ->addArgument('entity-class', InputArgument::OPTIONAL, 'The name of the entity witch will store content (e.g. <fg=yellow>Article, App\Entity\Product,...</>)')
//            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeCrud.txt'))
        ;

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('content-name')) {
            $argument = $command->getDefinition()->getArgument('content-name');
            $question = new Question($argument->getDescription());

            $value = $io->askQuestion($question);

            $input->setArgument('content-name', $this->inflector->tableize($value));
        }

        $defaultEntityClass = sprintf('App\Entity\Cms\%sContent', Str::asClassName($input->getArgument('content-name')));
        $this->entityClassName = $io->ask(sprintf('Choose a name for your entity class (e.g. <fg=yellow>%s</>)', $defaultEntityClass), $defaultEntityClass);
        $input->setArgument('entity-class', $this->entityClassName);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // TODO: Implement configureDependencies() method.
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
//        $entityClassDetails = $generator->createClassNameDetails(
//            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
//            'Entity\\'
//        );

        $name = $input->getArgument('content-name');
        $createType = sprintf('App\Form\Admin\%sContentCreateForm', Str::asClassName($name));
        $updateType = sprintf('App\Form\Admin\%sContentUpdateForm', Str::asClassName($name));

        // create config.yaml file
        $config = [
            'content' => [
                'revision' => 1,
//                'render_template' => "@type/$name/render.html.twig",
//                'edit_template' => "@type/$name/edit.html.twig",
                'entity_class' => $this->entityClassName,
                'admin' => [
                    'create_type' => $createType,
                    'update_type' => $updateType,
//                    'create_view' => "@content/$name/admin/create.html.twig",
//                    'update_view' => "@content/$name/admin/update.html.twig",
//                    'list_view' => "@content/$name/admin/list.html.twig",
//                    'list_page_view' => "@content/$name/admin/list-page.html.twig",
                ],
                'containers' => null,
            ],
        ];
        $generator->dumpFile("cms/contents/$name/config.yaml", Yaml::dump($config, 10, 4, Yaml::DUMP_NULL_AS_TILDE));

        // generate entity
        $generator->generateClass(
            $this->entityClassName,
            'vendor/softspring/cms-bundle/config/skeleton/Entity.tpl.php',
            [
                'table_name' => "cms_{$name}_content",
                'doctrine_use_attributes' => $this->doctrineHelper->isDoctrineSupportingAttributes() && $this->doctrineHelper->doesClassUsesAttributes($this->entityClassName),
            ]
        );

        // generate create form
        $generator->generateClass(
            $createType,
            'vendor/softspring/cms-bundle/config/skeleton/ContentCreateForm.tpl.php',
            [
                'entity_full_class' => $this->entityClassName,
                'entity_class' => Str::getShortClassName($this->entityClassName),
            ]
        );

        // generate update form
        $generator->generateClass(
            $updateType,
            'vendor/softspring/cms-bundle/config/skeleton/ContentUpdateForm.tpl.php',
            [
                'entity_full_class' => $this->entityClassName,
                'entity_class' => Str::getShortClassName($this->entityClassName),
            ]
        );

        $generator->writeChanges();
    }
}
