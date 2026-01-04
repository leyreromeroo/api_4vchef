<?php

namespace App\Entity;

use App\Repository\RecetaNutrienteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetaNutrienteRepository::class)]
class RecetaNutriente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $cantidad = null;

    #[ORM\ManyToOne(inversedBy: 'recetaNutrientes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Receta $receta = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?TipoNutriente $nutriente = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?float
    {
        return $this->cantidad;
    }

    public function setCantidad(float $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getReceta(): ?Receta
    {
        return $this->receta;
    }

    public function setReceta(?Receta $receta): static
    {
        $this->receta = $receta;

        return $this;
    }

    public function getNutriente(): ?TipoNutriente
    {
        return $this->nutriente;
    }

    public function setNutriente(?TipoNutriente $nutriente): static
    {
        $this->nutriente = $nutriente;

        return $this;
    }
}
