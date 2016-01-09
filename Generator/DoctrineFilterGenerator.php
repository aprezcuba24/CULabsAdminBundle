<?php

namespace CULabs\AdminBundle\Generator;

class DoctrineFilterGenerator extends DoctrineFormGenerator
{
    protected function getSuffixClass()
    {
        return 'FilterType';
    }

    protected function getNamespaceFrom()
    {
        return 'Filter';
    }

    protected function getTemplate()
    {
        return 'filter/FormType.php.twig';
    }

    protected function getTemplateService()
    {
        return 'filter/service.yml.twig';
    }


//    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $forceOverwrite = false)
//    {
////        $dirPath = $bundle->getPath().'/Filter';
////        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'FilterType.php';
////
////        parent::generate($bundle, $entity, $metadata, $forceOverwrite);
////
////        $parts       = explode('\\', $entity);
////        $entityClass = array_pop($parts);
////
////        $className = $entityClass.'FilterType';
////
////        $servicePath = $this->kernel->getRootDir().'/config/services.yml';
////        $content  = file_get_contents($servicePath);
////        $content .= "\n".$this->render('form/service.yml.twig', array(
////            'entity_class'     => $entityClass,
////            'namespace'        => $bundle->getNamespace(),
////            'entity_namespace' => implode('\\', $parts),
////            'form_class'       => $className,
////        ));
////
////        file_put_contents($servicePath, $content);
//    }
//
////    protected function renderFile($template, $target, $parameters)
////    {
////
////        print_r($parameters); exit;
////
////        return parent::renderFile('filter/FilterFormType.php.twig', $this->classPath, $parameters);
////    }
}
