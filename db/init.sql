CREATE TABLE log_report (
    sensor_uuid TEXT PRIMARY KEY,
    temperature REAL NOT NULL,
    datetime INTEGER NOT NULL
);

CREATE TABLE sensors (
    uuid TEXT PRIMARY KEY,
    ip TEXT
);