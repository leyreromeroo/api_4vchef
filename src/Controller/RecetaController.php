<?php

namespace App\Controller;

use App\Entity\Receta;
use App\Entity\Ingrediente;
use App\Entity\Paso;
use App\Entity\RecetaNutriente;
use App\Repository\RecetaRepository;
use App\Repository\TipoRecetaRepository;
use App\Repository\TipoNutrienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/recetas')]
class RecetaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    // 1. GET Recetas (Con filtro opcional y control de borrado lógico)
    #[Route('', name: 'get_recetas', methods: ['GET'])]
    public function index(Request $request, RecetaRepository $recetaRepo): JsonResponse
    {
        // Recogemos el parámetro de filtro ?tipo=Postre
        $filtroTipo = $request->query->get('tipo');

        // Construimos la query. !!Siempre filtrar 'deleted' => false!!
        $criteria = ['deleted' => false];
        
        // Traemos todas las activas y filtramos
        $recetas = $recetaRepo->findBy($criteria);

        $data = [];
        foreach ($recetas as $receta) {
            // Filtro manual por nombre de tipo si nos lo piden
            if ($filtroTipo && $receta->getTipo()->getNombre() !== $filtroTipo) {
                continue;
            }

            // Construimos el JSON
            $data[] = [
                'id' => $receta->getId(),
                'titulo' => $receta->getTitulo(),
                'foto' => $receta->getFoto(),
                'comensales' => $receta->getComensales(),
                'tipo' => $receta->getTipo()->getNombre(),
                'ingredientes_count' => count($receta->getIngredientes()), // Resumen
                'valoracion_media' => 0 // Cálculo media valoraciones
            ];
        }

        return $this->json($data);
    }

    // 2. GET Una Receta (Detalle completo)
    #[Route('/{id}', name: 'get_receta_detalle', methods: ['GET'])]
    public function show(int $id, RecetaRepository $repo): JsonResponse
    {
        $receta = $repo->find($id);

        // Validación: Existe y NO está borrada
        if (!$receta || $receta->isDeleted()) {
            return $this->json(['error' => 'Receta no encontrada'], 404);
        }

        // Mapeo completo
        $detalle = [
            'id' => $receta->getId(),
            'titulo' => $receta->getTitulo(),
            'comensales' => $receta->getComensales(),
            'tipo' => $receta->getTipo()->getNombre(),
            'ingredientes' => [],
            'pasos' => [],
            'nutricion' => []
        ];

        foreach ($receta->getIngredientes() as $ing) {
            $detalle['ingredientes'][] = [
                'nombre' => $ing->getNombre(),
                'cantidad' => $ing->getCantidad(),
                'unidad' => $ing->getUnidad()
            ];
        }

        foreach ($receta->getPasos() as $paso) {
            $detalle['pasos'][] = [
                'orden' => $paso->getOrden(),
                'descripcion' => $paso->getDescripcion()
            ];
        }

        foreach ($receta->getRecetaNutrientes() as $nut) {
            $detalle['nutricion'][] = [
                'nombre' => $nut->getNutriente()->getNombre(),
                'cantidad' => $nut->getCantidad(),
                'unidad' => $nut->getNutriente()->getUnidad()
            ];
        }

        return $this->json($detalle);
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

    // 4. POST Crear Receta
    #[Route('', name: 'create_receta', methods: ['POST'])]
    public function create(
        Request $request, 
        TipoRecetaRepository $tipoRepo, 
        TipoNutrienteRepository $nutrienteRepo
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // 1. Validaciones básicas
        if (empty($data['titulo']) || empty($data['comensales'])) {
            return $this->json(['error' => 'Faltan datos obligatorios'], 400);
        }
        if (empty($data['ingredientes']) || empty($data['pasos'])) {
            return $this->json(['error' => 'Debe tener al menos 1 ingrediente y 1 paso'], 400);
        }

        // 2. Crear la Receta Base
        $receta = new Receta();
        $receta->setTitulo($data['titulo']);
        $receta->setComensales($data['comensales']);
        $receta->setFoto($data['foto'] ?? null);
        $receta->setDeleted(false); // Por seguridad

        // Asignar Tipo (Buscamos en BBDD por ID o Nombre que venga en el JSON)
        $tipo = $tipoRepo->find($data['tipo_id']); // Asumimos que envían el ID
        if (!$tipo) {
            return $this->json(['error' => 'Tipo de receta no válido'], 400);
        }
        $receta->setTipo($tipo);

        // 3. Crear Ingredientes
        foreach ($data['ingredientes'] as $ingData) {
            $ingrediente = new Ingrediente();
            $ingrediente->setNombre($ingData['nombre']);
            $ingrediente->setCantidad($ingData['cantidad']);
            $ingrediente->setUnidad($ingData['unidad']);
            
            // Relación bidireccional (helper method recomendado o set manual)
            $receta->addIngrediente($ingrediente); 
            $this->em->persist($ingrediente);
        }

        // 4. Crear Pasos
        foreach ($data['pasos'] as $pasoData) {
            $paso = new Paso();
            $paso->setOrden($pasoData['orden']);
            $paso->setDescripcion($pasoData['descripcion']);
            
            $receta->addPaso($paso);
            $this->em->persist($paso);
        }

        // 5. Crear Nutrientes (Entidad Intermedia)
        if (!empty($data['nutrientes'])) {
            foreach ($data['nutrientes'] as $nutData) {
                $tipoNutriente = $nutrienteRepo->find($nutData['nutriente_id']);
                if ($tipoNutriente) {
                    $recetaNutriente = new RecetaNutriente();
                    $recetaNutriente->setNutriente($tipoNutriente); // Relación con el Catálogo
                    $recetaNutriente->setCantidad($nutData['cantidad']);
                    $recetaNutriente->setReceta($receta); // Relación con la Receta
                    
                    $this->em->persist($recetaNutriente);
                }
            }
        }

        // 6. Guardar todo de golpe
        $this->em->persist($receta);
        $this->em->flush();

        return $this->json(['message' => 'Receta creada', 'id' => $receta->getId()], 201);
    }
}