<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidWorkingHours extends Constraint
{
    public string $message = 'Invalid working hours format: {{ error }}';
    
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
