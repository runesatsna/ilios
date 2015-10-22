<?php

namespace Ilios\CoreBundle\Form\Type;

use Ilios\CoreBundle\Form\DataTransformer\RemoveMarkupTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserMadeReminderType
 * @package Ilios\CoreBundle\Form\Type
 */
class UserMadeReminderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note', null, ['empty_data' => null])
            ->add('dueDate', 'datetime', array(
                'widget' => 'single_text',
            ))
            ->add('closed', null, ['required' => false])
            ->add('user', 'tdn_single_related', [
                'required' => false,
                'entityName' => "IliosCoreBundle:User"
            ])
        ;
        $builder->get('note')->addViewTransformer(new RemoveMarkupTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ilios\CoreBundle\Entity\UserMadeReminder'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'usermadereminder';
    }
}
