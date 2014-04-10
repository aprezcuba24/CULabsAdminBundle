<?php

namespace CULabs\AdminBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Filesystem\Filesystem;

class DoctrineFiterGenerator extends Generator
{
    private $filesystem;
    private $className;
    private $classPath;

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->setSkeletonDirs($skeletonDir);
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.'FilterType';
        $dirPath         = $bundle->getPath().'/Filter';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'FilterType.php';

        if (file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s filter class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The form generator does not support entity classes with multiple primary keys.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $this->renderFile('FilterFormType.php.twig', $this->classPath, array(
            'fields'           => $this->getFieldsFromMetadata($metadata),
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class'     => $entityClass,
            'bundle'           => $bundle->getName(),
            'form_class'       => $this->className,
            'form_type_name'   => strtolower(str_replace('\\', '_', $bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.$this->className),
        ));

        $servicePath = $bundle->getPath().'/Resources/config/services.yml';
        $content  = file_get_contents($servicePath);
        $content .= "\n".$this->render('service.yml.twig', array(
            'entity_class'     => $entityClass,
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'form_class'       => $this->className,
        ));

        file_put_contents($servicePath, $content);
    }
    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldNames;

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $metadata->identifier);
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }
}
