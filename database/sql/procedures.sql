DROP PROCEDURE IF EXISTS `Proc_Admin_RecordDues`$$

CREATE PROCEDURE `Proc_Admin_RecordDues`(
    IN p_club_id BIGINT,
    IN p_payment_year YEAR,
    IN p_payment_date DATE,
    IN p_amount_paid DECIMAL(10, 2),
    IN p_payment_proof_url VARCHAR(255),
    IN p_notes TEXT,
    IN p_processed_by_user_id BIGINT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    INSERT INTO club_dues (
        club_id, 
        payment_year, 
        payment_date, 
        amount_paid, 
        payment_proof_url, 
        notes,
        status, 
        processed_by_user_id,
        created_at, 
        updated_at
    )
    VALUES (
        p_club_id, 
        p_payment_year, 
        p_payment_date, 
        p_amount_paid, 
        p_payment_proof_url,
        p_notes,
        'Approved',
        p_processed_by_user_id,
        NOW(), 
        NOW()
    );
    
    COMMIT;
    
END$$

DROP PROCEDURE IF EXISTS `Proc_ApplyForKIS`$$

CREATE PROCEDURE `Proc_ApplyForKIS`(
    IN p_user_id BIGINT,
    IN p_club_id BIGINT,
    IN p_tempat_lahir VARCHAR(255),
    IN p_tanggal_lahir DATE,
    IN p_no_ktp_sim VARCHAR(255),
    IN p_golongan_darah ENUM('A', 'B', 'AB', 'O', '-'),
    IN p_phone_number VARCHAR(20),
    IN p_address TEXT,
    IN p_kis_category_id BIGINT,
    IN p_file_surat_sehat_url VARCHAR(255),
    IN p_file_bukti_bayar_url VARCHAR(255),
    IN p_file_ktp_url VARCHAR(255),
    IN p_file_pas_foto_url VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    INSERT INTO pembalap_profiles (
        user_id, club_id, tempat_lahir, tanggal_lahir, no_ktp_sim, 
        golongan_darah, phone_number, address, created_at, updated_at
    )
    VALUES (
        p_user_id, p_club_id, p_tempat_lahir, p_tanggal_lahir, p_no_ktp_sim,
        p_golongan_darah, p_phone_number, p_address, NOW(), NOW()
    )
    ON DUPLICATE KEY UPDATE
        club_id = p_club_id,
        tempat_lahir = p_tempat_lahir,
        tanggal_lahir = p_tanggal_lahir,
        no_ktp_sim = p_no_ktp_sim,
        golongan_darah = p_golongan_darah,
        phone_number = p_phone_number,
        address = p_address,
        updated_at = NOW();
    
    INSERT INTO kis_applications (
        pembalap_user_id, 
        kis_category_id,
        file_ktp_url,
        file_pas_foto_url,
        status, 
        file_surat_sehat_url, 
        file_bukti_bayar_url, 
        created_at, 
        updated_at
    )
    VALUES (
        p_user_id,
        p_kis_category_id,
        p_file_ktp_url,
        p_file_pas_foto_url,
        'Pending', 
        p_file_surat_sehat_url,
        p_file_bukti_bayar_url,
        NOW(),
        NOW()
    );
    
    COMMIT;
END$$

DROP PROCEDURE IF EXISTS `Proc_GetLeaderboard`$$

CREATE PROCEDURE `Proc_GetLeaderboard`(
    IN p_category_id INT
)
BEGIN
    SELECT 
        u.name AS nama_pembalap,
        c.nama_klub,
        SUM(er.points_earned) AS total_poin,
        COUNT(er.id) AS jumlah_balapan,
        RANK() OVER (ORDER BY SUM(er.points_earned) DESC) as `peringkat`
    FROM event_registrations AS er
    JOIN users AS u ON er.pembalap_user_id = u.id
    JOIN pembalap_profiles AS pp ON u.id = pp.user_id
    JOIN clubs AS c ON pp.club_id = c.id
    JOIN kis_licenses AS kl ON u.id = kl.pembalap_user_id AND kl.expiry_date >= CURDATE()
    WHERE 
        kl.kis_category_id = p_category_id
        AND er.points_earned > 0
    GROUP BY 
        er.pembalap_user_id, u.name, c.nama_klub
    ORDER BY 
        total_poin DESC,
        jumlah_balapan ASC;
END$$
