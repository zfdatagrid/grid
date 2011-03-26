<?php
namespace My\Entity;

/**
 * @Entity
 * @Table(name="products")
 */
class Product
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(name="product_id", type="integer")
     */
    private $id;

    /**
     * @Column(name="product_name")
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}