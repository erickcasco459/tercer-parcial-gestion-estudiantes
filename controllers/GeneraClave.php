<?php
declare(strict_types=1);

/**
 * Generación de claves aleatorias seguras (random_int).
 * Alfabeto sin caracteres ambiguos (sin 0/O, 1/l/I).
 */
trait GeneraClave
{
    protected function generarClave(int $largo = 10): string
    {
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $max      = strlen($alfabeto) - 1;
        $clave    = '';
        for ($i = 0; $i < max(8, $largo); $i++) {
            $clave .= $alfabeto[random_int(0, $max)];
        }
        return $clave;
    }
}
