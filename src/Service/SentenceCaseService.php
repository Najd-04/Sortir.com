<?php

namespace App\Service;

class SentenceCaseService
{
/**
 *
 * Enlève les caractères spéciaux, passe toutes les lettres en minuscules
 * puis applique une majuscule à la première lettre de chaque mot.
 *
 * @param string $input pour la chaîne de caractères entrant
 * @return string, la chaîne de caractères une fois traitée.
 */

public function appliquerSentenceCase(string $input): string {

    $cleanedString = preg_replace('/[^A-Za-z0-9áéíóúàèìòùãõâêîôûäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ\s]/', ' ', $input);

    $cleanedString = trim($cleanedString);

    $lowercaseString = strtolower($cleanedString);

    $sentenceCaseString = ucfirst($lowercaseString);

    return $sentenceCaseString;

}



}