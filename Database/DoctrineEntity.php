<?php

namespace CRL\AdminBundle\Database;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\PropertyAccessDeniedException;

class DoctrineEntity	// extends AbstractEntity
{
	private $md;
	private $db;
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

    public function getEntityForm($adminobj)
    {
	    $propertyAccessor = PropertyAccess::getPropertyAccessor();
		$formbuilder = $this->db->getContainer()->createFormBuilder($adminobj);
        foreach ($this->getFieldsFromMetadata($this->md) as $propertyName)
		{
		  try {
		    // this will throw an error if not accessable
			$propVal = $propertyAccessor->getValue($adminobj, $propertyName);
		    $formbuilder->add($propertyName);
			}
			// should add the property to a list in this catch, so we can
			// let the user know what properties can't be accessed.
			catch (PropertyAccessDeniedException $e) { }
			catch (Exception $e) { }
		}
        $form = $formbuilder->getForm();
		return $form;
	}
	

    public function __construct(DoctrineDatabase $db, ClassMetadata $metadata)
    {
        $this->db = $db;
		$this->md = $metadata;
    }
	
	public function getEntitySlug()
	{
		return bin2hex($this->md->name);
	}
	
	public function getName()
	{
		return $this->md->name;
	}
	
	public function getNamespace()
	{
		return $this->md->namespace;
	}

	public function getMetadata()
	{
		return $this->md;
	}
	
	public function getObjectById($id)
	{
		$repository = $this->db->getRepository($this->md->name);
		return $repository->find($id);
	}
	
	public function getNewObject()
	{
		return new $this->md->name;   //	$repository->find($id);
	}
	
	public function getObjects($start, $count)
	{
		$repository = $this->db->getRepository($this->md->name);
//		$res = $repository->findAll();
//		findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
		$res = $repository->findBy(array(),null,$count,$start);
		$items = array();
		foreach ($res as $r)
		{
		  $id = $r->getId();
		  $name = $id;
		  if (method_exists($r, '__toString')) $name = $r->__toString();
		  $items[] = array('name' => $name, 'id' => $id);
		}
	  return $items;
	}
	
	public function persistEntity($obj)
	{
		// should verify object type $this->md->name;   //	$repository->find($id);
		$this->db->persistEntity($obj);
	}
	
	public function deleteEntity($obj)
	{
		// should verify object type $this->md->name;   //	$repository->find($id);
		$this->db->deleteEntity($obj);
	}
	

}
