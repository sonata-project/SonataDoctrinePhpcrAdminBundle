<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;

class CRUDController extends Controller
{
    /**
     * TODO: this should go into the
     * base class in the SAB CRUD controller we are extending here.
     * Is here as a proof of concept
     *
     * return the Response object associated to the create action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());

        $id= urldecode($id);
        $object = $this->admin->getNewInstance();
        $idField = $this->admin->getModelManager()->getModelIdentifier($this->admin->getClass());
        $object->{'set'.$idField}($id);

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bindRequest($this->get('request'));

            if ($form->isValid()) {
                $this->admin->create($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                        'result' => 'ok',
                        'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                $this->get('session')->setFlash('sonata_flash_success','flash_create_success');
                // redirect to edit mode
                return $this->redirectTo($object);
            }
            $this->get('session')->setFlash('sonata_flash_error', 'flash_create_error');
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getEditTemplate(), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ));
    }
}

