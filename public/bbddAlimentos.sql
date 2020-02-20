CREATE DATABASE  IF NOT EXISTS `alimentos`;
USE `alimentos`;


DROP TABLE IF EXISTS `alimentos`;

CREATE TABLE `alimentos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) DEFAULT NULL,
  `energia` decimal(8,0) DEFAULT NULL,
  `proteina` decimal(8,0) DEFAULT NULL,
  `hidratocarbono` decimal(8,0) DEFAULT NULL,
  `fibra` decimal(8,0) DEFAULT NULL,
  `grasatotal` decimal(8,0) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;


INSERT INTO `alimentos` VALUES (9,'Cebolla',12,12,12,12,12),(12,'Aceituna',12,12,12,12,21),(13,'Melocotón',21,21,21,21,21),(14,'Pan',120,12,12,120,120);

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `nomUsuario` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`nomUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `usuarios` VALUES ('admin','$2y$10$h/BWAuavuefuCn5k7BKD..1hoHhuhEDw56MA4PB4QCuKQ7r2GpnNC');

