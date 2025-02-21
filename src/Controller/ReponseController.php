<?php


namespace App\Controller;
use App\Entity\Reclamation;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ReponseType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface; 
use App\Form\ReclamationType;
use App\Entity\Reponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;


final class ReponseController extends AbstractController
{
    #[Route('/reponse/userConversation', name: 'app_reponse')]
    public function userConversation(ManagerRegistry $doctrine): Response
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
    }
 

    #[Route('/reponse/add_user/{reclamationId}', name: 'reponse_add_user', methods: ['POST'])]
    public function addResponseUser(
        Request $request, 
        ManagerRegistry $doctrine, 
        int $reclamationId
    ): Response {
        $entityManager = $doctrine->getManager();
        
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
        
        if (!$reclamation) {
            $this->addFlash('error', 'Reclamation not found');
            return $this->redirectToRoute('app_reponse');
        }
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user->getId());
        if (!$user) {
            throw $this->createNotFoundException('Default user with ID 1 not found.');
        }
    
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $reponse->setUser($user);
                $reponse->setReclamation($reclamation);
                $reponse->setCreatedAt(new \DateTimeImmutable());
                $reponse->setIsRead(false);
                
                $entityManager->persist($reponse);
                $entityManager->flush();
                
                $this->addFlash('success', 'Response added successfully.');
                return $this->redirectToRoute('app_reponse');
            }
            
            // If form is invalid, render the list page again with the errors
            $reclamations = $entityManager->getRepository(Reclamation::class)
                ->findBy([], ['createdAt' => 'DESC']);
            
            // Recreate forms for all reclamations
            $forms = [];
            foreach ($reclamations as $rec) {
                if ($rec->getId() === $reclamationId) {
                    // For the current reclamation, use the form with errors
                    $forms[$rec->getId()] = $form->createView();
                } else {
                    // For other reclamations, create new forms
                    $newReponse = new Reponse();
                    $forms[$rec->getId()] = $this->createForm(ReponseType::class, $newReponse)->createView();
                }
            }
    
            return $this->render('reponse/UserConversation.html.twig', [
                'reclamations' => $reclamations,
                'forms' => $forms,
            ]);
        }
        
        return $this->redirectToRoute('app_reponse');
    }
     
    #[Route('/message/delete/{id}', name: 'reponse_delete_user', methods: ['POST'])]
    public function deleteMessageUser(
        Request $request, 
        EntityManagerInterface $entityManager, 
        int $id
    ): Response
    {
        // Find the message
        $reponse = $entityManager->getRepository(Reponse::class)->find($id);
        $user = $this->getUser();
        if (!$reponse) {
            $this->addFlash('error', 'Message non trouvé.');
            return $this->redirectToRoute('app_reponse');
        }
    
        // Check if user has permission (user id = 1)
        if ($reponse->getUser()->getId() != $user->getId()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de supprimer ce message.');
            return $this->redirectToRoute('app_reponse');
        }
    
        try {
            // Remove the message
            $entityManager->remove($reponse);
            $entityManager->flush();
    
            $this->addFlash('success', 'Message supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du message.');
        }
    
        // Redirect back to the previous page
        return $this->redirectToRoute('app_reponse');
    }

    #[Route('/backReponse', name: 'back_Reponse')]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findAll();
        return $this->render('reponse/backReponse.html.twig', [
            'controller_name' => 'ReponseController',
            'reclamations' => $reclamations,
        ]);
    }

   

/*     #[Route('/conversation', name: 'conversation')]
public function index(): Response
{
    return $this->render('reponse/ReclamationConversation.html.twig', [
        'controller_name' => 'ReclamationController',
    ]);
} */



/* #[Route('/conversation', name: 'reponse_list')]
public function reponseList(ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();

    // Fetch all Reponses grouped by reclamation
    $reponses = $entityManager->getRepository(Reponse::class)->findBy([], ['createdAt' => 'DESC']);

    return $this->render('reponse/ReclamationConversation.html.twig', [
        'reponses' => $reponses  // Pass reponses variable to Twig
    ]);
} */
/*
#[Route('/conversation/{id}/add-response', name: 'add_response')]
public function addResponse(
    Request $request,
    EntityManagerInterface $entityManager,
    Reclamation $reclamation
): Response {
    // Créer une nouvelle réponse
    $response = new Reponse();
    $form = $this->createForm(ReponseType::class, $response);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $response->setReclamation($reclamation);
        $response->setUser($this->getUser()); // Assure-toi que l'utilisateur est connecté
        $response->setCreatedAt(new \DateTimeImmutable());
        $response->setIsRead(false);

        $entityManager->persist($response);
        $entityManager->flush();

        // Redirection vers la conversation
        return $this->redirectToRoute('reclamation_list');
    }

    // Récupérer les réponses associées à cette réclamation
    $responses = $entityManager->getRepository(Reponse::class)->findBy(
        ['reclamation' => $reclamation],
        ['createdAt' => 'ASC']
    );

    // Afficher la conversation avec le formulaire
    return $this->render('reponse/ReclamationConversation.html.twig', [
        'reclamation' => $reclamation,
        'responses' => $responses,
        'form' => $form->createView(), // Envoi du formulaire à la vue
    ]);
}*/


