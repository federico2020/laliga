<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Club
 *
 * @ORM\Table(name="club")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\ClubRepository")
 */
class Club
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Jugador", mappedBy="club")
     */
    private $jugadores;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="escudo", type="string", length=255, nullable=true)
     */
    private $escudo;

    /**
     * @var string
     *
     * @ORM\Column(name="limite_salarial", type="decimal", precision=10, scale=2)
     */
    private $limiteSalarial;


    public function __construct()
    {
        $this->jugadores = new ArrayCollection();
    }

    public function getJugadores(){
        return $this->jugadores;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Club
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set escudo
     *
     * @param string $escudo
     *
     * @return Club
     */
    public function setEscudo($escudo)
    {
        $this->escudo = $escudo;

        return $this;
    }

    /**
     * Get escudo
     *
     * @return string
     */
    public function getEscudo()
    {
        return $this->escudo;
    }

    /**
     * Set limiteSalarial
     *
     * @param string $limiteSalarial
     *
     * @return Club
     */
    public function setLimiteSalarial($limiteSalarial)
    {
        $this->limiteSalarial = $limiteSalarial;

        return $this;
    }

    /**
     * Get limiteSalarial
     *
     * @return string
     */
    public function getLimiteSalarial()
    {
        return $this->limiteSalarial;
    }
}

