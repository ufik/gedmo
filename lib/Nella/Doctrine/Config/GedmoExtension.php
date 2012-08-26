<?php
/**
 * This file is part of the Nella Framework (http://nellafw.org).
 *
 * Copyright (c) 2006, 2012 Patrik Votoček (http://patrik.votocek.cz)
 *
 * For the full copyright and license information,
 * please view the file LICENSE.txt that was distributed with this source code.
 */

namespace Nella\Doctrine\Config;

use Doctrine\Common\Persistence\ObjectManager,
	Doctrine\Common\EventSubscriber,
	Nette\Config\Configurator,
	Nette\Config\Compiler,
	Nette\Utils\Strings;

/**
 * Gedmo Nella Framework services.
 *
 * @author	Patrik Votoček
 */
class GedmoExtension extends \Nette\Config\CompilerExtension
{
	/** @var array */
	public $defaults = array(
		'orm' => array(
			'em' => NULL,
			'loggable' => FALSE,
			'sluggable' => FALSE,
			'softDeleteable' => FALSE,
			'sortable' => FALSE,
			'timestampable' => FALSE,
			'translatable' => FALSE,
			'tree' => FALSE,
			'uploadable' => FALSE,
		),
		'odm' => array(
			'dm' => NULL,
			'loggable' => FALSE,
			'sluggable' => FALSE,
			'sortable' => FALSE,
			'timestampable' => FALSE,
			'translatable' => FALSE,
			'tree' => FALSE,
		),
	);

	/**
	 * Processes configuration data
	 *
	 * @throws \Nette\InvalidStateException
	 */
	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$ref = \Nette\Reflection\ClassType::from('Gedmo\DoctrineExtensions');
		$baseDir = pathinfo($ref->getFileName(), PATHINFO_DIRNAME);

		\Gedmo\DoctrineExtensions::registerAnnotations();

		if (!is_array($config['orm']['em'])) {
			$config['orm']['em'] = is_null($config['orm']['em']) ? array() : array($config['orm']['em']);
		}
		if (!is_array($config['odm']['dm'])) {
			$config['odm']['dm'] = is_null($config['odm']['dm']) ? array() : array($config['odm']['dm']);
		}

		$loggable = $builder->addDefinition($this->prefix('loggable'))
			->setClass('Gedmo\Loggable\LoggableListener')
			->setAutowired(FALSE);

		$sluggable = $builder->addDefinition($this->prefix('sluggable'))
			->setClass('Gedmo\Sluggable\SluggableListener')
			->setAutowired(FALSE);

		$softDeleteable = $builder->addDefinition($this->prefix('softDeleteable'))
			->setClass('Gedmo\SoftDeleteable\SoftDeleteableListener')
			->setAutowired(FALSE);

		$sortable = $builder->addDefinition($this->prefix('sortable'))
			->setClass('Gedmo\Sortable\SortableListener')
			->setAutowired(FALSE);

		$timestampable = $builder->addDefinition($this->prefix('timestampable'))
			->setClass('Gedmo\Timestampable\TimestampableListener')
			->setAutowired(FALSE);

		$translatable = $builder->addDefinition($this->prefix('translatable'))
			->setClass('Gedmo\Translatable\TranslatableListener')
			->setAutowired(FALSE);

		$tree = $builder->addDefinition($this->prefix('tree'))
			->setClass('Gedmo\Tree\TreeListener')
			->setAutowired(FALSE);

		$uploadable = $builder->addDefinition($this->prefix('uploadable'))
			->setClass('Gedmo\Uploadable\UploadableListener')
			->setAutowired(FALSE);

		foreach ($config['orm']['em'] as $omName) {
			$om = $builder->getDefinition($this->removeAt($omName));

			if ($config['orm']['loggable']) {
				$om->addSetup(get_called_class() . '::registerLoggable', array($om, $baseDir, $loggable));
			}

			if ($config['orm']['sluggable']) {
				$om->addSetup(get_called_class() . '::registerSluggable', array($om, $sluggable));
			}

			if ($config['orm']['softDeleteable']) {
				$om->addSetup(get_called_class() . '::registerSoftDeleteable', array($om, $softDeleteable));
			}

			if ($config['orm']['sortable']) {
				$om->addSetup(get_called_class() . '::registerSortable', array($om, $sortable));
			}

			if ($config['orm']['timestampable']) {
				$om->addSetup(get_called_class() . '::registerTimestampable', array($om, $timestampable));
			}

			if ($config['orm']['translatable']) {
				$om->addSetup(get_called_class() . '::registerTranslatable', array($om, $baseDir, $translatable));
			}

			if ($config['orm']['tree']) {
				$om->addSetup(get_called_class() . '::registerTree', array($om, $baseDir, $tree));
			}

			if ($config['orm']['uploadable']) {
				$om->addSetup(get_called_class() . '::registerUploadable', array($om, $uploadable));
			}
		}

