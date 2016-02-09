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
class Dataset
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
     * @var string
     *
     * @ORM\Column(name="filename", type="string", nullable=false)
     */
    private $filename;
     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="dataset")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $questions;
     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserAnswer", mappedBy="dataset")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $userAnswers;

    function getId() {
        return $this->id;
    }

    function getFilename() {
        return $this->filename;
    }

    function getQuestions() {
        return $this->questions;
    }

    function getUserAnswers() {
        return $this->userAnswers;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFilename($filename) {
        $this->filename = $filename;
    }

    function setQuestions($questions) {
        $this->questions = $questions;
    }

    function setUserAnswers($userAnswers) {
        $this->userAnswers = $userAnswers;
    }
}
