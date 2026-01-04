<?php

namespace App\Entity;

use App\Repository\ValoracionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; // Importamos el validador

#[ORM\Entity(repositoryClass: ValoracionRepository::class)]
class Valoracion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 5, notInRangeMessage: 'La puntuación debe estar entre {{ min }} y {{ max }}.')]
    #[Assert\NotBlank]
    private ?int $puntuacion = null;

    // Campo IP: Longitud 45 para soportar IPv6 e IPv4
    #[ORM\Column(length: 45)]
    #[Assert\Ip]
    #[Assert\NotBlank]
    private ?string $ip = null;

    // Relación: Muchas valoraciones pertenecen a Una Receta
    #[ORM\ManyToOne(inversedBy: 'valoraciones')]
    #[ORM\JoinColumn(nullable: false)] // No puede haber una valoración sin receta
    private ?Receta $receta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPuntuacion(): ?int
    {
        return $this->puntuacion;
    }

    public function setPuntuacion(int $puntuacion): static
    {
        $this->puntuacion = $puntuacion;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

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
}