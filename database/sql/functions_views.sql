DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`;
DROP VIEW IF EXISTS `View_Leaderboard`;

CREATE FUNCTION `Func_GetPembalapTotalPoints`(
    p_pembalap_user_id BIGINT
)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total_points INT;
    SELECT IFNULL(SUM(points_earned), 0)
    INTO total_points
    FROM event_registrations
    WHERE pembalap_user_id = p_pembalap_user_id;
    RETURN total_points;
END;

CREATE VIEW `View_Leaderboard` AS
SELECT 
    u.name AS nama_pembalap,
    kc.nama_kategori AS kategori, 
    kc.id AS kategori_id,
    SUM(er.points_earned) AS total_poin,
    COUNT(er.id) AS jumlah_balapan
FROM event_registrations AS er
JOIN users AS u ON er.pembalap_user_id = u.id
LEFT JOIN kis_categories AS kc ON er.kis_category_id = kc.id 
GROUP BY 
    er.pembalap_user_id, u.name, kc.nama_kategori, kc.id
ORDER BY 
    kategori ASC,
    total_poin DESC;
