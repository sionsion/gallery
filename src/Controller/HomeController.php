<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Form\GalleryFormType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index($currentPage = 1, Request $request)
    {      
        $page = (int)$request->query->get('page')>0?(int)$request->query->get('page'):1;
       
        $images = $this->getDoctrine()->getRepository(Gallery::class)->pagenate($page);
        
        return $this->render('home/index.html.twig', [
            'images' => $images          
        ]);
    }
    
    public function add_image(Request $request, ValidatorInterface $validator, \Swift_Mailer $mailer): Response
    {
        $errors = []; 
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $image = new Gallery;
        $form = $this->createForm(GalleryFormType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {        
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            
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
            'errors' => $errors,
            'uForm' => $form->createView(),
        ]);
    }
    
}
