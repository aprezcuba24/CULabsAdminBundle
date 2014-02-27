    
    /**
     * Change Max Per Page.
     *
{% if 'annotation' == format %}
     * @Route("/changemaxperpage", name="{{ route_name_prefix }}_changemaxperpage")
     * @Secure(roles="ROLE_{{ entity|upper }}_LIST")
{% endif %}
     */
    public function changeMaxPerPageAction()
    {
{% if 'annotation' != format %}
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_LIST')) {
            throw new AccessDeniedException();
        }
{% endif %}
        $this->setSession('maxperpage', $this->get('request')->query->get('cant'));
        $this->setPage(1);

        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }
    
    /**
     * Change Sort.
     *
{% if 'annotation' == format %}
     * @Route("/{field}/{order}/short", name="{{ route_name_prefix }}_sort")
     * @Secure(roles="ROLE_{{ entity|upper }}_LIST")
{% endif %}
     */
    public function sortAction($field, $order)
    {
{% if 'annotation' != format %}
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_LIST')) {
            throw new AccessDeniedException();
        }
{% endif %}
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
        $filter_form = $this->createForm(new {{ entity }}FilterType(), $this->getFilters());

        return $filter_form;
    }