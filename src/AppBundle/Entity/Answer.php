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
     * @ORM\Column(name="answer_id", type="string", length=10, nullable=false)
     */
    private $answerId;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="string", length=1024, nullable=false)
     */
    private $answer;

     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserAnswer", mappedBy="answer")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $userAnswers;

    public function getId() {
        return $this->id;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function getAnswerId() {
        return $this->answerId;
    }

    public function getAnswer() {
        return $this->answer;
    }

    public function getUserAnswers() {
        return $this->userAnswers;
    }

    function getAnswerPercentage() {
        $answers = $this->userAnswers->count();
        $totalAnswers = $this->question->getUserAnswersCount();
        return round($answers/$totalAnswers*100, 2);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setQuestion($question) {
        $this->question = $question;
    }

    public function setAnswerId($answerId) {
        $this->answerId = $answerId;
    }

    public function setAnswer($answer) {
        $this->answer = $answer;
    }

    public function setUserAnswers($userAnswers) {
        $this->userAnswers = $userAnswers;
    }
}
