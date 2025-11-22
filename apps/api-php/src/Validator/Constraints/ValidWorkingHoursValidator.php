<?php

namespace App\Validator\Constraints;

use App\Service\WorkingHoursValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidWorkingHoursValidator extends ConstraintValidator
{
    public function __construct(
        private WorkingHoursValidator $workingHoursValidator
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidWorkingHours) {
            throw new UnexpectedTypeException($constraint, ValidWorkingHours::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        // Validate working hours array
        $errors = $this->workingHoursValidator->validateWorkingHoursArray($value);
        
        if (!empty($errors)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ error }}', implode('; ', $errors))
                ->addViolation();
        }
    }
}
