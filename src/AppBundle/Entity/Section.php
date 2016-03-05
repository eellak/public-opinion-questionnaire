<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Question
 *
 * @ORM\Table
 * @ORM\Entity
 */
class Section
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @ORM\Column(name="section_id", type="string", length=20, nullable=false)
     */
    private $sectionId;
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", nullable=false)
     */
    private $name;
     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="section")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $questions;

    function getId() {
        return $this->id;
    }

    function getSectionId() {
        return $this->sectionId;
    }

    function getName() {
        return $this->name;
    }

    function getQuestions() {
        return $this->questions;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setSectionId($sectionId) {
        $this->sectionId = $sectionId;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setQuestions($questions) {
        $this->questions = $questions;
    }
}