		foreach ($config['odm']['dm'] as $omName) {
			$om = $builder->getDefinition($this->removeAt($omName));

			if ($config['odm']['loggable']) {
				$om->addSetup(get_called_class() . '::registerLoggable', array($om, $baseDir, $loggable));
			}

			if ($config['odm']['sluggable']) {
				$om->addSetup(get_called_class() . '::registerSluggable', array($om, $sluggable));
			}

			if ($config['odm']['sortable']) {
				$om->addSetup(get_called_class() . '::registerSortable', array($om, $sortable));
			}

			if ($config['odm']['timestampable']) {
				$om->addSetup(get_called_class() . '::registerTimestampable', array($om, $timestampable));
			}

			if ($config['odm']['translatable']) {
				$om->addSetup(get_called_class() . '::registerTranslatable', array($om, $baseDir, $translatable));
			}

			if ($config['odm']['tree']) {
				$om->addSetup(get_called_class() . '::registerTree', array($om, $baseDir, $tree));
			}
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	private function removeAt($name)
	{
		if (Strings::startsWith($name, '@')) {
			return Strings::substring($name, 1);
		}
		return $name;
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param \Doctrine\Common\EventSubscriber
	 */
	public static function registerLoggable(ObjectManager $om, $baseDir, EventSubscriber $listener)
	{
		$driver = $om->getConfiguration()->getMetadataDriverImpl();

		if ($driver instanceof \Doctrine\ORM\Mapping\Driver\AnnotationDriver) {
			$driver->getPaths(array($baseDir . '/Loggable/Entity'));
		} elseif ($driver instanceof \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver) {
			$driver->getPaths(array($baseDir . '/Loggable/Document'));
		}

		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param \Doctrine\Common\EventSubscriber
	 */
	public static function registerSluggable(ObjectManager $om, EventSubscriber $listener)
	{
		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param \Doctrine\Common\EventSubscriber
	 */
	public static function registerSoftDeleteable(ObjectManager $om, EventSubscriber $listener)
	{
		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param \Doctrine\Common\EventSubscriber
	 */
	public static function registerSortable(ObjectManager $om, EventSubscriber $listener)
	{
		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param EventSubscriber
	 */
	public static function registerTimestampable(ObjectManager $om, EventSubscriber $listener)
	{
		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param EventSubscriber
	 */
	public static function registerTranslatable(ObjectManager $om, $baseDir, EventSubscriber $listener)
	{
		$driver = $om->getConfiguration()->getMetadataDriverImpl();

		if ($driver instanceof \Doctrine\ORM\Mapping\Driver\AnnotationDriver) {
			$driver->getPaths(array($baseDir . '/Translatable/Entity'));
		} elseif ($driver instanceof \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver) {
			$driver->getPaths(array($baseDir . '/Translatable/Document'));
		}

		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param string
	 * @param \Doctrine\Common\EventSubscriber
	 */
	public static function registerTree(ObjectManager $om, $baseDir, EventSubscriber $listener)
	{
		$driver = $om->getConfiguration()->getMetadataDriverImpl();

		if ($driver instanceof \Doctrine\ORM\Mapping\Driver\AnnotationDriver) {
			$driver->getPaths(array($baseDir . '/Tree/Entity'));
		}

		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager
	 * @param \Doctrine\Common\EventSubscriber
	 */
	public static function registerUploadable(ObjectManager $om, EventSubscriber $listener)
	{
		if ($om instanceof \Doctrine\ORM\EntityManager || $om instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$om->getEventManager()->addEventSubscriber($listener);
		}
	}

	/**
	 * Register extension to compiler.
	 *
	 * @param \Nette\Config\Configurator
	 * @param string
	 */
	public static function register(Configurator $configurator, $name = 'gedmo')
	{
		$class = get_called_class();
		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) use ($class, $name) {
			$compiler->addExtension($name, new $class);
		};
	}
}

