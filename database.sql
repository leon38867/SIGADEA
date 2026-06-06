SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS backup_settings;
DROP TABLE IF EXISTS status;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre_completo VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('administrador','usuario') NOT NULL DEFAULT 'usuario',
  grado VARCHAR(20) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_usuario (usuario),
  INDEX idx_usuarios_rol (rol),
  INDEX idx_usuarios_grado (grado)
) ENGINE=InnoDB;

CREATE TABLE status (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  docente_id INT UNSIGNED NULL,
  nombre VARCHAR(100) NOT NULL,
  apellido_p VARCHAR(100) NOT NULL,
  apellido_m VARCHAR(100) NOT NULL,
  turno VARCHAR(20) NOT NULL DEFAULT 'VESPERTINO',
  grado VARCHAR(20) NOT NULL,
  acta VARCHAR(255) DEFAULT '',
  certificado VARCHAR(255) DEFAULT '',
  comp_domicilio VARCHAR(255) DEFAULT '',
  curp VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status_grado (grado),
  INDEX idx_status_nombre (nombre, apellido_p, apellido_m),
  INDEX idx_status_docente (docente_id),
  CONSTRAINT fk_status_docente
    FOREIGN KEY (docente_id) REFERENCES usuarios(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE backup_settings (
  id TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
  dia ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo') NOT NULL,
  hora TINYINT UNSIGNED NOT NULL,
  ultima_ejecucion DATE NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_backup_hora CHECK (hora BETWEEN 8 AND 14)
) ENGINE=InnoDB;

INSERT INTO usuarios (nombre_completo, usuario, password_hash, rol, grado)
VALUES ('Administrador', 'admin', '$2y$10$wD7Gi4pMI20SU8VsNLTskuBQQ9eKVbxf7JL7bgbYnnmGrpHmRl89S', 'administrador', '');

SET FOREIGN_KEY_CHECKS=1;
