<?php

namespace SmartCore\Bundle\Unicat2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="unicat2_structures")
 */
class UnicatStructure
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $position;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="string")
     */
    protected $title_form;

    /**
     * single | multi
     *
     * @ORM\Column(type="string", length=16)
     */
    protected $entries;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $is_required;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $is_default_inheritance;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $properties;

    /**
     * @var UnicatRepository
     *
     * @ORM\ManyToOne(targetEntity="UnicatRepository", inversedBy="structures")
     */
    protected $repository;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->position   = 0;
        $this->properties = null;
        $this->user_id    = 0;
        $this->is_default_inheritance = false;
        $this->is_required = true;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param string $entries
     * @return $this
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;

        return $this;
    }

    /**
     * @param bool $is_default_inheritance
     * @return $this
     */
    public function setIsDefaultInheritance($is_default_inheritance)
    {
        if (empty($is_default_inheritance)) {
            $is_default_inheritance = 0;
        }

        $this->is_default_inheritance = $is_default_inheritance;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefaultInheritance()
    {
        return $this->is_default_inheritance;
    }

    /**
     * @param bool $is_required
     * @return $this
     */
    public function setIsRequired($is_required)
    {
        if (empty($is_required)) {
            $is_required = 0;
        }

        $this->is_required = $is_required;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRequired()
    {
        return $this->is_required;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $properties
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param UnicatRepository $repository
     * @return $this
     */
    public function setRepository(UnicatRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return UnicatRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title_form
     * @return $this
     */
    public function setTitleForm($title_form)
    {
        $this->title_form = $title_form;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitleForm()
    {
        return $this->title_form;
    }

    /**
     * @param int $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        if (is_object($user_id) and method_exists($user_id, 'getId')) {
            $user_id = $user_id->getId();
        }

        if (null === $user_id) {
            $user_id = 0;
        }

        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}
