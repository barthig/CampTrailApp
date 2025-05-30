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

-- 2 Tables

CREATE TABLE roles (
  id   SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE permissions (
  id   SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE role_permissions (
  role_id       INT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
  permission_id INT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
  PRIMARY KEY(role_id, permission_id)
);

CREATE TABLE users (
  id            SERIAL PRIMARY KEY,
  email         VARCHAR(255) NOT NULL UNIQUE,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name    VARCHAR(100),
  last_name     VARCHAR(100),
  bio           TEXT,
  avatar        VARCHAR(255) NOT NULL DEFAULT '/public/img/default-avatar.png',
  role_id       INT NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
  notify_services     BOOLEAN NOT NULL DEFAULT TRUE,
  notify_routes       BOOLEAN NOT NULL DEFAULT TRUE,
  notify_destinations BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE campers (
  id                  SERIAL PRIMARY KEY,
  user_id             INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  name                VARCHAR(100) NOT NULL,
  type                VARCHAR(50),
  capacity            INT NOT NULL CHECK (capacity > 0),
  vin                 CHAR(17),
  registration_number VARCHAR(10) UNIQUE,
  brand               VARCHAR(50),
  model               VARCHAR(50),
  year                INT CHECK (year BETWEEN 1900 AND EXTRACT(YEAR FROM CURRENT_DATE)),
  mileage             INT CHECK (mileage >= 0),
  purchase_date       DATE,
  created_at          TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at          TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE camper_images (
  id           SERIAL PRIMARY KEY,
  camper_id    INT NOT NULL REFERENCES campers(id) ON DELETE CASCADE,
  image_url    VARCHAR(512) NOT NULL,
  uploaded_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE destinations (
  id                SERIAL PRIMARY KEY,
  name              VARCHAR(255) NOT NULL,
  location          VARCHAR(255),
  short_description VARCHAR(512),
  description       TEXT,
  price             NUMERIC(10,2) CHECK(price>=0),
  capacity          INT CHECK(capacity>0),
  phone             VARCHAR(50),
  contact_email     VARCHAR(255),
  website           VARCHAR(255),
  latitude          DECIMAL(9,6),
  longitude         DECIMAL(9,6),
  season_from       DATE,
  season_to         DATE,
  checkin_time      TIME,
  checkout_time     TIME,
  rules             TEXT,
  created_at        TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at        TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE amenities (
  id    SERIAL PRIMARY KEY,
  code  VARCHAR(50) UNIQUE NOT NULL,
  label VARCHAR(100) NOT NULL
);

CREATE TABLE destination_amenity (
  destination_id INT NOT NULL REFERENCES destinations(id) ON DELETE CASCADE,
  amenity_id     INT NOT NULL REFERENCES amenities(id) ON DELETE CASCADE,
  PRIMARY KEY(destination_id, amenity_id)
);

CREATE TABLE destination_images (
  id             SERIAL PRIMARY KEY,
  destination_id INT NOT NULL REFERENCES destinations(id) ON DELETE CASCADE,
  image_url      VARCHAR(512) NOT NULL,
  uploaded_at    TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE routes (
  id             SERIAL PRIMARY KEY,
  origin_id      INT NOT NULL REFERENCES destinations(id) ON DELETE RESTRICT,
  destination_id INT NOT NULL REFERENCES destinations(id) ON DELETE RESTRICT,
  camper_id      INT NOT NULL REFERENCES campers(id) ON DELETE CASCADE,
  distance       NUMERIC(8,2) NOT NULL CHECK(distance>=0),
  user_id        INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at     TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at     TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE insurance (
  id                SERIAL PRIMARY KEY,
  camper_id         INT NOT NULL REFERENCES campers(id) ON DELETE CASCADE,
  insurer_name      VARCHAR(100),
  policy_number     VARCHAR(50),
  start_date        DATE,
  end_date          DATE,
  premium           NUMERIC(10,2) CHECK(premium>=0),
  created_at        TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE technical_inspection (
  id                    SERIAL PRIMARY KEY,
  camper_id             INT NOT NULL REFERENCES campers(id) ON DELETE CASCADE,
  inspection_date       DATE NOT NULL,
  valid_until           DATE,
  result                VARCHAR(50),
  inspector_name        VARCHAR(100),
  created_at            TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE notifications (
  id         SERIAL PRIMARY KEY,
  user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  message    TEXT NOT NULL,
  is_read    BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);
CREATE TABLE emergency_contact (
    user_id      INT PRIMARY KEY
                  REFERENCES users(id)
                  ON DELETE CASCADE,
    contact_name VARCHAR(255) NOT NULL,
    phone        VARCHAR(20)  NOT NULL,
    relation     VARCHAR(50)  NOT NULL,
    created_at   TIMESTAMP    DEFAULT now(),
    updated_at   TIMESTAMP    DEFAULT now()
);

-- Triggers and functions
CREATE OR REPLACE FUNCTION set_emergency_contact_updated_at()
RETURNS trigger AS $$
BEGIN
    NEW.updated_at := now();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_emergency_contact_updated_at
BEFORE UPDATE ON emergency_contact
FOR EACH ROW
EXECUTE FUNCTION set_emergency_contact_updated_at();

CREATE OR REPLACE FUNCTION set_updated_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at := NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_timestamp
  BEFORE UPDATE ON campers FOR EACH ROW EXECUTE FUNCTION set_updated_timestamp();
CREATE TRIGGER trg_update_timestamp_destinations
  BEFORE UPDATE ON destinations FOR EACH ROW EXECUTE FUNCTION set_updated_timestamp();
CREATE TRIGGER trg_update_timestamp_routes
  BEFORE UPDATE ON routes FOR EACH ROW EXECUTE FUNCTION set_updated_timestamp();

CREATE OR REPLACE FUNCTION fn_add_notification(_user_id INTEGER, _message TEXT)
RETURNS VOID AS $$
BEGIN
  INSERT INTO notifications(user_id, message) VALUES (_user_id, _message);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trg_destinations_notify()
RETURNS TRIGGER AS $$
DECLARE u RECORD;
BEGIN
  FOR u IN SELECT id FROM users WHERE notify_destinations LOOP
    PERFORM fn_add_notification(u.id, 'Dodano nową destynację: ' || NEW.name);
  END LOOP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER destinations_after_insert AFTER INSERT ON destinations FOR EACH ROW EXECUTE PROCEDURE trg_destinations_notify();

CREATE OR REPLACE FUNCTION trg_inspection_notify()
RETURNS TRIGGER AS $$
DECLARE owner_id INTEGER; camper_nm TEXT;
BEGIN
  IF NEW.valid_until <= CURRENT_DATE + INTERVAL '7 days' THEN
    SELECT user_id, name INTO owner_id, camper_nm FROM campers WHERE id = NEW.camper_id;
    IF owner_id IS NOT NULL THEN
      PERFORM fn_add_notification(owner_id, 'Przegląd kampera "' || camper_nm || '" wygasa ' || NEW.valid_until::TEXT);
    END IF;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER inspection_before_upsert BEFORE INSERT OR UPDATE ON technical_inspection FOR EACH ROW EXECUTE PROCEDURE trg_inspection_notify();

CREATE OR REPLACE FUNCTION trg_policy_notify()
RETURNS TRIGGER AS $$
DECLARE owner_id INTEGER; camper_nm TEXT;
BEGIN
  IF NEW.end_date <= CURRENT_DATE + INTERVAL '7 days' THEN
    SELECT c.user_id, c.name INTO owner_id, camper_nm FROM campers c WHERE c.id = NEW.camper_id;
    IF owner_id IS NOT NULL THEN
      PERFORM fn_add_notification(owner_id, 'Polisa "' || NEW.policy_number || '" dla kampera "' || camper_nm || '" wygasa ' || NEW.end_date::TEXT);
    END IF;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER policy_before_upsert BEFORE INSERT OR UPDATE ON insurance FOR EACH ROW EXECUTE PROCEDURE trg_policy_notify();

CREATE OR REPLACE FUNCTION trg_user_welcome_notify()
RETURNS TRIGGER AS $$
BEGIN
  PERFORM fn_add_notification(NEW.id, 'Witaj w CampTrail!');
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER users_after_insert_welcome AFTER INSERT ON users FOR EACH ROW EXECUTE PROCEDURE trg_user_welcome_notify();

CREATE OR REPLACE FUNCTION trg_routes_notify()
RETURNS TRIGGER AS $$
DECLARE origin_name TEXT; dest_name TEXT;
BEGIN
  SELECT name INTO origin_name FROM destinations WHERE id = NEW.origin_id;
  SELECT name INTO dest_name FROM destinations WHERE id = NEW.destination_id;
  PERFORM fn_add_notification(NEW.user_id, 'Dodano nową trasę: ' || origin_name || ' - ' || dest_name);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER routes_after_insert_notify AFTER INSERT ON routes FOR EACH ROW EXECUTE PROCEDURE trg_routes_notify();

-- Views
CREATE VIEW camper_insurance_view AS
  SELECT i.id, i.camper_id, c.name AS camper_name, i.insurer_name,
         i.policy_number, TO_CHAR(i.start_date, 'YYYY-MM-DD') AS start_date,
         TO_CHAR(i.end_date,   'YYYY-MM-DD') AS end_date, i.premium
    FROM insurance i
    JOIN campers c ON c.id = i.camper_id
   ORDER BY i.start_date DESC;

CREATE VIEW camper_inspections_view AS
  SELECT ti.id, ti.camper_id, c.name AS camper_name,
         TO_CHAR(ti.inspection_date, 'YYYY-MM-DD') AS inspection_date,
         TO_CHAR(ti.valid_until,         'YYYY-MM-DD') AS valid_until,
         ti.result, ti.inspector_name
    FROM technical_inspection ti
    JOIN campers c ON c.id = ti.camper_id
   ORDER BY ti.inspection_date DESC;

CREATE VIEW camper_view AS
  SELECT c.id, c.user_id, u.username AS owner_username, c.name, c.type,
         c.capacity, c.vin, c.registration_number, c.brand, c.model,
         c.year, c.mileage, TO_CHAR(c.purchase_date, 'YYYY-MM-DD') AS purchase_date,
         (SELECT ci.image_url FROM camper_images ci WHERE ci.camper_id = c.id ORDER BY ci.uploaded_at DESC LIMIT 1) AS image_url
    FROM campers c
    JOIN users u ON u.id = c.user_id;

CREATE VIEW destination_full_details_view AS
  SELECT d.id, d.name, d.location, d.short_description, d.description,
         d.price, d.capacity, d.phone, d.contact_email, d.website,
         d.latitude, d.longitude, d.season_from, d.season_to,
         d.checkin_time, d.checkout_time, d.rules,
         COALESCE(di.images, ARRAY[]::text[])    AS images,
         COALESCE(da.amenities, ARRAY[]::text[]) AS amenities
    FROM destinations d
    LEFT JOIN (SELECT destination_id, ARRAY_AGG(image_url ORDER BY id) AS images FROM destination_images GROUP BY destination_id) di ON di.destination_id = d.id
    LEFT JOIN (SELECT da.destination_id, ARRAY_AGG(a.code ORDER BY a.code) AS amenities FROM destination_amenity da JOIN amenities a ON a.id = da.amenity_id GROUP BY da.destination_id) da ON da.destination_id = d.id;

CREATE VIEW route_overview AS
  SELECT r.id AS route_id, r.user_id, u.username AS owner_username,
         TO_CHAR(r.created_at, 'YYYY-MM-DD') AS created_at,
         o.name AS origin, d.name AS destination, c.name AS camper_name,
         ROUND(r.distance::numeric, 1) AS distance
    FROM routes r
    JOIN users u ON r.user_id = u.id
    JOIN destinations o ON r.origin_id = o.id
    JOIN destinations d ON r.destination_id = d.id
    JOIN campers c ON r.camper_id = c.id
   ORDER BY r.created_at DESC;

CREATE VIEW route_stats_monthly AS
  SELECT r.user_id, r.camper_id, TO_CHAR(r.created_at, 'YYYY-MM') AS month_label,
         COUNT(*) AS routes_count, ROUND(COALESCE(SUM(r.distance),0)::numeric,1) AS km_sum
    FROM routes r
   GROUP BY r.user_id, r.camper_id, month_label
   ORDER BY month_label;

CREATE VIEW route_stats_yearly AS
  SELECT r.user_id, r.camper_id, TO_CHAR(r.created_at, 'YYYY') AS year_label,
         COUNT(*) AS routes_count, ROUND(COALESCE(SUM(r.distance),0)::numeric,1) AS km_sum
    FROM routes r
   GROUP BY r.user_id, r.camper_id, year_label
   ORDER BY year_label;

CREATE VIEW vw_user_credentials AS
  SELECT u.id AS id, u.email AS email, u.username AS username,
         u.password_hash AS password_hash, r.name AS role_name
    FROM users u
    JOIN roles r ON u.role_id = r.id;

CREATE VIEW vw_user_profile AS
  SELECT u.id AS id, u.username AS username, u.first_name, u.last_name,
         u.email, COALESCE(u.bio,'') AS bio, u.avatar, r.name AS role_name,
         u.notify_services, u.notify_routes, u.notify_destinations
    FROM users u
    JOIN roles r ON u.role_id = r.id;

-- 5. Sample data
BEGIN TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Roles & permissions
INSERT INTO roles(name) VALUES('admin'),('user');
INSERT INTO permissions(name)
  VALUES('manage_users'),('manage_campers'),
        ('manage_destinations'),('manage_routes'),
        ('manage_notifications');
INSERT INTO role_permissions(role_id,permission_id)
  SELECT (SELECT id FROM roles WHERE name='admin'), p.id FROM permissions p;

-- Users
INSERT INTO users(email,username,password_hash,first_name,last_name,bio,role_id)
VALUES
  ('admin@example.com','admin',crypt('adminpass',gen_salt('bf')),'Admin','User','Superuser',(SELECT id FROM roles WHERE name='admin')),
  ('traveler@example.com','traveler',crypt('travelerpass',gen_salt('bf')),'Traveler','User',NULL,(SELECT id FROM roles WHERE name='user'));

-- Campers tied to users
INSERT INTO campers(user_id,name,type,capacity,vin,registration_number,brand,model,year,mileage,purchase_date)
VALUES
  ((SELECT id FROM users WHERE username='traveler'),'Family Camper','Standard',4,'1HGCM82633A004352','WA12345','Ford','Transit',2018,45000,'2018-06-15'),
  ((SELECT id FROM users WHERE username='traveler'),'Luxury Camper','Premium',6,'2FMDK3KC8CBA12345','KR98765','Mercedes','Sprinter',2020,30000,'2020-04-20');

-- Insurance, inspections & maintenance
INSERT INTO insurance(camper_id,insurer_name,policy_number,start_date,end_date,premium)
VALUES
  ((SELECT id FROM campers WHERE name='Family Camper'),'PZU','PZU123','2024-01-01','2025-01-01',1250.00),
  ((SELECT id FROM campers WHERE name='Luxury Camper'),'Warta','WARTA456','2024-02-01','2025-02-01',1500.00);

INSERT INTO technical_inspection(camper_id,inspection_date,valid_until,result,inspector_name)
VALUES
  ((SELECT id FROM campers WHERE name='Family Camper'),'2024-03-15','2025-03-15','pozytywny','Jan Kontrolny'),
  ((SELECT id FROM campers WHERE name='Luxury Camper'),'2024-04-20','2025-04-20','pozytywny','Anna Przegląd');

-- Destinations & amenities (unchanged)...
-- Routes now reference camper_id
INSERT INTO destinations(name,location,short_description,description,price,capacity,phone,contact_email,website,latitude,longitude,season_from,season_to,checkin_time,checkout_time,rules)
VALUES
  ('Sunny Beach','Coastal Town','Cieplutki piasek','Laguna jak talala',150.00,8,'+481234567','beach@ex.com','https://beach',54.35,18.6667,'2025-06-01','2025-09-30','14:00','11:00','Zakaz palenia'),
  ('Mountain View','Wioska obok Zakopanego','Widok tatr','Domki u bacy',200.00,5,'+489876543','mountain@ex.com','https://mountain',49.1234,19.8765,'2025-05-01','2025-10-31','15:00','10:00','Zwierzęta');

INSERT INTO amenities(code,label) VALUES
  ('electricity','Prąd'),('water','Woda pitna'),('sewage','Ścieki'),
  ('wifi','Wi-Fi'),('shop','Sklep'),('access','Dla niepełnosprawnych');

INSERT INTO destination_amenity(destination_id,amenity_id)
SELECT d.id,a.id FROM destinations d JOIN amenities a ON a.code IN('electricity','water','wifi') WHERE d.name='Sunny Beach'
UNION ALL
SELECT d.id,a.id FROM destinations d JOIN amenities a ON a.code IN('electricity','water','sewage','access') WHERE d.name='Mountain View';

-- A sample route assigned to a camper
INSERT INTO routes(origin_id,destination_id,camper_id,distance,user_id)
VALUES
  ((SELECT id FROM destinations WHERE name='Sunny Beach'),
   (SELECT id FROM destinations WHERE name='Mountain View'),
   (SELECT id FROM campers WHERE name='Family Camper'),
   120.50,
   (SELECT id FROM users WHERE username='traveler'));


COMMIT;
