-- ========================================
-- EXISTING VIEWS & FUNCTIONS (JANGAN UBAH)
-- ========================================
DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`$$
DROP VIEW IF EXISTS `View_Leaderboard`$$
DROP VIEW IF EXISTS `View_Finished_Events`$$
DROP VIEW IF EXISTS `View_Detailed_Event_Results`$$
DROP VIEW IF EXISTS `View_Dashboard_KPIs`$$

CREATE VIEW `View_Dashboard_KPIs` AS
SELECT
    (SELECT COUNT(id) FROM kis_licenses WHERE expiry_date >= CURDATE()) AS total_pembalap_aktif,
    (SELECT COUNT(id) FROM clubs) AS total_klub,
    (SELECT COUNT(id) FROM events WHERE event_date < CURDATE() AND is_published = 1) AS total_event_selesai,
    (SELECT COUNT(id) FROM kis_applications WHERE status = 'Pending') AS total_kis_pending
$$

CREATE VIEW `View_Finished_Events` AS
SELECT e.*, c.nama_klub AS proposing_club_name
FROM events AS e
LEFT JOIN clubs AS c ON e.proposing_club_id = c.id
WHERE e.is_published = 1 AND e.event_date < CURDATE()
$$

CREATE VIEW `View_Detailed_Event_Results` AS
SELECT 
    er.id AS registration_id, er.event_id, er.pembalap_user_id, er.kis_category_id,
    er.result_position, er.points_earned, er.status AS registration_status,
    u.name AS pembalap_name, u.email AS pembalap_email,
    kc.nama_kategori AS category_name, kc.kode_kategori AS category_code
FROM event_registrations AS er
LEFT JOIN users AS u ON er.pembalap_user_id = u.id
LEFT JOIN kis_categories AS kc ON er.kis_category_id = kc.id
WHERE er.result_position IS NOT NULL OR er.points_earned > 0
$$

CREATE FUNCTION `Func_GetPembalapTotalPoints`(p_pembalap_user_id BIGINT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total_points INT;
    SELECT IFNULL(SUM(points_earned), 0) INTO total_points
    FROM event_registrations WHERE pembalap_user_id = p_pembalap_user_id;
    RETURN total_points;
END$$

CREATE VIEW `View_Leaderboard` AS
SELECT 
    u.name AS nama_pembalap, kc.nama_kategori AS kategori, kc.id AS kategori_id,
    SUM(er.points_earned) AS total_poin, COUNT(er.id) AS jumlah_balapan
FROM event_registrations AS er
JOIN users AS u ON er.pembalap_user_id = u.id
LEFT JOIN kis_categories AS kc ON er.kis_category_id = kc.id
GROUP BY er.pembalap_user_id, u.name, kc.nama_kategori, kc.id
ORDER BY kategori ASC, total_poin DESC
$$

DROP FUNCTION IF EXISTS `Func_Get_Event_Status`$$

CREATE FUNCTION `Func_Get_Event_Status`(
    p_event_date DATE, 
    p_registration_deadline DATE, 
    p_is_published BOOLEAN
) 
RETURNS VARCHAR(20) CHARSET utf8mb4
DETERMINISTIC
BEGIN
    IF p_is_published = 0 THEN
        RETURN 'Draft';
    ELSEIF p_registration_deadline >= CURDATE() THEN
        RETURN 'Open Registration';
    ELSEIF p_event_date >= CURDATE() THEN
        RETURN 'Registration Closed';
    ELSE
        RETURN 'Finished';
    END IF;
END$$