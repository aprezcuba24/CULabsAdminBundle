    
    /**
     * Change Max Per Page.
     *
     * @Route("/changemaxperpage", name="{{ route_name_prefix }}_changemaxperpage")
     */
    public function changeMaxPerPageAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_LIST')) {
            throw new AccessDeniedException();
        }
        $this->setSession('maxperpage', $this->get('request')->query->get('cant'));
        $this->setPage(1);
        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }
    
    /**
     * Change Sort.
     *
     * @Route("/{field}/{order}/short", name="{{ route_name_prefix }}_sort")
     */
    public function sortAction($field, $order)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_LIST')) {
            throw new AccessDeniedException();
        }
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
        $qb = $this->getRepository('{{ bundle }}:{{ entity }}')
                   ->createQueryBuilder('{{ entity }}')
        ;
        $sort = $this->getSort();
        if ($sort) {
            $qb->add('orderBy', sprintf('{{ entity }}.%s %s', $sort['field'], $sort['order']));
        }
        $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filter_form, $qb);
        $pager = $this->get('knp_paginator')->paginate(
            $qb->getQuery(),
            $this->getPage(),
            $this->getMaxPerPage()
        );        
        return $pager;
    }
    
    protected function getFilterForm()
    {
        $filter_form = $this->get('form.factory')->create(new {{ entity }}FilterType());        
        $filter_form->bind($this->getFilters());
        return $filter_form;
    }