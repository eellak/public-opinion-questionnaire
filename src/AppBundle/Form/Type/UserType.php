<?php
namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    private $dimension;
    private $values;

    public function __construct($dimension, $values) {
        $this->dimension = $dimension;
        $this->values = array();
        foreach($values as $curValue) {
            $this->values[$curValue] = $curValue;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add($this->dimension, 'choice', array('label' => false, 'required' => true, 'expanded' => true, 'choices' => $this->values));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return 'user_'.$this->dimension;
    }
}