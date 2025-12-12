<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop existing triggers
        DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_insert`');

        // Trigger 1: Auto create KIS license on approval
        DB::unprepared("
            CREATE TRIGGER `auto_create_kis_license_on_approval`
            AFTER UPDATE ON `kis_applications`
            FOR EACH ROW
            BEGIN
                DECLARE v_kis_number_seq BIGINT;
                DECLARE v_kis_category_code VARCHAR(10);
                DECLARE v_kis_month_roman VARCHAR(10);
                DECLARE v_kis_number_final VARCHAR(100);
                DECLARE v_expiry_date DATE;

                IF NEW.status = 'Approved' AND OLD.status <> 'Approved' THEN
                    
                    SELECT kode_kategori INTO v_kis_category_code
                    FROM kis_categories
                    WHERE id = NEW.kis_category_id;
                    
                    SET v_kis_month_roman = 
                        CASE MONTH(NOW())
                            WHEN 1 THEN 'I' WHEN 2 THEN 'II' WHEN 3 THEN 'III'
                            WHEN 4 THEN 'IV' WHEN 5 THEN 'V' WHEN 6 THEN 'VI'
                            WHEN 7 THEN 'VII' WHEN 8 THEN 'VIII' WHEN 9 THEN 'IX'
                            WHEN 10 THEN 'X' WHEN 11 THEN 'XI' WHEN 12 THEN 'XII'
                        END;
                        
                    SET v_expiry_date = CONCAT(YEAR(NOW()), '-12-31');

                    INSERT INTO kis_licenses (
                        pembalap_user_id, application_id, kis_category_id, 
                        kis_number, issued_date, expiry_date, created_at, updated_at
                    ) VALUES (
                        NEW.pembalap_user_id, NEW.id, NEW.kis_category_id, 
                        'PENDING', DATE(NOW()), v_expiry_date, NOW(), NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        application_id = NEW.id,
                        kis_category_id = NEW.kis_category_id,
                        kis_number = 'PENDING', 
                        issued_date = DATE(NOW()),
                        expiry_date = v_expiry_date,
                        updated_at = NOW();

                    SET v_kis_number_seq = LAST_INSERT_ID();
                    
                    SET v_kis_number_final = CONCAT(
                        v_kis_number_seq, '/', v_kis_category_code, '/MDN/',
                        v_kis_month_roman, '/', YEAR(NOW())
                    );

                    UPDATE kis_licenses
                    SET kis_number = v_kis_number_final
                    WHERE id = v_kis_number_seq;

                END IF;
            END
        ");

        // Trigger 2: Log KIS application insert
        DB::unprepared("
            CREATE TRIGGER `log_kis_application_insert`
            AFTER INSERT ON `kis_applications`
            FOR EACH ROW
            BEGIN
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    'INSERT',
                    'kis_applications',
                    NEW.id,
                    'Pengajuan KIS baru (Otomatis: Status Pending)',
                    NEW.pembalap_user_id,
                    NOW(),
                    NOW()
                );
            END
        ");

        // Trigger 3: Log KIS application update
        DB::unprepared("
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
                        CONCAT('Status lama: ', OLD.status),
                        CONCAT('Status baru: ', NEW.status),
                        NEW.processed_by_user_id,
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");

        // Trigger 4: Log event insert
        DB::unprepared("
            CREATE TRIGGER `log_event_insert`
            AFTER INSERT ON `events`
            FOR EACH ROW
            BEGIN
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    'INSERT',
                    'events',
                    NEW.id,
                    CONCAT('Event baru dibuat: ', NEW.event_name),
                    NEW.created_by_user_id,
                    NOW(),
                    NOW()
                );
            END
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_insert`');
    }
};