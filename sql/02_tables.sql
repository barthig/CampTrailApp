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
