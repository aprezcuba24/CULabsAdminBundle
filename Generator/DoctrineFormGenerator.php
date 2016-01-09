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
    private $filesystem;
    private $className;
    private $classPath;
    private $kernel;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct($kernel, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->kernel     = $kernel;
    }

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $forceOverwrite = false)
    {
        $parts = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.$this->getSuffixClass();
        $dirPath = $bundle->getPath().'/'.$this->getNamespaceFrom();
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).$this->getSuffixClass().'.php';

        if (!$forceOverwrite && file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s form class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The form generator does not support entity classes with multiple primary keys.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $this->renderFile($this->getTemplate(), $this->classPath, array(
            'fields' => $this->getFieldsFromMetadata($metadata),
            'fields_mapping' => $metadata->fieldMappings,
            'namespace' => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class' => $entityClass,
            'bundle' => $bundle->getName(),
            'form_class' => $this->className,
            'form_type_name' => strtolower(str_replace('\\', '_', $bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.substr($this->className, 0, -4)),

            // Add 'setDefaultOptions' method with deprecated type hint, if the new 'configureOptions' isn't available.
            // Required as long as Symfony 2.6 is supported.
            'configure_options_available' => method_exists('Symfony\Component\Form\AbstractType', 'configureOptions'),
            'get_name_required' => !method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix'),
        ));

        $servicePath = $this->kernel->getRootDir().'/config/services.yml';
        $content  = file_get_contents($servicePath);
        $content .= "\n".$this->render($this->getTemplateService(), array(
            'entity_class'     => $entityClass,
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'form_class'       => $this->className,
        ));

        file_put_contents($servicePath, $content);
    }

    protected function getSuffixClass()
    {
        return 'Type';
    }

    protected function getNamespaceFrom()
    {
        return 'Form';
    }

    protected function getTemplate()
    {
        return 'form/FormType.php.twig';
    }

    protected function getTemplateService()
    {
        return 'form/service.yml.twig';
    }

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return array $fields
     */
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