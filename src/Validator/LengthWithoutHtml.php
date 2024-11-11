<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;

#[\Attribute]
class LengthWithoutHtml extends Constraint
{
    public string $minMessage = 'field.constraint.length.min_message';

    #[HasNamedArguments]
    public function __construct(public int $min, ?string $minMessage = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->minMessage = $minMessage ?? $this->minMessage;
    }
}
