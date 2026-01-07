<?php

namespace App\Controller;
use App\Entity\Workshop;
use App\Form\WorkshopType;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\WorkshopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\String\Slugger\SluggerInterface;


final class AtelierController extends AbstractController
{
    #[Route('/atelier', name: 'workshop_detail')]
public function index(WorkshopRepository $workshopRepository, ManagerRegistry $managerr): Response
{
    // Fetch all workshops from the database for the listing
    $workshops = $workshopRepository->findAll();
    
    // Prepare data for the map (latitude, longitude, and name)
    $workshopData = [];
    foreach ($workshops as $workshopmap) {
        $workshopData[] = [
            'latitude' => $workshopmap->getLatitude(),
            'longitude' => $workshopmap->getLongitude(),
            'name' => $workshopmap->getTitle(),
        ];
    }
       // Pass both workshop listing and map data to the template
       return $this->render('atelier/index.html.twig', [
        'workshops' => $workshops,   // Full workshop details for listing
        'workshopData' => $workshopData, // Map data (latitude, longitude, name)
    ]);
}
    

    #[Route('/workshop/{id}', name: 'workshop_details')]
    public function detail(WorkshopRepository $workshopRepository, int $id): Response
    {
        $workshop = $workshopRepository->find($id);
    
        if (!$workshop) {
            throw $this->createNotFoundException('Workshop not found');
        }
    
        // Get related workshops (same type as current workshop)
        $relatedWorkshops = $workshopRepository->findBy(
            ['type' => $workshop->getType()], // Same type
            ['id' => 'DESC'], // Sort by latest workshops
            3 // Limit to 3 related workshops
        );
    
        return $this->render('atelier/singleWorkshop.html.twig', [
            'workshop' => $workshop,
            'related_workshops' => $relatedWorkshops, // Pass related workshops to the template
        ]);
    }
    


    #[Route('/backatelier', name: 'app_atelier')]
    public function back(): Response
    {
        return $this->render('atelier/backatelier.html.twig', [
            'controller_name' => 'AtelierController',
        ]);
    }


 

    #[Route('/listworkshop', name: 'workshops')]
    public function listwork(WorkshopRepository $repo, ManagerRegistry $manager, Request $req, SluggerInterface $slugger): Response
    {
        $work = $repo->findAll();
    
        // Create the form for adding a workshop
        $workshop = new Workshop();
        $form = $this->createForm(WorkshopType::class, $workshop);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('workshop_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading image.');
                    return $this->redirectToRoute('workshops');
                }
    
                // Set the image path in the entity
                $workshop->setImage('/images/workshops/'.$newFilename);
            }
    
            $em = $manager->getManager();
            $em->persist($workshop);
            $em->flush();
    
            $this->addFlash('success', 'Workshop added successfully.');
            return $this->redirectToRoute("workshops");
        }
    
        return $this->render("atelier/backatelier.html.twig", [
            "work" => $work,
            "form" => $form->createView(),
            'is_edit' => false, // This is for the add modal, make sure you set it to false for add
        ]);
    }
    
     
    #[Route('/workshop/{id}/edit', name: 'edit_workshop')]
    public function updateWorkshop(
        $id,
        WorkshopRepository $repo,
        ManagerRegistry $manager,
        Request $req,
        SluggerInterface $slugger
    ): Response {
        $workshop = $repo->find($id);
        
        if (!$workshop) {
            throw $this->createNotFoundException('Workshop not found');
        }
        
        // Create the form for updating a workshop
        $form = $this->createForm(WorkshopType::class, $workshop);
        $form->handleRequest($req);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                // Remove the old image file if there's a new one
                $oldImagePath = $this->getParameter('workshop_images_directory') . '/' . basename($workshop->getImage());
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
    
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('workshop_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading image.');
                    return $this->redirectToRoute('workshops');
                }
    
                // Set the new image path
                $workshop->setImage('/images/workshops/'.$newFilename);
            }
            
            // Persist the updated workshop
            $em = $manager->getManager();
            $em->persist($workshop);
            $em->flush();
    
            $this->addFlash('success', 'Workshop updated successfully.');
            return $this->redirectToRoute('workshops');
        }
    
        return $this->render("atelier/updateback.html.twig", [
            'workshop' => $workshop,
            'form' => $form->createView(),
          
        ]);
    }

    #[Route('/workshop/{id}/delete', name: 'delete_workshop')]
    public function deleteWorkshop($id, WorkshopRepository $repo, ManagerRegistry $manager): Response
    {
        $workshop = $repo->find($id);
    
        if (!$workshop) {
            throw $this->createNotFoundException('Workshop not found');
        }
    
        // Remove the associated image if it exists
        $imagePath = $this->getParameter('workshop_images_directory') . '/' . basename($workshop->getImage());
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    
        // Remove the workshop from the database
        $em = $manager->getManager();
        $em->remove($workshop);
        $em->flush();
    
        $this->addFlash('success', 'Workshop deleted successfully.');
        return $this->redirectToRoute('workshops');
    }
    

    
/*
#[Route('/addWorkshop', name: "add_work")]
public function add(ManagerRegistry $manager, Request $req): Response // <-- Add Response return type
{
    $em = $manager->getManager();
    $work = new Workshop();
    $form = $this->createForm(WorkshopType::class, $work);
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) { 
        $em->persist($work);
        $em->flush();
        return $this->redirectToRoute("workshops");
    }

    return $this->render("atelier/backatelier.html.twig", [
        "form" => $form
    ]);
}*/
}