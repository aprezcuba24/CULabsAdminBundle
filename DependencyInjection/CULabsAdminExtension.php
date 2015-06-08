<?php

namespace CULabs\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CULabsAdminExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);
        $configs = $configs[0];

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->complileThemeColletions($container);

        $container->setParameter('culabs.admin.list_cant', 10);

        $container->setParameter('culabs.admin.admin_menu', $container->getParameter($configs['menu_backend']));
    }

    protected function complileThemeColletions(ContainerBuilder $container)
    {
        $items = array();

        foreach ($container->findTaggedServiceIds('cu_labs_admin.theme') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $items[$alias] =  new Reference($serviceId);
        }

        $container->getDefinition('cu_labs_admin.theme.collection')->replaceArgument(0, $items);
    }
}
