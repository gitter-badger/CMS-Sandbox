<?php

namespace SmartCore\Bundle\MediaBundle\Service;

use Doctrine\ORM\EntityManager;
use SmartCore\Bundle\MediaBundle\Entity\File;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaCloudService
{
    use ContainerAwareTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CollectionService[]
     */
    protected $collections;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em        = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @param int $id
     * @return CollectionService
     */
    public function getCollection($id)
    {
        if (!isset($this->collections[$id])) {
            $this->collections[$id] = new CollectionService($this->container, $id);
        }

        return $this->collections[$id];
    }

    /**
     * Получить ссылку на файл.
     *
     * @param int $id
     * @param string $filer
     * @return string
     *
     * @todo кеширование.
     */
    public function getFileUrl($id, $filer = null)
    {
        if (!is_numeric($id)) {
            return null;
        }

        /** @var File $file */
        $file = $this->em->getRepository('SmartMediaBundle:File')->find($id);

        if (empty($file)) {
            return null;
        }

        return $this->getCollection($file->getCollection()->getId())->get($id, $filer);
    }

    public function createCollection()
    {
        // @todo
    }

    public function removeCollection()
    {
        // @todo
    }

    public function getCollectionsList()
    {
        // @todo
    }

    public function getStoragesList()
    {
        // @todo
    }

    public function createStorage()
    {
        // @todo
    }

    public function removeStorage()
    {
        // @todo
    }

    public function updateStorage()
    {
        // @todo
    }
}
