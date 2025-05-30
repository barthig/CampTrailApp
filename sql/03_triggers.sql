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
