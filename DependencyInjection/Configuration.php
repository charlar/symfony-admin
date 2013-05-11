<?php

namespace CRL\AdminBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('crl_admin');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('colors')
                    ->defaultValue('1')
                    ->info('choose optional color schemes')
                    ->example('example setting')
                ->end()
				->arrayNode('views')
                    ->useAttributeAsKey('name')
            		->prototype('array')
					->children()
						->arrayNode('entityviews')
            			->prototype('array')
							->children()
								->scalarNode('viewname')
									->isRequired()
									->cannotBeEmpty()
								->end()
								->arrayNode('fields')->prototype('scalar')->end()
							->end()
						->end()
				->end()
            ->end()
		;

        return $treeBuilder;
    }
}