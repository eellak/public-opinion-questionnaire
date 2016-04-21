<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnswerStat
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="answer_dimension_UNIQUE", columns={"answer_id", "dimension", "dimension_value"})})
 * @ORM\Entity
 */
class AnswerStat
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Answer", inversedBy="answerStats", fetch="EAGER")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $answer;

    /**
     * @ORM\Column(name="dimension", type="string", length=20, nullable=false)
     */
    private $dimension; // e.g. GENDER

    /**
     * @ORM\Column(name="dimension_value", type="string", length=20, nullable=false)
     */
    private $dimensionValue; // e.g. MALE

    /**
     * @ORM\Column(name="percentage", type="float")
     */
    private $percentage;

    function getId() {
        return $this->id;
    }

    function getAnswer() {
        return $this->answer;
    }

    function getDimension() {
        return $this->dimension;
    }

    function getDimensionValue() {
        return $this->dimensionValue;
    }

    function getPercentage() {
        return $this->percentage;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setAnswer($answer) {
        $this->answer = $answer;
    }

    function setDimension($dimension) {
        $this->dimension = $dimension;
    }

    function setDimensionValue($dimensionValue) {
        $this->dimensionValue = $dimensionValue;
    }

    function setPercentage($percentage) {
        $this->percentage = $percentage;
    }
}
