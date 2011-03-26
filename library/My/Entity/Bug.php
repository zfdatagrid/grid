<?php
namespace My\Entity;

/**
 * @Entity
 * @Table(name="bugs")
 */
class Bug
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(name="bug_id");
     */
    private $id;

    /**
     * @Column(name="bug_description")
     */
    private $description;

    /**
     * @Column(name="bug_status", length=20)
     */
    private $status;

    /**
     * @ManyToOne(targetEntity="Account")
     * @JoinColumn(name="reported_by", referencedColumnName="Id")
     */
    private $reporter;

    /**
     * @Column(name="next", type="boolean")
     */
    private $next;

    /**
     * @ManyToOne(targetEntity="Account")
     * @JoinColumn(name="assigned_to", referencedColumnName="Id")
     */
    private $assignedTo;

    /**
     * @ManyToOne(targetEntity="Account")
     * @JoinColumn(name="verified_by", referencedColumnName="Id")
     */
    private $verifiedBy;

    /**
     * @Column(name="time", type="datetime")
     */
    private $time;

    /**
     * @Column(name="date", type="date")
     */
    private $date;

    /**
     * @Column(name="status")
     */
    private $state;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getReporter()
    {
        return $this->reporter;
    }

    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;
    }

    public function getVerifiedBy()
    {
        return $this->verifiedBy;
    }

    public function setVerifiedBy($verifiedBy)
    {
        $this->verifiedBy = $verifiedBy;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }
}