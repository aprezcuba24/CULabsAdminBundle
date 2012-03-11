    
    /**
     * Change Max Per Page.
     *
     * @Route("/changemaxperpage", name="{{ route_name_prefix }}_changemaxperpage")
     */
    public function changeMaxPerPageAction()
    {
        $this->getRequest()->getSession()->set('admin_{{ entity|replace({'\\': '/'}) }}.maxperpage', $this->get('request')->query->get('cant', $this->container->getParameter('culabs.admin.list_cant')));
        $this->setPage(1);
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }
    
    /**
     * Change Sort.
     *
     * @Route("/{field}/{order}/short", name="{{ route_name_prefix }}_short")
     */
    public function sortAction($field, $order)
    {
        $this->setPage(1);
        $this->setSort(array(
            'field' => $field,
            'order' => $order,
            'next'  => $order == 'ASC'? 'DESC': 'ASC',
        ));
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }
    
    protected function getPager()
    {
        $filter_form = $this->getFilterForm();        
        $qb = $this->getDoctrine()->getEntityManager()
                   ->getRepository('{{ bundle }}:{{ entity }}')
                   ->createQueryBuilder('{{ entity }}')
        ;
        $sort = $this->getSort();
        if ($sort) {
            $qb->add('orderBy', sprintf('{{ entity }}.%s %s', $sort['field'], $sort['order']));
        }
        $this->get('lexik_form_filter.query_builder')->buildQuery($filter_form, $qb);
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb->getQuery()));
        $pager->setMaxPerPage($this->getMaxPerPage());
        $pager->setCurrentPage($this->getPage(), false, true);
        return $pager;
    }
    
    protected function getFilterForm()
    {
        $filter_form = $this->get('form.factory')->create(new {{ entity }}FilterType());        
        $filter_form->bind($this->getFilters());
        return $filter_form;
    }
    
    protected function getFilters()
    {
        return $this->getRequest()->getSession()->get('admin_{{ entity|replace({'\\': '/'}) }}.filters', array());
    }
    
    protected function setFilters(array $filters)
    {
        return $this->getRequest()->getSession()->set('admin_{{ entity|replace({'\\': '/'}) }}.filters', $filters);
    }
    
    public function getSort()
    {
        return $this->getRequest()->getSession()->get('admin_{{ entity|replace({'\\': '/'}) }}.short', array());
    }
    
    public function setSort($sort)
    {
        $this->getRequest()->getSession()->set('admin_{{ entity|replace({'\\': '/'}) }}.short', $sort);
    }
    
    protected function setPage($page)
    {
        $this->getRequest()->getSession()->set('admin_{{ entity|replace({'\\': '/'}) }}.page', $page);
    }
    
    protected function getPage()
    {
        return $this->getRequest()->getSession()->get('admin_{{ entity|replace({'\\': '/'}) }}.page', 1);
    }
    
    protected function getMaxPerPage()
    {
        return $this->getRequest()->getSession()->get('admin_{{ entity|replace({'\\': '/'}) }}.maxperpage', $this->container->getParameter('culabs.admin.list_cant'));
    }