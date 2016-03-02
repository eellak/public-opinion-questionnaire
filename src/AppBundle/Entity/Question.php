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
     * @ORM\Column(name="question_id", type="string", length=10, nullable=false, unique=true)
     */
    private $questionId;

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
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Dataset", inversedBy="questions")
     * @ORM\JoinColumn(name="dataset_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $dataset;

    public function __construct() {
        $this->answers = new ArrayCollection();
    }

    function getId() {
        return $this->id;
    }

    function getQuestionId() {
        return $this->questionId;
    }

    function getQuestion() {
        return $this->question;
    }

    function getAnswers() {
        return $this->answers;
    }

    function getUserAnswersCount() {
        $count = 0;
        foreach($this->answers as $curAnswer) {
            $count = $count + $curAnswer->getUserAnswers()->count();
        }
        return $count;
    }

    function getDataset() {
        return $this->dataset;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setQuestionId($questionId) {
        $this->questionId = $questionId;
    }

    function setQuestion($question) {
        $this->question = $question;
    }

    function setAnswers($answers) {
        $this->answers = $answers;
    }

    function setDataset($dataset) {
        $this->dataset = $dataset;
    }
}
