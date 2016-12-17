<?php

namespace Admin\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User.
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Admin\UserBundle\Entity\Profile", mappedBy="user")
     */
    private $profile;


    public function __construct()
    {
        parent::__construct();
        $this->profile = new \Admin\UserBundle\Entity\Profile();
    }

    /**
     * Set profile
     *
     * @param \Admin\UserBundle\Entity\Profile $profile
     * @return User
     */
    public function setProfile(\Admin\UserBundle\Entity\Profile $profile = null)
    {
        $profile->setUser($this);
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \Admin\UserBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Get realname
     *
     * @return string
     */
    public function getRealname()
    {
        $realname = $this->username;

        if ($this->profile !== null && $this->profile->getName() !== '') {
            $realname = $this->profile->getName();
        }

        return $realname;
    }
}
