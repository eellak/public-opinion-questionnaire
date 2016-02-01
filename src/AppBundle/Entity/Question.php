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
class Question
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
     * @ORM\Column(name="question", type="string", length=1024, nullable=false)
     */
    private $question;
     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Answer", mappedBy="question")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $answers;

    function getId() {
        return $this->id;
    }

    function getQuestion() {
        return $this->question;
    }

    function getAnswers() {
        return $this->answers;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setQuestion($question) {
        $this->question = $question;
    }

    function setAnswers($answers) {
        $this->answers = $answers;
    }
}
