<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reponse
 *
 * @ORM\Table(name="reponse", indexes={@ORM\Index(name="idRec", columns={"idRec"})})
 * @ORM\Entity
 */
class Reponse
{
    /**
     * @var int
     *
     * @ORM\Column(name="idReponse", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idreponse;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idRec", type="integer", nullable=true)
     */
    private $idrec;

    /**
     * @var string|null
     *
     * @ORM\Column(name="contenu", type="string", length=255, nullable=true)
     */
    private $contenu;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="dateReponse", type="datetime", nullable=true)
     */
    private $datereponse;


}
