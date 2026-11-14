CREATE TABLE IF NOT EXISTS solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campana_id INT NOT NULL,
  vendedor_id INT NOT NULL,
  estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  fecha DATETIME NOT NULL,
  UNIQUE KEY campana_vendedor (campana_id, vendedor_id),
  FOREIGN KEY (campana_id) REFERENCES campanas(id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO solicitudes (campana_id, vendedor_id, estado, fecha)
SELECT DISTINCT campana_id, vendedor_id, 'aprobada', NOW() FROM ventas;
