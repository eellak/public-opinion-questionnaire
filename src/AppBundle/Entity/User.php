<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table
 * @ORM\Entity
 */
class User
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
     * @ORM\Column(name="sessionId", type="string", nullable=false, unique=true)
     */
    private $sessionId;
    /**
     * @ORM\Column(name="email", type="string", length=1024, nullable=true)
     */
    private $email;

    function getId() {
        return $this->id;
    }

    function getSessionId() {
        return $this->sessionId;
    }

    function getEmail() {
        return $this->email;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    function setEmail($email) {
        $this->email = $email;
    }
}
