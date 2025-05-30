CREATE TABLE audit_logs (
  id         SERIAL PRIMARY KEY,
  action     TEXT NOT NULL,
  user_id    INT REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Audit trigger on campers
CREATE OR REPLACE FUNCTION audit_log()
RETURNS TRIGGER AS $$
BEGIN
  INSERT INTO audit_logs(action, user_id, created_at)
    VALUES (TG_TABLE_NAME || '_' || TG_OP, NEW.user_id, NOW());
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER campers_audit
  AFTER INSERT OR UPDATE OR DELETE ON campers
  FOR EACH ROW EXECUTE FUNCTION audit_log();

  CREATE OR REPLACE FUNCTION audit_log()
RETURNS TRIGGER AS $$
BEGIN
  INSERT INTO audit_logs(action, user_id, created_at)
  VALUES (TG_TABLE_NAME || '_' || TG_OP, NEW.user_id, NOW());
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER campers_audit
  AFTER INSERT OR UPDATE OR DELETE ON campers FOR EACH ROW EXECUTE FUNCTION audit_log();