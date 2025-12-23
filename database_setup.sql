-- ============================================
-- Database Setup for Driving Experience App
-- ============================================
-- This file creates all tables with proper structure
-- and inserts sample reference data

-- Create database (if needed)
-- CREATE DATABASE IF NOT EXISTS shabnam_driving_experience;
-- USE shabnam_driving_experience;

-- ============================================
-- Reference Tables (Lookup Tables)
-- ============================================

-- Weather Conditions Table
CREATE TABLE IF NOT EXISTS `weather` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Journey Type Table
CREATE TABLE IF NOT EXISTS `journey_type` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Road Surface Table
CREATE TABLE IF NOT EXISTS `road_surface` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Traffic Type Table
CREATE TABLE IF NOT EXISTS `traffic_type` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Main Table
-- ============================================

-- Driving Experience Table
CREATE TABLE IF NOT EXISTS `driving_experience` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `driving_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `mileage_km` DECIMAL(10,2) NOT NULL,
  `id_journey` INT(11) NOT NULL,
  `id_surface` INT(11) NOT NULL,
  `id_traffic` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_journey` (`id_journey`),
  KEY `fk_surface` (`id_surface`),
  KEY `fk_traffic` (`id_traffic`),
  CONSTRAINT `fk_journey` FOREIGN KEY (`id_journey`) REFERENCES `journey_type` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_surface` FOREIGN KEY (`id_surface`) REFERENCES `road_surface` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_traffic` FOREIGN KEY (`id_traffic`) REFERENCES `traffic_type` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Junction Table (Many-to-Many)
-- ============================================

-- Experience Weather Junction Table
CREATE TABLE IF NOT EXISTS `experience_weather` (
  `experience_id` INT(11) NOT NULL,
  `weather_id` INT(11) NOT NULL,
  PRIMARY KEY (`experience_id`, `weather_id`),
  KEY `fk_weather` (`weather_id`),
  CONSTRAINT `fk_experience` FOREIGN KEY (`experience_id`) REFERENCES `driving_experience` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_weather` FOREIGN KEY (`weather_id`) REFERENCES `weather` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert Sample Reference Data
-- ============================================

-- Insert Weather Conditions
INSERT IGNORE INTO `weather` (`id`, `label`) VALUES
(1, 'sunny'),
(2, 'cloud'),
(3, 'rainy'),
(4, 'windy'),
(5, 'snowy'),
(6, 'stormy'),
(7, 'foggy');

-- Insert Journey Types
INSERT IGNORE INTO `journey_type` (`id`, `label`) VALUES
(1, 'city'),
(2, 'country'),
(3, 'highway'),
(4, 'mountain driving');

-- Insert Road Surfaces
INSERT IGNORE INTO `road_surface` (`id`, `label`) VALUES
(1, 'dry'),
(2, 'wet'),
(3, 'icy'),
(4, 'gravel'),
(5, 'muddy'),
(6, 'potholes');

-- Insert Traffic Types
INSERT IGNORE INTO `traffic_type` (`id`, `label`) VALUES
(1, 'light'),
(2, 'moderate'),
(3, 'heavy'),
(4, 'no traffic');

