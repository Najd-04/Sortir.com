<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\VilleType;
use App\Helper\SentenceCaseService;
use App\Repository\VilleRepository;
use App\Service\UserImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    #[Route('/villes', name: '_ville')]
    public function afficherVilles(EntityManagerInterface $em,  Request $request, VilleRepository $repository, SentenceCaseService $sentenceCaseService ): Response
    {

        //pour afficher liste villes
        $villes = $repository->findAll();


        //pour ajouter nouvelle ville
        $nouvelleVille = new Ville($sentenceCaseService);

        $inputForm = $this->createForm(VilleType::class, $nouvelleVille,[
            'submit_label' => 'Ajouter',
        ]);
        $inputForm->handleRequest($request);

        // dump($inputForm->getErrors(true));

        if ($inputForm->isSubmitted() && $inputForm->isValid()) {


            $em->persist($nouvelleVille);
            $em->flush();

            $this->addFlash('success', 'Une nouvelle ville a été ajoutée');
            return $this->redirectToRoute('admin_ville');
        }


        return $this->render('gestion_admin/ville.html.twig', [
            'villes' => $villes,
            'inputForm' => $inputForm,
        ]);
    }


    #[Route('/villes/delete/{id}', name: '_ville-delete', requirements: ['id' => '\d+'])]
    public function delete(Ville $ville, EntityManagerInterface $em, Request $request, SentenceCaseService $sentenceCaseService): Response {

       $em->remove($ville);
       $em->flush();
       $this->addFlash('success','La ville a été supprimée');


        return $this->redirectToRoute('admin_ville');
    }

    #[Route('/villes/update/{id}', name: '_ville-update', requirements: ['id' => '\d+'])]
    public function update(Ville $ville, EntityManagerInterface $em, Request $request, SentenceCaseService $sentenceCaseService): Response {

        $form = $this->createForm(VilleType::class, $ville, [
            'submit_label' => 'Modifier',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
         $this->addFlash('success', 'La ville a été modifiée avec succès.');
         return $this->redirectToRoute('admin_ville');
        }


     return $this->render('gestion_admin/edit-ville.html.twig', [
      'form' => $form,
      ]);
    }
  #[Route('/register_users', name: '_register_users')]
  #[IsGranted('ROLE_ADMIN')]
  public function registerUsers(Request $request, UserImportService $userImportService): Response
  {
    // Créez le formulaire pour l'importation de fichiers CSV
    $form = $this->createFormBuilder()
      ->add('file', FileType::class, [
        'label' => 'Importer un fichier CSV pour inscrire les utilisateurs',
        'constraints' => [
          new NotBlank(['message' => 'Veuillez sélectionner un fichier.']),
          new File([
            'mimeTypes' => ['text/csv', 'text/plain'],
            'mimeTypesMessage' => 'Veuillez télécharger un fichier CSV.',
          ]),
        ],
      ])
      ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      /** @var UploadedFile $file */
      $file = $form->get('file')->getData();

      if ($file) {
        // Traite le fichier pour inscrire les utilisateurs
        $result = $userImportService->importUsersFromFile($file->getPathname());

        if ($result['status'] === 'success') {
          $this->addFlash('success', 'Utilisateurs inscrits avec succès !');
        } else {
          $this->addFlash('error', $result['message']);
        }
      } else {
        $this->addFlash('error', 'Le fichier n\'a pas été correctement reçu.');
      }

      return $this->redirectToRoute('admin_register_users');
    }

    return $this->render('admin/register_users.html.twig', [
      'form' => $form->createView(),
    ]);
  }
  #[Route('/list_users', name: '_list_users')]
  public function listUsers(EntityManagerInterface $entityManager): Response
  {
    $users = $entityManager->getRepository(Participant::class)->findAll();

    return $this->render('admin/list_users.html.twig', [
      'users' => $users
    ]);
  }


  #[Route('/delete_user/{id}', name: '_delete_user')]
  public function deleteUser(int $id, EntityManagerInterface $entityManager): Response
  {
    $user = $entityManager->getRepository(Participant::class)->find($id);

    if ($user) {
      $entityManager->remove($user);
      $entityManager->flush();

      $this->addFlash('success', 'Utilisateur supprimé avec succès.');
    } else {
      $this->addFlash('error', 'L\'utilisateur n\'existe pas.');
    }

    return $this->redirectToRoute('admin_list_users');
  }

  // Modifier un utilisateur
  #[Route('/edit_user/{id}', name: '_edit_user')]
  public function editUser(int $id, Request $request, EntityManagerInterface $entityManager): Response
  {
    $user = $entityManager->getRepository(Participant::class)->find($id);

    if (!$user) {
      $this->addFlash('error', 'Utilisateur introuvable.');
      return $this->redirectToRoute('admin_list_users');
    }

    // Tu peux ajouter ici le code pour modifier l'utilisateur avec un formulaire, comme dans la méthode d'inscription
    // ...

    return $this->render('admin/edit_user.html.twig', [
      'user' => $user
    ]);
  }

}

