-- HAPUS TRIGGER LAMA JIKA ADA
DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`;
DROP TRIGGER IF EXISTS `log_kis_application_insert`;
DROP TRIGGER IF EXISTS `log_kis_application_update`;
DROP TRIGGER IF EXISTS `log_event_insert`;

-- BUAT TRIGGER BARU

-- TRIGGER 1: Otomatis membuat/memperbarui KIS License
DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`;

CREATE TRIGGER `auto_create_kis_license_on_approval`
AFTER UPDATE ON `kis_applications`
FOR EACH ROW
BEGIN
    -- Deklarasi variabel
    DECLARE v_kis_number_seq BIGINT;
    DECLARE v_kis_category_code VARCHAR(10);
    DECLARE v_kis_month_roman VARCHAR(10);
    DECLARE v_kis_number_final VARCHAR(100);
    DECLARE v_expiry_date DATE;

    -- Hanya jalankan jika status BARU adalah "Approved"
    IF NEW.status = 'Approved' AND OLD.status <> 'Approved' THEN
        
        -- 1. LOGIKA NOMOR URUT (624)
        -- Kita akan gunakan 'id' dari 'kis_licenses' sebagai nomor urut
        -- (Ini adalah cara paling aman untuk jaminan keunikan)
        -- Kita perlu 'INSERT' dummy dulu untuk mendapatkan ID berikutnya
        
        -- 2. LOGIKA KATEGORI (C2)
        SELECT kode_kategori INTO v_kis_category_code
        FROM kis_categories
        WHERE id = NEW.kis_category_id;
        
        -- 3. LOGIKA BULAN (VII)
        SET v_kis_month_roman = 
            CASE MONTH(NOW())
                WHEN 1 THEN 'I'
                WHEN 2 THEN 'II'
                WHEN 3 THEN 'III'
                WHEN 4 THEN 'IV'
                WHEN 5 THEN 'V'
                WHEN 6 THEN 'VI'
                WHEN 7 THEN 'VII'
                WHEN 8 THEN 'VIII'
                WHEN 9 THEN 'IX'
                WHEN 10 THEN 'X'
                WHEN 11 THEN 'XI'
                WHEN 12 THEN 'XII'
            END;
            
        -- 4. LOGIKA KEDALUWARSA (31 Des)
        SET v_expiry_date = CONCAT(YEAR(NOW()), '-12-31');

        -- 5. Masukkan ke tabel KIS License
        -- 'kis_number' akan diisi 'PENDING' dulu
        INSERT INTO kis_licenses (
            pembalap_user_id, application_id, kis_number, issued_date, 
            expiry_date, created_at, updated_at
        )
        VALUES (
            NEW.pembalap_user_id, NEW.id, 'PENDING', DATE(NOW()), 
            v_expiry_date, NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE
            application_id = NEW.id,
            kis_number = 'PENDING', -- Set 'PENDING' dulu
            issued_date = DATE(NOW()),
            expiry_date = v_expiry_date,
            updated_at = NOW();

        -- 6. Dapatkan ID yang baru saja di-insert
        SET v_kis_number_seq = LAST_INSERT_ID();
        
        -- 7. Buat Nomor KIS Final (Contoh: 624/C2/VII)
        -- (Kita tambahkan MDN/VI/TAHUN agar mirip data asli)
        SET v_kis_number_final = CONCAT(
            v_kis_number_seq, '/', 
            v_kis_category_code, '/MDN/',
            v_kis_month_roman, '/', 
            YEAR(NOW())
        );

        -- 8. Perbarui 'kis_licenses' dengan nomor KIS final
        UPDATE kis_licenses
        SET kis_number = v_kis_number_final
        WHERE id = v_kis_number_seq;

    END IF;
END;

-- TRIGGER 2: Log Pengajuan KIS (Ini tidak berubah)
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

-- TRIGGER 3: Log Update Status KIS (Ini tidak berubah)
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

-- TRIGGER 4: Log Publikasi Event (DIPERBARUI)
-- Menggunakan created_by_user_id, bukan status 'Pending'
CREATE TRIGGER `log_event_insert`
AFTER INSERT ON `events`
FOR EACH ROW
BEGIN
    INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
    VALUES (
        'INSERT',
        'events',
        NEW.id,
        CONCAT('Event: ', NEW.event_name, ', Diterbitkan: ', IF(NEW.is_published, 'Ya', 'Tidak')),
        NEW.created_by_user_id, -- Mencatat Pengurus IMI yang menginput
        NOW(),
        NOW()
    );
END;