/* #[Route('/reponse/add/{reclamationId}', name: 'reponse_add', methods: ['POST'])]
public function addResponse(
    Request $request, 
    ManagerRegistry $doctrine, 
    int $reclamationId
): Response {
    $entityManager = $doctrine->getManager();
    
    // Get the reclamation
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
    
    if (!$reclamation) {
        $this->addFlash('error', 'Reclamation not found');
        return $this->redirectToRoute('reclamation_list');
    }

    // ✅ Fetch the default user (ID = 1) for testing
    $user = $entityManager->getRepository(User::class)->find(1);
    if (!$user) {
        throw $this->createNotFoundException('Default user with ID 1 not found.');
    }

    // Create new response and form
    $reponse = new Reponse();
    $form = $this->createForm(ReponseType::class, $reponse);
    
    // Handle form submission
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        // ✅ Set the default user
        $reponse->setUser($user);

        // ✅ Set the reclamation
        $reponse->setReclamation($reclamation);

        // ✅ Set the createdAt timestamp
        $reponse->setCreatedAt(new \DateTimeImmutable());

        // ✅ Set unread status
        $reponse->setIsRead(false);
        
        // Save the response
        $entityManager->persist($reponse);
        $entityManager->flush();
        
        $this->addFlash('success', 'Response added successfully.');
    } else {
        $this->addFlash('error', 'There was a problem adding your response.');
    }
    
    return $this->redirectToRoute('reclamation_list');
}
 */



 #[Route('/reponse/add/{reclamationId}', name: 'reponse_add', methods: ['POST'])]
public function addResponse(
    Request $request, 
    ManagerRegistry $doctrine, 
    int $reclamationId
): Response {
    $entityManager = $doctrine->getManager();
    
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($reclamationId);
    
    if (!$reclamation) {
        $this->addFlash('error', 'Reclamation not found');
        return $this->redirectToRoute('reclamation_list');
    }
    $user = $this->getUser();
    $user = $entityManager->getRepository(User::class)->find($user->getId());
    if (!$user) {
        throw $this->createNotFoundException('Default user with ID 1 not found.');
    }

    $reponse = new Reponse();
    $form = $this->createForm(ReponseType::class, $reponse);
    $form->handleRequest($request);
    
    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $reponse->setUser($user);
            $reponse->setReclamation($reclamation);
            $reponse->setCreatedAt(new \DateTimeImmutable());
            $reponse->setIsRead(false);
            
            $entityManager->persist($reponse);
            $entityManager->flush();
            
            $this->addFlash('success', 'Response added successfully.');
            return $this->redirectToRoute('reclamation_list');
        }
        
        // If form is invalid, render the list page again with the errors
        $reclamations = $entityManager->getRepository(Reclamation::class)
            ->findBy([], ['createdAt' => 'DESC']);
        
        // Recreate forms for all reclamations
        $forms = [];
        foreach ($reclamations as $rec) {
            if ($rec->getId() === $reclamationId) {
                // For the current reclamation, use the form with errors
                $forms[$rec->getId()] = $form->createView();
            } else {
                // For other reclamations, create new forms
                $newReponse = new Reponse();
                $forms[$rec->getId()] = $this->createForm(ReponseType::class, $newReponse)->createView();
            }
        }

        return $this->render('reponse/ReclamationConversation.html.twig', [
            'reclamations' => $reclamations,
            'forms' => $forms,
        ]);
    }
    
    return $this->redirectToRoute('reclamation_list');
}
 






#[Route('/message/edit/{id}', name: 'reponse_edit', methods: ['POST'])]
    public function editMessage(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        try {
            // Get the reponse entity
            $reponse = $entityManager->getRepository(Reponse::class)->find($id);
            
            if (!$reponse) {
                return $this->json([
                    'error' => 'Reponse not found',
                    'id' => $id
                ], 404);
            }

            // Get and decode the request content
            $content = json_decode($request->getContent(), true);
            
            if (!isset($content['message'])) {
                return $this->json([
                    'error' => 'Message content is required'
                ], 400);
            }

            // Update the reponse
            $reponse->setMessage($content['message']);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $reponse->getMessage()
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Server error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/message/delete/{id}', name: 'reponse_delete', methods: ['POST'])]
public function deleteMessage(
    Request $request, 
    EntityManagerInterface $entityManager, 
    int $id
): Response
{
    // Find the message
    $reponse = $entityManager->getRepository(Reponse::class)->find($id);
    $user = $this->getUser();
    if (!$reponse) {
        $this->addFlash('error', 'Message non trouvé.');
        return $this->redirectToRoute('reclamation_list');
    }

    // Check if user has permission (user id = 1)
    if ($reponse->getUser()->getId() != $user->getId()) {
        $this->addFlash('error', 'Vous n\'avez pas la permission de supprimer ce message.');
        return $this->redirectToRoute('reclamation_list');
    }

    try {
        // Remove the message
        $entityManager->remove($reponse);
        $entityManager->flush();

        $this->addFlash('success', 'Message supprimé avec succès.');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Une erreur est survenue lors de la suppression du message.');
    }

    // Redirect back to the previous page
    return $this->redirectToRoute('reclamation_list');
}
}