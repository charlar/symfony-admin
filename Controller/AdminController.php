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

use CRL\AdminBundle\Database\DoctrineDatabase;

class AdminController extends Controller
{
	private function getAddURL($ent)
	{
  	  return $this->generateUrl('_admin_add', array('entityenc' => $ent->getEntitySlug()));
	}

	private function getStructURL($ent)
	{
		return $this->generateUrl('_admin_struct', array('entityenc' => $ent->getEntitySlug()));
	}
	
	private function getBrowseURL($ent,$page)
	{
		return $this->generateUrl('_admin_browse', array('entityenc' => $ent->getEntitySlug(), 'page' => $page));
	}
	
	private function getEditURL($ent,$id)
	{
		return $this->generateUrl('_admin_edit', array('entityenc' => $ent->getEntitySlug(), 'id' => $id));
	}

	private function getDeleteURL($ent,$id)
	{
		return $this->generateUrl('_admin_delete', array('entityenc' => $ent->getEntitySlug(), 'id' => $id));
	}

    /**
     * @Route("/", name="_admin")
     * @Template()
     */
    public function indexAction()
    {
		$db = new DoctrineDatabase($this);
		
		$dbentities = $db->getAllEntities();

		$entities = array();
		$namespace = array();
		foreach ($dbentities as $dbe)
		{
		   $entityenc = $dbe->getEntitySlug();
		   $structurl = $this->getStructURL($dbe);
		   $browseurl =  $this->getBrowseURL($dbe,0);
  	       $addurl =  $this->getAddURL($dbe);
		   $entity = array('name' => $dbe->getName(),
		   				   'namespace' => $dbe->getNamespace(),
		                   'structurl' => $structurl,
						   'browseurl' => $browseurl,
						   'addurl' => $addurl
		     		);
			$entities[] = $entity;
			$namespace[$dbe->getNamespace()] = array();
		}
	
		foreach ($entities as $ent)
		{
		  $namespace[$ent['namespace']][] = $ent;
		}

		return array('metadatas' => $entities, 'sortedmetadatas' => $namespace);
    }
	
    /**
     * @Route("/settings", name="_admin_settings")
     * @Template()
     */
    public function adminSettingsAction()
    {
		$config = $this->container->getParameter('crl_admin.config');
		return array('config' => var_export($config, TRUE));
   }

    /**
     * @Route("/struct/{entityenc}", name="_admin_struct")
     * @Template()
     */
    public function adminStructAction($entityenc)
    {
		$db = new DoctrineDatabase($this);
		$ent = $db->getEntityBySlug($entityenc);
		$metadata = $ent->getMetadata();
		return array('entity' => $ent->getName(), 'metadata' => var_export($metadata, TRUE));
   }

    /**
     * @Route("/browse/{entityenc}/{page}", name="_admin_browse")
     * @Template()
     */
    public function adminBrowseAction($entityenc, $page)
    {
	    $count = 10;
		$db = new DoctrineDatabase($this);
		$ent = $db->getEntityBySlug($entityenc);
		$entity = $ent->getName();
		$items = array();
		foreach ($ent->getObjects($page * $count, $count) as $item)
		{
		  $editurl = $this->getEditURL($ent,$item['id']);
		  $deleteurl = $this->getDeleteURL($ent,$item['id']);
		  $items[] = array('name' => $item['name'],
		  				 'editurl' => $editurl, 'deleteurl' => $deleteurl);
		}
		
  	    $addurl =  $this->getAddURL($ent);
  	    $nexturl = $this->getBrowseURL($ent,$page+1);
		$prevurl = null;
		if ($page > 0) {
  	    	$prevurl = $this->getBrowseURL($ent,$page-1);
		}
		

		// if the slug is valid, log them in and redirect to the password change page
		return array('entity' => $entity, 'metadata' => $items,
		             'addurl' => $addurl, 'nexturl' => $nexturl, 'prevurl' => $prevurl);
    }

    /**
     * @Route("/edit/{entityenc}/{id}", name="_admin_edit")
     * @Template()
     */
    public function adminEditAction(Request $request, $entityenc, $id)
    {
		$db = new DoctrineDatabase($this);
		$ent = $db->getEntityBySlug($entityenc);
		$entity = $ent->getName();
		$editurl =  $this->getEditURL($ent, $id);
		
		$adminobj = $ent->getObjectById($id);
		$form = $ent->getEntityForm($adminobj);
		
		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);		// change to bind in 2.1
			$ent->persistEntity($adminobj);		// does the flush
		}


		return array('editurl' => $editurl, 'form' => $form->createView(), 'entity' => $entity);
    }

    /**
     * @Route("/delete/{entityenc}/{id}", name="_admin_delete")
     * @Template()
     */
    public function adminDeleteAction(Request $request, $entityenc, $id)
    {
		$db = new DoctrineDatabase($this);
		$ent = $db->getEntityBySlug($entityenc);
		$entity = $ent->getName();
		$deleteurl =  $this->getDeleteURL($ent, $id);
		
		$adminobj = $ent->getObjectById($id);
		$form = $ent->getEntityForm($adminobj);
		
		if ($request->getMethod() == 'POST') {
			// delete the object
			$ent->deleteEntity($adminobj);		// does the flush
			// redirect to the list using 303 - see other
		  $browse_url =  $this->getBrowseURL($ent,0);
	      return $this->redirect($browse_url, 303);
		}


		return array('deleteurl' => $deleteurl, 'form' => $form->createView(), 'entity' => $entity);
    }

    /**
     * @Route("/add/{entityenc}", name="_admin_add")
     * @Template()
     */
    public function adminAddAction(Request $request, $entityenc)
    {
		$db = new DoctrineDatabase($this);
		$ent = $db->getEntityBySlug($entityenc);
		$entity = $ent->getName();
  	    $addurl =  $this->getAddURL($ent);
		$adminobj = $ent->getNewObject();
		$form = $ent->getEntityForm($adminobj);
		
		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);		// change to bind in 2.1
			$ent->persistEntity($adminobj);		// does the flush
		}

		return array('addurl' => $addurl, 'form' => $form->createView(), 'entity' => $entity);
    }
}
