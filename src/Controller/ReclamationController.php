<?php

namespace App\Controller;

use App\Form\ReclamationType;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use App\Entity\Reponse;

use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'ajouter_reclamation')]
    public function new(Request $request, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $user = $this->getUser();
       // $user = $userRepository->find($user->getId());  
        $user = $entityManager->getRepository(User::class)->find($user->getId());

        $reclamation = new Reclamation();
        $reclamation->setUser($user); // ✅ Set default user

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
/*             $reclamation->setUser(getUser()); // Associate the logged-in user
*/            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('ajouter_reclamation'); // Redirect to the list page
        }

        
        
        return $this->render('reclamation/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/backReclamation', name: 'app_reclamationBack')]
    public function backReclamation(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findAll();


        return $this->render('reclamation/backReclamation.html.twig', [
            'reclamations' => $reclamations, // ✅ Pass reclamations to Twig
        ]);
    }



    #[Route('/reclamation/delete/{id}', name: 'reclamation_delete', methods: ['POST'])]
public function delete(Reclamation $reclamation, EntityManagerInterface $entityManager, Request $request): Response
{
    if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
        $entityManager->remove($reclamation);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_reclamationBack'); // Redirect after delete
}






/* #[Route('/reclamation/update/{id}', name: 'reclamation_update', methods: ['POST'])]
public function updateReclamation($id, Request $request, ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

    if (!$reclamation) {
        throw $this->createNotFoundException('No claim found for id ' . $id);
    }

    // Retrieve values from the request
    $status = $request->request->get('status');
    $importance = $request->request->get('importance');

     // Validate the status (only allow expected values)
    if (!in_array($status, ['open', 'closed', 'in_progress'])) {
        throw new \InvalidArgumentException('Invalid status value');
    }
    // Update entity
    $reclamation->setStatus($status);
    $reclamation->setIsMarked($importance == "1"); // Convert "1" to boolean true

    // Save to database
    $entityManager->flush();

    // Redirect back to the claims list
    return $this->redirectToRoute('app_reclamationBack'); // Change to your actual route
} */



#[Route('/reclamation/update-status', name: 'reclamation_update_status', methods: ['POST'])]
public function updateStatus(Request $request, ManagerRegistry $doctrine): JsonResponse
{
    $entityManager = $doctrine->getManager();
    $id = $request->request->get('id');
    $status = $request->request->get('status');
    $importance = $request->request->get('importance');

    // Debugging
    if (!$id) {
        return new JsonResponse(['success' => false, 'message' => 'No ID provided'], 400);
    }

    // Retrieve the Reclamation entity
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

    if (!$reclamation) {
        return new JsonResponse(['success' => false, 'message' => 'Claim not found'], 404);
    }

    // Validate status
    if (!in_array($status, ['open', 'closed', 'in_progress'])) {
        return new JsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
    }

    // Update status
    $reclamation->setStatus($status);

    // Update importance if provided
    if ($importance !== null) {
        $reclamation->setIsMarked($importance == '1');
    }

    // Save changes
    $entityManager->flush();

    return new JsonResponse(['success' => true, 'message' => 'Status updated successfully']);
}


/* #[Route('/conversation', name: 'conversation')]
public function index(): Response
{
    return $this->render('reclamation/ReclamationConversation.html.twig', [
        'controller_name' => 'ReclamationController',
    ]);
} */

#[Route('/baseback', name: 'baseback')]
public function back(): Response
{
    return $this->render('baseback.html.twig', [
        'controller_name' => 'ReclamationController',
    ]);
}


/* 
#[Route('/conversation', name: 'reclamation_list')]
public function reclamationList(ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();

    // Fetch all reclamations
    $reclamations = $entityManager->getRepository(Reclamation::class)->findBy([], ['createdAt' => 'DESC']);

    return $this->render('reponse/ReclamationConversation.html.twig', [
        'reclamations' => $reclamations // Pass reclamations variable to Twig
    ]);
}
 */

 
/*  #[Route('/conversation', name: 'reclamation_list')]
public function reclamationList(ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();
    
    // Fetch all reclamations with their responses
    $reclamations = $entityManager->getRepository(Reclamation::class)->findBy([], ['createdAt' => 'DESC']);

    return $this->render('reponse/ReclamationConversation.html.twig', [
        'reclamations' => $reclamations, // Pass reclamations to Twig
    ]);
} */
 
/* #[Route('/conversation', name: 'reclamation_list')]
public function reclamationList(ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();
    
    // Fetch all reclamations with their responses
    $reclamations = $entityManager->getRepository(Reclamation::class)->findBy([], ['createdAt' => 'DESC']);
    
    // Create the response form
    $reponse = new Reponse();
    $form = $this->createForm(ReponseType::class, $reponse);

    return $this->render('reponse/ReclamationConversation.html.twig', [
        'reclamations' => $reclamations,
        'formm' => $form->createView(),
    ]);
} */

#[Route('/conversation', name: 'reclamation_list')]
public function reclamationList(ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();
    
    // Fetch all reclamations with their responses
    $reclamations = $entityManager->getRepository(Reclamation::class)->findBy([], ['createdAt' => 'DESC']);
    
    // Create a form for each reclamation
    $forms = [];
    foreach ($reclamations as $reclamation) {
        $reponse = new Reponse();
        $forms[$reclamation->getId()] = $this->createForm(ReponseType::class, $reponse)->createView();
    }

    return $this->render('reponse/ReclamationConversation.html.twig', [
        'reclamations' => $reclamations,
        'forms' => $forms,
    ]);
}/* 
#[Route('/conversationUser', name: 'reclamation_list_user')]
public function reclamationListUser(ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();
    
    // Fetch all reclamations with their responses
    $reclamations = $entityManager->getRepository(Reclamation::class)->findBy([], ['createdAt' => 'DESC']);
    
    // Create a form for each reclamation
    $forms = [];
    foreach ($reclamations as $reclamation) {
        $reponse = new Reponse();
        $forms[$reclamation->getId()] = $this->createForm(ReponseType::class, $reponse)->createView();
    }

    return $this->render('reponse/UserConversation.html.twig', [
        'reclamations' => $reclamations,
        'forms' => $forms,
    ]);
} */








}
