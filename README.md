# Supervised Driving Experience - Web Application

## Live Application

**URL:** https://shabnam-nazarli.alwaysdata.net/index.php

## Project Description

A PHP web application for managing and analyzing supervised driving experiences. Built with PHP, MySQL, HTML5, CSS, and JavaScript following all teacher requirements.

## Technical Features

### Backend

- PHP with PDO (prepared statements for security)
- MySQL database with foreign key relationships
- Many-to-many relationship for weather conditions
- User-defined PHP class (`DrivingExperience`)
- PHP Sessions for application state

### Frontend

- HTML5 semantic elements (W3C compliant)
- CSS Grid and Flexbox (hand-written CSS)
- Responsive design with mobile optimization
- Mobile-friendly form (numeric keypad, default date/time)

### JavaScript Libraries

- ChartJS for interactive charts
- DataTables.js for sortable/filterable tables
- jQuery (via DataTables)

## Main Features

- **Record Driving Experiences** - Form to enter date, time, distance, and conditions
- **Dashboard** - View all experiences with total kilometers traveled
- **Statistics** - Charts and graphs with filters (by date range, by variable)
- **Variable Management** - Add/edit weather conditions, journey types, road surfaces, traffic types
- **Many-to-Many Relationship** - Multiple weather conditions per experience
- **Data Validation** - Input validation and SQL injection prevention

## Database Structure

- `driving_experience` - Main table
- `weather`, `journey_type`, `road_surface`, `traffic_type` - Reference tables
- `experience_weather` - Junction table (many-to-many)

