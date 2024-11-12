<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Helper\ImbricateForm;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{

  #[Route('/list', name: '_list')]
  public function index(SortieRepository $repository, EntityManagerInterface $entityManager, Request $request): Response
  {
    $etatEnCreation = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Brouillon']);
    $connectedUser = $this->getUser();

    $sites = $entityManager->getRepository(Site::class)->findAll();

    $site = $request->query->get('site', '');
    $nom = $request->query->get('nom', '');
    $dateDebut = $request->query->get('date_debut', '');
    $dateFin = $request->query->get('date_fin', '');
    $sortiePassee = $request->query->get('sortie_passee', false);
    $organisateurChecked = $request->query->get('organisateur_checked', false);
    $isInscritChecked = $request->query->get('is_inscrit_checked', false); // Checkbox "Inscrit"
    $isNotInscritChecked = $request->query->get('is_not_inscrit_checked', false);

    $etat = null;
    if ($sortiePassee) {
      $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Clôturée']);
    }

    $organisateur = null;
    if ($organisateurChecked) {
      $organisateur = $connectedUser;
    }

    $isInscrit = null;
    if ($isInscritChecked) {
      $isInscrit = $connectedUser;
    }

    $isNotInscrit = null;
    if ($isNotInscritChecked) {
      $isNotInscrit = $connectedUser;
    }

    // Appliquer les filtres en fonction des checkboxes
    $sorties = $repository->findSortieAvecParametre(
      $site,
      $nom,
      $dateDebut,
      $dateFin,
      $etat,
      $organisateur,
      $isInscrit,
      $isNotInscrit,
      $connectedUser,
      $etatEnCreation
    );

    return $this->render('sortie/list.html.twig', [
      'sorties' => $sorties,
      'sites' => $sites,
      'selected_site' => $site,
      'nom' => $nom,
      'date_debut' => $dateDebut,
      'date_fin' => $dateFin,
      'sortie_passee' => $sortiePassee,
      'organisateur_checked' => $organisateurChecked,
      'is_inscrit_checked' => $isInscritChecked, // Passer la valeur "Inscrit"
      'is_not_inscrit_checked' => $isNotInscritChecked, // Passer la valeur "Non inscrit"
    ]);
  }


  #[Route('/detail/{id}', name: '_detail')]
  public function detail(Sortie $sortie): Response
  {
    $googleMapsApiKey = $_ENV['GOOGLE_MAPS_API_KEY'];

    return $this->render('sortie/detail.html.twig', [
      'sortie' => $sortie,
      'google_maps_api_key' => $googleMapsApiKey,
    ]);
  }

  #[Route('/new', name: '_new')]
  public function new(Request $request, EntityManagerInterface $entityManager, ImbricateForm $imbricateForm): Response
  {
    $sortie = new Sortie();
    $form = $this->createForm(SortieType::class, $sortie);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $imbricateForm->addLieuToSortieForm($form, $sortie, $request);
      // Vérifier quel bouton a été cliqué
      $submittedButton = $request->get('submit');
      if ($submittedButton === 'enregistrer') {
        $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Brouillon']);
      } elseif ($submittedButton === 'publier') {
        $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Publiée']);
      } else {
        throw new \Exception('Bouton de soumission non reconnu');
      }
      $sortie->setEtat($etat);
      $user = $this->getUser();
      $sortie->setOrganisateur($user);
      $sortie->setSite($user->getSite());
      $entityManager->persist($sortie);
      $entityManager->flush();
      $this->addFlash('success', 'La sortie a été créée avec succès !');
      return $this->redirectToRoute('sortie_list');
    }
    return $this->render('sortie/new.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  #[Route('/update/{id}', name: '_update', requirements: ['id' => '\d+']), ]
  public function update(Request $request, EntityManagerInterface $entityManager, Sortie $sortie, ImbricateForm $imbricateForm): Response
  {
    $connectedUser = $this->getUser();
    $form = $this->createForm(SortieType::class, $sortie);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      if ($connectedUser->getId() === $sortie->getOrganisateur()->getId()) {
        $imbricateForm->addLieuToSortieForm($form, $sortie, $request);
        // Vérifier quel bouton a été cliqué
        $submittedButton = $request->get('submit');
        if ($submittedButton === 'enregistrer') {
          $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Brouillon']);
        } elseif ($submittedButton === 'publier') {
          $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Publiée']);
        } else {
          throw new \Exception('Bouton de soumission non reconnu');
        }
        $sortie->setEtat($etat);
        $entityManager->persist($sortie);
        $entityManager->flush();
      }
      $entityManager->persist($sortie);
      $entityManager->flush();
      $this->addFlash('success', 'La sortie a été modifée avec succès !');
      return $this->redirectToRoute('sortie_list');
    }
    return $this->render('sortie/new.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+']), ]
  public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
  {
    $connectedUser = $this->getUser();
    if (!$sortie) {
      throw $this->createNotFoundException('This wish do not exists! Sorry!');
    }
    if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->query->get('token'))) {
      if ($connectedUser->getId() === $sortie->getOrganisateur()->getId()) {
        $entityManager->remove($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'la sortie a été supprimé avec succès !');
      } else {
        $this->addFlash('danger', 'vous ne pouvez pas supprimer cette sortie !');
        return $this->redirectToRoute('sortie_list');
      }
    } else {
      $this->addFlash('warning', 'Impossible de supprimer cette sortie car elle n\'existe pas !');
    }
    return $this->redirectToRoute('sortie_list');
  }

  #[Route('/sortie/etat/{id}/{etat}', name: '_changer_etat', requirements: ['id' => '\d+', 'etat' => '.+'])]
  public function changerEtat(Sortie $sortie, string $etat, EntityManagerInterface $entityManager): Response
  {
    $connectedUser = $this->getUser();
    if ($connectedUser->getId() === $sortie->getOrganisateur()->getId()) {
      // Trouver l'état souhaité
      $nouvelEtat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => $etat]);
      if (!$nouvelEtat) {
        throw $this->createNotFoundException('L\'état spécifié est introuvable.');
      }

      // Mettre à jour l'état de la sortie
      $sortie->setEtat($nouvelEtat);
      $entityManager->persist($sortie);
      $entityManager->flush();
    }
    // Rediriger ou retourner une réponse appropriée
    $this->addFlash('success', 'La sortie a été publiée avec succès.');
    return $this->redirectToRoute('sortie_list'); // Ou toute autre route souhaitée
  }

  #[Route('/cancel/{id}', name: '_cancel', requirements: ['id' => '\d+'])]
  public function cancel(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
  {
    $connectedUser = $this->getUser();
    if (!$sortie) {
      throw $this->createNotFoundException('La sortie demandée n\'existe pas.');
    }

    // Vérifie si l'utilisateur connecté est l'organisateur
    if ($connectedUser->getId() !== $sortie->getOrganisateur()->getId()) {
      $this->addFlash('danger', 'Vous ne pouvez pas annuler cette sortie car vous n\'êtes pas l\'organisateur.');
      return $this->redirectToRoute('sortie_list');
    }

    // Créer un formulaire pour le motif de l'annulation
    $form = $this->createFormBuilder()
      ->add('motif', TextareaType::class, [
        'label' => 'Motif de l\'annulation',
        'required' => true,
        'attr' => ['placeholder' => 'Veuillez préciser le motif de l\'annulation...']
      ])
      ->getForm();

    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      $motif = $data['motif'];
      if (count($sortie->getInscriptions()) === 0) {
        $this->addFlash('warning', 'Il n\'y a aucun participant inscrit pour cette sortie.');
      } else {


        // Envoi d'un email aux participants inscrits
        foreach ($sortie->getInscriptions() as $inscription) {
          $participant = $inscription->getParticipant();
          $email = (new Email())
            ->from('noreply@example.com')
            ->to($participant->getEmail())
            ->subject('Annulation de la sortie : ' . $sortie->getNom())
            ->text("Bonjour {$participant->getPrenom()},\n\nLa sortie '{$sortie->getNom()}' prévue pour le {$sortie->getDateHeureDebut()->format('d/m/Y')} a été annulée.\n\nMotif : {$motif}.\n\nCordialement,\nL'équipe.");

          $mailer->send($email);
        }
      }

      // Enregistrer l'annulation ou toute action supplémentaire si nécessaire
      $entityManager->flush();

      $this->addFlash('success', 'La sortie a été annulée et les participants ont été informés par email.');

      return $this->redirectToRoute('sortie_list');
    }
    // Afficher la page d'annulation
    return $this->render('sortie/cancel.html.twig', [
      'sortie' => $sortie,
      'form' => $form->createView(),
    ]);
  }
}

