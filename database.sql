-- =====================================================================
-- database.sql  |  Control de inscripciones a eventos académicos
-- Compatible con MySQL 8.0.16+ / MariaDB 10.2+ (CHECK soportado)
-- NOTA: CREATE DATABASE y USE están comentados para importar directo en
-- hostings como Alwaysdata (sin permisos). Crea la BD desde el panel,
-- selecciónala al importar y, en local, descomenta las 3 líneas siguientes.
-- =====================================================================
-- CREATE DATABASE IF NOT EXISTS control_eventos
--   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE control_eventos;

CREATE TABLE IF NOT EXISTS estudiantes (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  carnet     VARCHAR(20)  NOT NULL,
  nombre     VARCHAR(100) NOT NULL,
  apellido   VARCHAR(100) NOT NULL,
  usuario    VARCHAR(50)  NOT NULL,
  clave      VARCHAR(255) NOT NULL,
  rol        ENUM('admin','estudiante') NOT NULL DEFAULT 'estudiante',
  creado_en  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_estudiantes_carnet (carnet),
  UNIQUE KEY uq_estudiantes_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eventos (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo           VARCHAR(20)  NOT NULL,
  nombre           VARCHAR(150) NOT NULL,
  descripcion      TEXT NULL,
  lugar            VARCHAR(150) NOT NULL,
  fecha            DATE NOT NULL,
  hora             TIME NOT NULL,
  cupo_disponible  INT NOT NULL DEFAULT 0,
  creado_en        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_eventos_codigo (codigo),
  CONSTRAINT chk_eventos_cupo CHECK (cupo_disponible >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscripciones (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  estudiante_id      INT UNSIGNED NOT NULL,
  evento_id          INT UNSIGNED NOT NULL,
  fecha_inscripcion  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inscripcion (estudiante_id, evento_id),
  CONSTRAINT fk_insc_estudiante FOREIGN KEY (estudiante_id)
    REFERENCES estudiantes (id) ON DELETE CASCADE,
  CONSTRAINT fk_insc_evento FOREIGN KEY (evento_id)
    REFERENCES eventos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS asistencias (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  inscripcion_id    INT UNSIGNED NOT NULL,
  fecha_asistencia  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado            ENUM('Presente','Ausente','Justificado') NOT NULL DEFAULT 'Presente',
  PRIMARY KEY (id),
  UNIQUE KEY uq_asistencia_inscripcion (inscripcion_id),
  CONSTRAINT fk_asist_inscripcion FOREIGN KEY (inscripcion_id)
    REFERENCES inscripciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Administrador inicial (SOLO PARA PRUEBAS, cambiar la clave en producción)
--   Usuario: admin
--   Clave:   Admin123!
-- ---------------------------------------------------------------------
INSERT INTO estudiantes (carnet, nombre, apellido, usuario, clave, rol)
VALUES ('ADM-0001', 'Administrador', 'Sistema', 'admin', '$2b$10$HF0bMnqAaW7bc2r7n.0bQe3TH.GYfY/myQV9iZUjV4/xgaQPb92Y2', 'admin');
