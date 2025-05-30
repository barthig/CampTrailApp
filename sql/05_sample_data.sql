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