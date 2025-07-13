<?php

if (! function_exists('format_currency')) {
    function format_currency(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return preg_replace('/[^\d.]/', '', $value);
    }
}
