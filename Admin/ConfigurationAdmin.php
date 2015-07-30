<?php

namespace Redking\Bundle\CoreRestBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ConfigurationAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('offline')
            // ->add('offline_message')
            // ->add('email_support')
            // ->add('email_marketing')
            // ->add('translation_updated_at')
            // ->add('iphone')
            // ->add('android')
            // ->add('ipad')
            // ->add('web')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('offline')
            ->add('offline_message')
            ->add('email_support')
            ->add('email_marketing')
            // ->add('translation_updated_at')
            // ->add('iphone')
            // ->add('android')
            // ->add('ipad')
            // ->add('web')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('General')
                ->with('Gestion mise en ligne', array('class' => 'col-md-6'))
                    ->add('offline')
                    ->add('offline_message', 'textarea')
                ->end()
                ->with('Emails', array('class' => 'col-md-6'))
                    ->add('email_support')
                    ->add('email_marketing')
                ->end()
            ->end()
            ->tab('iPhone')
                ->add('iphone', 'sonata_type_collection', 
                                array(), 
                                array(
                                    'edit' => 'inline',
                                    'inline' => 'table'
                                    ))
                ->end()
            ->end()
            ->tab('Android')
                ->add('android', 'sonata_type_collection', 
                                array(), 
                                array(
                                    'edit' => 'inline',
                                    'inline' => 'table'
                                    ))
                ->end()
            ->end()
            ->tab('IPad')
                ->add('ipad', 'sonata_type_collection', 
                                array(), 
                                array(
                                    'edit' => 'inline',
                                    'inline' => 'table'
                                    ))
                ->end()
            ->end()
            ->tab('Web')
                ->add('web', 'sonata_type_collection', 
                                array(), 
                                array(
                                    'edit' => 'inline',
                                    'inline' => 'table'
                                    ))
                ->end()
            ->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('offline')
            ->add('offline_message')
            ->add('email_support')
            ->add('email_marketing')
            ->add('translation_updated_at', 'datetime', array('format' => 'r'))
            ->add('iphone', 'string', array('template' => 'RedkingCoreRestBundle:SonataAdmin:show_configuration_platform.html.twig', 'field' => 'iphone'))
            ->add('android', 'string', array('template' => 'RedkingCoreRestBundle:SonataAdmin:show_configuration_platform.html.twig', 'field' => 'android'))
            ->add('ipad', 'string', array('template' => 'RedkingCoreRestBundle:SonataAdmin:show_configuration_platform.html.twig', 'field' => 'ipad'))
            ->add('web', 'string', array('template' => 'RedkingCoreRestBundle:SonataAdmin:show_configuration_platform.html.twig', 'field' => 'web'))
        ;
    }
}
