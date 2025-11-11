DROP PROCEDURE IF EXISTS `Proc_RegisterPembalapToEvent`;

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

DROP PROCEDURE IF EXISTS `Proc_GetLeaderboard`;

CREATE PROCEDURE `Proc_GetLeaderboard`(
    IN p_category_id INT -- Kita akan filter berdasarkan ID Kategori KIS
)
BEGIN
    SELECT 
        u.name AS nama_pembalap,
        c.nama_klub,
        SUM(er.points_earned) AS total_poin,
        COUNT(er.id) AS jumlah_balapan,
        -- Gunakan window function 'RANK()' untuk memberi peringkat
        RANK() OVER (ORDER BY SUM(er.points_earned) DESC) as `peringkat`
    FROM event_registrations AS er
    JOIN users AS u ON er.pembalap_user_id = u.id
    JOIN pembalap_profiles AS pp ON u.id = pp.user_id
    JOIN clubs AS c ON pp.club_id = c.id
    JOIN kis_applications AS ka ON u.id = ka.pembalap_user_id AND ka.status = 'Approved'
    WHERE 
        ka.kis_category_id = p_category_id
        AND er.points_earned > 0
    GROUP BY 
        er.pembalap_user_id, u.name, c.nama_klub
    ORDER BY 
        total_poin DESC,
        jumlah_balapan ASC;
END;

DROP PROCEDURE IF EXISTS `Proc_RegisterPembalap`;
CREATE PROCEDURE `Proc_RegisterPembalap`(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_password_hash VARCHAR(255),
    IN p_club_id BIGINT,
    IN p_tanggal_lahir DATE,
    IN p_phone_number VARCHAR(20)
)
BEGIN
    -- Deklarasikan variabel untuk menampung user_id baru
    DECLARE v_user_id BIGINT;
    
    -- Siapkan error handler: Jika ada error SQL, batalkan (ROLLBACK)
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; -- Kembalikan error-nya ke Laravel
    END;

    -- Mulai Transaksi
    START TRANSACTION;
    
    -- 1. Buat User di tabel 'users'
    INSERT INTO users (
        name, 
        email, 
        password, 
        role, 
        created_at, 
        updated_at
    ) 
    VALUES (
        p_name, 
        p_email, 
        p_password_hash, 
        'pembalap', 
        NOW(), 
        NOW()
    );
    
    -- 2. Dapatkan ID dari user yang baru saja dibuat
    SET v_user_id = LAST_INSERT_ID();
    
    -- 3. Buat Profil Pembalap di 'pembalap_profiles'
    INSERT INTO pembalap_profiles (
        user_id, 
        club_id, 
        tanggal_lahir, 
        phone_number, 
        created_at, 
        updated_at
    ) 
    VALUES (
        v_user_id, 
        p_club_id, 
        p_tanggal_lahir, 
        p_phone_number, 
        NOW(), 
        NOW()
    );
    COMMIT;
    
END;

-- TAMBAHKAN PROSEDUR BARU INI DI AKHIR FILE
-- Prosedur ini menangani pembuatan profil DAN pengajuan KIS dalam satu transaksi

DROP PROCEDURE IF EXISTS `Proc_ApplyForKIS`;

CREATE PROCEDURE `Proc_ApplyForKIS`(
    -- Data 'pembalap_profiles'
    IN p_user_id BIGINT,
    IN p_club_id BIGINT,
    IN p_tempat_lahir VARCHAR(255),
    IN p_tanggal_lahir DATE,
    IN p_no_ktp_sim VARCHAR(255),
    IN p_golongan_darah ENUM('A', 'B', 'AB', 'O', '-'),
    IN p_phone_number VARCHAR(20),
    IN p_address TEXT,
    
    -- Data 'kis_applications'
    IN p_kis_category_id BIGINT, -- <-- PARAMETER BARU
    IN p_file_surat_sehat_url VARCHAR(255),
    IN p_file_bukti_bayar_url VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    -- 1. Buat atau Update Profil Pembalap (Tetap sama)
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
    
    -- 2. Buat Pengajuan KIS Baru (DIPERBARUI DENGAN KATEGORI)
    INSERT INTO kis_applications (
        pembalap_user_id, 
        kis_category_id, -- <-- KOLOM BARU
        status, 
        file_surat_sehat_url, 
        file_bukti_bayar_url, 
        created_at, 
        updated_at
    )
    VALUES (
        p_user_id,
        p_kis_category_id, -- <-- DATA BARU
        'Pending', 
        p_file_surat_sehat_url,
        p_file_bukti_bayar_url,
        NOW(),
        NOW()
    );
    
    COMMIT;
END;

-- Prosedur ini untuk admin (Pengurus IMI) mencatat iuran secara manual

DROP PROCEDURE IF EXISTS `Proc_Admin_RecordDues`;

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
    -- Siapkan error handler
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    -- Mulai Transaksi
    START TRANSACTION;
    
    -- Langsung INSERT dengan status 'Approved'
    -- karena ini diinput oleh Pengurus yang tepercaya
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
        'Approved',  -- Langsung 'Approved'
        p_processed_by_user_id,
        NOW(), 
        NOW()
    );
    
    COMMIT;
    
END;