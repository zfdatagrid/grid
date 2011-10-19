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
    
    /**
     * @OneToMany(targetEntity="Bug", mappedBy="assignedTo")
     */
    private $bugsToReview;
    
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
    
    public function getBugsToReview()
    {
        return $this->bugsToReview;
    }

    public function setBugsToReview($bugsToReview)
    {
        $this->bugsToReview = $bugsToReview;
    }
}