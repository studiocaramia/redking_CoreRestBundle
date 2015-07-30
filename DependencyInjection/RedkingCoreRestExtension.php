<?php

namespace Redking\Bundle\CoreRestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RedkingCoreRestExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('bridge.xml');
        $loader->load('events.xml');
        $loader->load('forms.xml');
        $loader->load('services_rest.xml');
        
        if ($config['expose_api_dev'] === true) {
            $loader->load('services_api_rest.xml');
        }

        // if sonata admin
        $loader->load('services_admin.xml');

        $container->setParameter('redking_core_rest.api_route_prefix', $config['api_route_prefix']);
        $container->setParameter('redking_core_rest.document_for_activities', $config['document_for_activities']);
    }
}
