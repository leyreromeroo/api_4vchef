<?php

namespace App\Controller;

use App\Repository\TipoNutrienteRepository;
use App\Repository\TipoRecetaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class MaestrosController extends AbstractController
{
    // 1. GET Tipos de Receta (Para el desplegable de crear receta)
    #[Route('/tipos-receta', name: 'get_tipos_receta', methods: ['GET'])]
    public function getTiposReceta(TipoRecetaRepository $repo): JsonResponse
    {
        $tipos = $repo->findAll();
        
        $data = [];
        foreach ($tipos as $t) {
            $data[] = [
                'id' => $t->getId(),
                'nombre' => $t->getNombre(),
                'descripcion' => $t->getDescripcion()
            ];
        }

        return $this->json($data);
    }

    // 2. GET Tipos de Nutriente (Para añadir valores nutricionales)
    #[Route('/tipos-nutriente', name: 'get_tipos_nutriente', methods: ['GET'])]
    public function getTiposNutriente(TipoNutrienteRepository $repo): JsonResponse
    {
        $nutrientes = $repo->findAll();

        $data = [];
        foreach ($nutrientes as $n) {
            $data[] = [
                'id' => $n->getId(),
                'nombre' => $n->getNombre(),
                'unidad' => $n->getUnidad()
            ];
        }

        return $this->json($data);
    }
}