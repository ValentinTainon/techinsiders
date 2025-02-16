<?php

namespace App\Exception;

use App\Enum\EditorType;

class InvalidCkeditor5FieldArgumentException extends \InvalidArgumentException
{
    public static function missingParameter(string $parameterName, string $className): self
    {
        return new self(
            sprintf(
                'The $%s parameter is required when using the "%s" editor in "%s".',
                $parameterName,
                EditorType::FEATURE_RICH->value,
                $className
            )
        );
    }
}
