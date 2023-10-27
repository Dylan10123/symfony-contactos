<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Entity\Provincia;
use App\Form\ConfigFormType;
use App\Form\ContactoType;
use App\Form\ProvinciaFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContactoController extends AbstractController
{

    //==============================================================================================================
    //====CONFIG CONTACTO========================================================================================
    #[Route('/contacto/config/{codigo}', name: 'config_contacto')]
    public function configContacto(ManagerRegistry $doctrine, Request $request, $codigo, SessionInterface $session)
    {

        if ($this->getUser()) {
            $repositorio = $doctrine->getRepository(Contacto::class);
            $contacto = $repositorio->find($codigo);

            $formulario = $this->createForm(ConfigFormType::class, $contacto);

            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();
            }


            return $this->render('contacto/config.html.twig', ['formulario' => $formulario->createView(), 'contacto' => $contacto]);
        } else {
            $session->set('codigo', $codigo);
            $session->set('returnTo', 'config_contacto');
            return $this->redirectToRoute('app_login');
        }
    }

    //==============================================================================================================
    //====NUEVO CONTACTO============================================================================================
    #[Route('/contacto/nuevo', name: 'nuevo_contacto')]
    public function nuevoContacto(ManagerRegistry $doctrine, Request $request, SessionInterface $session, SluggerInterface $slugger)
    {

        if ($this->getUser()) {
            $contacto = new Contacto();

            $formulario = $this->createForm(ContactoType::class, $contacto);

            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();

                $file = $formulario->get('file')->getData();
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                    // Move the file to the directory where images are stored
                    try {

                        $file->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );
                        $filesystem = new Filesystem();
                        $filesystem->copy(
                            $this->getParameter('images_directory') . '/' . $newFilename,
                            true
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'file$filename' property to store the PDF file name
                    // instead of its contents
                    $contacto->setFile($newFilename);
                }
                $image = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($image);
                $entityManager->flush();

                return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
            }


            return $this->render('contacto/nuevo.html.twig', ['formulario' => $formulario->createView()]);
        } else {
            $session->set('returnTo', 'nuevo_contacto');
            return $this->redirectToRoute('app_login');
        }
    }

    //==============================================================================================================
    //====NUEVA PROVINCIA===========================================================================================
    #[Route('/provincia/nueva', name: 'nueva_provincia')]
    public function nuevoProvincia(ManagerRegistry $doctrine, Request $request)
    {
        $provincia = new Provincia();

        $formulario = $this->createForm(ProvinciaFormType::class, $provincia);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $provincia = $formulario->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($provincia);
            $entityManager->flush();

            return $this->redirectToRoute('ficha_provincia', ["codigo" => $provincia->getId()]);
        }


        return $this->render('contacto/nuevo.html.twig', ['formulario' => $formulario->createView()]);
    }

    //==============================================================================================================
    //====MODIFICAR CONTACTO========================================================================================
    #[Route('/contacto/editar/{codigo}', name: 'editar_contacto')]
    public function modContacto(ManagerRegistry $doctrine, Request $request, $codigo, SessionInterface $session, SluggerInterface $slugger)
    {
        if ($this->getUser()) {
            $repositorio = $doctrine->getRepository(Contacto::class);
            $contacto = $repositorio->find($codigo);

            $formulario = $this->createForm(ContactoType::class, $contacto);

            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();

                $file = $formulario->get('file')->getData();
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                    // Move the file to the directory where images are stored
                    try {

                        $file->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );
                        $filesystem = new Filesystem();
                        $filesystem->copy(
                            $this->getParameter('images_directory') . '/' . $newFilename,
                            true
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'file$filename' property to store the PDF file name
                    // instead of its contents
                    $contacto->setFile($newFilename);
                }
                $image = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($image);
                $entityManager->flush();

                return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
            }

            return $this->render('contacto/editar.html.twig', ['formulario' => $formulario->createView(), 'images' => $contacto]);
        } else {
            $session->set('codigo', $codigo);
            $session->set('returnTo', 'editar_contacto');
            return $this->redirectToRoute('app_login');
        }
    }

    //==============================================================================================================
    //====LISTAR TODOS LOS CONTACTOS================================================================================
    #[Route('/contactos', name: 'app_listaContactos')]
    public function allContactos(ManagerRegistry $doctrine): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contactos = $repositorio->findAll();
        return $this->render('lista_contactos.html.twig', ['contactos' => $contactos]);
    }

    //==============================================================================================================
    //====MOSTRAR UN CONTACTO=======================================================================================
    #[Route('/contacto/{codigo}', name: 'ficha_contacto')]
    public function contacto(ManagerRegistry $doctrine, int $codigo = 1): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        return $this->render('ficha_contacto.html.twig', ['contacto' => $contacto]);
    }

    //==============================================================================================================
    //====MOSTRAR UNA PROVINCIA=======================================================================================
    #[Route('/provincia/{codigo}', name: 'ficha_provincia')]
    public function provincia(ManagerRegistry $doctrine, int $codigo = 1): Response
    {
        $repositorio = $doctrine->getRepository(Provincia::class);
        $provincia = $repositorio->find($codigo);

        return $this->render('ficha_provincia.html.twig', ['provincia' => $provincia]);
    }

    //==============================================================================================================
    //====BUSCAR CONTACTO===========================================================================================
    #[Route('/contacto/buscar/{texto}', name: 'app_contacto_buscar')]
    public function buscar(ManagerRegistry $doctrine, string $texto): Response
    {
        $repositorio = $doctrine->getRepository(contacto::class);
        $contactos = $repositorio->findByNombre($texto);

        return $this->render('lista_contactos.html.twig', ['contactos' => $contactos]);
    }

    //==============================================================================================================
    //====BORRAR CONTACTO===========================================================================================
    #[Route('/contacto/delete/{id}', name: 'delete_contacto')]
    public function delete(ManagerRegistry $doctrine, int $id)
    {
        if ($this->getUser()) {
            $entityManager = $doctrine->getManager();
            $repositorio = $doctrine->getRepository(Contacto::class);
            $contacto = $repositorio->find($id);
            if ($contacto) {
                try {
                    $entityManager->remove($contacto);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_listaContactos');
                } catch (\Exception $e) {
                    return new Response("Error al elminar el contacto");
                }
            } else {
                return $this->render('ficha_contacto.html.twig', ['contacto' => null]);
            }
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    //==============================================================================================================
    //====INSERTAR CONTACTO CON PROVINCIA===========================================================================
    #[Route('/contacto/insertarConProvincia/{nombre}/{telefono}/{email}/{provincia}', name: 'insertar_con_provincia')]
    public function insConProvincia(ManagerRegistry $doctrine, string $nombre, string $telefono, string $email, string $provincia)
    {
        $entityManager = $doctrine->getManager();
        $prov = new Provincia();

        $prov->setNombre($provincia);
        $contacto = new contacto();

        $contacto->setNombre($nombre);
        $contacto->setTelefono($telefono);
        $contacto->setEmail($email);
        $contacto->setProvincia($prov);

        $entityManager->persist($prov);
        $entityManager->persist($contacto);

        try {
            $entityManager->flush();
            return $this->render('ficha_contacto.html.twig', ['contacto' => $contacto]);
        } catch (\Exception $e) {
            return new Response("Error insertar el contacto");
        }
    }

    //==============================================================================================================
    //====ACTUALIZAR PROVINCIA===========================================================================
    #[Route('/contacto/updateProvincia/{id}/{provincia}', name: 'update_provincia')]
    public function updateprov(ManagerRegistry $doctrine, int $id, string $provincia)
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);
        $repo = $doctrine->getRepository(Provincia::class);
        $prov = $repo->findOneBy(['nombre' => $provincia]);

        if ($contacto) {
            $contacto->setprovincia($prov);
            try {
                $entityManager->flush();
                return $this->render('ficha_contacto.html.twig', ['contacto' => $contacto]);
            } catch (\Exception $e) {
                return new Response("Error al actualizar los datos" . $e->getMessage());
            }
        } else {
            return $this->render('ficha_contacto.html.twig', ['contacto' => null]);
        }
    }

    //==============================================================================================================
    //====INSERTAR CONTACTO SIN PROVINCIA===========================================================================
    #[Route('/contacto/insertarSinprov/{nombre}/{telefono}/{email}/{provincia}', name: 'insertar_sin_provincia')]
    public function insSinprov(ManagerRegistry $doctrine, string $nombre, string $telefono, string $email, string $provincia)
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Provincia::class);

        $prov = $repositorio->findOneBy(['nombre' => $provincia]);

        $contacto = new contacto();

        $contacto->setNombre($nombre);
        $contacto->setTelefono($telefono);
        $contacto->setEmail($email);
        $contacto->setprovincia($prov);

        $entityManager->persist($contacto);

        try {
            $entityManager->flush();
            return $this->render('contactos/ficha_contacto.html.twig', ['contacto' => $contacto]);
        } catch (\Exception $e) {
            return new Response("Error insertar el contacto");
        }
    }

    //==============================================================================================================
    //====INICIO====================================================================================================
    #[Route('/', name: 'index')]
    public function inicio(SessionInterface $session)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_listaContactos');
        } else {
            $session->set('returnTo', 'index');
            return $this->redirectToRoute('app_login');
        }
    }
}
