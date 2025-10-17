<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ForbiddenUsername extends Constraint
{
    public string $message = 'Le pseudo "{{ value }}" est réservé et ne peut pas être utilisé.';
    
    // Cette option permet de passer une liste de mots interdits à la contrainte
    public array $forbiddenUsernames = [];
}
