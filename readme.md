[Nette Framework](http://nette.org) - Gedmo Extension
=====================================================

Library for easy integration [Gedmo Doctrine Extension](https://github.com/l3pp4rd/DoctrineExtensions)
to _Nette Framework_.

Requirements
------------

- PHP 5.3.2 or later
- Nette Framework 2.0.0 or later
- Gedmo 2.3.2 or later
- Doctrine ORM 2.2 or later / Doctrine ODM 1.0.0 Beta 5 or later


Installation
------------

Add `"nella/gedmo": "*"` to *composer.json and run `composer update`.
Edit your *bootstrap.php* and add `Nella\Doctrine\Config\GedmoExtension::register($configurator);`
before `$configurator->createContainer()`.


-----

For more info please follow [documentaion](http://doc.nellafw.org/en/gedmo).
