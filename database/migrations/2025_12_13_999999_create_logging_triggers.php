<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing triggers if they exist
        DB::unprepared('DROP TRIGGER IF EXISTS log_kis_application_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_kis_application_update');
        DB::unprepared('DROP TRIGGER IF EXISTS log_event_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_event_registration_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_event_registration_update');
        DB::unprepared('DROP TRIGGER IF EXISTS log_club_dues_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_club_dues_update');

        // =============================================
        // TRIGGER 1: Log KIS Application Insert
        // Mencatat saat ada pengajuan KIS baru
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_kis_application_insert
            AFTER INSERT ON kis_applications
            FOR EACH ROW
            BEGIN
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    "INSERT",
                    "kis_applications",
                    NEW.id,
                    CONCAT("Pengajuan KIS baru - Kategori ID: ", NEW.kis_category_id, " - Status: ", NEW.status),
                    NEW.pembalap_user_id,
                    NOW(),
                    NOW()
                );
            END
        ');

        // =============================================
        // TRIGGER 2: Log KIS Application Update
        // Mencatat saat status pengajuan KIS berubah
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_kis_application_update
            AFTER UPDATE ON kis_applications
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        "UPDATE",
                        "kis_applications",
                        NEW.id,
                        CONCAT("Status lama: ", OLD.status),
                        CONCAT("Status baru: ", NEW.status),
                        COALESCE(NEW.processed_by_user_id, NEW.pembalap_user_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // =============================================
        // TRIGGER 3: Log Event Insert
        // Mencatat saat event baru dibuat
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_event_insert
            AFTER INSERT ON events
            FOR EACH ROW
            BEGIN
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    "INSERT",
                    "events",
                    NEW.id,
                    CONCAT("Event baru dibuat: \"", NEW.event_name, "\" - Tanggal: ", NEW.event_date, " - Lokasi: ", NEW.location),
                    NEW.created_by_user_id,
                    NOW(),
                    NOW()
                );
            END
        ');

        // =============================================
        // TRIGGER 4: Log Event Registration Insert
        // Mencatat saat ada pendaftaran event baru
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_event_registration_insert
            AFTER INSERT ON event_registrations
            FOR EACH ROW
            BEGIN
                DECLARE v_event_name VARCHAR(255);
                
                -- Ambil nama event
                SELECT event_name INTO v_event_name FROM events WHERE id = NEW.event_id;
                
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    "INSERT",
                    "event_registrations",
                    NEW.id,
                    CONCAT("Pendaftaran event: \"", v_event_name, "\" - Status: ", NEW.status),
                    NEW.pembalap_user_id,
                    NOW(),
                    NOW()
                );
            END
        ');

        // =============================================
        // TRIGGER 5: Log Event Registration Update
        // Mencatat saat status pendaftaran event berubah
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_event_registration_update
            AFTER UPDATE ON event_registrations
            FOR EACH ROW
            BEGIN
                DECLARE v_event_name VARCHAR(255);
                
                IF OLD.status <> NEW.status THEN
                    -- Ambil nama event
                    SELECT event_name INTO v_event_name FROM events WHERE id = NEW.event_id;
                    
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        "UPDATE",
                        "event_registrations",
                        NEW.id,
                        CONCAT("Status lama: ", OLD.status),
                        CONCAT("Event: \"", v_event_name, "\" - Status baru: ", NEW.status),
                        COALESCE(NEW.payment_processed_by_user_id, NEW.pembalap_user_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // =============================================
        // TRIGGER 6: Log Club Dues Insert
        // Mencatat saat ada pembayaran iuran klub baru
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_club_dues_insert
            AFTER INSERT ON club_dues
            FOR EACH ROW
            BEGIN
                DECLARE v_club_name VARCHAR(255);
                
                -- Ambil nama klub
                SELECT nama_klub INTO v_club_name FROM clubs WHERE id = NEW.club_id;
                
                INSERT INTO logs (action_type, table_name, record_id, new_value, user_id, created_at, updated_at)
                VALUES (
                    "INSERT",
                    "club_dues",
                    NEW.id,
                    CONCAT("Pembayaran iuran klub: \"", v_club_name, "\" - Tahun: ", NEW.payment_year, " - Jumlah: Rp ", FORMAT(NEW.amount_paid, 0), " - Status: ", NEW.status),
                    NEW.processed_by_user_id,
                    NOW(),
                    NOW()
                );
            END
        ');

        // =============================================
        // TRIGGER 7: Log Club Dues Update
        // Mencatat saat status pembayaran iuran berubah
        // =============================================
        DB::unprepared('
            CREATE TRIGGER log_club_dues_update
            AFTER UPDATE ON club_dues
            FOR EACH ROW
            BEGIN
                DECLARE v_club_name VARCHAR(255);
                
                IF OLD.status <> NEW.status THEN
                    -- Ambil nama klub
                    SELECT nama_klub INTO v_club_name FROM clubs WHERE id = NEW.club_id;
                    
                    INSERT INTO logs (action_type, table_name, record_id, old_value, new_value, user_id, created_at, updated_at)
                    VALUES (
                        "UPDATE",
                        "club_dues",
                        NEW.id,
                        CONCAT("Status lama: ", OLD.status),
                        CONCAT("Klub: \"", v_club_name, "\" - Tahun: ", NEW.payment_year, " - Status baru: ", NEW.status),
                        NEW.processed_by_user_id,
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS log_kis_application_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_kis_application_update');
        DB::unprepared('DROP TRIGGER IF EXISTS log_event_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_event_registration_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_event_registration_update');
        DB::unprepared('DROP TRIGGER IF EXISTS log_club_dues_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS log_club_dues_update');
    }
};
