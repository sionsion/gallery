<?php
namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends BaseAdminController
{
    /** @var array The full configuration of the entire backend */
    protected $config;
    /** @var array The full configuration of the current entity */
    protected $entity;
    /** @var Request The instance of the current Symfony request */
    protected $request;
    /** @var EntityManager The Doctrine entity manager for the current entity */
    protected $em;
    
    private $passwordEncoder;
    
    /**
     * UserController constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function updateEntity($entity)
    {
        $request = Request::createFromGlobals();
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime());
        }
        
        if (method_exists($entity, 'setUpdatedBy')) {
            $user = $this->getUser();
            $userId = $user->getId();
            $entity->setUpdatedBy($userId);
        }
        
        
        if (method_exists($entity, 'setFile')) {
            
            if(isset($_FILES['gallery']['name']['file']['file']) and $_FILES['gallery']['name']['file']['file']){
                    $img = $path = $entity->getFile();
                    $rel_path = str_replace(realpath('')."/", "", $path);
                    $entity->setFile( $rel_path );
                    
                    try {
                    
                        $new_rel_path = str_replace('/gallery/', '/gallery/thumb/', $rel_path);
                        
                    $max_width = 640; 
                    $max_height = 480; 
                    $img_info = getimagesize($img);
                    if($img_info){
                        $width = $img_info[0];
                        $height = $img_info[1];
                        if($width>=$height*$max_width/$max_height){
                            if($width>$max_width){
                                $new_width = $max_width;
                                $new_height = $max_width*$height/$width; 
                            } else {
                                $new_width = $width;
                                $new_height = $height; 
                            }
                        } else {
                            if($height>$max_height){
                                $new_height = $max_height;
                                $new_width = $max_height*$width/$height; 
                            } else {
                                $new_width = $width;
                                $new_height = $height; 
                            }
                        }
                        $flag = true;
                        switch ($img_info[2]) {
                            case IMAGETYPE_GIF  : $src = imagecreatefromgif($img);  break;
                            case IMAGETYPE_JPEG : $src = imagecreatefromjpeg($img); break;
                            case IMAGETYPE_PNG  : $src = imagecreatefrompng($img);  break;
                            default : $flag = false;
                        }
                        if($flag){
                            $tmp = imagecreatetruecolor($new_width, $new_height);
                            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                            imagejpeg($tmp, realpath("")."/{$new_rel_path}");
                            $entity->setFileThumb( $new_rel_path );
                        }
                    }
                } catch (Exception  $e) {
                    // ... handle exception if something happens during file upload
                }
            }
        }
        
        if (method_exists($entity, 'setPassword')) {
            if( isset($request->get("user", null)['password']) and $request->get("user", null)['password']){
                $plainPassword = $request->get("user", null)['password'];
                $entity->setPassword(
                $this->passwordEncoder->encodePassword(
                        $entity,
                        $plainPassword
                    )
                );
            }
        }
        

        parent::updateEntity($entity);
    }
}
