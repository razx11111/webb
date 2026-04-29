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
