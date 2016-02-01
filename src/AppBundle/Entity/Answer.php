<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table
 * @ORM\Entity
 */
class Answer
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question", inversedBy="answers", fetch="EAGER")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="string", length=1024, nullable=false)
     */
    private $answer;

    function getId() {
        return $this->id;
    }

    function getQuestion() {
        return $this->question;
    }

    function getAnswer() {
        return $this->answer;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setQuestion($question) {
        $this->question = $question;
    }

    function setAnswer($answer) {
        $this->answer = $answer;
    }
}
