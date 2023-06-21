<?php

namespace Softspring\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\UserBundle\Model\UserInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    protected EntityManagerInterface $sfsUserEm;

    public function __construct(EntityManagerInterface $sfsUserEm)
    {
        $this->sfsUserEm = $sfsUserEm;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'class' => UserInterface::class,
            'em' => $this->sfsUserEm,
            'choice_label' => function (UserInterface $user) {
                return $user->getDisplayName();
            },
        ]);
    }

    //    public function buildForm(FormBuilderInterface $builder, array $options)
    //    {
    //        /** @var EntityManagerInterface $em */
    //        $em = $options['em'];
    //
    //        $builder->addModelTransformer(new CallbackTransformer(function ($userId) use ($em) {
    //            return $userId ? $em->getRepository(UserInterface::class)->findOneById($userId['id']) : null;
    //        }, function ($user) {
    //            return $user instanceof UserInterface ? [
    //                'id' => $user->getId(),
    //                'type' => UserInterface::class,
    //                'displayName' => $user->getDisplayName(),
    //            ] : null;
    //        }));
    //    }
}
