CREATE DATABASE IF NOT EXISTS beneficios
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE beneficios;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','vendedor') NOT NULL DEFAULT 'vendedor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@gmail.com', '$2y$10$agkeX6LcXsgRE3jYTI3cgOGtX1cPN0wJlPBsclOeXXMBZjErc52iy', 'admin'),
('Gerente', 'gerente@gmail.com', '$2y$10$agkeX6LcXsgRE3jYTI3cgOGtX1cPN0wJlPBsclOeXXMBZjErc52iy', 'vendedor');

CREATE TABLE campanas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  fecha_inicio DATE,
  fecha_fin DATE,
  estado ENUM('borrador','abierta','cerrada') NOT NULL DEFAULT 'borrador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campana_id INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  FOREIGN KEY (campana_id) REFERENCES campanas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE movimientos_stock (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  motivo VARCHAR(150),
  fecha DATETIME NOT NULL,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campana_id INT NOT NULL,
  vendedor_id INT NOT NULL,
  cliente VARCHAR(150),
  fecha DATETIME NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  entregado TINYINT(1) NOT NULL DEFAULT 0,
  fecha_entrega DATE NULL,
  FOREIGN KEY (campana_id) REFERENCES campanas(id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE venta_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venta_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unit DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (venta_id) REFERENCES ventas(id),
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venta_id INT NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha DATETIME NOT NULL,
  FOREIGN KEY (venta_id) REFERENCES ventas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE rendiciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campana_id INT NOT NULL,
  vendedor_id INT NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha DATETIME NOT NULL,
  confirmada TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (campana_id) REFERENCES campanas(id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campana_id INT NOT NULL,
  vendedor_id INT NOT NULL,
  estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  fecha DATETIME NOT NULL,
  UNIQUE KEY campana_vendedor (campana_id, vendedor_id),
  FOREIGN KEY (campana_id) REFERENCES campanas(id),
  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
