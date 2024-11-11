<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class LengthWithoutHtmlValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof LengthWithoutHtml) {
            throw new UnexpectedTypeException($constraint, LengthWithoutHtml::class);
        }

        $valueWithoutHtml = strip_tags($value);

        if (empty($valueWithoutHtml)) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (mb_strlen($valueWithoutHtml) >= $constraint->min) {
            return;
        }

        $this->context->buildViolation($constraint->minMessage)
            ->setParameter('{{ limit }}', $constraint->min)
            ->addViolation();
    }
}
