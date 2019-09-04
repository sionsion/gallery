<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GalleryRepository")
 * @Vich\Uploadable
 */
class Gallery
{
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $ename;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $file;
    
    /**
     * @Vich\UploadableField(mapping="gallery_files", fileNameProperty="file")
     * @var File
     */
    private $imageFile;
    
    /**
     * @ORM\Column(type="string", length=150)
     */
    private $file_thumb;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $updated_by;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $user_updated;  
            
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEname(): ?string
    {
        return $this->ename;
    }

    public function setEname(?string $ename): self
    {
        $this->ename = $ename;

        return $this;
    }
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param imageFile |UploadedFile $imageFile
     */
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->updated_at = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }
    public function setFile(?string $imageFile): self
    {
        $this->file = $imageFile;
        
        $file_arr = explode('/', $this->file);
        $image_folder = count($file_arr)>2?$file_arr[count($file_arr)-2]:'images';
        $new_rel_path = str_replace("/{$image_folder}/", "/{$image_folder}/thumb/", $this->file);
        
        $this->setFileThumb( $new_rel_path );

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getFileThumb(): ?string
    {
        return $this->file_thumb;
    }

    public function setFileThumb(string $file_thumb): self
    {
        $this->file_thumb = $file_thumb;

        return $this;
    }
    
    public function __toString() {
        return $this->ename;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
    
    public static function getFullImagePath(){       
        return 'images/gallery';
    }
    
    
    /*
    public static function setFullImage(UploadedFile $file, $abs_path, $original_name){
        $request = Request::createFromGlobals();
        
        $id = (int)$request->query->get('id');
        if($id){      
            
            
            print_r($file);
            echo '<br>';
            echo $abs_path;
            echo '<br>';
            echo $original_name;
            echo 'test';

            exit();
            return 'test';
        }
    }
     * 
     */

    public function getUpdatedBy(): ?int
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?int $updated_by): self
    {
        $this->updated_by = $updated_by;

        return $this;
    }

    public function getUserUpdated(): ?User
    {
        return $this->user_updated;
    }

    public function setUserUpdated(?User $user_updated): self
    {
        $this->user_updated = $user_updated;

        return $this;
    }

}
