<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAnswers
 *
 * @ORM\Table(name="user_answers", uniqueConstraints={@ORM\UniqueConstraint(name="user_id", columns={"user_id", "answer_id"})})
 * @ORM\Entity
 */
class UserAnswer
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
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="datetime", nullable=false)
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Answer", fetch="EAGER")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $answer;

    public function __construct() {
        $this->time = new \DateTime('now');
    }

    function getId() {
        return $this->id;
    }

    function getTime() {
        return $this->time;
    }

    function getUser() {
        return $this->user;
    }

    function getAnswer() {
        return $this->answer;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTime(\DateTime $time) {
        $this->time = $time;
    }

    function setUser(User $user) {
        $this->user = $user;
    }

    function setAnswer(Answer $answer) {
        $this->answer = $answer;
    }
}
