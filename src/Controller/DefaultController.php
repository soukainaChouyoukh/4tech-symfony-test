<?php

namespace App\Controller;

use App\Repository\VehiclesRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends AbstractController
{


    private $em;
    private $vr;
    private $paginator;
    //injection
    public function __construct(EntityManagerInterface $em,VehiclesRepository $vr , PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->vr = $vr;
        $this->paginator = $paginator;
    }

    /**
     * Affiche la page d'accueil des analyses
     *
     * @Route("/",
     *      name="app_home",
     *      requirements={"page"="\d+"},
     *      defaults={"page"="1"}
     * )
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param integer $page
     * @return Response
     */
    public function appDefaultAction(Request $request, $page = 1): Response
    {
        $limit = 12;
        if ($request->isMethod('post')) {
            $sort_by = $request->get('sort_by');
            $ready_to_go = $request->get('ready_to_go');
            $zero_km = $request->get('zero_km');
            $promotion = $request->get('promotion');
            $energy = $request->get('energy');
            $selected_brand = $request->get('brand');
            $selected_model =  $request->get('model');
            $car_type = $request->get('car_type');
            $data = $this->vr->createQueryBuilder('cc')->select();
            if($selected_brand){
                $data->where("cc.brand LIKE :selected_brand");
                $data->setParameter('selected_brand', $selected_brand);
            }
            if($selected_model){
                $data->where("cc.model LIKE :selected_model");
                $data->setParameter('selected_model', $selected_model);
            }
            if($energy){
                $data->where("cc.energy LIKE :energy");
                $data->setParameter('energy', $energy);
            }
            if($promotion){
                $data->where("cc.price_retail <> null");
            }
            if($sort_by){
                switch ($sort_by){
                    case 'brand_asc':
                        $data->orderBy('cc.brand','ASC');
                        break;
                    case 'brand_desc':
                        $data->orderBy('cc.brand','DESC');
                        break;
                    case 'price_asc':
                        $data->orderBy('cc.price','ASC');
                        break;
                    case 'price_desc':
                        $data->orderBy('cc.price','DESC');
                        break;
                }
            }
            $data = $data->getQuery();
        }else{
            $data = $this->vr->findAll();
            $selected_brand = [];
            $selected_model = [];
            $car_type = [];
            $energy = [];
            $promotion = false;
        }
        $vehicles = $this->paginator->paginate($data,$page,$limit);
        $vehicles->setTemplate('@KnpPaginator/Pagination/tailwindcss_pagination.html.twig');
        //var_dump($vehicles);die();
        return $this->render('app/index.html.twig', [
            'vehicles' => $vehicles,
            'page' => $page,
            'models' => $this->getModles(),
            'brands' => $this->getBrands(),
            'selectd_energy' => $energy,
            'selected_brand' => $selected_brand,
            'selected_model' => $selected_model,
            'select_car_type' => $car_type,
            'promotion' => $promotion,
        ]);
    }

    /**
     * @return int|mixed|string
     */
    public function getModles()
    {
        $models = $this->vr->createQueryBuilder('cc')
            ->select('DISTINCT cc.model')
            ->getQuery();
        $models = $models->getResult();
        return $models;
    }

    /**
     * @return int|mixed|string
     */
    public function getBrands()
    {
        $brands = $this->vr->createQueryBuilder('cc')
            ->select('DISTINCT cc.brand')
            ->getQuery();
        $brands = $brands->getResult();
        return $brands;
    }


}