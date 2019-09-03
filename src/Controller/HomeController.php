<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\SendEmailsLog;
use App\Form\GalleryFormType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\HttpFoundation\File\File;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index($currentPage = 1, Request $request)
    {      
        $repository = $this->getDoctrine()->getRepository(Gallery::class);
        
        $page = (int)$request->query->get('page')>0?(int)$request->query->get('page'):1;
        
        $limit = 8;
        $offset = ($limit * ($page - 1));
        
        $images = $repository->findBy(
            [],
            ['created_at'=>'desc'],
            $limit,
            $offset
        );
       
        
        $images_count = $repository->getCount();
        $images_count = $images_count?$images_count['cnt']:0;
        $maxPages = ceil($images_count / $limit);
        
        //echo \App\Entity\Gallery::setFullImage();
        //echo realpath('');
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'images' => $images, 
            'maxPages' => $maxPages, 'thisPage' => $page,
            'routeName' => $request->get('_route'),
            
        ]);
    }
    
    public function add_image(Request $request, ValidatorInterface $validator, \Swift_Mailer $mailer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $userId = (int)$user->getId();
        
        $image = new Gallery;
        
        $form = $this->createForm(GalleryFormType::class, $image);
        $form->handleRequest($request);
        
        $errors = [];     
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $file = $form['file']->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = strtotime("now").'-'.uniqid().'.'.$file->guessExtension();

                $dir = 'images/gallery';
                
                //echo realpath("{$dir}/thumb/");
                // Move the file to the directory where brochures are stored
                try {
                    
                    $max_width = 640; 
                    $max_height = 480; 
                    $img = $file->getPathname();
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
                            imagejpeg($tmp, realpath("{$dir}/thumb/")."/{$newFilename}");
                            $file_thumb = "/{$dir}/thumb/{$newFilename}";
                        }
                    }
                    
                    $file->move(
                        $dir,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $image->setFile("{$dir}/{$newFilename}");
                $image->setFileThumb("{$dir}/thumb/{$newFilename}");
                
            }
            
                
                $image->setUserId($userId);
                $image->setUser($user);
                //$image->setUpdatedBy($userId);
                $image->getUserId();
                
                $now = new \DateTime("now");
                $image->setCreatedAt($now);
                $image->setUpdatedAt($now);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            
            $body = $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/photo.html.twig',
                    ['ename' => $image->getEname(), 'email_from' => $user->getEmail(), 'id' => $image->getId() ]
                );
            
            $message = (new \Swift_Message('Image From Gallery Site'))
            ->setFrom($this->getParameter('mailto'))
            ->setCc($user->getEmail())
            ->setTo($this->getParameter('mailto'))
            ->setBody(
                $body,
                'text/html'
            )->attach(\Swift_Attachment::fromPath( realpath(trim($image->getFile(),'/')) ));

            $mailer->send($message);
            
            $emaillog = new SendEmailsLog;
            $emaillog->setSendto($this->getParameter('mailto'));
            $emaillog->setUserId($userId);
            $emaillog->setUser($user);
            $emaillog->setBody($body);
            $emaillog->setFile($image->getFile());
            $emaillog->setCreatedAt($now);
            
            $entityManager->persist($emaillog);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_homepage');
        } else {
            foreach ($form->getErrors() as $key => $error) {
                if ($form->isRoot()) {
                    $errors[] = $error->getMessage();
                } else {
                    $errors[] = $error->getMessage();
                }
            }
        }
        
        return $this->render('home/add_image.html.twig', [
            'controller_name' => 'HomeController',
            'errors' => $errors,
            'uForm' => $form->createView(),
        ]);
    }
    
}
