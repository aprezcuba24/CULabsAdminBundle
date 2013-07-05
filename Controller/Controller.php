<?php

namespace CULabs\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of Controller
 *
 * @author 
 */
class Controller extends BaseController
{
    protected function rediretJs($url)
    {
        return new Response(sprintf('<script> window.location = "%s"; </script>', $url));
    }
    protected function getRepository($entityName, $entityManagerName = null)
    {
        return $this->getDoctrine()->getRepository($entityName, $entityManagerName);
    }
    protected function getManager($name = null)
    {
        return $this->getDoctrine()->getManager($name);
    }
    protected function addFlash($name, $value)
    {
        $this->get('session')->getFlashBag()->add(
            $name,
            $value
        );
    }
    protected function sessionName($name)
    {
        return str_replace('\\', '_', get_class($this)).'_'.$name;
    }
    protected function setSession($name, $value)
    {
        $this->getRequest()->getSession()->set($this->sessionName($name), $value);
    }
    protected function getSession($name, $default)
    {
        return $this->getRequest()->getSession()->get($this->sessionName($name), $default);
    }
}