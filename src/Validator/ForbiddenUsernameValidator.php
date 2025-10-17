<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ForbiddenUsernameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\ForbiddenUsername */

        // Vérifie que le pseudo n'est pas dans la liste des noms interdits
        if (in_array(strtolower($value), array_map('strtolower', $constraint->forbiddenUsernames), true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
