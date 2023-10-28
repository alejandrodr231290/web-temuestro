/*
 Navicat Premium Data Transfer

 Source Server         : DOKER
 Source Server Type    : MySQL
 Source Server Version : 80100 (8.1.0)
 Source Host           : localhost:3306
 Source Schema         : tm

 Target Server Type    : MySQL
 Target Server Version : 80100 (8.1.0)
 File Encoding         : 65001

 Date: 14/09/2023 09:40:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for almacen
-- ----------------------------
DROP TABLE IF EXISTS `almacen`;
CREATE TABLE `almacen`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `conexion_id` int NULL DEFAULT NULL,
  `id_almacen` int NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seleccionado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_D5B2D25027105691`(`conexion_id` ASC) USING BTREE,
  CONSTRAINT `FK_D5B2D25027105691` FOREIGN KEY (`conexion_id`) REFERENCES `conexion` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of almacen
-- ----------------------------
INSERT INTO `almacen` VALUES (1, 1, 6, 'Servicios', 0);
INSERT INTO `almacen` VALUES (2, 1, 8, 'Tienda Villa Clara', 1);
INSERT INTO `almacen` VALUES (3, 1, 9, 'SHOW-ROOM', 0);
INSERT INTO `almacen` VALUES (4, 1, 10, 'Productos No Conformes', 0);
INSERT INTO `almacen` VALUES (5, 2, 6, 'Servicios', 1);
INSERT INTO `almacen` VALUES (6, 2, 8, 'Tienda Villa Clara', 0);
INSERT INTO `almacen` VALUES (7, 2, 9, 'SHOW-ROOM', 0);
INSERT INTO `almacen` VALUES (8, 2, 10, 'Productos No Conformes', 0);

-- ----------------------------
-- Table structure for conexion
-- ----------------------------
DROP TABLE IF EXISTS `conexion`;
CREATE TABLE `conexion`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `unidad_id` int NULL DEFAULT NULL,
  `host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `instancia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` int NOT NULL,
  `sistema` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_847691C19D01464C`(`unidad_id` ASC) USING BTREE,
  CONSTRAINT `FK_847691C19D01464C` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of conexion
-- ----------------------------
INSERT INTO `conexion` VALUES (1, 1, '10.10.0.8', 'SQLEXPRESS', 'facsinv', 'remoto', 'remoto', 1, 2);
INSERT INTO `conexion` VALUES (2, 1, '10.10.0.8', 'SQLEXPRESS', 'facsinv', 'remoto', 'remoto', 2, 2);

-- ----------------------------
-- Table structure for imagen
-- ----------------------------
DROP TABLE IF EXISTS `imagen`;
CREATE TABLE `imagen`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `unidad_id` int NULL DEFAULT NULL,
  `idproducto` int NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_8319D2B39D01464C`(`unidad_id` ASC) USING BTREE,
  CONSTRAINT `FK_8319D2B39D01464C` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of imagen
-- ----------------------------

-- ----------------------------
-- Table structure for mes
-- ----------------------------
DROP TABLE IF EXISTS `mes`;
CREATE TABLE `mes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mes
-- ----------------------------
INSERT INTO `mes` VALUES (1, 'Enero', 1);
INSERT INTO `mes` VALUES (2, 'Febrero', 2);
INSERT INTO `mes` VALUES (3, 'Marzo', 3);
INSERT INTO `mes` VALUES (4, 'Abril', 4);
INSERT INTO `mes` VALUES (5, 'Mayo', 5);
INSERT INTO `mes` VALUES (6, 'Junio', 6);
INSERT INTO `mes` VALUES (7, 'Julio', 7);
INSERT INTO `mes` VALUES (8, 'Agosto', 8);
INSERT INTO `mes` VALUES (9, 'Septiembre', 9);
INSERT INTO `mes` VALUES (10, 'Octubre', 10);
INSERT INTO `mes` VALUES (11, 'Noviembre', 11);
INSERT INTO `mes` VALUES (12, 'Diciembre', 12);

-- ----------------------------
-- Table structure for messenger_messages
-- ----------------------------
DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE `messenger_messages`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime NULL DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_75EA56E0FB7336F0`(`queue_name` ASC) USING BTREE,
  INDEX `IDX_75EA56E0E3BD61CE`(`available_at` ASC) USING BTREE,
  INDEX `IDX_75EA56E016BA31DB`(`delivered_at` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of messenger_messages
-- ----------------------------

-- ----------------------------
-- Table structure for plan
-- ----------------------------
DROP TABLE IF EXISTS `plan`;
CREATE TABLE `plan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `unidad_id` int NULL DEFAULT NULL,
  `mes_id` int NULL DEFAULT NULL,
  `venta` double NOT NULL,
  `servicio` double NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `IDX_DD5A5B7D9D01464C`(`unidad_id` ASC) USING BTREE,
  INDEX `IDX_DD5A5B7DB4F0564A`(`mes_id` ASC) USING BTREE,
  CONSTRAINT `FK_DD5A5B7D9D01464C` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_DD5A5B7DB4F0564A` FOREIGN KEY (`mes_id`) REFERENCES `mes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan
-- ----------------------------
INSERT INTO `plan` VALUES (1, 1, 1, 0, 0);
INSERT INTO `plan` VALUES (2, 1, 2, 0, 0);
INSERT INTO `plan` VALUES (3, 1, 3, 0, 0);
INSERT INTO `plan` VALUES (4, 1, 4, 0, 0);
INSERT INTO `plan` VALUES (5, 1, 5, 0, 0);
INSERT INTO `plan` VALUES (6, 1, 6, 0, 0);
INSERT INTO `plan` VALUES (7, 1, 7, 0, 0);
INSERT INTO `plan` VALUES (8, 1, 8, 0, 0);
INSERT INTO `plan` VALUES (9, 1, 9, 0, 0);
INSERT INTO `plan` VALUES (10, 1, 10, 0, 0);
INSERT INTO `plan` VALUES (11, 1, 11, 0, 0);
INSERT INTO `plan` VALUES (12, 1, 12, 0, 0);

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES (1, 'Trabajador', 'ROLE_TRABAJADOR', 1);
INSERT INTO `role` VALUES (2, 'Comercial', 'ROLE_COMERCIAL', 2);
INSERT INTO `role` VALUES (3, 'Directivo', 'ROLE_DIRECTIVO', 3);
INSERT INTO `role` VALUES (4, 'Administrador', 'ROLE_ADMIN', 4);

-- ----------------------------
-- Table structure for unidad
-- ----------------------------
DROP TABLE IF EXISTS `unidad`;
CREATE TABLE `unidad`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `margencomercial` double NOT NULL,
  `codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of unidad
-- ----------------------------
INSERT INTO `unidad` VALUES (1, 'Villa Clara', 11, 'VCL');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `unidad_id` int NULL DEFAULT NULL,
  `rol_id` int NULL DEFAULT NULL,
  `username` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `UNIQ_8D93D649F85E0677`(`username` ASC) USING BTREE,
  INDEX `IDX_8D93D6499D01464C`(`unidad_id` ASC) USING BTREE,
  INDEX `IDX_8D93D6494BAB96C`(`rol_id` ASC) USING BTREE,
  CONSTRAINT `FK_8D93D6494BAB96C` FOREIGN KEY (`rol_id`) REFERENCES `role` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_8D93D6499D01464C` FOREIGN KEY (`unidad_id`) REFERENCES `unidad` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 1, 4, 'admin', '$2y$13$eYWIvBAL6dwdR3Q1049pweB6HS9U7PbgU5FC0uj2epb5FKc0oeWGG', 'admin', '');

SET FOREIGN_KEY_CHECKS = 1;
