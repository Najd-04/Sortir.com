<?php


namespace App\Twig\Components;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Entity\Ville as Ville;


#[AsTwigComponent]
class UpdateFormAlert
{



    public string $type = 'success';
    public string $message;
}

// might need to create setters

// possibility to create a hook via a mount() method.
//mount() is custom but must be called that way

