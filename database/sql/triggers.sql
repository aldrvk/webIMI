CREATE TRIGGER `auto_create_kis_license_on_approval`
AFTER UPDATE ON `kis_applications`
FOR EACH ROW
BEGIN
    DECLARE v_kis_number VARCHAR(100);
    DECLARE v_expiry_date DATE;

    IF NEW.status = 'Approved' AND OLD.status <> 'Approved' THEN
        SET v_kis_number = CONCAT('KIS-', YEAR(NOW()), '-', NEW.pembalap_user_id, '-', NEW.id);
        SET v_expiry_date = DATE_ADD(DATE(NOW()), INTERVAL 1 YEAR);

        INSERT INTO kis_licenses (
            pembalap_user_id, application_id, kis_number, issued_date, 
            expiry_date, created_at, updated_at
        )
        VALUES (
            NEW.pembalap_user_id, NEW.id, v_kis_number, DATE(NOW()), 
            v_expiry_date, NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE
            application_id = NEW.id,
            kis_number = v_kis_number,
            issued_date = DATE(NOW()),
            expiry_date = v_expiry_date,
            updated_at = NOW();
    END IF;
END;

CREATE TRIGGER `log_kis_application_insert`
AFTER INSERT ON `kis_applications`
FOR EACH ROW
BEGIN
    INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
    VALUES (
        'INSERT',
        'kis_applications',
        NEW.id,
        CONCAT('Status: ', NEW.status),
        NEW.pembalap_user_id,
        NOW(),
        NOW()
    );
END;

CREATE TRIGGER `log_kis_application_update`
AFTER UPDATE ON `kis_applications`
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
        VALUES (
            'UPDATE',
            'kis_applications',
            NEW.id,
            CONCAT('Status: ', OLD.status),
            CONCAT('Status: ', NEW.status, ', Diproses oleh: ', NEW.processed_by_user_id),
            NEW.processed_by_user_id,
            NOW(),
            NOW()
        );
    END IF;
END;

CREATE TRIGGER `log_event_insert`
AFTER INSERT ON `events`
FOR EACH ROW
BEGIN
    INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
    VALUES (
        'INSERT',
        'events',
        NEW.id,
        CONCAT('Event: ', NEW.event_name),
        NEW.created_by_user_id,
        NOW(),
        NOW()
    );
END;