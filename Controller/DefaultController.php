<?php

namespace Toak\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Toak\UserBundle\Entity\User;
use Toak\UserBundle\Form\UserType;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Secure(roles="ROLE_ADMIN") 
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ToakUserBundle:User')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}/show", name="user_show")
     * @Secure(roles="ROLE_ADMIN") 
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ToakUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }


        return array(
            'entity'      => $entity,
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Secure(roles="ROLE_ADMIN") 
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createForm(new UserType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/create", name="user_create")
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN") 
     * @Template("ToakUserBundle:Default:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new User();
        $form = $this->createForm(new UserType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($entity);
            $entity->encodePassword($encoder);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Deletes a User entity.
     * @Secure(roles="ROLE_ADMIN") 
     * @Route("/{id}/delete", name="user_delete")
     */
    public function deleteAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ToakUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * @Route("/{id}/edit", name="user_edit")
     * @Secure(roles="ROLE_USER") 
     * @Template()
     */
    public function editAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ToakUserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $request = $this->get('request');

        $form = $this->createFormBuilder()
            ->add('old', 'password', array(
                'always_empty' => true, 
                'required' => true, 
                'label' => 'Ancien mot de passe')
            )
            ->add('new', 'repeated', array(
                'type' => 'password', 
                'required' => true, 
                'invalid_message' => 'Les champs password doivent correspondre',
                'first_options' => array('label' => 'Nouveau mot de passe'),
                'second_options' => array('label' => 'Répéter le nouveau mot de passe')
                )
            )->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                if ($user->getPassword() !== $encoder->encodePassword($form->get('old')->getData(), $user->getSalt())) {
                    $this->get('session')->getFlashBag()->add('error', 'L\'ancien mot de passe est invalide !');
                    return array('user' => $user, 'form' => $form->createView());
                }

                if (strlen($form->get('new')->getData()) < 8) {
                    $this->get('session')->getFlashBag()->add('error', 'Le nouveau mot de passe n\'est pas assez long, 8 caractères minimum !');
                    return array('user' => $user, 'form' => $form->createView());
                }

                $user->setRawPassword($form->get('new')->getData());

                if (!$user->isPasswordValid()) {
                    $this->get('session')->getFlashBag()->add('error', 'Le nouveau mot de passe n\'est pas valide, il doit être différent de votre email !');
                    return array('user' => $user, 'form' => $form->createView());
                }

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $user->encodePassword($encoder);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Votre mot de passe a été modifié !');
            }
        }

        return array('user' => $user, 'form' => $form->createView());
    }
}
