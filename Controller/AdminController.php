<?php

namespace CRL\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Query\ResultSetMapping;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AdminController extends Controller
{
    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
	 * (copied from DoctrineFormGenerator.php)
     *
     * @param ClassMetadataInfo $metadata
     * @return array $fields
     */
    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldNames;

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $metadata->identifier);
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }
	
    private function getEntityForm($adminobj, ClassMetadataInfo $metadata)
    {
			$formbuilder = $this->createFormBuilder($adminobj);
//		foreach ($metadata->getColumnNames() as $columnName)
        foreach ($this->getFieldsFromMetadata($metadata) as $propertyName)
		{
//		  $propertyName = $metadata->getFieldName($columnName);
		  $formbuilder->add($propertyName);
		}
        $form = $formbuilder->getForm();
		return $form;
	}

    /**
     * @Route("/", name="_admin")
     * @Template()
     */
    public function indexAction()
    {
		$em = $this->getDoctrine()->getEntityManager();
		$metadatas = $em->getMetadataFactory()->getAllMetadata();
		$entities = array();
		foreach ($metadatas as $md)
		{
		   $entityenc = bin2hex($md->name);
		   $structurl = $this->generateUrl('_admin_struct', array('entity' => $entityenc, ));
		   $browseurl =  $this->generateUrl('_admin_browse', array('entityenc' => $entityenc, 'page' => 0));
  	       $addurl =  $this->generateUrl('_admin_add', array('entityenc' => $entityenc));
		   $entity = array('name' => $md->name,
		                   'structurl' => $structurl,
						   'browseurl' => $browseurl,
						   'addurl' => $addurl
		     		);
			$entities[] = $entity;
		}
		return array('metadatas' => $entities);
    }

    /**
     * @Route("/struct/{entity}", name="_admin_struct")
     * @Template()
     */
    public function adminStructAction($entity)
    {
		$entity = pack("H*" , $entity);
		$em = $this->getDoctrine()->getEntityManager();
		$metadata = $em->getMetadataFactory()->getMetadataFor($entity);
		// if the slug is valid, log them in and redirect to the password change page
		return array('entity' => $entity, 'metadata' => var_export($metadata, TRUE));
   //		return array('entity' => $entity, 'metadata' => $metadata->getColumnNames());
   }

    /**
     * @Route("/browse/{entityenc}/{page}", name="_admin_browse")
     * @Template()
     */
    public function adminBrowseAction($entityenc, $page)
    {
		$entity = pack("H*" , $entityenc);
		$em = $this->getDoctrine()->getEntityManager();
//		$metadata = $em->getMetadataFactory()->getMetadataFor($entity);
		
//		$repository = $this->getDoctrine()->getRepository($entity);
		$repository = $em->getRepository($entity);
		$res = $repository->findAll();
		$items = array();
		foreach ($res as $r)
		{
		  $id = $r->getId();
		  $name = $id;
		  if (method_exists($r, '__toString')) $name = $r->__toString();
  	      $editurl =  $this->generateUrl('_admin_edit', array('entityenc' => $entityenc, 'id' => $id));
//		  $items[] = array('id' => $r->getId(), 'name' => $r->getId());
		  $items[] = array('name' => $name, 'editurl' => $editurl);
		}
		
  	    $addurl =  $this->generateUrl('_admin_add', array('entityenc' => $entityenc));

		// if the slug is valid, log them in and redirect to the password change page
		return array('entity' => $entity, 'metadata' => $items);
    }

    /**
     * @Route("/edit/{entityenc}/{id}", name="_admin_edit")
     * @Template()
     */
    public function adminEditAction(Request $request, $entityenc, $id)
    {
		$entity = pack("H*" , $entityenc);
  	    $editurl =  $this->generateUrl('_admin_edit', array('entityenc' => $entityenc, 'id' => $id));
		
		$em = $this->getDoctrine()->getEntityManager();
		$repository = $em->getRepository($entity);
		$adminobj = $repository->find($id);

		$metadata = $em->getMetadataFactory()->getMetadataFor($entity);

///		$formbuilder = $this->createFormBuilder($adminobj);
//		foreach ($metadata->getColumnNames() as $columnName)
//		{
//		  $propertyName = $metadata->getFieldName($columnName);
//		  $formbuilder->add($propertyName);
//		}
//        $form = $formbuilder->getForm();
        $form = $this->getEntityForm($adminobj, $metadata);

		// if the slug is valid, log them in and redirect to the password change page
		return array('editurl' => $editurl, 'form' => $form->createView(), 'entity' => $entity);
    }

    /**
     * @Route("/add/{entityenc}", name="_admin_add")
     * @Template()
     */
    public function adminAddAction(Request $request, $entityenc)
    {
		$entity = pack("H*" , $entityenc);
  	    $addurl =  $this->generateUrl('_admin_add', array('entityenc' => $entityenc));
		
		$em = $this->getDoctrine()->getEntityManager();
		$repository = $em->getRepository($entity);
		$adminobj = new $entity;   //	$repository->find($id);
		$metadata = $em->getMetadataFactory()->getMetadataFor($entity);
        $form = $this->getEntityForm($adminobj, $metadata);

		// if the slug is valid, log them in and redirect to the password change page
		return array('addurl' => $addurl, 'form' => $form->createView(), 'entity' => $entity);
    }
}
