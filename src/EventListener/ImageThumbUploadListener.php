<?php

namespace App\EventListener;

use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageThumbUploadListener {
    
    private $uploaderHelper;
    
    public function __construct(UploaderHelper $uploaderHelper)
    {
	$this->uploaderHelper = $uploaderHelper;
    }
    
    public function onVichUploaderPostUpload(Event $event)
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();
        
        $path = null;
        
        
        switch(get_class($object)){
            case 'App\Entity\Gallery':                
                $path = $object->getFile();
                break;
        }
        if($path){     
                try {
                    
                    $file_arr = explode('/', $path);
                    $image_folder = count($file_arr)>2?$file_arr[count($file_arr)-2]:'images';
                    $filename = $file_arr[count($file_arr)-1];
                    $file_arr[count($file_arr)-1] = 'thumb';
                    $file_arr[] = $filename;
                    
                    
                    $new_rel_path = implode('/', $file_arr);                   
                    $img = realpath("")."/{$path}";
                        
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
                        }
                    }
                } catch (Exception  $e) {
                    // ... handle exception if something happens during file upload
                }
        }
        
    }
    
}
