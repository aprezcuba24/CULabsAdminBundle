<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator as BaseDoctrineFormGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class DoctrineFormGenerator extends BaseDoctrineFormGenerator
{
    private $kernel;

    public function __construct($kernel, Filesystem $filesystem)
    {
        parent::__construct($filesystem);
        $this->kernel = $kernel;
    }

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        parent::generate($bundle, $entity, $metadata);

        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $className = $entityClass.'Type';

        $servicePath = $this->kernel->getRootDir().'/config/services.yml';
        $content  = file_get_contents($servicePath);
        $content .= "\n".$this->render('form/service.yml.twig', array(
            'entity_class'     => $entityClass,
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'form_class'       => $className,
        ));

        file_put_contents($servicePath, $content);
    }
} 