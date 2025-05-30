SET client_encoding = 'UTF8';

-- Drop triggers
DROP TRIGGER IF EXISTS trg_update_timestamp ON campers;
DROP TRIGGER IF EXISTS trg_update_timestamp_destinations ON destinations;
DROP TRIGGER IF EXISTS trg_update_timestamp_routes ON routes;
DROP TRIGGER IF EXISTS destinations_after_insert ON destinations;
DROP TRIGGER IF EXISTS inspection_before_upsert ON technical_inspection;
DROP TRIGGER IF EXISTS policy_before_upsert ON insurance;
DROP TRIGGER IF EXISTS users_after_insert_welcome ON users;
DROP TRIGGER IF EXISTS routes_after_insert_notify ON routes;

-- Drop functions
DROP FUNCTION IF EXISTS fn_add_notification(INTEGER, TEXT) CASCADE;
DROP FUNCTION IF EXISTS set_updated_timestamp() CASCADE;
DROP FUNCTION IF EXISTS trg_destinations_notify() CASCADE;
DROP FUNCTION IF EXISTS trg_inspection_notify() CASCADE;
DROP FUNCTION IF EXISTS trg_policy_notify() CASCADE;
DROP FUNCTION IF EXISTS trg_routes_notify() CASCADE;
DROP FUNCTION IF EXISTS trg_user_welcome_notify() CASCADE;

-- Drop views
DROP VIEW IF EXISTS camper_insurance_view;
DROP VIEW IF EXISTS camper_inspections_view;
DROP VIEW IF EXISTS camper_view;
DROP VIEW IF EXISTS destination_full_details_view;
DROP VIEW IF EXISTS route_overview;
DROP VIEW IF EXISTS route_stats_monthly;
DROP VIEW IF EXISTS route_stats_yearly;
DROP VIEW IF EXISTS vw_user_credentials;
DROP VIEW IF EXISTS vw_user_profile;

-- Drop tables
DROP TABLE IF EXISTS amenities CASCADE;
DROP TABLE IF EXISTS camper_images CASCADE;
DROP TABLE IF EXISTS campers CASCADE;
DROP TABLE IF EXISTS destination_amenity CASCADE;
DROP TABLE IF EXISTS destination_images CASCADE;
DROP TABLE IF EXISTS destinations CASCADE;
DROP TABLE IF EXISTS insurance CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS permissions CASCADE;
DROP TABLE IF EXISTS role_permissions CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS routes CASCADE;
DROP TABLE IF EXISTS technical_inspection CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS emergency_contact CASCADE;

CREATE EXTENSION IF NOT EXISTS pgcrypto;