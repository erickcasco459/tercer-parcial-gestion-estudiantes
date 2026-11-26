<?php
declare(strict_types=1);

/**
 * Modelo de eventos: SOLO acceso a datos (PDO preparado).
 * Sin HTML, sin $_POST/$_SESSION ni reglas de negocio.
 */
final class EventoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Todos los eventos, los más próximos primero. */
    public function listar(): array
    {
        $sql = 'SELECT id, codigo, nombre, descripcion, lugar, fecha, hora, cupo_disponible
                  FROM eventos
                 ORDER BY fecha ASC, hora ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function existeCodigo(string $codigo): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM eventos WHERE codigo = :codigo');
        $st->execute([':codigo' => $codigo]);
        return (int) $st->fetchColumn() > 0;
    }

    /** @param array{codigo:string,nombre:string,descripcion:?string,lugar:string,fecha:string,hora:string,cupo_disponible:int} $d */
    public function crear(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO eventos (codigo, nombre, descripcion, lugar, fecha, hora, cupo_disponible)
             VALUES (:codigo, :nombre, :descripcion, :lugar, :fecha, :hora, :cupo)'
        );
        $st->execute([
            ':codigo'      => $d['codigo'],
            ':nombre'      => $d['nombre'],
            ':descripcion' => $d['descripcion'],
            ':lugar'       => $d['lugar'],
            ':fecha'       => $d['fecha'],
            ':hora'        => $d['hora'],
            ':cupo'        => $d['cupo_disponible'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}
