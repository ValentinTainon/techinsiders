<?php

namespace App\Exception;

use App\Enum\EditorType;

class InvalidCkeditor5FieldArgumentException extends \InvalidArgumentException
{
    public static function missingOption(string $optionName, string $className): self
    {
        return new self(
            sprintf(
                'The "%s" option is required when using the "%s" editor in "%s".',
                $optionName,
                EditorType::FEATURE_RICH->value,
                $className
            )
        );
    }

    public static function uselessOption(string $optionName, string $className): self
    {
        return new self(
            sprintf(
                'The "%s" option is useless when using the "%s" editor in "%s".',
                $optionName,
                EditorType::STARTER->value,
                $className
            )
        );
    }
}
