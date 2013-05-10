symfony-admin
=============

## Installing the admin bundle.

The admin bundle should install as a bundle using composer.

In the alternative, follow the following steps:

	1) Place the CRL/Admin directory with CRL rooted
	either in /Symfony/src or in /Symfony/vendor/bundles
	
	2) In Symfony/web/AppKernal.php, in register bundles add:
	   new CRL\AdminBundle\CRLAdminBundle(),
	
	3) In routing.yml (or routing_dev.yml) add:
	_admin:
		resource: "@CRLAdminBundle/Controller/AdminController.php"
		type:     annotation
		prefix:   /admin

## Using the admin bundle

	1) Navigate to /admin, you will get a list of Entities.
	2) each data object will have a link to view structure, browse data and add.
	3) if you browse data, you will get a list of data objects. the objects will be listed
		by id, or by the data returned from __toString(), if it is implemented on the Entity
	4) clicking on the entity will allow you to edit it.
	5) from the data object main page, clicking add, will allow you to create a new object
	
## Bugs:

This is a proof of concept, and certainly rife with bugs.  Contributions are welcome.  I wish
a well developed version of this had been part of the standard Symfony distribution, like
Django has a built in admin package.
	
