<?php
declare(strict_types=1);

/**
 * Modelo de asistencia e informes: SOLO acceso a datos (PDO preparado).
 * Sin HTML, sin $_POST/$_SESSION ni reglas de negocio.
 */
final class AsistenciaModel
{
    /** Estados válidos (coinciden con el ENUM de asistencias.estado). */
    public const ESTADOS = ['Presente', 'Ausente', 'Justificado'];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Eventos para el selector de asistencia. */
    public function listarEventos(): array
    {
        $sql = 'SELECT id, codigo, nombre, fecha FROM eventos ORDER BY fecha DESC, hora DESC';
        return $this->db->query($sql)->fetchAll();
    }

    /** Estudiantes para el filtro del informe. */
    public function listarEstudiantes(): array
    {
        $sql = 'SELECT id, carnet, nombre, apellido FROM estudiantes ORDER BY apellido, nombre';
        return $this->db->query($sql)->fetchAll();
    }

    /** Inscritos de un evento con su asistencia (estado NULL = sin registrar). */
    public function listarInscritos(int $eventoId): array
    {
        $st = $this->db->prepare(
            'SELECT i.id AS inscripcion_id, es.carnet, es.nombre, es.apellido,
                    i.fecha_inscripcion, a.estado, a.fecha_asistencia
               FROM inscripciones i
               INNER JOIN estudiantes es ON es.id = i.estudiante_id
               LEFT JOIN asistencias a ON a.inscripcion_id = i.id
              WHERE i.evento_id = :evento
              ORDER BY es.apellido, es.nombre'
        );
        $st->execute([':evento' => $eventoId]);
        return $st->fetchAll();
    }

    public function existeInscripcion(int $inscripcionId): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM inscripciones WHERE id = :id');
        $st->execute([':id' => $inscripcionId]);
        return (int) $st->fetchColumn() > 0;
    }

    /** Crea o actualiza la asistencia (una por inscripción). $fecha null = ahora. */
    public function registrar(int $inscripcionId, string $estado, ?string $fecha): void
    {
        $st = $this->db->prepare(
            'INSERT INTO asistencias (inscripcion_id, estado, fecha_asistencia)
             VALUES (:inscripcion, :estado, COALESCE(:fecha, CURRENT_TIMESTAMP))
             ON DUPLICATE KEY UPDATE estado = VALUES(estado), fecha_asistencia = VALUES(fecha_asistencia)'
        );
        $st->execute([':inscripcion' => $inscripcionId, ':estado' => $estado, ':fecha' => $fecha]);
    }

    /** Informe: inscripciones con fecha de inscripción y asistencia. Filtro opcional por estudiante. */
    public function informe(?int $estudianteId): array
    {
        $sql = 'SELECT es.id AS estudiante_id, es.carnet,
                       CONCAT(es.nombre, \' \', es.apellido) AS estudiante,
                       ev.codigo, ev.nombre AS evento, ev.fecha, ev.hora,
                       i.fecha_inscripcion, a.estado, a.fecha_asistencia
                  FROM inscripciones i
                  INNER JOIN estudiantes es ON es.id = i.estudiante_id
                  INNER JOIN eventos ev ON ev.id = i.evento_id
                  LEFT JOIN asistencias a ON a.inscripcion_id = i.id';
        $params = [];
        if ($estudianteId !== null) {
            $sql .= ' WHERE i.estudiante_id = :estudiante';
            $params[':estudiante'] = $estudianteId;
        }
        $sql .= ' ORDER BY es.apellido, es.nombre, ev.fecha DESC';

        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}
