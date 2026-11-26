<?php
declare(strict_types=1);

/**
 * Modelo de eventos: SOLO acceso a datos (PDO con consultas preparadas).
 * Sin validaciones de negocio, sin HTML, sin $_POST/$_SESSION.
 */
final class EventoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar(): array
    {
        $sql = 'SELECT id, codigo, nombre, descripcion, lugar, fecha, hora, cupo_disponible
                FROM eventos
                ORDER BY fecha DESC, hora DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function obtener(int $id): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, codigo, nombre, descripcion, lugar, fecha, hora, cupo_disponible
             FROM eventos WHERE id = :id'
        );
        $st->execute([':id' => $id]);
        $fila = $st->fetch();
        return $fila === false ? null : $fila;
    }

    public function existeCodigo(string $codigo, ?int $exceptoId = null): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM eventos WHERE codigo = :codigo AND id <> :id');
        $st->execute([':codigo' => $codigo, ':id' => $exceptoId ?? 0]);
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

    public function actualizar(int $id, array $d): bool
    {
        $st = $this->db->prepare(
            'UPDATE eventos
                SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion,
                    lugar = :lugar, fecha = :fecha, hora = :hora, cupo_disponible = :cupo
              WHERE id = :id'
        );
        return $st->execute([
            ':codigo'      => $d['codigo'],
            ':nombre'      => $d['nombre'],
            ':descripcion' => $d['descripcion'],
            ':lugar'       => $d['lugar'],
            ':fecha'       => $d['fecha'],
            ':hora'        => $d['hora'],
            ':cupo'        => $d['cupo_disponible'],
            ':id'          => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $st = $this->db->prepare('DELETE FROM eventos WHERE id = :id');
        $st->execute([':id' => $id]);
        return $st->rowCount() > 0;
    }
}
