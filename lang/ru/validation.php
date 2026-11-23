<?php

declare(strict_types=1);

// Сообщения валидации на русском. Набор правил расширяется по мере
// появления новых полей; :attribute подставляет подпись поля.

return [
    'accepted' => 'Поле «:attribute» нужно принять.',
    'alpha_dash' => 'Поле «:attribute» может содержать только латинские буквы, цифры, дефис и подчёркивание.',
    'array' => 'Поле «:attribute» должно быть списком.',
    'boolean' => 'Поле «:attribute» должно быть «да» или «нет».',
    'confirmed' => 'Поле «:attribute» не совпадает с подтверждением.',
    'date' => 'Поле «:attribute» должно быть датой.',
    'different' => 'Поля «:attribute» и «:other» должны различаться.',
    'email' => 'Поле «:attribute» должно быть корректным адресом почты.',
    'enum' => 'Выбрано недопустимое значение поля «:attribute».',
    'exists' => 'Выбранное значение поля «:attribute» не найдено.',
    'file' => 'Поле «:attribute» должно быть файлом.',
    'image' => 'Поле «:attribute» должно быть изображением.',
    'in' => 'Выбрано недопустимое значение поля «:attribute».',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'max' => [
        'array' => 'В поле «:attribute» не больше :max элементов.',
        'file' => 'Файл «:attribute» не больше :max КБ.',
        'numeric' => 'Поле «:attribute» не больше :max.',
        'string' => 'Поле «:attribute» не длиннее :max символов.',
    ],
    'min' => [
        'array' => 'В поле «:attribute» не меньше :min элементов.',
        'file' => 'Файл «:attribute» не меньше :min КБ.',
        'numeric' => 'Поле «:attribute» не меньше :min.',
        'string' => 'Поле «:attribute» не короче :min символов.',
    ],
    'not_in' => 'Выбрано недопустимое значение поля «:attribute».',
    'prohibited' => 'Поле «:attribute» должно быть пустым.',
    'numeric' => 'Поле «:attribute» должно быть числом.',
    'regex' => 'Поле «:attribute» имеет неверный формат.',
    'required' => 'Заполните поле «:attribute».',
    'string' => 'Поле «:attribute» должно быть строкой.',
    'unique' => 'Такое значение поля «:attribute» уже занято.',
    'url' => 'Поле «:attribute» должно быть ссылкой.',

    'custom' => [],

    'attributes' => [],
];
