<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'url' => 'O campo :attribute deve ser um link (URL) válido.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'min' => [
        'numeric' => 'O campo :attribute deve ser maior que :min.',
    ],

    'attributes' => [
        'company_name' => 'nome da empresa',
        'job_title' => 'título da vaga',
        'job_description' => 'descrição da vaga',
        'job_salary' => 'salário',
        'job_link' => 'link da vaga',
    ],
]; 