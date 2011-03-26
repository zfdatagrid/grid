<?php
namespace My\Entity;

/**
 * @Entity
 * @Table(name="accounts")
 */
class Account
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(name="Id", type="integer")
     */
    private $Id;

    /**
     * @Column(name="account_name")
     */
    private $name;

    public function getId()
    {
        return $this->Id;
    }

    public function setId($id)
    {
        $this->Id = $id;
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