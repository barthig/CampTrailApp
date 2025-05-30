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
