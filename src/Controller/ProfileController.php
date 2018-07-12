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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends Controller
{
    /**
     * Affiche la page add-idea du site
     * @Route ("/profile/add", name="profile_add")
     */
    public function addProfile(Request $request){

        $user = $this->getUser();

        $profile = $user->getProfile();
        $form = $this->CreateForm(ProfileType::class, $profile);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $profile->setFirstname($profile->getFirstname());
            $profile->setLastname($profile->getLastname());
            //$profile->setPicture($profile->getPicture());

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */

            //$file = $profile->getPicture();
            $file = $form->get('picture')->getData();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move($this->getParameter('pictures_directory'), $fileName);

            // updates the 'picture' property to store the PDF file name
            // instead of its contents
            $profile->setPicture($fileName);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($profile);

            dump($profile);

            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('profile/modify.html.twig',
            array('addProfileForm' => $form->createView()));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}