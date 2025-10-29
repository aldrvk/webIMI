CREATE PROCEDURE `Proc_RegisterPembalapToEvent`(
    IN p_pembalap_user_id BIGINT,
    IN p_event_id BIGINT,
    IN p_category VARCHAR(100)
)
BEGIN
    DECLARE v_kis_active INT DEFAULT 0;
    START TRANSACTION;
    SELECT COUNT(id)
    INTO v_kis_active
    FROM kis_licenses
    WHERE pembalap_user_id = p_pembalap_user_id
      AND expiry_date >= CURDATE();
    IF v_kis_active > 0 THEN
        INSERT INTO event_registrations (
            event_id, 
            pembalap_user_id, 
            category, 
            created_at, 
            updated_at
        )
        VALUES (
            p_event_id,
            p_pembalap_user_id,
            p_category,
            NOW(),
            NOW()
        );
        COMMIT;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pembalap tidak memiliki KIS yang aktif atau valid.';
    END IF;
END; 

CREATE PROCEDURE `Proc_GetLeaderboard`(
    IN p_category VARCHAR(100)
)
BEGIN
    SELECT 
        u.name AS nama_pembalap,
        SUM(er.points_earned) AS total_poin
    FROM event_registrations AS er
    JOIN users AS u ON er.pembalap_user_id = u.id
    WHERE 
        er.category = p_category
    GROUP BY 
        er.pembalap_user_id, u.name
    ORDER BY 
        total_poin DESC,
        u.name ASC;
END; 