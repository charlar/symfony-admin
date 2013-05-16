<?php

namespace CRL\AdminBundle\Database;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class DoctrineDatabase	// extends AbstractDatabase
{
    private $container;
	private $em;
	public $usehash;

    public function __construct(ContainerAwareInterface $container)
    {
        $this->container = $container;
		$this->em = $this->container->getDoctrine()->getEntityManager();
		$config = $this->container->getContainer()->getParameter('crl_admin.config');
		$this->usehash = $config['hash'];
    }
	
	public function getRepository($name)
	{
	  return $this->em->getRepository($name);
	}
	
	public function getContainer()
	{
	  return $this->container;
	}
	
	public function getAllEntities()
	{
		$metadatas = $this->em->getMetadataFactory()->getAllMetadata();
		$entities = array();
		foreach ($metadatas as $md)
		{
		    $entity = new DoctrineEntity($this, $md);
			$entities[] = $entity;
		}
		return $entities;
	}
	
	public function getEntityBySlug($slug)
	{
		foreach ($this->getAllEntities() as $ent)
		{
		  if ($ent->getEntitySlug() == $slug)
		  		return $ent;
		}
		return null;
	}
	
	public function persistEntity($entity)
	{
		$this->em->persist($entity);
		$this->em->flush();
	}

	public function deleteEntity($entity)
	{
		$this->em->remove($entity);
		$this->em->flush();
	}

}
