<?php

namespace CULabs\AdminBundle\Controller;

use CULabs\AdminBundle\Controller\Controller as BaseController;
use Symfony\Component\Form\Form;

/**
 * Description of Controller
 *
 * @author
 */
class CRUDController extends BaseController
{
    protected function getFilters()
    {
        return $this->getSession('filters', array());
    }

    protected function setFilters(array $filters)
    {
        foreach ($filters as $key => $item) {
            if (is_object($item) && is_callable(array($item, 'getId'))) {
                $filters[$key] = $item->getId();
            }
        }

        $this->setSession('filters', $filters);
    }

    protected function setFilterInForm(Form $form)
    {
        $filters = $this->getFilters();
        foreach ($form->all() as $child) {
            $name = $child->getName();
            if (isset($filters[$name])) {
                if ($value = $this->prepareFilterValue($child, $filters[$name])) {
                    $filters[$name] = $value;
                }
            }
        }
        $form->setData($filters);
    }

    protected function prepareFilterValue(Form $form, $value)
    {
        $childType = $form->getConfig()->getType()->getInnerType()->getName();
        if (in_array($childType, ['filter_entity', 'entity'])) {
            $class = $form->getConfig()->getOption('class');
            $entity = $this->getRepository($class)->find($value);

            return $entity;
        }
    }

    protected function getSort()
    {
        return $this->getSession('sort', array());
    }

    protected function setSort($sort)
    {
        $this->setSession('sort', $sort);
    }

    protected function getPage()
    {
        return $this->getSession('page', 1);
    }

    protected function setPage($page)
    {
        $this->setSession('page', $page);
    }
    protected function getMaxPerPage()
    {
        return $this->getSession('maxperpage', $this->container->getParameter('culabs.admin.list_cant'));
    }

    protected function setMaxPerPage($maxperpage)
    {
        $this->setSession('maxperpage', $maxperpage);
    }
}
