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
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_registration_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_registration_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_club_dues_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_club_dues_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_user_role_update`');

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

        // Trigger 5: Log event registration insert
        DB::unprepared("
            CREATE TRIGGER `log_event_registration_insert`
            AFTER INSERT ON `event_registrations`
            FOR EACH ROW
            BEGIN
                DECLARE v_event_name VARCHAR(255);
                
                SELECT event_name INTO v_event_name
                FROM events
                WHERE id = NEW.event_id;
                
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    'INSERT',
                    'event_registrations',
                    NEW.id,
                    CONCAT('Pendaftaran event: ', v_event_name, ' (Status: ', NEW.status, ')'),
                    NEW.pembalap_user_id,
                    NOW(),
                    NOW()
                );
            END
        ");

        // Trigger 6: Log event registration update
        DB::unprepared("
            CREATE TRIGGER `log_event_registration_update`
            AFTER UPDATE ON `event_registrations`
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        'UPDATE',
                        'event_registrations',
                        NEW.id,
                        CONCAT('Status lama: ', OLD.status),
                        CONCAT('Status baru: ', NEW.status),
                        COALESCE(NEW.payment_processed_by_user_id, NEW.pembalap_user_id),
                        NOW(),
                        NOW()
                    );
                END IF;
                
                IF (OLD.result_position IS NULL AND NEW.result_position IS NOT NULL) 
                    OR (OLD.result_position <> NEW.result_position) 
                    OR (OLD.points_earned <> NEW.points_earned) THEN
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        'UPDATE',
                        'event_registrations',
                        NEW.id,
                        CONCAT('Hasil lama - Posisi: ', IFNULL(OLD.result_position, 'Belum ada'), ', Poin: ', OLD.points_earned),
                        CONCAT('Hasil baru - Posisi: ', NEW.result_position, ', Poin: ', NEW.points_earned, ', Status: ', IFNULL(NEW.result_status, 'Finished')),
                        NEW.payment_processed_by_user_id,
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");

        // Trigger 7: Log club dues insert
        DB::unprepared("
            CREATE TRIGGER `log_club_dues_insert`
            AFTER INSERT ON `club_dues`
            FOR EACH ROW
            BEGIN
                DECLARE v_club_name VARCHAR(255);
                
                SELECT nama_klub INTO v_club_name
                FROM clubs
                WHERE id = NEW.club_id;
                
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    'INSERT',
                    'club_dues',
                    NEW.id,
                    CONCAT('Iuran klub: ', v_club_name, ' tahun ', NEW.payment_year, ' - Rp', FORMAT(NEW.amount_paid, 0)),
                    NEW.processed_by_user_id,
                    NOW(),
                    NOW()
                );
            END
        ");

        // Trigger 8: Log club dues update
        DB::unprepared("
            CREATE TRIGGER `log_club_dues_update`
            AFTER UPDATE ON `club_dues`
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        'UPDATE',
                        'club_dues',
                        NEW.id,
                        CONCAT('Status lama: ', OLD.status),
                        CONCAT('Status baru: ', NEW.status, IF(NEW.rejection_reason IS NOT NULL, CONCAT(' - Alasan: ', NEW.rejection_reason), '')),
                        NEW.processed_by_user_id,
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");

        // Trigger 9: Log user role update (is_active akan di-handle di migration terpisah)
        DB::unprepared("
            CREATE TRIGGER `log_user_role_update`
            AFTER UPDATE ON `users`
            FOR EACH ROW
            BEGIN
                -- Log perubahan role saja (is_active belum ada di tabel users saat ini)
                IF OLD.role <> NEW.role THEN
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        'UPDATE',
                        'users',
                        NEW.id,
                        CONCAT('Role lama: ', OLD.role),
                        CONCAT('Role baru: ', NEW.role, ' untuk user: ', NEW.email),
                        NEW.id,
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_registration_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_registration_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_club_dues_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_club_dues_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_user_role_update`');
    }
};