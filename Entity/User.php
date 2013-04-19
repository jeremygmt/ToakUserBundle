<?php

namespace Toak\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Toak\UserBundle\Entity\User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Toak\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken.")
 */
class User implements UserInterface
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    
    /**
     * @ORM\Column(name="email", type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;


    /**
     * @ORM\Column(name="password", type="string", length=150)
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=8)
     */
    private $rawPassword;

    /**
     * @ORM\Column(name="salt", type="string", length=40)
     */
    private $salt;
    
    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var boolean $isAdmin
     *
     * @ORM\Column(name="is_admin", type="boolean")
     */
    private $isAdmin;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->isActive = true;
        $this->isAdmin = false;
    }

    /**
     * [setRawPassword description]
     * @param [type] $password [description]
     */
    public function setRawPassword($password)
    {
        $this->rawPassword = $password;
    }
    
    /**
     * [getRawPassword description]
     * @return [type] [description]
     */
    public function getRawPassword()
    {
        return $this->rawPassword;
    }

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
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * get username
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return User
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    
        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean 
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }
    
     /**
     * @Assert\True(message="Your password must not contain your username.")
     */
    public function isPasswordValid()
    {
        return 0 === preg_match('/' . preg_quote($this->email) . '/i', $this->rawPassword);
    }
    
    /**
     * [getRoles description]
     * @return [type] [description]
     */
    public function getRoles()
    {
        return $this->isAdmin ? array('ROLE_ADMIN') : array('ROLE_USER');
    }
    
    /**
     * [eraseCredentials description]
     * @return [type] [description]
     */
    public function eraseCredentials()
    {
        $this->rawPassword = null;
    }
    
    /**
     * [encodePassword description]
     * @param  PasswordEncoderInterface $encoder [description]
     * @return [type]                            [description]
     */
    public function encodePassword(PasswordEncoderInterface $encoder)
    {
        if (null !== $this->rawPassword) {
            $this->salt = sha1(uniqid(mt_rand(0, 9999).time()));
            $this->password = $encoder->encodePassword($this->rawPassword, $this->salt);
            $this->eraseCredentials();
        }
    }
}


?>
