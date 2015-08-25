<?php

namespace Redking\Bundle\CoreRestBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Redking\Bundle\CoreRestBundle\Document\Translation;

class TranslationAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('support')
            ->add('screen')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, array('identifier' => true))
            ->add('support')
            ->add('screen')
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
            ->with('General', ['class' => 'col-md-4'])
                ->add('id')
                ->add('support', 'choice', ['choices' => Translation::getSupportChoices()])
                ->add('screen')
            ->end()
            ->with('Traductions', ['class' => 'col-md-8'])
                ->add('translations', 'translation', [
                    'options' => [
                        'fields' => [
                            'content' => [
                                'type' => 'textarea'
                                ]
                            ]
                        ]
                    ]
                    )
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
            ->add('support')
            ->add('screen')
            ->add('translations', 'string', array('template' => 'RedkingODMTranslatorBundle:SonataAdmin:show_translations.html.twig'))
        ;
    }

    public function getExportFields(){

        $translations = array();
        foreach($this->getConfigurationPool()->getContainer()->getParameter('locales') as $locale){
            $translations[$locale] = "contentTranslations[$locale]";
        }

        $fields = array_merge(parent::getExportFields(),$translations);

        $fields = array_diff($fields,['content','translations']);

        return $fields;
    }
}
