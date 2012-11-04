
    /**
     * Lists all {{ entity }} entities.
     *
{% if 'annotation' == format %}
     * @Route("", name="{{ route_name_prefix }}")
     * @Template()
     * @Secure(roles="ROLE_{{ entity|upper }}_LIST")
{% endif %}
     */
    public function indexAction()
    {        
        $page = $this->get('request')->query->get('page', $this->getPage());
        $this->setPage($page);
        $pager = $this->getPager();
        if ($this->get('request')->isXmlHttpRequest()) {
            return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}CRUD:list.html.twig', array(
                'pager' => $pager,
                'sort'  => $this->getSort(),
            ));
        }
        $filter_form = $this->getFilterForm();
{% if 'annotation' == format %}
        return array(
            'pager'  => $pager,
            'filter' => $filter_form->createView(),
            'sort'   => $this->getSort(),
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}CRUD:index.html.twig', array(
            'pager'  => $pager,
            'filter' => $filter_form->createView(),
            'sort'   => $this->getSort(),
        ));
{% endif %}
    }
    
    /**
     * Filter {{ entity }} entities.
     *
{% if 'annotation' == format %}
     * @Route("/filter", name="{{ route_name_prefix }}_filter")
     * @Method("post")
{%- endif %}     
     */
    public function filterAction()
    {
        {%- if 'annotation' != format %}
        if ($this->getRequest()->getMethod() == 'POST')
            throw $this->createNotFoundException();
        {% endif %}        
        if ($this->getRequest()->request->get('action_reset')) {
            $this->setFilters(array());
            return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
        }        
        $filter_form = $this->get('form.factory')->create(new {{ entity_class }}FilterType());        
        $filter_form->bindRequest($this->get('request'));        
        if ($filter_form->isValid()) {
            $this->setPage(1);
            $this->setFilters($filter_form->getData());
            return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
        }        
        return $this->render('{{ bundle }}:{{ entity }}:index.html.twig', array(
            'filter' => $filter_form->createView(),
            'pager'  => $this->getPager(),
            'sort'   => $this->getSort(),
        ));
    }
    