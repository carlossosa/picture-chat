<?php
// src/Acme/UserBundle/Entity/User.php

namespace PictureChat\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="picturechat_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="PictureChat\FileBundle\Entity\File", mappedBy="user")
     */
    protected $files;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->files = new ArrayCollection();
    }
       
}