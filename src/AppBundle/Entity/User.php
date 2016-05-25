<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="facebook_sender_id", type="integer", unique=true)
     */
    private $facebookSenderId;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get facebookSenderId
     *
     * @return integer
     */
    public function getFacebookSenderId()
    {
        return $this->facebookSenderId;
    }

    /**
     * Set facebookSenderId
     *
     * @param integer $facebookSenderId
     *
     * @return User
     */
    public function setFacebookSenderId($facebookSenderId)
    {
        $this->facebookSenderId = $facebookSenderId;

        return $this;
    }
}
