<?php

namespace App\Entity;

use App\Repository\RecetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetaRepository::class)]
class Receta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foto = null;

    #[ORM\Column]
    private ?int $comensales = null;

    #[ORM\Column]
    private ?bool $deleted = false; // La receta por defecto no está borrada (false). Cambio de null a false porque es un booleano en la BBDD

    #[ORM\ManyToOne(inversedBy: 'recetas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TipoReceta $tipo = null;

    /**
     * @var Collection<int, Ingrediente>
     */
    #[ORM\OneToMany(targetEntity: Ingrediente::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $ingredientes;

    /**
     * @var Collection<int, Paso>
     */
    #[ORM\OneToMany(targetEntity: Paso::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $pasos;

    /**
     * @var Collection<int, RecetaNutriente>
     */
    #[ORM\OneToMany(targetEntity: RecetaNutriente::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $recetaNutrientes;

    /**
     * @var Collection<int, Valoracion>
     */
    #[ORM\OneToMany(targetEntity: Valoracion::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $valoraciones;

    public function __construct()
    {
        $this->ingredientes = new ArrayCollection();
        $this->pasos = new ArrayCollection();
        $this->recetaNutrientes = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function getComensales(): ?int
    {
        return $this->comensales;
    }

    public function setComensales(int $comensales): static
    {
        $this->comensales = $comensales;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getTipo(): ?TipoReceta
    {
        return $this->tipo;
    }

    public function setTipo(?TipoReceta $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return Collection<int, Ingrediente>
     */
    public function getIngredientes(): Collection
    {
        return $this->ingredientes;
    }

    public function addIngrediente(Ingrediente $ingrediente): static
    {
        if (!$this->ingredientes->contains($ingrediente)) {
            $this->ingredientes->add($ingrediente);
            $ingrediente->setReceta($this);
        }

        return $this;
    }

    public function removeIngrediente(Ingrediente $ingrediente): static
    {
        if ($this->ingredientes->removeElement($ingrediente)) {
            // set the owning side to null (unless already changed)
            if ($ingrediente->getReceta() === $this) {
                $ingrediente->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paso>
     */
    public function getPasos(): Collection
    {
        return $this->pasos;
    }

    public function addPaso(Paso $paso): static
    {
        if (!$this->pasos->contains($paso)) {
            $this->pasos->add($paso);
            $paso->setReceta($this);
        }

        return $this;
    }

    public function removePaso(Paso $paso): static
    {
        if ($this->pasos->removeElement($paso)) {
            // set the owning side to null (unless already changed)
            if ($paso->getReceta() === $this) {
                $paso->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RecetaNutriente>
     */
    public function getRecetaNutrientes(): Collection
    {
        return $this->recetaNutrientes;
    }

    public function addRecetaNutriente(RecetaNutriente $recetaNutriente): static
    {
        if (!$this->recetaNutrientes->contains($recetaNutriente)) {
            $this->recetaNutrientes->add($recetaNutriente);
            $recetaNutriente->setReceta($this);
        }

        return $this;
    }

    public function removeRecetaNutriente(RecetaNutriente $recetaNutriente): static
    {
        if ($this->recetaNutrientes->removeElement($recetaNutriente)) {
            // set the owning side to null (unless already changed)
            if ($recetaNutriente->getReceta() === $this) {
                $recetaNutriente->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Valoracion>
     */
    public function getValoraciones(): Collection
    {
        return $this->valoraciones;
    }

    public function addValoracion(Valoracion $valoracion): static
    {
        if (!$this->valoraciones->contains($valoracion)) {
            $this->valoraciones->add($valoracion);
            $valoracion->setReceta($this);
        }

        return $this;
    }

    public function removeValoracion(Valoracion $valoracion): static
    {
        if ($this->valoraciones->removeElement($valoracion)) {
            // set the owning side to null (unless already changed)
            if ($valoracion->getReceta() === $this) {
                $valoracion->setReceta(null);
            }
        }

        return $this;
    }

    public function getPromedioVotos(): float
    {
        if ($this->valoraciones->isEmpty()) {
            return 0.0;
        }

        $suma = 0;
        foreach ($this->valoraciones as $v) {
            $suma += $v->getPuntuacion();
        }

        return round($suma / $this->valoraciones->count(), 1);
    }
}
