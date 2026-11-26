<?php
declare(strict_types=1);

/**
 * Modelo de estudiantes/usuarios: SOLO acceso a datos (PDO con consultas preparadas).
 * Sin validaciones de negocio, sin HTML, sin $_POST/$_SESSION y sin generar ni cifrar claves
 * (el controlador entrega la clave ya cifrada con password_hash()).
 */
final class EstudianteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Para login. ÚNICO método que devuelve la columna `clave` (hash).
     * Nunca enviar este resultado a una vista ni a una respuesta JSON.
     */
    public function buscarPorUsuario(string $usuario): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, carnet, nombre, apellido, usuario, clave, rol
               FROM estudiantes
              WHERE usuario = :usuario
              LIMIT 1'
        );
        $st->execute([':usuario' => $usuario]);
        $fila = $st->fetch();
        return $fila === false ? null : $fila;
    }

    /** Sin la columna `clave`. */
    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, carnet, nombre, apellido, usuario, rol, creado_en
               FROM estudiantes
              WHERE id = :id'
        );
        $st->execute([':id' => $id]);
        $fila = $st->fetch();
        return $fila === false ? null : $fila;
    }

    /** Listado para Tabulator. Sin la columna `clave`. */
    public function listar(): array
    {
        $sql = 'SELECT id, carnet, nombre, apellido, usuario, rol, creado_en
                  FROM estudiantes
              ORDER BY apellido ASC, nombre ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function existeCarnet(string $carnet): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM estudiantes WHERE carnet = :carnet');
        $st->execute([':carnet' => $carnet]);
        return (int) $st->fetchColumn() > 0;
    }

    public function existeUsuario(string $usuario): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM estudiantes WHERE usuario = :usuario');
        $st->execute([':usuario' => $usuario]);
        return (int) $st->fetchColumn() > 0;
    }

    /**
     * @param array{carnet:string,nombre:string,apellido:string,usuario:string,clave:string} $d
     *        `clave` debe venir YA cifrada (password_hash).
     * @param string $rol 'estudiante' (por defecto) o 'admin'; lo decide el controlador, nunca el formulario.
     */
    public function crear(array $d, string $rol = 'estudiante'): int
    {
        $st = $this->db->prepare(
            'INSERT INTO estudiantes (carnet, nombre, apellido, usuario, clave, rol)
             VALUES (:carnet, :nombre, :apellido, :usuario, :clave, :rol)'
        );
        $st->execute([
            ':carnet'   => $d['carnet'],
            ':nombre'   => $d['nombre'],
            ':apellido' => $d['apellido'],
            ':usuario'  => $d['usuario'],
            ':clave'    => $d['clave'],
            ':rol'      => $rol,
        ]);
        return (int) $this->db->lastInsertId();
    }
}
