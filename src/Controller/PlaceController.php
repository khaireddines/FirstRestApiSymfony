<?php

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Place;
use App\Form\PlaceFormType;
use App\Repository\PlaceRepository;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *@Rest\RouteResource("Place")
 */

class PlaceController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var PlaceRepository
     */
    private $placeRepository;
    //Injecting The EntityManager Via Constructor
    public function __construct(
        EntityManagerInterface $entityManager,
        PlaceRepository $placeRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->placeRepository=$placeRepository;
    }
    //ShowAll
    
    public function cgetAction()
    {
        $view=$this->view($this->placeRepository->findAll());
        return $this->handleView($view);
    }
    //ShowOne
    
    public function getAction($id)
    {
        $view=$this->view($this->placeRepository->find($id));
        return $this->handleView($view);
    }

    //New  
    public function postAction(Request $request)
    {
        $form= $this->createForm(PlaceFormType::class, new Place());
        $form->submit($request->request->all());
        if (false === $form->isValid()) 
        {
            return $this->handleView($this->view($form));
        }
        $this->entityManager->persist($form->getData());
        $this->entityManager->flush();
        return $this->handleView(
            $this->view(
                ['status' => 'ok',],
                Response::HTTP_CREATED
            )
        );

    }
    //Update
    public function putAction($id,Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $existingPlace=$this->placeRepository->find($id);
        $form=$this->createForm(PlaceFormType::class, $existingPlace);
        $form->submit($data);
        if (false === $form->isValid()) 
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $this->formErrorSerializer->convertFormToArray($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        $this->entityManager->flush();

        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT
        );
    }
    //Delete
    public function deleteAction($id)
    {
        $existingPlace=$this->placeRepository->find($id);
        $this->entityManager->remove($existingPlace);
        $this->entityManager->flush();
        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT
        );
    }
   
}
