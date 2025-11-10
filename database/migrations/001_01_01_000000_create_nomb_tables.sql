-- Crear tablas (MySQL)
CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0,
  categoria VARCHAR(50) DEFAULT NULL,
  estado ENUM('Activo','Inactivo') DEFAULT 'Activo'
);

CREATE TABLE mesas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  estado ENUM('Libre','Ocupada') DEFAULT 'Libre'
);

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mesa_id INT NULL,
  usuario_id INT NULL,
  estado ENUM('Pendiente','En cocina','Servido','Pagado') DEFAULT 'Pendiente',
  total DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE detalle_pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT,
  producto_id INT,
  cantidad INT,
  precio_unitario DECIMAL(10,2),
  subtotal DECIMAL(10,2)
);

CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT,
  tipo_pago VARCHAR(50),
  total DECIMAL(10,2),
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);
