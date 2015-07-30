<?php

namespace Redking\Bundle\CoreRestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('redking_core_rest');

        $rootNode->children()
            ->scalarNode('api_route_prefix')->defaultValue('/api/v1')->end()
            ->scalarNode('expose_api_dev')->defaultTrue()->end()

            // Définition des documents qui entraine la creation d'une activity
            ->arrayNode('document_for_activities')
                ->useAttributeAsKey('class')
                ->prototype('array')
                    ->children()
                        ->arrayNode('actions')->prototype('scalar')->end()
                    ->end()
                ->end()
                // Utilisé pour forcer le champ user a partir d'un attribut du document
                ->children()->scalarNode('user_field')->defaultNull()->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
