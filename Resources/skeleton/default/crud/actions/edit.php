
    /**
     * Displays a form to edit an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/edit", name="{{ route_name_prefix }}_edit")
     * @Template()
     * @Secure(roles="ROLE_{{ entity|upper }}_EDIT")
{% endif %}
     */
    public function editAction($id)
    {
        $entity = $this->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
        $form = $this->createForm(new {{ entity_class }}Type(), $entity);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($entity);
                $em->flush();
                $this->setFlash('notice', 'The entity is saved.');
                return $this->redirect($this->generateUrl('{{ route_name_prefix }}_show', array('id' => $entity->getId())));
            }
        } 
{% if 'annotation' == format %}
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}CRUD:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
{% endif %}
    }
