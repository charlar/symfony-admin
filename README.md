symfony-admin
=============

## Installing the admin bundle.

#### Using Composer

The admin bundle should install as a bundle using composer.  If you try to do this, I
would appreciate your feedback.  Composer does not run on the shared server where my
development is hosted.   It takes to long to run and the job gets aborted before completion.

#### Manually adding

As an alternative to composer, use the following steps:

	1) Place the CRL/Admin directory with CRL rooted
	either in /Symfony/src or in /Symfony/vendor/bundles
	
	2) In Symfony/web/AppKernal.php, in register bundles add:
	   new CRL\AdminBundle\CRLAdminBundle(), (depending on the version of symfony, you may
	   have to manually edit the Autoloads file.
	
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

## Configuration

No configuration is required.  However you can add configuration to create browseing
layouts for your entities.  The is also a switch called hash to create shorter urls for your
entites using hashes intead of hex encoded strings.  The configurations are not currently
used.

## Known Issues

	1) If not all fields of your entity have a get function, Symfony will crash when
	you try to edit that entity.
	2) If you have a one to one relationship and the target entity does not have a __toString
	function defined, Symfony will crash when you try to edit that entity.
	
## Bugs:

This is a proof of concept, and certainly rife with bugs.  Contributions are welcome.  I wish
a well developed version of this had been part of the standard Symfony distribution, like
Django has a built in admin package.
	
