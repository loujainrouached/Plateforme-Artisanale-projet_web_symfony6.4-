<?php
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Workshop;
use App\Entity\User; // Ensure this is added
use App\Repository\UserRepository; 
use App\Form\ReservationType;
use App\Repository\WorkshopRepository;
use App\Repository\ReservationRepository;  // <-- Add this line
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }



    #[Route('/backreservation', name: 'back_reservations')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Get all reservations from the database
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

        // Render the reservations in the dashboard
        return $this->render('reservation/backreservation.html.twig', [
            'reservations' => $reservations,
        ]);
    }

   /*  #[Route('/profile', name: 'user_reservations')]
    public function profile(ReservationRepository $reservationRepository, UserRepository $userRepository)
    {
        // Fetch user with id=1 (for testing purposes)
        $user = $userRepository->find(1); // Static user with id=1

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $reservations = $reservationRepository->findBy(['user' => $user]);

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'reservations' => $reservations
        ]);
    } */

    #[Route('/reservation/new/{workshopId}', name: 'reservation_new')]
    public function newReservation(
        int $workshopId,
        Request $request,
        EntityManagerInterface $em,
        WorkshopRepository $workshopRepo
    ): Response {
        // 1) Fetch the Workshop
        $workshop = $workshopRepo->find($workshopId);
        if (!$workshop) {
            throw $this->createNotFoundException('Workshop not found.');
        }
    
        // 2) Create Reservation
        $reservation = new Reservation();
        $reservation->setWorkshop($workshop);
    
        // 3) Get the currently logged-in user or default to user with ID 1
        $user = $this->getUser();
        if (!$user) {
            $user = $em->getRepository(User::class)->find($user->getId()); // Default user with ID 1
        }
    
        if (!$user) {
            throw $this->createNotFoundException('Default user with ID 1 not found.');
        }
    
        $reservation->setUser($user);
        $reservation->setUniqueCode('RES-' . substr(uniqid(), -6));
    
        // 4) Create the form
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Set dateReservation based on input or default to now
            $dateReservationString = $request->request->get('form')['dateReservation'] ?? null;
            if ($dateReservationString) {
                $reservation->setDateReservation(new \DateTime($dateReservationString));
            } else {
                $reservation->setDateReservation(new \DateTime()); // Default to now
            }
    
            $em->persist($reservation);
            $em->flush();
    
            $this->addFlash('success', 'Reservation created!');
            return $this->redirectToRoute('workshop_detail', ['id' => $workshopId]);
        }
    
        return $this->render('reservation/index.html.twig', [
            'user' => $user,
            'workshop' => $workshop,
            'form' => $form->createView(),
            'dateReservation' => (new \DateTime())->format('Y-m-d H:i:s') // Pass as string to Twig
        ]);
    }
    




    #[Route('/reservation/{id}/edit', name: 'edit_reservation')]
    public function edit(
        Reservation $reservation, // Automatically injects the reservation entity by its ID
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            // Use $entityManager instead of $em
            $user = $entityManager->getRepository(User::class)->find($user->getId());
        }
    
        if (!$user) {
            throw $this->createNotFoundException('Default user with ID 1 not found.');
        }
    
        // Create the form for editing the reservation
        $form = $this->createForm(ReservationType::class, $reservation);
    
        // Handle form submission
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the updated reservation entity
            $entityManager->flush();
    
            // Redirect to the reservation details page or a success page
            $this->addFlash('success', 'Reservation updated successfully!');
            return $this->redirectToRoute('profil');
        }
    
        return $this->render('user/editReservation.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
            'user' => $user
        ]);
    }
    
    #[Route('/reservation/{id}/delete', name: 'delete_reservation')]
    public function delete(Reservation $reservation, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user) {
    
            $user = $entityManager->getRepository(User::class)->find($user->getId());
        }
        if (!$user) {
            throw $this->createNotFoundException('Default user with ID 1 not found.');
        }
    

        // Remove the reservation
        $entityManager->remove($reservation);
        $entityManager->flush();

        // Add a success message
        $this->addFlash('success', 'Reservation deleted successfully!');

        // Redirect to the user reservations page
        return $this->redirectToRoute('profil');
    }




    #[Route('/backres/{id}/edit', name: 'editback_reservation')]
    public function editback(
        Reservation $reservation, // Automatically injects the reservation entity by its ID
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            // Use $entityManager instead of $em
            $user = $entityManager->getRepository(User::class)->find($user->getId());
        }
    
        if (!$user) {
            throw $this->createNotFoundException('Default user with ID 1 not found.');
        }
    
        // Create the form for editing the reservation
        $form = $this->createForm(ReservationType::class, $reservation);
    
        // Handle form submission
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the updated reservation entity
            $entityManager->flush();
    
            // Redirect to the reservation details page or a success page
            $this->addFlash('success', 'Reservation updated successfully!');
            return $this->redirectToRoute('back_reservations');
        }
    
        return $this->render('reservation/updatereservation.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
            'user' => $user
        ]);
    }
    #[Route('/backres/{id}/delete', name: 'deleteback_reservation')]
    public function deleteback(Reservation $reservation, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user) {
    
            $user = $entityManager->getRepository(User::class)->find($user->getId());
        }
        if (!$user) {
            throw $this->createNotFoundException('Default user with ID 1 not found.');
        }
    

        // Remove the reservation
        $entityManager->remove($reservation);
        $entityManager->flush();

        // Add a success message
        $this->addFlash('success', 'Reservation deleted successfully!');

        // Redirect to the user reservations page
        return $this->redirectToRoute('back_reservations');
    } 

    
}

    


