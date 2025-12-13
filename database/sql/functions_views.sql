-- ========================================
-- EXISTING VIEWS & FUNCTIONS (JANGAN UBAH)
-- ========================================
-- TOTAL: 8 VIEWS + 2 FUNCTIONS
-- 
-- VIEWS:
-- 1. View_Dashboard_KPIs - KPI utama dashboard (digunakan di DashboardController)
-- 2. View_Finished_Events - Event yang sudah selesai (digunakan di AnalyticsController)
-- 3. View_Detailed_Event_Results - Detail hasil event (digunakan di PublicPembalapController, EventControllerPembalap)
-- 4. View_Leaderboard - Leaderboard pembalap (digunakan di LeaderboardController)
-- 5. View_Event_Revenue_Ranking - Ranking event berdasarkan revenue (digunakan di AnalyticsController)
-- 6. View_Operational_Alerts - Alert operasional (digunakan di DashboardController, AnalyticsController)
-- 7. View_Revenue_Breakdown_YTD - Breakdown revenue year-to-date (digunakan di AnalyticsController)
-- 8. View_Top_Clubs_Performance - Performa klub teratas (digunakan di AnalyticsController)
--
-- FUNCTIONS:
-- 1. Func_GetPembalapTotalPoints - Menghitung total poin pembalap
-- 2. Func_Get_Event_Status - Menentukan status event
--
DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`$$
DROP VIEW IF EXISTS `View_Leaderboard`$$
DROP VIEW IF EXISTS `View_Finished_Events`$$
DROP VIEW IF EXISTS `View_Detailed_Event_Results`$$
DROP VIEW IF EXISTS `View_Dashboard_KPIs`$$
DROP VIEW IF EXISTS `View_Event_Revenue_Ranking`$$
DROP VIEW IF EXISTS `View_Operational_Alerts`$$
DROP VIEW IF EXISTS `View_Revenue_Breakdown_YTD`$$
DROP VIEW IF EXISTS `View_Top_Clubs_Performance`$$

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

-- ========================================
-- NEW VIEWS FOR ANALYTICS & MONITORING
-- ========================================

CREATE VIEW `View_Event_Revenue_Ranking` AS
SELECT 
    e.id AS event_id,
    e.event_name,
    e.event_date,
    c.nama_klub AS proposing_club,
    COUNT(er.id) AS total_registrations,
    SUM(CASE WHEN er.status IN ('Confirmed', 'Pending Confirmation') THEN 1 ELSE 0 END) AS confirmed_count,
    SUM(CASE WHEN er.status = 'Pending Payment' THEN 1 ELSE 0 END) AS pending_payment_count,
    SUM(CASE WHEN er.status IN ('Confirmed', 'Pending Confirmation') THEN 1 ELSE 0 END) * 100000 AS estimated_revenue
FROM events AS e
LEFT JOIN event_registrations AS er ON e.id = er.event_id
LEFT JOIN clubs AS c ON e.proposing_club_id = c.id
WHERE e.is_published = 1
GROUP BY e.id, e.event_name, e.event_date, c.nama_klub
ORDER BY estimated_revenue DESC
$$

CREATE VIEW `View_Operational_Alerts` AS
SELECT 
    'KIS Application Pending' AS alert_type,
    COUNT(id) AS count,
    'Pending' AS status,
    CURDATE() AS alert_date
FROM kis_applications
WHERE status = 'Pending'
UNION ALL
SELECT 
    'KIS License Expiring Soon' AS alert_type,
    COUNT(id) AS count,
    'Expiring in 30 Days' AS status,
    CURDATE() AS alert_date
FROM kis_licenses
WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
UNION ALL
SELECT 
    'Event Registration Pending Confirmation' AS alert_type,
    COUNT(id) AS count,
    'Pending Confirmation' AS status,
    CURDATE() AS alert_date
FROM event_registrations
WHERE status = 'Pending Confirmation'
UNION ALL
SELECT 
    'Club Dues Pending Approval' AS alert_type,
    COUNT(id) AS count,
    'Pending' AS status,
    CURDATE() AS alert_date
FROM club_dues
WHERE status = 'Pending'
$$

CREATE VIEW `View_Revenue_Breakdown_YTD` AS
SELECT 
    YEAR(e.event_date) AS tahun,
    MONTH(e.event_date) AS bulan,
    DATE_FORMAT(e.event_date, '%Y-%m') AS periode,
    COUNT(DISTINCT e.id) AS total_events,
    COUNT(er.id) AS total_registrations,
    SUM(CASE WHEN er.status IN ('Confirmed', 'Pending Confirmation') THEN 1 ELSE 0 END) AS confirmed_registrations,
    SUM(CASE WHEN er.status IN ('Confirmed', 'Pending Confirmation') THEN 1 ELSE 0 END) * 100000 AS revenue_estimate
FROM events AS e
LEFT JOIN event_registrations AS er ON e.id = er.event_id
WHERE e.is_published = 1 
    AND YEAR(e.event_date) = YEAR(CURDATE())
GROUP BY YEAR(e.event_date), MONTH(e.event_date), DATE_FORMAT(e.event_date, '%Y-%m')
ORDER BY tahun DESC, bulan DESC
$$

CREATE VIEW `View_Top_Clubs_Performance` AS
SELECT 
    c.id AS club_id,
    c.nama_klub,
    COUNT(DISTINCT e.id) AS total_events_organized,
    COUNT(DISTINCT er.id) AS total_participants,
    SUM(CASE WHEN cd.status = 'Approved' THEN cd.amount_paid ELSE 0 END) AS total_dues_paid,
    MAX(cd.payment_date) AS last_payment_date,
    CASE 
        WHEN MAX(cd.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) THEN 'Active'
        ELSE 'Inactive'
    END AS club_status
FROM clubs AS c
LEFT JOIN events AS e ON c.id = e.proposing_club_id
LEFT JOIN event_registrations AS er ON e.id = er.event_id
LEFT JOIN club_dues AS cd ON c.id = cd.club_id
GROUP BY c.id, c.nama_klub
ORDER BY total_events_organized DESC, total_participants DESC
$$