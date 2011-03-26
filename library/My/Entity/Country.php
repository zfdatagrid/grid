<?php
namespace My\Entity;

/**
 * @Entity
 */
class Country
{
    /**
     * @Id
     * @Column(type="string", length=3)
     */
    private $code;

    /**
     * @Column(name="Name", length=52)
     */
    private $name;

    /**
     * @Column(name="Continent", length=20)
     */
    private $continent;

    /**
     * @Column(name="Region", length=26)
     */
    private $region;

    /**
     * @Column(name="SurfaceArea", type="float")
     */
    private $surfaceArea;

    /**
     * @Column(name="IndepYear", type="integer", nullable=true)
     */
    private $indepYear;

    /**
     * @Column(name="Population", type="integer")
     */
    private $population;

    /**
     * @Column(name="LifeExpectancy", type="float", nullable=true)
     */
    private $lifeExpectancy;

    /**
     * @Column(name="GNP", type="float", nullable=true)
     */
    private $gnp;

    /**
     * @Column(name="GNPOld", type="float", nullable=true)
     */
    private $gnpOld;

    /**
     * @Column(name="LocalName", length=45)
     */
    private $localName;

    /**
     * @Column(name="GovernmentForm", length=45)
     */
    private $governmentForm;

    /**
     * @Column(name="HeadOfState", length=60, nullable=true)
     */
    private $headOfState;

    /**
     * @OneToOne(targetEntity="Capital")
     * @JoinColumn(name="Capital", referencedColumnName="id")
     */
    private $capital;

    /**
     * @Column(name="Code2", length=2)
     */
    private $code2;

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getContinent()
    {
        return $this->continent;
    }

    public function setContinent($continent)
    {
        $this->continent = $continent;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        $this->region = $region;
    }

    public function getSurfaceArea()
    {
        return $this->surfaceArea;
    }

    public function setSurfaceArea($surfaceArea)
    {
        $this->surfaceArea = $surfaceArea;
    }

    public function getIndepYear()
    {
        return $this->indepYear;
    }

    public function setIndepYear($indepYear)
    {
        $this->indepYear = $indepYear;
    }

    public function getPopulation()
    {
        return $this->population;
    }

    public function setPopulation($population)
    {
        $this->population = $population;
    }

    public function getLifeExpectancy()
    {
        return $this->lifeExpectancy;
    }

    public function setLifeExpectancy($lifeExpectancy)
    {
        $this->lifeExpectancy = $lifeExpectancy;
    }

    public function getGnp()
    {
        return $this->gnp;
    }

    public function setGnp($gnp)
    {
        $this->gnp = $gnp;
    }

    public function getGnpOld()
    {
        return $this->gnpOld;
    }

    public function setGnpOld($gnpOld)
    {
        $this->gnpOld = $gnpOld;
    }

    public function getLocalName()
    {
        return $this->localName;
    }

    public function setLocalName($localName)
    {
        $this->localName = $localName;
    }

    public function getGovernmentForm()
    {
        return $this->governmentForm;
    }

    public function setGovernmentForm($governmentForm)
    {
        $this->governmentForm = $governmentForm;
    }

    public function getHeadOfState()
    {
        return $this->headOfState;
    }

    public function setHeadOfState($headOfState)
    {
        $this->headOfState = $headOfState;
    }

    public function getCapital()
    {
        return $this->capital;
    }

    public function setCapital($capital)
    {
        $this->capital = $capital;
    }

    public function getCode2()
    {
        return $this->code2;
    }

    public function setCode2($code2)
    {
        $this->code2 = $code2;
    }
}