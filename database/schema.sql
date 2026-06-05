-- Dataset Table for earthquakes information
-- Source: https://www.seismicportal.eu/
CREATE TABLE earthquakes (
    id SERIAL PRIMARY KEY,
    external_id VARCHAR(100) UNIQUE NOT NULL, -- id from website
    magnitude DECIMAL(3,1), -- magnitude of the earthquake
    magnitude_type VARCHAR(10), -- type of measure: 'ml', 'mw'
    latitude DECIMAL (9,6) NOT NULL , -- latitude of the location
    longitude DECIMAL (9,6) NOT NULL , -- longitude of the location
    depth DECIMAL (5,2), -- depth in km
    region TEXT, -- region of the earthquake (ex: ROMANIA)
    event_time TIMESTAMP WITH TIME ZONE, -- remember the exact time until s AND save with specific timezone
    source_catalog VARCHAR(50), -- source of information (ex: "EMSC-RTS")
    authority VARCHAR(50), -- by who the data was validated (ex: "EMSC")
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Two tables for floods and fires both taken from the same dataset
-- Source: https://www.gdacs.org/

-- Table for Flood information
CREATE TABLE floods (
    id SERIAL PRIMARY KEY,
    external_id VARCHAR(100) UNIQUE NOT NULL, -- Unique identifier from the source (e.g., GUID)
    title TEXT, -- Descriptive title of the flood event
    latitude DECIMAL(9,6) NOT NULL, -- Latitude coordinate
    longitude DECIMAL(9,6) NOT NULL, -- Longitude coordinate
    event_time TIMESTAMP WITH TIME ZONE, -- Timestamp when the alert was published
    source VARCHAR(100) DEFAULT 'GDACS', -- Data origin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Record creation time in our DB
);

-- Table for Fire (Wildfire) information
CREATE TABLE fires (
    id SERIAL PRIMARY KEY,
    external_id VARCHAR(100) UNIQUE NOT NULL,
    title TEXT,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    event_time TIMESTAMP WITH TIME ZONE,
    source VARCHAR(100) DEFAULT 'GDACS/EFFIS',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Authentication Tables

-- Regular Users Table (Can sign up)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Administrators Table (Manual DB insertion only)
CREATE TABLE admins (
    id SERIAL PRIMARY KEY,
    nume VARCHAR(50) NOT NULL,
    prenume VARCHAR(50) NOT NULL,
    nr_tel VARCHAR(20) UNIQUE NOT NULL, -- Used as the login identifier
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Emergency Shelters Table
-- Managed by Administrators, displayed to users during alerts
CREATE TABLE shelters (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    capacity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);