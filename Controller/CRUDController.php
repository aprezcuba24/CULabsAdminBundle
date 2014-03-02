<?php

namespace CULabs\AdminBundle\Controller;

use CULabs\AdminBundle\Controller\Controller as BaseController;

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
        $this->setSession('filters', $filters);
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
