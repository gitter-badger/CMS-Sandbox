<?php

namespace SmartCore\Bundle\Unicat2Bundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use SmartCore\Bundle\MediaBundle\Service\CollectionService;
use SmartCore\Bundle\Unicat2Bundle\Entity\UnicatRepository;
use SmartCore\Bundle\Unicat2Bundle\Entity\UnicatStructure;
use SmartCore\Bundle\Unicat2Bundle\Form\Type\CategoryFormType;
use SmartCore\Bundle\Unicat2Bundle\Form\Type\ItemFormType;
use SmartCore\Bundle\Unicat2Bundle\Form\Type\PropertiesGroupFormType;
use SmartCore\Bundle\Unicat2Bundle\Form\Type\StructureFormType;
//use SmartCore\Bundle\Unicat2Bundle\Model\CategoryModel;
use SmartCore\Bundle\Unicat2Bundle\Model\ItemModel;
use SmartCore\Bundle\Unicat2Bundle\Model\PropertiesGroupModel;
use SmartCore\Bundle\Unicat2Bundle\Model\PropertyModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UnicatRepositoryManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var CollectionService
     */
    protected $mc;

    /**
     * @var UnicatRepository
     */
    protected $repository;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param EntityManager $em
     * @param FormFactoryInterface $formFactory
     * @param UnicatRepository $repository
     * @param CollectionService $mc
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        EntityManager $em,
        FormFactoryInterface $formFactory,
        UnicatRepository $repository,
        CollectionService $mc,
        SecurityContextInterface $securityContext
    ) {
        $this->em          = $em;
        $this->formFactory = $formFactory;
        $this->mc          = $mc;
        $this->repository  = $repository;
        $this->securityContext = $securityContext;
    }

    /**
     * @return UnicatRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param array|null $orderBy
     * @return ItemModel|null
     */
    public function findAllItems($orderBy = null)
    {
        return $this->em->getRepository($this->repository->getItemClass())->findBy([], $orderBy);
    }

    /**
     * @param CategoryModel $category
     * @param array $order
     * @return ItemModel[]|null
     *
     * @todo сделать настройку сортировки
     */
    public function findItemsInCategory(CategoryModel $category, array $order = ['position' => 'ASC'])
    {
        $itemEntity = $this->repository->getItemClass();

        $query = $this->em->createQuery("
           SELECT i
           FROM $itemEntity AS i
           JOIN i.categoriesSingle AS cs
           WHERE cs.id = :category
           AND i.is_enabled = 1
           ORDER BY i.position ASC
        ")->setParameter('category', $category->getId());

        return $query->getResult();
    }

    /**
     * @param string|int $val
     * @return ItemModel|null
     */
    public function findItem($val)
    {
        $key = intval($val) ? 'id' : 'slug';

        return $this->em->getRepository($this->repository->getItemClass())->findOneBy([$key => $val]);
    }

    /**
     * @param string $slug
     * @return CategoryModel[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function findCategoriesBySlug($slug = null)
    {
        $categories = [];
        $parent = null;
        foreach (explode('/', $slug) as $categoryName) {
            if (strlen($categoryName) == 0) {
                break;
            }

            /** @var ItemModel $category */
            $category = $this->getItemRepository()->findOneBy([
                'is_enabled' => true,
                'parent' => $parent,
                'slug'   => $categoryName,
                'type'   => ItemModel::TYPE_CATEGORY,
            ]);

            if ($category) {
                $categories[] = $category;
                $parent = $category;
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $categories;
    }

    /**
     * @param int|null $id
     * @return null|ItemModel
     */
    public function getCategory($id = null)
    {
        return $this->em->getRepository($this->getItemClass())->findOneBy([
            'id'    => $id,
            'type'  => ItemModel::TYPE_CATEGORY,
        ]);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     *
     * @deprecated
     */
    public function getCategoryRepository()
    {
        return $this->em->getRepository($this->repository->getCategoryClass());
    }

    /**
     * @return string
     */
    public function getItemClass()
    {
        return $this->repository->getItemClass();
    }

    /**
     * @return string
     *
     * @deprecated
     */
    public function getCategoryClass()
    {
        return $this->repository->getCategoryClass();
    }

    /**
     * @return UnicatStructure
     */
    public function getDefaultStructure()
    {
        return $this->repository->getDefaultStructure();
    }

    /**
     * @param UnicatStructure $structure
     * @param array $options
     * @param ItemModel|null $parent_category
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getCategoryCreateForm(UnicatStructure $structure, array $options = [], ItemModel $parent_category = null)
    {
        $category = $structure->getRepository()->createCategory();

        $category
            ->setStructure($structure)
            ->setIsInheritance($structure->getIsDefaultInheritance())
            ->setUserId($this->getUserId())
        ;

        if ($parent_category) {
            $category->setParent($parent_category);
        }

        return $this->formFactory->create(new CategoryFormType($this->repository), $category, $options)
            ->add('create', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]]);
    }

    /**
     * @param mixed $data    The initial data for the form
     * @param array $options
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getCategoryFormType($data = null, array $options = [])
    {
        $form = $this->formFactory->create(new CategoryFormType($this->repository), $data, $options);

        $form
            ->add('create', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]])
            ->add('cancel', 'submit', ['attr' => [ 'class' => 'btn', 'formnovalidate' => 'formnovalidate' ]]);

        return $form;
    }

    /**
     * @param mixed $data    The initial data for the form
     * @param array $options
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getItemEditForm($data = null, array $options = [])
    {
        return $this->getItemForm($data, $options)
            ->add('update', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]])
            ->add('delete', 'submit', ['attr' => [ 'class' => 'btn btn-danger', 'onclick' => "return confirm('Вы уверены, что хотите удалить запись?')" ]])
            ->add('cancel', 'submit', ['attr' => [ 'class' => 'btn', 'formnovalidate' => 'formnovalidate' ]]);
    }

    /**
     * @param mixed $data    The initial data for the form
     * @param array $options
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getItemForm($data = null, array $options = [])
    {
        return $this->formFactory->create(new ItemFormType($this->repository), $data, $options);
    }

    /**
     * @param mixed $data    The initial data for the form
     * @param array $options
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getItemCreateForm($data = null, array $options = [])
    {
        return $this->getItemForm($data, $options)
            ->add('create', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]])
            ->add('cancel', 'submit', ['attr' => [ 'class' => 'btn', 'formnovalidate' => 'formnovalidate' ]]);
    }

    /**
     * @param array $options
     * @return $this|\Symfony\Component\Form\Form
     */
    public function getStructureCreateForm(array $options = [])
    {
        $structure = new UnicatStructure();
        $structure->setRepository($this->repository);

        return $this->getStructureForm($structure, $options)
            ->add('create', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]])
            ->add('cancel', 'submit', ['attr' => [ 'class' => 'btn', 'formnovalidate' => 'formnovalidate' ]]);
    }

    /**
     * @param array $options
     * @return $this|\Symfony\Component\Form\Form
     */
    public function getStructureEditForm($data = null, array $options = [])
    {
        return $this->getStructureForm($data, $options)
            ->add('update', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]])
            ->add('cancel', 'submit', ['attr' => [ 'class' => 'btn', 'formnovalidate' => 'formnovalidate' ]]);
    }

    /**
     * @param array $options
     * @return $this|\Symfony\Component\Form\Form
     */
    public function getPropertiesGroupCreateForm(array $options = [])
    {
        $group = $this->repository->createPropertiesGroup();
        $group->setRepository($this->repository);

        return $this->getPropertiesGroupForm($group, $options)
            ->add('create', 'submit', ['attr' => [ 'class' => 'btn btn-success' ]])
            ->add('cancel', 'submit', ['attr' => [ 'class' => 'btn', 'formnovalidate' => 'formnovalidate' ]]);
    }

    /**
     * @param mixed|null $data
     * @param array $options
     * @return \Symfony\Component\Form\Form
     */
    public function getStructureForm($data = null, array $options = [])
    {
        return $this->formFactory->create(new StructureFormType(), $data, $options);
    }

    /**
     * @param mixed|null $data
     * @param array $options
     * @return \Symfony\Component\Form\Form
     */
    public function getPropertiesGroupForm($data = null, array $options = [])
    {
        return $this->formFactory->create(new PropertiesGroupFormType($this->repository), $data, $options);
    }

    /**
     * @param int $id
     * @return UnicatStructure
     */
    public function getStructure($id)
    {
        return $this->em->getRepository('Unicat2Bundle:UnicatStructure')->find($id);
    }

    /**
     * @return ItemModel
     */
    public function createItemEntity()
    {
        $class = $this->repository->getItemClass();

        return new $class;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return $this
     *
     * @todo события
     */
    public function createItem(FormInterface $form, Request $request)
    {
        return $this->saveItem($form, $request);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return $this
     *
     * @todo события
     */
    public function updateItem(FormInterface $form, Request $request)
    {
        return $this->saveItem($form, $request);
    }

    /**
     * @param ItemModel $item
     * @return $this
     *
     * @todo события
     */
    public function removeItem(ItemModel $item)
    {
        foreach ($this->getProperties() as $property) {
            if ($property->isType('image') and $item->hasProperty($property->getName()) ) {
                // @todo сделать кеширование при первом же вытаскивании данных о записи. тоже самое в saveItem(), а еще лучше выделить этот код в отельный защищенный метод.
                $tableItems = $this->em->getClassMetadata($this->repository->getItemClass())->getTableName();
                $sql = "SELECT * FROM $tableItems WHERE id = '{$item->getId()}'";
                $res = $this->em->getConnection()->query($sql)->fetch();

                $fileId = null;
                if (!empty($res)) {
                    $previousProperties = unserialize($res['properties']);
                    $fileId = $previousProperties[$property->getName()];
                }

                $this->mc->remove($fileId);
            }
        }

        $this->em->remove($item);
        $this->em->flush(); // Надо делать полный flush т.к. каскадом удаляются связи с категориями.

        return $this;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return $this|array
     */
    public function saveItem(FormInterface $form, Request $request)
    {
        /** @var ItemModel $item */
        $item = $form->getData();

        // Проверка и модификация свойств. В частности загрука картинок и валидация.
        foreach ($this->getProperties() as $property) {
            if ($property->isType('image') and $item->hasProperty($property->getName()) ) {
                $tableItems = $this->em->getClassMetadata($this->repository->getItemClass())->getTableName();
                $sql = "SELECT * FROM $tableItems WHERE id = '{$item->getId()}'";
                $res = $this->em->getConnection()->query($sql)->fetch();

                if (!empty($res)) {
                    $previousProperties = unserialize($res['properties']);
                    $fileId = $previousProperties[$property->getName()];
                } else {
                    $fileId = null;
                }

                // удаление файла.
                $_delete_ = $request->request->get('_delete_');
                if (is_array($_delete_)
                    and isset($_delete_['property:' . $property->getName()])
                    and 'on' === $_delete_['property:' . $property->getName()]) {
                    $this->mc->remove($fileId);
                    $fileId = null;
                } else {
                    $file = $item->getProperty($property->getName());

                    if ($file) {
                        $this->mc->remove($fileId);
                        $fileId = $this->mc->upload($file);
                    }
                }

                $item->setProperty($property->getName(), $fileId);
            }
        }

        //@todo $structuresColection = $this->em->getRepository($repository->getCategoryClass())->findIn($structures);

        $pd = $request->request->get($form->getName());

        $structures = [];
        foreach ($pd as $key => $val) {
            if (false !== strpos($key, 'structure:')) {
                //$name = str_replace('structure:', '', $key);
                //$structures[$name] = $val;

                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $structures[] = $val2;
                    }
                } else {
                    $structures[] = $val;
                }
            }
        }

        $request->request->set($form->getName(), $pd);

        // @todo убрать выборку структур в StructureRepository (Entity)
        /*
        $list_string = '';
        foreach ($structures as $node_id) {
            $list_string .= $node_id . ',';
        }

        $list_string = substr($list_string, 0, strlen($list_string)-1);

        ld($list_string);

        if (false == $list_string) {
            return [];
        }

        $structuresSingleColection = $this->em->createQuery("
            SELECT c
            FROM {$this->repository->getCategoryClass()} c
            WHERE c.id IN({$list_string})
        ")->getResult();

        //$structuresCollection = new ArrayCollection(); // @todo наследуемые категории.

        $item->setCategories($structuresSingleColection)
            ->setCategoriesSingle($structuresSingleColection);
        */

        $this->em->persist($item);
        $this->em->flush($item);

        return $this;
    }

    /**
     * @param UnicatRepository $repository
     * @param int $groupId
     * @return PropertyModel[]
     */
    public function getProperties($groupId = null)
    {
        $filter = ($groupId) ? ['group' => $groupId] : [];

        return $this->em->getRepository($this->repository->getPropertyClass())->findBy($filter, ['position' => 'ASC']);
    }

    /**
     * @param PropertyModel $property
     * @return $this
     */
    public function createProperty(PropertyModel $property)
    {
        $this->em->persist($property);
        $this->em->flush($property);

        return $this;
    }

    /**
     * @param CategoryModel $category
     * @return $this
     */
    public function updateCategory(CategoryModel $category)
    {
        $this->em->persist($category);
        $this->em->flush($category);

        return $this;
    }

    /**
     * @param PropertyModel $property
     * @return $this
     */
    public function updateProperty(PropertyModel $property)
    {
        $this->em->persist($property);
        $this->em->flush($property);

        return $this;
    }

    /**
     * @param PropertiesGroupModel $property
     * @return $this
     */
    public function updatePropertiesGroup(PropertiesGroupModel $entity)
    {
        $this->em->persist($entity);
        $this->em->flush($entity);

        return $this;
    }

    /**
     * @param UnicatStructure $entity
     * @return $this
     */
    public function updateStructure(UnicatStructure $entity)
    {
        $this->em->persist($entity);
        $this->em->flush($entity);

        return $this;
    }

    /**
     * @return int
     *
     * @deprecated
     */
    protected function getUserId()
    {
        if (null === $securityContext = $this->securityContext) {
            return 0;
        }

        if (null === $token = $this->securityContext->getToken()) {
            return 0;
        }

        if (!is_object($user = $token->getUser())) {
            return 0;
        }

        return $user->getId();
    }
}
