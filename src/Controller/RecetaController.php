<?php

namespace App\Controller;

use App\Entity\Receta;
use App\Entity\Ingrediente;
use App\Entity\Paso;
use App\Entity\RecetaNutriente;
use App\Entity\Valoracion;
use App\Repository\RecetaRepository;
use App\Repository\TipoRecetaRepository;
use App\Repository\TipoNutrienteRepository;
use App\Repository\ValoracionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/recipes')]
class RecetaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    // 1. GET Recetas (Con filtro opcional y control de borrado lógico)
    #[Route('', name: 'get_recetas', methods: ['GET'])]
    public function index(Request $request, RecetaRepository $recetaRepo): JsonResponse
    {
        // Recogemos el parámetro de filtro ?type=10 (ID del tipo)
        $filtroTipoId = $request->query->get('type');

        // Construimos la query. !!Siempre filtrar 'deleted' => false!!
        $criteria = ['deleted' => false];
        if ($filtroTipoId) {
            $criteria['tipo'] = $filtroTipoId;
        }
        
        $recetas = $recetaRepo->findBy($criteria);

        $data = [];
        foreach ($recetas as $receta) {
            $data[] = [
                'id' => $receta->getId(),
                'title' => $receta->getTitulo(),
                'number-diner' => $receta->getComensales(),
                'type' => [
                    'id' => $receta->getTipo()->getId(),
                    'name' => $receta->getTipo()->getNombre(),
                    'description' => $receta->getTipo()->getDescription()
                ],
                'ingredients' => array_map(fn($i) => [
                    'name' => $i->getNombre(),
                    'quantity' => $i->getCantidad(),
                    'unit' => $i->getUnidad()
                ], $receta->getIngredientes()->toArray()),
                'steps' => array_map(fn($p) => [
                    'order' => $p->getOrden(),
                    'description' => $p->getDescripcion()
                ], $receta->getPasos()->toArray()),
                'nutrients' => array_map(fn($n) => [
                    'id' => $n->getId(),
                    'type' => [
                        'id' => $n->getNutriente()->getId(),
                        'name' => $n->getNutriente()->getNombre(),
                        'unit' => $n->getNutriente()->getUnidad()
                    ],
                    'quantity' => $n->getCantidad()
                ], $receta->getRecetaNutrientes()->toArray()),
                'rating' => [
                    'number-votes' => $receta->getValoraciones()->count(),
                    'rating-avg' => $receta->getPromedioVotos()
                ]
            ];
        }

        return $this->json($data);
    }

    // 2. GET Una Receta (Detalle completo)
    #[Route('/{id}', name: 'get_receta_detalle', methods: ['GET'])]
    public function show(int $id, RecetaRepository $repo): JsonResponse
    {
        $receta = $repo->findOneBy(['id' => $id, 'deleted' => false]);

        if (!$receta) {
            return $this->json(['code' => 404, 'description' => 'Receta no encontrada'], 404);
        }

        return $this->json([
            'id' => $receta->getId(),
            'title' => $receta->getTitulo(),
            'number-diner' => $receta->getComensales(),
            'type' => [
                'id' => $receta->getTipo()->getId(),
                'name' => $receta->getTipo()->getNombre(),
                'description' => $receta->getTipo()->getDescripcion()
            ],
            'ingredients' => array_map(fn($i) => [
                'name' => $i->getNombre(),
                'quantity' => $i->getCantidad(),
                'unit' => $i->getUnidad()
            ], $receta->getIngredientes()->toArray()),
            'steps' => array_map(fn($p) => [
                'order' => $p->getOrden(),
                'description' => $p->getDescripcion()
            ], $receta->getPasos()->toArray()),
            'nutrients' => array_map(fn($n) => [
                'id' => $n->getId(),
                'type' => [
                    'id' => $n->getNutriente()->getId(),
                    'name' => $n->getNutriente()->getNombre(),
                    'unit' => $n->getNutriente()->getUnidad()
                ],
                'quantity' => $n->getCantidad()
            ], $receta->getRecetaNutrientes()->toArray()),
            'rating' => [
                'number-votes' => $receta->getValoraciones()->count(),
                'rating-avg' => $receta->getPromedioVotos()
            ]
        ]);
    }

    // 3. DELETE (Borrado Lógico)
    #[Route('/{id}', name: 'delete_receta', methods: ['DELETE'])]
    public function delete(int $id, RecetaRepository $repo): JsonResponse
    {
        $receta = $repo->find($id);

        if (!$receta || $receta->isDeleted()) {
            return $this->json(['error' => 'Receta no encontrada'], 404);
        }

        // Borrado lógico
        $receta->setDeleted(true);
        $this->em->flush(); // Guardamos el cambio

        return $this->json(['message' => 'Receta eliminada correctamente'], 200);
    }

    // 4. POST Crear Receta (Con validaciones estrictas)
    #[Route('', name: 'create_receta', methods: ['POST'])]
    public function create(
        Request $request, 
        TipoRecetaRepository $tipoRepo, 
        TipoNutrienteRepository $nutrienteRepo
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validaciones obligatorias
        if (empty($data['title'])) {
            return $this->json(['code' => 400, 'description' => 'El título es obligatorio'], 400);
        }
        if (empty($data['number-diner'])) {
            return $this->json(['code' => 400, 'description' => 'El número de comensales es obligatorio'], 400);
        }
        if (empty($data['ingredients']) || count($data['ingredients']) < 1) {
            return $this->json(['code' => 400, 'description' => 'Debe tener al menos 1 ingrediente'], 400);
        }
        if (empty($data['steps']) || count($data['steps']) < 1) {
            return $this->json(['code' => 400, 'description' => 'Debe tener al menos 1 paso'], 400);
        }

        // Validar Tipo Receta
        $tipo = $tipoRepo->find($data['type-id'] ?? null);
        if (!$tipo) {
            return $this->json(['code' => 400, 'description' => 'El tipo de receta no existe'], 400);
        }

        $receta = new Receta();
        $receta->setTitulo($data['title']);
        $receta->setComensales($data['number-diner']);
        $receta->setTipo($tipo);
        $receta->setDeleted(false);

        // Ingredientes
        foreach ($data['ingredients'] as $ingData) {
            $ingrediente = new Ingrediente();
            $ingrediente->setNombre($ingData['name']);
            $ingrediente->setCantidad($ingData['quantity']);
            $ingrediente->setUnidad($ingData['unit']);
            $receta->addIngrediente($ingrediente); 
            $this->em->persist($ingrediente);
        }

        // Pasos
        foreach ($data['steps'] as $pasoData) {
            $paso = new Paso();
            $paso->setOrden($pasoData['order']);
            $paso->setDescripcion($pasoData['description']);
            $receta->addPaso($paso);
            $this->em->persist($paso);
        }

        // Nutrientes
        if (!empty($data['nutrients'])) {
            foreach ($data['nutrients'] as $nutData) {
                $tipoNutriente = $nutrienteRepo->find($nutData['type-id']);
                if (!$tipoNutriente) {
                    return $this->json(['code' => 400, 'description' => 'El tipo de nutriente no existe'], 400);
                }
                $recetaNutriente = new RecetaNutriente();
                $recetaNutriente->setNutriente($tipoNutriente);
                $recetaNutriente->setCantidad($nutData['quantity']);
                $recetaNutriente->setReceta($receta);
                $this->em->persist($recetaNutriente);
            }
        }

        $this->em->persist($receta);
        $this->em->flush();

        return $this->json(['id' => $receta->getId(), 'title' => $receta->getTitulo()], 201);
    }

    // 5. POST Voto (Con control de IP)
    #[Route('/{recipeId}/rating/{rate}', name: 'votar_receta', methods: ['POST'])]
    public function votar(int $recipeId, int $rate, Request $request, ValoracionRepository $valoracionRepo, RecetaRepository $recetaRepo): JsonResponse
    {
        $receta = $recetaRepo->findOneBy(['id' => $recipeId, 'deleted' => false]);
        if (!$receta) {
            return $this->json(['code' => 404, 'description' => 'Receta no encontrada'], 404);
        }

        if ($rate < 0 || $rate > 5) {
            return $this->json(['code' => 400, 'description' => 'El voto debe estar entre 0 y 5'], 400);
        }

        $ip = $request->getClientIp();
        $votoExistente = $valoracionRepo->findOneBy(['receta' => $receta, 'ip' => $ip]);
        if ($votoExistente) {
            return $this->json(['code' => 400, 'description' => 'Ya has votado esta receta'], 400);
        }

        $valoracion = new Valoracion();
        $valoracion->setReceta($receta);
        $valoracion->setIp($ip);
        $valoracion->setPuntuacion($rate);

        $this->em->persist($valoracion);
        $this->em->flush();

        return $this->json([
            'id' => $receta->getId(),
            'rating' => [
                'number-votes' => $receta->getValoraciones()->count(),
                'rating-avg' => $receta->getPromedioVotos()
            ]
        ]);
    }
}