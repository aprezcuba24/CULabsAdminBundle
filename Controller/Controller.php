<?php

namespace CULabs\AdminBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of Controller
 *
 * @author
 */
class Controller extends BaseController
{
    protected function isXmlHttpRequest()
    {
        return $this->get('request')->isXmlHttpRequest();
    }

    protected function redirectJs($url)
    {
        return new Response(sprintf('<script> window.location = "%s"; </script>', $url));
    }

    protected function getRepository($entityName, $entityManagerName = null)
    {
        return $this->getDoctrine()->getRepository($entityName, $entityManagerName);
    }

    protected function persist($entity, $flush = true)
    {
        $this->getManager()->persist($entity);

        if ($flush) {
            $this->flush($entity);
        }
    }

    protected function remove($entity, $flush = true)
    {
        $this->getManager()->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    protected function flush($entity = null)
    {
        $this->getManager()->flush($entity);
    }

    protected function getManager($name = null)
    {
        return $this->getDoctrine()->getManager($name);
    }

    protected function findOr404($entity, $criterias = array())
    {
        $result = null;
        $findMethod = is_scalar($criterias) ? 'find' : 'findOneBy';

        if (is_object($entity) && $entity instanceof ObjectRepository) {
            $result = $entity->$findMethod($criterias);
        } elseif (is_object($entity) && $this->getManager()->contains($entity)) {
            $result = $this->getManager()->refresh($entity);
        } elseif (is_string($entity)) {
            $repository = $this->getRepository($entity);
            $result = $repository->$findMethod($criterias);
        }

        if (null !== $result){
            return $result;
        }

        throw $this->createNotFoundException('Resource not found');
    }

    protected function getSecurity()
    {
        return $this->get('security.context');
    }

    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    protected function getRequestParameter($name, $default = null)
    {
        return $this->get('request')->get($name, $default);
    }

    protected function sessionName($name)
    {
        return str_replace('\\', '_', get_class($this)).'_'.$name;
    }

    protected function setSession($name, $value)
    {
        $this->get('session')->set($this->sessionName($name), $value);
    }

    protected function getSession($name, $default)
    {
        return $this->get('session')->get($this->sessionName($name), $default);
    }
}
