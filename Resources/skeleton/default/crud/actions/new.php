
    /**
     * Displays a form to create a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/new", name="{{ route_name_prefix }}_new")
     * @Secure(roles="ROLE_{{ entity|upper }}_NEW")
     * @Template()
{% endif %}
     */
    public function newAction(Request $request)
    {
{% if 'annotation' != format %}
        if (false === $this->get('security.context')->isGranted('ROLE_{{ entity|upper }}_NEW')) {
            throw new AccessDeniedException();
        }
{% endif %}
        $entity = new {{ entity_class }}();
        $form   = $this->createForm(new {{ entity_class }}Type(), $entity);
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
            'form'   => $form->createView()
        );
{% else %}
        return $this->render('{{ bundle }}:{{ entity|replace({'\\': '/'}) }}CRUD:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
{% endif %}
    }
