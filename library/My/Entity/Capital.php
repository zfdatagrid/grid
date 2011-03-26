<?php
namespace My\Entity;

/**
 * @Entity
 * @Table(name="City")
 */
class Capital
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @Column(name="Name", length=35)
     */
    private $name;

    /**
     * @Column(name="CountryCode", length=3)
     */
    private $countryCode;

    /**
     * @Column(name="District", length=20)
     */
    private $district;

    /**
     * @Column(name="Population", type="integer")
     */
    private $population;

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

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getDistrict()
    {
        return $this->district;
    }

    public function setDistrict($district)
    {
        $this->district = $district;
    }

    public function getPopulation()
    {
        return $this->population;
    }

    public function setPopulation($population)
    {
        $this->population = $population;
    }
}
