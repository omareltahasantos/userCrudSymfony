<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{

    #[Route('/', name: 'app_user_main', methods: ['GET'])]

    public function main() :Response
    {
        return $this->redirectToRoute('app_user_index', array('currentPage' => 1));
    }


    #[Route('/page/{currentPage}', name: 'app_user_index', methods: ['GET'])]
    public function index($currentPage, UserRepository $userRepository):Response
    {
        $limit = 2;
        $currentPage = intval($currentPage);
        
        if ($currentPage === 0) {
            $currentPage = 1;
        }
        
        $users = $userRepository->getAllUsers($currentPage, $limit);
        $maxPages = ceil($users->count()/ $limit);
        
        return $this->render('user/index.html.twig', [
            'users' => $users,
            'maxPages' => $maxPages,
            'thisPage' => $currentPage
        ]);
    

    }
   
     #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Crear usuario
             $userRepository->save($user);
             
           return $this->redirectToRoute('app_user_index', array('currentPage' => $request->query->get('currentPage')));
        }

        return $this->renderForm('user/new.html.twig', [
            'form' => $form,
            'currentPage' => $request->query->get('currentPage')
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]

    public function show($id, UserRepository $userRepository, Request $request) :Response
    {
        $user = $userRepository->find($id);

        return $this->render('/user/show.html.twig', [
            'user' => $user,
            'currentPage' => $request->query->get('currentPage')
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]

    public function delete($id, UserRepository $userRepository, Request $request):Response
    {
        $user = $userRepository->find($id);

        if ($user) {
            //eliminar usuario
           $userRepository->remove($user);

            return $this->redirectToRoute('app_user_index', array('currentPage' => $request->query->get('currentPage')));
        }
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]

    public function edit($id, UserRepository $userRepository, Request $request):Response
    {
        $user = $userRepository->find($id);

        if ($user) {
           $form = $this->createForm(UserType::class, $user);
           $form->handleRequest($request);
           
           if ($form->isSubmitted() && $form->isValid()) {
               //edit user
                $userRepository->save($user);
                
               return $this->redirectToRoute('app_user_index', array('currentPage' => $request->query->get('currentPage')));
               
           }
           
           return $this->renderForm('user/edit.html.twig', [
               'form' => $form,
               'user' => $user,
               'currentPage' => $request->query->get('currentPage')
           ]);
        }
    }

}