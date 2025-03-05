<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class PdfController extends AbstractController
{
    #[Route('/user/{id}/pdf', name: 'user_pdf')]
    public function generatePdf(User $user, Request $request): Response
    {
        // Options Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Permet d'utiliser des images distantes (URL externes)
        $options->set('isPhpEnabled', true); // Active l'utilisation de PHP pour certaines fonctionnalités

        // Initialisation de Dompdf
        $dompdf = new Dompdf($options);

        // Vérification du fichier image
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/profile_pictures/' . $user->getPhoto();
        $imageBase64 = null;

        // Si le fichier image existe
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);  // Lire l'image
            $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);  // Déterminer le type d'image
            $imageBase64 = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);  // Encoder en base64
        } else {
            // Si l'image n'existe pas, charger une image par défaut ou mettre un message d'erreur
            $imageBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($this->getParameter('kernel.project_dir') . '/public/uploads/profile_pictures/default.png'));
        }

        // Générer le contenu HTML pour le PDF
        $html = $this->renderView('user/PDF.html.twig', [
            'user' => $user,
            'imageBase64' => $imageBase64,
        ]);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le PDF généré
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="user_' . $user->getId() . '.pdf"',
            ]
        );
    }
}