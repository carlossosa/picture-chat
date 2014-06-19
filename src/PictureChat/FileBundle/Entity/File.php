<?php

namespace PictureChat\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use PictureChat\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="picturechat_files")
 * @Vich\Uploadable
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
    * @ORM\ManyToOne(targetEntity="PictureChat\UserBundle\Entity\User", inversedBy="files")
    * @ORM\JoinColumn(referencedColumnName="id")
    */
    protected $user;

    /**
     * @Assert\File(
     *     maxSize="1M",
     *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg", "application/zip", "application/x-zip", "application/octet-stream", "application/x-zip-compressed" }
     * )
     * @Vich\UploadableField(mapping="file_upload", fileNameProperty="fileName")
     *
     * @var HttpFile $file
     */
    protected $file;

    /**
     * @ORM\Column(type="string", length=255, name="file_name")
     *
     * @var string $fileName
     */
    protected $fileName;
    

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     */
    public function setFile(HttpFile $file)
    {
        $this->file = $file;
    }

    /**
     * @return HttpFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
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
     * Set user
     *
     * @param \PictureChat\UserBundle\Entity\User $user
     * @return File
     */
    public function setUser(\PictureChat\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PictureChat\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
