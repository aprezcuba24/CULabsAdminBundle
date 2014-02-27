
    /**
     * Displays a form to edit an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/edit", name="{{ route_name_prefix }}_edit")
     * @Secure(roles="ROLE_{{ entity|upper }}_EDIT")
     * @Template()
{% endif %}
     */
    public function editAction($id, Request $request)
    {
{% if 'annotation' != format %}
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_EDIT')) {
            throw new AccessDeniedException();
        }
{% endif %}
        $entity = $this->getRepository('{{ bundle }}:{{ entity }}')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find {{ entity }} entity.');
        }
        $form = $this->createForm(new {{ entity_class }}Type(), $entity);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getManager();
                $em->persist($entity);
                $em->flush();
                $this->addFlash('notice', 'The entity is saved.');

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
