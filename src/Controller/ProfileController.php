<?php
/**
 * Created by PhpStorm.
 * User: adelaunay2017
 * Date: 12/07/2018
 * Time: 09:10
 */

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use App\Service\UserService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends Controller
{
    /**
     * @Route ("/profile/modify", name="profile_modify")
     */
    public function modifyProfile(Request $request)
    {
        $profile = $this->getUser()->getProfile();
        $form = $this->CreateForm(ProfileType::class, $profile);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */

            $file = $form->get('picture')->getData();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move($this->getParameter('pictures_profile_directory'), $fileName);

            // updates the 'picture' property to store the PDF file name
            // instead of its contents
            $profile->setPicture($fileName);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($profile);

            $entityManager->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render(
            'profile/modify.html.twig',
            array('addProfileForm' => $form->createView())
        );
    }

    /**

     * @Route("/profile/myprofile", name="my_profile")
     */
    public function getMyProfile()
    {
        // initialize
        $rep = $this->getDoctrine()->getRepository(Profile::class);
        $service = new UserService($rep,$this->container->get('doctrine')->getEntityManager());

        // init variables
        $user = $this->getUser();
        $profile = $rep->findOneById($user->getProfile()->getId());
        $stats = $service->getAllstat($user);

        return $this->render('profile/my-profile.html.twig', [
            'user' => $user,
            'pathImage' => "img/profile/" . $profile->getPicture(),
            'stats' => $stats,
        ]);
    }
}
