<?php

namespace App\Support;

class PurposeNormalizer
{
    /**
     * Alias de variantes/errores frecuentes hacia un motivo canonico.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'CLAVE Y USURIO' => 'CLAVE Y USUARIO',
        'CLAVE Y USARIO' => 'CLAVE Y USUARIO',
        'CLAVE USUARIO' => 'CLAVE Y USUARIO',
        'CALVE Y USUARIO' => 'CLAVE Y USUARIO',
        'CLAVE Y USU' => 'CLAVE Y USUARIO',
        'CLAVE Y USUARI' => 'CLAVE Y USUARIO',
        'CLAVE U USUARIO' => 'CLAVE Y USUARIO',
        'CLOAVE Y USUARIO' => 'CLAVE Y USUARIO',
        'USUARIO Y CLAVE' => 'CLAVE Y USUARIO',
        'RECUPERACION DE CLAVE Y USUARIO' => 'CLAVE Y USUARIO',

        'HOMOLOGACIO' => 'HOMOLOGACION',
        'HOMOLOGACIONN' => 'HOMOLOGACION',
        'HOMOLOGACIN' => 'HOMOLOGACION',
        'HOMOLOGAION' => 'HOMOLOGACION',
        'HOMOLOGACICION' => 'HOMOLOGACION',
        'HOMOLOGACIOIN' => 'HOMOLOGACION',
        'HOMOLOGACXION' => 'HOMOLOGACION',
        'HOMOLOGACVION' => 'HOMOLOGACION',

        'CORREPONDENCIA' => 'CORRESPONDENCIA',
        'CORRESONDENCIA' => 'CORRESPONDENCIA',
        'CORRESP' => 'CORRESPONDENCIA',

        'ACTULIZACION' => 'ACTUALIZACION',
        'ACTUALZACION' => 'ACTUALIZACION',

        'ISRL' => 'ISLR',
        'ILSR' => 'ISLR',

        'REUNIOM' => 'REUNION',
        'REUNIOMN' => 'REUNION',
        'REUNIPM' => 'REUNION',

        'PEROSNAL' => 'PERSONAL',
        'OFCIO' => 'OFICIO',
        'ENTREGA DE OFIC' => 'ENTREGA DE OFICIO',

        'ENTREGA D DOC' => 'ENTREGA DE DOC',
        'ENTREGAR DOC' => 'ENTREGA DE DOC',

        'FIRMA HOMOLOLGACION' => 'FIRMA HOMOLOGACION',
        'ACESORAMIENTO' => 'ASESORIA',

        'PREINTALACION' => 'INSTALACION',
        'PRE INSTALACION' => 'INSTALACION',

        'CALVE' => 'CLAVE',
        'CAVE' => 'CLAVE',
    ];

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value));

        if ($value === '') {
            return null;
        }

        $value = mb_strtoupper($value, 'UTF-8');
        $value = strtr($value, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ñ' => 'N',
        ]);

        return $value;
    }

    public static function canonicalize(?string $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        return self::ALIASES[$normalized] ?? $normalized;
    }
}
