-- usuarios.sql
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuarios` (`email`, `password`, `nombre`) VALUES
('demo@correo.com', '$2b$12$BZ1DxjPApJJs8gpA1.5Hs.wmQZh8Bj7Vq0Vv1oL.mi5xCo39bo876', 'Usuario de Prueba');
