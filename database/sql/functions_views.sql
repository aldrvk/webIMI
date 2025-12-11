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

-- ========================================
-- NEW VIEWS FOR PIMPINAN DASHBOARD
-- ========================================
DROP FUNCTION IF EXISTS Func_Get_Event_Status$$
DROP VIEW IF EXISTS View_Revenue_Breakdown_YTD$$
DROP VIEW IF EXISTS View_Operational_Alerts$$
DROP VIEW IF EXISTS View_Top_Clubs_Performance$$
DROP VIEW IF EXISTS View_Event_Revenue_Ranking$$

CREATE FUNCTION Func_Get_Event_Status(event_date DATE)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    IF event_date > CURDATE() THEN RETURN 'Akan Datang';
    ELSEIF event_date = CURDATE() THEN RETURN 'Sedang Berjalan';
    ELSE RETURN 'Selesai';
    END IF;
END$$

CREATE VIEW View_Revenue_Breakdown_YTD AS
SELECT 
    COALESCE((SELECT SUM(amount_paid) FROM club_dues WHERE status = 'Approved' AND YEAR(payment_date) = YEAR(CURDATE())), 0) AS revenue_iuran,
    COALESCE((SELECT SUM(kc.biaya_kis) FROM kis_applications ka JOIN kis_categories kc ON ka.kis_category_id = kc.id WHERE ka.status = 'Approved' AND YEAR(ka.approved_at) = YEAR(CURDATE())), 0) AS revenue_kis,
    COALESCE((SELECT SUM(er.amount_paid) FROM event_registrations er JOIN events e ON er.event_id = e.id WHERE er.status = 'Confirmed' AND YEAR(e.event_date) = YEAR(CURDATE())), 0) AS revenue_event,
    (
        COALESCE((SELECT SUM(amount_paid) FROM club_dues WHERE status = 'Approved' AND YEAR(payment_date) = YEAR(CURDATE())), 0) +
        COALESCE((SELECT SUM(kc.biaya_kis) FROM kis_applications ka JOIN kis_categories kc ON ka.kis_category_id = kc.id WHERE ka.status = 'Approved' AND YEAR(ka.approved_at) = YEAR(CURDATE())), 0) +
        COALESCE((SELECT SUM(er.amount_paid) FROM event_registrations er JOIN events e ON er.event_id = e.id WHERE er.status = 'Confirmed' AND YEAR(e.event_date) = YEAR(CURDATE())), 0)
    ) AS total_revenue_ytd
$$

CREATE VIEW View_Operational_Alerts AS
SELECT 
    (SELECT COUNT(DISTINCT kl.pembalap_user_id) FROM kis_licenses kl WHERE kl.expiry_date < CONCAT(YEAR(CURDATE()), '-01-01') AND kl.pembalap_user_id NOT IN (SELECT pembalap_user_id FROM kis_applications WHERE status IN ('Pending', 'Approved') AND YEAR(created_at) = YEAR(CURDATE()))) AS kis_belum_diperbaharui,
    (SELECT COUNT(*) FROM clubs c WHERE NOT EXISTS (SELECT 1 FROM club_dues cd WHERE cd.club_id = c.id AND cd.payment_year = YEAR(CURDATE()) AND cd.status = 'Approved')) AS klub_belum_bayar_iuran,
    (SELECT COUNT(*) FROM (SELECT e.id FROM events e LEFT JOIN event_registrations er ON e.id = er.event_id WHERE e.is_published = TRUE AND e.event_date >= CURDATE() GROUP BY e.id HAVING COUNT(er.id) < 10) AS low_reg_events) AS event_low_registration
$$

CREATE VIEW View_Top_Clubs_Performance AS
SELECT 
    c.id AS club_id, c.nama_klub,
    (SELECT COUNT(*) FROM pembalap_profiles pp JOIN kis_licenses kl ON pp.user_id = kl.pembalap_user_id WHERE pp.club_id = c.id AND kl.expiry_date >= CURDATE()) AS total_anggota_aktif,
    (SELECT COUNT(*) FROM events e WHERE e.proposing_club_id = c.id AND YEAR(e.event_date) = YEAR(CURDATE())) AS total_event_tahun_ini,
    (SELECT CASE WHEN COUNT(*) > 0 THEN 'Lunas' ELSE 'Belum Bayar' END FROM club_dues cd WHERE cd.club_id = c.id AND cd.payment_year = YEAR(CURDATE()) AND cd.status = 'Approved') AS status_iuran,
    (
        (SELECT COUNT(*) FROM pembalap_profiles pp JOIN kis_licenses kl ON pp.user_id = kl.pembalap_user_id WHERE pp.club_id = c.id AND kl.expiry_date >= CURDATE()) * 10 +
        (SELECT COUNT(*) FROM events e WHERE e.proposing_club_id = c.id AND YEAR(e.event_date) = YEAR(CURDATE())) * 50 +
        (SELECT CASE WHEN COUNT(*) > 0 THEN 100 ELSE 0 END FROM club_dues cd WHERE cd.club_id = c.id AND cd.payment_year = YEAR(CURDATE()) AND cd.status = 'Approved')
    ) AS score_klub
FROM clubs c
ORDER BY score_klub DESC
LIMIT 3
$$

CREATE VIEW View_Event_Revenue_Ranking AS
SELECT 
    e.id AS event_id, e.event_name, e.event_date,
    Func_Get_Event_Status(e.event_date) AS status_event,
    e.biaya_pendaftaran,
    COUNT(er.id) AS total_registrants,
    COALESCE(SUM(er.amount_paid), 0) AS total_revenue
FROM events e
LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status = 'Confirmed'
WHERE e.is_published = TRUE AND YEAR(e.event_date) = YEAR(CURDATE())
GROUP BY e.id, e.event_name, e.event_date, e.biaya_pendaftaran
ORDER BY total_revenue DESC
$$