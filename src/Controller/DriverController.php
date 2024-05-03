<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\User;
use App\Form\DriverType;
use App\Repository\DriverRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_GROUP_ADMIN')]
class DriverController extends AbstractController
{
    #[Route('/conductores', name: 'driver_main')]
    public function index(DriverRepository $driverRepository): Response{
        $drivers = $driverRepository->findAll();

        return $this->render('Users/main.html.twig', [
            'drivers' => $drivers,
        ]);
    }

    /**
     * Preguntar si esta funcion tiene que ir en ScheduleController o puede ir en DriverController
     */
    #[Route('/conductores/nuevo', name: 'driver_new')]
    public function nuevo(DriverRepository $driverRepository, Request $request, UserRepository $userRepository): Response
    {
        $driver = new Driver();
        $user = new User();
        $driver->setUser($user);
        $driverRepository->add($driver);
        $userRepository->add($user);
        return $this->modificar($driver, $driverRepository, $request);
    }

    #[Route('/conductor/modificar/{id}', name: 'driver_mod')]
    public function modificar(Driver $driver, DriverRepository $driverRepository, Request $request):Response
    {
        $form = $this->createForm(DriverType::class, $driver);

        $form->handleRequest($request);

        $nuevo = $driver->getId()===null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $driverRepository->save();
                if ($nuevo) {
                    $this->addFlash('success', 'Conductor creado con exito');
                } else {
                    $this->addFlash('success', 'Cambios guardados con exito');
                }
                return $this->redirectToRoute('driver_main');
            } catch (\Exception $e) {
                $this->addFlash('error', 'No se han podido guardar los cambios');
            }
        }
        return $this->render('Users/modificar.html.twig', [
            'form' => $form->createView(),
            'driver' => $driver,
        ]);
    }
    #[Route('/conductor/eliminar/{id}', name: 'driver_delete')]
    public function eliminar(
        Request $request,
        DriverRepository $driverRepository,
        Driver $driver): Response
    {
        if ($request->request->has('confirmar')) {
            try {
                $driverRepository->remove($driver);
                $driverRepository->save();
                $this->addFlash('success', 'Conductor eliminado con éxito');
                return $this->redirectToRoute('driver_main');
            } catch (\Exception $e) {
                $this->addFlash('error', 'No se ha podido eliminar el conductor');
            }
        }
        return $this->render('Users/eliminar.html.twig', [
            'driver' => $driver
        ]);
    }
}