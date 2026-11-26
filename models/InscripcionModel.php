<?php
declare(strict_types=1);

/**
 * Modelo de inscripciones: SOLO acceso a datos (PDO preparado).
 * Sin HTML, sin $_POST/$_SESSION. El id del estudiante llega por parámetro.
 */
final class InscripcionModel
{
    // Resultados posibles de inscribir()
    public const OK         = 'ok';
    public const DUPLICADA  = 'duplicada';
    public const SIN_CUPO   = 'sin_cupo';
    public const SIN_EVENTO = 'sin_evento';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Eventos con cupo > 0 en los que el estudiante aún NO está inscrito. */
    public function listarDisponibles(int $estudianteId): array
    {
        $st = $this->db->prepare(
            'SELECT e.id, e.codigo, e.nombre, e.descripcion, e.lugar, e.fecha, e.hora, e.cupo_disponible
               FROM eventos e
              WHERE e.cupo_disponible > 0
                AND NOT EXISTS (
                    SELECT 1 FROM inscripciones i
                     WHERE i.evento_id = e.id AND i.estudiante_id = :estudiante
                )
              ORDER BY e.fecha ASC, e.hora ASC'
        );
        $st->execute([':estudiante' => $estudianteId]);
        return $st->fetchAll();
    }

    /** Inscripciones del estudiante con los datos del evento. */
    public function listarPorEstudiante(int $estudianteId): array
    {
        $st = $this->db->prepare(
            'SELECT i.id, i.fecha_inscripcion, e.codigo, e.nombre, e.lugar, e.fecha, e.hora
               FROM inscripciones i
               INNER JOIN eventos e ON e.id = i.evento_id
              WHERE i.estudiante_id = :estudiante
              ORDER BY i.fecha_inscripcion DESC'
        );
        $st->execute([':estudiante' => $estudianteId]);
        return $st->fetchAll();
    }

    /**
     * Inscribe en una sola transacción: bloquea el evento (FOR UPDATE), valida
     * duplicado y cupo, inserta la inscripción y descuenta 1 al cupo.
     * Devuelve una constante de resultado; relanza PDOException ante errores de BD
     * (p. ej. SQLSTATE 23000) tras hacer rollBack.
     */
    public function inscribir(int $estudianteId, int $eventoId): string
    {
        $this->db->beginTransaction();
        try {
            // Bloqueo de fila: serializa inscripciones simultáneas al mismo evento
            $st = $this->db->prepare('SELECT cupo_disponible FROM eventos WHERE id = :evento FOR UPDATE');
            $st->execute([':evento' => $eventoId]);
            $cupo = $st->fetchColumn();

            if ($cupo === false) {
                $this->db->rollBack();
                return self::SIN_EVENTO;
            }

            $st = $this->db->prepare(
                'SELECT 1 FROM inscripciones WHERE estudiante_id = :estudiante AND evento_id = :evento LIMIT 1'
            );
            $st->execute([':estudiante' => $estudianteId, ':evento' => $eventoId]);
            if ($st->fetchColumn() !== false) {
                $this->db->rollBack();
                return self::DUPLICADA;
            }

            if ((int) $cupo <= 0) {
                $this->db->rollBack();
                return self::SIN_CUPO;
            }

            $st = $this->db->prepare(
                'INSERT INTO inscripciones (estudiante_id, evento_id) VALUES (:estudiante, :evento)'
            );
            $st->execute([':estudiante' => $estudianteId, ':evento' => $eventoId]);

            $st = $this->db->prepare(
                'UPDATE eventos SET cupo_disponible = cupo_disponible - 1
                  WHERE id = :evento AND cupo_disponible > 0'
            );
            $st->execute([':evento' => $eventoId]);
            if ($st->rowCount() !== 1) {
                $this->db->rollBack();
                return self::SIN_CUPO;
            }

            $this->db->commit();
            return self::OK;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
