<?php

/**
 * @autor: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\AdminBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Filesystem\Filesystem;

class DoctrineModelGenerator extends Generator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->setSkeletonDirs($skeletonDir);
    }

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $className = $entityClass.'Model';
        $dirPath   = $bundle->getPath().'/Model';
        $classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'Model.php';

        if (file_exists($classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s model class as it already exists under the %s file', $className, $classPath));
        }

        $this->renderFile('Model.php.twig', $classPath, array(
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class'     => $entityClass,
            'bundle'           => $bundle->getName(),
            'model_class'      => $className,
            'entity'           => $entity,
        ));

        $servicePath = $bundle->getPath().'/Resources/config/services.yml';
        $content  = file_get_contents($servicePath);
        $content .= "\n".$this->render('service.yml.twig', array(
            'entity_class'     => $entityClass,
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'model_class'      => $className,
        ));

        file_put_contents($servicePath, $content);
    }
} 