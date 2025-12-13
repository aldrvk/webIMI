-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 13, 2025 at 05:22 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `imi`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_Admin_RecordDues` (IN `p_club_id` BIGINT, IN `p_payment_year` YEAR, IN `p_payment_date` DATE, IN `p_amount_paid` DECIMAL(10,2), IN `p_payment_proof_url` VARCHAR(255), IN `p_notes` TEXT, IN `p_processed_by_user_id` BIGINT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    INSERT INTO club_dues (
        club_id, payment_year, payment_date, amount_paid, payment_proof_url, 
        notes, status, processed_by_user_id, created_at, updated_at
    )
    VALUES (
        p_club_id, p_payment_year, p_payment_date, p_amount_paid, p_payment_proof_url,
        p_notes, 'Approved', p_processed_by_user_id, NOW(), NOW()
    );
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_ApplyForKIS` (IN `p_user_id` BIGINT, IN `p_club_id` BIGINT, IN `p_tempat_lahir` VARCHAR(255), IN `p_tanggal_lahir` DATE, IN `p_no_ktp_sim` VARCHAR(255), IN `p_golongan_darah` ENUM('A','B','AB','O','-'), IN `p_phone_number` VARCHAR(20), IN `p_address` TEXT, IN `p_kis_category_id` BIGINT, IN `p_file_surat_sehat_url` VARCHAR(255), IN `p_file_bukti_bayar_url` VARCHAR(255), IN `p_file_pas_foto_url` VARCHAR(255), IN `p_file_ktp_url` VARCHAR(255), IN `p_file_kk_url` VARCHAR(255), IN `p_file_surat_izin_url` VARCHAR(255))   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    -- 1. Update Profil
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
    
    -- 2. Insert Aplikasi KIS
    INSERT INTO kis_applications (
        pembalap_user_id, 
        kis_category_id,
        file_surat_sehat_url, 
        file_bukti_bayar_url, 
        file_pas_foto_url,
        
        file_ktp_url,
        file_kk_url,
        file_surat_izin_url,
        
        status, 
        created_at, 
        updated_at
    )
    VALUES (
        p_user_id,
        p_kis_category_id,
        p_file_surat_sehat_url,
        p_file_bukti_bayar_url,
        p_file_pas_foto_url,

        p_file_ktp_url,
        p_file_kk_url,
        p_file_surat_izin_url,
        
        'Pending', 
        NOW(),
        NOW()
    );
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_GetLeaderboard` (IN `p_category_id` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_RegisterPembalap` (IN `p_user_id` BIGINT, IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_club_id` BIGINT, IN `p_tempat_lahir` VARCHAR(255), IN `p_tanggal_lahir` DATE, IN `p_no_ktp_sim` VARCHAR(255), IN `p_golongan_darah` ENUM('A','B','AB','O','-'), IN `p_phone_number` VARCHAR(20), IN `p_address` TEXT)   BEGIN
    DECLARE v_user_id BIGINT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    -- 1. Insert atau Update User (jika user_id diberikan, update; jika tidak, insert)
    IF p_user_id IS NULL OR p_user_id = 0 THEN
        -- Insert user baru
        INSERT INTO users (name, email, password, role, created_at, updated_at)
        VALUES (p_name, p_email, p_password, 'Pembalap', NOW(), NOW());
        SET v_user_id = LAST_INSERT_ID();
    ELSE
        -- Update user yang sudah ada
        UPDATE users 
        SET name = p_name,
            email = p_email,
            updated_at = NOW()
        WHERE id = p_user_id;
        SET v_user_id = p_user_id;
    END IF;
    
    -- 2. Insert atau Update Pembalap Profile
    INSERT INTO pembalap_profiles (
        user_id, club_id, tempat_lahir, tanggal_lahir, no_ktp_sim, 
        golongan_darah, phone_number, address, created_at, updated_at
    )
    VALUES (
        v_user_id, p_club_id, p_tempat_lahir, p_tanggal_lahir, p_no_ktp_sim,
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
    
    COMMIT;
    
    -- Return user_id yang baru dibuat atau diupdate
    SELECT v_user_id AS user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_RegisterPembalapToEvent` (IN `p_event_id` BIGINT, IN `p_pembalap_user_id` BIGINT, IN `p_kis_category_id` BIGINT, IN `p_payment_proof_url` VARCHAR(255))   BEGIN
    DECLARE v_event_fee DECIMAL(10, 2);
    DECLARE v_initial_status VARCHAR(50);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;

    START TRANSACTION;
    
    -- 1. Cek biaya event
    SELECT biaya_pendaftaran INTO v_event_fee
    FROM events
    WHERE id = p_event_id;
    
    -- 2. Tentukan status awal berdasarkan biaya
    IF v_event_fee > 0 THEN
        IF p_payment_proof_url IS NOT NULL AND p_payment_proof_url != '' THEN
            SET v_initial_status = 'Pending Confirmation';
        ELSE
            SET v_initial_status = 'Pending Payment';
        END IF;
    ELSE
        SET v_initial_status = 'Confirmed';
    END IF;
    
    -- 3. Insert atau Update registrasi
    INSERT INTO event_registrations (
        event_id, pembalap_user_id, kis_category_id, 
        payment_proof_url, status, points_earned, 
        created_at, updated_at
    )
    VALUES (
        p_event_id, p_pembalap_user_id, p_kis_category_id,
        p_payment_proof_url, v_initial_status, 0,
        NOW(), NOW()
    )
    ON DUPLICATE KEY UPDATE
        payment_proof_url = COALESCE(p_payment_proof_url, payment_proof_url),
        status = v_initial_status,
        updated_at = NOW();
    
    COMMIT;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `Func_GetPembalapTotalPoints` (`p_pembalap_user_id` BIGINT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE total_points INT;
    SELECT IFNULL(SUM(points_earned), 0) INTO total_points
    FROM event_registrations WHERE pembalap_user_id = p_pembalap_user_id;
    RETURN total_points;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Func_Get_Event_Status` (`p_event_date` DATE, `p_registration_deadline` DATE, `p_is_published` BOOLEAN) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
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

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_klub` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `nama_ketua` varchar(255) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `email_klub` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `nama_klub`, `alamat`, `nama_ketua`, `hp`, `email_klub`, `created_at`, `updated_at`) VALUES
(1, 'IMI Sumut Official', 'Jl. Taruma No. 52 Medan', 'Harun Nasution', '081234567890', 'admin@imi-sumut.or.id', '2025-12-13 16:31:10', '2025-12-13 16:31:10'),
(2, 'SPEED\'ER MOTORSPORT', 'Jl. Jorlang Hatoran No. 85 A, Siantar', 'Hasanuddin Lubis', '081234567891', 'speeder@example.com', '2025-12-13 16:31:10', '2025-12-13 16:31:10'),
(3, 'Kitakita Motorsport', 'Medan', 'Adek Hidayat', '081234567892', 'kitakita@example.com', '2025-12-13 16:31:10', '2025-12-13 16:31:10'),
(4, 'Racing Club Medan', 'Jl. Gatot Subroto No. 123, Medan', 'Budi Santoso', '081234567893', 'racing.medan@example.com', '2024-11-18 17:00:00', '2025-12-13 16:31:12'),
(5, 'Speedster Motorsport', 'Jl. Sisingamangaraja No. 45, Medan', 'Andi Wijaya', '081234567894', 'speedster@example.com', '2023-12-31 17:00:00', '2025-12-13 16:31:12'),
(6, 'Thunder Racing Team', 'Jl. Iskandar Muda No. 78, Medan', 'Dedi Kurniawan', '081234567895', 'thunder@example.com', '2024-10-19 17:00:00', '2025-12-13 16:31:12'),
(7, 'Velocity Club Sumut', 'Jl. Dr. Mansyur No. 90, Medan', 'Eko Prasetyo', '081234567896', 'velocity@example.com', '2024-05-08 17:00:00', '2025-12-13 16:31:12'),
(8, 'Nitro Racing Team', 'Jl. Sudirman No. 112, Pematang Siantar', 'Faisal Rahman', '081234567897', 'nitro@example.com', '2024-01-07 17:00:00', '2025-12-13 16:31:12'),
(9, 'Apex Motorsport', 'Jl. Jend. Ahmad Yani No. 67, Binjai', 'Gunawan Tan', '081234567898', 'apex@example.com', '2024-06-19 17:00:00', '2025-12-13 16:31:12'),
(10, 'Turbo Racing Club', 'Jl. Kapten Maulana Lubis No. 34, Medan', 'Hendra Lim', '081234567899', 'turbo@example.com', '2024-03-11 17:00:00', '2025-12-13 16:31:12'),
(11, 'Phoenix Motorsport', 'Jl. Setia Budi No. 156, Medan', 'Indra Gunawan', '081234567800', 'phoenix@example.com', '2024-09-04 17:00:00', '2025-12-13 16:31:12'),
(12, 'Dragon Racing Team', 'Jl. Karya No. 89, Tebing Tinggi', 'Joko Widodo', '081234567801', 'dragon@example.com', '2024-04-23 17:00:00', '2025-12-13 16:31:12'),
(13, 'Falcon Motorsport', 'Jl. Veteran No. 45, Medan', 'Kurniawan Setiawan', '081234567802', 'falcon@example.com', '2024-02-19 17:00:00', '2025-12-13 16:31:12'),
(14, 'Viper Racing Club', 'Jl. Brigjend Katamso No. 78, Medan', 'Luhut Pangaribuan', '081234567803', 'viper@example.com', '2024-10-05 17:00:00', '2025-12-13 16:31:12'),
(15, 'Eagle Motorsport Team', 'Jl. Imam Bonjol No. 23, Medan', 'Mario Situmorang', '081234567804', 'eagle@example.com', '2024-02-21 17:00:00', '2025-12-13 16:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `club_dues`
--

CREATE TABLE `club_dues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `club_id` bigint(20) UNSIGNED NOT NULL,
  `payment_year` year(4) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_proof_url` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `rejection_reason` text DEFAULT NULL,
  `processed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `club_dues`
--

INSERT INTO `club_dues` (`id`, `club_id`, `payment_year`, `payment_date`, `amount_paid`, `payment_proof_url`, `status`, `rejection_reason`, `processed_by_user_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 9, 2024, '2024-10-27', '5000000.00', 'payment-proofs/klub-9-2024.jpg', 'Approved', NULL, 3, 'Pembayaran iuran tahun 2024', '2024-10-26 17:00:00', '2025-12-13 16:31:31'),
(2, 12, 2024, '2024-08-01', '5000000.00', 'payment-proofs/klub-12-2024.jpg', 'Approved', NULL, 1, 'Pembayaran iuran tahun 2024', '2024-07-31 17:00:00', '2025-12-13 16:31:31'),
(3, 15, 2024, '2024-05-18', '5000000.00', 'payment-proofs/pending-15-2024.jpg', 'Pending', NULL, NULL, 'Menunggu verifikasi pembayaran', '2024-05-17 17:00:00', '2025-12-13 16:31:31'),
(4, 13, 2024, '2024-04-10', '5000000.00', 'payment-proofs/pending-13-2024.jpg', 'Pending', NULL, NULL, 'Menunggu verifikasi pembayaran', '2024-04-09 17:00:00', '2025-12-13 16:31:31'),
(5, 1, 2024, '2024-11-28', '5000000.00', 'payment-proofs/klub-1-2024.jpg', 'Approved', NULL, 1, 'Pembayaran iuran tahun 2024', '2024-11-27 17:00:00', '2025-12-13 16:31:31'),
(6, 3, 2024, '2024-06-27', '5000000.00', 'payment-proofs/pending-3-2024.jpg', 'Pending', NULL, NULL, 'Menunggu verifikasi pembayaran', '2024-06-26 17:00:00', '2025-12-13 16:31:31'),
(7, 8, 2024, '2024-06-19', '5000000.00', 'payment-proofs/klub-8-2024.jpg', 'Approved', NULL, 2, 'Pembayaran iuran tahun 2024', '2024-06-18 17:00:00', '2025-12-13 16:31:31'),
(8, 11, 2024, '2024-08-25', '5000000.00', 'payment-proofs/klub-11-2024.jpg', 'Approved', NULL, 3, 'Pembayaran iuran tahun 2024', '2024-08-24 17:00:00', '2025-12-13 16:31:31'),
(9, 4, 2024, '2024-12-24', '5000000.00', 'payment-proofs/klub-4-2024.jpg', 'Approved', NULL, 2, 'Pembayaran iuran tahun 2024', '2024-12-23 17:00:00', '2025-12-13 16:31:31'),
(10, 2, 2024, '2024-05-24', '5000000.00', 'payment-proofs/klub-2-2024.jpg', 'Approved', NULL, 1, 'Pembayaran iuran tahun 2024', '2024-05-23 17:00:00', '2025-12-13 16:31:31'),
(11, 5, 2024, '2024-08-06', '5000000.00', 'payment-proofs/klub-5-2024.jpg', 'Approved', NULL, 2, 'Pembayaran iuran tahun 2024', '2024-08-05 17:00:00', '2025-12-13 16:31:31'),
(12, 6, 2024, '2024-10-23', '5000000.00', 'payment-proofs/klub-6-2024.jpg', 'Approved', NULL, 1, 'Pembayaran iuran tahun 2024', '2024-10-22 17:00:00', '2025-12-13 16:31:31'),
(13, 10, 2024, '2024-11-27', '5000000.00', 'payment-proofs/klub-10-2024.jpg', 'Approved', NULL, 3, 'Pembayaran iuran tahun 2024', '2024-11-26 17:00:00', '2025-12-13 16:31:31'),
(14, 7, 2024, '2024-03-28', '5000000.00', 'payment-proofs/klub-7-2024.jpg', 'Approved', NULL, 1, 'Pembayaran iuran tahun 2024', '2024-03-27 17:00:00', '2025-12-13 16:31:31'),
(15, 14, 2024, '2024-12-27', '5000000.00', 'payment-proofs/klub-14-2024.jpg', 'Approved', NULL, 3, 'Pembayaran iuran tahun 2024', '2024-12-26 17:00:00', '2025-12-13 16:31:31');

--
-- Triggers `club_dues`
--
DELIMITER $$
CREATE TRIGGER `log_club_dues_insert` AFTER INSERT ON `club_dues` FOR EACH ROW BEGIN
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
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_club_dues_update` AFTER UPDATE ON `club_dues` FOR EACH ROW BEGIN
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `biaya_pendaftaran` decimal(13,2) NOT NULL DEFAULT 0.00,
  `bank_account_info` text DEFAULT NULL,
  `kontak_panitia` varchar(255) DEFAULT NULL,
  `url_regulasi` varchar(255) DEFAULT NULL,
  `image_banner_url` varchar(255) DEFAULT NULL,
  `proposing_club_id` bigint(20) UNSIGNED NOT NULL,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `event_date`, `registration_deadline`, `location`, `description`, `biaya_pendaftaran`, `bank_account_info`, `kontak_panitia`, `url_regulasi`, `image_banner_url`, `proposing_club_id`, `created_by_user_id`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Kejuaraan Tes (SELESAI)', '2025-12-06', '2025-12-04 23:59:59', 'Sirkuit Pancing', NULL, '100000.00', 'BCA 12345 a/n Klub Speeder', NULL, NULL, NULL, 2, 2, 1, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(2, 'Kejuaraan Tes (AKAN DATANG)', '2026-01-13', '2026-01-03 23:59:59', 'Sirkuit Karting IMI', NULL, '250000.00', 'Mandiri 98765 a/n Panitia Speeder', NULL, NULL, 'event-posters/dummy-poster.jpg', 2, 2, 1, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(3, 'New Year Cup 2024', '2024-01-04', '2024-01-01 23:59:59', 'Sirkuit Medan', 'Event balap New Year Cup 2024 - Historical Data 2024', '800000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 2, 1, '2023-12-31 17:00:00', '2025-12-13 16:31:31'),
(4, 'Lunar Racing Championship', '2024-01-10', '2024-01-07 23:59:59', 'Sirkuit Binjai', 'Event balap Lunar Racing Championship - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 7, 3, 1, '2024-01-05 17:00:00', '2025-12-13 16:31:31'),
(5, 'Independence Day Race', '2024-01-04', '2024-01-01 23:59:59', 'Sirkuit Sentul', 'Event balap Independence Day Race - Historical Data 2024', '1500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 1, 4, 1, '2024-01-04 17:00:00', '2025-12-13 16:31:31'),
(6, 'Spring Championship Round 1', '2024-01-07', '2024-01-04 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Spring Championship Round 1 - Historical Data 2024', '1600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 3, 2, 1, '2023-12-31 17:00:00', '2025-12-13 16:31:31'),
(7, 'Spring Championship Round 2', '2024-01-28', '2024-01-25 23:59:59', 'Sirkuit Binjai', 'Event balap Spring Championship Round 2 - Historical Data 2024', '1100000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 1, 1, '2024-01-26 17:00:00', '2025-12-13 16:31:31'),
(8, 'Spring Championship Round 3', '2024-02-05', '2024-02-02 23:59:59', 'Sirkuit Sentul', 'Event balap Spring Championship Round 3 - Historical Data 2024', '700000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 1, 3, 1, '2024-01-10 17:00:00', '2025-12-13 16:31:31'),
(9, 'Kejurda Medan Q1 Round 1', '2024-02-26', '2024-02-23 23:59:59', 'Sirkuit Binjai', 'Event balap Kejurda Medan Q1 Round 1 - Historical Data 2024', '1100000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 14, 1, 1, '2024-01-07 17:00:00', '2025-12-13 16:31:31'),
(10, 'Kejurda Medan Q1 Round 2', '2024-02-08', '2024-02-05 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Kejurda Medan Q1 Round 2 - Historical Data 2024', '1100000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 3, 4, 1, '2024-01-17 17:00:00', '2025-12-13 16:31:31'),
(11, 'Kejurda Medan Q1 Round 3', '2024-02-23', '2024-02-20 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Kejurda Medan Q1 Round 3 - Historical Data 2024', '1300000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 4, 1, '2024-01-17 17:00:00', '2025-12-13 16:31:31'),
(12, 'Open Track Day January', '2024-02-15', '2024-02-12 23:59:59', 'Sirkuit Medan', 'Event balap Open Track Day January - Historical Data 2024', '500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 9, 1, 1, '2024-01-26 17:00:00', '2025-12-13 16:31:31'),
(13, 'Valentine Special Race', '2024-03-18', '2024-03-15 23:59:59', 'Sirkuit Sentul', 'Event balap Valentine Special Race - Historical Data 2024', '1600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 7, 4, 1, '2024-02-24 17:00:00', '2025-12-13 16:31:31'),
(14, 'March Madness Race', '2024-03-12', '2024-03-09 23:59:59', 'Sirkuit Sentul', 'Event balap March Madness Race - Historical Data 2024', '1600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 9, 3, 1, '2024-02-23 17:00:00', '2025-12-13 16:31:31'),
(15, 'Pematang Siantar Cup 1', '2024-03-04', '2024-03-01 23:59:59', 'Sirkuit Sentul', 'Event balap Pematang Siantar Cup 1 - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 11, 1, 1, '2024-02-01 17:00:00', '2025-12-13 16:31:31'),
(16, 'Binjai Racing Series 1', '2024-03-23', '2024-03-20 23:59:59', 'Sirkuit Medan', 'Event balap Binjai Racing Series 1 - Historical Data 2024', '600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 10, 2, 1, '2024-02-07 17:00:00', '2025-12-13 16:31:31'),
(17, 'Tebing Tinggi Open 1', '2024-03-18', '2024-03-15 23:59:59', 'Sirkuit Medan', 'Event balap Tebing Tinggi Open 1 - Historical Data 2024', '600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 13, 2, 1, '2024-02-01 17:00:00', '2025-12-13 16:31:31'),
(18, 'Summer Heat Championship R1', '2024-04-28', '2024-04-25 23:59:59', 'Sirkuit Binjai', 'Event balap Summer Heat Championship R1 - Historical Data 2024', '1100000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 2, 1, '2024-03-18 17:00:00', '2025-12-13 16:31:31'),
(19, 'Summer Heat Championship R2', '2024-04-03', '2024-04-01 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Summer Heat Championship R2 - Historical Data 2024', '1000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 7, 3, 1, '2024-03-04 17:00:00', '2025-12-13 16:31:31'),
(20, 'Summer Heat Championship R3', '2024-04-27', '2024-04-24 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Summer Heat Championship R3 - Historical Data 2024', '1000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 6, 4, 1, '2024-03-14 17:00:00', '2025-12-13 16:31:31'),
(21, 'Kejurprov Sumut Series 1', '2024-04-01', '2024-04-01 23:59:59', 'Sirkuit Binjai', 'Event balap Kejurprov Sumut Series 1 - Historical Data 2024', '1500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 8, 1, 1, '2024-03-27 17:00:00', '2025-12-13 16:31:31'),
(22, 'Kejurprov Sumut Series 2', '2024-04-12', '2024-04-09 23:59:59', 'Sirkuit Binjai', 'Event balap Kejurprov Sumut Series 2 - Historical Data 2024', '1300000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 1, 1, '2024-03-08 17:00:00', '2025-12-13 16:31:31'),
(23, 'Kejurprov Sumut Series 3', '2024-05-18', '2024-05-15 23:59:59', 'Sirkuit Sentul', 'Event balap Kejurprov Sumut Series 3 - Historical Data 2024', '1100000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 8, 1, 1, '2024-04-19 17:00:00', '2025-12-13 16:31:31'),
(24, 'Ramadan Cup 2024', '2024-05-17', '2024-05-14 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Ramadan Cup 2024 - Historical Data 2024', '1000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 4, 3, 1, '2024-04-27 17:00:00', '2025-12-13 16:31:31'),
(25, 'Eid Mubarak Race', '2024-05-25', '2024-05-22 23:59:59', 'Sirkuit Sentul', 'Event balap Eid Mubarak Race - Historical Data 2024', '2000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 9, 4, 1, '2024-04-21 17:00:00', '2025-12-13 16:31:31'),
(26, 'Independence Preparation Cup', '2024-05-24', '2024-05-21 23:59:59', 'Sirkuit Sentul', 'Event balap Independence Preparation Cup - Historical Data 2024', '600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 6, 1, 1, '2024-04-21 17:00:00', '2025-12-13 16:31:31'),
(27, 'Mid Year Championship', '2024-05-22', '2024-05-19 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Mid Year Championship - Historical Data 2024', '1300000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 14, 4, 1, '2024-04-12 17:00:00', '2025-12-13 16:31:31'),
(28, 'Open Track Day April', '2024-06-16', '2024-06-13 23:59:59', 'Sirkuit Medan', 'Event balap Open Track Day April - Historical Data 2024', '800000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 6, 2, 1, '2024-05-26 17:00:00', '2025-12-13 16:31:31'),
(29, 'May Day Racing', '2024-06-03', '2024-06-01 23:59:59', 'Sirkuit Binjai', 'Event balap May Day Racing - Historical Data 2024', '1400000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 1, 1, '2024-05-19 17:00:00', '2025-12-13 16:31:31'),
(30, 'Pematang Siantar Cup 2', '2024-06-04', '2024-06-01 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Pematang Siantar Cup 2 - Historical Data 2024', '500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 3, 3, 1, '2024-05-16 17:00:00', '2025-12-13 16:31:31'),
(31, 'Binjai Racing Series 2', '2024-06-16', '2024-06-13 23:59:59', 'Sirkuit Binjai', 'Event balap Binjai Racing Series 2 - Historical Data 2024', '1900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 4, 1, '2024-05-08 17:00:00', '2025-12-13 16:31:31'),
(32, 'June Thunder Race', '2024-06-16', '2024-06-13 23:59:59', 'Sirkuit Deli Serdang', 'Event balap June Thunder Race - Historical Data 2024', '1800000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 3, 2, 1, '2024-05-04 17:00:00', '2025-12-13 16:31:31'),
(33, 'Independence Day Special', '2024-07-13', '2024-07-10 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Independence Day Special - Historical Data 2024', '700000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 11, 3, 1, '2024-06-21 17:00:00', '2025-12-13 16:31:31'),
(34, 'August 17 Freedom Race', '2024-07-17', '2024-07-14 23:59:59', 'Sirkuit Sentul', 'Event balap August 17 Freedom Race - Historical Data 2024', '1800000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 13, 2, 1, '2024-06-22 17:00:00', '2025-12-13 16:31:31'),
(35, 'Merdeka Cup 2024', '2024-07-23', '2024-07-20 23:59:59', 'Sirkuit Sentul', 'Event balap Merdeka Cup 2024 - Historical Data 2024', '700000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 6, 1, 1, '2024-06-26 17:00:00', '2025-12-13 16:31:31'),
(36, 'Autumn Championship R1', '2024-07-26', '2024-07-23 23:59:59', 'Sirkuit Medan', 'Event balap Autumn Championship R1 - Historical Data 2024', '1600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 10, 2, 1, '2024-06-17 17:00:00', '2025-12-13 16:31:31'),
(37, 'Autumn Championship R2', '2024-07-10', '2024-07-07 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Autumn Championship R2 - Historical Data 2024', '2000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 4, 1, '2024-06-07 17:00:00', '2025-12-13 16:31:31'),
(38, 'Autumn Championship R3', '2024-08-04', '2024-08-01 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Autumn Championship R3 - Historical Data 2024', '1100000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 1, 3, 1, '2024-07-18 17:00:00', '2025-12-13 16:31:31'),
(39, 'Grasstrack Medan Cup 1', '2024-08-20', '2024-08-17 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Grasstrack Medan Cup 1 - Historical Data 2024', '700000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 11, 2, 1, '2024-07-17 17:00:00', '2025-12-13 16:31:31'),
(40, 'Grasstrack Medan Cup 2', '2024-08-16', '2024-08-13 23:59:59', 'Sirkuit Medan', 'Event balap Grasstrack Medan Cup 2 - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 13, 3, 1, '2024-07-22 17:00:00', '2025-12-13 16:31:31'),
(41, 'Grasstrack Medan Cup 3', '2024-08-15', '2024-08-12 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Grasstrack Medan Cup 3 - Historical Data 2024', '1900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 1, 1, '2024-07-12 17:00:00', '2025-12-13 16:31:31'),
(42, 'National Championship Q3 R1', '2024-08-03', '2024-08-01 23:59:59', 'Sirkuit Binjai', 'Event balap National Championship Q3 R1 - Historical Data 2024', '500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 1, 1, '2024-07-24 17:00:00', '2025-12-13 16:31:31'),
(43, 'Open Track Day July', '2024-09-16', '2024-09-13 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Open Track Day July - Historical Data 2024', '1300000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 1, 2, 1, '2024-08-05 17:00:00', '2025-12-13 16:31:31'),
(44, 'September Speed Fest', '2024-09-26', '2024-09-23 23:59:59', 'Sirkuit Medan', 'Event balap September Speed Fest - Historical Data 2024', '500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 2, 1, '2024-08-10 17:00:00', '2025-12-13 16:31:31'),
(45, 'Tebing Tinggi Open 2', '2024-09-23', '2024-09-20 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Tebing Tinggi Open 2 - Historical Data 2024', '1200000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 2, 1, '2024-08-13 17:00:00', '2025-12-13 16:31:31'),
(46, 'Binjai Racing Series 3', '2024-09-08', '2024-09-05 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Binjai Racing Series 3 - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 15, 2, 1, '2024-08-25 17:00:00', '2025-12-13 16:31:31'),
(47, 'Sumut Grand Prix Round 1', '2024-09-07', '2024-09-04 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Sumut Grand Prix Round 1 - Historical Data 2024', '1600000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 10, 2, 1, '2024-08-25 17:00:00', '2025-12-13 16:31:31'),
(48, 'Year End Championship R1', '2024-10-23', '2024-10-20 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Year End Championship R1 - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 14, 1, 1, '2024-09-25 17:00:00', '2025-12-13 16:31:31'),
(49, 'Year End Championship R2', '2024-10-16', '2024-10-13 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Year End Championship R2 - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 13, 4, 1, '2024-09-03 17:00:00', '2025-12-13 16:31:31'),
(50, 'Year End Championship R3', '2024-10-12', '2024-10-09 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Year End Championship R3 - Historical Data 2024', '1300000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 13, 2, 1, '2024-09-24 17:00:00', '2025-12-13 16:31:31'),
(51, 'Grand Final Preparation', '2024-10-19', '2024-10-16 23:59:59', 'Sirkuit Pematang Siantar', 'Event balap Grand Final Preparation - Historical Data 2024', '700000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 4, 1, 1, '2024-09-18 17:00:00', '2025-12-13 16:31:31'),
(52, 'Semi Final Championship', '2024-10-10', '2024-10-07 23:59:59', 'Sirkuit Deli Serdang', 'Event balap Semi Final Championship - Historical Data 2024', '1500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 15, 2, 1, '2024-09-09 17:00:00', '2025-12-13 16:31:31'),
(53, 'Grand Final Championship 2024', '2024-11-04', '2024-11-01 23:59:59', 'Sirkuit Binjai', 'Event balap Grand Final Championship 2024 - Historical Data 2024', '900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 12, 3, 1, '2024-10-23 17:00:00', '2025-12-13 16:31:31'),
(54, 'Christmas Special Race', '2024-11-22', '2024-11-19 23:59:59', 'Sirkuit Binjai', 'Event balap Christmas Special Race - Historical Data 2024', '1200000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 13, 3, 1, '2024-10-07 17:00:00', '2025-12-13 16:31:31'),
(55, 'New Year Preparation Cup', '2024-11-24', '2024-11-21 23:59:59', 'Sirkuit Medan', 'Event balap New Year Preparation Cup - Historical Data 2024', '1700000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 2, 4, 1, '2024-10-27 17:00:00', '2025-12-13 16:31:31'),
(56, 'Endurance Race 2024', '2024-11-05', '2024-11-02 23:59:59', 'Sirkuit Medan', 'Event balap Endurance Race 2024 - Historical Data 2024', '2000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 10, 1, 1, '2024-10-15 17:00:00', '2025-12-13 16:31:31'),
(57, 'October Fast Race', '2024-11-21', '2024-11-18 23:59:59', 'Sirkuit Deli Serdang', 'Event balap October Fast Race - Historical Data 2024', '2000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 8, 1, 1, '2024-10-21 17:00:00', '2025-12-13 16:31:31'),
(58, 'November Thunder', '2024-12-20', '2024-12-17 23:59:59', 'Sirkuit Medan', 'Event balap November Thunder - Historical Data 2024', '2000000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 9, 3, 1, '2024-11-07 17:00:00', '2025-12-13 16:31:31'),
(59, 'December Speedway', '2024-12-22', '2024-12-19 23:59:59', 'Sirkuit Binjai', 'Event balap December Speedway - Historical Data 2024', '1900000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 1, 1, 1, '2024-11-03 17:00:00', '2025-12-13 16:31:31'),
(60, 'Pematang Siantar Cup 3', '2024-12-03', '2024-12-01 23:59:59', 'Sirkuit Medan', 'Event balap Pematang Siantar Cup 3 - Historical Data 2024', '1800000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 5, 3, 1, '2024-11-15 17:00:00', '2025-12-13 16:31:31'),
(61, 'Sumut Grand Prix Round 2', '2024-12-24', '2024-12-21 23:59:59', 'Sirkuit Medan', 'Event balap Sumut Grand Prix Round 2 - Historical Data 2024', '1500000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 5, 4, 1, '2024-11-24 17:00:00', '2025-12-13 16:31:31'),
(62, 'IMI Sumut Closing Race', '2024-12-05', '2024-12-02 23:59:59', 'Sirkuit Deli Serdang', 'Event balap IMI Sumut Closing Race - Historical Data 2024', '1300000.00', 'BCA 1234567890 a/n IMI Sumut', '081234567890', NULL, NULL, 14, 3, 1, '2024-11-14 17:00:00', '2025-12-13 16:31:31');

--
-- Triggers `events`
--
DELIMITER $$
CREATE TRIGGER `log_event_insert` AFTER INSERT ON `events` FOR EACH ROW BEGIN
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `event_kis_category`
--

CREATE TABLE `event_kis_category` (
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `kis_category_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_kis_category`
--

INSERT INTO `event_kis_category` (`event_id`, `kis_category_id`) VALUES
(1, 1),
(2, 1),
(2, 8);

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `pembalap_user_id` bigint(20) UNSIGNED NOT NULL,
  `kis_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `result_position` int(11) DEFAULT NULL,
  `result_status` enum('Finished','DNF','DSQ') DEFAULT NULL,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `status` enum('Pending Payment','Pending Confirmation','Confirmed','Rejected','Cancelled') NOT NULL DEFAULT 'Pending Payment',
  `payment_proof_url` varchar(255) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `payment_processed_at` timestamp NULL DEFAULT NULL,
  `payment_processed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `pembalap_user_id`, `kis_category_id`, `result_position`, `result_status`, `points_earned`, `status`, `payment_proof_url`, `admin_note`, `payment_processed_at`, `payment_processed_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-lunas.jpg', NULL, '2025-12-13 16:31:12', 4, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(2, 2, 6, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-pending.jpg', NULL, NULL, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(3, 2, 7, 8, NULL, NULL, 0, 'Rejected', 'payment-proofs/dummy-ditolak.jpg', 'Bukti transfer tidak jelas/buram. Harap upload ulang.', '2025-12-13 16:31:12', 4, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(4, 3, 13, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-3.jpg', NULL, NULL, NULL, '2024-08-11 17:00:00', '2025-12-13 16:31:32'),
(5, 3, 16, 4, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-16-3.jpg', NULL, NULL, NULL, '2024-09-22 17:00:00', '2025-12-13 16:31:32'),
(6, 3, 15, 12, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-15-3.jpg', NULL, NULL, NULL, '2024-11-17 17:00:00', '2025-12-13 16:31:32'),
(7, 3, 12, 1, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-12-3.jpg', NULL, NULL, NULL, '2024-08-22 17:00:00', '2025-12-13 16:31:32'),
(8, 3, 8, 6, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-8-3.jpg', NULL, NULL, NULL, '2024-12-07 17:00:00', '2025-12-13 16:31:32'),
(9, 3, 10, 5, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-10-3.jpg', NULL, NULL, NULL, '2024-05-08 17:00:00', '2025-12-13 16:31:32'),
(10, 3, 17, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-3.jpg', NULL, NULL, NULL, '2024-03-05 17:00:00', '2025-12-13 16:31:32'),
(11, 3, 19, 8, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-19-3.jpg', NULL, NULL, NULL, '2024-06-26 17:00:00', '2025-12-13 16:31:32'),
(12, 3, 9, 4, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-9-3.jpg', NULL, NULL, NULL, '2024-08-21 17:00:00', '2025-12-13 16:31:32'),
(13, 3, 18, 2, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-18-3.jpg', NULL, NULL, NULL, '2024-08-21 17:00:00', '2025-12-13 16:31:32'),
(14, 3, 14, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-3.jpg', NULL, NULL, NULL, '2024-11-17 17:00:00', '2025-12-13 16:31:32'),
(15, 3, 11, 4, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-11-3.jpg', NULL, NULL, NULL, '2024-12-14 17:00:00', '2025-12-13 16:31:32'),
(16, 4, 21, 3, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-21-4.jpg', NULL, NULL, NULL, '2024-08-20 17:00:00', '2025-12-13 16:31:32'),
(17, 4, 26, 9, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-26-4.jpg', NULL, NULL, NULL, '2024-05-01 17:00:00', '2025-12-13 16:31:32'),
(18, 4, 13, 8, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-13-4.jpg', NULL, NULL, NULL, '2024-08-13 17:00:00', '2025-12-13 16:31:32'),
(19, 4, 24, 7, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-24-4.jpg', NULL, NULL, NULL, '2024-01-21 17:00:00', '2025-12-13 16:31:32'),
(20, 4, 17, 7, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-17-4.jpg', NULL, NULL, NULL, '2024-11-14 17:00:00', '2025-12-13 16:31:32'),
(21, 4, 12, 12, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-4.jpg', NULL, NULL, NULL, '2024-10-13 17:00:00', '2025-12-13 16:31:32'),
(22, 4, 18, 3, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-4.jpg', NULL, NULL, NULL, '2024-12-26 17:00:00', '2025-12-13 16:31:32'),
(23, 4, 15, 10, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-15-4.jpg', NULL, NULL, NULL, '2024-07-10 17:00:00', '2025-12-13 16:31:32'),
(24, 4, 22, 12, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-22-4.jpg', NULL, NULL, NULL, '2024-07-01 17:00:00', '2025-12-13 16:31:32'),
(25, 4, 19, 9, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-19-4.jpg', NULL, NULL, NULL, '2024-05-04 17:00:00', '2025-12-13 16:31:32'),
(26, 4, 9, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-4.jpg', NULL, NULL, NULL, '2024-09-10 17:00:00', '2025-12-13 16:31:32'),
(27, 4, 23, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-23-4.jpg', NULL, NULL, NULL, '2024-08-14 17:00:00', '2025-12-13 16:31:32'),
(28, 4, 11, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-4.jpg', NULL, NULL, NULL, '2024-02-15 17:00:00', '2025-12-13 16:31:32'),
(29, 4, 16, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-16-4.jpg', NULL, NULL, NULL, '2024-05-10 17:00:00', '2025-12-13 16:31:32'),
(30, 4, 25, 1, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-25-4.jpg', NULL, NULL, NULL, '2024-12-08 17:00:00', '2025-12-13 16:31:32'),
(31, 4, 20, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-4.jpg', NULL, NULL, NULL, '2024-04-10 17:00:00', '2025-12-13 16:31:32'),
(32, 4, 10, 2, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-10-4.jpg', NULL, NULL, NULL, '2024-11-09 17:00:00', '2025-12-13 16:31:32'),
(33, 4, 8, 9, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-8-4.jpg', NULL, NULL, NULL, '2024-05-10 17:00:00', '2025-12-13 16:31:32'),
(34, 4, 14, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-4.jpg', NULL, NULL, NULL, '2024-11-16 17:00:00', '2025-12-13 16:31:32'),
(35, 5, 8, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-8-5.jpg', NULL, NULL, NULL, '2024-11-23 17:00:00', '2025-12-13 16:31:32'),
(36, 5, 9, 11, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-9-5.jpg', NULL, NULL, NULL, '2024-01-01 17:00:00', '2025-12-13 16:31:32'),
(37, 5, 13, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-13-5.jpg', NULL, NULL, NULL, '2024-10-07 17:00:00', '2025-12-13 16:31:32'),
(38, 5, 10, 1, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-10-5.jpg', NULL, NULL, NULL, '2024-09-21 17:00:00', '2025-12-13 16:31:32'),
(39, 5, 14, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-5.jpg', NULL, NULL, NULL, '2024-10-22 17:00:00', '2025-12-13 16:31:32'),
(40, 5, 21, 7, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-21-5.jpg', NULL, NULL, NULL, '2024-05-04 17:00:00', '2025-12-13 16:31:32'),
(41, 5, 16, 3, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-16-5.jpg', NULL, NULL, NULL, '2024-02-23 17:00:00', '2025-12-13 16:31:32'),
(42, 5, 11, 10, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-11-5.jpg', NULL, NULL, NULL, '2024-07-31 17:00:00', '2025-12-13 16:31:32'),
(43, 5, 12, 10, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-5.jpg', NULL, NULL, NULL, '2024-08-27 17:00:00', '2025-12-13 16:31:32'),
(44, 5, 15, 10, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-15-5.jpg', NULL, NULL, NULL, '2024-08-15 17:00:00', '2025-12-13 16:31:32'),
(45, 5, 18, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-18-5.jpg', NULL, NULL, NULL, '2024-05-05 17:00:00', '2025-12-13 16:31:32'),
(46, 5, 20, 1, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-20-5.jpg', NULL, NULL, NULL, '2024-02-09 17:00:00', '2025-12-13 16:31:32'),
(47, 5, 17, 6, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-17-5.jpg', NULL, NULL, NULL, '2024-09-21 17:00:00', '2025-12-13 16:31:32'),
(48, 5, 19, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-19-5.jpg', NULL, NULL, NULL, '2024-12-13 17:00:00', '2025-12-13 16:31:32'),
(49, 5, 22, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-22-5.jpg', NULL, NULL, NULL, '2024-11-02 17:00:00', '2025-12-13 16:31:32'),
(50, 6, 10, 5, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-10-6.jpg', NULL, NULL, NULL, '2024-02-08 17:00:00', '2025-12-13 16:31:32'),
(51, 6, 11, 10, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-11-6.jpg', NULL, NULL, NULL, '2024-09-03 17:00:00', '2025-12-13 16:31:32'),
(52, 6, 8, 7, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-8-6.jpg', NULL, NULL, NULL, '2024-10-23 17:00:00', '2025-12-13 16:31:32'),
(53, 6, 17, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-17-6.jpg', NULL, NULL, NULL, '2024-03-16 17:00:00', '2025-12-13 16:31:32'),
(54, 6, 16, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-16-6.jpg', NULL, NULL, NULL, '2024-12-24 17:00:00', '2025-12-13 16:31:32'),
(55, 6, 9, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-6.jpg', NULL, NULL, NULL, '2024-10-15 17:00:00', '2025-12-13 16:31:32'),
(56, 6, 18, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-18-6.jpg', NULL, NULL, NULL, '2024-02-26 17:00:00', '2025-12-13 16:31:32'),
(57, 6, 21, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-21-6.jpg', NULL, NULL, NULL, '2024-05-20 17:00:00', '2025-12-13 16:31:32'),
(58, 6, 19, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-19-6.jpg', NULL, NULL, NULL, '2024-03-15 17:00:00', '2025-12-13 16:31:32'),
(59, 6, 12, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-6.jpg', NULL, NULL, NULL, '2024-03-20 17:00:00', '2025-12-13 16:31:32'),
(60, 6, 13, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-13-6.jpg', NULL, NULL, NULL, '2024-12-04 17:00:00', '2025-12-13 16:31:32'),
(61, 6, 22, 11, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-22-6.jpg', NULL, NULL, NULL, '2024-07-06 17:00:00', '2025-12-13 16:31:32'),
(62, 6, 15, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-6.jpg', NULL, NULL, NULL, '2024-05-04 17:00:00', '2025-12-13 16:31:32'),
(63, 6, 14, 5, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-6.jpg', NULL, NULL, NULL, '2024-11-30 17:00:00', '2025-12-13 16:31:32'),
(64, 6, 20, 8, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-20-6.jpg', NULL, NULL, NULL, '2024-04-02 17:00:00', '2025-12-13 16:31:32'),
(65, 7, 17, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-17-7.jpg', NULL, NULL, NULL, '2024-09-14 17:00:00', '2025-12-13 16:31:32'),
(66, 7, 8, 9, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-7.jpg', NULL, NULL, NULL, '2024-12-07 17:00:00', '2025-12-13 16:31:32'),
(67, 7, 10, 9, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-10-7.jpg', NULL, NULL, NULL, '2024-03-25 17:00:00', '2025-12-13 16:31:32'),
(68, 7, 18, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-7.jpg', NULL, NULL, NULL, '2024-07-24 17:00:00', '2025-12-13 16:31:32'),
(69, 7, 16, 2, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-16-7.jpg', NULL, NULL, NULL, '2024-02-07 17:00:00', '2025-12-13 16:31:32'),
(70, 7, 22, 7, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-22-7.jpg', NULL, NULL, NULL, '2024-03-15 17:00:00', '2025-12-13 16:31:32'),
(71, 7, 15, 6, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-15-7.jpg', NULL, NULL, NULL, '2024-06-24 17:00:00', '2025-12-13 16:31:32'),
(72, 7, 19, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-7.jpg', NULL, NULL, NULL, '2024-09-26 17:00:00', '2025-12-13 16:31:32'),
(73, 7, 21, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-21-7.jpg', NULL, NULL, NULL, '2024-06-06 17:00:00', '2025-12-13 16:31:32'),
(74, 7, 23, 7, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-23-7.jpg', NULL, NULL, NULL, '2024-05-24 17:00:00', '2025-12-13 16:31:32'),
(75, 7, 14, 6, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-14-7.jpg', NULL, NULL, NULL, '2024-11-20 17:00:00', '2025-12-13 16:31:32'),
(76, 7, 11, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-11-7.jpg', NULL, NULL, NULL, '2024-03-15 17:00:00', '2025-12-13 16:31:32'),
(77, 7, 13, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-7.jpg', NULL, NULL, NULL, '2024-05-20 17:00:00', '2025-12-13 16:31:32'),
(78, 7, 12, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-7.jpg', NULL, NULL, NULL, '2024-09-20 17:00:00', '2025-12-13 16:31:32'),
(79, 7, 20, 4, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-20-7.jpg', NULL, NULL, NULL, '2024-04-26 17:00:00', '2025-12-13 16:31:32'),
(80, 7, 9, 1, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-9-7.jpg', NULL, NULL, NULL, '2024-09-23 17:00:00', '2025-12-13 16:31:32'),
(81, 8, 18, 7, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-8.jpg', NULL, NULL, NULL, '2024-06-27 17:00:00', '2025-12-13 16:31:32'),
(82, 8, 19, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-8.jpg', NULL, NULL, NULL, '2024-01-04 17:00:00', '2025-12-13 16:31:32'),
(83, 8, 14, 7, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-14-8.jpg', NULL, NULL, NULL, '2024-12-17 17:00:00', '2025-12-13 16:31:32'),
(84, 8, 11, 12, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-11-8.jpg', NULL, NULL, NULL, '2024-03-26 17:00:00', '2025-12-13 16:31:32'),
(85, 8, 13, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-8.jpg', NULL, NULL, NULL, '2024-11-23 17:00:00', '2025-12-13 16:31:32'),
(86, 8, 9, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-9-8.jpg', NULL, NULL, NULL, '2024-03-09 17:00:00', '2025-12-13 16:31:32'),
(87, 8, 16, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-8.jpg', NULL, NULL, NULL, '2024-10-07 17:00:00', '2025-12-13 16:31:32'),
(88, 8, 12, 2, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-12-8.jpg', NULL, NULL, NULL, '2024-08-21 17:00:00', '2025-12-13 16:31:32'),
(89, 8, 8, 3, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-8-8.jpg', NULL, NULL, NULL, '2024-03-11 17:00:00', '2025-12-13 16:31:32'),
(90, 8, 15, 9, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-15-8.jpg', NULL, NULL, NULL, '2024-04-27 17:00:00', '2025-12-13 16:31:32'),
(91, 8, 10, 8, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-10-8.jpg', NULL, NULL, NULL, '2024-03-21 17:00:00', '2025-12-13 16:31:32'),
(92, 8, 17, 11, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-17-8.jpg', NULL, NULL, NULL, '2024-05-05 17:00:00', '2025-12-13 16:31:32'),
(93, 9, 8, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-9.jpg', NULL, NULL, NULL, '2024-03-09 17:00:00', '2025-12-13 16:31:32'),
(94, 9, 11, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-9.jpg', NULL, NULL, NULL, '2024-01-05 17:00:00', '2025-12-13 16:31:32'),
(95, 9, 9, 7, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-9-9.jpg', NULL, NULL, NULL, '2024-03-18 17:00:00', '2025-12-13 16:31:32'),
(96, 9, 21, 2, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-21-9.jpg', NULL, NULL, NULL, '2024-06-23 17:00:00', '2025-12-13 16:31:32'),
(97, 9, 10, 11, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-10-9.jpg', NULL, NULL, NULL, '2024-08-31 17:00:00', '2025-12-13 16:31:32'),
(98, 9, 16, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-9.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:32'),
(99, 9, 12, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-12-9.jpg', NULL, NULL, NULL, '2024-02-13 17:00:00', '2025-12-13 16:31:32'),
(100, 9, 23, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-23-9.jpg', NULL, NULL, NULL, '2024-01-08 17:00:00', '2025-12-13 16:31:32'),
(101, 9, 19, 4, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-19-9.jpg', NULL, NULL, NULL, '2024-07-08 17:00:00', '2025-12-13 16:31:32'),
(102, 9, 22, 5, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-22-9.jpg', NULL, NULL, NULL, '2024-08-16 17:00:00', '2025-12-13 16:31:32'),
(103, 9, 15, 4, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-15-9.jpg', NULL, NULL, NULL, '2024-06-26 17:00:00', '2025-12-13 16:31:32'),
(104, 9, 18, 4, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-18-9.jpg', NULL, NULL, NULL, '2024-01-03 17:00:00', '2025-12-13 16:31:32'),
(105, 9, 17, 4, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-17-9.jpg', NULL, NULL, NULL, '2024-01-21 17:00:00', '2025-12-13 16:31:32'),
(106, 9, 13, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-9.jpg', NULL, NULL, NULL, '2024-06-15 17:00:00', '2025-12-13 16:31:32'),
(107, 9, 20, 10, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-20-9.jpg', NULL, NULL, NULL, '2024-11-19 17:00:00', '2025-12-13 16:31:32'),
(108, 9, 14, 8, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-14-9.jpg', NULL, NULL, NULL, '2024-01-10 17:00:00', '2025-12-13 16:31:32'),
(109, 10, 16, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-16-10.jpg', NULL, NULL, NULL, '2024-11-01 17:00:00', '2025-12-13 16:31:32'),
(110, 10, 20, 2, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-20-10.jpg', NULL, NULL, NULL, '2024-04-11 17:00:00', '2025-12-13 16:31:32'),
(111, 10, 9, 5, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-9-10.jpg', NULL, NULL, NULL, '2024-09-06 17:00:00', '2025-12-13 16:31:32'),
(112, 10, 10, 6, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-10-10.jpg', NULL, NULL, NULL, '2024-07-15 17:00:00', '2025-12-13 16:31:32'),
(113, 10, 12, 7, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-12-10.jpg', NULL, NULL, NULL, '2024-02-02 17:00:00', '2025-12-13 16:31:32'),
(114, 10, 18, 7, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-18-10.jpg', NULL, NULL, NULL, '2024-12-12 17:00:00', '2025-12-13 16:31:32'),
(115, 10, 17, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-10.jpg', NULL, NULL, NULL, '2024-07-14 17:00:00', '2025-12-13 16:31:32'),
(116, 10, 13, 7, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-13-10.jpg', NULL, NULL, NULL, '2024-09-21 17:00:00', '2025-12-13 16:31:32'),
(117, 10, 22, 5, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-22-10.jpg', NULL, NULL, NULL, '2024-06-05 17:00:00', '2025-12-13 16:31:32'),
(118, 10, 8, 1, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-8-10.jpg', NULL, NULL, NULL, '2024-03-06 17:00:00', '2025-12-13 16:31:32'),
(119, 10, 19, 12, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-19-10.jpg', NULL, NULL, NULL, '2024-11-04 17:00:00', '2025-12-13 16:31:32'),
(120, 10, 23, 12, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-23-10.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:32'),
(121, 10, 21, 8, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-21-10.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:32'),
(122, 10, 15, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-10.jpg', NULL, NULL, NULL, '2024-11-26 17:00:00', '2025-12-13 16:31:32'),
(123, 10, 14, 3, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-14-10.jpg', NULL, NULL, NULL, '2024-06-30 17:00:00', '2025-12-13 16:31:32'),
(124, 10, 11, 9, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-11-10.jpg', NULL, NULL, NULL, '2024-11-01 17:00:00', '2025-12-13 16:31:32'),
(125, 11, 11, 4, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-11-11.jpg', NULL, NULL, NULL, '2024-09-30 17:00:00', '2025-12-13 16:31:32'),
(126, 11, 10, 9, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-10-11.jpg', NULL, NULL, NULL, '2024-02-10 17:00:00', '2025-12-13 16:31:32'),
(127, 11, 9, 8, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-9-11.jpg', NULL, NULL, NULL, '2024-08-16 17:00:00', '2025-12-13 16:31:32'),
(128, 11, 8, 11, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-11.jpg', NULL, NULL, NULL, '2024-01-02 17:00:00', '2025-12-13 16:31:32'),
(129, 11, 14, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-14-11.jpg', NULL, NULL, NULL, '2024-09-17 17:00:00', '2025-12-13 16:31:32'),
(130, 11, 21, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-21-11.jpg', NULL, NULL, NULL, '2024-01-31 17:00:00', '2025-12-13 16:31:32'),
(131, 11, 19, 12, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-19-11.jpg', NULL, NULL, NULL, '2024-11-04 17:00:00', '2025-12-13 16:31:32'),
(132, 11, 12, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-11.jpg', NULL, NULL, NULL, '2024-05-15 17:00:00', '2025-12-13 16:31:32'),
(133, 11, 25, 9, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-25-11.jpg', NULL, NULL, NULL, '2024-09-12 17:00:00', '2025-12-13 16:31:32'),
(134, 11, 17, 7, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-17-11.jpg', NULL, NULL, NULL, '2024-11-01 17:00:00', '2025-12-13 16:31:32'),
(135, 11, 27, 1, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-27-11.jpg', NULL, NULL, NULL, '2024-03-23 17:00:00', '2025-12-13 16:31:32'),
(136, 11, 18, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-11.jpg', NULL, NULL, NULL, '2024-11-26 17:00:00', '2025-12-13 16:31:32'),
(137, 11, 22, 5, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-22-11.jpg', NULL, NULL, NULL, '2024-10-02 17:00:00', '2025-12-13 16:31:32'),
(138, 11, 16, 11, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-16-11.jpg', NULL, NULL, NULL, '2024-11-19 17:00:00', '2025-12-13 16:31:32'),
(139, 11, 15, 7, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-15-11.jpg', NULL, NULL, NULL, '2024-06-23 17:00:00', '2025-12-13 16:31:32'),
(140, 11, 20, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-11.jpg', NULL, NULL, NULL, '2024-03-03 17:00:00', '2025-12-13 16:31:32'),
(141, 11, 26, 10, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-26-11.jpg', NULL, NULL, NULL, '2024-05-03 17:00:00', '2025-12-13 16:31:32'),
(142, 11, 24, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-24-11.jpg', NULL, NULL, NULL, '2024-07-23 17:00:00', '2025-12-13 16:31:32'),
(143, 11, 23, 9, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-23-11.jpg', NULL, NULL, NULL, '2024-05-10 17:00:00', '2025-12-13 16:31:32'),
(144, 11, 13, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-11.jpg', NULL, NULL, NULL, '2024-10-23 17:00:00', '2025-12-13 16:31:32'),
(145, 12, 22, 5, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-22-12.jpg', NULL, NULL, NULL, '2024-06-11 17:00:00', '2025-12-13 16:31:32'),
(146, 12, 14, 8, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-14-12.jpg', NULL, NULL, NULL, '2024-12-19 17:00:00', '2025-12-13 16:31:32'),
(147, 12, 9, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-12.jpg', NULL, NULL, NULL, '2024-09-10 17:00:00', '2025-12-13 16:31:32'),
(148, 12, 16, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-16-12.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:32'),
(149, 12, 8, 10, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-8-12.jpg', NULL, NULL, NULL, '2024-10-20 17:00:00', '2025-12-13 16:31:32'),
(150, 12, 24, 2, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-24-12.jpg', NULL, NULL, NULL, '2024-07-08 17:00:00', '2025-12-13 16:31:32'),
(151, 12, 18, 5, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-18-12.jpg', NULL, NULL, NULL, '2024-09-25 17:00:00', '2025-12-13 16:31:32'),
(152, 12, 10, 10, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-10-12.jpg', NULL, NULL, NULL, '2024-07-07 17:00:00', '2025-12-13 16:31:32'),
(153, 12, 11, 7, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-11-12.jpg', NULL, NULL, NULL, '2024-10-13 17:00:00', '2025-12-13 16:31:32'),
(154, 12, 13, 6, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-13-12.jpg', NULL, NULL, NULL, '2024-04-30 17:00:00', '2025-12-13 16:31:32'),
(155, 12, 17, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-12.jpg', NULL, NULL, NULL, '2024-01-25 17:00:00', '2025-12-13 16:31:32'),
(156, 12, 12, 7, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-12-12.jpg', NULL, NULL, NULL, '2024-06-20 17:00:00', '2025-12-13 16:31:32'),
(157, 12, 20, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-12.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:32'),
(158, 12, 15, 9, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-15-12.jpg', NULL, NULL, NULL, '2024-02-17 17:00:00', '2025-12-13 16:31:32'),
(159, 12, 26, 7, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-26-12.jpg', NULL, NULL, NULL, '2024-11-05 17:00:00', '2025-12-13 16:31:32'),
(160, 12, 21, 2, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-21-12.jpg', NULL, NULL, NULL, '2024-02-02 17:00:00', '2025-12-13 16:31:32'),
(161, 12, 19, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-12.jpg', NULL, NULL, NULL, '2024-09-30 17:00:00', '2025-12-13 16:31:32'),
(162, 12, 23, 8, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-23-12.jpg', NULL, NULL, NULL, '2024-05-15 17:00:00', '2025-12-13 16:31:32'),
(163, 12, 25, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-25-12.jpg', NULL, NULL, NULL, '2024-10-10 17:00:00', '2025-12-13 16:31:32'),
(164, 13, 11, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-11-13.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:32'),
(165, 13, 13, 7, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-13-13.jpg', NULL, NULL, NULL, '2024-07-27 17:00:00', '2025-12-13 16:31:32'),
(166, 13, 15, 11, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-15-13.jpg', NULL, NULL, NULL, '2024-12-08 17:00:00', '2025-12-13 16:31:32'),
(167, 13, 22, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-22-13.jpg', NULL, NULL, NULL, '2024-01-11 17:00:00', '2025-12-13 16:31:32'),
(168, 13, 8, 5, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-8-13.jpg', NULL, NULL, NULL, '2024-01-16 17:00:00', '2025-12-13 16:31:32'),
(169, 13, 16, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-16-13.jpg', NULL, NULL, NULL, '2024-11-07 17:00:00', '2025-12-13 16:31:32'),
(170, 13, 17, 2, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-17-13.jpg', NULL, NULL, NULL, '2024-09-20 17:00:00', '2025-12-13 16:31:32'),
(171, 13, 20, 3, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-20-13.jpg', NULL, NULL, NULL, '2024-10-03 17:00:00', '2025-12-13 16:31:32'),
(172, 13, 10, 4, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-10-13.jpg', NULL, NULL, NULL, '2024-08-01 17:00:00', '2025-12-13 16:31:32'),
(173, 13, 9, 4, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-9-13.jpg', NULL, NULL, NULL, '2024-11-16 17:00:00', '2025-12-13 16:31:32'),
(174, 13, 19, 9, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-13.jpg', NULL, NULL, NULL, '2024-12-16 17:00:00', '2025-12-13 16:31:32'),
(175, 13, 12, 8, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-12-13.jpg', NULL, NULL, NULL, '2024-08-26 17:00:00', '2025-12-13 16:31:32'),
(176, 13, 21, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-21-13.jpg', NULL, NULL, NULL, '2024-08-24 17:00:00', '2025-12-13 16:31:32'),
(177, 13, 18, 12, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-18-13.jpg', NULL, NULL, NULL, '2024-03-03 17:00:00', '2025-12-13 16:31:32'),
(178, 13, 14, 8, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-14-13.jpg', NULL, NULL, NULL, '2024-03-24 17:00:00', '2025-12-13 16:31:32'),
(179, 14, 12, 10, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-12-14.jpg', NULL, NULL, NULL, '2024-05-10 17:00:00', '2025-12-13 16:31:32'),
(180, 14, 18, 8, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-14.jpg', NULL, NULL, NULL, '2024-05-05 17:00:00', '2025-12-13 16:31:32'),
(181, 14, 15, 6, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-15-14.jpg', NULL, NULL, NULL, '2024-08-21 17:00:00', '2025-12-13 16:31:32'),
(182, 14, 24, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-24-14.jpg', NULL, NULL, NULL, '2024-01-31 17:00:00', '2025-12-13 16:31:32'),
(183, 14, 23, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-23-14.jpg', NULL, NULL, NULL, '2024-04-27 17:00:00', '2025-12-13 16:31:32'),
(184, 14, 9, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-9-14.jpg', NULL, NULL, NULL, '2024-11-17 17:00:00', '2025-12-13 16:31:32'),
(185, 14, 16, 1, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-16-14.jpg', NULL, NULL, NULL, '2024-02-05 17:00:00', '2025-12-13 16:31:32'),
(186, 14, 22, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-22-14.jpg', NULL, NULL, NULL, '2024-10-21 17:00:00', '2025-12-13 16:31:32'),
(187, 14, 11, 7, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-11-14.jpg', NULL, NULL, NULL, '2024-04-12 17:00:00', '2025-12-13 16:31:32'),
(188, 14, 17, 10, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-17-14.jpg', NULL, NULL, NULL, '2024-05-18 17:00:00', '2025-12-13 16:31:32'),
(189, 14, 8, 5, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-8-14.jpg', NULL, NULL, NULL, '2024-05-10 17:00:00', '2025-12-13 16:31:32'),
(190, 14, 21, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-21-14.jpg', NULL, NULL, NULL, '2024-07-06 17:00:00', '2025-12-13 16:31:32'),
(191, 14, 19, 10, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-19-14.jpg', NULL, NULL, NULL, '2024-09-18 17:00:00', '2025-12-13 16:31:32'),
(192, 14, 13, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-13-14.jpg', NULL, NULL, NULL, '2024-12-03 17:00:00', '2025-12-13 16:31:32'),
(193, 14, 14, 4, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-14-14.jpg', NULL, NULL, NULL, '2024-02-21 17:00:00', '2025-12-13 16:31:32'),
(194, 14, 10, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-10-14.jpg', NULL, NULL, NULL, '2024-05-15 17:00:00', '2025-12-13 16:31:32'),
(195, 14, 20, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-14.jpg', NULL, NULL, NULL, '2024-02-08 17:00:00', '2025-12-13 16:31:32'),
(196, 15, 17, 10, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-17-15.jpg', NULL, NULL, NULL, '2024-04-13 17:00:00', '2025-12-13 16:31:32'),
(197, 15, 27, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-27-15.jpg', NULL, NULL, NULL, '2024-04-11 17:00:00', '2025-12-13 16:31:32'),
(198, 15, 19, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-19-15.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:32'),
(199, 15, 20, 10, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-20-15.jpg', NULL, NULL, NULL, '2024-07-15 17:00:00', '2025-12-13 16:31:32'),
(200, 15, 11, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-15.jpg', NULL, NULL, NULL, '2024-04-06 17:00:00', '2025-12-13 16:31:32'),
(201, 15, 15, 3, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-15-15.jpg', NULL, NULL, NULL, '2024-07-05 17:00:00', '2025-12-13 16:31:32'),
(202, 15, 23, 4, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-23-15.jpg', NULL, NULL, NULL, '2024-07-25 17:00:00', '2025-12-13 16:31:32'),
(203, 15, 26, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-26-15.jpg', NULL, NULL, NULL, '2024-02-05 17:00:00', '2025-12-13 16:31:32'),
(204, 15, 14, 12, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-14-15.jpg', NULL, NULL, NULL, '2024-11-10 17:00:00', '2025-12-13 16:31:32'),
(205, 15, 10, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-10-15.jpg', NULL, NULL, NULL, '2024-10-09 17:00:00', '2025-12-13 16:31:32'),
(206, 15, 16, 7, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-16-15.jpg', NULL, NULL, NULL, '2024-03-04 17:00:00', '2025-12-13 16:31:32'),
(207, 15, 9, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-15.jpg', NULL, NULL, NULL, '2024-11-05 17:00:00', '2025-12-13 16:31:32'),
(208, 15, 21, 9, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-21-15.jpg', NULL, NULL, NULL, '2024-04-11 17:00:00', '2025-12-13 16:31:32'),
(209, 15, 25, 3, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-25-15.jpg', NULL, NULL, NULL, '2024-09-19 17:00:00', '2025-12-13 16:31:32'),
(210, 15, 18, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-18-15.jpg', NULL, NULL, NULL, '2024-10-13 17:00:00', '2025-12-13 16:31:32'),
(211, 15, 8, 12, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-8-15.jpg', NULL, NULL, NULL, '2024-04-22 17:00:00', '2025-12-13 16:31:32'),
(212, 15, 24, 8, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-24-15.jpg', NULL, NULL, NULL, '2024-08-11 17:00:00', '2025-12-13 16:31:32'),
(213, 15, 12, 4, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-15.jpg', NULL, NULL, NULL, '2024-08-19 17:00:00', '2025-12-13 16:31:32'),
(214, 15, 22, 3, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-22-15.jpg', NULL, NULL, NULL, '2024-04-10 17:00:00', '2025-12-13 16:31:32'),
(215, 15, 13, 10, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-13-15.jpg', NULL, NULL, NULL, '2024-08-27 17:00:00', '2025-12-13 16:31:32'),
(216, 16, 17, 10, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-17-16.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:32'),
(217, 16, 8, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-16.jpg', NULL, NULL, NULL, '2024-03-10 17:00:00', '2025-12-13 16:31:32'),
(218, 16, 22, 11, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-22-16.jpg', NULL, NULL, NULL, '2024-04-09 17:00:00', '2025-12-13 16:31:32'),
(219, 16, 10, 10, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-10-16.jpg', NULL, NULL, NULL, '2024-03-24 17:00:00', '2025-12-13 16:31:32'),
(220, 16, 11, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-16.jpg', NULL, NULL, NULL, '2024-02-18 17:00:00', '2025-12-13 16:31:32'),
(221, 16, 19, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-16.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:32'),
(222, 16, 18, 12, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-16.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:32'),
(223, 16, 9, 11, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-9-16.jpg', NULL, NULL, NULL, '2024-01-23 17:00:00', '2025-12-13 16:31:32'),
(224, 16, 21, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-21-16.jpg', NULL, NULL, NULL, '2024-06-13 17:00:00', '2025-12-13 16:31:32'),
(225, 16, 24, 8, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-24-16.jpg', NULL, NULL, NULL, '2024-08-07 17:00:00', '2025-12-13 16:31:32'),
(226, 16, 12, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-16.jpg', NULL, NULL, NULL, '2024-04-10 17:00:00', '2025-12-13 16:31:32'),
(227, 16, 13, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-16.jpg', NULL, NULL, NULL, '2024-12-04 17:00:00', '2025-12-13 16:31:32'),
(228, 16, 14, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-16.jpg', NULL, NULL, NULL, '2024-01-18 17:00:00', '2025-12-13 16:31:32'),
(229, 16, 23, 9, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-23-16.jpg', NULL, NULL, NULL, '2024-01-13 17:00:00', '2025-12-13 16:31:32'),
(230, 16, 15, 6, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-15-16.jpg', NULL, NULL, NULL, '2024-08-13 17:00:00', '2025-12-13 16:31:32'),
(231, 16, 20, 6, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-20-16.jpg', NULL, NULL, NULL, '2024-07-06 17:00:00', '2025-12-13 16:31:32'),
(232, 16, 16, 9, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-16-16.jpg', NULL, NULL, NULL, '2024-02-26 17:00:00', '2025-12-13 16:31:32'),
(233, 17, 22, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-22-17.jpg', NULL, NULL, NULL, '2024-02-09 17:00:00', '2025-12-13 16:31:32'),
(234, 17, 12, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-12-17.jpg', NULL, NULL, NULL, '2024-10-25 17:00:00', '2025-12-13 16:31:32'),
(235, 17, 14, 12, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-14-17.jpg', NULL, NULL, NULL, '2024-09-07 17:00:00', '2025-12-13 16:31:32'),
(236, 17, 8, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-8-17.jpg', NULL, NULL, NULL, '2024-06-23 17:00:00', '2025-12-13 16:31:32'),
(237, 17, 13, 12, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-13-17.jpg', NULL, NULL, NULL, '2024-02-06 17:00:00', '2025-12-13 16:31:32'),
(238, 17, 21, 10, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-21-17.jpg', NULL, NULL, NULL, '2024-12-10 17:00:00', '2025-12-13 16:31:32'),
(239, 17, 9, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-9-17.jpg', NULL, NULL, NULL, '2024-05-19 17:00:00', '2025-12-13 16:31:32'),
(240, 17, 25, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-25-17.jpg', NULL, NULL, NULL, '2024-04-18 17:00:00', '2025-12-13 16:31:32'),
(241, 17, 24, 11, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-24-17.jpg', NULL, NULL, NULL, '2024-06-30 17:00:00', '2025-12-13 16:31:32'),
(242, 17, 26, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-26-17.jpg', NULL, NULL, NULL, '2024-01-27 17:00:00', '2025-12-13 16:31:32'),
(243, 17, 16, 9, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-16-17.jpg', NULL, NULL, NULL, '2024-11-27 17:00:00', '2025-12-13 16:31:32'),
(244, 17, 11, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-17.jpg', NULL, NULL, NULL, '2024-12-10 17:00:00', '2025-12-13 16:31:32'),
(245, 17, 20, 10, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-20-17.jpg', NULL, NULL, NULL, '2024-02-13 17:00:00', '2025-12-13 16:31:32'),
(246, 17, 23, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-23-17.jpg', NULL, NULL, NULL, '2024-04-10 17:00:00', '2025-12-13 16:31:32'),
(247, 17, 18, 9, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-18-17.jpg', NULL, NULL, NULL, '2024-09-08 17:00:00', '2025-12-13 16:31:32'),
(248, 17, 19, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-17.jpg', NULL, NULL, NULL, '2024-04-12 17:00:00', '2025-12-13 16:31:32'),
(249, 17, 10, 3, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-10-17.jpg', NULL, NULL, NULL, '2024-09-19 17:00:00', '2025-12-13 16:31:32'),
(250, 17, 15, 10, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-15-17.jpg', NULL, NULL, NULL, '2024-02-16 17:00:00', '2025-12-13 16:31:32'),
(251, 17, 17, 9, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-17-17.jpg', NULL, NULL, NULL, '2024-04-01 17:00:00', '2025-12-13 16:31:32'),
(252, 18, 19, 4, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-19-18.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:32'),
(253, 18, 13, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-18.jpg', NULL, NULL, NULL, '2024-08-11 17:00:00', '2025-12-13 16:31:32'),
(254, 18, 16, 3, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-18.jpg', NULL, NULL, NULL, '2024-08-25 17:00:00', '2025-12-13 16:31:32'),
(255, 18, 9, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-9-18.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:32'),
(256, 18, 21, 9, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-21-18.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:32'),
(257, 18, 22, 9, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-22-18.jpg', NULL, NULL, NULL, '2024-11-27 17:00:00', '2025-12-13 16:31:32'),
(258, 18, 25, 11, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-25-18.jpg', NULL, NULL, NULL, '2024-10-27 17:00:00', '2025-12-13 16:31:32'),
(259, 18, 26, 6, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-26-18.jpg', NULL, NULL, NULL, '2024-06-16 17:00:00', '2025-12-13 16:31:32'),
(260, 18, 20, 8, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-20-18.jpg', NULL, NULL, NULL, '2024-06-06 17:00:00', '2025-12-13 16:31:32'),
(261, 18, 24, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-24-18.jpg', NULL, NULL, NULL, '2024-02-20 17:00:00', '2025-12-13 16:31:32'),
(262, 18, 18, 11, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-18-18.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:32'),
(263, 18, 11, 7, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-11-18.jpg', NULL, NULL, NULL, '2024-12-10 17:00:00', '2025-12-13 16:31:32'),
(264, 18, 8, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-8-18.jpg', NULL, NULL, NULL, '2024-01-01 17:00:00', '2025-12-13 16:31:32'),
(265, 18, 12, 2, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-12-18.jpg', NULL, NULL, NULL, '2024-01-23 17:00:00', '2025-12-13 16:31:32'),
(266, 18, 14, 9, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-14-18.jpg', NULL, NULL, NULL, '2024-02-19 17:00:00', '2025-12-13 16:31:32'),
(267, 18, 15, 7, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-15-18.jpg', NULL, NULL, NULL, '2024-06-11 17:00:00', '2025-12-13 16:31:32'),
(268, 18, 17, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-18.jpg', NULL, NULL, NULL, '2024-10-23 17:00:00', '2025-12-13 16:31:32'),
(269, 18, 10, 5, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-10-18.jpg', NULL, NULL, NULL, '2024-03-22 17:00:00', '2025-12-13 16:31:32'),
(270, 18, 27, 2, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-27-18.jpg', NULL, NULL, NULL, '2024-12-19 17:00:00', '2025-12-13 16:31:32'),
(271, 18, 23, 11, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-23-18.jpg', NULL, NULL, NULL, '2024-07-27 17:00:00', '2025-12-13 16:31:32'),
(272, 19, 11, 11, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-11-19.jpg', NULL, NULL, NULL, '2024-11-01 17:00:00', '2025-12-13 16:31:32'),
(273, 19, 13, 4, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-13-19.jpg', NULL, NULL, NULL, '2024-11-14 17:00:00', '2025-12-13 16:31:32'),
(274, 19, 18, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-19.jpg', NULL, NULL, NULL, '2024-03-15 17:00:00', '2025-12-13 16:31:32'),
(275, 19, 10, 1, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-10-19.jpg', NULL, NULL, NULL, '2024-10-15 17:00:00', '2025-12-13 16:31:32'),
(276, 19, 14, 8, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-14-19.jpg', NULL, NULL, NULL, '2024-01-02 17:00:00', '2025-12-13 16:31:32'),
(277, 19, 8, 2, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-8-19.jpg', NULL, NULL, NULL, '2024-09-03 17:00:00', '2025-12-13 16:31:32'),
(278, 19, 19, 10, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-19.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:32'),
(279, 19, 17, 7, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-17-19.jpg', NULL, NULL, NULL, '2024-06-22 17:00:00', '2025-12-13 16:31:32'),
(280, 19, 16, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-19.jpg', NULL, NULL, NULL, '2024-11-07 17:00:00', '2025-12-13 16:31:32'),
(281, 19, 15, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-19.jpg', NULL, NULL, NULL, '2024-05-17 17:00:00', '2025-12-13 16:31:32'),
(282, 19, 9, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-19.jpg', NULL, NULL, NULL, '2024-04-08 17:00:00', '2025-12-13 16:31:32'),
(283, 19, 12, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-19.jpg', NULL, NULL, NULL, '2024-01-02 17:00:00', '2025-12-13 16:31:32'),
(284, 20, 22, 11, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-22-20.jpg', NULL, NULL, NULL, '2024-09-17 17:00:00', '2025-12-13 16:31:32'),
(285, 20, 25, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-25-20.jpg', NULL, NULL, NULL, '2024-05-01 17:00:00', '2025-12-13 16:31:32'),
(286, 20, 11, 4, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-11-20.jpg', NULL, NULL, NULL, '2024-02-12 17:00:00', '2025-12-13 16:31:32'),
(287, 20, 10, 10, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-10-20.jpg', NULL, NULL, NULL, '2024-08-11 17:00:00', '2025-12-13 16:31:32'),
(288, 20, 15, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-20.jpg', NULL, NULL, NULL, '2024-03-09 17:00:00', '2025-12-13 16:31:32'),
(289, 20, 19, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-20.jpg', NULL, NULL, NULL, '2024-04-20 17:00:00', '2025-12-13 16:31:32'),
(290, 20, 12, 6, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-12-20.jpg', NULL, NULL, NULL, '2024-04-05 17:00:00', '2025-12-13 16:31:32'),
(291, 20, 23, 12, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-23-20.jpg', NULL, NULL, NULL, '2024-01-14 17:00:00', '2025-12-13 16:31:32'),
(292, 20, 18, 8, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-18-20.jpg', NULL, NULL, NULL, '2024-10-20 17:00:00', '2025-12-13 16:31:32'),
(293, 20, 17, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-17-20.jpg', NULL, NULL, NULL, '2024-12-12 17:00:00', '2025-12-13 16:31:32'),
(294, 20, 21, 6, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-21-20.jpg', NULL, NULL, NULL, '2024-09-06 17:00:00', '2025-12-13 16:31:32'),
(295, 20, 24, 8, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-24-20.jpg', NULL, NULL, NULL, '2024-09-03 17:00:00', '2025-12-13 16:31:32'),
(296, 20, 16, 4, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-16-20.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:32'),
(297, 20, 27, 4, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-27-20.jpg', NULL, NULL, NULL, '2024-02-11 17:00:00', '2025-12-13 16:31:32'),
(298, 20, 14, 8, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-20.jpg', NULL, NULL, NULL, '2024-07-07 17:00:00', '2025-12-13 16:31:32'),
(299, 20, 26, 7, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-26-20.jpg', NULL, NULL, NULL, '2024-03-04 17:00:00', '2025-12-13 16:31:32'),
(300, 20, 20, 1, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-20-20.jpg', NULL, NULL, NULL, '2024-03-31 17:00:00', '2025-12-13 16:31:32'),
(301, 20, 8, 3, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-8-20.jpg', NULL, NULL, NULL, '2024-08-22 17:00:00', '2025-12-13 16:31:32'),
(302, 20, 9, 8, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-9-20.jpg', NULL, NULL, NULL, '2024-05-15 17:00:00', '2025-12-13 16:31:32'),
(303, 20, 13, 4, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-13-20.jpg', NULL, NULL, NULL, '2024-08-26 17:00:00', '2025-12-13 16:31:32'),
(304, 21, 11, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-21.jpg', NULL, NULL, NULL, '2024-06-20 17:00:00', '2025-12-13 16:31:32'),
(305, 21, 14, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-14-21.jpg', NULL, NULL, NULL, '2024-05-24 17:00:00', '2025-12-13 16:31:32'),
(306, 21, 20, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-21.jpg', NULL, NULL, NULL, '2024-11-06 17:00:00', '2025-12-13 16:31:32'),
(307, 21, 13, 6, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-13-21.jpg', NULL, NULL, NULL, '2024-09-04 17:00:00', '2025-12-13 16:31:32'),
(308, 21, 16, 5, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-16-21.jpg', NULL, NULL, NULL, '2024-12-14 17:00:00', '2025-12-13 16:31:32'),
(309, 21, 15, 6, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-15-21.jpg', NULL, NULL, NULL, '2024-10-27 17:00:00', '2025-12-13 16:31:32'),
(310, 21, 9, 9, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-9-21.jpg', NULL, NULL, NULL, '2024-10-31 17:00:00', '2025-12-13 16:31:32'),
(311, 21, 12, 4, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-12-21.jpg', NULL, NULL, NULL, '2024-04-23 17:00:00', '2025-12-13 16:31:32'),
(312, 21, 18, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-21.jpg', NULL, NULL, NULL, '2024-05-04 17:00:00', '2025-12-13 16:31:32'),
(313, 21, 17, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-21.jpg', NULL, NULL, NULL, '2024-11-06 17:00:00', '2025-12-13 16:31:32'),
(314, 21, 10, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-10-21.jpg', NULL, NULL, NULL, '2024-05-11 17:00:00', '2025-12-13 16:31:32'),
(315, 21, 19, 5, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-19-21.jpg', NULL, NULL, NULL, '2024-12-23 17:00:00', '2025-12-13 16:31:32'),
(316, 21, 8, 12, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-8-21.jpg', NULL, NULL, NULL, '2024-09-09 17:00:00', '2025-12-13 16:31:32'),
(317, 22, 9, 3, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-9-22.jpg', NULL, NULL, NULL, '2024-11-07 17:00:00', '2025-12-13 16:31:32'),
(318, 22, 19, 9, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-19-22.jpg', NULL, NULL, NULL, '2024-12-10 17:00:00', '2025-12-13 16:31:32'),
(319, 22, 8, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-22.jpg', NULL, NULL, NULL, '2024-09-11 17:00:00', '2025-12-13 16:31:32'),
(320, 22, 16, 11, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-22.jpg', NULL, NULL, NULL, '2024-04-11 17:00:00', '2025-12-13 16:31:32'),
(321, 22, 13, 8, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-13-22.jpg', NULL, NULL, NULL, '2024-01-06 17:00:00', '2025-12-13 16:31:32'),
(322, 22, 20, 7, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-20-22.jpg', NULL, NULL, NULL, '2024-02-03 17:00:00', '2025-12-13 16:31:32'),
(323, 22, 10, 12, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-10-22.jpg', NULL, NULL, NULL, '2024-06-27 17:00:00', '2025-12-13 16:31:32'),
(324, 22, 17, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-17-22.jpg', NULL, NULL, NULL, '2024-05-08 17:00:00', '2025-12-13 16:31:32'),
(325, 22, 15, 6, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-15-22.jpg', NULL, NULL, NULL, '2024-03-23 17:00:00', '2025-12-13 16:31:32'),
(326, 22, 11, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-11-22.jpg', NULL, NULL, NULL, '2024-12-25 17:00:00', '2025-12-13 16:31:32'),
(327, 22, 14, 4, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-14-22.jpg', NULL, NULL, NULL, '2024-01-14 17:00:00', '2025-12-13 16:31:32'),
(328, 22, 18, 3, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-22.jpg', NULL, NULL, NULL, '2024-01-13 17:00:00', '2025-12-13 16:31:32'),
(329, 22, 12, 4, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-12-22.jpg', NULL, NULL, NULL, '2024-11-27 17:00:00', '2025-12-13 16:31:32'),
(330, 23, 12, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-23.jpg', NULL, NULL, NULL, '2024-11-11 17:00:00', '2025-12-13 16:31:32'),
(331, 23, 15, 6, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-15-23.jpg', NULL, NULL, NULL, '2024-03-12 17:00:00', '2025-12-13 16:31:32'),
(332, 23, 8, 12, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-8-23.jpg', NULL, NULL, NULL, '2024-02-13 17:00:00', '2025-12-13 16:31:32'),
(333, 23, 16, 2, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-16-23.jpg', NULL, NULL, NULL, '2024-03-11 17:00:00', '2025-12-13 16:31:32'),
(334, 23, 14, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-14-23.jpg', NULL, NULL, NULL, '2024-09-17 17:00:00', '2025-12-13 16:31:32'),
(335, 23, 13, 3, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-13-23.jpg', NULL, NULL, NULL, '2024-11-26 17:00:00', '2025-12-13 16:31:32'),
(336, 23, 11, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-23.jpg', NULL, NULL, NULL, '2024-12-22 17:00:00', '2025-12-13 16:31:32'),
(337, 23, 9, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-9-23.jpg', NULL, NULL, NULL, '2024-05-25 17:00:00', '2025-12-13 16:31:32');
INSERT INTO `event_registrations` (`id`, `event_id`, `pembalap_user_id`, `kis_category_id`, `result_position`, `result_status`, `points_earned`, `status`, `payment_proof_url`, `admin_note`, `payment_processed_at`, `payment_processed_by_user_id`, `created_at`, `updated_at`) VALUES
(338, 23, 18, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-18-23.jpg', NULL, NULL, NULL, '2024-11-15 17:00:00', '2025-12-13 16:31:32'),
(339, 23, 20, 11, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-20-23.jpg', NULL, NULL, NULL, '2024-05-16 17:00:00', '2025-12-13 16:31:32'),
(340, 23, 17, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-23.jpg', NULL, NULL, NULL, '2024-07-01 17:00:00', '2025-12-13 16:31:32'),
(341, 23, 19, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-23.jpg', NULL, NULL, NULL, '2024-09-27 17:00:00', '2025-12-13 16:31:32'),
(342, 23, 10, 6, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-10-23.jpg', NULL, NULL, NULL, '2024-05-04 17:00:00', '2025-12-13 16:31:32'),
(343, 24, 12, 5, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-24.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:32'),
(344, 24, 8, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-24.jpg', NULL, NULL, NULL, '2024-02-01 17:00:00', '2025-12-13 16:31:32'),
(345, 24, 18, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-24.jpg', NULL, NULL, NULL, '2024-11-20 17:00:00', '2025-12-13 16:31:32'),
(346, 24, 13, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-13-24.jpg', NULL, NULL, NULL, '2024-06-19 17:00:00', '2025-12-13 16:31:32'),
(347, 24, 9, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-24.jpg', NULL, NULL, NULL, '2024-10-06 17:00:00', '2025-12-13 16:31:32'),
(348, 24, 14, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-14-24.jpg', NULL, NULL, NULL, '2024-06-09 17:00:00', '2025-12-13 16:31:32'),
(349, 24, 15, 8, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-15-24.jpg', NULL, NULL, NULL, '2024-06-20 17:00:00', '2025-12-13 16:31:32'),
(350, 24, 17, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-24.jpg', NULL, NULL, NULL, '2024-12-18 17:00:00', '2025-12-13 16:31:32'),
(351, 24, 16, 3, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-16-24.jpg', NULL, NULL, NULL, '2024-10-24 17:00:00', '2025-12-13 16:31:32'),
(352, 24, 11, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-11-24.jpg', NULL, NULL, NULL, '2024-08-01 17:00:00', '2025-12-13 16:31:32'),
(353, 24, 10, 12, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-10-24.jpg', NULL, NULL, NULL, '2024-02-27 17:00:00', '2025-12-13 16:31:32'),
(354, 24, 19, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-24.jpg', NULL, NULL, NULL, '2024-05-21 17:00:00', '2025-12-13 16:31:32'),
(355, 25, 16, 12, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-16-25.jpg', NULL, NULL, NULL, '2024-07-20 17:00:00', '2025-12-13 16:31:32'),
(356, 25, 20, 10, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-20-25.jpg', NULL, NULL, NULL, '2024-01-09 17:00:00', '2025-12-13 16:31:32'),
(357, 25, 12, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-12-25.jpg', NULL, NULL, NULL, '2024-09-14 17:00:00', '2025-12-13 16:31:32'),
(358, 25, 18, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-25.jpg', NULL, NULL, NULL, '2024-03-26 17:00:00', '2025-12-13 16:31:32'),
(359, 25, 10, 9, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-10-25.jpg', NULL, NULL, NULL, '2023-12-31 17:00:00', '2025-12-13 16:31:32'),
(360, 25, 9, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-25.jpg', NULL, NULL, NULL, '2024-10-13 17:00:00', '2025-12-13 16:31:32'),
(361, 25, 13, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-25.jpg', NULL, NULL, NULL, '2024-11-13 17:00:00', '2025-12-13 16:31:32'),
(362, 25, 19, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-19-25.jpg', NULL, NULL, NULL, '2024-09-14 17:00:00', '2025-12-13 16:31:32'),
(363, 25, 15, 4, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-15-25.jpg', NULL, NULL, NULL, '2024-01-18 17:00:00', '2025-12-13 16:31:32'),
(364, 25, 8, 5, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-8-25.jpg', NULL, NULL, NULL, '2024-09-02 17:00:00', '2025-12-13 16:31:32'),
(365, 25, 17, 5, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-17-25.jpg', NULL, NULL, NULL, '2024-06-13 17:00:00', '2025-12-13 16:31:32'),
(366, 25, 14, 10, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-14-25.jpg', NULL, NULL, NULL, '2024-10-06 17:00:00', '2025-12-13 16:31:32'),
(367, 25, 11, 7, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-11-25.jpg', NULL, NULL, NULL, '2024-04-12 17:00:00', '2025-12-13 16:31:32'),
(368, 26, 10, 5, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-10-26.jpg', NULL, NULL, NULL, '2024-01-14 17:00:00', '2025-12-13 16:31:32'),
(369, 26, 14, 8, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-14-26.jpg', NULL, NULL, NULL, '2024-05-07 17:00:00', '2025-12-13 16:31:32'),
(370, 26, 19, 5, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-19-26.jpg', NULL, NULL, NULL, '2024-01-12 17:00:00', '2025-12-13 16:31:32'),
(371, 26, 8, 3, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-26.jpg', NULL, NULL, NULL, '2024-12-11 17:00:00', '2025-12-13 16:31:32'),
(372, 26, 16, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-26.jpg', NULL, NULL, NULL, '2024-11-14 17:00:00', '2025-12-13 16:31:32'),
(373, 26, 13, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-13-26.jpg', NULL, NULL, NULL, '2024-12-07 17:00:00', '2025-12-13 16:31:32'),
(374, 26, 17, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-17-26.jpg', NULL, NULL, NULL, '2024-05-13 17:00:00', '2025-12-13 16:31:32'),
(375, 26, 20, 6, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-20-26.jpg', NULL, NULL, NULL, '2024-11-01 17:00:00', '2025-12-13 16:31:32'),
(376, 26, 15, 6, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-15-26.jpg', NULL, NULL, NULL, '2024-01-07 17:00:00', '2025-12-13 16:31:32'),
(377, 26, 18, 10, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-18-26.jpg', NULL, NULL, NULL, '2024-12-11 17:00:00', '2025-12-13 16:31:32'),
(378, 26, 11, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-26.jpg', NULL, NULL, NULL, '2024-04-04 17:00:00', '2025-12-13 16:31:32'),
(379, 26, 12, 2, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-12-26.jpg', NULL, NULL, NULL, '2024-06-24 17:00:00', '2025-12-13 16:31:32'),
(380, 26, 9, 11, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-9-26.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:32'),
(381, 27, 18, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-27.jpg', NULL, NULL, NULL, '2024-08-01 17:00:00', '2025-12-13 16:31:32'),
(382, 27, 9, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-9-27.jpg', NULL, NULL, NULL, '2024-04-08 17:00:00', '2025-12-13 16:31:32'),
(383, 27, 13, 9, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-13-27.jpg', NULL, NULL, NULL, '2024-09-19 17:00:00', '2025-12-13 16:31:32'),
(384, 27, 10, 1, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-10-27.jpg', NULL, NULL, NULL, '2024-06-10 17:00:00', '2025-12-13 16:31:32'),
(385, 27, 14, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-27.jpg', NULL, NULL, NULL, '2024-10-07 17:00:00', '2025-12-13 16:31:32'),
(386, 27, 17, 9, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-17-27.jpg', NULL, NULL, NULL, '2024-02-07 17:00:00', '2025-12-13 16:31:32'),
(387, 27, 15, 9, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-15-27.jpg', NULL, NULL, NULL, '2024-02-13 17:00:00', '2025-12-13 16:31:32'),
(388, 27, 16, 8, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-27.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:32'),
(389, 27, 12, 7, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-12-27.jpg', NULL, NULL, NULL, '2024-04-07 17:00:00', '2025-12-13 16:31:32'),
(390, 27, 11, 1, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-11-27.jpg', NULL, NULL, NULL, '2024-06-18 17:00:00', '2025-12-13 16:31:32'),
(391, 27, 20, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-20-27.jpg', NULL, NULL, NULL, '2024-10-16 17:00:00', '2025-12-13 16:31:32'),
(392, 27, 8, 1, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-8-27.jpg', NULL, NULL, NULL, '2024-05-27 17:00:00', '2025-12-13 16:31:32'),
(393, 27, 19, 3, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-19-27.jpg', NULL, NULL, NULL, '2024-04-14 17:00:00', '2025-12-13 16:31:32'),
(394, 28, 14, 8, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-14-28.jpg', NULL, NULL, NULL, '2024-06-05 17:00:00', '2025-12-13 16:31:32'),
(395, 28, 17, 3, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-17-28.jpg', NULL, NULL, NULL, '2024-03-20 17:00:00', '2025-12-13 16:31:32'),
(396, 28, 11, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-11-28.jpg', NULL, NULL, NULL, '2024-01-24 17:00:00', '2025-12-13 16:31:32'),
(397, 28, 15, 4, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-15-28.jpg', NULL, NULL, NULL, '2024-07-07 17:00:00', '2025-12-13 16:31:32'),
(398, 28, 20, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-20-28.jpg', NULL, NULL, NULL, '2024-04-27 17:00:00', '2025-12-13 16:31:32'),
(399, 28, 18, 5, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-18-28.jpg', NULL, NULL, NULL, '2024-12-25 17:00:00', '2025-12-13 16:31:32'),
(400, 28, 8, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-28.jpg', NULL, NULL, NULL, '2024-12-14 17:00:00', '2025-12-13 16:31:32'),
(401, 28, 19, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-19-28.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:32'),
(402, 28, 9, 7, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-9-28.jpg', NULL, NULL, NULL, '2024-04-09 17:00:00', '2025-12-13 16:31:32'),
(403, 28, 12, 3, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-28.jpg', NULL, NULL, NULL, '2024-06-05 17:00:00', '2025-12-13 16:31:32'),
(404, 28, 13, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-28.jpg', NULL, NULL, NULL, '2024-02-06 17:00:00', '2025-12-13 16:31:32'),
(405, 28, 16, 8, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-16-28.jpg', NULL, NULL, NULL, '2024-05-13 17:00:00', '2025-12-13 16:31:32'),
(406, 28, 10, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-10-28.jpg', NULL, NULL, NULL, '2024-08-02 17:00:00', '2025-12-13 16:31:32'),
(407, 28, 21, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-21-28.jpg', NULL, NULL, NULL, '2024-10-12 17:00:00', '2025-12-13 16:31:32'),
(408, 29, 13, 7, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-13-29.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:32'),
(409, 29, 20, 12, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-20-29.jpg', NULL, NULL, NULL, '2024-06-01 17:00:00', '2025-12-13 16:31:32'),
(410, 29, 12, 3, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-29.jpg', NULL, NULL, NULL, '2024-04-02 17:00:00', '2025-12-13 16:31:32'),
(411, 29, 11, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-29.jpg', NULL, NULL, NULL, '2024-09-25 17:00:00', '2025-12-13 16:31:32'),
(412, 29, 15, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-29.jpg', NULL, NULL, NULL, '2024-08-25 17:00:00', '2025-12-13 16:31:32'),
(413, 29, 23, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-23-29.jpg', NULL, NULL, NULL, '2024-06-16 17:00:00', '2025-12-13 16:31:32'),
(414, 29, 16, 12, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-16-29.jpg', NULL, NULL, NULL, '2024-02-17 17:00:00', '2025-12-13 16:31:32'),
(415, 29, 17, 2, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-17-29.jpg', NULL, NULL, NULL, '2024-06-30 17:00:00', '2025-12-13 16:31:32'),
(416, 29, 14, 2, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-14-29.jpg', NULL, NULL, NULL, '2024-01-20 17:00:00', '2025-12-13 16:31:32'),
(417, 29, 21, 5, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-21-29.jpg', NULL, NULL, NULL, '2024-10-10 17:00:00', '2025-12-13 16:31:32'),
(418, 29, 9, 3, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-9-29.jpg', NULL, NULL, NULL, '2024-12-16 17:00:00', '2025-12-13 16:31:32'),
(419, 29, 22, 4, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-22-29.jpg', NULL, NULL, NULL, '2024-09-16 17:00:00', '2025-12-13 16:31:32'),
(420, 29, 10, 10, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-10-29.jpg', NULL, NULL, NULL, '2024-08-06 17:00:00', '2025-12-13 16:31:32'),
(421, 29, 25, 3, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-25-29.jpg', NULL, NULL, NULL, '2024-05-31 17:00:00', '2025-12-13 16:31:32'),
(422, 29, 24, 5, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-24-29.jpg', NULL, NULL, NULL, '2024-01-13 17:00:00', '2025-12-13 16:31:32'),
(423, 29, 19, 5, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-29.jpg', NULL, NULL, NULL, '2024-04-05 17:00:00', '2025-12-13 16:31:32'),
(424, 29, 8, 2, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-8-29.jpg', NULL, NULL, NULL, '2024-11-13 17:00:00', '2025-12-13 16:31:32'),
(425, 29, 18, 9, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-18-29.jpg', NULL, NULL, NULL, '2024-04-24 17:00:00', '2025-12-13 16:31:32'),
(426, 30, 17, 9, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-17-30.jpg', NULL, NULL, NULL, '2024-06-14 17:00:00', '2025-12-13 16:31:32'),
(427, 30, 18, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-30.jpg', NULL, NULL, NULL, '2024-05-01 17:00:00', '2025-12-13 16:31:32'),
(428, 30, 9, 1, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-9-30.jpg', NULL, NULL, NULL, '2024-04-17 17:00:00', '2025-12-13 16:31:32'),
(429, 30, 23, 10, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-23-30.jpg', NULL, NULL, NULL, '2024-03-02 17:00:00', '2025-12-13 16:31:32'),
(430, 30, 25, 12, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-25-30.jpg', NULL, NULL, NULL, '2024-07-01 17:00:00', '2025-12-13 16:31:32'),
(431, 30, 12, 2, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-12-30.jpg', NULL, NULL, NULL, '2024-06-02 17:00:00', '2025-12-13 16:31:32'),
(432, 30, 16, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-30.jpg', NULL, NULL, NULL, '2024-12-27 17:00:00', '2025-12-13 16:31:32'),
(433, 30, 13, 11, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-13-30.jpg', NULL, NULL, NULL, '2024-05-15 17:00:00', '2025-12-13 16:31:32'),
(434, 30, 22, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-22-30.jpg', NULL, NULL, NULL, '2024-12-03 17:00:00', '2025-12-13 16:31:32'),
(435, 30, 19, 1, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-19-30.jpg', NULL, NULL, NULL, '2024-07-09 17:00:00', '2025-12-13 16:31:32'),
(436, 30, 21, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-21-30.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:32'),
(437, 30, 10, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-10-30.jpg', NULL, NULL, NULL, '2024-02-22 17:00:00', '2025-12-13 16:31:32'),
(438, 30, 14, 1, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-30.jpg', NULL, NULL, NULL, '2024-03-26 17:00:00', '2025-12-13 16:31:32'),
(439, 30, 11, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-30.jpg', NULL, NULL, NULL, '2024-02-16 17:00:00', '2025-12-13 16:31:32'),
(440, 30, 15, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-15-30.jpg', NULL, NULL, NULL, '2024-08-14 17:00:00', '2025-12-13 16:31:32'),
(441, 30, 20, 8, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-20-30.jpg', NULL, NULL, NULL, '2024-05-25 17:00:00', '2025-12-13 16:31:32'),
(442, 30, 26, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-26-30.jpg', NULL, NULL, NULL, '2024-02-19 17:00:00', '2025-12-13 16:31:32'),
(443, 30, 8, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-30.jpg', NULL, NULL, NULL, '2024-02-17 17:00:00', '2025-12-13 16:31:32'),
(444, 30, 24, 4, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-24-30.jpg', NULL, NULL, NULL, '2024-06-05 17:00:00', '2025-12-13 16:31:32'),
(445, 31, 9, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-9-31.jpg', NULL, NULL, NULL, '2024-07-22 17:00:00', '2025-12-13 16:31:32'),
(446, 31, 13, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-13-31.jpg', NULL, NULL, NULL, '2024-08-06 17:00:00', '2025-12-13 16:31:32'),
(447, 31, 14, 11, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-14-31.jpg', NULL, NULL, NULL, '2024-06-18 17:00:00', '2025-12-13 16:31:32'),
(448, 31, 12, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-31.jpg', NULL, NULL, NULL, '2024-06-12 17:00:00', '2025-12-13 16:31:32'),
(449, 31, 18, 8, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-18-31.jpg', NULL, NULL, NULL, '2024-06-08 17:00:00', '2025-12-13 16:31:32'),
(450, 31, 20, 9, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-20-31.jpg', NULL, NULL, NULL, '2024-07-22 17:00:00', '2025-12-13 16:31:32'),
(451, 31, 19, 10, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-31.jpg', NULL, NULL, NULL, '2024-08-24 17:00:00', '2025-12-13 16:31:32'),
(452, 31, 10, 4, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-10-31.jpg', NULL, NULL, NULL, '2024-02-18 17:00:00', '2025-12-13 16:31:32'),
(453, 31, 17, 7, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-17-31.jpg', NULL, NULL, NULL, '2024-01-25 17:00:00', '2025-12-13 16:31:32'),
(454, 31, 11, 9, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-11-31.jpg', NULL, NULL, NULL, '2024-05-16 17:00:00', '2025-12-13 16:31:32'),
(455, 31, 8, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-31.jpg', NULL, NULL, NULL, '2024-04-19 17:00:00', '2025-12-13 16:31:32'),
(456, 31, 16, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-31.jpg', NULL, NULL, NULL, '2024-02-11 17:00:00', '2025-12-13 16:31:32'),
(457, 31, 15, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-31.jpg', NULL, NULL, NULL, '2024-06-06 17:00:00', '2025-12-13 16:31:32'),
(458, 32, 12, 6, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-12-32.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:32'),
(459, 32, 9, 11, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-9-32.jpg', NULL, NULL, NULL, '2024-09-12 17:00:00', '2025-12-13 16:31:32'),
(460, 32, 8, 12, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-8-32.jpg', NULL, NULL, NULL, '2024-10-10 17:00:00', '2025-12-13 16:31:32'),
(461, 32, 10, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-10-32.jpg', NULL, NULL, NULL, '2024-04-07 17:00:00', '2025-12-13 16:31:32'),
(462, 32, 20, 6, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-20-32.jpg', NULL, NULL, NULL, '2024-01-13 17:00:00', '2025-12-13 16:31:32'),
(463, 32, 19, 10, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-19-32.jpg', NULL, NULL, NULL, '2024-12-08 17:00:00', '2025-12-13 16:31:32'),
(464, 32, 16, 6, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-16-32.jpg', NULL, NULL, NULL, '2024-06-06 17:00:00', '2025-12-13 16:31:32'),
(465, 32, 14, 6, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-14-32.jpg', NULL, NULL, NULL, '2024-05-10 17:00:00', '2025-12-13 16:31:32'),
(466, 32, 13, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-13-32.jpg', NULL, NULL, NULL, '2024-12-17 17:00:00', '2025-12-13 16:31:32'),
(467, 32, 21, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-21-32.jpg', NULL, NULL, NULL, '2024-07-24 17:00:00', '2025-12-13 16:31:32'),
(468, 32, 17, 12, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-17-32.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:32'),
(469, 32, 11, 6, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-11-32.jpg', NULL, NULL, NULL, '2024-11-12 17:00:00', '2025-12-13 16:31:32'),
(470, 32, 15, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-32.jpg', NULL, NULL, NULL, '2024-11-08 17:00:00', '2025-12-13 16:31:32'),
(471, 32, 18, 2, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-18-32.jpg', NULL, NULL, NULL, '2024-01-23 17:00:00', '2025-12-13 16:31:32'),
(472, 33, 8, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-33.jpg', NULL, NULL, NULL, '2024-06-14 17:00:00', '2025-12-13 16:31:32'),
(473, 33, 15, 10, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-15-33.jpg', NULL, NULL, NULL, '2024-03-13 17:00:00', '2025-12-13 16:31:32'),
(474, 33, 11, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-33.jpg', NULL, NULL, NULL, '2024-01-15 17:00:00', '2025-12-13 16:31:32'),
(475, 33, 12, 1, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-12-33.jpg', NULL, NULL, NULL, '2024-05-08 17:00:00', '2025-12-13 16:31:32'),
(476, 33, 13, 2, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-13-33.jpg', NULL, NULL, NULL, '2024-06-25 17:00:00', '2025-12-13 16:31:32'),
(477, 33, 17, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-33.jpg', NULL, NULL, NULL, '2024-11-05 17:00:00', '2025-12-13 16:31:32'),
(478, 33, 14, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-33.jpg', NULL, NULL, NULL, '2024-05-16 17:00:00', '2025-12-13 16:31:32'),
(479, 33, 10, 9, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-10-33.jpg', NULL, NULL, NULL, '2024-03-17 17:00:00', '2025-12-13 16:31:32'),
(480, 33, 19, 10, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-19-33.jpg', NULL, NULL, NULL, '2024-10-25 17:00:00', '2025-12-13 16:31:32'),
(481, 33, 18, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-18-33.jpg', NULL, NULL, NULL, '2024-06-07 17:00:00', '2025-12-13 16:31:32'),
(482, 33, 9, 12, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-9-33.jpg', NULL, NULL, NULL, '2024-07-10 17:00:00', '2025-12-13 16:31:32'),
(483, 33, 16, 8, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-16-33.jpg', NULL, NULL, NULL, '2024-12-24 17:00:00', '2025-12-13 16:31:32'),
(484, 34, 21, 11, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-21-34.jpg', NULL, NULL, NULL, '2024-12-15 17:00:00', '2025-12-13 16:31:32'),
(485, 34, 9, 1, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-9-34.jpg', NULL, NULL, NULL, '2024-04-20 17:00:00', '2025-12-13 16:31:32'),
(486, 34, 23, 12, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-23-34.jpg', NULL, NULL, NULL, '2024-06-02 17:00:00', '2025-12-13 16:31:32'),
(487, 34, 27, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-27-34.jpg', NULL, NULL, NULL, '2024-04-20 17:00:00', '2025-12-13 16:31:32'),
(488, 34, 12, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-34.jpg', NULL, NULL, NULL, '2024-12-18 17:00:00', '2025-12-13 16:31:32'),
(489, 34, 25, 1, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-25-34.jpg', NULL, NULL, NULL, '2024-10-06 17:00:00', '2025-12-13 16:31:32'),
(490, 34, 11, 8, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-11-34.jpg', NULL, NULL, NULL, '2024-12-16 17:00:00', '2025-12-13 16:31:32'),
(491, 34, 14, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-34.jpg', NULL, NULL, NULL, '2024-07-15 17:00:00', '2025-12-13 16:31:32'),
(492, 34, 22, 2, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-22-34.jpg', NULL, NULL, NULL, '2024-12-17 17:00:00', '2025-12-13 16:31:32'),
(493, 34, 8, 4, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-8-34.jpg', NULL, NULL, NULL, '2024-07-01 17:00:00', '2025-12-13 16:31:32'),
(494, 34, 10, 6, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-10-34.jpg', NULL, NULL, NULL, '2024-11-08 17:00:00', '2025-12-13 16:31:32'),
(495, 34, 17, 4, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-17-34.jpg', NULL, NULL, NULL, '2024-04-20 17:00:00', '2025-12-13 16:31:32'),
(496, 34, 15, 8, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-15-34.jpg', NULL, NULL, NULL, '2024-05-03 17:00:00', '2025-12-13 16:31:32'),
(497, 34, 13, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-34.jpg', NULL, NULL, NULL, '2024-12-27 17:00:00', '2025-12-13 16:31:32'),
(498, 34, 19, 4, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-34.jpg', NULL, NULL, NULL, '2024-03-16 17:00:00', '2025-12-13 16:31:32'),
(499, 34, 24, 2, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-24-34.jpg', NULL, NULL, NULL, '2024-03-02 17:00:00', '2025-12-13 16:31:32'),
(500, 34, 20, 7, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-20-34.jpg', NULL, NULL, NULL, '2024-12-19 17:00:00', '2025-12-13 16:31:32'),
(501, 34, 16, 4, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-16-34.jpg', NULL, NULL, NULL, '2024-11-10 17:00:00', '2025-12-13 16:31:32'),
(502, 34, 26, 5, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-26-34.jpg', NULL, NULL, NULL, '2024-01-15 17:00:00', '2025-12-13 16:31:32'),
(503, 34, 18, 3, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-18-34.jpg', NULL, NULL, NULL, '2024-10-04 17:00:00', '2025-12-13 16:31:32'),
(504, 35, 25, 11, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-25-35.jpg', NULL, NULL, NULL, '2024-09-09 17:00:00', '2025-12-13 16:31:32'),
(505, 35, 17, 12, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-17-35.jpg', NULL, NULL, NULL, '2024-01-26 17:00:00', '2025-12-13 16:31:32'),
(506, 35, 21, 6, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-21-35.jpg', NULL, NULL, NULL, '2024-02-03 17:00:00', '2025-12-13 16:31:32'),
(507, 35, 18, 9, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-18-35.jpg', NULL, NULL, NULL, '2024-07-19 17:00:00', '2025-12-13 16:31:32'),
(508, 35, 23, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-23-35.jpg', NULL, NULL, NULL, '2024-01-17 17:00:00', '2025-12-13 16:31:32'),
(509, 35, 12, 7, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-12-35.jpg', NULL, NULL, NULL, '2024-12-16 17:00:00', '2025-12-13 16:31:32'),
(510, 35, 22, 4, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-22-35.jpg', NULL, NULL, NULL, '2024-05-02 17:00:00', '2025-12-13 16:31:32'),
(511, 35, 9, 1, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-9-35.jpg', NULL, NULL, NULL, '2024-08-17 17:00:00', '2025-12-13 16:31:32'),
(512, 35, 8, 8, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-35.jpg', NULL, NULL, NULL, '2024-01-20 17:00:00', '2025-12-13 16:31:32'),
(513, 35, 13, 12, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-13-35.jpg', NULL, NULL, NULL, '2024-04-25 17:00:00', '2025-12-13 16:31:32'),
(514, 35, 16, 12, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-16-35.jpg', NULL, NULL, NULL, '2024-07-07 17:00:00', '2025-12-13 16:31:32'),
(515, 35, 19, 5, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-35.jpg', NULL, NULL, NULL, '2024-09-04 17:00:00', '2025-12-13 16:31:32'),
(516, 35, 11, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-35.jpg', NULL, NULL, NULL, '2024-03-21 17:00:00', '2025-12-13 16:31:32'),
(517, 35, 20, 6, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-20-35.jpg', NULL, NULL, NULL, '2024-08-21 17:00:00', '2025-12-13 16:31:32'),
(518, 35, 14, 8, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-14-35.jpg', NULL, NULL, NULL, '2024-06-02 17:00:00', '2025-12-13 16:31:32'),
(519, 35, 24, 6, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-24-35.jpg', NULL, NULL, NULL, '2024-02-24 17:00:00', '2025-12-13 16:31:32'),
(520, 35, 15, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-15-35.jpg', NULL, NULL, NULL, '2024-05-27 17:00:00', '2025-12-13 16:31:32'),
(521, 35, 10, 10, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-10-35.jpg', NULL, NULL, NULL, '2024-03-25 17:00:00', '2025-12-13 16:31:32'),
(522, 36, 17, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-36.jpg', NULL, NULL, NULL, '2024-09-26 17:00:00', '2025-12-13 16:31:32'),
(523, 36, 22, 12, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-22-36.jpg', NULL, NULL, NULL, '2024-07-04 17:00:00', '2025-12-13 16:31:32'),
(524, 36, 13, 10, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-13-36.jpg', NULL, NULL, NULL, '2024-07-27 17:00:00', '2025-12-13 16:31:32'),
(525, 36, 21, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-21-36.jpg', NULL, NULL, NULL, '2024-11-07 17:00:00', '2025-12-13 16:31:32'),
(526, 36, 12, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-36.jpg', NULL, NULL, NULL, '2024-02-12 17:00:00', '2025-12-13 16:31:32'),
(527, 36, 15, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-36.jpg', NULL, NULL, NULL, '2024-09-01 17:00:00', '2025-12-13 16:31:32'),
(528, 36, 8, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-8-36.jpg', NULL, NULL, NULL, '2024-09-22 17:00:00', '2025-12-13 16:31:32'),
(529, 36, 14, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-36.jpg', NULL, NULL, NULL, '2024-02-24 17:00:00', '2025-12-13 16:31:32'),
(530, 36, 18, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-36.jpg', NULL, NULL, NULL, '2024-12-08 17:00:00', '2025-12-13 16:31:32'),
(531, 36, 10, 7, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-10-36.jpg', NULL, NULL, NULL, '2024-01-06 17:00:00', '2025-12-13 16:31:32'),
(532, 36, 23, 7, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-23-36.jpg', NULL, NULL, NULL, '2024-10-19 17:00:00', '2025-12-13 16:31:32'),
(533, 36, 11, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-36.jpg', NULL, NULL, NULL, '2024-11-21 17:00:00', '2025-12-13 16:31:32'),
(534, 36, 20, 8, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-20-36.jpg', NULL, NULL, NULL, '2024-02-08 17:00:00', '2025-12-13 16:31:32'),
(535, 36, 9, 11, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-9-36.jpg', NULL, NULL, NULL, '2024-12-20 17:00:00', '2025-12-13 16:31:32'),
(536, 36, 19, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-36.jpg', NULL, NULL, NULL, '2024-01-22 17:00:00', '2025-12-13 16:31:32'),
(537, 36, 16, 7, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-16-36.jpg', NULL, NULL, NULL, '2024-12-05 17:00:00', '2025-12-13 16:31:33'),
(538, 37, 15, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-37.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(539, 37, 19, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-37.jpg', NULL, NULL, NULL, '2024-11-24 17:00:00', '2025-12-13 16:31:33'),
(540, 37, 20, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-20-37.jpg', NULL, NULL, NULL, '2024-05-11 17:00:00', '2025-12-13 16:31:33'),
(541, 37, 18, 6, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-18-37.jpg', NULL, NULL, NULL, '2024-07-09 17:00:00', '2025-12-13 16:31:33'),
(542, 37, 11, 1, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-11-37.jpg', NULL, NULL, NULL, '2024-10-21 17:00:00', '2025-12-13 16:31:33'),
(543, 37, 10, 2, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-10-37.jpg', NULL, NULL, NULL, '2024-05-11 17:00:00', '2025-12-13 16:31:33'),
(544, 37, 13, 11, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-13-37.jpg', NULL, NULL, NULL, '2024-01-24 17:00:00', '2025-12-13 16:31:33'),
(545, 37, 8, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-8-37.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(546, 37, 16, 1, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-16-37.jpg', NULL, NULL, NULL, '2024-05-17 17:00:00', '2025-12-13 16:31:33'),
(547, 37, 14, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-37.jpg', NULL, NULL, NULL, '2024-10-20 17:00:00', '2025-12-13 16:31:33'),
(548, 37, 12, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-37.jpg', NULL, NULL, NULL, '2024-12-26 17:00:00', '2025-12-13 16:31:33'),
(549, 37, 17, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-17-37.jpg', NULL, NULL, NULL, '2024-02-27 17:00:00', '2025-12-13 16:31:33'),
(550, 37, 9, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-9-37.jpg', NULL, NULL, NULL, '2024-10-31 17:00:00', '2025-12-13 16:31:33'),
(551, 38, 10, 4, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-10-38.jpg', NULL, NULL, NULL, '2024-06-20 17:00:00', '2025-12-13 16:31:33'),
(552, 38, 14, 10, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-14-38.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:33'),
(553, 38, 23, 5, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-23-38.jpg', NULL, NULL, NULL, '2024-08-20 17:00:00', '2025-12-13 16:31:33'),
(554, 38, 15, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-15-38.jpg', NULL, NULL, NULL, '2024-09-27 17:00:00', '2025-12-13 16:31:33'),
(555, 38, 8, 4, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-8-38.jpg', NULL, NULL, NULL, '2024-12-22 17:00:00', '2025-12-13 16:31:33'),
(556, 38, 13, 1, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-13-38.jpg', NULL, NULL, NULL, '2024-11-14 17:00:00', '2025-12-13 16:31:33'),
(557, 38, 24, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-24-38.jpg', NULL, NULL, NULL, '2024-12-11 17:00:00', '2025-12-13 16:31:33'),
(558, 38, 16, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-16-38.jpg', NULL, NULL, NULL, '2024-02-29 17:00:00', '2025-12-13 16:31:33'),
(559, 38, 17, 7, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-17-38.jpg', NULL, NULL, NULL, '2024-06-19 17:00:00', '2025-12-13 16:31:33'),
(560, 38, 25, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-25-38.jpg', NULL, NULL, NULL, '2024-11-15 17:00:00', '2025-12-13 16:31:33'),
(561, 38, 18, 12, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-18-38.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:33'),
(562, 38, 22, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-22-38.jpg', NULL, NULL, NULL, '2024-01-24 17:00:00', '2025-12-13 16:31:33'),
(563, 38, 21, 6, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-21-38.jpg', NULL, NULL, NULL, '2024-04-02 17:00:00', '2025-12-13 16:31:33'),
(564, 38, 20, 7, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-20-38.jpg', NULL, NULL, NULL, '2024-06-19 17:00:00', '2025-12-13 16:31:33'),
(565, 38, 12, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-38.jpg', NULL, NULL, NULL, '2024-07-15 17:00:00', '2025-12-13 16:31:33'),
(566, 38, 19, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-38.jpg', NULL, NULL, NULL, '2024-07-02 17:00:00', '2025-12-13 16:31:33'),
(567, 38, 9, 1, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-9-38.jpg', NULL, NULL, NULL, '2024-08-12 17:00:00', '2025-12-13 16:31:33'),
(568, 38, 11, 2, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-11-38.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:33'),
(569, 39, 15, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-39.jpg', NULL, NULL, NULL, '2024-11-07 17:00:00', '2025-12-13 16:31:33'),
(570, 39, 8, 5, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-8-39.jpg', NULL, NULL, NULL, '2024-11-23 17:00:00', '2025-12-13 16:31:33'),
(571, 39, 13, 12, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-13-39.jpg', NULL, NULL, NULL, '2024-11-24 17:00:00', '2025-12-13 16:31:33'),
(572, 39, 14, 7, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-39.jpg', NULL, NULL, NULL, '2024-10-07 17:00:00', '2025-12-13 16:31:33'),
(573, 39, 11, 11, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-11-39.jpg', NULL, NULL, NULL, '2024-11-07 17:00:00', '2025-12-13 16:31:33'),
(574, 39, 12, 4, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-12-39.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:33'),
(575, 39, 10, 7, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-10-39.jpg', NULL, NULL, NULL, '2024-08-19 17:00:00', '2025-12-13 16:31:33'),
(576, 39, 17, 6, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-17-39.jpg', NULL, NULL, NULL, '2024-12-26 17:00:00', '2025-12-13 16:31:33'),
(577, 39, 18, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-39.jpg', NULL, NULL, NULL, '2024-06-09 17:00:00', '2025-12-13 16:31:33'),
(578, 39, 9, 2, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-9-39.jpg', NULL, NULL, NULL, '2024-07-01 17:00:00', '2025-12-13 16:31:33'),
(579, 39, 16, 8, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-16-39.jpg', NULL, NULL, NULL, '2024-11-21 17:00:00', '2025-12-13 16:31:33'),
(580, 39, 19, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-39.jpg', NULL, NULL, NULL, '2024-11-06 17:00:00', '2025-12-13 16:31:33'),
(581, 40, 17, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-40.jpg', NULL, NULL, NULL, '2024-04-13 17:00:00', '2025-12-13 16:31:33'),
(582, 40, 15, 6, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-15-40.jpg', NULL, NULL, NULL, '2024-07-27 17:00:00', '2025-12-13 16:31:33'),
(583, 40, 12, 5, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-12-40.jpg', NULL, NULL, NULL, '2024-04-22 17:00:00', '2025-12-13 16:31:33'),
(584, 40, 9, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-9-40.jpg', NULL, NULL, NULL, '2024-09-08 17:00:00', '2025-12-13 16:31:33'),
(585, 40, 13, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-40.jpg', NULL, NULL, NULL, '2024-10-04 17:00:00', '2025-12-13 16:31:33'),
(586, 40, 10, 8, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-10-40.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:33'),
(587, 40, 16, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-16-40.jpg', NULL, NULL, NULL, '2024-08-03 17:00:00', '2025-12-13 16:31:33'),
(588, 40, 11, 8, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-11-40.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(589, 40, 18, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-40.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(590, 40, 8, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-40.jpg', NULL, NULL, NULL, '2024-04-25 17:00:00', '2025-12-13 16:31:33'),
(591, 40, 21, 8, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-21-40.jpg', NULL, NULL, NULL, '2024-06-03 17:00:00', '2025-12-13 16:31:33'),
(592, 40, 20, 8, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-20-40.jpg', NULL, NULL, NULL, '2024-10-11 17:00:00', '2025-12-13 16:31:33'),
(593, 40, 19, 10, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-19-40.jpg', NULL, NULL, NULL, '2024-11-22 17:00:00', '2025-12-13 16:31:33'),
(594, 40, 14, 10, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-14-40.jpg', NULL, NULL, NULL, '2024-07-17 17:00:00', '2025-12-13 16:31:33'),
(595, 41, 12, 12, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-41.jpg', NULL, NULL, NULL, '2024-09-07 17:00:00', '2025-12-13 16:31:33'),
(596, 41, 8, 9, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-8-41.jpg', NULL, NULL, NULL, '2024-12-06 17:00:00', '2025-12-13 16:31:33'),
(597, 41, 13, 5, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-13-41.jpg', NULL, NULL, NULL, '2024-06-08 17:00:00', '2025-12-13 16:31:33'),
(598, 41, 19, 6, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-19-41.jpg', NULL, NULL, NULL, '2024-02-01 17:00:00', '2025-12-13 16:31:33'),
(599, 41, 22, 11, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-22-41.jpg', NULL, NULL, NULL, '2024-03-08 17:00:00', '2025-12-13 16:31:33'),
(600, 41, 26, 3, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-26-41.jpg', NULL, NULL, NULL, '2024-04-05 17:00:00', '2025-12-13 16:31:33'),
(601, 41, 16, 10, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-16-41.jpg', NULL, NULL, NULL, '2024-05-20 17:00:00', '2025-12-13 16:31:33'),
(602, 41, 15, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-15-41.jpg', NULL, NULL, NULL, '2024-04-21 17:00:00', '2025-12-13 16:31:33'),
(603, 41, 23, 3, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-23-41.jpg', NULL, NULL, NULL, '2024-12-25 17:00:00', '2025-12-13 16:31:33'),
(604, 41, 9, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-9-41.jpg', NULL, NULL, NULL, '2024-02-06 17:00:00', '2025-12-13 16:31:33'),
(605, 41, 27, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-27-41.jpg', NULL, NULL, NULL, '2024-02-03 17:00:00', '2025-12-13 16:31:33'),
(606, 41, 20, 4, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-20-41.jpg', NULL, NULL, NULL, '2024-09-05 17:00:00', '2025-12-13 16:31:33'),
(607, 41, 11, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-41.jpg', NULL, NULL, NULL, '2024-05-16 17:00:00', '2025-12-13 16:31:33'),
(608, 41, 14, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-14-41.jpg', NULL, NULL, NULL, '2024-11-20 17:00:00', '2025-12-13 16:31:33'),
(609, 41, 24, 11, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-24-41.jpg', NULL, NULL, NULL, '2024-11-04 17:00:00', '2025-12-13 16:31:33'),
(610, 41, 21, 12, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-21-41.jpg', NULL, NULL, NULL, '2024-12-13 17:00:00', '2025-12-13 16:31:33'),
(611, 41, 10, 1, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-10-41.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(612, 41, 17, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-41.jpg', NULL, NULL, NULL, '2024-03-08 17:00:00', '2025-12-13 16:31:33'),
(613, 41, 18, 3, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-18-41.jpg', NULL, NULL, NULL, '2024-04-19 17:00:00', '2025-12-13 16:31:33'),
(614, 41, 25, 12, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-25-41.jpg', NULL, NULL, NULL, '2024-08-11 17:00:00', '2025-12-13 16:31:33'),
(615, 42, 18, 5, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-18-42.jpg', NULL, NULL, NULL, '2024-05-08 17:00:00', '2025-12-13 16:31:33'),
(616, 42, 20, 10, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-20-42.jpg', NULL, NULL, NULL, '2024-05-06 17:00:00', '2025-12-13 16:31:33'),
(617, 42, 8, 3, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-8-42.jpg', NULL, NULL, NULL, '2024-06-01 17:00:00', '2025-12-13 16:31:33'),
(618, 42, 19, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-42.jpg', NULL, NULL, NULL, '2024-08-22 17:00:00', '2025-12-13 16:31:33'),
(619, 42, 16, 7, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-16-42.jpg', NULL, NULL, NULL, '2024-01-21 17:00:00', '2025-12-13 16:31:33'),
(620, 42, 12, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-42.jpg', NULL, NULL, NULL, '2024-08-24 17:00:00', '2025-12-13 16:31:33'),
(621, 42, 11, 4, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-11-42.jpg', NULL, NULL, NULL, '2024-09-30 17:00:00', '2025-12-13 16:31:33'),
(622, 42, 10, 4, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-10-42.jpg', NULL, NULL, NULL, '2024-10-07 17:00:00', '2025-12-13 16:31:33'),
(623, 42, 17, 2, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-17-42.jpg', NULL, NULL, NULL, '2024-06-10 17:00:00', '2025-12-13 16:31:33'),
(624, 42, 9, 4, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-9-42.jpg', NULL, NULL, NULL, '2024-03-13 17:00:00', '2025-12-13 16:31:33'),
(625, 42, 13, 8, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-13-42.jpg', NULL, NULL, NULL, '2024-10-25 17:00:00', '2025-12-13 16:31:33'),
(626, 42, 15, 3, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-15-42.jpg', NULL, NULL, NULL, '2024-06-20 17:00:00', '2025-12-13 16:31:33'),
(627, 42, 14, 10, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-14-42.jpg', NULL, NULL, NULL, '2024-05-31 17:00:00', '2025-12-13 16:31:33'),
(628, 43, 11, 7, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-11-43.jpg', NULL, NULL, NULL, '2024-02-03 17:00:00', '2025-12-13 16:31:33'),
(629, 43, 9, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-9-43.jpg', NULL, NULL, NULL, '2024-01-24 17:00:00', '2025-12-13 16:31:33'),
(630, 43, 16, 7, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-16-43.jpg', NULL, NULL, NULL, '2024-11-08 17:00:00', '2025-12-13 16:31:33'),
(631, 43, 8, 2, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-8-43.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:33'),
(632, 43, 18, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-43.jpg', NULL, NULL, NULL, '2024-11-09 17:00:00', '2025-12-13 16:31:33'),
(633, 43, 15, 4, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-15-43.jpg', NULL, NULL, NULL, '2024-02-20 17:00:00', '2025-12-13 16:31:33'),
(634, 43, 17, 7, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-17-43.jpg', NULL, NULL, NULL, '2024-12-03 17:00:00', '2025-12-13 16:31:33'),
(635, 43, 20, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-20-43.jpg', NULL, NULL, NULL, '2024-07-10 17:00:00', '2025-12-13 16:31:33'),
(636, 43, 10, 6, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-10-43.jpg', NULL, NULL, NULL, '2024-06-14 17:00:00', '2025-12-13 16:31:33'),
(637, 43, 13, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-43.jpg', NULL, NULL, NULL, '2024-11-17 17:00:00', '2025-12-13 16:31:33'),
(638, 43, 12, 3, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-12-43.jpg', NULL, NULL, NULL, '2024-05-16 17:00:00', '2025-12-13 16:31:33'),
(639, 43, 14, 5, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-14-43.jpg', NULL, NULL, NULL, '2024-02-22 17:00:00', '2025-12-13 16:31:33'),
(640, 43, 19, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-43.jpg', NULL, NULL, NULL, '2024-09-22 17:00:00', '2025-12-13 16:31:33'),
(641, 43, 21, 7, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-21-43.jpg', NULL, NULL, NULL, '2024-08-23 17:00:00', '2025-12-13 16:31:33'),
(642, 44, 13, 2, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-13-44.jpg', NULL, NULL, NULL, '2024-03-21 17:00:00', '2025-12-13 16:31:33'),
(643, 44, 8, 8, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-8-44.jpg', NULL, NULL, NULL, '2024-06-03 17:00:00', '2025-12-13 16:31:33'),
(644, 44, 10, 2, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-10-44.jpg', NULL, NULL, NULL, '2024-01-09 17:00:00', '2025-12-13 16:31:33'),
(645, 44, 14, 11, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-14-44.jpg', NULL, NULL, NULL, '2024-12-15 17:00:00', '2025-12-13 16:31:33'),
(646, 44, 25, 12, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-25-44.jpg', NULL, NULL, NULL, '2024-07-09 17:00:00', '2025-12-13 16:31:33'),
(647, 44, 26, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-26-44.jpg', NULL, NULL, NULL, '2024-09-04 17:00:00', '2025-12-13 16:31:33'),
(648, 44, 20, 8, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-20-44.jpg', NULL, NULL, NULL, '2024-11-23 17:00:00', '2025-12-13 16:31:33'),
(649, 44, 22, 5, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-22-44.jpg', NULL, NULL, NULL, '2024-09-25 17:00:00', '2025-12-13 16:31:33'),
(650, 44, 21, 12, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-21-44.jpg', NULL, NULL, NULL, '2024-03-23 17:00:00', '2025-12-13 16:31:33'),
(651, 44, 17, 2, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-17-44.jpg', NULL, NULL, NULL, '2024-03-02 17:00:00', '2025-12-13 16:31:33'),
(652, 44, 16, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-16-44.jpg', NULL, NULL, NULL, '2024-07-27 17:00:00', '2025-12-13 16:31:33'),
(653, 44, 24, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-24-44.jpg', NULL, NULL, NULL, '2024-02-08 17:00:00', '2025-12-13 16:31:33'),
(654, 44, 23, 8, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-23-44.jpg', NULL, NULL, NULL, '2024-06-03 17:00:00', '2025-12-13 16:31:33'),
(655, 44, 9, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-9-44.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(656, 44, 15, 4, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-15-44.jpg', NULL, NULL, NULL, '2024-10-06 17:00:00', '2025-12-13 16:31:33'),
(657, 44, 19, 3, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-19-44.jpg', NULL, NULL, NULL, '2024-12-26 17:00:00', '2025-12-13 16:31:33'),
(658, 44, 12, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-12-44.jpg', NULL, NULL, NULL, '2024-09-15 17:00:00', '2025-12-13 16:31:33'),
(659, 44, 18, 2, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-18-44.jpg', NULL, NULL, NULL, '2024-05-23 17:00:00', '2025-12-13 16:31:33'),
(660, 44, 11, 8, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-11-44.jpg', NULL, NULL, NULL, '2024-09-08 17:00:00', '2025-12-13 16:31:33'),
(661, 45, 13, 3, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-13-45.jpg', NULL, NULL, NULL, '2024-07-22 17:00:00', '2025-12-13 16:31:33'),
(662, 45, 11, 2, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-11-45.jpg', NULL, NULL, NULL, '2024-09-04 17:00:00', '2025-12-13 16:31:33'),
(663, 45, 12, 4, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-45.jpg', NULL, NULL, NULL, '2024-05-07 17:00:00', '2025-12-13 16:31:33'),
(664, 45, 21, 11, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-21-45.jpg', NULL, NULL, NULL, '2024-12-02 17:00:00', '2025-12-13 16:31:33'),
(665, 45, 20, 5, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-20-45.jpg', NULL, NULL, NULL, '2024-07-09 17:00:00', '2025-12-13 16:31:33'),
(666, 45, 8, 4, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-8-45.jpg', NULL, NULL, NULL, '2024-06-12 17:00:00', '2025-12-13 16:31:33'),
(667, 45, 9, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-45.jpg', NULL, NULL, NULL, '2024-07-05 17:00:00', '2025-12-13 16:31:33'),
(668, 45, 14, 8, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-45.jpg', NULL, NULL, NULL, '2024-11-10 17:00:00', '2025-12-13 16:31:33'),
(669, 45, 15, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-45.jpg', NULL, NULL, NULL, '2024-08-08 17:00:00', '2025-12-13 16:31:33'),
(670, 45, 17, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-45.jpg', NULL, NULL, NULL, '2024-09-02 17:00:00', '2025-12-13 16:31:33'),
(671, 45, 16, 9, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-45.jpg', NULL, NULL, NULL, '2024-02-14 17:00:00', '2025-12-13 16:31:33'),
(672, 45, 10, 8, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-10-45.jpg', NULL, NULL, NULL, '2024-09-20 17:00:00', '2025-12-13 16:31:33'),
(673, 45, 18, 2, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-18-45.jpg', NULL, NULL, NULL, '2024-03-11 17:00:00', '2025-12-13 16:31:33');
INSERT INTO `event_registrations` (`id`, `event_id`, `pembalap_user_id`, `kis_category_id`, `result_position`, `result_status`, `points_earned`, `status`, `payment_proof_url`, `admin_note`, `payment_processed_at`, `payment_processed_by_user_id`, `created_at`, `updated_at`) VALUES
(674, 45, 19, 6, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-45.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:33'),
(675, 46, 19, 10, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-19-46.jpg', NULL, NULL, NULL, '2024-04-04 17:00:00', '2025-12-13 16:31:33'),
(676, 46, 11, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-46.jpg', NULL, NULL, NULL, '2024-09-25 17:00:00', '2025-12-13 16:31:33'),
(677, 46, 8, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-46.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:33'),
(678, 46, 14, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-46.jpg', NULL, NULL, NULL, '2024-03-26 17:00:00', '2025-12-13 16:31:33'),
(679, 46, 20, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-46.jpg', NULL, NULL, NULL, '2024-10-21 17:00:00', '2025-12-13 16:31:33'),
(680, 46, 10, 7, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-10-46.jpg', NULL, NULL, NULL, '2024-04-17 17:00:00', '2025-12-13 16:31:33'),
(681, 46, 13, 10, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-13-46.jpg', NULL, NULL, NULL, '2024-02-12 17:00:00', '2025-12-13 16:31:33'),
(682, 46, 17, 3, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-17-46.jpg', NULL, NULL, NULL, '2024-01-22 17:00:00', '2025-12-13 16:31:33'),
(683, 46, 15, 8, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-15-46.jpg', NULL, NULL, NULL, '2024-10-16 17:00:00', '2025-12-13 16:31:33'),
(684, 46, 25, 9, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-25-46.jpg', NULL, NULL, NULL, '2024-03-27 17:00:00', '2025-12-13 16:31:33'),
(685, 46, 23, 4, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-23-46.jpg', NULL, NULL, NULL, '2024-04-21 17:00:00', '2025-12-13 16:31:33'),
(686, 46, 22, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-22-46.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:33'),
(687, 46, 12, 9, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-12-46.jpg', NULL, NULL, NULL, '2024-01-20 17:00:00', '2025-12-13 16:31:33'),
(688, 46, 24, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-24-46.jpg', NULL, NULL, NULL, '2024-02-27 17:00:00', '2025-12-13 16:31:33'),
(689, 46, 9, 3, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-9-46.jpg', NULL, NULL, NULL, '2024-09-19 17:00:00', '2025-12-13 16:31:33'),
(690, 46, 26, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-26-46.jpg', NULL, NULL, NULL, '2024-08-04 17:00:00', '2025-12-13 16:31:33'),
(691, 46, 16, 1, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-16-46.jpg', NULL, NULL, NULL, '2024-05-20 17:00:00', '2025-12-13 16:31:33'),
(692, 46, 18, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-46.jpg', NULL, NULL, NULL, '2024-09-03 17:00:00', '2025-12-13 16:31:33'),
(693, 46, 21, 6, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-21-46.jpg', NULL, NULL, NULL, '2024-02-24 17:00:00', '2025-12-13 16:31:33'),
(694, 46, 27, 12, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-27-46.jpg', NULL, NULL, NULL, '2024-05-11 17:00:00', '2025-12-13 16:31:33'),
(695, 47, 13, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-13-47.jpg', NULL, NULL, NULL, '2024-06-03 17:00:00', '2025-12-13 16:31:33'),
(696, 47, 9, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-47.jpg', NULL, NULL, NULL, '2024-10-20 17:00:00', '2025-12-13 16:31:33'),
(697, 47, 19, 9, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-19-47.jpg', NULL, NULL, NULL, '2024-03-12 17:00:00', '2025-12-13 16:31:33'),
(698, 47, 17, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-17-47.jpg', NULL, NULL, NULL, '2024-12-14 17:00:00', '2025-12-13 16:31:33'),
(699, 47, 14, 6, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-47.jpg', NULL, NULL, NULL, '2024-11-26 17:00:00', '2025-12-13 16:31:33'),
(700, 47, 8, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-8-47.jpg', NULL, NULL, NULL, '2024-05-12 17:00:00', '2025-12-13 16:31:33'),
(701, 47, 10, 11, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-10-47.jpg', NULL, NULL, NULL, '2024-06-20 17:00:00', '2025-12-13 16:31:33'),
(702, 47, 11, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-11-47.jpg', NULL, NULL, NULL, '2024-04-19 17:00:00', '2025-12-13 16:31:33'),
(703, 47, 16, 7, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-16-47.jpg', NULL, NULL, NULL, '2024-02-09 17:00:00', '2025-12-13 16:31:33'),
(704, 47, 15, 9, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-15-47.jpg', NULL, NULL, NULL, '2024-05-19 17:00:00', '2025-12-13 16:31:33'),
(705, 47, 12, 2, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-12-47.jpg', NULL, NULL, NULL, '2024-02-04 17:00:00', '2025-12-13 16:31:33'),
(706, 47, 18, 12, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-18-47.jpg', NULL, NULL, NULL, '2024-09-16 17:00:00', '2025-12-13 16:31:33'),
(707, 48, 19, 3, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-19-48.jpg', NULL, NULL, NULL, '2024-01-10 17:00:00', '2025-12-13 16:31:33'),
(708, 48, 17, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-48.jpg', NULL, NULL, NULL, '2024-01-22 17:00:00', '2025-12-13 16:31:33'),
(709, 48, 12, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-48.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:33'),
(710, 48, 11, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-11-48.jpg', NULL, NULL, NULL, '2024-01-18 17:00:00', '2025-12-13 16:31:33'),
(711, 48, 15, 5, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-15-48.jpg', NULL, NULL, NULL, '2024-11-25 17:00:00', '2025-12-13 16:31:33'),
(712, 48, 9, 5, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-9-48.jpg', NULL, NULL, NULL, '2024-01-03 17:00:00', '2025-12-13 16:31:33'),
(713, 48, 20, 1, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-20-48.jpg', NULL, NULL, NULL, '2024-01-25 17:00:00', '2025-12-13 16:31:33'),
(714, 48, 16, 1, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-16-48.jpg', NULL, NULL, NULL, '2024-12-06 17:00:00', '2025-12-13 16:31:33'),
(715, 48, 14, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-48.jpg', NULL, NULL, NULL, '2024-06-10 17:00:00', '2025-12-13 16:31:33'),
(716, 48, 8, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-8-48.jpg', NULL, NULL, NULL, '2024-09-23 17:00:00', '2025-12-13 16:31:33'),
(717, 48, 13, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-13-48.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:33'),
(718, 48, 10, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-10-48.jpg', NULL, NULL, NULL, '2024-10-23 17:00:00', '2025-12-13 16:31:33'),
(719, 48, 18, 2, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-18-48.jpg', NULL, NULL, NULL, '2024-06-22 17:00:00', '2025-12-13 16:31:33'),
(720, 49, 23, 12, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-23-49.jpg', NULL, NULL, NULL, '2024-11-30 17:00:00', '2025-12-13 16:31:33'),
(721, 49, 17, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-49.jpg', NULL, NULL, NULL, '2024-04-24 17:00:00', '2025-12-13 16:31:33'),
(722, 49, 12, 7, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-49.jpg', NULL, NULL, NULL, '2024-04-18 17:00:00', '2025-12-13 16:31:33'),
(723, 49, 9, 12, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-9-49.jpg', NULL, NULL, NULL, '2024-02-13 17:00:00', '2025-12-13 16:31:33'),
(724, 49, 8, 1, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-8-49.jpg', NULL, NULL, NULL, '2024-05-17 17:00:00', '2025-12-13 16:31:33'),
(725, 49, 13, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-13-49.jpg', NULL, NULL, NULL, '2024-12-04 17:00:00', '2025-12-13 16:31:33'),
(726, 49, 14, 6, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-14-49.jpg', NULL, NULL, NULL, '2024-03-01 17:00:00', '2025-12-13 16:31:33'),
(727, 49, 24, 7, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-24-49.jpg', NULL, NULL, NULL, '2024-11-22 17:00:00', '2025-12-13 16:31:33'),
(728, 49, 18, 8, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-18-49.jpg', NULL, NULL, NULL, '2024-08-21 17:00:00', '2025-12-13 16:31:33'),
(729, 49, 22, 10, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-22-49.jpg', NULL, NULL, NULL, '2024-12-11 17:00:00', '2025-12-13 16:31:33'),
(730, 49, 19, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-49.jpg', NULL, NULL, NULL, '2024-04-15 17:00:00', '2025-12-13 16:31:33'),
(731, 49, 21, 3, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-21-49.jpg', NULL, NULL, NULL, '2024-08-06 17:00:00', '2025-12-13 16:31:33'),
(732, 49, 10, 7, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-10-49.jpg', NULL, NULL, NULL, '2024-11-15 17:00:00', '2025-12-13 16:31:33'),
(733, 49, 11, 3, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-11-49.jpg', NULL, NULL, NULL, '2024-03-24 17:00:00', '2025-12-13 16:31:33'),
(734, 49, 20, 10, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-20-49.jpg', NULL, NULL, NULL, '2024-12-04 17:00:00', '2025-12-13 16:31:33'),
(735, 49, 15, 11, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-15-49.jpg', NULL, NULL, NULL, '2024-01-06 17:00:00', '2025-12-13 16:31:33'),
(736, 49, 16, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-49.jpg', NULL, NULL, NULL, '2024-07-20 17:00:00', '2025-12-13 16:31:33'),
(737, 50, 16, 4, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-16-50.jpg', NULL, NULL, NULL, '2024-09-01 17:00:00', '2025-12-13 16:31:33'),
(738, 50, 17, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-50.jpg', NULL, NULL, NULL, '2024-11-18 17:00:00', '2025-12-13 16:31:33'),
(739, 50, 12, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-12-50.jpg', NULL, NULL, NULL, '2024-11-27 17:00:00', '2025-12-13 16:31:33'),
(740, 50, 19, 11, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-50.jpg', NULL, NULL, NULL, '2024-06-13 17:00:00', '2025-12-13 16:31:33'),
(741, 50, 14, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-14-50.jpg', NULL, NULL, NULL, '2024-02-09 17:00:00', '2025-12-13 16:31:33'),
(742, 50, 8, 9, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-8-50.jpg', NULL, NULL, NULL, '2024-08-06 17:00:00', '2025-12-13 16:31:33'),
(743, 50, 10, 8, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-10-50.jpg', NULL, NULL, NULL, '2024-06-10 17:00:00', '2025-12-13 16:31:33'),
(744, 50, 9, 10, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-9-50.jpg', NULL, NULL, NULL, '2024-07-04 17:00:00', '2025-12-13 16:31:33'),
(745, 50, 15, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-50.jpg', NULL, NULL, NULL, '2024-05-13 17:00:00', '2025-12-13 16:31:33'),
(746, 50, 18, 10, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-50.jpg', NULL, NULL, NULL, '2024-09-18 17:00:00', '2025-12-13 16:31:33'),
(747, 50, 11, 1, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-11-50.jpg', NULL, NULL, NULL, '2024-06-04 17:00:00', '2025-12-13 16:31:33'),
(748, 50, 13, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-50.jpg', NULL, NULL, NULL, '2024-05-25 17:00:00', '2025-12-13 16:31:33'),
(749, 51, 20, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-20-51.jpg', NULL, NULL, NULL, '2024-03-07 17:00:00', '2025-12-13 16:31:33'),
(750, 51, 18, 4, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-51.jpg', NULL, NULL, NULL, '2024-03-24 17:00:00', '2025-12-13 16:31:33'),
(751, 51, 12, 6, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-12-51.jpg', NULL, NULL, NULL, '2024-07-04 17:00:00', '2025-12-13 16:31:33'),
(752, 51, 14, 11, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-14-51.jpg', NULL, NULL, NULL, '2024-07-14 17:00:00', '2025-12-13 16:31:33'),
(753, 51, 10, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-10-51.jpg', NULL, NULL, NULL, '2024-03-25 17:00:00', '2025-12-13 16:31:33'),
(754, 51, 16, 4, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-51.jpg', NULL, NULL, NULL, '2024-01-04 17:00:00', '2025-12-13 16:31:33'),
(755, 51, 9, 12, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-9-51.jpg', NULL, NULL, NULL, '2024-06-16 17:00:00', '2025-12-13 16:31:33'),
(756, 51, 21, 9, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-21-51.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:33'),
(757, 51, 11, 8, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-11-51.jpg', NULL, NULL, NULL, '2024-08-16 17:00:00', '2025-12-13 16:31:33'),
(758, 51, 8, 6, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-8-51.jpg', NULL, NULL, NULL, '2024-02-21 17:00:00', '2025-12-13 16:31:33'),
(759, 51, 17, 10, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-17-51.jpg', NULL, NULL, NULL, '2024-09-10 17:00:00', '2025-12-13 16:31:33'),
(760, 51, 13, 11, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-13-51.jpg', NULL, NULL, NULL, '2024-09-22 17:00:00', '2025-12-13 16:31:33'),
(761, 51, 23, 5, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-23-51.jpg', NULL, NULL, NULL, '2024-09-27 17:00:00', '2025-12-13 16:31:33'),
(762, 51, 15, 6, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-15-51.jpg', NULL, NULL, NULL, '2024-11-06 17:00:00', '2025-12-13 16:31:33'),
(763, 51, 22, 2, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-22-51.jpg', NULL, NULL, NULL, '2024-08-17 17:00:00', '2025-12-13 16:31:33'),
(764, 51, 19, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-19-51.jpg', NULL, NULL, NULL, '2024-07-04 17:00:00', '2025-12-13 16:31:33'),
(765, 52, 16, 9, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-16-52.jpg', NULL, NULL, NULL, '2024-06-25 17:00:00', '2025-12-13 16:31:33'),
(766, 52, 11, 7, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-11-52.jpg', NULL, NULL, NULL, '2024-01-13 17:00:00', '2025-12-13 16:31:33'),
(767, 52, 20, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-52.jpg', NULL, NULL, NULL, '2024-01-05 17:00:00', '2025-12-13 16:31:33'),
(768, 52, 18, 10, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-18-52.jpg', NULL, NULL, NULL, '2024-05-11 17:00:00', '2025-12-13 16:31:33'),
(769, 52, 13, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-13-52.jpg', NULL, NULL, NULL, '2024-07-19 17:00:00', '2025-12-13 16:31:33'),
(770, 52, 17, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-52.jpg', NULL, NULL, NULL, '2024-05-12 17:00:00', '2025-12-13 16:31:33'),
(771, 52, 22, 12, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-22-52.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:33'),
(772, 52, 15, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-15-52.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:33'),
(773, 52, 19, 9, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-19-52.jpg', NULL, NULL, NULL, '2024-10-21 17:00:00', '2025-12-13 16:31:33'),
(774, 52, 21, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-21-52.jpg', NULL, NULL, NULL, '2024-03-22 17:00:00', '2025-12-13 16:31:33'),
(775, 52, 10, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-10-52.jpg', NULL, NULL, NULL, '2024-12-09 17:00:00', '2025-12-13 16:31:33'),
(776, 52, 12, 6, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-12-52.jpg', NULL, NULL, NULL, '2024-02-29 17:00:00', '2025-12-13 16:31:33'),
(777, 52, 8, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-52.jpg', NULL, NULL, NULL, '2024-09-27 17:00:00', '2025-12-13 16:31:33'),
(778, 52, 14, 4, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-14-52.jpg', NULL, NULL, NULL, '2024-10-24 17:00:00', '2025-12-13 16:31:33'),
(779, 52, 9, 5, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-9-52.jpg', NULL, NULL, NULL, '2024-09-24 17:00:00', '2025-12-13 16:31:33'),
(780, 53, 20, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-53.jpg', NULL, NULL, NULL, '2024-04-07 17:00:00', '2025-12-13 16:31:33'),
(781, 53, 23, 5, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-23-53.jpg', NULL, NULL, NULL, '2024-07-08 17:00:00', '2025-12-13 16:31:33'),
(782, 53, 9, 2, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-9-53.jpg', NULL, NULL, NULL, '2024-06-26 17:00:00', '2025-12-13 16:31:33'),
(783, 53, 11, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-53.jpg', NULL, NULL, NULL, '2024-09-25 17:00:00', '2025-12-13 16:31:33'),
(784, 53, 21, 10, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-21-53.jpg', NULL, NULL, NULL, '2024-04-06 17:00:00', '2025-12-13 16:31:33'),
(785, 53, 22, 9, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-22-53.jpg', NULL, NULL, NULL, '2024-02-06 17:00:00', '2025-12-13 16:31:33'),
(786, 53, 17, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-17-53.jpg', NULL, NULL, NULL, '2024-02-25 17:00:00', '2025-12-13 16:31:33'),
(787, 53, 18, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-18-53.jpg', NULL, NULL, NULL, '2023-12-31 17:00:00', '2025-12-13 16:31:33'),
(788, 53, 19, 3, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-19-53.jpg', NULL, NULL, NULL, '2024-06-25 17:00:00', '2025-12-13 16:31:33'),
(789, 53, 15, 1, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-15-53.jpg', NULL, NULL, NULL, '2024-11-12 17:00:00', '2025-12-13 16:31:33'),
(790, 53, 12, 3, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-53.jpg', NULL, NULL, NULL, '2024-01-15 17:00:00', '2025-12-13 16:31:33'),
(791, 53, 14, 8, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-14-53.jpg', NULL, NULL, NULL, '2024-04-30 17:00:00', '2025-12-13 16:31:33'),
(792, 53, 16, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-53.jpg', NULL, NULL, NULL, '2024-05-02 17:00:00', '2025-12-13 16:31:33'),
(793, 53, 10, 1, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-10-53.jpg', NULL, NULL, NULL, '2024-02-13 17:00:00', '2025-12-13 16:31:33'),
(794, 53, 8, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-53.jpg', NULL, NULL, NULL, '2024-05-20 17:00:00', '2025-12-13 16:31:33'),
(795, 53, 13, 7, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-13-53.jpg', NULL, NULL, NULL, '2024-06-12 17:00:00', '2025-12-13 16:31:33'),
(796, 54, 12, 11, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-12-54.jpg', NULL, NULL, NULL, '2024-06-05 17:00:00', '2025-12-13 16:31:33'),
(797, 54, 24, 4, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-24-54.jpg', NULL, NULL, NULL, '2024-03-24 17:00:00', '2025-12-13 16:31:33'),
(798, 54, 25, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-25-54.jpg', NULL, NULL, NULL, '2024-12-01 17:00:00', '2025-12-13 16:31:33'),
(799, 54, 18, 9, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-18-54.jpg', NULL, NULL, NULL, '2024-09-08 17:00:00', '2025-12-13 16:31:33'),
(800, 54, 11, 2, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-11-54.jpg', NULL, NULL, NULL, '2024-10-01 17:00:00', '2025-12-13 16:31:33'),
(801, 54, 22, 12, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-22-54.jpg', NULL, NULL, NULL, '2024-08-19 17:00:00', '2025-12-13 16:31:33'),
(802, 54, 17, 1, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-17-54.jpg', NULL, NULL, NULL, '2024-03-19 17:00:00', '2025-12-13 16:31:33'),
(803, 54, 15, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-15-54.jpg', NULL, NULL, NULL, '2024-11-06 17:00:00', '2025-12-13 16:31:33'),
(804, 54, 21, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-21-54.jpg', NULL, NULL, NULL, '2024-08-05 17:00:00', '2025-12-13 16:31:33'),
(805, 54, 20, 12, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-20-54.jpg', NULL, NULL, NULL, '2024-12-06 17:00:00', '2025-12-13 16:31:33'),
(806, 54, 8, 4, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-8-54.jpg', NULL, NULL, NULL, '2024-10-03 17:00:00', '2025-12-13 16:31:33'),
(807, 54, 10, 2, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-10-54.jpg', NULL, NULL, NULL, '2024-09-16 17:00:00', '2025-12-13 16:31:33'),
(808, 54, 13, 6, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-13-54.jpg', NULL, NULL, NULL, '2024-06-24 17:00:00', '2025-12-13 16:31:33'),
(809, 54, 14, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-54.jpg', NULL, NULL, NULL, '2024-11-14 17:00:00', '2025-12-13 16:31:33'),
(810, 54, 23, 2, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-23-54.jpg', NULL, NULL, NULL, '2024-05-26 17:00:00', '2025-12-13 16:31:33'),
(811, 54, 9, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-54.jpg', NULL, NULL, NULL, '2024-01-22 17:00:00', '2025-12-13 16:31:33'),
(812, 54, 16, 11, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-16-54.jpg', NULL, NULL, NULL, '2024-11-16 17:00:00', '2025-12-13 16:31:33'),
(813, 54, 19, 4, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-19-54.jpg', NULL, NULL, NULL, '2024-01-04 17:00:00', '2025-12-13 16:31:33'),
(814, 55, 23, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-23-55.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:33'),
(815, 55, 16, 10, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-16-55.jpg', NULL, NULL, NULL, '2024-11-04 17:00:00', '2025-12-13 16:31:33'),
(816, 55, 13, 3, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-13-55.jpg', NULL, NULL, NULL, '2024-04-15 17:00:00', '2025-12-13 16:31:33'),
(817, 55, 9, 11, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-9-55.jpg', NULL, NULL, NULL, '2024-08-01 17:00:00', '2025-12-13 16:31:33'),
(818, 55, 12, 9, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-12-55.jpg', NULL, NULL, NULL, '2024-06-18 17:00:00', '2025-12-13 16:31:33'),
(819, 55, 11, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-11-55.jpg', NULL, NULL, NULL, '2024-07-16 17:00:00', '2025-12-13 16:31:33'),
(820, 55, 10, 4, NULL, NULL, 0, 'Confirmed', 'payment-proofs/dummy-2024-10-55.jpg', NULL, NULL, NULL, '2024-05-21 17:00:00', '2025-12-13 16:31:33'),
(821, 55, 18, 5, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-18-55.jpg', NULL, NULL, NULL, '2024-07-01 17:00:00', '2025-12-13 16:31:33'),
(822, 55, 22, 8, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-22-55.jpg', NULL, NULL, NULL, '2024-07-15 17:00:00', '2025-12-13 16:31:33'),
(823, 55, 8, 12, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-8-55.jpg', NULL, NULL, NULL, '2024-05-02 17:00:00', '2025-12-13 16:31:33'),
(824, 55, 19, 3, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-19-55.jpg', NULL, NULL, NULL, '2024-09-16 17:00:00', '2025-12-13 16:31:33'),
(825, 55, 14, 12, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-14-55.jpg', NULL, NULL, NULL, '2024-11-26 17:00:00', '2025-12-13 16:31:33'),
(826, 55, 20, 10, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-20-55.jpg', NULL, NULL, NULL, '2024-09-26 17:00:00', '2025-12-13 16:31:33'),
(827, 55, 21, 2, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-21-55.jpg', NULL, NULL, NULL, '2024-03-22 17:00:00', '2025-12-13 16:31:33'),
(828, 55, 17, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-55.jpg', NULL, NULL, NULL, '2024-08-23 17:00:00', '2025-12-13 16:31:33'),
(829, 55, 15, 3, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-15-55.jpg', NULL, NULL, NULL, '2024-04-12 17:00:00', '2025-12-13 16:31:33'),
(830, 56, 17, 1, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-17-56.jpg', NULL, NULL, NULL, '2024-10-16 17:00:00', '2025-12-13 16:31:33'),
(831, 56, 21, 2, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-21-56.jpg', NULL, NULL, NULL, '2024-11-27 17:00:00', '2025-12-13 16:31:33'),
(832, 56, 8, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-56.jpg', NULL, NULL, NULL, '2024-06-04 17:00:00', '2025-12-13 16:31:33'),
(833, 56, 24, 7, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-24-56.jpg', NULL, NULL, NULL, '2024-05-31 17:00:00', '2025-12-13 16:31:33'),
(834, 56, 20, 3, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-20-56.jpg', NULL, NULL, NULL, '2024-07-13 17:00:00', '2025-12-13 16:31:33'),
(835, 56, 16, 8, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-16-56.jpg', NULL, NULL, NULL, '2024-10-07 17:00:00', '2025-12-13 16:31:33'),
(836, 56, 22, 2, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-22-56.jpg', NULL, NULL, NULL, '2024-05-05 17:00:00', '2025-12-13 16:31:33'),
(837, 56, 23, 9, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-23-56.jpg', NULL, NULL, NULL, '2024-01-18 17:00:00', '2025-12-13 16:31:33'),
(838, 56, 11, 4, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-11-56.jpg', NULL, NULL, NULL, '2024-01-11 17:00:00', '2025-12-13 16:31:33'),
(839, 56, 14, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-14-56.jpg', NULL, NULL, NULL, '2024-05-25 17:00:00', '2025-12-13 16:31:33'),
(840, 56, 18, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-18-56.jpg', NULL, NULL, NULL, '2024-04-08 17:00:00', '2025-12-13 16:31:33'),
(841, 56, 9, 4, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-9-56.jpg', NULL, NULL, NULL, '2024-07-27 17:00:00', '2025-12-13 16:31:33'),
(842, 56, 13, 3, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-13-56.jpg', NULL, NULL, NULL, '2024-04-13 17:00:00', '2025-12-13 16:31:33'),
(843, 56, 12, 6, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-12-56.jpg', NULL, NULL, NULL, '2024-12-24 17:00:00', '2025-12-13 16:31:33'),
(844, 56, 15, 8, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-15-56.jpg', NULL, NULL, NULL, '2024-09-23 17:00:00', '2025-12-13 16:31:33'),
(845, 56, 10, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-10-56.jpg', NULL, NULL, NULL, '2024-12-18 17:00:00', '2025-12-13 16:31:33'),
(846, 56, 19, 1, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-19-56.jpg', NULL, NULL, NULL, '2024-11-20 17:00:00', '2025-12-13 16:31:33'),
(847, 56, 25, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-25-56.jpg', NULL, NULL, NULL, '2024-05-17 17:00:00', '2025-12-13 16:31:33'),
(848, 57, 22, 7, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-22-57.jpg', NULL, NULL, NULL, '2024-11-08 17:00:00', '2025-12-13 16:31:33'),
(849, 57, 12, 2, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-12-57.jpg', NULL, NULL, NULL, '2024-11-21 17:00:00', '2025-12-13 16:31:33'),
(850, 57, 15, 4, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-15-57.jpg', NULL, NULL, NULL, '2024-07-20 17:00:00', '2025-12-13 16:31:33'),
(851, 57, 9, 12, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-9-57.jpg', NULL, NULL, NULL, '2024-05-07 17:00:00', '2025-12-13 16:31:33'),
(852, 57, 20, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-57.jpg', NULL, NULL, NULL, '2024-04-05 17:00:00', '2025-12-13 16:31:33'),
(853, 57, 11, 4, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-11-57.jpg', NULL, NULL, NULL, '2024-10-11 17:00:00', '2025-12-13 16:31:33'),
(854, 57, 21, 5, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-21-57.jpg', NULL, NULL, NULL, '2024-07-15 17:00:00', '2025-12-13 16:31:33'),
(855, 57, 17, 5, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-17-57.jpg', NULL, NULL, NULL, '2024-01-17 17:00:00', '2025-12-13 16:31:33'),
(856, 57, 14, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-14-57.jpg', NULL, NULL, NULL, '2024-06-14 17:00:00', '2025-12-13 16:31:33'),
(857, 57, 13, 3, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-13-57.jpg', NULL, NULL, NULL, '2024-02-09 17:00:00', '2025-12-13 16:31:33'),
(858, 57, 19, 6, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-19-57.jpg', NULL, NULL, NULL, '2024-07-12 17:00:00', '2025-12-13 16:31:33'),
(859, 57, 8, 4, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-57.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:33'),
(860, 57, 16, 4, NULL, NULL, 3, 'Confirmed', 'payment-proofs/dummy-2024-16-57.jpg', NULL, NULL, NULL, '2024-03-17 17:00:00', '2025-12-13 16:31:33'),
(861, 57, 18, 12, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-18-57.jpg', NULL, NULL, NULL, '2024-01-08 17:00:00', '2025-12-13 16:31:33'),
(862, 57, 10, 11, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-10-57.jpg', NULL, NULL, NULL, '2024-07-25 17:00:00', '2025-12-13 16:31:33'),
(863, 58, 9, 2, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-9-58.jpg', NULL, NULL, NULL, '2024-09-12 17:00:00', '2025-12-13 16:31:33'),
(864, 58, 11, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-58.jpg', NULL, NULL, NULL, '2024-08-06 17:00:00', '2025-12-13 16:31:33'),
(865, 58, 10, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-10-58.jpg', NULL, NULL, NULL, '2024-02-11 17:00:00', '2025-12-13 16:31:33'),
(866, 58, 18, 10, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-18-58.jpg', NULL, NULL, NULL, '2024-07-02 17:00:00', '2025-12-13 16:31:33'),
(867, 58, 16, 9, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-16-58.jpg', NULL, NULL, NULL, '2024-07-08 17:00:00', '2025-12-13 16:31:33'),
(868, 58, 15, 5, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-15-58.jpg', NULL, NULL, NULL, '2024-02-15 17:00:00', '2025-12-13 16:31:33'),
(869, 58, 17, 9, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-17-58.jpg', NULL, NULL, NULL, '2024-07-07 17:00:00', '2025-12-13 16:31:33'),
(870, 58, 14, 4, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-14-58.jpg', NULL, NULL, NULL, '2024-06-26 17:00:00', '2025-12-13 16:31:33'),
(871, 58, 12, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-58.jpg', NULL, NULL, NULL, '2024-01-31 17:00:00', '2025-12-13 16:31:33'),
(872, 58, 13, 6, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-58.jpg', NULL, NULL, NULL, '2024-09-06 17:00:00', '2025-12-13 16:31:33'),
(873, 58, 19, 9, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-19-58.jpg', NULL, NULL, NULL, '2024-04-10 17:00:00', '2025-12-13 16:31:33'),
(874, 58, 8, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-8-58.jpg', NULL, NULL, NULL, '2024-10-22 17:00:00', '2025-12-13 16:31:33'),
(875, 59, 20, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-20-59.jpg', NULL, NULL, NULL, '2024-03-21 17:00:00', '2025-12-13 16:31:33'),
(876, 59, 17, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-17-59.jpg', NULL, NULL, NULL, '2024-09-04 17:00:00', '2025-12-13 16:31:33'),
(877, 59, 21, 7, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-21-59.jpg', NULL, NULL, NULL, '2024-07-03 17:00:00', '2025-12-13 16:31:33'),
(878, 59, 8, 11, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-8-59.jpg', NULL, NULL, NULL, '2024-10-18 17:00:00', '2025-12-13 16:31:33'),
(879, 59, 9, 9, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-9-59.jpg', NULL, NULL, NULL, '2024-04-02 17:00:00', '2025-12-13 16:31:33'),
(880, 59, 24, 10, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-24-59.jpg', NULL, NULL, NULL, '2024-07-20 17:00:00', '2025-12-13 16:31:33'),
(881, 59, 16, 3, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-59.jpg', NULL, NULL, NULL, '2024-06-06 17:00:00', '2025-12-13 16:31:33'),
(882, 59, 14, 3, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-14-59.jpg', NULL, NULL, NULL, '2024-07-26 17:00:00', '2025-12-13 16:31:33'),
(883, 59, 25, 3, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-25-59.jpg', NULL, NULL, NULL, '2024-03-18 17:00:00', '2025-12-13 16:31:33'),
(884, 59, 18, 5, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-18-59.jpg', NULL, NULL, NULL, '2024-10-19 17:00:00', '2025-12-13 16:31:33'),
(885, 59, 22, 8, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-22-59.jpg', NULL, NULL, NULL, '2024-03-10 17:00:00', '2025-12-13 16:31:33'),
(886, 59, 10, 11, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-10-59.jpg', NULL, NULL, NULL, '2024-11-10 17:00:00', '2025-12-13 16:31:33'),
(887, 59, 23, 7, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-23-59.jpg', NULL, NULL, NULL, '2024-01-04 17:00:00', '2025-12-13 16:31:33'),
(888, 59, 11, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-59.jpg', NULL, NULL, NULL, '2024-04-05 17:00:00', '2025-12-13 16:31:33'),
(889, 59, 15, 9, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-15-59.jpg', NULL, NULL, NULL, '2024-02-03 17:00:00', '2025-12-13 16:31:33'),
(890, 59, 19, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-19-59.jpg', NULL, NULL, NULL, '2024-06-10 17:00:00', '2025-12-13 16:31:33'),
(891, 59, 13, 5, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-13-59.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:33'),
(892, 59, 12, 12, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-12-59.jpg', NULL, NULL, NULL, '2024-03-03 17:00:00', '2025-12-13 16:31:33'),
(893, 60, 16, 8, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-16-60.jpg', NULL, NULL, NULL, '2024-03-23 17:00:00', '2025-12-13 16:31:33'),
(894, 60, 14, 6, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-14-60.jpg', NULL, NULL, NULL, '2024-01-04 17:00:00', '2025-12-13 16:31:33'),
(895, 60, 9, 11, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-9-60.jpg', NULL, NULL, NULL, '2024-08-08 17:00:00', '2025-12-13 16:31:33'),
(896, 60, 19, 12, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-19-60.jpg', NULL, NULL, NULL, '2024-08-20 17:00:00', '2025-12-13 16:31:33'),
(897, 60, 13, 1, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-13-60.jpg', NULL, NULL, NULL, '2024-01-09 17:00:00', '2025-12-13 16:31:33'),
(898, 60, 12, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-12-60.jpg', NULL, NULL, NULL, '2024-03-14 17:00:00', '2025-12-13 16:31:33'),
(899, 60, 18, 12, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-18-60.jpg', NULL, NULL, NULL, '2024-10-14 17:00:00', '2025-12-13 16:31:33'),
(900, 60, 15, 5, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-15-60.jpg', NULL, NULL, NULL, '2024-12-04 17:00:00', '2025-12-13 16:31:33'),
(901, 60, 10, 12, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-10-60.jpg', NULL, NULL, NULL, '2024-06-16 17:00:00', '2025-12-13 16:31:33'),
(902, 60, 17, 3, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-17-60.jpg', NULL, NULL, NULL, '2024-03-18 17:00:00', '2025-12-13 16:31:33'),
(903, 60, 11, 2, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-11-60.jpg', NULL, NULL, NULL, '2024-01-27 17:00:00', '2025-12-13 16:31:33'),
(904, 60, 8, 7, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-8-60.jpg', NULL, NULL, NULL, '2024-05-18 17:00:00', '2025-12-13 16:31:33'),
(905, 61, 15, 12, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-15-61.jpg', NULL, NULL, NULL, '2024-09-02 17:00:00', '2025-12-13 16:31:33'),
(906, 61, 25, 2, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-25-61.jpg', NULL, NULL, NULL, '2024-11-13 17:00:00', '2025-12-13 16:31:33'),
(907, 61, 21, 11, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-21-61.jpg', NULL, NULL, NULL, '2024-11-08 17:00:00', '2025-12-13 16:31:33'),
(908, 61, 12, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-12-61.jpg', NULL, NULL, NULL, '2024-12-14 17:00:00', '2025-12-13 16:31:33'),
(909, 61, 10, 1, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-10-61.jpg', NULL, NULL, NULL, '2024-07-21 17:00:00', '2025-12-13 16:31:33'),
(910, 61, 20, 3, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-20-61.jpg', NULL, NULL, NULL, '2024-09-04 17:00:00', '2025-12-13 16:31:33'),
(911, 61, 23, 12, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-23-61.jpg', NULL, NULL, NULL, '2024-10-16 17:00:00', '2025-12-13 16:31:33'),
(912, 61, 19, 11, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-19-61.jpg', NULL, NULL, NULL, '2024-03-20 17:00:00', '2025-12-13 16:31:33'),
(913, 61, 13, 12, NULL, NULL, 1, 'Confirmed', 'payment-proofs/dummy-2024-13-61.jpg', NULL, NULL, NULL, '2024-01-11 17:00:00', '2025-12-13 16:31:33'),
(914, 61, 14, 4, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-14-61.jpg', NULL, NULL, NULL, '2024-05-11 17:00:00', '2025-12-13 16:31:33'),
(915, 61, 8, 8, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-61.jpg', NULL, NULL, NULL, '2024-12-11 17:00:00', '2025-12-13 16:31:33'),
(916, 61, 11, 12, 2, NULL, 20, 'Confirmed', 'payment-proofs/dummy-2024-11-61.jpg', NULL, NULL, NULL, '2024-10-27 17:00:00', '2025-12-13 16:31:33'),
(917, 61, 22, 6, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-22-61.jpg', NULL, NULL, NULL, '2024-07-24 17:00:00', '2025-12-13 16:31:33'),
(918, 61, 26, 2, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-26-61.jpg', NULL, NULL, NULL, '2024-03-07 17:00:00', '2025-12-13 16:31:33'),
(919, 61, 16, 8, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-16-61.jpg', NULL, NULL, NULL, '2024-11-27 17:00:00', '2025-12-13 16:31:33'),
(920, 61, 17, 4, 5, NULL, 11, 'Confirmed', 'payment-proofs/dummy-2024-17-61.jpg', NULL, NULL, NULL, '2024-11-15 17:00:00', '2025-12-13 16:31:33'),
(921, 61, 24, 8, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-24-61.jpg', NULL, NULL, NULL, '2024-09-09 17:00:00', '2025-12-13 16:31:33'),
(922, 61, 9, 7, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-9-61.jpg', NULL, NULL, NULL, '2024-12-27 17:00:00', '2025-12-13 16:31:33'),
(923, 61, 27, 11, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-27-61.jpg', NULL, NULL, NULL, '2024-01-03 17:00:00', '2025-12-13 16:31:33'),
(924, 61, 18, 1, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-18-61.jpg', NULL, NULL, NULL, '2024-12-13 17:00:00', '2025-12-13 16:31:33'),
(925, 62, 12, 6, 8, NULL, 8, 'Confirmed', 'payment-proofs/dummy-2024-12-62.jpg', NULL, NULL, NULL, '2024-04-21 17:00:00', '2025-12-13 16:31:33'),
(926, 62, 23, 12, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-23-62.jpg', NULL, NULL, NULL, '2024-04-08 17:00:00', '2025-12-13 16:31:33'),
(927, 62, 20, 12, 4, NULL, 13, 'Confirmed', 'payment-proofs/dummy-2024-20-62.jpg', NULL, NULL, NULL, '2024-09-26 17:00:00', '2025-12-13 16:31:33'),
(928, 62, 13, 5, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-13-62.jpg', NULL, NULL, NULL, '2024-09-06 17:00:00', '2025-12-13 16:31:33'),
(929, 62, 27, 12, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-27-62.jpg', NULL, NULL, NULL, '2024-10-09 17:00:00', '2025-12-13 16:31:33'),
(930, 62, 9, 5, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-9-62.jpg', NULL, NULL, NULL, '2024-11-20 17:00:00', '2025-12-13 16:31:33'),
(931, 62, 24, 9, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-24-62.jpg', NULL, NULL, NULL, '2024-05-13 17:00:00', '2025-12-13 16:31:33'),
(932, 62, 16, 10, NULL, NULL, 4, 'Confirmed', 'payment-proofs/dummy-2024-16-62.jpg', NULL, NULL, NULL, '2024-09-23 17:00:00', '2025-12-13 16:31:33'),
(933, 62, 21, 8, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-21-62.jpg', NULL, NULL, NULL, '2024-09-12 17:00:00', '2025-12-13 16:31:33'),
(934, 62, 18, 4, NULL, NULL, 2, 'Confirmed', 'payment-proofs/dummy-2024-18-62.jpg', NULL, NULL, NULL, '2024-03-16 17:00:00', '2025-12-13 16:31:33'),
(935, 62, 14, 8, 10, NULL, 6, 'Confirmed', 'payment-proofs/dummy-2024-14-62.jpg', NULL, NULL, NULL, '2024-07-18 17:00:00', '2025-12-13 16:31:33'),
(936, 62, 8, 2, 3, NULL, 16, 'Confirmed', 'payment-proofs/dummy-2024-8-62.jpg', NULL, NULL, NULL, '2024-03-04 17:00:00', '2025-12-13 16:31:33'),
(937, 62, 25, 9, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-25-62.jpg', NULL, NULL, NULL, '2024-09-13 17:00:00', '2025-12-13 16:31:33'),
(938, 62, 17, 8, 9, NULL, 7, 'Confirmed', 'payment-proofs/dummy-2024-17-62.jpg', NULL, NULL, NULL, '2024-05-02 17:00:00', '2025-12-13 16:31:33'),
(939, 62, 19, 9, NULL, NULL, 5, 'Confirmed', 'payment-proofs/dummy-2024-19-62.jpg', NULL, NULL, NULL, '2024-02-07 17:00:00', '2025-12-13 16:31:33'),
(940, 62, 22, 10, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-2024-22-62.jpg', NULL, NULL, NULL, '2024-09-20 17:00:00', '2025-12-13 16:31:33'),
(941, 62, 15, 1, 7, NULL, 9, 'Confirmed', 'payment-proofs/dummy-2024-15-62.jpg', NULL, NULL, NULL, '2024-11-10 17:00:00', '2025-12-13 16:31:33'),
(942, 62, 10, 8, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-2024-10-62.jpg', NULL, NULL, NULL, '2024-03-17 17:00:00', '2025-12-13 16:31:33'),
(943, 62, 11, 8, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-11-62.jpg', NULL, NULL, NULL, '2024-11-19 17:00:00', '2025-12-13 16:31:33'),
(944, 62, 26, 7, 6, NULL, 10, 'Confirmed', 'payment-proofs/dummy-2024-26-62.jpg', NULL, NULL, NULL, '2024-04-18 17:00:00', '2025-12-13 16:31:33');

--
-- Triggers `event_registrations`
--
DELIMITER $$
CREATE TRIGGER `log_event_registration_insert` AFTER INSERT ON `event_registrations` FOR EACH ROW BEGIN
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
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_event_registration_update` AFTER UPDATE ON `event_registrations` FOR EACH ROW BEGIN
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kis_applications`
--

CREATE TABLE `kis_applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pembalap_user_id` bigint(20) UNSIGNED NOT NULL,
  `kis_category_id` bigint(20) UNSIGNED NOT NULL,
  `file_ktp_url` varchar(255) DEFAULT NULL,
  `file_pas_foto_url` varchar(255) DEFAULT NULL,
  `file_kk_url` varchar(255) DEFAULT NULL,
  `file_surat_izin_url` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `file_surat_sehat_url` varchar(255) DEFAULT NULL,
  `file_bukti_bayar_url` varchar(255) DEFAULT NULL,
  `processed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kis_applications`
--

INSERT INTO `kis_applications` (`id`, `pembalap_user_id`, `kis_category_id`, `file_ktp_url`, `file_pas_foto_url`, `file_kk_url`, `file_surat_izin_url`, `status`, `file_surat_sehat_url`, `file_bukti_bayar_url`, `processed_by_user_id`, `rejection_reason`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 5, 1, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, 2, NULL, '2025-12-13 16:31:11', '2025-12-13 16:31:11', '2025-12-13 16:31:11'),
(2, 6, 1, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, 2, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(3, 7, 8, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, 2, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(4, 90, 5, 'kis-docs/ktp-90.jpg', 'kis-docs/pas-foto-90.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-90.pdf', 'kis-payments/bukti-bayar-90-0-2024.jpg', 2, NULL, '2024-08-09 17:00:00', '2024-08-07 17:00:00', '2024-08-12 17:00:00'),
(5, 107, 10, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-11-20 17:00:00', '2024-11-25 17:00:00'),
(6, 38, 5, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-10-18 17:00:00', '2024-10-23 17:00:00'),
(7, 18, 8, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-09-04 17:00:00', '2024-09-06 17:00:00'),
(8, 65, 9, 'kis-docs/ktp-65.jpg', 'kis-docs/pas-foto-65.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-65.pdf', 'kis-payments/bukti-bayar-65-4-2024.jpg', 1, NULL, '2024-04-20 17:00:00', '2024-04-18 17:00:00', '2024-04-22 17:00:00'),
(9, 57, 9, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-10-05 17:00:00', '2024-10-06 17:00:00'),
(10, 22, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-08-15 17:00:00', '2024-08-22 17:00:00'),
(11, 11, 8, 'kis-docs/ktp-11.jpg', 'kis-docs/pas-foto-11.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-11.pdf', 'kis-payments/bukti-bayar-11-7-2024.jpg', 3, NULL, '2024-07-18 17:00:00', '2024-07-15 17:00:00', '2024-07-18 17:00:00'),
(12, 41, 4, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-06-27 17:00:00', '2024-07-03 17:00:00'),
(13, 42, 3, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-04-02 17:00:00', '2024-04-05 17:00:00'),
(14, 83, 11, 'kis-docs/ktp-83.jpg', 'kis-docs/pas-foto-83.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-83.pdf', 'kis-payments/bukti-bayar-83-10-2024.jpg', 3, NULL, '2024-02-09 17:00:00', '2024-02-06 17:00:00', '2024-02-08 17:00:00'),
(15, 85, 9, 'kis-docs/ktp-85.jpg', 'kis-docs/pas-foto-85.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-85.pdf', 'kis-payments/bukti-bayar-85-11-2024.jpg', 3, NULL, '2024-08-15 17:00:00', '2024-08-13 17:00:00', '2024-08-20 17:00:00'),
(16, 35, 2, 'kis-docs/ktp-35.jpg', 'kis-docs/pas-foto-35.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-35.pdf', 'kis-payments/bukti-bayar-35-12-2024.jpg', 3, NULL, '2024-02-25 17:00:00', '2024-02-23 17:00:00', '2024-02-26 17:00:00'),
(17, 26, 12, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-06-03 17:00:00', '2024-06-05 17:00:00'),
(18, 22, 3, 'kis-docs/ktp-22.jpg', 'kis-docs/pas-foto-22.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-22.pdf', 'kis-payments/bukti-bayar-22-14-2024.jpg', 1, NULL, '2024-10-09 17:00:00', '2024-10-07 17:00:00', '2024-10-10 17:00:00'),
(19, 23, 2, 'kis-docs/ktp-23.jpg', 'kis-docs/pas-foto-23.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-23.pdf', 'kis-payments/bukti-bayar-23-15-2024.jpg', 1, NULL, '2024-09-14 17:00:00', '2024-09-13 17:00:00', '2024-09-15 17:00:00'),
(20, 73, 4, 'kis-docs/ktp-73.jpg', 'kis-docs/pas-foto-73.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-73.pdf', 'kis-payments/bukti-bayar-73-16-2024.jpg', 2, NULL, '2024-05-25 17:00:00', '2024-05-22 17:00:00', '2024-05-28 17:00:00'),
(21, 99, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-08-23 17:00:00', '2024-08-27 17:00:00'),
(22, 95, 9, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-02-09 17:00:00', '2024-02-15 17:00:00'),
(23, 55, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-02-25 17:00:00', '2024-02-29 17:00:00'),
(24, 71, 11, 'kis-docs/ktp-71.jpg', 'kis-docs/pas-foto-71.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-71.pdf', 'kis-payments/bukti-bayar-71-20-2024.jpg', 2, NULL, '2024-07-03 17:00:00', '2024-06-30 17:00:00', '2024-07-03 17:00:00'),
(25, 97, 5, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-12-09 17:00:00', '2024-12-12 17:00:00'),
(26, 59, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-04-04 17:00:00', '2024-04-11 17:00:00'),
(27, 75, 9, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-03-12 17:00:00', '2024-03-15 17:00:00'),
(28, 37, 5, 'kis-docs/ktp-37.jpg', 'kis-docs/pas-foto-37.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-37.pdf', 'kis-payments/bukti-bayar-37-24-2024.jpg', 1, NULL, '2024-07-09 17:00:00', '2024-07-08 17:00:00', '2024-07-11 17:00:00'),
(29, 18, 10, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-12-13 17:00:00', '2024-12-19 17:00:00'),
(30, 29, 5, 'kis-docs/ktp-29.jpg', 'kis-docs/pas-foto-29.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-29.pdf', 'kis-payments/bukti-bayar-29-26-2024.jpg', 3, NULL, '2024-11-21 17:00:00', '2024-11-18 17:00:00', '2024-11-22 17:00:00'),
(31, 61, 6, 'kis-docs/ktp-61.jpg', 'kis-docs/pas-foto-61.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-61.pdf', 'kis-payments/bukti-bayar-61-27-2024.jpg', 2, NULL, '2024-03-08 17:00:00', '2024-03-06 17:00:00', '2024-03-08 17:00:00'),
(32, 47, 3, 'kis-docs/ktp-47.jpg', 'kis-docs/pas-foto-47.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-47.pdf', 'kis-payments/bukti-bayar-47-28-2024.jpg', 3, NULL, '2024-07-22 17:00:00', '2024-07-20 17:00:00', '2024-07-26 17:00:00'),
(33, 102, 12, 'kis-docs/ktp-102.jpg', 'kis-docs/pas-foto-102.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-102.pdf', 'kis-payments/bukti-bayar-102-29-2024.jpg', 3, NULL, '2024-07-15 17:00:00', '2024-07-14 17:00:00', '2024-07-21 17:00:00'),
(34, 26, 7, 'kis-docs/ktp-26.jpg', 'kis-docs/pas-foto-26.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-26.pdf', 'kis-payments/bukti-bayar-26-30-2024.jpg', 2, NULL, '2024-08-24 17:00:00', '2024-08-22 17:00:00', '2024-08-24 17:00:00'),
(35, 94, 1, 'kis-docs/ktp-94.jpg', 'kis-docs/pas-foto-94.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-94.pdf', 'kis-payments/bukti-bayar-94-31-2024.jpg', 1, NULL, '2024-08-18 17:00:00', '2024-08-17 17:00:00', '2024-08-21 17:00:00'),
(36, 62, 10, 'kis-docs/ktp-62.jpg', 'kis-docs/pas-foto-62.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-62.pdf', 'kis-payments/bukti-bayar-62-32-2024.jpg', 1, NULL, '2024-07-14 17:00:00', '2024-07-13 17:00:00', '2024-07-18 17:00:00'),
(37, 22, 9, 'kis-docs/ktp-22.jpg', 'kis-docs/pas-foto-22.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-22.pdf', 'kis-payments/bukti-bayar-22-33-2024.jpg', 1, NULL, '2024-05-12 17:00:00', '2024-05-11 17:00:00', '2024-05-13 17:00:00'),
(38, 83, 5, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-10-15 17:00:00', '2024-10-21 17:00:00'),
(39, 78, 3, 'kis-docs/ktp-78.jpg', 'kis-docs/pas-foto-78.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-78.pdf', 'kis-payments/bukti-bayar-78-35-2024.jpg', 2, NULL, '2024-04-09 17:00:00', '2024-04-07 17:00:00', '2024-04-14 17:00:00'),
(40, 65, 9, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-09-23 17:00:00', '2024-09-29 17:00:00'),
(41, 55, 6, 'kis-docs/ktp-55.jpg', 'kis-docs/pas-foto-55.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-55.pdf', 'kis-payments/bukti-bayar-55-37-2024.jpg', 2, NULL, '2024-02-24 17:00:00', '2024-02-21 17:00:00', '2024-02-22 17:00:00'),
(42, 12, 10, 'kis-docs/ktp-12.jpg', 'kis-docs/pas-foto-12.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-12.pdf', 'kis-payments/bukti-bayar-12-38-2024.jpg', 2, NULL, '2024-12-15 17:00:00', '2024-12-14 17:00:00', '2024-12-15 17:00:00'),
(43, 26, 10, 'kis-docs/ktp-26.jpg', 'kis-docs/pas-foto-26.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-26.pdf', 'kis-payments/bukti-bayar-26-39-2024.jpg', 1, NULL, '2024-10-03 17:00:00', '2024-10-01 17:00:00', '2024-10-04 17:00:00'),
(44, 77, 6, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-11-26 17:00:00', '2024-11-30 17:00:00'),
(45, 97, 6, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-02-18 17:00:00', '2024-02-22 17:00:00'),
(46, 8, 4, 'kis-docs/ktp-8.jpg', 'kis-docs/pas-foto-8.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-8.pdf', 'kis-payments/bukti-bayar-8-42-2024.jpg', 1, NULL, '2024-02-26 17:00:00', '2024-02-24 17:00:00', '2024-02-28 17:00:00'),
(47, 81, 12, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-01-06 17:00:00', '2024-01-08 17:00:00'),
(48, 8, 9, 'kis-docs/ktp-8.jpg', 'kis-docs/pas-foto-8.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-8.pdf', 'kis-payments/bukti-bayar-8-44-2024.jpg', 1, NULL, '2024-01-23 17:00:00', '2024-01-20 17:00:00', '2024-01-21 17:00:00'),
(49, 63, 5, 'kis-docs/ktp-63.jpg', 'kis-docs/pas-foto-63.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-63.pdf', 'kis-payments/bukti-bayar-63-45-2024.jpg', 2, NULL, '2024-08-27 17:00:00', '2024-08-26 17:00:00', '2024-09-01 17:00:00'),
(50, 52, 3, 'kis-docs/ktp-52.jpg', 'kis-docs/pas-foto-52.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-52.pdf', 'kis-payments/bukti-bayar-52-46-2024.jpg', 1, NULL, '2024-01-21 17:00:00', '2024-01-19 17:00:00', '2024-01-22 17:00:00'),
(51, 67, 7, 'kis-docs/ktp-67.jpg', 'kis-docs/pas-foto-67.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-67.pdf', 'kis-payments/bukti-bayar-67-47-2024.jpg', 1, NULL, '2024-03-10 17:00:00', '2024-03-08 17:00:00', '2024-03-09 17:00:00'),
(52, 64, 11, 'kis-docs/ktp-64.jpg', 'kis-docs/pas-foto-64.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-64.pdf', 'kis-payments/bukti-bayar-64-48-2024.jpg', 3, NULL, '2024-05-09 17:00:00', '2024-05-07 17:00:00', '2024-05-10 17:00:00'),
(53, 35, 4, 'kis-docs/ktp-35.jpg', 'kis-docs/pas-foto-35.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-35.pdf', 'kis-payments/bukti-bayar-35-49-2024.jpg', 1, NULL, '2024-05-19 17:00:00', '2024-05-16 17:00:00', '2024-05-23 17:00:00'),
(54, 70, 8, 'kis-docs/ktp-70.jpg', 'kis-docs/pas-foto-70.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-70.pdf', 'kis-payments/bukti-bayar-70-50-2024.jpg', 2, NULL, '2024-12-12 17:00:00', '2024-12-10 17:00:00', '2024-12-17 17:00:00'),
(55, 31, 2, 'kis-docs/ktp-31.jpg', 'kis-docs/pas-foto-31.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-31.pdf', 'kis-payments/bukti-bayar-31-51-2024.jpg', 2, NULL, '2024-01-11 17:00:00', '2024-01-09 17:00:00', '2024-01-11 17:00:00'),
(56, 38, 5, 'kis-docs/ktp-38.jpg', 'kis-docs/pas-foto-38.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-38.pdf', 'kis-payments/bukti-bayar-38-52-2024.jpg', 3, NULL, '2024-09-15 17:00:00', '2024-09-13 17:00:00', '2024-09-15 17:00:00'),
(57, 9, 8, 'kis-docs/ktp-9.jpg', 'kis-docs/pas-foto-9.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-9.pdf', 'kis-payments/bukti-bayar-9-53-2024.jpg', 3, NULL, '2024-09-04 17:00:00', '2024-09-02 17:00:00', '2024-09-05 17:00:00'),
(58, 53, 1, 'kis-docs/ktp-53.jpg', 'kis-docs/pas-foto-53.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-53.pdf', 'kis-payments/bukti-bayar-53-54-2024.jpg', 2, NULL, '2024-11-08 17:00:00', '2024-11-06 17:00:00', '2024-11-13 17:00:00'),
(59, 106, 5, 'kis-docs/ktp-106.jpg', 'kis-docs/pas-foto-106.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-106.pdf', 'kis-payments/bukti-bayar-106-55-2024.jpg', 3, NULL, '2024-08-09 17:00:00', '2024-08-08 17:00:00', '2024-08-15 17:00:00'),
(60, 74, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-02-25 17:00:00', '2024-02-29 17:00:00'),
(61, 56, 2, 'kis-docs/ktp-56.jpg', 'kis-docs/pas-foto-56.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-56.pdf', 'kis-payments/bukti-bayar-56-57-2024.jpg', 1, NULL, '2024-10-12 17:00:00', '2024-10-10 17:00:00', '2024-10-16 17:00:00'),
(62, 53, 6, 'kis-docs/ktp-53.jpg', 'kis-docs/pas-foto-53.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-53.pdf', 'kis-payments/bukti-bayar-53-58-2024.jpg', 3, NULL, '2024-10-03 17:00:00', '2024-10-02 17:00:00', '2024-10-07 17:00:00'),
(63, 9, 2, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-05-23 17:00:00', '2024-05-24 17:00:00'),
(64, 11, 8, 'kis-docs/ktp-11.jpg', 'kis-docs/pas-foto-11.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-11.pdf', 'kis-payments/bukti-bayar-11-60-2024.jpg', 1, NULL, '2024-11-04 17:00:00', '2024-11-02 17:00:00', '2024-11-09 17:00:00'),
(65, 74, 7, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-02-02 17:00:00', '2024-02-04 17:00:00'),
(66, 68, 4, 'kis-docs/ktp-68.jpg', 'kis-docs/pas-foto-68.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-68.pdf', 'kis-payments/bukti-bayar-68-62-2024.jpg', 2, NULL, '2024-03-24 17:00:00', '2024-03-23 17:00:00', '2024-03-27 17:00:00'),
(67, 57, 1, 'kis-docs/ktp-57.jpg', 'kis-docs/pas-foto-57.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-57.pdf', 'kis-payments/bukti-bayar-57-63-2024.jpg', 1, NULL, '2024-12-05 17:00:00', '2024-12-02 17:00:00', '2024-12-04 17:00:00'),
(68, 100, 6, 'kis-docs/ktp-100.jpg', 'kis-docs/pas-foto-100.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-100.pdf', 'kis-payments/bukti-bayar-100-64-2024.jpg', 1, NULL, '2024-05-05 17:00:00', '2024-05-03 17:00:00', '2024-05-08 17:00:00'),
(69, 49, 12, 'kis-docs/ktp-49.jpg', 'kis-docs/pas-foto-49.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-49.pdf', 'kis-payments/bukti-bayar-49-65-2024.jpg', 2, NULL, '2024-03-03 17:00:00', '2024-03-02 17:00:00', '2024-03-08 17:00:00'),
(70, 18, 9, 'kis-docs/ktp-18.jpg', 'kis-docs/pas-foto-18.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-18.pdf', 'kis-payments/bukti-bayar-18-66-2024.jpg', 1, NULL, '2024-04-01 17:00:00', '2024-03-31 17:00:00', '2024-04-04 17:00:00'),
(71, 90, 7, 'kis-docs/ktp-90.jpg', 'kis-docs/pas-foto-90.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-90.pdf', 'kis-payments/bukti-bayar-90-67-2024.jpg', 2, NULL, '2024-10-22 17:00:00', '2024-10-20 17:00:00', '2024-10-22 17:00:00'),
(72, 72, 3, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-11-25 17:00:00', '2024-12-01 17:00:00'),
(73, 86, 12, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-11-02 17:00:00', '2024-11-06 17:00:00'),
(74, 17, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-02-03 17:00:00', '2024-02-05 17:00:00'),
(75, 32, 3, 'kis-docs/ktp-32.jpg', 'kis-docs/pas-foto-32.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-32.pdf', 'kis-payments/bukti-bayar-32-71-2024.jpg', 3, NULL, '2024-12-07 17:00:00', '2024-12-04 17:00:00', '2024-12-09 17:00:00'),
(76, 44, 12, 'kis-docs/ktp-44.jpg', 'kis-docs/pas-foto-44.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-44.pdf', 'kis-payments/bukti-bayar-44-72-2024.jpg', 3, NULL, '2024-07-11 17:00:00', '2024-07-08 17:00:00', '2024-07-09 17:00:00'),
(77, 21, 8, 'kis-docs/ktp-21.jpg', 'kis-docs/pas-foto-21.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-21.pdf', 'kis-payments/bukti-bayar-21-73-2024.jpg', 3, NULL, '2024-12-04 17:00:00', '2024-12-03 17:00:00', '2024-12-06 17:00:00'),
(78, 56, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-03-09 17:00:00', '2024-03-11 17:00:00'),
(79, 95, 4, 'kis-docs/ktp-95.jpg', 'kis-docs/pas-foto-95.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-95.pdf', 'kis-payments/bukti-bayar-95-75-2024.jpg', 2, NULL, '2024-06-08 17:00:00', '2024-06-07 17:00:00', '2024-06-08 17:00:00'),
(80, 60, 2, 'kis-docs/ktp-60.jpg', 'kis-docs/pas-foto-60.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-60.pdf', 'kis-payments/bukti-bayar-60-76-2024.jpg', 2, NULL, '2024-03-10 17:00:00', '2024-03-09 17:00:00', '2024-03-14 17:00:00'),
(81, 98, 8, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-03-04 17:00:00', '2024-03-10 17:00:00'),
(82, 91, 10, 'kis-docs/ktp-91.jpg', 'kis-docs/pas-foto-91.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-91.pdf', 'kis-payments/bukti-bayar-91-78-2024.jpg', 2, NULL, '2024-01-27 17:00:00', '2024-01-26 17:00:00', '2024-01-30 17:00:00'),
(83, 101, 12, 'kis-docs/ktp-101.jpg', 'kis-docs/pas-foto-101.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-101.pdf', 'kis-payments/bukti-bayar-101-79-2024.jpg', 2, NULL, '2024-06-28 17:00:00', '2024-06-25 17:00:00', '2024-06-29 17:00:00'),
(84, 36, 5, 'kis-docs/ktp-36.jpg', 'kis-docs/pas-foto-36.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-36.pdf', 'kis-payments/bukti-bayar-36-80-2024.jpg', 2, NULL, '2024-04-12 17:00:00', '2024-04-10 17:00:00', '2024-04-15 17:00:00'),
(85, 20, 3, 'kis-docs/ktp-20.jpg', 'kis-docs/pas-foto-20.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-20.pdf', 'kis-payments/bukti-bayar-20-81-2024.jpg', 3, NULL, '2024-12-10 17:00:00', '2024-12-07 17:00:00', '2024-12-10 17:00:00'),
(86, 55, 5, 'kis-docs/ktp-55.jpg', 'kis-docs/pas-foto-55.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-55.pdf', 'kis-payments/bukti-bayar-55-82-2024.jpg', 2, NULL, '2024-01-29 17:00:00', '2024-01-26 17:00:00', '2024-02-01 17:00:00'),
(87, 45, 12, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-12-17 17:00:00', '2024-12-21 17:00:00'),
(88, 20, 1, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-06-20 17:00:00', '2024-06-27 17:00:00'),
(89, 87, 11, 'kis-docs/ktp-87.jpg', 'kis-docs/pas-foto-87.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-87.pdf', 'kis-payments/bukti-bayar-87-85-2024.jpg', 2, NULL, '2024-06-24 17:00:00', '2024-06-21 17:00:00', '2024-06-23 17:00:00'),
(90, 95, 10, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-09-30 17:00:00', '2024-10-01 17:00:00'),
(91, 94, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-07-03 17:00:00', '2024-07-04 17:00:00'),
(92, 107, 4, 'kis-docs/ktp-107.jpg', 'kis-docs/pas-foto-107.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-107.pdf', 'kis-payments/bukti-bayar-107-88-2024.jpg', 3, NULL, '2024-04-05 17:00:00', '2024-04-03 17:00:00', '2024-04-04 17:00:00'),
(93, 46, 1, 'kis-docs/ktp-46.jpg', 'kis-docs/pas-foto-46.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-46.pdf', 'kis-payments/bukti-bayar-46-89-2024.jpg', 3, NULL, '2024-12-24 17:00:00', '2024-12-23 17:00:00', '2024-12-28 17:00:00'),
(94, 24, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-12-08 17:00:00', '2024-12-11 17:00:00'),
(95, 46, 6, 'kis-docs/ktp-46.jpg', 'kis-docs/pas-foto-46.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-46.pdf', 'kis-payments/bukti-bayar-46-91-2024.jpg', 1, NULL, '2024-03-07 17:00:00', '2024-03-04 17:00:00', '2024-03-09 17:00:00'),
(96, 100, 9, 'kis-docs/ktp-100.jpg', 'kis-docs/pas-foto-100.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-100.pdf', 'kis-payments/bukti-bayar-100-92-2024.jpg', 1, NULL, '2024-01-06 17:00:00', '2024-01-05 17:00:00', '2024-01-06 17:00:00'),
(97, 39, 6, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-11-14 17:00:00', '2024-11-17 17:00:00'),
(98, 71, 5, 'kis-docs/ktp-71.jpg', 'kis-docs/pas-foto-71.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-71.pdf', 'kis-payments/bukti-bayar-71-94-2024.jpg', 3, NULL, '2024-04-28 17:00:00', '2024-04-27 17:00:00', '2024-05-01 17:00:00'),
(99, 87, 5, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-08-31 17:00:00', '2024-09-04 17:00:00'),
(100, 80, 2, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-09-02 17:00:00', '2024-09-07 17:00:00'),
(101, 94, 1, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-06-27 17:00:00', '2024-07-04 17:00:00'),
(102, 104, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-06-22 17:00:00', '2024-06-27 17:00:00'),
(103, 78, 3, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-01-31 17:00:00', '2024-02-04 17:00:00'),
(104, 12, 8, 'kis-docs/ktp-12.jpg', 'kis-docs/pas-foto-12.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-12.pdf', 'kis-payments/bukti-bayar-12-100-2024.jpg', 2, NULL, '2024-02-06 17:00:00', '2024-02-05 17:00:00', '2024-02-11 17:00:00'),
(105, 92, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-03-20 17:00:00', '2024-03-23 17:00:00'),
(106, 32, 5, 'kis-docs/ktp-32.jpg', 'kis-docs/pas-foto-32.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-32.pdf', 'kis-payments/bukti-bayar-32-102-2024.jpg', 3, NULL, '2024-11-07 17:00:00', '2024-11-04 17:00:00', '2024-11-09 17:00:00'),
(107, 14, 5, 'kis-docs/ktp-14.jpg', 'kis-docs/pas-foto-14.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-14.pdf', 'kis-payments/bukti-bayar-14-103-2024.jpg', 3, NULL, '2024-09-19 17:00:00', '2024-09-17 17:00:00', '2024-09-20 17:00:00'),
(108, 36, 11, 'kis-docs/ktp-36.jpg', 'kis-docs/pas-foto-36.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-36.pdf', 'kis-payments/bukti-bayar-36-104-2024.jpg', 3, NULL, '2024-04-25 17:00:00', '2024-04-22 17:00:00', '2024-04-26 17:00:00'),
(109, 20, 11, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-11-07 17:00:00', '2024-11-12 17:00:00'),
(110, 62, 7, 'kis-docs/ktp-62.jpg', 'kis-docs/pas-foto-62.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-62.pdf', 'kis-payments/bukti-bayar-62-106-2024.jpg', 2, NULL, '2024-12-14 17:00:00', '2024-12-12 17:00:00', '2024-12-19 17:00:00'),
(111, 41, 5, 'kis-docs/ktp-41.jpg', 'kis-docs/pas-foto-41.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-41.pdf', 'kis-payments/bukti-bayar-41-107-2024.jpg', 3, NULL, '2024-05-24 17:00:00', '2024-05-22 17:00:00', '2024-05-25 17:00:00'),
(112, 106, 7, 'kis-docs/ktp-106.jpg', 'kis-docs/pas-foto-106.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-106.pdf', 'kis-payments/bukti-bayar-106-108-2024.jpg', 2, NULL, '2024-06-27 17:00:00', '2024-06-25 17:00:00', '2024-06-27 17:00:00'),
(113, 61, 11, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-10-11 17:00:00', '2024-10-15 17:00:00'),
(114, 68, 4, 'kis-docs/ktp-68.jpg', 'kis-docs/pas-foto-68.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-68.pdf', 'kis-payments/bukti-bayar-68-110-2024.jpg', 3, NULL, '2024-01-13 17:00:00', '2024-01-12 17:00:00', '2024-01-15 17:00:00'),
(115, 28, 1, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-12-26 17:00:00', '2024-12-31 17:00:00'),
(116, 102, 5, 'kis-docs/ktp-102.jpg', 'kis-docs/pas-foto-102.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-102.pdf', 'kis-payments/bukti-bayar-102-112-2024.jpg', 3, NULL, '2024-12-09 17:00:00', '2024-12-08 17:00:00', '2024-12-14 17:00:00'),
(117, 71, 2, 'kis-docs/ktp-71.jpg', 'kis-docs/pas-foto-71.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-71.pdf', 'kis-payments/bukti-bayar-71-113-2024.jpg', 3, NULL, '2024-11-16 17:00:00', '2024-11-14 17:00:00', '2024-11-19 17:00:00'),
(118, 74, 1, 'kis-docs/ktp-74.jpg', 'kis-docs/pas-foto-74.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-74.pdf', 'kis-payments/bukti-bayar-74-114-2024.jpg', 1, NULL, '2024-12-23 17:00:00', '2024-12-20 17:00:00', '2024-12-26 17:00:00'),
(119, 91, 6, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-12-06 17:00:00', '2024-12-13 17:00:00'),
(120, 82, 9, 'kis-docs/ktp-82.jpg', 'kis-docs/pas-foto-82.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-82.pdf', 'kis-payments/bukti-bayar-82-116-2024.jpg', 3, NULL, '2024-10-27 17:00:00', '2024-10-24 17:00:00', '2024-10-27 17:00:00'),
(121, 99, 3, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-12-24 17:00:00', '2024-12-31 17:00:00'),
(122, 15, 7, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-01-26 17:00:00', '2024-01-29 17:00:00'),
(123, 78, 3, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-07-02 17:00:00', '2024-07-08 17:00:00'),
(124, 27, 8, 'kis-docs/ktp-27.jpg', 'kis-docs/pas-foto-27.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-27.pdf', 'kis-payments/bukti-bayar-27-120-2024.jpg', 3, NULL, '2024-04-19 17:00:00', '2024-04-16 17:00:00', '2024-04-21 17:00:00'),
(125, 31, 5, 'kis-docs/ktp-31.jpg', 'kis-docs/pas-foto-31.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-31.pdf', 'kis-payments/bukti-bayar-31-121-2024.jpg', 3, NULL, '2024-04-10 17:00:00', '2024-04-09 17:00:00', '2024-04-15 17:00:00'),
(126, 78, 7, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-07-31 17:00:00', '2024-08-04 17:00:00'),
(127, 68, 2, 'kis-docs/ktp-68.jpg', 'kis-docs/pas-foto-68.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-68.pdf', 'kis-payments/bukti-bayar-68-123-2024.jpg', 2, NULL, '2024-09-01 17:00:00', '2024-08-31 17:00:00', '2024-09-07 17:00:00'),
(128, 47, 6, 'kis-docs/ktp-47.jpg', 'kis-docs/pas-foto-47.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-47.pdf', 'kis-payments/bukti-bayar-47-124-2024.jpg', 1, NULL, '2024-07-21 17:00:00', '2024-07-18 17:00:00', '2024-07-25 17:00:00'),
(129, 43, 3, 'kis-docs/ktp-43.jpg', 'kis-docs/pas-foto-43.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-43.pdf', 'kis-payments/bukti-bayar-43-125-2024.jpg', 3, NULL, '2024-06-23 17:00:00', '2024-06-21 17:00:00', '2024-06-22 17:00:00'),
(130, 68, 11, 'kis-docs/ktp-68.jpg', 'kis-docs/pas-foto-68.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-68.pdf', 'kis-payments/bukti-bayar-68-126-2024.jpg', 1, NULL, '2024-08-19 17:00:00', '2024-08-18 17:00:00', '2024-08-23 17:00:00'),
(131, 37, 11, 'kis-docs/ktp-37.jpg', 'kis-docs/pas-foto-37.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-37.pdf', 'kis-payments/bukti-bayar-37-127-2024.jpg', 3, NULL, '2024-07-20 17:00:00', '2024-07-19 17:00:00', '2024-07-24 17:00:00'),
(132, 48, 4, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-02-21 17:00:00', '2024-02-28 17:00:00'),
(133, 29, 1, 'kis-docs/ktp-29.jpg', 'kis-docs/pas-foto-29.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-29.pdf', 'kis-payments/bukti-bayar-29-129-2024.jpg', 2, NULL, '2024-01-08 17:00:00', '2024-01-06 17:00:00', '2024-01-10 17:00:00'),
(134, 59, 4, 'kis-docs/ktp-59.jpg', 'kis-docs/pas-foto-59.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-59.pdf', 'kis-payments/bukti-bayar-59-130-2024.jpg', 3, NULL, '2024-06-07 17:00:00', '2024-06-04 17:00:00', '2024-06-09 17:00:00'),
(135, 36, 4, 'kis-docs/ktp-36.jpg', 'kis-docs/pas-foto-36.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-36.pdf', 'kis-payments/bukti-bayar-36-131-2024.jpg', 1, NULL, '2024-06-02 17:00:00', '2024-05-31 17:00:00', '2024-06-02 17:00:00'),
(136, 11, 9, 'kis-docs/ktp-11.jpg', 'kis-docs/pas-foto-11.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-11.pdf', 'kis-payments/bukti-bayar-11-132-2024.jpg', 2, NULL, '2024-11-18 17:00:00', '2024-11-16 17:00:00', '2024-11-20 17:00:00'),
(137, 49, 9, 'kis-docs/ktp-49.jpg', 'kis-docs/pas-foto-49.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-49.pdf', 'kis-payments/bukti-bayar-49-133-2024.jpg', 2, NULL, '2024-02-25 17:00:00', '2024-02-23 17:00:00', '2024-02-28 17:00:00'),
(138, 107, 10, 'kis-docs/ktp-107.jpg', 'kis-docs/pas-foto-107.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-107.pdf', 'kis-payments/bukti-bayar-107-134-2024.jpg', 2, NULL, '2024-05-10 17:00:00', '2024-05-07 17:00:00', '2024-05-14 17:00:00'),
(139, 107, 11, 'kis-docs/ktp-107.jpg', 'kis-docs/pas-foto-107.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-107.pdf', 'kis-payments/bukti-bayar-107-135-2024.jpg', 2, NULL, '2024-07-21 17:00:00', '2024-07-18 17:00:00', '2024-07-25 17:00:00'),
(140, 74, 5, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-12-04 17:00:00', '2024-12-08 17:00:00'),
(141, 88, 7, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-08-17 17:00:00', '2024-08-18 17:00:00'),
(142, 46, 12, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-09-19 17:00:00', '2024-09-21 17:00:00'),
(143, 32, 3, 'kis-docs/ktp-32.jpg', 'kis-docs/pas-foto-32.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-32.pdf', 'kis-payments/bukti-bayar-32-139-2024.jpg', 1, NULL, '2024-06-09 17:00:00', '2024-06-06 17:00:00', '2024-06-07 17:00:00'),
(144, 75, 8, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 1, 'Dokumen tidak lengkap', NULL, '2024-05-08 17:00:00', '2024-05-11 17:00:00'),
(145, 41, 2, 'kis-docs/ktp-41.jpg', 'kis-docs/pas-foto-41.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-41.pdf', 'kis-payments/bukti-bayar-41-141-2024.jpg', 2, NULL, '2024-02-05 17:00:00', '2024-02-04 17:00:00', '2024-02-10 17:00:00'),
(146, 69, 3, 'kis-docs/ktp-69.jpg', 'kis-docs/pas-foto-69.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-69.pdf', 'kis-payments/bukti-bayar-69-142-2024.jpg', 2, NULL, '2024-05-29 17:00:00', '2024-05-27 17:00:00', '2024-05-29 17:00:00'),
(147, 66, 10, 'kis-docs/ktp-66.jpg', 'kis-docs/pas-foto-66.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-66.pdf', 'kis-payments/bukti-bayar-66-143-2024.jpg', 1, NULL, '2024-06-24 17:00:00', '2024-06-22 17:00:00', '2024-06-25 17:00:00'),
(148, 19, 8, 'kis-docs/ktp-19.jpg', 'kis-docs/pas-foto-19.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-19.pdf', 'kis-payments/bukti-bayar-19-144-2024.jpg', 3, NULL, '2024-09-06 17:00:00', '2024-09-04 17:00:00', '2024-09-07 17:00:00'),
(149, 80, 4, 'kis-docs/ktp-80.jpg', 'kis-docs/pas-foto-80.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-80.pdf', 'kis-payments/bukti-bayar-80-145-2024.jpg', 3, NULL, '2024-03-09 17:00:00', '2024-03-06 17:00:00', '2024-03-11 17:00:00'),
(150, 74, 5, 'kis-docs/ktp-74.jpg', 'kis-docs/pas-foto-74.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-74.pdf', 'kis-payments/bukti-bayar-74-146-2024.jpg', 1, NULL, '2024-06-20 17:00:00', '2024-06-19 17:00:00', '2024-06-20 17:00:00'),
(151, 106, 1, 'kis-docs/ktp-106.jpg', 'kis-docs/pas-foto-106.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-106.pdf', 'kis-payments/bukti-bayar-106-147-2024.jpg', 2, NULL, '2024-12-04 17:00:00', '2024-12-03 17:00:00', '2024-12-10 17:00:00'),
(152, 35, 5, 'kis-docs/ktp-35.jpg', 'kis-docs/pas-foto-35.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-35.pdf', 'kis-payments/bukti-bayar-35-148-2024.jpg', 1, NULL, '2024-10-24 17:00:00', '2024-10-22 17:00:00', '2024-10-27 17:00:00'),
(153, 51, 5, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-01-02 17:00:00', '2024-01-05 17:00:00'),
(154, 9, 12, 'kis-docs/ktp-9.jpg', 'kis-docs/pas-foto-9.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-9.pdf', 'kis-payments/bukti-bayar-9-150-2024.jpg', 3, NULL, '2024-11-19 17:00:00', '2024-11-18 17:00:00', '2024-11-20 17:00:00'),
(155, 26, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-11-14 17:00:00', '2024-11-19 17:00:00'),
(156, 53, 12, 'kis-docs/ktp-53.jpg', 'kis-docs/pas-foto-53.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-53.pdf', 'kis-payments/bukti-bayar-53-152-2024.jpg', 2, NULL, '2024-08-05 17:00:00', '2024-08-03 17:00:00', '2024-08-06 17:00:00'),
(157, 93, 7, 'kis-docs/ktp-93.jpg', 'kis-docs/pas-foto-93.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-93.pdf', 'kis-payments/bukti-bayar-93-153-2024.jpg', 2, NULL, '2024-03-12 17:00:00', '2024-03-10 17:00:00', '2024-03-11 17:00:00'),
(158, 12, 9, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-11-05 17:00:00', '2024-11-08 17:00:00'),
(159, 39, 5, 'kis-docs/ktp-39.jpg', 'kis-docs/pas-foto-39.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-39.pdf', 'kis-payments/bukti-bayar-39-155-2024.jpg', 2, NULL, '2024-04-19 17:00:00', '2024-04-18 17:00:00', '2024-04-22 17:00:00'),
(160, 84, 9, 'kis-docs/ktp-84.jpg', 'kis-docs/pas-foto-84.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-84.pdf', 'kis-payments/bukti-bayar-84-156-2024.jpg', 1, NULL, '2024-02-11 17:00:00', '2024-02-10 17:00:00', '2024-02-17 17:00:00'),
(161, 95, 5, 'kis-docs/ktp-95.jpg', 'kis-docs/pas-foto-95.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-95.pdf', 'kis-payments/bukti-bayar-95-157-2024.jpg', 3, NULL, '2024-08-04 17:00:00', '2024-08-03 17:00:00', '2024-08-07 17:00:00'),
(162, 87, 7, 'kis-docs/ktp-87.jpg', 'kis-docs/pas-foto-87.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-87.pdf', 'kis-payments/bukti-bayar-87-158-2024.jpg', 2, NULL, '2024-12-27 17:00:00', '2024-12-25 17:00:00', '2024-12-27 17:00:00'),
(163, 86, 10, 'kis-docs/ktp-86.jpg', 'kis-docs/pas-foto-86.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-86.pdf', 'kis-payments/bukti-bayar-86-159-2024.jpg', 3, NULL, '2024-09-11 17:00:00', '2024-09-09 17:00:00', '2024-09-13 17:00:00'),
(164, 68, 8, 'kis-docs/ktp-68.jpg', 'kis-docs/pas-foto-68.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-68.pdf', 'kis-payments/bukti-bayar-68-160-2024.jpg', 1, NULL, '2024-08-19 17:00:00', '2024-08-18 17:00:00', '2024-08-24 17:00:00'),
(165, 60, 12, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-03-23 17:00:00', '2024-03-24 17:00:00'),
(166, 64, 3, 'kis-docs/ktp-64.jpg', 'kis-docs/pas-foto-64.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-64.pdf', 'kis-payments/bukti-bayar-64-162-2024.jpg', 1, NULL, '2024-05-10 17:00:00', '2024-05-08 17:00:00', '2024-05-12 17:00:00'),
(167, 66, 4, 'kis-docs/ktp-66.jpg', 'kis-docs/pas-foto-66.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-66.pdf', 'kis-payments/bukti-bayar-66-163-2024.jpg', 2, NULL, '2024-11-25 17:00:00', '2024-11-24 17:00:00', '2024-11-25 17:00:00'),
(168, 49, 4, 'kis-docs/ktp-49.jpg', 'kis-docs/pas-foto-49.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-49.pdf', 'kis-payments/bukti-bayar-49-164-2024.jpg', 3, NULL, '2024-04-26 17:00:00', '2024-04-23 17:00:00', '2024-04-24 17:00:00'),
(169, 26, 5, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-09-23 17:00:00', '2024-09-29 17:00:00'),
(170, 38, 8, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-11-02 17:00:00', '2024-11-05 17:00:00'),
(171, 57, 1, 'kis-docs/ktp-57.jpg', 'kis-docs/pas-foto-57.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-57.pdf', 'kis-payments/bukti-bayar-57-167-2024.jpg', 2, NULL, '2024-07-04 17:00:00', '2024-07-02 17:00:00', '2024-07-05 17:00:00'),
(172, 96, 4, 'kis-docs/ktp-96.jpg', 'kis-docs/pas-foto-96.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-96.pdf', 'kis-payments/bukti-bayar-96-168-2024.jpg', 1, NULL, '2024-05-29 17:00:00', '2024-05-27 17:00:00', '2024-05-29 17:00:00'),
(173, 86, 7, 'kis-docs/ktp-86.jpg', 'kis-docs/pas-foto-86.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-86.pdf', 'kis-payments/bukti-bayar-86-169-2024.jpg', 2, NULL, '2024-06-19 17:00:00', '2024-06-18 17:00:00', '2024-06-21 17:00:00'),
(174, 23, 4, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-06-07 17:00:00', '2024-06-13 17:00:00'),
(175, 81, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-10-31 17:00:00', '2024-11-02 17:00:00'),
(176, 99, 4, 'kis-docs/ktp-99.jpg', 'kis-docs/pas-foto-99.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-99.pdf', 'kis-payments/bukti-bayar-99-172-2024.jpg', 3, NULL, '2024-09-14 17:00:00', '2024-09-12 17:00:00', '2024-09-13 17:00:00'),
(177, 14, 6, 'kis-docs/ktp-14.jpg', 'kis-docs/pas-foto-14.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-14.pdf', 'kis-payments/bukti-bayar-14-173-2024.jpg', 1, NULL, '2024-08-05 17:00:00', '2024-08-04 17:00:00', '2024-08-06 17:00:00'),
(178, 47, 2, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-03-11 17:00:00', '2024-03-14 17:00:00'),
(179, 102, 1, 'kis-docs/ktp-102.jpg', 'kis-docs/pas-foto-102.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-102.pdf', 'kis-payments/bukti-bayar-102-175-2024.jpg', 3, NULL, '2024-11-23 17:00:00', '2024-11-22 17:00:00', '2024-11-26 17:00:00'),
(180, 59, 4, 'kis-docs/ktp-59.jpg', 'kis-docs/pas-foto-59.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-59.pdf', 'kis-payments/bukti-bayar-59-176-2024.jpg', 3, NULL, '2024-01-24 17:00:00', '2024-01-22 17:00:00', '2024-01-25 17:00:00'),
(181, 40, 5, 'kis-docs/ktp-40.jpg', 'kis-docs/pas-foto-40.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-40.pdf', 'kis-payments/bukti-bayar-40-177-2024.jpg', 3, NULL, '2024-11-19 17:00:00', '2024-11-16 17:00:00', '2024-11-20 17:00:00'),
(182, 39, 4, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-03-05 17:00:00', '2024-03-07 17:00:00'),
(183, 95, 2, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-09-27 17:00:00', '2024-10-01 17:00:00'),
(184, 11, 5, 'kis-docs/ktp-11.jpg', 'kis-docs/pas-foto-11.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-11.pdf', 'kis-payments/bukti-bayar-11-180-2024.jpg', 1, NULL, '2024-12-24 17:00:00', '2024-12-22 17:00:00', '2024-12-28 17:00:00'),
(185, 99, 2, 'kis-docs/ktp-99.jpg', 'kis-docs/pas-foto-99.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-99.pdf', 'kis-payments/bukti-bayar-99-181-2024.jpg', 1, NULL, '2024-09-27 17:00:00', '2024-09-24 17:00:00', '2024-09-25 17:00:00'),
(186, 89, 6, 'kis-docs/ktp-89.jpg', 'kis-docs/pas-foto-89.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-89.pdf', 'kis-payments/bukti-bayar-89-182-2024.jpg', 3, NULL, '2024-03-18 17:00:00', '2024-03-16 17:00:00', '2024-03-21 17:00:00'),
(187, 73, 9, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-07-02 17:00:00', '2024-07-06 17:00:00'),
(188, 14, 10, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-02-20 17:00:00', '2024-02-24 17:00:00'),
(189, 20, 4, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-08-12 17:00:00', '2024-08-19 17:00:00'),
(190, 21, 5, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-07-27 17:00:00', '2024-08-02 17:00:00'),
(191, 98, 10, 'kis-docs/ktp-98.jpg', 'kis-docs/pas-foto-98.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-98.pdf', 'kis-payments/bukti-bayar-98-187-2024.jpg', 1, NULL, '2024-01-21 17:00:00', '2024-01-18 17:00:00', '2024-01-24 17:00:00'),
(192, 103, 12, 'kis-docs/ktp-103.jpg', 'kis-docs/pas-foto-103.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-103.pdf', 'kis-payments/bukti-bayar-103-188-2024.jpg', 3, NULL, '2024-08-20 17:00:00', '2024-08-17 17:00:00', '2024-08-23 17:00:00'),
(193, 41, 9, NULL, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, '2024-09-27 17:00:00', '2024-10-03 17:00:00'),
(194, 101, 4, 'kis-docs/ktp-101.jpg', 'kis-docs/pas-foto-101.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-101.pdf', 'kis-payments/bukti-bayar-101-190-2024.jpg', 3, NULL, '2024-09-07 17:00:00', '2024-09-06 17:00:00', '2024-09-07 17:00:00'),
(195, 37, 11, 'kis-docs/ktp-37.jpg', 'kis-docs/pas-foto-37.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-37.pdf', 'kis-payments/bukti-bayar-37-191-2024.jpg', 1, NULL, '2024-01-24 17:00:00', '2024-01-23 17:00:00', '2024-01-30 17:00:00'),
(196, 57, 9, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 3, 'Dokumen tidak lengkap', NULL, '2024-04-08 17:00:00', '2024-04-13 17:00:00'),
(197, 99, 11, 'kis-docs/ktp-99.jpg', 'kis-docs/pas-foto-99.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-99.pdf', 'kis-payments/bukti-bayar-99-193-2024.jpg', 3, NULL, '2024-01-27 17:00:00', '2024-01-24 17:00:00', '2024-01-29 17:00:00'),
(198, 76, 9, 'kis-docs/ktp-76.jpg', 'kis-docs/pas-foto-76.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-76.pdf', 'kis-payments/bukti-bayar-76-194-2024.jpg', 2, NULL, '2024-04-13 17:00:00', '2024-04-12 17:00:00', '2024-04-17 17:00:00'),
(199, 13, 8, 'kis-docs/ktp-13.jpg', 'kis-docs/pas-foto-13.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-13.pdf', 'kis-payments/bukti-bayar-13-195-2024.jpg', 1, NULL, '2024-02-18 17:00:00', '2024-02-15 17:00:00', '2024-02-16 17:00:00'),
(200, 69, 12, 'kis-docs/ktp-69.jpg', 'kis-docs/pas-foto-69.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-69.pdf', 'kis-payments/bukti-bayar-69-196-2024.jpg', 3, NULL, '2024-10-05 17:00:00', '2024-10-04 17:00:00', '2024-10-10 17:00:00'),
(201, 39, 3, 'kis-docs/ktp-39.jpg', 'kis-docs/pas-foto-39.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-39.pdf', 'kis-payments/bukti-bayar-39-197-2024.jpg', 1, NULL, '2024-08-18 17:00:00', '2024-08-16 17:00:00', '2024-08-17 17:00:00'),
(202, 73, 9, 'kis-docs/ktp-73.jpg', 'kis-docs/pas-foto-73.jpg', NULL, NULL, 'Approved', 'kis-docs/surat-sehat-73.pdf', 'kis-payments/bukti-bayar-73-198-2024.jpg', 1, NULL, '2024-03-27 17:00:00', '2024-03-26 17:00:00', '2024-03-29 17:00:00'),
(203, 89, 2, NULL, NULL, NULL, NULL, 'Rejected', NULL, NULL, 2, 'Dokumen tidak lengkap', NULL, '2024-03-08 17:00:00', '2024-03-09 17:00:00');

--
-- Triggers `kis_applications`
--
DELIMITER $$
CREATE TRIGGER `auto_create_kis_license_on_approval` AFTER UPDATE ON `kis_applications` FOR EACH ROW BEGIN
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
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_kis_application_insert` AFTER INSERT ON `kis_applications` FOR EACH ROW BEGIN
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
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_kis_application_update` AFTER UPDATE ON `kis_applications` FOR EACH ROW BEGIN
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kis_categories`
--

CREATE TABLE `kis_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_kategori` varchar(10) NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `tipe` enum('Mobil','Motor') NOT NULL,
  `biaya_kis` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kis_categories`
--

INSERT INTO `kis_categories` (`id`, `kode_kategori`, `nama_kategori`, `tipe`, `biaya_kis`) VALUES
(1, 'C1', 'Balap Motor, Dragsbike', 'Motor', '0.00'),
(2, 'C2', 'Motocross, Supercross, Grasstrack', 'Motor', '0.00'),
(3, 'C3', 'Rally', 'Motor', '0.00'),
(4, 'A1', 'Racing, Drag Race', 'Mobil', '0.00'),
(5, 'B1', 'Rally/Sprint', 'Mobil', '0.00'),
(6, 'B3', 'Offroad Adventure/Sprint', 'Mobil', '0.00'),
(7, 'B4', 'Drift', 'Mobil', '0.00'),
(8, 'B5', 'Karting', 'Mobil', '0.00'),
(9, 'B6', 'Slalom', 'Mobil', '0.00'),
(10, 'GP', 'Grand Prix', 'Motor', '150000.00'),
(11, 'SB', 'Supersport', 'Motor', '150000.00'),
(12, 'MP', 'Moped', 'Motor', '150000.00');

-- --------------------------------------------------------

--
-- Table structure for table `kis_licenses`
--

CREATE TABLE `kis_licenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pembalap_user_id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `kis_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kis_number` varchar(100) NOT NULL,
  `issued_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kis_licenses`
--

INSERT INTO `kis_licenses` (`id`, `pembalap_user_id`, `application_id`, `kis_category_id`, `kis_number`, `issued_date`, `expiry_date`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 1, 'TEST-KIS-001', '2025-12-13', '2026-12-13', '2025-12-13 16:31:11', '2025-12-13 16:31:11'),
(2, 6, 2, 1, 'TEST-KIS-002', '2025-12-13', '2026-12-13', '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(3, 7, 3, 8, 'TEST-KIS-003', '2025-12-13', '2026-12-13', '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(4, 90, 4, 5, '4/XX/MDN/VIII/2024', '2024-08-08', '2024-12-31', '2024-08-07 17:00:00', '2025-12-13 16:31:31'),
(5, 65, 8, 9, '8/XX/MDN/IV/2024', '2024-04-19', '2024-12-31', '2024-04-18 17:00:00', '2025-12-13 16:31:31'),
(6, 11, 11, 8, '11/XX/MDN/VII/2024', '2024-07-16', '2024-12-31', '2024-07-15 17:00:00', '2025-12-13 16:31:31'),
(7, 83, 14, 11, '14/SB/MDN/II/2024', '2024-02-07', '2024-12-31', '2024-02-06 17:00:00', '2025-12-13 16:31:31'),
(8, 85, 15, 9, '15/XX/MDN/VIII/2024', '2024-08-14', '2024-12-31', '2024-08-13 17:00:00', '2025-12-13 16:31:31'),
(9, 35, 16, 2, '16/XX/MDN/II/2024', '2024-02-24', '2024-12-31', '2024-02-23 17:00:00', '2025-12-13 16:31:31'),
(10, 22, 18, 3, '18/XX/MDN/X/2024', '2024-10-08', '2024-12-31', '2024-10-07 17:00:00', '2025-12-13 16:31:31'),
(11, 23, 19, 2, '19/XX/MDN/IX/2024', '2024-09-14', '2024-12-31', '2024-09-13 17:00:00', '2025-12-13 16:31:31'),
(12, 73, 20, 4, '20/XX/MDN/V/2024', '2024-05-23', '2024-12-31', '2024-05-22 17:00:00', '2025-12-13 16:31:31'),
(13, 71, 24, 11, '24/SB/MDN/VII/2024', '2024-07-01', '2024-12-31', '2024-06-30 17:00:00', '2025-12-13 16:31:31'),
(14, 37, 28, 5, '28/XX/MDN/VII/2024', '2024-07-09', '2024-12-31', '2024-07-08 17:00:00', '2025-12-13 16:31:31'),
(15, 29, 30, 5, '30/XX/MDN/XI/2024', '2024-11-19', '2024-12-31', '2024-11-18 17:00:00', '2025-12-13 16:31:31'),
(16, 61, 31, 6, '31/XX/MDN/III/2024', '2024-03-07', '2024-12-31', '2024-03-06 17:00:00', '2025-12-13 16:31:31'),
(17, 47, 32, 3, '32/XX/MDN/VII/2024', '2024-07-21', '2024-12-31', '2024-07-20 17:00:00', '2025-12-13 16:31:31'),
(18, 102, 33, 12, '33/MP/MDN/VII/2024', '2024-07-15', '2024-12-31', '2024-07-14 17:00:00', '2025-12-13 16:31:31'),
(19, 26, 34, 7, '34/XX/MDN/VIII/2024', '2024-08-23', '2024-12-31', '2024-08-22 17:00:00', '2025-12-13 16:31:31'),
(20, 94, 35, 1, '35/XX/MDN/VIII/2024', '2024-08-18', '2024-12-31', '2024-08-17 17:00:00', '2025-12-13 16:31:31'),
(21, 62, 36, 10, '36/GP/MDN/VII/2024', '2024-07-14', '2024-12-31', '2024-07-13 17:00:00', '2025-12-13 16:31:31'),
(22, 78, 39, 3, '39/XX/MDN/IV/2024', '2024-04-08', '2024-12-31', '2024-04-07 17:00:00', '2025-12-13 16:31:31'),
(23, 55, 41, 6, '41/XX/MDN/II/2024', '2024-02-22', '2024-12-31', '2024-02-21 17:00:00', '2025-12-13 16:31:31'),
(24, 12, 42, 10, '42/GP/MDN/XII/2024', '2024-12-15', '2024-12-31', '2024-12-14 17:00:00', '2025-12-13 16:31:31'),
(25, 8, 46, 4, '46/XX/MDN/II/2024', '2024-02-25', '2024-12-31', '2024-02-24 17:00:00', '2025-12-13 16:31:31'),
(26, 63, 49, 5, '49/XX/MDN/VIII/2024', '2024-08-27', '2024-12-31', '2024-08-26 17:00:00', '2025-12-13 16:31:31'),
(27, 52, 50, 3, '50/XX/MDN/I/2024', '2024-01-20', '2024-12-31', '2024-01-19 17:00:00', '2025-12-13 16:31:31'),
(28, 67, 51, 7, '51/XX/MDN/III/2024', '2024-03-09', '2024-12-31', '2024-03-08 17:00:00', '2025-12-13 16:31:31'),
(29, 64, 52, 11, '52/SB/MDN/V/2024', '2024-05-08', '2024-12-31', '2024-05-07 17:00:00', '2025-12-13 16:31:31'),
(30, 70, 54, 8, '54/XX/MDN/XII/2024', '2024-12-11', '2024-12-31', '2024-12-10 17:00:00', '2025-12-13 16:31:31'),
(31, 31, 55, 2, '55/XX/MDN/I/2024', '2024-01-10', '2024-12-31', '2024-01-09 17:00:00', '2025-12-13 16:31:31'),
(32, 38, 56, 5, '56/XX/MDN/IX/2024', '2024-09-14', '2024-12-31', '2024-09-13 17:00:00', '2025-12-13 16:31:31'),
(33, 9, 57, 8, '57/XX/MDN/IX/2024', '2024-09-03', '2024-12-31', '2024-09-02 17:00:00', '2025-12-13 16:31:31'),
(34, 53, 58, 1, '58/XX/MDN/XI/2024', '2024-11-07', '2024-12-31', '2024-11-06 17:00:00', '2025-12-13 16:31:31'),
(35, 106, 59, 5, '59/XX/MDN/VIII/2024', '2024-08-09', '2024-12-31', '2024-08-08 17:00:00', '2025-12-13 16:31:31'),
(36, 56, 61, 2, '61/XX/MDN/X/2024', '2024-10-11', '2024-12-31', '2024-10-10 17:00:00', '2025-12-13 16:31:31'),
(37, 68, 66, 4, '66/XX/MDN/III/2024', '2024-03-24', '2024-12-31', '2024-03-23 17:00:00', '2025-12-13 16:31:31'),
(38, 57, 67, 1, '67/XX/MDN/XII/2024', '2024-12-03', '2024-12-31', '2024-12-02 17:00:00', '2025-12-13 16:31:31'),
(39, 100, 68, 6, '68/XX/MDN/V/2024', '2024-05-04', '2024-12-31', '2024-05-03 17:00:00', '2025-12-13 16:31:31'),
(40, 49, 69, 12, '69/MP/MDN/III/2024', '2024-03-03', '2024-12-31', '2024-03-02 17:00:00', '2025-12-13 16:31:31'),
(41, 18, 70, 9, '70/XX/MDN/IV/2024', '2024-04-01', '2024-12-31', '2024-03-31 17:00:00', '2025-12-13 16:31:31'),
(42, 32, 75, 3, '75/XX/MDN/XII/2024', '2024-12-05', '2024-12-31', '2024-12-04 17:00:00', '2025-12-13 16:31:31'),
(43, 44, 76, 12, '76/MP/MDN/VII/2024', '2024-07-09', '2024-12-31', '2024-07-08 17:00:00', '2025-12-13 16:31:31'),
(44, 21, 77, 8, '77/XX/MDN/XII/2024', '2024-12-04', '2024-12-31', '2024-12-03 17:00:00', '2025-12-13 16:31:31'),
(45, 95, 79, 4, '79/XX/MDN/VI/2024', '2024-06-08', '2024-12-31', '2024-06-07 17:00:00', '2025-12-13 16:31:31'),
(46, 60, 80, 2, '80/XX/MDN/III/2024', '2024-03-10', '2024-12-31', '2024-03-09 17:00:00', '2025-12-13 16:31:31'),
(47, 91, 82, 10, '82/GP/MDN/I/2024', '2024-01-27', '2024-12-31', '2024-01-26 17:00:00', '2025-12-13 16:31:31'),
(48, 101, 83, 12, '83/MP/MDN/VI/2024', '2024-06-26', '2024-12-31', '2024-06-25 17:00:00', '2025-12-13 16:31:31'),
(49, 36, 84, 5, '84/XX/MDN/IV/2024', '2024-04-11', '2024-12-31', '2024-04-10 17:00:00', '2025-12-13 16:31:31'),
(50, 20, 85, 3, '85/XX/MDN/XII/2024', '2024-12-08', '2024-12-31', '2024-12-07 17:00:00', '2025-12-13 16:31:31'),
(51, 87, 89, 11, '89/SB/MDN/VI/2024', '2024-06-22', '2024-12-31', '2024-06-21 17:00:00', '2025-12-13 16:31:31'),
(52, 107, 92, 4, '92/XX/MDN/IV/2024', '2024-04-04', '2024-12-31', '2024-04-03 17:00:00', '2025-12-13 16:31:31'),
(53, 46, 93, 1, '93/XX/MDN/XII/2024', '2024-12-24', '2024-12-31', '2024-12-23 17:00:00', '2025-12-13 16:31:31'),
(54, 14, 107, 5, '107/XX/MDN/IX/2024', '2024-09-18', '2024-12-31', '2024-09-17 17:00:00', '2025-12-13 16:31:31'),
(55, 41, 111, 5, '111/XX/MDN/V/2024', '2024-05-23', '2024-12-31', '2024-05-22 17:00:00', '2025-12-13 16:31:31'),
(56, 74, 118, 1, '118/XX/MDN/XII/2024', '2024-12-21', '2024-12-31', '2024-12-20 17:00:00', '2025-12-13 16:31:31'),
(57, 82, 120, 9, '120/XX/MDN/X/2024', '2024-10-25', '2024-12-31', '2024-10-24 17:00:00', '2025-12-13 16:31:31'),
(58, 27, 124, 8, '124/XX/MDN/IV/2024', '2024-04-17', '2024-12-31', '2024-04-16 17:00:00', '2025-12-13 16:31:31'),
(59, 43, 129, 3, '129/XX/MDN/VI/2024', '2024-06-22', '2024-12-31', '2024-06-21 17:00:00', '2025-12-13 16:31:31'),
(60, 59, 134, 4, '134/XX/MDN/VI/2024', '2024-06-05', '2024-12-31', '2024-06-04 17:00:00', '2025-12-13 16:31:31'),
(61, 69, 146, 3, '146/XX/MDN/V/2024', '2024-05-28', '2024-12-31', '2024-05-27 17:00:00', '2025-12-13 16:31:31'),
(62, 66, 147, 10, '147/GP/MDN/VI/2024', '2024-06-23', '2024-12-31', '2024-06-22 17:00:00', '2025-12-13 16:31:31'),
(63, 19, 148, 8, '148/XX/MDN/IX/2024', '2024-09-05', '2024-12-31', '2024-09-04 17:00:00', '2025-12-13 16:31:31'),
(64, 80, 149, 4, '149/XX/MDN/III/2024', '2024-03-07', '2024-12-31', '2024-03-06 17:00:00', '2025-12-13 16:31:32'),
(65, 93, 157, 7, '157/XX/MDN/III/2024', '2024-03-11', '2024-12-31', '2024-03-10 17:00:00', '2025-12-13 16:31:32'),
(66, 39, 159, 5, '159/XX/MDN/IV/2024', '2024-04-19', '2024-12-31', '2024-04-18 17:00:00', '2025-12-13 16:31:32'),
(67, 84, 160, 9, '160/XX/MDN/II/2024', '2024-02-11', '2024-12-31', '2024-02-10 17:00:00', '2025-12-13 16:31:32'),
(68, 86, 163, 10, '163/GP/MDN/IX/2024', '2024-09-10', '2024-12-31', '2024-09-09 17:00:00', '2025-12-13 16:31:32'),
(69, 96, 172, 4, '172/XX/MDN/V/2024', '2024-05-28', '2024-12-31', '2024-05-27 17:00:00', '2025-12-13 16:31:32'),
(70, 99, 176, 4, '176/XX/MDN/IX/2024', '2024-09-13', '2024-12-31', '2024-09-12 17:00:00', '2025-12-13 16:31:32'),
(71, 40, 181, 5, '181/XX/MDN/XI/2024', '2024-11-17', '2024-12-31', '2024-11-16 17:00:00', '2025-12-13 16:31:32'),
(72, 89, 186, 6, '186/XX/MDN/III/2024', '2024-03-17', '2024-12-31', '2024-03-16 17:00:00', '2025-12-13 16:31:32'),
(73, 98, 191, 10, '191/GP/MDN/I/2024', '2024-01-19', '2024-12-31', '2024-01-18 17:00:00', '2025-12-13 16:31:32'),
(74, 103, 192, 12, '192/MP/MDN/VIII/2024', '2024-08-18', '2024-12-31', '2024-08-17 17:00:00', '2025-12-13 16:31:32'),
(75, 76, 198, 9, '198/XX/MDN/IV/2024', '2024-04-13', '2024-12-31', '2024-04-12 17:00:00', '2025-12-13 16:31:32'),
(76, 13, 199, 8, '199/XX/MDN/II/2024', '2024-02-16', '2024-12-31', '2024-02-15 17:00:00', '2025-12-13 16:31:32');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action_type` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` bigint(20) UNSIGNED NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `action_type`, `table_name`, `record_id`, `old_value`, `new_value`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'INSERT', 'kis_applications', 1, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 5, '2025-12-13 16:31:11', '2025-12-13 16:31:11'),
(2, 'INSERT', 'kis_applications', 2, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 6, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(3, 'INSERT', 'kis_applications', 3, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 7, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(4, 'INSERT', 'events', 1, NULL, 'Event baru dibuat: Kejuaraan Tes (SELESAI)', 2, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(5, 'INSERT', 'events', 2, NULL, 'Event baru dibuat: Kejuaraan Tes (AKAN DATANG)', 2, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(6, 'INSERT', 'event_registrations', 1, NULL, 'Pendaftaran event: Kejuaraan Tes (SELESAI) (Status: Confirmed)', 5, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(7, 'INSERT', 'event_registrations', 2, NULL, 'Pendaftaran event: Kejuaraan Tes (AKAN DATANG) (Status: Pending Confirmation)', 6, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(8, 'INSERT', 'event_registrations', 3, NULL, 'Pendaftaran event: Kejuaraan Tes (AKAN DATANG) (Status: Rejected)', 7, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(9, 'INSERT', 'club_dues', 1, NULL, 'Iuran klub: Apex Motorsport tahun 2024 - Rp5,000,000', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(10, 'INSERT', 'club_dues', 2, NULL, 'Iuran klub: Dragon Racing Team tahun 2024 - Rp5,000,000', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(11, 'INSERT', 'club_dues', 3, NULL, 'Iuran klub: Eagle Motorsport Team tahun 2024 - Rp5,000,000', NULL, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(12, 'INSERT', 'club_dues', 4, NULL, 'Iuran klub: Falcon Motorsport tahun 2024 - Rp5,000,000', NULL, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(13, 'INSERT', 'club_dues', 5, NULL, 'Iuran klub: IMI Sumut Official tahun 2024 - Rp5,000,000', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(14, 'INSERT', 'club_dues', 6, NULL, 'Iuran klub: Kitakita Motorsport tahun 2024 - Rp5,000,000', NULL, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(15, 'INSERT', 'club_dues', 7, NULL, 'Iuran klub: Nitro Racing Team tahun 2024 - Rp5,000,000', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(16, 'INSERT', 'club_dues', 8, NULL, 'Iuran klub: Phoenix Motorsport tahun 2024 - Rp5,000,000', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(17, 'INSERT', 'club_dues', 9, NULL, 'Iuran klub: Racing Club Medan tahun 2024 - Rp5,000,000', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(18, 'INSERT', 'club_dues', 10, NULL, 'Iuran klub: SPEED\'ER MOTORSPORT tahun 2024 - Rp5,000,000', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(19, 'INSERT', 'club_dues', 11, NULL, 'Iuran klub: Speedster Motorsport tahun 2024 - Rp5,000,000', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(20, 'INSERT', 'club_dues', 12, NULL, 'Iuran klub: Thunder Racing Team tahun 2024 - Rp5,000,000', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(21, 'INSERT', 'club_dues', 13, NULL, 'Iuran klub: Turbo Racing Club tahun 2024 - Rp5,000,000', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(22, 'INSERT', 'club_dues', 14, NULL, 'Iuran klub: Velocity Club Sumut tahun 2024 - Rp5,000,000', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(23, 'INSERT', 'club_dues', 15, NULL, 'Iuran klub: Viper Racing Club tahun 2024 - Rp5,000,000', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(24, 'INSERT', 'events', 3, NULL, 'Event baru dibuat: New Year Cup 2024', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(25, 'INSERT', 'events', 4, NULL, 'Event baru dibuat: Lunar Racing Championship', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(26, 'INSERT', 'events', 5, NULL, 'Event baru dibuat: Independence Day Race', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(27, 'INSERT', 'events', 6, NULL, 'Event baru dibuat: Spring Championship Round 1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(28, 'INSERT', 'events', 7, NULL, 'Event baru dibuat: Spring Championship Round 2', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(29, 'INSERT', 'events', 8, NULL, 'Event baru dibuat: Spring Championship Round 3', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(30, 'INSERT', 'events', 9, NULL, 'Event baru dibuat: Kejurda Medan Q1 Round 1', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(31, 'INSERT', 'events', 10, NULL, 'Event baru dibuat: Kejurda Medan Q1 Round 2', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(32, 'INSERT', 'events', 11, NULL, 'Event baru dibuat: Kejurda Medan Q1 Round 3', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(33, 'INSERT', 'events', 12, NULL, 'Event baru dibuat: Open Track Day January', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(34, 'INSERT', 'events', 13, NULL, 'Event baru dibuat: Valentine Special Race', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(35, 'INSERT', 'events', 14, NULL, 'Event baru dibuat: March Madness Race', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(36, 'INSERT', 'events', 15, NULL, 'Event baru dibuat: Pematang Siantar Cup 1', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(37, 'INSERT', 'events', 16, NULL, 'Event baru dibuat: Binjai Racing Series 1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(38, 'INSERT', 'events', 17, NULL, 'Event baru dibuat: Tebing Tinggi Open 1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(39, 'INSERT', 'events', 18, NULL, 'Event baru dibuat: Summer Heat Championship R1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(40, 'INSERT', 'events', 19, NULL, 'Event baru dibuat: Summer Heat Championship R2', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(41, 'INSERT', 'events', 20, NULL, 'Event baru dibuat: Summer Heat Championship R3', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(42, 'INSERT', 'events', 21, NULL, 'Event baru dibuat: Kejurprov Sumut Series 1', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(43, 'INSERT', 'events', 22, NULL, 'Event baru dibuat: Kejurprov Sumut Series 2', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(44, 'INSERT', 'events', 23, NULL, 'Event baru dibuat: Kejurprov Sumut Series 3', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(45, 'INSERT', 'events', 24, NULL, 'Event baru dibuat: Ramadan Cup 2024', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(46, 'INSERT', 'events', 25, NULL, 'Event baru dibuat: Eid Mubarak Race', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(47, 'INSERT', 'events', 26, NULL, 'Event baru dibuat: Independence Preparation Cup', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(48, 'INSERT', 'events', 27, NULL, 'Event baru dibuat: Mid Year Championship', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(49, 'INSERT', 'events', 28, NULL, 'Event baru dibuat: Open Track Day April', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(50, 'INSERT', 'events', 29, NULL, 'Event baru dibuat: May Day Racing', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(51, 'INSERT', 'events', 30, NULL, 'Event baru dibuat: Pematang Siantar Cup 2', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(52, 'INSERT', 'events', 31, NULL, 'Event baru dibuat: Binjai Racing Series 2', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(53, 'INSERT', 'events', 32, NULL, 'Event baru dibuat: June Thunder Race', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(54, 'INSERT', 'events', 33, NULL, 'Event baru dibuat: Independence Day Special', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(55, 'INSERT', 'events', 34, NULL, 'Event baru dibuat: August 17 Freedom Race', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(56, 'INSERT', 'events', 35, NULL, 'Event baru dibuat: Merdeka Cup 2024', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(57, 'INSERT', 'events', 36, NULL, 'Event baru dibuat: Autumn Championship R1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(58, 'INSERT', 'events', 37, NULL, 'Event baru dibuat: Autumn Championship R2', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(59, 'INSERT', 'events', 38, NULL, 'Event baru dibuat: Autumn Championship R3', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(60, 'INSERT', 'events', 39, NULL, 'Event baru dibuat: Grasstrack Medan Cup 1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(61, 'INSERT', 'events', 40, NULL, 'Event baru dibuat: Grasstrack Medan Cup 2', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(62, 'INSERT', 'events', 41, NULL, 'Event baru dibuat: Grasstrack Medan Cup 3', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(63, 'INSERT', 'events', 42, NULL, 'Event baru dibuat: National Championship Q3 R1', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(64, 'INSERT', 'events', 43, NULL, 'Event baru dibuat: Open Track Day July', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(65, 'INSERT', 'events', 44, NULL, 'Event baru dibuat: September Speed Fest', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(66, 'INSERT', 'events', 45, NULL, 'Event baru dibuat: Tebing Tinggi Open 2', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(67, 'INSERT', 'events', 46, NULL, 'Event baru dibuat: Binjai Racing Series 3', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(68, 'INSERT', 'events', 47, NULL, 'Event baru dibuat: Sumut Grand Prix Round 1', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(69, 'INSERT', 'events', 48, NULL, 'Event baru dibuat: Year End Championship R1', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(70, 'INSERT', 'events', 49, NULL, 'Event baru dibuat: Year End Championship R2', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(71, 'INSERT', 'events', 50, NULL, 'Event baru dibuat: Year End Championship R3', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(72, 'INSERT', 'events', 51, NULL, 'Event baru dibuat: Grand Final Preparation', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(73, 'INSERT', 'events', 52, NULL, 'Event baru dibuat: Semi Final Championship', 2, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(74, 'INSERT', 'events', 53, NULL, 'Event baru dibuat: Grand Final Championship 2024', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(75, 'INSERT', 'events', 54, NULL, 'Event baru dibuat: Christmas Special Race', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(76, 'INSERT', 'events', 55, NULL, 'Event baru dibuat: New Year Preparation Cup', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(77, 'INSERT', 'events', 56, NULL, 'Event baru dibuat: Endurance Race 2024', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(78, 'INSERT', 'events', 57, NULL, 'Event baru dibuat: October Fast Race', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(79, 'INSERT', 'events', 58, NULL, 'Event baru dibuat: November Thunder', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(80, 'INSERT', 'events', 59, NULL, 'Event baru dibuat: December Speedway', 1, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(81, 'INSERT', 'events', 60, NULL, 'Event baru dibuat: Pematang Siantar Cup 3', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(82, 'INSERT', 'events', 61, NULL, 'Event baru dibuat: Sumut Grand Prix Round 2', 4, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(83, 'INSERT', 'events', 62, NULL, 'Event baru dibuat: IMI Sumut Closing Race', 3, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(84, 'INSERT', 'kis_applications', 4, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 90, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(85, 'INSERT', 'kis_applications', 5, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 107, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(86, 'INSERT', 'kis_applications', 6, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 38, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(87, 'INSERT', 'kis_applications', 7, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 18, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(88, 'INSERT', 'kis_applications', 8, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 65, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(89, 'INSERT', 'kis_applications', 9, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 57, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(90, 'INSERT', 'kis_applications', 10, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 22, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(91, 'INSERT', 'kis_applications', 11, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 11, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(92, 'INSERT', 'kis_applications', 12, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 41, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(93, 'INSERT', 'kis_applications', 13, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 42, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(94, 'INSERT', 'kis_applications', 14, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 83, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(95, 'INSERT', 'kis_applications', 15, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 85, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(96, 'INSERT', 'kis_applications', 16, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 35, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(97, 'INSERT', 'kis_applications', 17, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 26, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(98, 'INSERT', 'kis_applications', 18, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 22, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(99, 'INSERT', 'kis_applications', 19, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 23, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(100, 'INSERT', 'kis_applications', 20, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 73, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(101, 'INSERT', 'kis_applications', 21, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 99, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(102, 'INSERT', 'kis_applications', 22, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 95, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(103, 'INSERT', 'kis_applications', 23, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 55, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(104, 'INSERT', 'kis_applications', 24, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 71, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(105, 'INSERT', 'kis_applications', 25, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 97, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(106, 'INSERT', 'kis_applications', 26, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 59, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(107, 'INSERT', 'kis_applications', 27, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 75, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(108, 'INSERT', 'kis_applications', 28, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 37, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(109, 'INSERT', 'kis_applications', 29, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 18, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(110, 'INSERT', 'kis_applications', 30, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 29, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(111, 'INSERT', 'kis_applications', 31, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 61, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(112, 'INSERT', 'kis_applications', 32, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 47, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(113, 'INSERT', 'kis_applications', 33, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 102, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(114, 'INSERT', 'kis_applications', 34, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 26, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(115, 'INSERT', 'kis_applications', 35, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 94, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(116, 'INSERT', 'kis_applications', 36, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 62, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(117, 'INSERT', 'kis_applications', 37, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 22, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(118, 'INSERT', 'kis_applications', 38, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 83, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(119, 'INSERT', 'kis_applications', 39, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 78, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(120, 'INSERT', 'kis_applications', 40, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 65, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(121, 'INSERT', 'kis_applications', 41, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 55, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(122, 'INSERT', 'kis_applications', 42, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 12, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(123, 'INSERT', 'kis_applications', 43, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 26, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(124, 'INSERT', 'kis_applications', 44, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 77, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(125, 'INSERT', 'kis_applications', 45, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 97, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(126, 'INSERT', 'kis_applications', 46, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 8, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(127, 'INSERT', 'kis_applications', 47, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 81, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(128, 'INSERT', 'kis_applications', 48, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 8, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(129, 'INSERT', 'kis_applications', 49, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 63, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(130, 'INSERT', 'kis_applications', 50, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 52, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(131, 'INSERT', 'kis_applications', 51, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 67, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(132, 'INSERT', 'kis_applications', 52, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 64, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(133, 'INSERT', 'kis_applications', 53, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 35, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(134, 'INSERT', 'kis_applications', 54, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 70, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(135, 'INSERT', 'kis_applications', 55, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 31, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(136, 'INSERT', 'kis_applications', 56, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 38, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(137, 'INSERT', 'kis_applications', 57, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 9, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(138, 'INSERT', 'kis_applications', 58, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 53, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(139, 'INSERT', 'kis_applications', 59, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 106, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(140, 'INSERT', 'kis_applications', 60, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 74, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(141, 'INSERT', 'kis_applications', 61, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 56, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(142, 'INSERT', 'kis_applications', 62, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 53, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(143, 'INSERT', 'kis_applications', 63, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 9, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(144, 'INSERT', 'kis_applications', 64, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 11, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(145, 'INSERT', 'kis_applications', 65, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 74, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(146, 'INSERT', 'kis_applications', 66, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 68, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(147, 'INSERT', 'kis_applications', 67, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 57, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(148, 'INSERT', 'kis_applications', 68, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 100, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(149, 'INSERT', 'kis_applications', 69, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 49, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(150, 'INSERT', 'kis_applications', 70, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 18, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(151, 'INSERT', 'kis_applications', 71, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 90, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(152, 'INSERT', 'kis_applications', 72, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 72, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(153, 'INSERT', 'kis_applications', 73, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 86, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(154, 'INSERT', 'kis_applications', 74, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 17, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(155, 'INSERT', 'kis_applications', 75, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 32, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(156, 'INSERT', 'kis_applications', 76, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 44, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(157, 'INSERT', 'kis_applications', 77, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 21, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(158, 'INSERT', 'kis_applications', 78, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 56, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(159, 'INSERT', 'kis_applications', 79, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 95, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(160, 'INSERT', 'kis_applications', 80, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 60, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(161, 'INSERT', 'kis_applications', 81, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 98, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(162, 'INSERT', 'kis_applications', 82, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 91, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(163, 'INSERT', 'kis_applications', 83, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 101, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(164, 'INSERT', 'kis_applications', 84, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 36, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(165, 'INSERT', 'kis_applications', 85, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 20, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(166, 'INSERT', 'kis_applications', 86, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 55, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(167, 'INSERT', 'kis_applications', 87, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 45, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(168, 'INSERT', 'kis_applications', 88, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 20, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(169, 'INSERT', 'kis_applications', 89, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 87, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(170, 'INSERT', 'kis_applications', 90, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 95, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(171, 'INSERT', 'kis_applications', 91, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 94, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(172, 'INSERT', 'kis_applications', 92, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 107, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(173, 'INSERT', 'kis_applications', 93, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 46, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(174, 'INSERT', 'kis_applications', 94, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 24, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(175, 'INSERT', 'kis_applications', 95, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 46, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(176, 'INSERT', 'kis_applications', 96, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 100, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(177, 'INSERT', 'kis_applications', 97, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 39, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(178, 'INSERT', 'kis_applications', 98, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 71, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(179, 'INSERT', 'kis_applications', 99, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 87, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(180, 'INSERT', 'kis_applications', 100, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 80, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(181, 'INSERT', 'kis_applications', 101, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 94, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(182, 'INSERT', 'kis_applications', 102, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 104, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(183, 'INSERT', 'kis_applications', 103, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 78, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(184, 'INSERT', 'kis_applications', 104, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 12, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(185, 'INSERT', 'kis_applications', 105, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 92, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(186, 'INSERT', 'kis_applications', 106, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 32, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(187, 'INSERT', 'kis_applications', 107, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 14, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(188, 'INSERT', 'kis_applications', 108, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 36, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(189, 'INSERT', 'kis_applications', 109, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 20, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(190, 'INSERT', 'kis_applications', 110, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 62, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(191, 'INSERT', 'kis_applications', 111, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 41, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(192, 'INSERT', 'kis_applications', 112, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 106, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(193, 'INSERT', 'kis_applications', 113, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 61, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(194, 'INSERT', 'kis_applications', 114, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 68, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(195, 'INSERT', 'kis_applications', 115, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 28, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(196, 'INSERT', 'kis_applications', 116, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 102, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(197, 'INSERT', 'kis_applications', 117, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 71, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(198, 'INSERT', 'kis_applications', 118, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 74, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(199, 'INSERT', 'kis_applications', 119, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 91, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(200, 'INSERT', 'kis_applications', 120, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 82, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(201, 'INSERT', 'kis_applications', 121, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 99, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(202, 'INSERT', 'kis_applications', 122, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 15, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(203, 'INSERT', 'kis_applications', 123, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 78, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(204, 'INSERT', 'kis_applications', 124, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 27, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(205, 'INSERT', 'kis_applications', 125, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 31, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(206, 'INSERT', 'kis_applications', 126, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 78, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(207, 'INSERT', 'kis_applications', 127, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 68, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(208, 'INSERT', 'kis_applications', 128, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 47, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(209, 'INSERT', 'kis_applications', 129, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 43, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(210, 'INSERT', 'kis_applications', 130, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 68, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(211, 'INSERT', 'kis_applications', 131, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 37, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(212, 'INSERT', 'kis_applications', 132, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 48, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(213, 'INSERT', 'kis_applications', 133, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 29, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(214, 'INSERT', 'kis_applications', 134, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 59, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(215, 'INSERT', 'kis_applications', 135, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 36, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(216, 'INSERT', 'kis_applications', 136, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 11, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(217, 'INSERT', 'kis_applications', 137, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 49, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(218, 'INSERT', 'kis_applications', 138, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 107, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(219, 'INSERT', 'kis_applications', 139, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 107, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(220, 'INSERT', 'kis_applications', 140, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 74, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(221, 'INSERT', 'kis_applications', 141, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 88, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(222, 'INSERT', 'kis_applications', 142, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 46, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(223, 'INSERT', 'kis_applications', 143, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 32, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(224, 'INSERT', 'kis_applications', 144, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 75, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(225, 'INSERT', 'kis_applications', 145, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 41, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(226, 'INSERT', 'kis_applications', 146, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 69, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(227, 'INSERT', 'kis_applications', 147, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 66, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(228, 'INSERT', 'kis_applications', 148, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 19, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(229, 'INSERT', 'kis_applications', 149, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 80, '2025-12-13 16:31:31', '2025-12-13 16:31:31'),
(230, 'INSERT', 'kis_applications', 150, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 74, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(231, 'INSERT', 'kis_applications', 151, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 106, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(232, 'INSERT', 'kis_applications', 152, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 35, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(233, 'INSERT', 'kis_applications', 153, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 51, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(234, 'INSERT', 'kis_applications', 154, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(235, 'INSERT', 'kis_applications', 155, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(236, 'INSERT', 'kis_applications', 156, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 53, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(237, 'INSERT', 'kis_applications', 157, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 93, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(238, 'INSERT', 'kis_applications', 158, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(239, 'INSERT', 'kis_applications', 159, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 39, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(240, 'INSERT', 'kis_applications', 160, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 84, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(241, 'INSERT', 'kis_applications', 161, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 95, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(242, 'INSERT', 'kis_applications', 162, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 87, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(243, 'INSERT', 'kis_applications', 163, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 86, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(244, 'INSERT', 'kis_applications', 164, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 68, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(245, 'INSERT', 'kis_applications', 165, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 60, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(246, 'INSERT', 'kis_applications', 166, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 64, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(247, 'INSERT', 'kis_applications', 167, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 66, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(248, 'INSERT', 'kis_applications', 168, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 49, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(249, 'INSERT', 'kis_applications', 169, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(250, 'INSERT', 'kis_applications', 170, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 38, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(251, 'INSERT', 'kis_applications', 171, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 57, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(252, 'INSERT', 'kis_applications', 172, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 96, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(253, 'INSERT', 'kis_applications', 173, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 86, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(254, 'INSERT', 'kis_applications', 174, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(255, 'INSERT', 'kis_applications', 175, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 81, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(256, 'INSERT', 'kis_applications', 176, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 99, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(257, 'INSERT', 'kis_applications', 177, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(258, 'INSERT', 'kis_applications', 178, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 47, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(259, 'INSERT', 'kis_applications', 179, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 102, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(260, 'INSERT', 'kis_applications', 180, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 59, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(261, 'INSERT', 'kis_applications', 181, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 40, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(262, 'INSERT', 'kis_applications', 182, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 39, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(263, 'INSERT', 'kis_applications', 183, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 95, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(264, 'INSERT', 'kis_applications', 184, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(265, 'INSERT', 'kis_applications', 185, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 99, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(266, 'INSERT', 'kis_applications', 186, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 89, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(267, 'INSERT', 'kis_applications', 187, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 73, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(268, 'INSERT', 'kis_applications', 188, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(269, 'INSERT', 'kis_applications', 189, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(270, 'INSERT', 'kis_applications', 190, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(271, 'INSERT', 'kis_applications', 191, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 98, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(272, 'INSERT', 'kis_applications', 192, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 103, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(273, 'INSERT', 'kis_applications', 193, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 41, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(274, 'INSERT', 'kis_applications', 194, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 101, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(275, 'INSERT', 'kis_applications', 195, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 37, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(276, 'INSERT', 'kis_applications', 196, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 57, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(277, 'INSERT', 'kis_applications', 197, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 99, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(278, 'INSERT', 'kis_applications', 198, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 76, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(279, 'INSERT', 'kis_applications', 199, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(280, 'INSERT', 'kis_applications', 200, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 69, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(281, 'INSERT', 'kis_applications', 201, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 39, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(282, 'INSERT', 'kis_applications', 202, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 73, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(283, 'INSERT', 'kis_applications', 203, NULL, 'Pengajuan KIS baru (Otomatis: Status Pending)', 89, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(284, 'INSERT', 'event_registrations', 4, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(285, 'INSERT', 'event_registrations', 5, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(286, 'INSERT', 'event_registrations', 6, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(287, 'INSERT', 'event_registrations', 7, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(288, 'INSERT', 'event_registrations', 8, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(289, 'INSERT', 'event_registrations', 9, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(290, 'INSERT', 'event_registrations', 10, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(291, 'INSERT', 'event_registrations', 11, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(292, 'INSERT', 'event_registrations', 12, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(293, 'INSERT', 'event_registrations', 13, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(294, 'INSERT', 'event_registrations', 14, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(295, 'INSERT', 'event_registrations', 15, NULL, 'Pendaftaran event: New Year Cup 2024 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(296, 'INSERT', 'event_registrations', 16, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(297, 'INSERT', 'event_registrations', 17, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(298, 'INSERT', 'event_registrations', 18, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(299, 'INSERT', 'event_registrations', 19, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(300, 'INSERT', 'event_registrations', 20, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(301, 'INSERT', 'event_registrations', 21, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(302, 'INSERT', 'event_registrations', 22, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(303, 'INSERT', 'event_registrations', 23, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(304, 'INSERT', 'event_registrations', 24, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(305, 'INSERT', 'event_registrations', 25, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(306, 'INSERT', 'event_registrations', 26, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(307, 'INSERT', 'event_registrations', 27, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Pending Confirmation)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(308, 'INSERT', 'event_registrations', 28, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(309, 'INSERT', 'event_registrations', 29, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(310, 'INSERT', 'event_registrations', 30, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(311, 'INSERT', 'event_registrations', 31, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Pending Confirmation)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(312, 'INSERT', 'event_registrations', 32, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(313, 'INSERT', 'event_registrations', 33, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(314, 'INSERT', 'event_registrations', 34, NULL, 'Pendaftaran event: Lunar Racing Championship (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(315, 'INSERT', 'event_registrations', 35, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(316, 'INSERT', 'event_registrations', 36, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(317, 'INSERT', 'event_registrations', 37, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(318, 'INSERT', 'event_registrations', 38, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(319, 'INSERT', 'event_registrations', 39, NULL, 'Pendaftaran event: Independence Day Race (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(320, 'INSERT', 'event_registrations', 40, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(321, 'INSERT', 'event_registrations', 41, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(322, 'INSERT', 'event_registrations', 42, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(323, 'INSERT', 'event_registrations', 43, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(324, 'INSERT', 'event_registrations', 44, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(325, 'INSERT', 'event_registrations', 45, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(326, 'INSERT', 'event_registrations', 46, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(327, 'INSERT', 'event_registrations', 47, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(328, 'INSERT', 'event_registrations', 48, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(329, 'INSERT', 'event_registrations', 49, NULL, 'Pendaftaran event: Independence Day Race (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(330, 'INSERT', 'event_registrations', 50, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(331, 'INSERT', 'event_registrations', 51, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(332, 'INSERT', 'event_registrations', 52, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(333, 'INSERT', 'event_registrations', 53, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(334, 'INSERT', 'event_registrations', 54, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(335, 'INSERT', 'event_registrations', 55, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(336, 'INSERT', 'event_registrations', 56, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(337, 'INSERT', 'event_registrations', 57, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(338, 'INSERT', 'event_registrations', 58, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(339, 'INSERT', 'event_registrations', 59, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(340, 'INSERT', 'event_registrations', 60, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(341, 'INSERT', 'event_registrations', 61, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(342, 'INSERT', 'event_registrations', 62, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32');
INSERT INTO `logs` (`id`, `action_type`, `table_name`, `record_id`, `old_value`, `new_value`, `user_id`, `created_at`, `updated_at`) VALUES
(343, 'INSERT', 'event_registrations', 63, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(344, 'INSERT', 'event_registrations', 64, NULL, 'Pendaftaran event: Spring Championship Round 1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(345, 'INSERT', 'event_registrations', 65, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(346, 'INSERT', 'event_registrations', 66, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(347, 'INSERT', 'event_registrations', 67, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(348, 'INSERT', 'event_registrations', 68, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(349, 'INSERT', 'event_registrations', 69, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(350, 'INSERT', 'event_registrations', 70, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(351, 'INSERT', 'event_registrations', 71, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(352, 'INSERT', 'event_registrations', 72, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(353, 'INSERT', 'event_registrations', 73, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(354, 'INSERT', 'event_registrations', 74, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(355, 'INSERT', 'event_registrations', 75, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(356, 'INSERT', 'event_registrations', 76, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(357, 'INSERT', 'event_registrations', 77, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(358, 'INSERT', 'event_registrations', 78, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(359, 'INSERT', 'event_registrations', 79, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(360, 'INSERT', 'event_registrations', 80, NULL, 'Pendaftaran event: Spring Championship Round 2 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(361, 'INSERT', 'event_registrations', 81, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(362, 'INSERT', 'event_registrations', 82, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(363, 'INSERT', 'event_registrations', 83, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(364, 'INSERT', 'event_registrations', 84, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(365, 'INSERT', 'event_registrations', 85, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(366, 'INSERT', 'event_registrations', 86, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(367, 'INSERT', 'event_registrations', 87, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(368, 'INSERT', 'event_registrations', 88, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(369, 'INSERT', 'event_registrations', 89, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(370, 'INSERT', 'event_registrations', 90, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(371, 'INSERT', 'event_registrations', 91, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(372, 'INSERT', 'event_registrations', 92, NULL, 'Pendaftaran event: Spring Championship Round 3 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(373, 'INSERT', 'event_registrations', 93, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(374, 'INSERT', 'event_registrations', 94, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(375, 'INSERT', 'event_registrations', 95, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(376, 'INSERT', 'event_registrations', 96, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(377, 'INSERT', 'event_registrations', 97, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(378, 'INSERT', 'event_registrations', 98, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(379, 'INSERT', 'event_registrations', 99, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(380, 'INSERT', 'event_registrations', 100, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(381, 'INSERT', 'event_registrations', 101, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(382, 'INSERT', 'event_registrations', 102, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(383, 'INSERT', 'event_registrations', 103, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(384, 'INSERT', 'event_registrations', 104, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(385, 'INSERT', 'event_registrations', 105, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(386, 'INSERT', 'event_registrations', 106, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(387, 'INSERT', 'event_registrations', 107, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(388, 'INSERT', 'event_registrations', 108, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 1 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(389, 'INSERT', 'event_registrations', 109, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(390, 'INSERT', 'event_registrations', 110, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(391, 'INSERT', 'event_registrations', 111, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(392, 'INSERT', 'event_registrations', 112, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(393, 'INSERT', 'event_registrations', 113, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(394, 'INSERT', 'event_registrations', 114, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(395, 'INSERT', 'event_registrations', 115, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(396, 'INSERT', 'event_registrations', 116, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(397, 'INSERT', 'event_registrations', 117, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(398, 'INSERT', 'event_registrations', 118, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(399, 'INSERT', 'event_registrations', 119, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(400, 'INSERT', 'event_registrations', 120, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(401, 'INSERT', 'event_registrations', 121, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(402, 'INSERT', 'event_registrations', 122, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(403, 'INSERT', 'event_registrations', 123, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(404, 'INSERT', 'event_registrations', 124, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 2 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(405, 'INSERT', 'event_registrations', 125, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(406, 'INSERT', 'event_registrations', 126, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(407, 'INSERT', 'event_registrations', 127, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(408, 'INSERT', 'event_registrations', 128, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(409, 'INSERT', 'event_registrations', 129, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(410, 'INSERT', 'event_registrations', 130, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(411, 'INSERT', 'event_registrations', 131, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(412, 'INSERT', 'event_registrations', 132, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(413, 'INSERT', 'event_registrations', 133, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(414, 'INSERT', 'event_registrations', 134, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(415, 'INSERT', 'event_registrations', 135, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 27, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(416, 'INSERT', 'event_registrations', 136, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(417, 'INSERT', 'event_registrations', 137, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(418, 'INSERT', 'event_registrations', 138, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(419, 'INSERT', 'event_registrations', 139, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(420, 'INSERT', 'event_registrations', 140, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Pending Confirmation)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(421, 'INSERT', 'event_registrations', 141, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(422, 'INSERT', 'event_registrations', 142, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(423, 'INSERT', 'event_registrations', 143, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(424, 'INSERT', 'event_registrations', 144, NULL, 'Pendaftaran event: Kejurda Medan Q1 Round 3 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(425, 'INSERT', 'event_registrations', 145, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(426, 'INSERT', 'event_registrations', 146, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(427, 'INSERT', 'event_registrations', 147, NULL, 'Pendaftaran event: Open Track Day January (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(428, 'INSERT', 'event_registrations', 148, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(429, 'INSERT', 'event_registrations', 149, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(430, 'INSERT', 'event_registrations', 150, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(431, 'INSERT', 'event_registrations', 151, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(432, 'INSERT', 'event_registrations', 152, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(433, 'INSERT', 'event_registrations', 153, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(434, 'INSERT', 'event_registrations', 154, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(435, 'INSERT', 'event_registrations', 155, NULL, 'Pendaftaran event: Open Track Day January (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(436, 'INSERT', 'event_registrations', 156, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(437, 'INSERT', 'event_registrations', 157, NULL, 'Pendaftaran event: Open Track Day January (Status: Pending Confirmation)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(438, 'INSERT', 'event_registrations', 158, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(439, 'INSERT', 'event_registrations', 159, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(440, 'INSERT', 'event_registrations', 160, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(441, 'INSERT', 'event_registrations', 161, NULL, 'Pendaftaran event: Open Track Day January (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(442, 'INSERT', 'event_registrations', 162, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(443, 'INSERT', 'event_registrations', 163, NULL, 'Pendaftaran event: Open Track Day January (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(444, 'INSERT', 'event_registrations', 164, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(445, 'INSERT', 'event_registrations', 165, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(446, 'INSERT', 'event_registrations', 166, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(447, 'INSERT', 'event_registrations', 167, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(448, 'INSERT', 'event_registrations', 168, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(449, 'INSERT', 'event_registrations', 169, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(450, 'INSERT', 'event_registrations', 170, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(451, 'INSERT', 'event_registrations', 171, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(452, 'INSERT', 'event_registrations', 172, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(453, 'INSERT', 'event_registrations', 173, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(454, 'INSERT', 'event_registrations', 174, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(455, 'INSERT', 'event_registrations', 175, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(456, 'INSERT', 'event_registrations', 176, NULL, 'Pendaftaran event: Valentine Special Race (Status: Pending Confirmation)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(457, 'INSERT', 'event_registrations', 177, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(458, 'INSERT', 'event_registrations', 178, NULL, 'Pendaftaran event: Valentine Special Race (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(459, 'INSERT', 'event_registrations', 179, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(460, 'INSERT', 'event_registrations', 180, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(461, 'INSERT', 'event_registrations', 181, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(462, 'INSERT', 'event_registrations', 182, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(463, 'INSERT', 'event_registrations', 183, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(464, 'INSERT', 'event_registrations', 184, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(465, 'INSERT', 'event_registrations', 185, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(466, 'INSERT', 'event_registrations', 186, NULL, 'Pendaftaran event: March Madness Race (Status: Pending Confirmation)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(467, 'INSERT', 'event_registrations', 187, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(468, 'INSERT', 'event_registrations', 188, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(469, 'INSERT', 'event_registrations', 189, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(470, 'INSERT', 'event_registrations', 190, NULL, 'Pendaftaran event: March Madness Race (Status: Pending Confirmation)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(471, 'INSERT', 'event_registrations', 191, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(472, 'INSERT', 'event_registrations', 192, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(473, 'INSERT', 'event_registrations', 193, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(474, 'INSERT', 'event_registrations', 194, NULL, 'Pendaftaran event: March Madness Race (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(475, 'INSERT', 'event_registrations', 195, NULL, 'Pendaftaran event: March Madness Race (Status: Pending Confirmation)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(476, 'INSERT', 'event_registrations', 196, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(477, 'INSERT', 'event_registrations', 197, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Pending Confirmation)', 27, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(478, 'INSERT', 'event_registrations', 198, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(479, 'INSERT', 'event_registrations', 199, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(480, 'INSERT', 'event_registrations', 200, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(481, 'INSERT', 'event_registrations', 201, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(482, 'INSERT', 'event_registrations', 202, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(483, 'INSERT', 'event_registrations', 203, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Pending Confirmation)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(484, 'INSERT', 'event_registrations', 204, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(485, 'INSERT', 'event_registrations', 205, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Pending Confirmation)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(486, 'INSERT', 'event_registrations', 206, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(487, 'INSERT', 'event_registrations', 207, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(488, 'INSERT', 'event_registrations', 208, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(489, 'INSERT', 'event_registrations', 209, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(490, 'INSERT', 'event_registrations', 210, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(491, 'INSERT', 'event_registrations', 211, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(492, 'INSERT', 'event_registrations', 212, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(493, 'INSERT', 'event_registrations', 213, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(494, 'INSERT', 'event_registrations', 214, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(495, 'INSERT', 'event_registrations', 215, NULL, 'Pendaftaran event: Pematang Siantar Cup 1 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(496, 'INSERT', 'event_registrations', 216, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(497, 'INSERT', 'event_registrations', 217, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(498, 'INSERT', 'event_registrations', 218, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(499, 'INSERT', 'event_registrations', 219, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(500, 'INSERT', 'event_registrations', 220, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(501, 'INSERT', 'event_registrations', 221, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(502, 'INSERT', 'event_registrations', 222, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(503, 'INSERT', 'event_registrations', 223, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(504, 'INSERT', 'event_registrations', 224, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(505, 'INSERT', 'event_registrations', 225, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(506, 'INSERT', 'event_registrations', 226, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(507, 'INSERT', 'event_registrations', 227, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(508, 'INSERT', 'event_registrations', 228, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(509, 'INSERT', 'event_registrations', 229, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(510, 'INSERT', 'event_registrations', 230, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(511, 'INSERT', 'event_registrations', 231, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(512, 'INSERT', 'event_registrations', 232, NULL, 'Pendaftaran event: Binjai Racing Series 1 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(513, 'INSERT', 'event_registrations', 233, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Pending Confirmation)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(514, 'INSERT', 'event_registrations', 234, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(515, 'INSERT', 'event_registrations', 235, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(516, 'INSERT', 'event_registrations', 236, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(517, 'INSERT', 'event_registrations', 237, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(518, 'INSERT', 'event_registrations', 238, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(519, 'INSERT', 'event_registrations', 239, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(520, 'INSERT', 'event_registrations', 240, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(521, 'INSERT', 'event_registrations', 241, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(522, 'INSERT', 'event_registrations', 242, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(523, 'INSERT', 'event_registrations', 243, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(524, 'INSERT', 'event_registrations', 244, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(525, 'INSERT', 'event_registrations', 245, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(526, 'INSERT', 'event_registrations', 246, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Pending Confirmation)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(527, 'INSERT', 'event_registrations', 247, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(528, 'INSERT', 'event_registrations', 248, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(529, 'INSERT', 'event_registrations', 249, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(530, 'INSERT', 'event_registrations', 250, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(531, 'INSERT', 'event_registrations', 251, NULL, 'Pendaftaran event: Tebing Tinggi Open 1 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(532, 'INSERT', 'event_registrations', 252, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(533, 'INSERT', 'event_registrations', 253, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(534, 'INSERT', 'event_registrations', 254, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(535, 'INSERT', 'event_registrations', 255, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(536, 'INSERT', 'event_registrations', 256, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(537, 'INSERT', 'event_registrations', 257, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(538, 'INSERT', 'event_registrations', 258, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(539, 'INSERT', 'event_registrations', 259, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(540, 'INSERT', 'event_registrations', 260, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(541, 'INSERT', 'event_registrations', 261, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(542, 'INSERT', 'event_registrations', 262, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(543, 'INSERT', 'event_registrations', 263, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(544, 'INSERT', 'event_registrations', 264, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(545, 'INSERT', 'event_registrations', 265, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(546, 'INSERT', 'event_registrations', 266, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(547, 'INSERT', 'event_registrations', 267, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(548, 'INSERT', 'event_registrations', 268, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(549, 'INSERT', 'event_registrations', 269, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(550, 'INSERT', 'event_registrations', 270, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 27, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(551, 'INSERT', 'event_registrations', 271, NULL, 'Pendaftaran event: Summer Heat Championship R1 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(552, 'INSERT', 'event_registrations', 272, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(553, 'INSERT', 'event_registrations', 273, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(554, 'INSERT', 'event_registrations', 274, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(555, 'INSERT', 'event_registrations', 275, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(556, 'INSERT', 'event_registrations', 276, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(557, 'INSERT', 'event_registrations', 277, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(558, 'INSERT', 'event_registrations', 278, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(559, 'INSERT', 'event_registrations', 279, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(560, 'INSERT', 'event_registrations', 280, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(561, 'INSERT', 'event_registrations', 281, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(562, 'INSERT', 'event_registrations', 282, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(563, 'INSERT', 'event_registrations', 283, NULL, 'Pendaftaran event: Summer Heat Championship R2 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(564, 'INSERT', 'event_registrations', 284, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(565, 'INSERT', 'event_registrations', 285, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Pending Confirmation)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(566, 'INSERT', 'event_registrations', 286, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(567, 'INSERT', 'event_registrations', 287, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(568, 'INSERT', 'event_registrations', 288, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(569, 'INSERT', 'event_registrations', 289, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(570, 'INSERT', 'event_registrations', 290, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(571, 'INSERT', 'event_registrations', 291, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(572, 'INSERT', 'event_registrations', 292, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(573, 'INSERT', 'event_registrations', 293, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(574, 'INSERT', 'event_registrations', 294, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(575, 'INSERT', 'event_registrations', 295, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(576, 'INSERT', 'event_registrations', 296, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(577, 'INSERT', 'event_registrations', 297, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 27, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(578, 'INSERT', 'event_registrations', 298, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(579, 'INSERT', 'event_registrations', 299, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(580, 'INSERT', 'event_registrations', 300, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(581, 'INSERT', 'event_registrations', 301, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(582, 'INSERT', 'event_registrations', 302, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(583, 'INSERT', 'event_registrations', 303, NULL, 'Pendaftaran event: Summer Heat Championship R3 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(584, 'INSERT', 'event_registrations', 304, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(585, 'INSERT', 'event_registrations', 305, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(586, 'INSERT', 'event_registrations', 306, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Pending Confirmation)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(587, 'INSERT', 'event_registrations', 307, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(588, 'INSERT', 'event_registrations', 308, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(589, 'INSERT', 'event_registrations', 309, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(590, 'INSERT', 'event_registrations', 310, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(591, 'INSERT', 'event_registrations', 311, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(592, 'INSERT', 'event_registrations', 312, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(593, 'INSERT', 'event_registrations', 313, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(594, 'INSERT', 'event_registrations', 314, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(595, 'INSERT', 'event_registrations', 315, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(596, 'INSERT', 'event_registrations', 316, NULL, 'Pendaftaran event: Kejurprov Sumut Series 1 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(597, 'INSERT', 'event_registrations', 317, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(598, 'INSERT', 'event_registrations', 318, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(599, 'INSERT', 'event_registrations', 319, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(600, 'INSERT', 'event_registrations', 320, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(601, 'INSERT', 'event_registrations', 321, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(602, 'INSERT', 'event_registrations', 322, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(603, 'INSERT', 'event_registrations', 323, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(604, 'INSERT', 'event_registrations', 324, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(605, 'INSERT', 'event_registrations', 325, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(606, 'INSERT', 'event_registrations', 326, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(607, 'INSERT', 'event_registrations', 327, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(608, 'INSERT', 'event_registrations', 328, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(609, 'INSERT', 'event_registrations', 329, NULL, 'Pendaftaran event: Kejurprov Sumut Series 2 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(610, 'INSERT', 'event_registrations', 330, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(611, 'INSERT', 'event_registrations', 331, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(612, 'INSERT', 'event_registrations', 332, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(613, 'INSERT', 'event_registrations', 333, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(614, 'INSERT', 'event_registrations', 334, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(615, 'INSERT', 'event_registrations', 335, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(616, 'INSERT', 'event_registrations', 336, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(617, 'INSERT', 'event_registrations', 337, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(618, 'INSERT', 'event_registrations', 338, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(619, 'INSERT', 'event_registrations', 339, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(620, 'INSERT', 'event_registrations', 340, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(621, 'INSERT', 'event_registrations', 341, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(622, 'INSERT', 'event_registrations', 342, NULL, 'Pendaftaran event: Kejurprov Sumut Series 3 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(623, 'INSERT', 'event_registrations', 343, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(624, 'INSERT', 'event_registrations', 344, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(625, 'INSERT', 'event_registrations', 345, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(626, 'INSERT', 'event_registrations', 346, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(627, 'INSERT', 'event_registrations', 347, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(628, 'INSERT', 'event_registrations', 348, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(629, 'INSERT', 'event_registrations', 349, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(630, 'INSERT', 'event_registrations', 350, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(631, 'INSERT', 'event_registrations', 351, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(632, 'INSERT', 'event_registrations', 352, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(633, 'INSERT', 'event_registrations', 353, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(634, 'INSERT', 'event_registrations', 354, NULL, 'Pendaftaran event: Ramadan Cup 2024 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(635, 'INSERT', 'event_registrations', 355, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(636, 'INSERT', 'event_registrations', 356, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(637, 'INSERT', 'event_registrations', 357, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(638, 'INSERT', 'event_registrations', 358, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Pending Confirmation)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(639, 'INSERT', 'event_registrations', 359, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(640, 'INSERT', 'event_registrations', 360, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Pending Confirmation)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32');
INSERT INTO `logs` (`id`, `action_type`, `table_name`, `record_id`, `old_value`, `new_value`, `user_id`, `created_at`, `updated_at`) VALUES
(641, 'INSERT', 'event_registrations', 361, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(642, 'INSERT', 'event_registrations', 362, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(643, 'INSERT', 'event_registrations', 363, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(644, 'INSERT', 'event_registrations', 364, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(645, 'INSERT', 'event_registrations', 365, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(646, 'INSERT', 'event_registrations', 366, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(647, 'INSERT', 'event_registrations', 367, NULL, 'Pendaftaran event: Eid Mubarak Race (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(648, 'INSERT', 'event_registrations', 368, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(649, 'INSERT', 'event_registrations', 369, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(650, 'INSERT', 'event_registrations', 370, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(651, 'INSERT', 'event_registrations', 371, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(652, 'INSERT', 'event_registrations', 372, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Pending Confirmation)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(653, 'INSERT', 'event_registrations', 373, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(654, 'INSERT', 'event_registrations', 374, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(655, 'INSERT', 'event_registrations', 375, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(656, 'INSERT', 'event_registrations', 376, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(657, 'INSERT', 'event_registrations', 377, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(658, 'INSERT', 'event_registrations', 378, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(659, 'INSERT', 'event_registrations', 379, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(660, 'INSERT', 'event_registrations', 380, NULL, 'Pendaftaran event: Independence Preparation Cup (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(661, 'INSERT', 'event_registrations', 381, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(662, 'INSERT', 'event_registrations', 382, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(663, 'INSERT', 'event_registrations', 383, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(664, 'INSERT', 'event_registrations', 384, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(665, 'INSERT', 'event_registrations', 385, NULL, 'Pendaftaran event: Mid Year Championship (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(666, 'INSERT', 'event_registrations', 386, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(667, 'INSERT', 'event_registrations', 387, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(668, 'INSERT', 'event_registrations', 388, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(669, 'INSERT', 'event_registrations', 389, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(670, 'INSERT', 'event_registrations', 390, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(671, 'INSERT', 'event_registrations', 391, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(672, 'INSERT', 'event_registrations', 392, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(673, 'INSERT', 'event_registrations', 393, NULL, 'Pendaftaran event: Mid Year Championship (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(674, 'INSERT', 'event_registrations', 394, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(675, 'INSERT', 'event_registrations', 395, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(676, 'INSERT', 'event_registrations', 396, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(677, 'INSERT', 'event_registrations', 397, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(678, 'INSERT', 'event_registrations', 398, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(679, 'INSERT', 'event_registrations', 399, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(680, 'INSERT', 'event_registrations', 400, NULL, 'Pendaftaran event: Open Track Day April (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(681, 'INSERT', 'event_registrations', 401, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(682, 'INSERT', 'event_registrations', 402, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(683, 'INSERT', 'event_registrations', 403, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(684, 'INSERT', 'event_registrations', 404, NULL, 'Pendaftaran event: Open Track Day April (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(685, 'INSERT', 'event_registrations', 405, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(686, 'INSERT', 'event_registrations', 406, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(687, 'INSERT', 'event_registrations', 407, NULL, 'Pendaftaran event: Open Track Day April (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(688, 'INSERT', 'event_registrations', 408, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(689, 'INSERT', 'event_registrations', 409, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(690, 'INSERT', 'event_registrations', 410, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(691, 'INSERT', 'event_registrations', 411, NULL, 'Pendaftaran event: May Day Racing (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(692, 'INSERT', 'event_registrations', 412, NULL, 'Pendaftaran event: May Day Racing (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(693, 'INSERT', 'event_registrations', 413, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(694, 'INSERT', 'event_registrations', 414, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(695, 'INSERT', 'event_registrations', 415, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(696, 'INSERT', 'event_registrations', 416, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(697, 'INSERT', 'event_registrations', 417, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(698, 'INSERT', 'event_registrations', 418, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(699, 'INSERT', 'event_registrations', 419, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(700, 'INSERT', 'event_registrations', 420, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(701, 'INSERT', 'event_registrations', 421, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(702, 'INSERT', 'event_registrations', 422, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(703, 'INSERT', 'event_registrations', 423, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(704, 'INSERT', 'event_registrations', 424, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(705, 'INSERT', 'event_registrations', 425, NULL, 'Pendaftaran event: May Day Racing (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(706, 'INSERT', 'event_registrations', 426, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(707, 'INSERT', 'event_registrations', 427, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(708, 'INSERT', 'event_registrations', 428, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(709, 'INSERT', 'event_registrations', 429, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(710, 'INSERT', 'event_registrations', 430, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(711, 'INSERT', 'event_registrations', 431, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(712, 'INSERT', 'event_registrations', 432, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(713, 'INSERT', 'event_registrations', 433, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(714, 'INSERT', 'event_registrations', 434, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Pending Confirmation)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(715, 'INSERT', 'event_registrations', 435, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(716, 'INSERT', 'event_registrations', 436, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(717, 'INSERT', 'event_registrations', 437, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(718, 'INSERT', 'event_registrations', 438, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(719, 'INSERT', 'event_registrations', 439, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(720, 'INSERT', 'event_registrations', 440, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(721, 'INSERT', 'event_registrations', 441, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(722, 'INSERT', 'event_registrations', 442, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(723, 'INSERT', 'event_registrations', 443, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(724, 'INSERT', 'event_registrations', 444, NULL, 'Pendaftaran event: Pematang Siantar Cup 2 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(725, 'INSERT', 'event_registrations', 445, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(726, 'INSERT', 'event_registrations', 446, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(727, 'INSERT', 'event_registrations', 447, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(728, 'INSERT', 'event_registrations', 448, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(729, 'INSERT', 'event_registrations', 449, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(730, 'INSERT', 'event_registrations', 450, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(731, 'INSERT', 'event_registrations', 451, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(732, 'INSERT', 'event_registrations', 452, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(733, 'INSERT', 'event_registrations', 453, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(734, 'INSERT', 'event_registrations', 454, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(735, 'INSERT', 'event_registrations', 455, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(736, 'INSERT', 'event_registrations', 456, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(737, 'INSERT', 'event_registrations', 457, NULL, 'Pendaftaran event: Binjai Racing Series 2 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(738, 'INSERT', 'event_registrations', 458, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(739, 'INSERT', 'event_registrations', 459, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(740, 'INSERT', 'event_registrations', 460, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(741, 'INSERT', 'event_registrations', 461, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(742, 'INSERT', 'event_registrations', 462, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(743, 'INSERT', 'event_registrations', 463, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(744, 'INSERT', 'event_registrations', 464, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(745, 'INSERT', 'event_registrations', 465, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(746, 'INSERT', 'event_registrations', 466, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(747, 'INSERT', 'event_registrations', 467, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(748, 'INSERT', 'event_registrations', 468, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(749, 'INSERT', 'event_registrations', 469, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(750, 'INSERT', 'event_registrations', 470, NULL, 'Pendaftaran event: June Thunder Race (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(751, 'INSERT', 'event_registrations', 471, NULL, 'Pendaftaran event: June Thunder Race (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(752, 'INSERT', 'event_registrations', 472, NULL, 'Pendaftaran event: Independence Day Special (Status: Pending Confirmation)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(753, 'INSERT', 'event_registrations', 473, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(754, 'INSERT', 'event_registrations', 474, NULL, 'Pendaftaran event: Independence Day Special (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(755, 'INSERT', 'event_registrations', 475, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(756, 'INSERT', 'event_registrations', 476, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(757, 'INSERT', 'event_registrations', 477, NULL, 'Pendaftaran event: Independence Day Special (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(758, 'INSERT', 'event_registrations', 478, NULL, 'Pendaftaran event: Independence Day Special (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(759, 'INSERT', 'event_registrations', 479, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(760, 'INSERT', 'event_registrations', 480, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(761, 'INSERT', 'event_registrations', 481, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(762, 'INSERT', 'event_registrations', 482, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(763, 'INSERT', 'event_registrations', 483, NULL, 'Pendaftaran event: Independence Day Special (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(764, 'INSERT', 'event_registrations', 484, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(765, 'INSERT', 'event_registrations', 485, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(766, 'INSERT', 'event_registrations', 486, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(767, 'INSERT', 'event_registrations', 487, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Pending Confirmation)', 27, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(768, 'INSERT', 'event_registrations', 488, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Pending Confirmation)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(769, 'INSERT', 'event_registrations', 489, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(770, 'INSERT', 'event_registrations', 490, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(771, 'INSERT', 'event_registrations', 491, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(772, 'INSERT', 'event_registrations', 492, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(773, 'INSERT', 'event_registrations', 493, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(774, 'INSERT', 'event_registrations', 494, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(775, 'INSERT', 'event_registrations', 495, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(776, 'INSERT', 'event_registrations', 496, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(777, 'INSERT', 'event_registrations', 497, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Pending Confirmation)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(778, 'INSERT', 'event_registrations', 498, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(779, 'INSERT', 'event_registrations', 499, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(780, 'INSERT', 'event_registrations', 500, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(781, 'INSERT', 'event_registrations', 501, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(782, 'INSERT', 'event_registrations', 502, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 26, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(783, 'INSERT', 'event_registrations', 503, NULL, 'Pendaftaran event: August 17 Freedom Race (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(784, 'INSERT', 'event_registrations', 504, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 25, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(785, 'INSERT', 'event_registrations', 505, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(786, 'INSERT', 'event_registrations', 506, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(787, 'INSERT', 'event_registrations', 507, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(788, 'INSERT', 'event_registrations', 508, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Pending Confirmation)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(789, 'INSERT', 'event_registrations', 509, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(790, 'INSERT', 'event_registrations', 510, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(791, 'INSERT', 'event_registrations', 511, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(792, 'INSERT', 'event_registrations', 512, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(793, 'INSERT', 'event_registrations', 513, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(794, 'INSERT', 'event_registrations', 514, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 16, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(795, 'INSERT', 'event_registrations', 515, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(796, 'INSERT', 'event_registrations', 516, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(797, 'INSERT', 'event_registrations', 517, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(798, 'INSERT', 'event_registrations', 518, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(799, 'INSERT', 'event_registrations', 519, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 24, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(800, 'INSERT', 'event_registrations', 520, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(801, 'INSERT', 'event_registrations', 521, NULL, 'Pendaftaran event: Merdeka Cup 2024 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(802, 'INSERT', 'event_registrations', 522, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(803, 'INSERT', 'event_registrations', 523, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 22, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(804, 'INSERT', 'event_registrations', 524, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 13, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(805, 'INSERT', 'event_registrations', 525, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Pending Confirmation)', 21, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(806, 'INSERT', 'event_registrations', 526, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 12, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(807, 'INSERT', 'event_registrations', 527, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(808, 'INSERT', 'event_registrations', 528, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 8, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(809, 'INSERT', 'event_registrations', 529, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Pending Confirmation)', 14, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(810, 'INSERT', 'event_registrations', 530, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 18, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(811, 'INSERT', 'event_registrations', 531, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 10, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(812, 'INSERT', 'event_registrations', 532, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 23, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(813, 'INSERT', 'event_registrations', 533, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(814, 'INSERT', 'event_registrations', 534, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 20, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(815, 'INSERT', 'event_registrations', 535, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 9, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(816, 'INSERT', 'event_registrations', 536, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 19, '2025-12-13 16:31:32', '2025-12-13 16:31:32'),
(817, 'INSERT', 'event_registrations', 537, NULL, 'Pendaftaran event: Autumn Championship R1 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(818, 'INSERT', 'event_registrations', 538, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(819, 'INSERT', 'event_registrations', 539, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(820, 'INSERT', 'event_registrations', 540, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(821, 'INSERT', 'event_registrations', 541, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(822, 'INSERT', 'event_registrations', 542, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(823, 'INSERT', 'event_registrations', 543, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(824, 'INSERT', 'event_registrations', 544, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(825, 'INSERT', 'event_registrations', 545, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(826, 'INSERT', 'event_registrations', 546, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(827, 'INSERT', 'event_registrations', 547, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Pending Confirmation)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(828, 'INSERT', 'event_registrations', 548, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(829, 'INSERT', 'event_registrations', 549, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(830, 'INSERT', 'event_registrations', 550, NULL, 'Pendaftaran event: Autumn Championship R2 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(831, 'INSERT', 'event_registrations', 551, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(832, 'INSERT', 'event_registrations', 552, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(833, 'INSERT', 'event_registrations', 553, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(834, 'INSERT', 'event_registrations', 554, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(835, 'INSERT', 'event_registrations', 555, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(836, 'INSERT', 'event_registrations', 556, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(837, 'INSERT', 'event_registrations', 557, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Pending Confirmation)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(838, 'INSERT', 'event_registrations', 558, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(839, 'INSERT', 'event_registrations', 559, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(840, 'INSERT', 'event_registrations', 560, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(841, 'INSERT', 'event_registrations', 561, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(842, 'INSERT', 'event_registrations', 562, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Pending Confirmation)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(843, 'INSERT', 'event_registrations', 563, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(844, 'INSERT', 'event_registrations', 564, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(845, 'INSERT', 'event_registrations', 565, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(846, 'INSERT', 'event_registrations', 566, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(847, 'INSERT', 'event_registrations', 567, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(848, 'INSERT', 'event_registrations', 568, NULL, 'Pendaftaran event: Autumn Championship R3 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(849, 'INSERT', 'event_registrations', 569, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(850, 'INSERT', 'event_registrations', 570, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(851, 'INSERT', 'event_registrations', 571, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(852, 'INSERT', 'event_registrations', 572, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(853, 'INSERT', 'event_registrations', 573, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(854, 'INSERT', 'event_registrations', 574, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(855, 'INSERT', 'event_registrations', 575, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(856, 'INSERT', 'event_registrations', 576, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(857, 'INSERT', 'event_registrations', 577, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(858, 'INSERT', 'event_registrations', 578, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(859, 'INSERT', 'event_registrations', 579, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(860, 'INSERT', 'event_registrations', 580, NULL, 'Pendaftaran event: Grasstrack Medan Cup 1 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(861, 'INSERT', 'event_registrations', 581, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(862, 'INSERT', 'event_registrations', 582, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(863, 'INSERT', 'event_registrations', 583, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(864, 'INSERT', 'event_registrations', 584, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(865, 'INSERT', 'event_registrations', 585, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(866, 'INSERT', 'event_registrations', 586, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(867, 'INSERT', 'event_registrations', 587, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(868, 'INSERT', 'event_registrations', 588, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(869, 'INSERT', 'event_registrations', 589, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(870, 'INSERT', 'event_registrations', 590, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(871, 'INSERT', 'event_registrations', 591, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(872, 'INSERT', 'event_registrations', 592, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(873, 'INSERT', 'event_registrations', 593, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(874, 'INSERT', 'event_registrations', 594, NULL, 'Pendaftaran event: Grasstrack Medan Cup 2 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(875, 'INSERT', 'event_registrations', 595, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(876, 'INSERT', 'event_registrations', 596, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(877, 'INSERT', 'event_registrations', 597, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(878, 'INSERT', 'event_registrations', 598, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(879, 'INSERT', 'event_registrations', 599, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(880, 'INSERT', 'event_registrations', 600, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 26, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(881, 'INSERT', 'event_registrations', 601, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(882, 'INSERT', 'event_registrations', 602, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(883, 'INSERT', 'event_registrations', 603, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(884, 'INSERT', 'event_registrations', 604, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(885, 'INSERT', 'event_registrations', 605, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Pending Confirmation)', 27, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(886, 'INSERT', 'event_registrations', 606, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(887, 'INSERT', 'event_registrations', 607, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(888, 'INSERT', 'event_registrations', 608, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(889, 'INSERT', 'event_registrations', 609, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(890, 'INSERT', 'event_registrations', 610, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(891, 'INSERT', 'event_registrations', 611, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(892, 'INSERT', 'event_registrations', 612, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(893, 'INSERT', 'event_registrations', 613, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(894, 'INSERT', 'event_registrations', 614, NULL, 'Pendaftaran event: Grasstrack Medan Cup 3 (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(895, 'INSERT', 'event_registrations', 615, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(896, 'INSERT', 'event_registrations', 616, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(897, 'INSERT', 'event_registrations', 617, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(898, 'INSERT', 'event_registrations', 618, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(899, 'INSERT', 'event_registrations', 619, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(900, 'INSERT', 'event_registrations', 620, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(901, 'INSERT', 'event_registrations', 621, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(902, 'INSERT', 'event_registrations', 622, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(903, 'INSERT', 'event_registrations', 623, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(904, 'INSERT', 'event_registrations', 624, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(905, 'INSERT', 'event_registrations', 625, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(906, 'INSERT', 'event_registrations', 626, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(907, 'INSERT', 'event_registrations', 627, NULL, 'Pendaftaran event: National Championship Q3 R1 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(908, 'INSERT', 'event_registrations', 628, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(909, 'INSERT', 'event_registrations', 629, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(910, 'INSERT', 'event_registrations', 630, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(911, 'INSERT', 'event_registrations', 631, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(912, 'INSERT', 'event_registrations', 632, NULL, 'Pendaftaran event: Open Track Day July (Status: Pending Confirmation)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(913, 'INSERT', 'event_registrations', 633, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(914, 'INSERT', 'event_registrations', 634, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(915, 'INSERT', 'event_registrations', 635, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(916, 'INSERT', 'event_registrations', 636, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(917, 'INSERT', 'event_registrations', 637, NULL, 'Pendaftaran event: Open Track Day July (Status: Pending Confirmation)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(918, 'INSERT', 'event_registrations', 638, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(919, 'INSERT', 'event_registrations', 639, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(920, 'INSERT', 'event_registrations', 640, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(921, 'INSERT', 'event_registrations', 641, NULL, 'Pendaftaran event: Open Track Day July (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(922, 'INSERT', 'event_registrations', 642, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(923, 'INSERT', 'event_registrations', 643, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(924, 'INSERT', 'event_registrations', 644, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(925, 'INSERT', 'event_registrations', 645, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(926, 'INSERT', 'event_registrations', 646, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(927, 'INSERT', 'event_registrations', 647, NULL, 'Pendaftaran event: September Speed Fest (Status: Pending Confirmation)', 26, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(928, 'INSERT', 'event_registrations', 648, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(929, 'INSERT', 'event_registrations', 649, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(930, 'INSERT', 'event_registrations', 650, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(931, 'INSERT', 'event_registrations', 651, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(932, 'INSERT', 'event_registrations', 652, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(933, 'INSERT', 'event_registrations', 653, NULL, 'Pendaftaran event: September Speed Fest (Status: Pending Confirmation)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(934, 'INSERT', 'event_registrations', 654, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(935, 'INSERT', 'event_registrations', 655, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(936, 'INSERT', 'event_registrations', 656, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(937, 'INSERT', 'event_registrations', 657, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(938, 'INSERT', 'event_registrations', 658, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(939, 'INSERT', 'event_registrations', 659, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(940, 'INSERT', 'event_registrations', 660, NULL, 'Pendaftaran event: September Speed Fest (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(941, 'INSERT', 'event_registrations', 661, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(942, 'INSERT', 'event_registrations', 662, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33');
INSERT INTO `logs` (`id`, `action_type`, `table_name`, `record_id`, `old_value`, `new_value`, `user_id`, `created_at`, `updated_at`) VALUES
(943, 'INSERT', 'event_registrations', 663, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(944, 'INSERT', 'event_registrations', 664, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(945, 'INSERT', 'event_registrations', 665, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(946, 'INSERT', 'event_registrations', 666, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(947, 'INSERT', 'event_registrations', 667, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(948, 'INSERT', 'event_registrations', 668, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(949, 'INSERT', 'event_registrations', 669, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(950, 'INSERT', 'event_registrations', 670, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(951, 'INSERT', 'event_registrations', 671, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(952, 'INSERT', 'event_registrations', 672, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(953, 'INSERT', 'event_registrations', 673, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(954, 'INSERT', 'event_registrations', 674, NULL, 'Pendaftaran event: Tebing Tinggi Open 2 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(955, 'INSERT', 'event_registrations', 675, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(956, 'INSERT', 'event_registrations', 676, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(957, 'INSERT', 'event_registrations', 677, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(958, 'INSERT', 'event_registrations', 678, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(959, 'INSERT', 'event_registrations', 679, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Pending Confirmation)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(960, 'INSERT', 'event_registrations', 680, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(961, 'INSERT', 'event_registrations', 681, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(962, 'INSERT', 'event_registrations', 682, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(963, 'INSERT', 'event_registrations', 683, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(964, 'INSERT', 'event_registrations', 684, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(965, 'INSERT', 'event_registrations', 685, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(966, 'INSERT', 'event_registrations', 686, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(967, 'INSERT', 'event_registrations', 687, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(968, 'INSERT', 'event_registrations', 688, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(969, 'INSERT', 'event_registrations', 689, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(970, 'INSERT', 'event_registrations', 690, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 26, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(971, 'INSERT', 'event_registrations', 691, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(972, 'INSERT', 'event_registrations', 692, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(973, 'INSERT', 'event_registrations', 693, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(974, 'INSERT', 'event_registrations', 694, NULL, 'Pendaftaran event: Binjai Racing Series 3 (Status: Confirmed)', 27, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(975, 'INSERT', 'event_registrations', 695, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(976, 'INSERT', 'event_registrations', 696, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(977, 'INSERT', 'event_registrations', 697, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(978, 'INSERT', 'event_registrations', 698, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(979, 'INSERT', 'event_registrations', 699, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(980, 'INSERT', 'event_registrations', 700, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(981, 'INSERT', 'event_registrations', 701, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(982, 'INSERT', 'event_registrations', 702, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(983, 'INSERT', 'event_registrations', 703, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(984, 'INSERT', 'event_registrations', 704, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(985, 'INSERT', 'event_registrations', 705, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(986, 'INSERT', 'event_registrations', 706, NULL, 'Pendaftaran event: Sumut Grand Prix Round 1 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(987, 'INSERT', 'event_registrations', 707, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(988, 'INSERT', 'event_registrations', 708, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(989, 'INSERT', 'event_registrations', 709, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(990, 'INSERT', 'event_registrations', 710, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(991, 'INSERT', 'event_registrations', 711, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(992, 'INSERT', 'event_registrations', 712, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(993, 'INSERT', 'event_registrations', 713, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(994, 'INSERT', 'event_registrations', 714, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(995, 'INSERT', 'event_registrations', 715, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Pending Confirmation)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(996, 'INSERT', 'event_registrations', 716, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(997, 'INSERT', 'event_registrations', 717, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(998, 'INSERT', 'event_registrations', 718, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(999, 'INSERT', 'event_registrations', 719, NULL, 'Pendaftaran event: Year End Championship R1 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1000, 'INSERT', 'event_registrations', 720, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1001, 'INSERT', 'event_registrations', 721, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1002, 'INSERT', 'event_registrations', 722, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1003, 'INSERT', 'event_registrations', 723, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1004, 'INSERT', 'event_registrations', 724, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1005, 'INSERT', 'event_registrations', 725, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1006, 'INSERT', 'event_registrations', 726, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1007, 'INSERT', 'event_registrations', 727, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1008, 'INSERT', 'event_registrations', 728, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1009, 'INSERT', 'event_registrations', 729, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1010, 'INSERT', 'event_registrations', 730, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Pending Confirmation)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1011, 'INSERT', 'event_registrations', 731, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1012, 'INSERT', 'event_registrations', 732, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1013, 'INSERT', 'event_registrations', 733, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1014, 'INSERT', 'event_registrations', 734, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1015, 'INSERT', 'event_registrations', 735, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1016, 'INSERT', 'event_registrations', 736, NULL, 'Pendaftaran event: Year End Championship R2 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1017, 'INSERT', 'event_registrations', 737, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1018, 'INSERT', 'event_registrations', 738, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1019, 'INSERT', 'event_registrations', 739, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1020, 'INSERT', 'event_registrations', 740, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1021, 'INSERT', 'event_registrations', 741, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1022, 'INSERT', 'event_registrations', 742, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1023, 'INSERT', 'event_registrations', 743, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1024, 'INSERT', 'event_registrations', 744, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1025, 'INSERT', 'event_registrations', 745, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Pending Confirmation)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1026, 'INSERT', 'event_registrations', 746, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1027, 'INSERT', 'event_registrations', 747, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1028, 'INSERT', 'event_registrations', 748, NULL, 'Pendaftaran event: Year End Championship R3 (Status: Pending Confirmation)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1029, 'INSERT', 'event_registrations', 749, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1030, 'INSERT', 'event_registrations', 750, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1031, 'INSERT', 'event_registrations', 751, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1032, 'INSERT', 'event_registrations', 752, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1033, 'INSERT', 'event_registrations', 753, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Pending Confirmation)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1034, 'INSERT', 'event_registrations', 754, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1035, 'INSERT', 'event_registrations', 755, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1036, 'INSERT', 'event_registrations', 756, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1037, 'INSERT', 'event_registrations', 757, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1038, 'INSERT', 'event_registrations', 758, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1039, 'INSERT', 'event_registrations', 759, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1040, 'INSERT', 'event_registrations', 760, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1041, 'INSERT', 'event_registrations', 761, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1042, 'INSERT', 'event_registrations', 762, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1043, 'INSERT', 'event_registrations', 763, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1044, 'INSERT', 'event_registrations', 764, NULL, 'Pendaftaran event: Grand Final Preparation (Status: Pending Confirmation)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1045, 'INSERT', 'event_registrations', 765, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1046, 'INSERT', 'event_registrations', 766, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1047, 'INSERT', 'event_registrations', 767, NULL, 'Pendaftaran event: Semi Final Championship (Status: Pending Confirmation)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1048, 'INSERT', 'event_registrations', 768, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1049, 'INSERT', 'event_registrations', 769, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1050, 'INSERT', 'event_registrations', 770, NULL, 'Pendaftaran event: Semi Final Championship (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1051, 'INSERT', 'event_registrations', 771, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1052, 'INSERT', 'event_registrations', 772, NULL, 'Pendaftaran event: Semi Final Championship (Status: Pending Confirmation)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1053, 'INSERT', 'event_registrations', 773, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1054, 'INSERT', 'event_registrations', 774, NULL, 'Pendaftaran event: Semi Final Championship (Status: Pending Confirmation)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1055, 'INSERT', 'event_registrations', 775, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1056, 'INSERT', 'event_registrations', 776, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1057, 'INSERT', 'event_registrations', 777, NULL, 'Pendaftaran event: Semi Final Championship (Status: Pending Confirmation)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1058, 'INSERT', 'event_registrations', 778, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1059, 'INSERT', 'event_registrations', 779, NULL, 'Pendaftaran event: Semi Final Championship (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1060, 'INSERT', 'event_registrations', 780, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Pending Confirmation)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1061, 'INSERT', 'event_registrations', 781, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1062, 'INSERT', 'event_registrations', 782, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1063, 'INSERT', 'event_registrations', 783, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1064, 'INSERT', 'event_registrations', 784, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1065, 'INSERT', 'event_registrations', 785, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1066, 'INSERT', 'event_registrations', 786, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1067, 'INSERT', 'event_registrations', 787, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1068, 'INSERT', 'event_registrations', 788, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1069, 'INSERT', 'event_registrations', 789, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1070, 'INSERT', 'event_registrations', 790, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1071, 'INSERT', 'event_registrations', 791, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1072, 'INSERT', 'event_registrations', 792, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1073, 'INSERT', 'event_registrations', 793, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1074, 'INSERT', 'event_registrations', 794, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1075, 'INSERT', 'event_registrations', 795, NULL, 'Pendaftaran event: Grand Final Championship 2024 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1076, 'INSERT', 'event_registrations', 796, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1077, 'INSERT', 'event_registrations', 797, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1078, 'INSERT', 'event_registrations', 798, NULL, 'Pendaftaran event: Christmas Special Race (Status: Pending Confirmation)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1079, 'INSERT', 'event_registrations', 799, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1080, 'INSERT', 'event_registrations', 800, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1081, 'INSERT', 'event_registrations', 801, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1082, 'INSERT', 'event_registrations', 802, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1083, 'INSERT', 'event_registrations', 803, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1084, 'INSERT', 'event_registrations', 804, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1085, 'INSERT', 'event_registrations', 805, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1086, 'INSERT', 'event_registrations', 806, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1087, 'INSERT', 'event_registrations', 807, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1088, 'INSERT', 'event_registrations', 808, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1089, 'INSERT', 'event_registrations', 809, NULL, 'Pendaftaran event: Christmas Special Race (Status: Pending Confirmation)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1090, 'INSERT', 'event_registrations', 810, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1091, 'INSERT', 'event_registrations', 811, NULL, 'Pendaftaran event: Christmas Special Race (Status: Pending Confirmation)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1092, 'INSERT', 'event_registrations', 812, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1093, 'INSERT', 'event_registrations', 813, NULL, 'Pendaftaran event: Christmas Special Race (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1094, 'INSERT', 'event_registrations', 814, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Pending Confirmation)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1095, 'INSERT', 'event_registrations', 815, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1096, 'INSERT', 'event_registrations', 816, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1097, 'INSERT', 'event_registrations', 817, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1098, 'INSERT', 'event_registrations', 818, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1099, 'INSERT', 'event_registrations', 819, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1100, 'INSERT', 'event_registrations', 820, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1101, 'INSERT', 'event_registrations', 821, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1102, 'INSERT', 'event_registrations', 822, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1103, 'INSERT', 'event_registrations', 823, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1104, 'INSERT', 'event_registrations', 824, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1105, 'INSERT', 'event_registrations', 825, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1106, 'INSERT', 'event_registrations', 826, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1107, 'INSERT', 'event_registrations', 827, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1108, 'INSERT', 'event_registrations', 828, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1109, 'INSERT', 'event_registrations', 829, NULL, 'Pendaftaran event: New Year Preparation Cup (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1110, 'INSERT', 'event_registrations', 830, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1111, 'INSERT', 'event_registrations', 831, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1112, 'INSERT', 'event_registrations', 832, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Pending Confirmation)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1113, 'INSERT', 'event_registrations', 833, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1114, 'INSERT', 'event_registrations', 834, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1115, 'INSERT', 'event_registrations', 835, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1116, 'INSERT', 'event_registrations', 836, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1117, 'INSERT', 'event_registrations', 837, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1118, 'INSERT', 'event_registrations', 838, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1119, 'INSERT', 'event_registrations', 839, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Pending Confirmation)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1120, 'INSERT', 'event_registrations', 840, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Pending Confirmation)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1121, 'INSERT', 'event_registrations', 841, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1122, 'INSERT', 'event_registrations', 842, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1123, 'INSERT', 'event_registrations', 843, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1124, 'INSERT', 'event_registrations', 844, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1125, 'INSERT', 'event_registrations', 845, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Pending Confirmation)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1126, 'INSERT', 'event_registrations', 846, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1127, 'INSERT', 'event_registrations', 847, NULL, 'Pendaftaran event: Endurance Race 2024 (Status: Pending Confirmation)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1128, 'INSERT', 'event_registrations', 848, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1129, 'INSERT', 'event_registrations', 849, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1130, 'INSERT', 'event_registrations', 850, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1131, 'INSERT', 'event_registrations', 851, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1132, 'INSERT', 'event_registrations', 852, NULL, 'Pendaftaran event: October Fast Race (Status: Pending Confirmation)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1133, 'INSERT', 'event_registrations', 853, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1134, 'INSERT', 'event_registrations', 854, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1135, 'INSERT', 'event_registrations', 855, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1136, 'INSERT', 'event_registrations', 856, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1137, 'INSERT', 'event_registrations', 857, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1138, 'INSERT', 'event_registrations', 858, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1139, 'INSERT', 'event_registrations', 859, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1140, 'INSERT', 'event_registrations', 860, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1141, 'INSERT', 'event_registrations', 861, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1142, 'INSERT', 'event_registrations', 862, NULL, 'Pendaftaran event: October Fast Race (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1143, 'INSERT', 'event_registrations', 863, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1144, 'INSERT', 'event_registrations', 864, NULL, 'Pendaftaran event: November Thunder (Status: Pending Confirmation)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1145, 'INSERT', 'event_registrations', 865, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1146, 'INSERT', 'event_registrations', 866, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1147, 'INSERT', 'event_registrations', 867, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1148, 'INSERT', 'event_registrations', 868, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1149, 'INSERT', 'event_registrations', 869, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1150, 'INSERT', 'event_registrations', 870, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1151, 'INSERT', 'event_registrations', 871, NULL, 'Pendaftaran event: November Thunder (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1152, 'INSERT', 'event_registrations', 872, NULL, 'Pendaftaran event: November Thunder (Status: Pending Confirmation)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1153, 'INSERT', 'event_registrations', 873, NULL, 'Pendaftaran event: November Thunder (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1154, 'INSERT', 'event_registrations', 874, NULL, 'Pendaftaran event: November Thunder (Status: Pending Confirmation)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1155, 'INSERT', 'event_registrations', 875, NULL, 'Pendaftaran event: December Speedway (Status: Pending Confirmation)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1156, 'INSERT', 'event_registrations', 876, NULL, 'Pendaftaran event: December Speedway (Status: Pending Confirmation)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1157, 'INSERT', 'event_registrations', 877, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1158, 'INSERT', 'event_registrations', 878, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1159, 'INSERT', 'event_registrations', 879, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1160, 'INSERT', 'event_registrations', 880, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1161, 'INSERT', 'event_registrations', 881, NULL, 'Pendaftaran event: December Speedway (Status: Pending Confirmation)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1162, 'INSERT', 'event_registrations', 882, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1163, 'INSERT', 'event_registrations', 883, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1164, 'INSERT', 'event_registrations', 884, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1165, 'INSERT', 'event_registrations', 885, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1166, 'INSERT', 'event_registrations', 886, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1167, 'INSERT', 'event_registrations', 887, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1168, 'INSERT', 'event_registrations', 888, NULL, 'Pendaftaran event: December Speedway (Status: Pending Confirmation)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1169, 'INSERT', 'event_registrations', 889, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1170, 'INSERT', 'event_registrations', 890, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1171, 'INSERT', 'event_registrations', 891, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1172, 'INSERT', 'event_registrations', 892, NULL, 'Pendaftaran event: December Speedway (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1173, 'INSERT', 'event_registrations', 893, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1174, 'INSERT', 'event_registrations', 894, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1175, 'INSERT', 'event_registrations', 895, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Pending Confirmation)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1176, 'INSERT', 'event_registrations', 896, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1177, 'INSERT', 'event_registrations', 897, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1178, 'INSERT', 'event_registrations', 898, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1179, 'INSERT', 'event_registrations', 899, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1180, 'INSERT', 'event_registrations', 900, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1181, 'INSERT', 'event_registrations', 901, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1182, 'INSERT', 'event_registrations', 902, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1183, 'INSERT', 'event_registrations', 903, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Pending Confirmation)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1184, 'INSERT', 'event_registrations', 904, NULL, 'Pendaftaran event: Pematang Siantar Cup 3 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1185, 'INSERT', 'event_registrations', 905, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1186, 'INSERT', 'event_registrations', 906, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1187, 'INSERT', 'event_registrations', 907, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1188, 'INSERT', 'event_registrations', 908, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Pending Confirmation)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1189, 'INSERT', 'event_registrations', 909, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1190, 'INSERT', 'event_registrations', 910, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1191, 'INSERT', 'event_registrations', 911, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1192, 'INSERT', 'event_registrations', 912, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1193, 'INSERT', 'event_registrations', 913, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1194, 'INSERT', 'event_registrations', 914, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1195, 'INSERT', 'event_registrations', 915, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1196, 'INSERT', 'event_registrations', 916, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1197, 'INSERT', 'event_registrations', 917, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1198, 'INSERT', 'event_registrations', 918, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 26, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1199, 'INSERT', 'event_registrations', 919, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Pending Confirmation)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1200, 'INSERT', 'event_registrations', 920, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1201, 'INSERT', 'event_registrations', 921, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1202, 'INSERT', 'event_registrations', 922, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1203, 'INSERT', 'event_registrations', 923, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 27, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1204, 'INSERT', 'event_registrations', 924, NULL, 'Pendaftaran event: Sumut Grand Prix Round 2 (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1205, 'INSERT', 'event_registrations', 925, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 12, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1206, 'INSERT', 'event_registrations', 926, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 23, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1207, 'INSERT', 'event_registrations', 927, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 20, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1208, 'INSERT', 'event_registrations', 928, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Pending Confirmation)', 13, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1209, 'INSERT', 'event_registrations', 929, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Pending Confirmation)', 27, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1210, 'INSERT', 'event_registrations', 930, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 9, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1211, 'INSERT', 'event_registrations', 931, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Pending Confirmation)', 24, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1212, 'INSERT', 'event_registrations', 932, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 16, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1213, 'INSERT', 'event_registrations', 933, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 21, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1214, 'INSERT', 'event_registrations', 934, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 18, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1215, 'INSERT', 'event_registrations', 935, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 14, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1216, 'INSERT', 'event_registrations', 936, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 8, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1217, 'INSERT', 'event_registrations', 937, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 25, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1218, 'INSERT', 'event_registrations', 938, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 17, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1219, 'INSERT', 'event_registrations', 939, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 19, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1220, 'INSERT', 'event_registrations', 940, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Pending Confirmation)', 22, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1221, 'INSERT', 'event_registrations', 941, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 15, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1222, 'INSERT', 'event_registrations', 942, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 10, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1223, 'INSERT', 'event_registrations', 943, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 11, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1224, 'INSERT', 'event_registrations', 944, NULL, 'Pendaftaran event: IMI Sumut Closing Race (Status: Confirmed)', 26, '2025-12-13 16:31:33', '2025-12-13 16:31:33'),
(1225, 'INSERT', 'events', 173, NULL, 'Event baru dibuat', 2, '2024-01-23 14:56:00', '2025-12-13 16:31:33'),
(1226, 'INSERT', 'event_registrations', 143, NULL, 'Peserta didaftarkan ke event', 41, '2024-04-16 22:33:00', '2025-12-13 16:31:33'),
(1227, 'INSERT', 'events', 7, NULL, 'Event baru dibuat', 72, '2024-11-26 20:38:00', '2025-12-13 16:31:33'),
(1228, 'UPDATE', 'events', 72, 'Status lama: Pending', 'Event diupdate', 29, '2024-07-03 18:12:00', '2025-12-13 16:31:33'),
(1229, 'UPDATE', 'kis_applications', 106, 'Status lama: Pending', 'Status diubah ke Approved', 9, '2024-04-08 19:40:00', '2025-12-13 16:31:33'),
(1230, 'INSERT', 'club_dues', 196, NULL, 'Iuran klub dibayar', 59, '2024-12-10 07:28:00', '2025-12-13 16:31:33'),
(1231, 'INSERT', 'kis_applications', 159, NULL, 'Pengajuan KIS baru', 1, '2024-05-12 03:21:00', '2025-12-13 16:31:33'),
(1232, 'INSERT', 'club_dues', 41, NULL, 'Iuran klub dibayar', 33, '2024-04-26 12:34:00', '2025-12-13 16:31:33'),
(1233, 'INSERT', 'club_dues', 103, NULL, 'Iuran klub dibayar', 10, '2024-03-18 13:57:00', '2025-12-13 16:31:33'),
(1234, 'UPDATE', 'kis_applications', 18, 'Status lama: Pending', 'Status diubah ke Rejected', 98, '2024-01-28 00:25:00', '2025-12-13 16:31:33'),
(1235, 'UPDATE', 'kis_applications', 40, 'Status lama: Pending', 'Status diubah ke Rejected', 103, '2024-04-30 18:46:00', '2025-12-13 16:31:33'),
(1236, 'INSERT', 'kis_applications', 199, NULL, 'Pengajuan KIS baru', 100, '2024-12-16 23:55:00', '2025-12-13 16:31:33'),
(1237, 'UPDATE', 'events', 23, 'Status lama: Pending', 'Event diupdate', 77, '2024-06-05 12:41:00', '2025-12-13 16:31:33'),
(1238, 'UPDATE', 'events', 196, 'Status lama: Pending', 'Event diupdate', 53, '2024-10-23 00:39:00', '2025-12-13 16:31:33'),
(1239, 'INSERT', 'kis_applications', 168, NULL, 'Pengajuan KIS baru', 91, '2024-02-07 07:01:00', '2025-12-13 16:31:33'),
(1240, 'UPDATE', 'kis_applications', 137, 'Status lama: Pending', 'Status diubah ke Approved', 17, '2024-11-04 04:36:00', '2025-12-13 16:31:33'),
(1241, 'INSERT', 'event_registrations', 36, NULL, 'Peserta didaftarkan ke event', 2, '2024-08-27 22:59:00', '2025-12-13 16:31:33'),
(1242, 'UPDATE', 'kis_applications', 118, 'Status lama: Pending', 'Status diubah ke Rejected', 95, '2024-07-27 15:22:00', '2025-12-13 16:31:33'),
(1243, 'UPDATE', 'kis_applications', 26, 'Status lama: Pending', 'Status diubah ke Rejected', 104, '2024-06-09 20:53:00', '2025-12-13 16:31:33'),
(1244, 'UPDATE', 'kis_applications', 149, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-10-13 05:09:00', '2025-12-13 16:31:33'),
(1245, 'INSERT', 'club_dues', 9, NULL, 'Iuran klub dibayar', 1, '2024-06-22 04:00:00', '2025-12-13 16:31:33'),
(1246, 'UPDATE', 'kis_applications', 84, 'Status lama: Pending', 'Status diubah ke Approved', 65, '2024-01-21 12:22:00', '2025-12-13 16:31:33');
INSERT INTO `logs` (`id`, `action_type`, `table_name`, `record_id`, `old_value`, `new_value`, `user_id`, `created_at`, `updated_at`) VALUES
(1247, 'INSERT', 'event_registrations', 175, NULL, 'Peserta didaftarkan ke event', 2, '2024-12-28 15:32:00', '2025-12-13 16:31:33'),
(1248, 'UPDATE', 'events', 131, 'Status lama: Pending', 'Event diupdate', 19, '2024-12-09 20:17:00', '2025-12-13 16:31:33'),
(1249, 'INSERT', 'kis_applications', 198, NULL, 'Pengajuan KIS baru', 22, '2024-07-03 02:23:00', '2025-12-13 16:31:33'),
(1250, 'INSERT', 'club_dues', 149, NULL, 'Iuran klub dibayar', 40, '2024-08-19 08:52:00', '2025-12-13 16:31:33'),
(1251, 'UPDATE', 'kis_applications', 195, 'Status lama: Pending', 'Status diubah ke Rejected', 18, '2024-11-27 05:11:00', '2025-12-13 16:31:33'),
(1252, 'INSERT', 'events', 22, NULL, 'Event baru dibuat', 97, '2024-02-04 18:19:00', '2025-12-13 16:31:33'),
(1253, 'INSERT', 'club_dues', 38, NULL, 'Iuran klub dibayar', 2, '2024-05-18 23:45:00', '2025-12-13 16:31:33'),
(1254, 'UPDATE', 'events', 93, 'Status lama: Pending', 'Event diupdate', 3, '2024-03-02 22:05:00', '2025-12-13 16:31:33'),
(1255, 'INSERT', 'event_registrations', 57, NULL, 'Peserta didaftarkan ke event', 84, '2024-01-16 12:36:00', '2025-12-13 16:31:33'),
(1256, 'INSERT', 'events', 50, NULL, 'Event baru dibuat', 4, '2024-05-17 09:22:00', '2025-12-13 16:31:33'),
(1257, 'UPDATE', 'events', 141, 'Status lama: Pending', 'Event diupdate', 3, '2024-11-22 01:03:00', '2025-12-13 16:31:33'),
(1258, 'UPDATE', 'events', 96, 'Status lama: Pending', 'Event diupdate', 1, '2024-07-16 08:01:00', '2025-12-13 16:31:33'),
(1259, 'INSERT', 'kis_applications', 98, NULL, 'Pengajuan KIS baru', 21, '2024-06-20 06:39:00', '2025-12-13 16:31:33'),
(1260, 'UPDATE', 'kis_applications', 185, 'Status lama: Pending', 'Status diubah ke Approved', 34, '2024-04-06 08:34:00', '2025-12-13 16:31:33'),
(1261, 'INSERT', 'club_dues', 89, NULL, 'Iuran klub dibayar', 84, '2024-10-28 10:45:00', '2025-12-13 16:31:33'),
(1262, 'INSERT', 'events', 45, NULL, 'Event baru dibuat', 59, '2024-01-18 02:04:00', '2025-12-13 16:31:33'),
(1263, 'UPDATE', 'kis_applications', 33, 'Status lama: Pending', 'Status diubah ke Rejected', 3, '2024-03-19 05:25:00', '2025-12-13 16:31:33'),
(1264, 'INSERT', 'events', 165, NULL, 'Event baru dibuat', 99, '2024-08-12 20:21:00', '2025-12-13 16:31:33'),
(1265, 'UPDATE', 'events', 79, 'Status lama: Pending', 'Event diupdate', 103, '2024-03-10 06:11:00', '2025-12-13 16:31:33'),
(1266, 'UPDATE', 'kis_applications', 42, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-11-15 23:46:00', '2025-12-13 16:31:33'),
(1267, 'UPDATE', 'kis_applications', 24, 'Status lama: Pending', 'Status diubah ke Approved', 97, '2024-12-21 17:11:00', '2025-12-13 16:31:33'),
(1268, 'UPDATE', 'events', 123, 'Status lama: Pending', 'Event diupdate', 8, '2024-10-26 08:40:00', '2025-12-13 16:31:33'),
(1269, 'INSERT', 'kis_applications', 70, NULL, 'Pengajuan KIS baru', 2, '2024-05-02 01:47:00', '2025-12-13 16:31:33'),
(1270, 'UPDATE', 'kis_applications', 29, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-04-11 03:45:00', '2025-12-13 16:31:33'),
(1271, 'INSERT', 'kis_applications', 162, NULL, 'Pengajuan KIS baru', 36, '2024-02-20 12:38:00', '2025-12-13 16:31:33'),
(1272, 'UPDATE', 'kis_applications', 80, 'Status lama: Pending', 'Status diubah ke Approved', 4, '2024-07-06 12:39:00', '2025-12-13 16:31:33'),
(1273, 'INSERT', 'kis_applications', 176, NULL, 'Pengajuan KIS baru', 101, '2024-09-17 04:18:00', '2025-12-13 16:31:33'),
(1274, 'INSERT', 'kis_applications', 85, NULL, 'Pengajuan KIS baru', 57, '2024-11-24 12:42:00', '2025-12-13 16:31:33'),
(1275, 'INSERT', 'club_dues', 31, NULL, 'Iuran klub dibayar', 4, '2024-12-03 23:08:00', '2025-12-13 16:31:33'),
(1276, 'UPDATE', 'kis_applications', 186, 'Status lama: Pending', 'Status diubah ke Approved', 42, '2024-01-09 17:22:00', '2025-12-13 16:31:33'),
(1277, 'INSERT', 'club_dues', 54, NULL, 'Iuran klub dibayar', 3, '2024-03-19 04:27:00', '2025-12-13 16:31:33'),
(1278, 'INSERT', 'club_dues', 149, NULL, 'Iuran klub dibayar', 2, '2024-03-08 18:37:00', '2025-12-13 16:31:33'),
(1279, 'UPDATE', 'kis_applications', 21, 'Status lama: Pending', 'Status diubah ke Approved', 4, '2024-07-31 18:54:00', '2025-12-13 16:31:33'),
(1280, 'INSERT', 'event_registrations', 163, NULL, 'Peserta didaftarkan ke event', 3, '2024-07-10 21:32:00', '2025-12-13 16:31:33'),
(1281, 'UPDATE', 'kis_applications', 38, 'Status lama: Pending', 'Status diubah ke Approved', 19, '2024-04-27 23:17:00', '2025-12-13 16:31:33'),
(1282, 'INSERT', 'events', 160, NULL, 'Event baru dibuat', 62, '2024-07-20 10:44:00', '2025-12-13 16:31:33'),
(1283, 'UPDATE', 'kis_applications', 119, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-02-06 19:43:00', '2025-12-13 16:31:33'),
(1284, 'UPDATE', 'events', 68, 'Status lama: Pending', 'Event diupdate', 3, '2024-09-05 15:33:00', '2025-12-13 16:31:33'),
(1285, 'INSERT', 'events', 23, NULL, 'Event baru dibuat', 72, '2024-03-25 18:57:00', '2025-12-13 16:31:33'),
(1286, 'INSERT', 'event_registrations', 105, NULL, 'Peserta didaftarkan ke event', 4, '2024-03-16 12:36:00', '2025-12-13 16:31:33'),
(1287, 'INSERT', 'event_registrations', 177, NULL, 'Peserta didaftarkan ke event', 89, '2024-03-26 07:58:00', '2025-12-13 16:31:33'),
(1288, 'UPDATE', 'kis_applications', 13, 'Status lama: Pending', 'Status diubah ke Approved', 102, '2024-08-20 14:40:00', '2025-12-13 16:31:33'),
(1289, 'INSERT', 'kis_applications', 166, NULL, 'Pengajuan KIS baru', 10, '2024-05-17 00:06:00', '2025-12-13 16:31:33'),
(1290, 'UPDATE', 'kis_applications', 147, 'Status lama: Pending', 'Status diubah ke Rejected', 102, '2024-09-25 14:42:00', '2025-12-13 16:31:33'),
(1291, 'UPDATE', 'events', 42, 'Status lama: Pending', 'Event diupdate', 23, '2024-03-07 12:05:00', '2025-12-13 16:31:33'),
(1292, 'INSERT', 'events', 167, NULL, 'Event baru dibuat', 48, '2024-10-09 12:03:00', '2025-12-13 16:31:33'),
(1293, 'UPDATE', 'events', 194, 'Status lama: Pending', 'Event diupdate', 52, '2024-12-16 22:17:00', '2025-12-13 16:31:33'),
(1294, 'UPDATE', 'kis_applications', 72, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-06-05 21:36:00', '2025-12-13 16:31:33'),
(1295, 'UPDATE', 'kis_applications', 39, 'Status lama: Pending', 'Status diubah ke Rejected', 69, '2024-08-19 17:34:00', '2025-12-13 16:31:33'),
(1296, 'INSERT', 'event_registrations', 82, NULL, 'Peserta didaftarkan ke event', 63, '2024-01-02 20:06:00', '2025-12-13 16:31:33'),
(1297, 'INSERT', 'kis_applications', 157, NULL, 'Pengajuan KIS baru', 4, '2024-03-08 10:23:00', '2025-12-13 16:31:33'),
(1298, 'UPDATE', 'kis_applications', 81, 'Status lama: Pending', 'Status diubah ke Approved', 32, '2024-09-14 08:04:00', '2025-12-13 16:31:33'),
(1299, 'UPDATE', 'events', 163, 'Status lama: Pending', 'Event diupdate', 29, '2024-04-27 04:24:00', '2025-12-13 16:31:33'),
(1300, 'INSERT', 'club_dues', 73, NULL, 'Iuran klub dibayar', 50, '2024-03-01 03:23:00', '2025-12-13 16:31:33'),
(1301, 'UPDATE', 'events', 108, 'Status lama: Pending', 'Event diupdate', 91, '2024-11-09 07:23:00', '2025-12-13 16:31:33'),
(1302, 'INSERT', 'events', 46, NULL, 'Event baru dibuat', 90, '2024-01-14 08:30:00', '2025-12-13 16:31:33'),
(1303, 'INSERT', 'event_registrations', 39, NULL, 'Peserta didaftarkan ke event', 42, '2024-07-04 02:19:00', '2025-12-13 16:31:33'),
(1304, 'UPDATE', 'events', 69, 'Status lama: Pending', 'Event diupdate', 68, '2024-12-14 20:52:00', '2025-12-13 16:31:33'),
(1305, 'INSERT', 'kis_applications', 28, NULL, 'Pengajuan KIS baru', 55, '2024-08-24 02:28:00', '2025-12-13 16:31:33'),
(1306, 'UPDATE', 'kis_applications', 110, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-10-26 03:28:00', '2025-12-13 16:31:33'),
(1307, 'UPDATE', 'kis_applications', 28, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-01-03 23:28:00', '2025-12-13 16:31:33'),
(1308, 'UPDATE', 'kis_applications', 51, 'Status lama: Pending', 'Status diubah ke Approved', 88, '2024-03-25 21:35:00', '2025-12-13 16:31:33'),
(1309, 'INSERT', 'event_registrations', 107, NULL, 'Peserta didaftarkan ke event', 97, '2024-03-08 12:39:00', '2025-12-13 16:31:33'),
(1310, 'INSERT', 'events', 193, NULL, 'Event baru dibuat', 3, '2024-09-18 00:10:00', '2025-12-13 16:31:33'),
(1311, 'UPDATE', 'kis_applications', 60, 'Status lama: Pending', 'Status diubah ke Approved', 86, '2024-01-12 11:39:00', '2025-12-13 16:31:33'),
(1312, 'INSERT', 'event_registrations', 153, NULL, 'Peserta didaftarkan ke event', 3, '2024-11-24 21:10:00', '2025-12-13 16:31:33'),
(1313, 'UPDATE', 'kis_applications', 101, 'Status lama: Pending', 'Status diubah ke Approved', 100, '2024-11-04 05:49:00', '2025-12-13 16:31:33'),
(1314, 'UPDATE', 'events', 55, 'Status lama: Pending', 'Event diupdate', 3, '2024-08-16 03:24:00', '2025-12-13 16:31:33'),
(1315, 'INSERT', 'event_registrations', 124, NULL, 'Peserta didaftarkan ke event', 99, '2024-10-03 18:30:00', '2025-12-13 16:31:33'),
(1316, 'UPDATE', 'events', 163, 'Status lama: Pending', 'Event diupdate', 11, '2024-06-07 20:36:00', '2025-12-13 16:31:33'),
(1317, 'UPDATE', 'kis_applications', 184, 'Status lama: Pending', 'Status diubah ke Approved', 66, '2024-03-06 23:42:00', '2025-12-13 16:31:33'),
(1318, 'UPDATE', 'events', 59, 'Status lama: Pending', 'Event diupdate', 3, '2024-04-11 10:24:00', '2025-12-13 16:31:33'),
(1319, 'UPDATE', 'kis_applications', 102, 'Status lama: Pending', 'Status diubah ke Approved', 16, '2024-05-17 11:23:00', '2025-12-13 16:31:33'),
(1320, 'INSERT', 'club_dues', 125, NULL, 'Iuran klub dibayar', 28, '2024-12-03 12:24:00', '2025-12-13 16:31:33'),
(1321, 'INSERT', 'event_registrations', 137, NULL, 'Peserta didaftarkan ke event', 96, '2024-12-08 12:26:00', '2025-12-13 16:31:33'),
(1322, 'UPDATE', 'kis_applications', 178, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-11-30 22:47:00', '2025-12-13 16:31:33'),
(1323, 'UPDATE', 'kis_applications', 33, 'Status lama: Pending', 'Status diubah ke Rejected', 64, '2024-12-24 03:55:00', '2025-12-13 16:31:33'),
(1324, 'UPDATE', 'kis_applications', 92, 'Status lama: Pending', 'Status diubah ke Approved', 65, '2024-06-25 14:25:00', '2025-12-13 16:31:33'),
(1325, 'INSERT', 'events', 200, NULL, 'Event baru dibuat', 78, '2024-06-01 12:27:00', '2025-12-13 16:31:33'),
(1326, 'INSERT', 'club_dues', 119, NULL, 'Iuran klub dibayar', 85, '2024-07-27 22:56:00', '2025-12-13 16:31:33'),
(1327, 'INSERT', 'club_dues', 30, NULL, 'Iuran klub dibayar', 36, '2024-06-01 23:46:00', '2025-12-13 16:31:33'),
(1328, 'INSERT', 'club_dues', 170, NULL, 'Iuran klub dibayar', 37, '2024-07-21 16:24:00', '2025-12-13 16:31:33'),
(1329, 'UPDATE', 'kis_applications', 71, 'Status lama: Pending', 'Status diubah ke Rejected', 2, '2024-02-14 18:09:00', '2025-12-13 16:31:33'),
(1330, 'UPDATE', 'kis_applications', 200, 'Status lama: Pending', 'Status diubah ke Rejected', 17, '2024-02-05 17:03:00', '2025-12-13 16:31:33'),
(1331, 'UPDATE', 'kis_applications', 1, 'Status lama: Pending', 'Status diubah ke Approved', 54, '2024-03-16 02:33:00', '2025-12-13 16:31:33'),
(1332, 'INSERT', 'club_dues', 47, NULL, 'Iuran klub dibayar', 11, '2024-09-06 03:00:00', '2025-12-13 16:31:33'),
(1333, 'INSERT', 'event_registrations', 133, NULL, 'Peserta didaftarkan ke event', 79, '2024-12-04 05:47:00', '2025-12-13 16:31:33'),
(1334, 'UPDATE', 'kis_applications', 77, 'Status lama: Pending', 'Status diubah ke Rejected', 38, '2024-07-28 11:14:00', '2025-12-13 16:31:33'),
(1335, 'INSERT', 'event_registrations', 25, NULL, 'Peserta didaftarkan ke event', 3, '2024-01-18 05:37:00', '2025-12-13 16:31:33'),
(1336, 'INSERT', 'club_dues', 106, NULL, 'Iuran klub dibayar', 36, '2024-06-24 05:01:00', '2025-12-13 16:31:33'),
(1337, 'UPDATE', 'kis_applications', 12, 'Status lama: Pending', 'Status diubah ke Approved', 4, '2024-11-24 11:50:00', '2025-12-13 16:31:33'),
(1338, 'INSERT', 'event_registrations', 145, NULL, 'Peserta didaftarkan ke event', 90, '2024-09-21 23:46:00', '2025-12-13 16:31:33'),
(1339, 'UPDATE', 'events', 22, 'Status lama: Pending', 'Event diupdate', 2, '2024-04-03 19:13:00', '2025-12-13 16:31:33'),
(1340, 'UPDATE', 'events', 121, 'Status lama: Pending', 'Event diupdate', 3, '2024-10-08 13:29:00', '2025-12-13 16:31:33'),
(1341, 'INSERT', 'event_registrations', 169, NULL, 'Peserta didaftarkan ke event', 4, '2024-06-05 13:44:00', '2025-12-13 16:31:33'),
(1342, 'INSERT', 'kis_applications', 115, NULL, 'Pengajuan KIS baru', 4, '2024-10-19 23:32:00', '2025-12-13 16:31:33'),
(1343, 'UPDATE', 'kis_applications', 123, 'Status lama: Pending', 'Status diubah ke Approved', 62, '2024-02-13 20:42:00', '2025-12-13 16:31:33'),
(1344, 'INSERT', 'events', 20, NULL, 'Event baru dibuat', 1, '2024-01-19 09:34:00', '2025-12-13 16:31:33'),
(1345, 'INSERT', 'event_registrations', 141, NULL, 'Peserta didaftarkan ke event', 102, '2024-06-23 04:26:00', '2025-12-13 16:31:33'),
(1346, 'INSERT', 'kis_applications', 13, NULL, 'Pengajuan KIS baru', 28, '2024-03-20 20:14:00', '2025-12-13 16:31:33'),
(1347, 'UPDATE', 'kis_applications', 61, 'Status lama: Pending', 'Status diubah ke Approved', 27, '2024-09-25 22:17:00', '2025-12-13 16:31:33'),
(1348, 'INSERT', 'kis_applications', 141, NULL, 'Pengajuan KIS baru', 30, '2024-07-22 10:00:00', '2025-12-13 16:31:33'),
(1349, 'INSERT', 'events', 139, NULL, 'Event baru dibuat', 3, '2024-07-06 09:14:00', '2025-12-13 16:31:33'),
(1350, 'INSERT', 'events', 196, NULL, 'Event baru dibuat', 3, '2024-05-01 08:00:00', '2025-12-13 16:31:33'),
(1351, 'INSERT', 'club_dues', 38, NULL, 'Iuran klub dibayar', 50, '2024-09-24 10:02:00', '2025-12-13 16:31:33'),
(1352, 'INSERT', 'kis_applications', 82, NULL, 'Pengajuan KIS baru', 82, '2024-06-06 13:14:00', '2025-12-13 16:31:33'),
(1353, 'UPDATE', 'events', 159, 'Status lama: Pending', 'Event diupdate', 58, '2024-03-16 22:33:00', '2025-12-13 16:31:33'),
(1354, 'UPDATE', 'kis_applications', 93, 'Status lama: Pending', 'Status diubah ke Rejected', 89, '2024-03-07 12:00:00', '2025-12-13 16:31:33'),
(1355, 'INSERT', 'kis_applications', 95, NULL, 'Pengajuan KIS baru', 52, '2024-12-04 07:29:00', '2025-12-13 16:31:33'),
(1356, 'UPDATE', 'kis_applications', 36, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-01-16 23:02:00', '2025-12-13 16:31:33'),
(1357, 'INSERT', 'kis_applications', 185, NULL, 'Pengajuan KIS baru', 1, '2024-06-02 21:18:00', '2025-12-13 16:31:33'),
(1358, 'INSERT', 'events', 59, NULL, 'Event baru dibuat', 4, '2024-05-19 10:23:00', '2025-12-13 16:31:33'),
(1359, 'UPDATE', 'events', 131, 'Status lama: Pending', 'Event diupdate', 49, '2024-02-10 09:23:00', '2025-12-13 16:31:33'),
(1360, 'UPDATE', 'events', 132, 'Status lama: Pending', 'Event diupdate', 4, '2024-08-07 13:39:00', '2025-12-13 16:31:33'),
(1361, 'UPDATE', 'events', 126, 'Status lama: Pending', 'Event diupdate', 38, '2024-03-15 03:48:00', '2025-12-13 16:31:33'),
(1362, 'UPDATE', 'events', 95, 'Status lama: Pending', 'Event diupdate', 9, '2024-04-26 14:04:00', '2025-12-13 16:31:33'),
(1363, 'INSERT', 'kis_applications', 29, NULL, 'Pengajuan KIS baru', 4, '2024-06-28 13:12:00', '2025-12-13 16:31:33'),
(1364, 'INSERT', 'club_dues', 187, NULL, 'Iuran klub dibayar', 13, '2024-09-15 21:40:00', '2025-12-13 16:31:33'),
(1365, 'UPDATE', 'kis_applications', 11, 'Status lama: Pending', 'Status diubah ke Approved', 4, '2024-08-06 19:03:00', '2025-12-13 16:31:33'),
(1366, 'UPDATE', 'events', 63, 'Status lama: Pending', 'Event diupdate', 101, '2024-06-06 15:10:00', '2025-12-13 16:31:33'),
(1367, 'UPDATE', 'events', 188, 'Status lama: Pending', 'Event diupdate', 1, '2024-01-21 23:21:00', '2025-12-13 16:31:33'),
(1368, 'INSERT', 'events', 169, NULL, 'Event baru dibuat', 53, '2024-01-19 23:27:00', '2025-12-13 16:31:33'),
(1369, 'INSERT', 'kis_applications', 126, NULL, 'Pengajuan KIS baru', 9, '2024-04-24 17:17:00', '2025-12-13 16:31:33'),
(1370, 'INSERT', 'club_dues', 59, NULL, 'Iuran klub dibayar', 3, '2024-08-23 06:43:00', '2025-12-13 16:31:33'),
(1371, 'INSERT', 'club_dues', 177, NULL, 'Iuran klub dibayar', 14, '2024-08-16 05:15:00', '2025-12-13 16:31:33'),
(1372, 'UPDATE', 'kis_applications', 31, 'Status lama: Pending', 'Status diubah ke Rejected', 20, '2024-07-27 20:37:00', '2025-12-13 16:31:33'),
(1373, 'INSERT', 'events', 4, NULL, 'Event baru dibuat', 76, '2024-11-16 11:19:00', '2025-12-13 16:31:33'),
(1374, 'INSERT', 'club_dues', 152, NULL, 'Iuran klub dibayar', 60, '2024-09-21 01:57:00', '2025-12-13 16:31:33'),
(1375, 'INSERT', 'events', 12, NULL, 'Event baru dibuat', 94, '2024-02-24 18:46:00', '2025-12-13 16:31:33'),
(1376, 'UPDATE', 'kis_applications', 132, 'Status lama: Pending', 'Status diubah ke Rejected', 4, '2024-02-09 01:36:00', '2025-12-13 16:31:33'),
(1377, 'INSERT', 'events', 82, NULL, 'Event baru dibuat', 98, '2024-03-02 03:13:00', '2025-12-13 16:31:33'),
(1378, 'INSERT', 'club_dues', 23, NULL, 'Iuran klub dibayar', 4, '2024-03-22 04:19:00', '2025-12-13 16:31:33'),
(1379, 'UPDATE', 'kis_applications', 177, 'Status lama: Pending', 'Status diubah ke Rejected', 39, '2024-06-22 19:18:00', '2025-12-13 16:31:33'),
(1380, 'INSERT', 'club_dues', 144, NULL, 'Iuran klub dibayar', 42, '2024-12-18 12:11:00', '2025-12-13 16:31:33'),
(1381, 'INSERT', 'events', 79, NULL, 'Event baru dibuat', 46, '2024-02-11 17:40:00', '2025-12-13 16:31:33'),
(1382, 'UPDATE', 'kis_applications', 54, 'Status lama: Pending', 'Status diubah ke Approved', 3, '2024-12-24 01:56:00', '2025-12-13 16:31:33'),
(1383, 'INSERT', 'kis_applications', 128, NULL, 'Pengajuan KIS baru', 12, '2024-02-27 17:31:00', '2025-12-13 16:31:33'),
(1384, 'UPDATE', 'kis_applications', 198, 'Status lama: Pending', 'Status diubah ke Rejected', 61, '2024-11-24 13:40:00', '2025-12-13 16:31:33'),
(1385, 'INSERT', 'events', 117, NULL, 'Event baru dibuat', 4, '2024-02-03 17:46:00', '2025-12-13 16:31:33'),
(1386, 'INSERT', 'kis_applications', 77, NULL, 'Pengajuan KIS baru', 49, '2024-01-23 23:29:00', '2025-12-13 16:31:33'),
(1387, 'UPDATE', 'events', 187, 'Status lama: Pending', 'Event diupdate', 79, '2024-07-12 12:58:00', '2025-12-13 16:31:33'),
(1388, 'INSERT', 'kis_applications', 118, NULL, 'Pengajuan KIS baru', 4, '2024-03-03 06:48:00', '2025-12-13 16:31:33'),
(1389, 'INSERT', 'club_dues', 33, NULL, 'Iuran klub dibayar', 64, '2024-10-03 06:57:00', '2025-12-13 16:31:33'),
(1390, 'UPDATE', 'kis_applications', 66, 'Status lama: Pending', 'Status diubah ke Approved', 3, '2024-06-14 13:39:00', '2025-12-13 16:31:33'),
(1391, 'UPDATE', 'kis_applications', 141, 'Status lama: Pending', 'Status diubah ke Approved', 93, '2024-06-10 06:51:00', '2025-12-13 16:31:33'),
(1392, 'INSERT', 'event_registrations', 86, NULL, 'Peserta didaftarkan ke event', 15, '2024-10-01 09:17:00', '2025-12-13 16:31:33'),
(1393, 'INSERT', 'club_dues', 60, NULL, 'Iuran klub dibayar', 15, '2024-01-26 14:51:00', '2025-12-13 16:31:33'),
(1394, 'UPDATE', 'events', 193, 'Status lama: Pending', 'Event diupdate', 4, '2024-08-05 07:16:00', '2025-12-13 16:31:33'),
(1395, 'UPDATE', 'kis_applications', 120, 'Status lama: Pending', 'Status diubah ke Rejected', 86, '2024-03-08 01:48:00', '2025-12-13 16:31:33'),
(1396, 'INSERT', 'event_registrations', 11, NULL, 'Peserta didaftarkan ke event', 39, '2024-10-03 18:36:00', '2025-12-13 16:31:33'),
(1397, 'INSERT', 'event_registrations', 105, NULL, 'Peserta didaftarkan ke event', 79, '2024-09-30 17:05:00', '2025-12-13 16:31:33'),
(1398, 'INSERT', 'kis_applications', 101, NULL, 'Pengajuan KIS baru', 2, '2024-01-04 19:12:00', '2025-12-13 16:31:33'),
(1399, 'UPDATE', 'events', 20, 'Status lama: Pending', 'Event diupdate', 1, '2024-09-02 13:09:00', '2025-12-13 16:31:33'),
(1400, 'INSERT', 'kis_applications', 137, NULL, 'Pengajuan KIS baru', 91, '2024-12-17 17:02:00', '2025-12-13 16:31:33'),
(1401, 'INSERT', 'events', 198, NULL, 'Event baru dibuat', 3, '2024-08-05 22:07:00', '2025-12-13 16:31:33'),
(1402, 'INSERT', 'events', 144, NULL, 'Event baru dibuat', 62, '2024-01-07 14:49:00', '2025-12-13 16:31:33'),
(1403, 'UPDATE', 'kis_applications', 138, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-01-12 15:19:00', '2025-12-13 16:31:33'),
(1404, 'INSERT', 'events', 192, NULL, 'Event baru dibuat', 4, '2024-09-13 01:35:00', '2025-12-13 16:31:33'),
(1405, 'INSERT', 'club_dues', 109, NULL, 'Iuran klub dibayar', 2, '2024-02-27 16:46:00', '2025-12-13 16:31:33'),
(1406, 'INSERT', 'club_dues', 155, NULL, 'Iuran klub dibayar', 37, '2024-11-04 01:35:00', '2025-12-13 16:31:33'),
(1407, 'INSERT', 'club_dues', 1, NULL, 'Iuran klub dibayar', 106, '2024-02-03 08:21:00', '2025-12-13 16:31:33'),
(1408, 'UPDATE', 'events', 61, 'Status lama: Pending', 'Event diupdate', 3, '2024-11-18 18:21:00', '2025-12-13 16:31:33'),
(1409, 'INSERT', 'kis_applications', 22, NULL, 'Pengajuan KIS baru', 1, '2024-11-21 04:19:00', '2025-12-13 16:31:33'),
(1410, 'INSERT', 'club_dues', 95, NULL, 'Iuran klub dibayar', 2, '2024-03-02 23:22:00', '2025-12-13 16:31:33'),
(1411, 'UPDATE', 'kis_applications', 100, 'Status lama: Pending', 'Status diubah ke Rejected', 90, '2024-04-25 07:12:00', '2025-12-13 16:31:33'),
(1412, 'INSERT', 'events', 159, NULL, 'Event baru dibuat', 1, '2024-01-24 17:22:00', '2025-12-13 16:31:33'),
(1413, 'INSERT', 'event_registrations', 53, NULL, 'Peserta didaftarkan ke event', 97, '2024-11-10 05:53:00', '2025-12-13 16:31:33'),
(1414, 'UPDATE', 'kis_applications', 183, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-10-10 02:32:00', '2025-12-13 16:31:33'),
(1415, 'UPDATE', 'kis_applications', 84, 'Status lama: Pending', 'Status diubah ke Rejected', 4, '2024-02-18 02:39:00', '2025-12-13 16:31:33'),
(1416, 'UPDATE', 'events', 150, 'Status lama: Pending', 'Event diupdate', 21, '2024-05-10 10:13:00', '2025-12-13 16:31:33'),
(1417, 'UPDATE', 'kis_applications', 181, 'Status lama: Pending', 'Status diubah ke Approved', 31, '2024-08-11 01:16:00', '2025-12-13 16:31:33'),
(1418, 'INSERT', 'events', 166, NULL, 'Event baru dibuat', 2, '2024-08-23 07:28:00', '2025-12-13 16:31:33'),
(1419, 'INSERT', 'events', 17, NULL, 'Event baru dibuat', 8, '2024-07-04 03:02:00', '2025-12-13 16:31:33'),
(1420, 'UPDATE', 'kis_applications', 103, 'Status lama: Pending', 'Status diubah ke Rejected', 77, '2024-04-14 11:31:00', '2025-12-13 16:31:33'),
(1421, 'INSERT', 'club_dues', 158, NULL, 'Iuran klub dibayar', 2, '2024-07-06 07:38:00', '2025-12-13 16:31:33'),
(1422, 'INSERT', 'event_registrations', 96, NULL, 'Peserta didaftarkan ke event', 2, '2024-02-04 14:22:00', '2025-12-13 16:31:33'),
(1423, 'UPDATE', 'kis_applications', 44, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-01-14 22:36:00', '2025-12-13 16:31:33'),
(1424, 'UPDATE', 'kis_applications', 70, 'Status lama: Pending', 'Status diubah ke Rejected', 91, '2024-05-15 21:12:00', '2025-12-13 16:31:33'),
(1425, 'INSERT', 'kis_applications', 53, NULL, 'Pengajuan KIS baru', 77, '2024-12-06 21:10:00', '2025-12-13 16:31:33'),
(1426, 'INSERT', 'kis_applications', 60, NULL, 'Pengajuan KIS baru', 2, '2024-07-18 15:21:00', '2025-12-13 16:31:33'),
(1427, 'INSERT', 'club_dues', 89, NULL, 'Iuran klub dibayar', 4, '2024-06-10 23:59:00', '2025-12-13 16:31:33'),
(1428, 'INSERT', 'kis_applications', 37, NULL, 'Pengajuan KIS baru', 4, '2024-08-26 17:49:00', '2025-12-13 16:31:33'),
(1429, 'UPDATE', 'kis_applications', 167, 'Status lama: Pending', 'Status diubah ke Rejected', 2, '2024-05-07 06:49:00', '2025-12-13 16:31:33'),
(1430, 'INSERT', 'kis_applications', 186, NULL, 'Pengajuan KIS baru', 1, '2024-08-03 23:10:00', '2025-12-13 16:31:33'),
(1431, 'INSERT', 'kis_applications', 79, NULL, 'Pengajuan KIS baru', 72, '2024-10-03 02:53:00', '2025-12-13 16:31:33'),
(1432, 'INSERT', 'club_dues', 176, NULL, 'Iuran klub dibayar', 2, '2024-11-16 09:57:00', '2025-12-13 16:31:33'),
(1433, 'UPDATE', 'kis_applications', 42, 'Status lama: Pending', 'Status diubah ke Rejected', 43, '2024-12-20 12:43:00', '2025-12-13 16:31:33'),
(1434, 'UPDATE', 'events', 124, 'Status lama: Pending', 'Event diupdate', 4, '2024-04-06 04:08:00', '2025-12-13 16:31:33'),
(1435, 'INSERT', 'events', 147, NULL, 'Event baru dibuat', 45, '2024-03-03 17:55:00', '2025-12-13 16:31:34'),
(1436, 'UPDATE', 'kis_applications', 93, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-06-08 18:49:00', '2025-12-13 16:31:34'),
(1437, 'UPDATE', 'kis_applications', 184, 'Status lama: Pending', 'Status diubah ke Approved', 76, '2024-11-20 18:11:00', '2025-12-13 16:31:34'),
(1438, 'INSERT', 'kis_applications', 30, NULL, 'Pengajuan KIS baru', 31, '2024-11-02 17:45:00', '2025-12-13 16:31:34'),
(1439, 'INSERT', 'event_registrations', 64, NULL, 'Peserta didaftarkan ke event', 4, '2024-09-06 06:32:00', '2025-12-13 16:31:34'),
(1440, 'UPDATE', 'kis_applications', 85, 'Status lama: Pending', 'Status diubah ke Approved', 13, '2024-12-07 05:45:00', '2025-12-13 16:31:34'),
(1441, 'INSERT', 'events', 101, NULL, 'Event baru dibuat', 37, '2024-10-10 21:49:00', '2025-12-13 16:31:34'),
(1442, 'INSERT', 'events', 42, NULL, 'Event baru dibuat', 92, '2024-09-20 11:57:00', '2025-12-13 16:31:34'),
(1443, 'INSERT', 'events', 52, NULL, 'Event baru dibuat', 40, '2024-02-06 16:01:00', '2025-12-13 16:31:34'),
(1444, 'UPDATE', 'events', 183, 'Status lama: Pending', 'Event diupdate', 85, '2024-05-13 00:57:00', '2025-12-13 16:31:34'),
(1445, 'INSERT', 'kis_applications', 141, NULL, 'Pengajuan KIS baru', 101, '2024-06-05 16:03:00', '2025-12-13 16:31:34'),
(1446, 'UPDATE', 'kis_applications', 22, 'Status lama: Pending', 'Status diubah ke Rejected', 63, '2024-11-24 12:09:00', '2025-12-13 16:31:34'),
(1447, 'UPDATE', 'kis_applications', 103, 'Status lama: Pending', 'Status diubah ke Approved', 84, '2024-06-13 10:14:00', '2025-12-13 16:31:34'),
(1448, 'UPDATE', 'events', 75, 'Status lama: Pending', 'Event diupdate', 3, '2024-03-02 14:32:00', '2025-12-13 16:31:34'),
(1449, 'UPDATE', 'kis_applications', 18, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-04-08 02:20:00', '2025-12-13 16:31:34'),
(1450, 'UPDATE', 'kis_applications', 69, 'Status lama: Pending', 'Status diubah ke Approved', 13, '2024-05-14 04:48:00', '2025-12-13 16:31:34'),
(1451, 'UPDATE', 'events', 89, 'Status lama: Pending', 'Event diupdate', 2, '2024-09-25 03:25:00', '2025-12-13 16:31:34'),
(1452, 'UPDATE', 'kis_applications', 142, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-11-18 02:34:00', '2025-12-13 16:31:34'),
(1453, 'INSERT', 'event_registrations', 69, NULL, 'Peserta didaftarkan ke event', 95, '2024-03-11 07:58:00', '2025-12-13 16:31:34'),
(1454, 'UPDATE', 'kis_applications', 109, 'Status lama: Pending', 'Status diubah ke Rejected', 69, '2024-04-05 04:06:00', '2025-12-13 16:31:34'),
(1455, 'UPDATE', 'kis_applications', 122, 'Status lama: Pending', 'Status diubah ke Approved', 33, '2024-12-11 16:25:00', '2025-12-13 16:31:34'),
(1456, 'INSERT', 'events', 25, NULL, 'Event baru dibuat', 4, '2024-12-21 02:17:00', '2025-12-13 16:31:34'),
(1457, 'UPDATE', 'kis_applications', 100, 'Status lama: Pending', 'Status diubah ke Rejected', 4, '2024-11-17 14:43:00', '2025-12-13 16:31:34'),
(1458, 'UPDATE', 'events', 169, 'Status lama: Pending', 'Event diupdate', 2, '2024-12-23 14:52:00', '2025-12-13 16:31:34'),
(1459, 'INSERT', 'kis_applications', 185, NULL, 'Pengajuan KIS baru', 36, '2024-09-12 08:10:00', '2025-12-13 16:31:34'),
(1460, 'INSERT', 'club_dues', 128, NULL, 'Iuran klub dibayar', 4, '2023-12-31 23:29:00', '2025-12-13 16:31:34'),
(1461, 'INSERT', 'kis_applications', 55, NULL, 'Pengajuan KIS baru', 3, '2024-11-27 05:31:00', '2025-12-13 16:31:34'),
(1462, 'INSERT', 'kis_applications', 40, NULL, 'Pengajuan KIS baru', 102, '2024-05-08 06:08:00', '2025-12-13 16:31:34'),
(1463, 'UPDATE', 'events', 189, 'Status lama: Pending', 'Event diupdate', 30, '2024-01-25 09:38:00', '2025-12-13 16:31:34'),
(1464, 'INSERT', 'club_dues', 53, NULL, 'Iuran klub dibayar', 3, '2024-07-06 12:57:00', '2025-12-13 16:31:34'),
(1465, 'UPDATE', 'kis_applications', 45, 'Status lama: Pending', 'Status diubah ke Approved', 71, '2024-09-14 13:05:00', '2025-12-13 16:31:34'),
(1466, 'INSERT', 'events', 81, NULL, 'Event baru dibuat', 91, '2024-11-20 02:01:00', '2025-12-13 16:31:34'),
(1467, 'INSERT', 'club_dues', 34, NULL, 'Iuran klub dibayar', 29, '2024-09-24 19:51:00', '2025-12-13 16:31:34'),
(1468, 'UPDATE', 'kis_applications', 27, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-08-27 08:52:00', '2025-12-13 16:31:34'),
(1469, 'INSERT', 'club_dues', 182, NULL, 'Iuran klub dibayar', 4, '2024-11-03 23:14:00', '2025-12-13 16:31:34'),
(1470, 'INSERT', 'event_registrations', 154, NULL, 'Peserta didaftarkan ke event', 3, '2024-08-04 21:13:00', '2025-12-13 16:31:34'),
(1471, 'UPDATE', 'events', 176, 'Status lama: Pending', 'Event diupdate', 54, '2024-03-25 07:01:00', '2025-12-13 16:31:34'),
(1472, 'INSERT', 'club_dues', 190, NULL, 'Iuran klub dibayar', 49, '2024-08-03 19:07:00', '2025-12-13 16:31:34'),
(1473, 'UPDATE', 'events', 73, 'Status lama: Pending', 'Event diupdate', 62, '2024-04-21 19:11:00', '2025-12-13 16:31:34'),
(1474, 'INSERT', 'event_registrations', 147, NULL, 'Peserta didaftarkan ke event', 20, '2024-06-25 05:12:00', '2025-12-13 16:31:34'),
(1475, 'UPDATE', 'kis_applications', 11, 'Status lama: Pending', 'Status diubah ke Rejected', 43, '2024-02-19 12:38:00', '2025-12-13 16:31:34'),
(1476, 'UPDATE', 'events', 175, 'Status lama: Pending', 'Event diupdate', 22, '2024-10-15 13:42:00', '2025-12-13 16:31:34'),
(1477, 'INSERT', 'event_registrations', 154, NULL, 'Peserta didaftarkan ke event', 49, '2024-08-15 09:23:00', '2025-12-13 16:31:34'),
(1478, 'INSERT', 'events', 6, NULL, 'Event baru dibuat', 27, '2024-04-01 05:54:00', '2025-12-13 16:31:34'),
(1479, 'UPDATE', 'kis_applications', 128, 'Status lama: Pending', 'Status diubah ke Approved', 46, '2024-02-08 10:49:00', '2025-12-13 16:31:34'),
(1480, 'UPDATE', 'kis_applications', 181, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-07-22 10:29:00', '2025-12-13 16:31:34'),
(1481, 'INSERT', 'club_dues', 80, NULL, 'Iuran klub dibayar', 15, '2024-03-17 02:11:00', '2025-12-13 16:31:34'),
(1482, 'INSERT', 'club_dues', 84, NULL, 'Iuran klub dibayar', 1, '2024-12-07 08:07:00', '2025-12-13 16:31:34'),
(1483, 'UPDATE', 'events', 77, 'Status lama: Pending', 'Event diupdate', 24, '2024-12-20 01:25:00', '2025-12-13 16:31:34'),
(1484, 'INSERT', 'events', 173, NULL, 'Event baru dibuat', 32, '2024-04-24 01:31:00', '2025-12-13 16:31:34'),
(1485, 'INSERT', 'event_registrations', 192, NULL, 'Peserta didaftarkan ke event', 78, '2024-05-09 19:32:00', '2025-12-13 16:31:34'),
(1486, 'UPDATE', 'kis_applications', 167, 'Status lama: Pending', 'Status diubah ke Rejected', 2, '2024-12-15 19:00:00', '2025-12-13 16:31:34'),
(1487, 'UPDATE', 'kis_applications', 110, 'Status lama: Pending', 'Status diubah ke Approved', 62, '2024-07-04 07:42:00', '2025-12-13 16:31:34'),
(1488, 'UPDATE', 'kis_applications', 165, 'Status lama: Pending', 'Status diubah ke Rejected', 2, '2024-03-16 22:19:00', '2025-12-13 16:31:34'),
(1489, 'INSERT', 'events', 54, NULL, 'Event baru dibuat', 79, '2024-02-23 11:13:00', '2025-12-13 16:31:34'),
(1490, 'UPDATE', 'kis_applications', 45, 'Status lama: Pending', 'Status diubah ke Approved', 53, '2024-10-14 04:13:00', '2025-12-13 16:31:34'),
(1491, 'INSERT', 'club_dues', 112, NULL, 'Iuran klub dibayar', 4, '2024-07-27 17:57:00', '2025-12-13 16:31:34'),
(1492, 'UPDATE', 'kis_applications', 124, 'Status lama: Pending', 'Status diubah ke Rejected', 22, '2024-12-06 06:26:00', '2025-12-13 16:31:34'),
(1493, 'UPDATE', 'kis_applications', 20, 'Status lama: Pending', 'Status diubah ke Approved', 12, '2024-10-21 20:08:00', '2025-12-13 16:31:34'),
(1494, 'INSERT', 'kis_applications', 38, NULL, 'Pengajuan KIS baru', 94, '2024-10-15 18:52:00', '2025-12-13 16:31:34'),
(1495, 'UPDATE', 'kis_applications', 123, 'Status lama: Pending', 'Status diubah ke Approved', 4, '2024-10-04 08:24:00', '2025-12-13 16:31:34'),
(1496, 'UPDATE', 'kis_applications', 27, 'Status lama: Pending', 'Status diubah ke Rejected', 4, '2024-07-20 00:13:00', '2025-12-13 16:31:34'),
(1497, 'INSERT', 'event_registrations', 31, NULL, 'Peserta didaftarkan ke event', 90, '2024-04-07 10:42:00', '2025-12-13 16:31:34'),
(1498, 'INSERT', 'event_registrations', 171, NULL, 'Peserta didaftarkan ke event', 23, '2024-11-21 21:49:00', '2025-12-13 16:31:34'),
(1499, 'UPDATE', 'kis_applications', 5, 'Status lama: Pending', 'Status diubah ke Rejected', 74, '2024-12-23 15:26:00', '2025-12-13 16:31:34'),
(1500, 'INSERT', 'club_dues', 135, NULL, 'Iuran klub dibayar', 4, '2024-07-03 04:14:00', '2025-12-13 16:31:34'),
(1501, 'INSERT', 'kis_applications', 107, NULL, 'Pengajuan KIS baru', 2, '2024-12-07 09:07:00', '2025-12-13 16:31:34'),
(1502, 'UPDATE', 'kis_applications', 11, 'Status lama: Pending', 'Status diubah ke Rejected', 56, '2024-09-27 11:57:00', '2025-12-13 16:31:34'),
(1503, 'UPDATE', 'kis_applications', 184, 'Status lama: Pending', 'Status diubah ke Approved', 49, '2024-01-25 00:55:00', '2025-12-13 16:31:34'),
(1504, 'INSERT', 'events', 38, NULL, 'Event baru dibuat', 2, '2024-12-27 01:29:00', '2025-12-13 16:31:34'),
(1505, 'INSERT', 'events', 3, NULL, 'Event baru dibuat', 43, '2024-11-27 16:02:00', '2025-12-13 16:31:34'),
(1506, 'UPDATE', 'kis_applications', 81, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-06-14 12:29:00', '2025-12-13 16:31:34'),
(1507, 'INSERT', 'kis_applications', 30, NULL, 'Pengajuan KIS baru', 29, '2024-04-27 18:28:00', '2025-12-13 16:31:34'),
(1508, 'UPDATE', 'kis_applications', 22, 'Status lama: Pending', 'Status diubah ke Rejected', 94, '2024-03-11 14:06:00', '2025-12-13 16:31:34'),
(1509, 'INSERT', 'kis_applications', 118, NULL, 'Pengajuan KIS baru', 2, '2024-01-18 09:55:00', '2025-12-13 16:31:34'),
(1510, 'INSERT', 'event_registrations', 26, NULL, 'Peserta didaftarkan ke event', 28, '2024-09-04 07:24:00', '2025-12-13 16:31:34'),
(1511, 'INSERT', 'events', 93, NULL, 'Event baru dibuat', 19, '2024-11-20 14:19:00', '2025-12-13 16:31:34'),
(1512, 'UPDATE', 'kis_applications', 117, 'Status lama: Pending', 'Status diubah ke Rejected', 107, '2024-06-04 19:00:00', '2025-12-13 16:31:34'),
(1513, 'UPDATE', 'kis_applications', 89, 'Status lama: Pending', 'Status diubah ke Rejected', 104, '2024-04-21 03:39:00', '2025-12-13 16:31:34'),
(1514, 'INSERT', 'club_dues', 177, NULL, 'Iuran klub dibayar', 13, '2024-08-11 19:16:00', '2025-12-13 16:31:34'),
(1515, 'INSERT', 'kis_applications', 127, NULL, 'Pengajuan KIS baru', 17, '2024-07-14 23:05:00', '2025-12-13 16:31:34'),
(1516, 'INSERT', 'events', 31, NULL, 'Event baru dibuat', 2, '2024-12-08 00:55:00', '2025-12-13 16:31:34'),
(1517, 'UPDATE', 'kis_applications', 161, 'Status lama: Pending', 'Status diubah ke Approved', 63, '2024-06-02 08:50:00', '2025-12-13 16:31:34'),
(1518, 'INSERT', 'club_dues', 93, NULL, 'Iuran klub dibayar', 96, '2024-01-08 10:10:00', '2025-12-13 16:31:34'),
(1519, 'UPDATE', 'kis_applications', 68, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-05-08 11:49:00', '2025-12-13 16:31:34'),
(1520, 'UPDATE', 'kis_applications', 41, 'Status lama: Pending', 'Status diubah ke Rejected', 2, '2024-04-09 09:28:00', '2025-12-13 16:31:34'),
(1521, 'UPDATE', 'events', 10, 'Status lama: Pending', 'Event diupdate', 4, '2024-03-03 23:37:00', '2025-12-13 16:31:34'),
(1522, 'UPDATE', 'kis_applications', 185, 'Status lama: Pending', 'Status diubah ke Rejected', 33, '2024-12-12 02:46:00', '2025-12-13 16:31:34'),
(1523, 'INSERT', 'event_registrations', 34, NULL, 'Peserta didaftarkan ke event', 104, '2024-04-03 16:05:00', '2025-12-13 16:31:34'),
(1524, 'UPDATE', 'kis_applications', 81, 'Status lama: Pending', 'Status diubah ke Rejected', 3, '2024-04-09 13:55:00', '2025-12-13 16:31:34'),
(1525, 'UPDATE', 'events', 187, 'Status lama: Pending', 'Event diupdate', 4, '2024-08-26 12:39:00', '2025-12-13 16:31:34'),
(1526, 'INSERT', 'events', 131, NULL, 'Event baru dibuat', 8, '2024-12-10 21:44:00', '2025-12-13 16:31:34'),
(1527, 'INSERT', 'event_registrations', 52, NULL, 'Peserta didaftarkan ke event', 11, '2024-11-17 11:19:00', '2025-12-13 16:31:34'),
(1528, 'INSERT', 'event_registrations', 17, NULL, 'Peserta didaftarkan ke event', 19, '2024-03-13 21:07:00', '2025-12-13 16:31:34'),
(1529, 'UPDATE', 'kis_applications', 53, 'Status lama: Pending', 'Status diubah ke Rejected', 49, '2024-04-21 05:43:00', '2025-12-13 16:31:34'),
(1530, 'UPDATE', 'kis_applications', 164, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-01-06 09:57:00', '2025-12-13 16:31:34'),
(1531, 'UPDATE', 'kis_applications', 114, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-04-13 11:43:00', '2025-12-13 16:31:34'),
(1532, 'INSERT', 'club_dues', 147, NULL, 'Iuran klub dibayar', 84, '2024-08-13 04:43:00', '2025-12-13 16:31:34'),
(1533, 'INSERT', 'event_registrations', 20, NULL, 'Peserta didaftarkan ke event', 59, '2024-09-26 01:07:00', '2025-12-13 16:31:34'),
(1534, 'INSERT', 'event_registrations', 57, NULL, 'Peserta didaftarkan ke event', 89, '2024-03-20 15:52:00', '2025-12-13 16:31:34'),
(1535, 'INSERT', 'kis_applications', 53, NULL, 'Pengajuan KIS baru', 4, '2024-06-28 15:17:00', '2025-12-13 16:31:34'),
(1536, 'INSERT', 'events', 14, NULL, 'Event baru dibuat', 4, '2024-07-05 20:54:00', '2025-12-13 16:31:34'),
(1537, 'UPDATE', 'events', 57, 'Status lama: Pending', 'Event diupdate', 63, '2024-04-26 03:37:00', '2025-12-13 16:31:34'),
(1538, 'INSERT', 'event_registrations', 83, NULL, 'Peserta didaftarkan ke event', 1, '2024-01-06 14:39:00', '2025-12-13 16:31:34'),
(1539, 'INSERT', 'events', 96, NULL, 'Event baru dibuat', 55, '2024-11-09 15:05:00', '2025-12-13 16:31:34'),
(1540, 'INSERT', 'club_dues', 60, NULL, 'Iuran klub dibayar', 94, '2024-11-15 08:24:00', '2025-12-13 16:31:34'),
(1541, 'INSERT', 'club_dues', 76, NULL, 'Iuran klub dibayar', 79, '2024-10-06 11:19:00', '2025-12-13 16:31:34'),
(1542, 'UPDATE', 'kis_applications', 136, 'Status lama: Pending', 'Status diubah ke Approved', 103, '2024-12-25 08:46:00', '2025-12-13 16:31:34'),
(1543, 'INSERT', 'events', 193, NULL, 'Event baru dibuat', 27, '2024-01-11 18:49:00', '2025-12-13 16:31:34'),
(1544, 'INSERT', 'events', 18, NULL, 'Event baru dibuat', 55, '2024-03-05 01:57:00', '2025-12-13 16:31:34'),
(1545, 'UPDATE', 'events', 88, 'Status lama: Pending', 'Event diupdate', 3, '2024-05-25 16:53:00', '2025-12-13 16:31:34'),
(1546, 'UPDATE', 'kis_applications', 73, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-06-28 02:06:00', '2025-12-13 16:31:34'),
(1547, 'INSERT', 'kis_applications', 50, NULL, 'Pengajuan KIS baru', 31, '2024-01-23 06:20:00', '2025-12-13 16:31:34'),
(1548, 'INSERT', 'event_registrations', 78, NULL, 'Peserta didaftarkan ke event', 12, '2024-01-01 05:36:00', '2025-12-13 16:31:34'),
(1549, 'UPDATE', 'kis_applications', 18, 'Status lama: Pending', 'Status diubah ke Rejected', 50, '2024-05-26 03:22:00', '2025-12-13 16:31:34'),
(1550, 'INSERT', 'event_registrations', 78, NULL, 'Peserta didaftarkan ke event', 3, '2024-04-27 23:11:00', '2025-12-13 16:31:34'),
(1551, 'UPDATE', 'events', 88, 'Status lama: Pending', 'Event diupdate', 8, '2024-02-07 01:22:00', '2025-12-13 16:31:34'),
(1552, 'INSERT', 'events', 35, NULL, 'Event baru dibuat', 93, '2024-08-03 10:02:00', '2025-12-13 16:31:34'),
(1553, 'INSERT', 'club_dues', 63, NULL, 'Iuran klub dibayar', 35, '2024-12-22 07:02:00', '2025-12-13 16:31:34'),
(1554, 'INSERT', 'kis_applications', 130, NULL, 'Pengajuan KIS baru', 49, '2024-02-17 22:21:00', '2025-12-13 16:31:34'),
(1555, 'UPDATE', 'kis_applications', 13, 'Status lama: Pending', 'Status diubah ke Rejected', 103, '2024-06-03 00:21:00', '2025-12-13 16:31:34'),
(1556, 'INSERT', 'club_dues', 118, NULL, 'Iuran klub dibayar', 28, '2024-07-03 06:43:00', '2025-12-13 16:31:34'),
(1557, 'UPDATE', 'kis_applications', 126, 'Status lama: Pending', 'Status diubah ke Rejected', 104, '2024-02-21 16:00:00', '2025-12-13 16:31:34'),
(1558, 'UPDATE', 'kis_applications', 152, 'Status lama: Pending', 'Status diubah ke Approved', 42, '2024-04-24 23:08:00', '2025-12-13 16:31:34'),
(1559, 'INSERT', 'kis_applications', 24, NULL, 'Pengajuan KIS baru', 75, '2024-03-23 19:21:00', '2025-12-13 16:31:34'),
(1560, 'UPDATE', 'kis_applications', 153, 'Status lama: Pending', 'Status diubah ke Rejected', 31, '2024-04-12 07:45:00', '2025-12-13 16:31:34'),
(1561, 'INSERT', 'event_registrations', 165, NULL, 'Peserta didaftarkan ke event', 92, '2024-07-18 22:24:00', '2025-12-13 16:31:34'),
(1562, 'UPDATE', 'kis_applications', 127, 'Status lama: Pending', 'Status diubah ke Approved', 61, '2024-01-16 01:11:00', '2025-12-13 16:31:34'),
(1563, 'INSERT', 'events', 53, NULL, 'Event baru dibuat', 45, '2024-02-27 05:25:00', '2025-12-13 16:31:34'),
(1564, 'INSERT', 'events', 85, NULL, 'Event baru dibuat', 69, '2024-05-13 19:37:00', '2025-12-13 16:31:34'),
(1565, 'INSERT', 'event_registrations', 3, NULL, 'Peserta didaftarkan ke event', 80, '2024-05-16 02:02:00', '2025-12-13 16:31:34'),
(1566, 'INSERT', 'club_dues', 90, NULL, 'Iuran klub dibayar', 22, '2024-05-18 11:41:00', '2025-12-13 16:31:34'),
(1567, 'INSERT', 'event_registrations', 73, NULL, 'Peserta didaftarkan ke event', 4, '2024-03-14 22:19:00', '2025-12-13 16:31:34'),
(1568, 'UPDATE', 'kis_applications', 155, 'Status lama: Pending', 'Status diubah ke Approved', 27, '2024-03-03 19:51:00', '2025-12-13 16:31:34'),
(1569, 'UPDATE', 'kis_applications', 77, 'Status lama: Pending', 'Status diubah ke Rejected', 43, '2024-09-15 00:03:00', '2025-12-13 16:31:34'),
(1570, 'UPDATE', 'kis_applications', 89, 'Status lama: Pending', 'Status diubah ke Rejected', 77, '2024-11-03 16:22:00', '2025-12-13 16:31:34'),
(1571, 'INSERT', 'club_dues', 198, NULL, 'Iuran klub dibayar', 84, '2024-11-15 20:46:00', '2025-12-13 16:31:34'),
(1572, 'INSERT', 'club_dues', 183, NULL, 'Iuran klub dibayar', 56, '2024-09-20 16:32:00', '2025-12-13 16:31:34'),
(1573, 'UPDATE', 'kis_applications', 53, 'Status lama: Pending', 'Status diubah ke Rejected', 66, '2024-04-20 19:07:00', '2025-12-13 16:31:34'),
(1574, 'INSERT', 'kis_applications', 56, NULL, 'Pengajuan KIS baru', 3, '2024-02-22 18:32:00', '2025-12-13 16:31:34'),
(1575, 'INSERT', 'kis_applications', 45, NULL, 'Pengajuan KIS baru', 1, '2024-03-03 23:09:00', '2025-12-13 16:31:34'),
(1576, 'INSERT', 'event_registrations', 157, NULL, 'Peserta didaftarkan ke event', 68, '2024-09-08 08:46:00', '2025-12-13 16:31:34'),
(1577, 'INSERT', 'event_registrations', 110, NULL, 'Peserta didaftarkan ke event', 3, '2024-12-19 21:10:00', '2025-12-13 16:31:34'),
(1578, 'INSERT', 'events', 10, NULL, 'Event baru dibuat', 3, '2024-08-24 18:06:00', '2025-12-13 16:31:34'),
(1579, 'INSERT', 'club_dues', 71, NULL, 'Iuran klub dibayar', 11, '2024-06-05 19:31:00', '2025-12-13 16:31:34'),
(1580, 'INSERT', 'kis_applications', 59, NULL, 'Pengajuan KIS baru', 2, '2024-06-16 21:44:00', '2025-12-13 16:31:34'),
(1581, 'UPDATE', 'events', 115, 'Status lama: Pending', 'Event diupdate', 88, '2024-01-31 18:21:00', '2025-12-13 16:31:34'),
(1582, 'INSERT', 'club_dues', 170, NULL, 'Iuran klub dibayar', 4, '2024-09-18 04:44:00', '2025-12-13 16:31:34'),
(1583, 'INSERT', 'club_dues', 37, NULL, 'Iuran klub dibayar', 1, '2024-10-07 14:19:00', '2025-12-13 16:31:34'),
(1584, 'INSERT', 'event_registrations', 103, NULL, 'Peserta didaftarkan ke event', 45, '2024-10-23 15:11:00', '2025-12-13 16:31:34'),
(1585, 'UPDATE', 'events', 57, 'Status lama: Pending', 'Event diupdate', 69, '2024-05-09 19:50:00', '2025-12-13 16:31:34'),
(1586, 'UPDATE', 'kis_applications', 14, 'Status lama: Pending', 'Status diubah ke Approved', 75, '2024-05-22 16:00:00', '2025-12-13 16:31:34'),
(1587, 'UPDATE', 'events', 45, 'Status lama: Pending', 'Event diupdate', 96, '2024-02-01 05:04:00', '2025-12-13 16:31:34'),
(1588, 'INSERT', 'event_registrations', 131, NULL, 'Peserta didaftarkan ke event', 1, '2024-03-05 17:57:00', '2025-12-13 16:31:34'),
(1589, 'INSERT', 'events', 135, NULL, 'Event baru dibuat', 1, '2024-10-04 03:28:00', '2025-12-13 16:31:34'),
(1590, 'UPDATE', 'kis_applications', 79, 'Status lama: Pending', 'Status diubah ke Approved', 66, '2024-06-13 23:04:00', '2025-12-13 16:31:34'),
(1591, 'UPDATE', 'kis_applications', 117, 'Status lama: Pending', 'Status diubah ke Rejected', 18, '2024-06-18 20:57:00', '2025-12-13 16:31:34'),
(1592, 'INSERT', 'club_dues', 157, NULL, 'Iuran klub dibayar', 96, '2024-09-26 08:39:00', '2025-12-13 16:31:34'),
(1593, 'INSERT', 'events', 61, NULL, 'Event baru dibuat', 77, '2024-09-26 11:19:00', '2025-12-13 16:31:34'),
(1594, 'INSERT', 'events', 92, NULL, 'Event baru dibuat', 56, '2024-01-15 05:58:00', '2025-12-13 16:31:34'),
(1595, 'UPDATE', 'events', 181, 'Status lama: Pending', 'Event diupdate', 67, '2024-05-16 20:10:00', '2025-12-13 16:31:34'),
(1596, 'INSERT', 'events', 43, NULL, 'Event baru dibuat', 1, '2024-01-21 11:45:00', '2025-12-13 16:31:34'),
(1597, 'UPDATE', 'kis_applications', 99, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-03-03 14:30:00', '2025-12-13 16:31:34'),
(1598, 'UPDATE', 'kis_applications', 79, 'Status lama: Pending', 'Status diubah ke Approved', 48, '2024-06-01 03:19:00', '2025-12-13 16:31:34'),
(1599, 'UPDATE', 'kis_applications', 166, 'Status lama: Pending', 'Status diubah ke Approved', 96, '2024-01-17 03:28:00', '2025-12-13 16:31:34'),
(1600, 'INSERT', 'event_registrations', 90, NULL, 'Peserta didaftarkan ke event', 3, '2024-11-13 06:44:00', '2025-12-13 16:31:34'),
(1601, 'UPDATE', 'kis_applications', 123, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-01-13 13:11:00', '2025-12-13 16:31:34'),
(1602, 'INSERT', 'events', 14, NULL, 'Event baru dibuat', 21, '2024-04-10 07:00:00', '2025-12-13 16:31:34'),
(1603, 'INSERT', 'club_dues', 29, NULL, 'Iuran klub dibayar', 9, '2024-06-08 13:40:00', '2025-12-13 16:31:34'),
(1604, 'UPDATE', 'events', 68, 'Status lama: Pending', 'Event diupdate', 20, '2024-06-09 05:32:00', '2025-12-13 16:31:34'),
(1605, 'UPDATE', 'events', 26, 'Status lama: Pending', 'Event diupdate', 42, '2024-01-19 01:25:00', '2025-12-13 16:31:34'),
(1606, 'INSERT', 'event_registrations', 19, NULL, 'Peserta didaftarkan ke event', 25, '2024-07-05 16:34:00', '2025-12-13 16:31:34'),
(1607, 'INSERT', 'club_dues', 109, NULL, 'Iuran klub dibayar', 26, '2024-01-19 05:55:00', '2025-12-13 16:31:34'),
(1608, 'UPDATE', 'kis_applications', 118, 'Status lama: Pending', 'Status diubah ke Rejected', 102, '2024-10-06 14:40:00', '2025-12-13 16:31:34'),
(1609, 'INSERT', 'events', 188, NULL, 'Event baru dibuat', 2, '2024-03-06 06:58:00', '2025-12-13 16:31:34'),
(1610, 'UPDATE', 'events', 17, 'Status lama: Pending', 'Event diupdate', 26, '2024-03-21 01:50:00', '2025-12-13 16:31:34'),
(1611, 'INSERT', 'event_registrations', 50, NULL, 'Peserta didaftarkan ke event', 1, '2024-09-28 16:59:00', '2025-12-13 16:31:34'),
(1612, 'UPDATE', 'kis_applications', 13, 'Status lama: Pending', 'Status diubah ke Approved', 9, '2024-06-20 22:47:00', '2025-12-13 16:31:34'),
(1613, 'INSERT', 'kis_applications', 76, NULL, 'Pengajuan KIS baru', 2, '2024-11-28 10:13:00', '2025-12-13 16:31:34'),
(1614, 'INSERT', 'event_registrations', 180, NULL, 'Peserta didaftarkan ke event', 21, '2024-06-20 18:50:00', '2025-12-13 16:31:34'),
(1615, 'INSERT', 'events', 149, NULL, 'Event baru dibuat', 42, '2024-09-09 12:59:00', '2025-12-13 16:31:34'),
(1616, 'INSERT', 'events', 13, NULL, 'Event baru dibuat', 73, '2024-10-19 01:45:00', '2025-12-13 16:31:34'),
(1617, 'INSERT', 'club_dues', 178, NULL, 'Iuran klub dibayar', 50, '2024-06-06 03:57:00', '2025-12-13 16:31:34'),
(1618, 'UPDATE', 'kis_applications', 140, 'Status lama: Pending', 'Status diubah ke Rejected', 18, '2024-04-13 10:17:00', '2025-12-13 16:31:34'),
(1619, 'INSERT', 'events', 144, NULL, 'Event baru dibuat', 64, '2024-01-07 14:28:00', '2025-12-13 16:31:34'),
(1620, 'INSERT', 'club_dues', 56, NULL, 'Iuran klub dibayar', 2, '2024-01-05 04:34:00', '2025-12-13 16:31:34'),
(1621, 'INSERT', 'club_dues', 184, NULL, 'Iuran klub dibayar', 77, '2024-04-26 09:50:00', '2025-12-13 16:31:34'),
(1622, 'UPDATE', 'kis_applications', 53, 'Status lama: Pending', 'Status diubah ke Rejected', 3, '2024-01-18 20:18:00', '2025-12-13 16:31:34'),
(1623, 'UPDATE', 'events', 164, 'Status lama: Pending', 'Event diupdate', 71, '2024-08-10 11:24:00', '2025-12-13 16:31:34'),
(1624, 'UPDATE', 'kis_applications', 81, 'Status lama: Pending', 'Status diubah ke Approved', 3, '2024-06-19 20:37:00', '2025-12-13 16:31:34'),
(1625, 'UPDATE', 'kis_applications', 195, 'Status lama: Pending', 'Status diubah ke Approved', 12, '2024-02-11 01:47:00', '2025-12-13 16:31:34'),
(1626, 'INSERT', 'kis_applications', 169, NULL, 'Pengajuan KIS baru', 11, '2024-11-12 14:34:00', '2025-12-13 16:31:34'),
(1627, 'INSERT', 'kis_applications', 29, NULL, 'Pengajuan KIS baru', 14, '2024-04-25 23:42:00', '2025-12-13 16:31:34'),
(1628, 'INSERT', 'events', 41, NULL, 'Event baru dibuat', 20, '2024-09-23 07:20:00', '2025-12-13 16:31:34'),
(1629, 'UPDATE', 'kis_applications', 155, 'Status lama: Pending', 'Status diubah ke Approved', 9, '2024-09-19 11:34:00', '2025-12-13 16:31:34'),
(1630, 'UPDATE', 'kis_applications', 53, 'Status lama: Pending', 'Status diubah ke Approved', 50, '2024-12-18 16:35:00', '2025-12-13 16:31:34'),
(1631, 'UPDATE', 'kis_applications', 187, 'Status lama: Pending', 'Status diubah ke Approved', 12, '2024-05-06 19:56:00', '2025-12-13 16:31:34'),
(1632, 'INSERT', 'club_dues', 109, NULL, 'Iuran klub dibayar', 2, '2024-07-08 14:05:00', '2025-12-13 16:31:34'),
(1633, 'UPDATE', 'events', 120, 'Status lama: Pending', 'Event diupdate', 4, '2024-01-02 01:54:00', '2025-12-13 16:31:34'),
(1634, 'INSERT', 'events', 191, NULL, 'Event baru dibuat', 30, '2024-08-07 19:37:00', '2025-12-13 16:31:34'),
(1635, 'INSERT', 'events', 34, NULL, 'Event baru dibuat', 83, '2024-12-12 19:58:00', '2025-12-13 16:31:34'),
(1636, 'UPDATE', 'events', 130, 'Status lama: Pending', 'Event diupdate', 89, '2024-03-16 17:14:00', '2025-12-13 16:31:34'),
(1637, 'INSERT', 'club_dues', 146, NULL, 'Iuran klub dibayar', 72, '2024-11-02 21:53:00', '2025-12-13 16:31:34'),
(1638, 'UPDATE', 'events', 184, 'Status lama: Pending', 'Event diupdate', 23, '2024-02-12 09:47:00', '2025-12-13 16:31:34'),
(1639, 'INSERT', 'events', 39, NULL, 'Event baru dibuat', 97, '2024-06-16 22:56:00', '2025-12-13 16:31:34'),
(1640, 'UPDATE', 'kis_applications', 39, 'Status lama: Pending', 'Status diubah ke Approved', 72, '2024-12-20 21:23:00', '2025-12-13 16:31:34'),
(1641, 'UPDATE', 'kis_applications', 128, 'Status lama: Pending', 'Status diubah ke Rejected', 74, '2024-12-17 06:00:00', '2025-12-13 16:31:34'),
(1642, 'INSERT', 'kis_applications', 84, NULL, 'Pengajuan KIS baru', 46, '2024-12-26 11:09:00', '2025-12-13 16:31:34'),
(1643, 'INSERT', 'events', 15, NULL, 'Event baru dibuat', 44, '2024-07-07 10:26:00', '2025-12-13 16:31:34'),
(1644, 'INSERT', 'club_dues', 64, NULL, 'Iuran klub dibayar', 26, '2024-07-14 12:16:00', '2025-12-13 16:31:34');
INSERT INTO `logs` (`id`, `action_type`, `table_name`, `record_id`, `old_value`, `new_value`, `user_id`, `created_at`, `updated_at`) VALUES
(1645, 'UPDATE', 'kis_applications', 123, 'Status lama: Pending', 'Status diubah ke Rejected', 82, '2024-02-18 02:44:00', '2025-12-13 16:31:34'),
(1646, 'INSERT', 'club_dues', 91, NULL, 'Iuran klub dibayar', 93, '2024-04-04 05:27:00', '2025-12-13 16:31:34'),
(1647, 'UPDATE', 'kis_applications', 19, 'Status lama: Pending', 'Status diubah ke Approved', 3, '2024-04-21 22:06:00', '2025-12-13 16:31:34'),
(1648, 'INSERT', 'events', 13, NULL, 'Event baru dibuat', 4, '2024-02-16 23:58:00', '2025-12-13 16:31:34'),
(1649, 'INSERT', 'club_dues', 59, NULL, 'Iuran klub dibayar', 1, '2024-01-04 00:08:00', '2025-12-13 16:31:34'),
(1650, 'INSERT', 'club_dues', 71, NULL, 'Iuran klub dibayar', 73, '2024-06-26 03:37:00', '2025-12-13 16:31:34'),
(1651, 'INSERT', 'club_dues', 88, NULL, 'Iuran klub dibayar', 13, '2024-03-11 04:35:00', '2025-12-13 16:31:34'),
(1652, 'INSERT', 'club_dues', 28, NULL, 'Iuran klub dibayar', 4, '2024-08-18 05:21:00', '2025-12-13 16:31:34'),
(1653, 'INSERT', 'event_registrations', 119, NULL, 'Peserta didaftarkan ke event', 84, '2024-11-17 00:02:00', '2025-12-13 16:31:34'),
(1654, 'UPDATE', 'kis_applications', 3, 'Status lama: Pending', 'Status diubah ke Approved', 92, '2024-09-08 08:54:00', '2025-12-13 16:31:34'),
(1655, 'UPDATE', 'kis_applications', 21, 'Status lama: Pending', 'Status diubah ke Rejected', 96, '2024-06-01 22:52:00', '2025-12-13 16:31:34'),
(1656, 'INSERT', 'event_registrations', 192, NULL, 'Peserta didaftarkan ke event', 4, '2024-12-15 16:56:00', '2025-12-13 16:31:34'),
(1657, 'UPDATE', 'events', 118, 'Status lama: Pending', 'Event diupdate', 2, '2024-09-06 12:03:00', '2025-12-13 16:31:34'),
(1658, 'INSERT', 'event_registrations', 5, NULL, 'Peserta didaftarkan ke event', 2, '2024-06-14 06:27:00', '2025-12-13 16:31:34'),
(1659, 'INSERT', 'event_registrations', 72, NULL, 'Peserta didaftarkan ke event', 1, '2024-06-25 20:41:00', '2025-12-13 16:31:34'),
(1660, 'INSERT', 'events', 56, NULL, 'Event baru dibuat', 106, '2024-06-25 12:12:00', '2025-12-13 16:31:34'),
(1661, 'UPDATE', 'kis_applications', 189, 'Status lama: Pending', 'Status diubah ke Rejected', 57, '2024-07-23 19:07:00', '2025-12-13 16:31:34'),
(1662, 'INSERT', 'kis_applications', 165, NULL, 'Pengajuan KIS baru', 57, '2024-10-27 08:54:00', '2025-12-13 16:31:34'),
(1663, 'INSERT', 'kis_applications', 162, NULL, 'Pengajuan KIS baru', 3, '2024-01-27 11:04:00', '2025-12-13 16:31:34'),
(1664, 'UPDATE', 'kis_applications', 130, 'Status lama: Pending', 'Status diubah ke Approved', 65, '2024-07-18 12:28:00', '2025-12-13 16:31:34'),
(1665, 'UPDATE', 'kis_applications', 10, 'Status lama: Pending', 'Status diubah ke Rejected', 2, '2024-04-20 23:49:00', '2025-12-13 16:31:34'),
(1666, 'INSERT', 'club_dues', 2, NULL, 'Iuran klub dibayar', 4, '2024-12-14 12:12:00', '2025-12-13 16:31:34'),
(1667, 'INSERT', 'club_dues', 132, NULL, 'Iuran klub dibayar', 105, '2024-12-14 18:03:00', '2025-12-13 16:31:34'),
(1668, 'UPDATE', 'kis_applications', 183, 'Status lama: Pending', 'Status diubah ke Approved', 41, '2024-06-17 16:49:00', '2025-12-13 16:31:34'),
(1669, 'UPDATE', 'events', 191, 'Status lama: Pending', 'Event diupdate', 83, '2024-06-21 02:23:00', '2025-12-13 16:31:34'),
(1670, 'INSERT', 'club_dues', 190, NULL, 'Iuran klub dibayar', 79, '2024-01-02 10:19:00', '2025-12-13 16:31:34'),
(1671, 'INSERT', 'event_registrations', 25, NULL, 'Peserta didaftarkan ke event', 2, '2024-10-04 23:45:00', '2025-12-13 16:31:34'),
(1672, 'INSERT', 'event_registrations', 103, NULL, 'Peserta didaftarkan ke event', 80, '2024-05-16 10:43:00', '2025-12-13 16:31:34'),
(1673, 'INSERT', 'club_dues', 106, NULL, 'Iuran klub dibayar', 2, '2024-07-14 23:17:00', '2025-12-13 16:31:34'),
(1674, 'UPDATE', 'kis_applications', 157, 'Status lama: Pending', 'Status diubah ke Rejected', 4, '2024-05-08 17:31:00', '2025-12-13 16:31:34'),
(1675, 'UPDATE', 'kis_applications', 130, 'Status lama: Pending', 'Status diubah ke Approved', 78, '2024-01-23 04:52:00', '2025-12-13 16:31:34'),
(1676, 'INSERT', 'event_registrations', 142, NULL, 'Peserta didaftarkan ke event', 59, '2024-12-24 11:43:00', '2025-12-13 16:31:34'),
(1677, 'INSERT', 'events', 152, NULL, 'Event baru dibuat', 38, '2024-03-23 11:34:00', '2025-12-13 16:31:34'),
(1678, 'UPDATE', 'kis_applications', 8, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-08-25 18:31:00', '2025-12-13 16:31:34'),
(1679, 'UPDATE', 'events', 82, 'Status lama: Pending', 'Event diupdate', 37, '2024-04-01 23:46:00', '2025-12-13 16:31:34'),
(1680, 'UPDATE', 'kis_applications', 171, 'Status lama: Pending', 'Status diubah ke Rejected', 66, '2024-12-04 08:06:00', '2025-12-13 16:31:34'),
(1681, 'UPDATE', 'kis_applications', 163, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-01-26 16:20:00', '2025-12-13 16:31:34'),
(1682, 'INSERT', 'kis_applications', 15, NULL, 'Pengajuan KIS baru', 58, '2024-04-07 21:17:00', '2025-12-13 16:31:34'),
(1683, 'UPDATE', 'kis_applications', 9, 'Status lama: Pending', 'Status diubah ke Approved', 23, '2024-04-28 16:05:00', '2025-12-13 16:31:34'),
(1684, 'UPDATE', 'events', 17, 'Status lama: Pending', 'Event diupdate', 10, '2024-03-22 22:25:00', '2025-12-13 16:31:34'),
(1685, 'INSERT', 'event_registrations', 145, NULL, 'Peserta didaftarkan ke event', 99, '2024-12-20 06:22:00', '2025-12-13 16:31:34'),
(1686, 'INSERT', 'club_dues', 22, NULL, 'Iuran klub dibayar', 1, '2024-11-22 11:56:00', '2025-12-13 16:31:34'),
(1687, 'UPDATE', 'events', 105, 'Status lama: Pending', 'Event diupdate', 64, '2024-05-19 17:15:00', '2025-12-13 16:31:34'),
(1688, 'INSERT', 'kis_applications', 173, NULL, 'Pengajuan KIS baru', 101, '2024-09-15 21:10:00', '2025-12-13 16:31:34'),
(1689, 'UPDATE', 'events', 64, 'Status lama: Pending', 'Event diupdate', 70, '2024-06-02 23:50:00', '2025-12-13 16:31:34'),
(1690, 'INSERT', 'club_dues', 25, NULL, 'Iuran klub dibayar', 32, '2024-06-21 04:25:00', '2025-12-13 16:31:34'),
(1691, 'INSERT', 'event_registrations', 137, NULL, 'Peserta didaftarkan ke event', 30, '2024-02-19 12:45:00', '2025-12-13 16:31:34'),
(1692, 'INSERT', 'kis_applications', 35, NULL, 'Pengajuan KIS baru', 4, '2024-02-01 05:01:00', '2025-12-13 16:31:34'),
(1693, 'INSERT', 'events', 147, NULL, 'Event baru dibuat', 4, '2024-11-06 16:53:00', '2025-12-13 16:31:34'),
(1694, 'INSERT', 'kis_applications', 132, NULL, 'Pengajuan KIS baru', 58, '2024-07-28 02:13:00', '2025-12-13 16:31:34'),
(1695, 'UPDATE', 'kis_applications', 170, 'Status lama: Pending', 'Status diubah ke Approved', 1, '2024-02-17 11:20:00', '2025-12-13 16:31:34'),
(1696, 'UPDATE', 'kis_applications', 165, 'Status lama: Pending', 'Status diubah ke Rejected', 38, '2024-04-20 19:35:00', '2025-12-13 16:31:34'),
(1697, 'INSERT', 'kis_applications', 58, NULL, 'Pengajuan KIS baru', 45, '2024-07-25 03:24:00', '2025-12-13 16:31:34'),
(1698, 'UPDATE', 'kis_applications', 154, 'Status lama: Pending', 'Status diubah ke Approved', 74, '2024-01-02 12:13:00', '2025-12-13 16:31:34'),
(1699, 'INSERT', 'event_registrations', 156, NULL, 'Peserta didaftarkan ke event', 93, '2024-03-27 18:27:00', '2025-12-13 16:31:34'),
(1700, 'UPDATE', 'events', 65, 'Status lama: Pending', 'Event diupdate', 100, '2024-05-05 20:43:00', '2025-12-13 16:31:34'),
(1701, 'UPDATE', 'kis_applications', 123, 'Status lama: Pending', 'Status diubah ke Rejected', 1, '2024-02-19 00:06:00', '2025-12-13 16:31:34'),
(1702, 'INSERT', 'event_registrations', 69, NULL, 'Peserta didaftarkan ke event', 24, '2024-08-12 02:04:00', '2025-12-13 16:31:34'),
(1703, 'UPDATE', 'events', 119, 'Status lama: Pending', 'Event diupdate', 52, '2024-06-15 10:59:00', '2025-12-13 16:31:34'),
(1704, 'UPDATE', 'kis_applications', 26, 'Status lama: Pending', 'Status diubah ke Approved', 31, '2024-06-04 03:44:00', '2025-12-13 16:31:34'),
(1705, 'INSERT', 'club_dues', 34, NULL, 'Iuran klub dibayar', 27, '2024-12-10 15:39:00', '2025-12-13 16:31:34'),
(1706, 'UPDATE', 'kis_applications', 34, 'Status lama: Pending', 'Status diubah ke Approved', 27, '2024-12-13 04:18:00', '2025-12-13 16:31:34'),
(1707, 'INSERT', 'kis_applications', 17, NULL, 'Pengajuan KIS baru', 64, '2024-11-22 02:36:00', '2025-12-13 16:31:34'),
(1708, 'INSERT', 'kis_applications', 124, NULL, 'Pengajuan KIS baru', 94, '2024-01-01 07:22:00', '2025-12-13 16:31:34'),
(1709, 'UPDATE', 'kis_applications', 107, 'Status lama: Pending', 'Status diubah ke Rejected', 66, '2024-12-03 04:00:00', '2025-12-13 16:31:34'),
(1710, 'UPDATE', 'kis_applications', 10, 'Status lama: Pending', 'Status diubah ke Approved', 90, '2024-12-14 19:42:00', '2025-12-13 16:31:34'),
(1711, 'INSERT', 'club_dues', 18, NULL, 'Iuran klub dibayar', 34, '2024-07-24 01:05:00', '2025-12-13 16:31:34'),
(1712, 'INSERT', 'event_registrations', 168, NULL, 'Peserta didaftarkan ke event', 2, '2024-01-08 15:51:00', '2025-12-13 16:31:34'),
(1713, 'UPDATE', 'kis_applications', 53, 'Status lama: Pending', 'Status diubah ke Approved', 2, '2024-12-26 02:01:00', '2025-12-13 16:31:34'),
(1714, 'INSERT', 'club_dues', 135, NULL, 'Iuran klub dibayar', 35, '2024-04-24 01:59:00', '2025-12-13 16:31:34'),
(1715, 'UPDATE', 'kis_applications', 131, 'Status lama: Pending', 'Status diubah ke Rejected', 75, '2024-04-01 21:47:00', '2025-12-13 16:31:34'),
(1716, 'INSERT', 'club_dues', 159, NULL, 'Iuran klub dibayar', 96, '2024-01-23 13:17:00', '2025-12-13 16:31:34'),
(1717, 'INSERT', 'club_dues', 70, NULL, 'Iuran klub dibayar', 74, '2024-04-15 21:56:00', '2025-12-13 16:31:34'),
(1718, 'INSERT', 'club_dues', 168, NULL, 'Iuran klub dibayar', 62, '2024-10-08 11:49:00', '2025-12-13 16:31:34'),
(1719, 'INSERT', 'club_dues', 94, NULL, 'Iuran klub dibayar', 48, '2024-10-17 14:56:00', '2025-12-13 16:31:34'),
(1720, 'INSERT', 'club_dues', 66, NULL, 'Iuran klub dibayar', 13, '2024-06-06 16:30:00', '2025-12-13 16:31:34'),
(1721, 'INSERT', 'club_dues', 180, NULL, 'Iuran klub dibayar', 16, '2024-06-21 05:19:00', '2025-12-13 16:31:34'),
(1722, 'UPDATE', 'events', 200, 'Status lama: Pending', 'Event diupdate', 4, '2024-06-24 17:23:00', '2025-12-13 16:31:34'),
(1723, 'INSERT', 'event_registrations', 63, NULL, 'Peserta didaftarkan ke event', 2, '2024-06-21 11:36:00', '2025-12-13 16:31:34'),
(1724, 'UPDATE', 'kis_applications', 48, 'Status lama: Pending', 'Status diubah ke Rejected', 84, '2024-10-18 03:37:00', '2025-12-13 16:31:34');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_clubs_table', 1),
(2, '0001_01_01_000000_create_users_table', 1),
(3, '0001_01_01_000001_create_cache_table', 1),
(4, '0001_01_01_000002_create_jobs_table', 1),
(5, '0001_01_01_042408_create_events_table', 1),
(6, '0001_01_01_120041_create_kis_categories_table', 1),
(7, '2025_10_03_000000_create_event_registrations_table', 1),
(8, '2025_10_26_060227_create_kis_applications_table', 1),
(9, '2025_10_26_060228_create_kis_licenses_table', 1),
(10, '2025_10_26_060228_create_logs_table', 1),
(11, '2025_10_27_074622_create_club_dues_table', 1),
(12, '2025_10_31_000000_add_biaya_kis_to_kis_categories', 1),
(13, '2025_10_31_041008_create_pembalap_profiles_table', 1),
(14, '2025_10_31_044305_create_database_functions_and_views', 1),
(15, '2025_10_31_044305_create_database_procedures', 1),
(16, '2025_10_31_044305_create_database_triggers', 1),
(17, '2025_10_31_055233_create_sessions_table', 1),
(18, '2025_11_02_153603_add_active_status_and_reason_to_users_table', 1),
(19, '2025_11_09_084852_add_club_id_to_users_table', 1),
(20, '2025_11_10_062150_add_kis_category_id_to_kis_licenses_table', 1),
(21, '2025_11_10_065151_add_details_to_events_table', 1),
(22, '2025_11_10_072430_create_event_kis_category_pivot_table', 1),
(23, '2025_11_10_094212_add_image_banner_url_to_events_table', 1),
(24, '2025_11_10_152306_add_registration_deadline_to_events_table', 1),
(25, '2025_11_13_143022_add_bank_account_info_to_events_table', 1),
(26, '2025_11_16_070028_add_identity_files_to_kis_applications_table', 1),
(27, '2025_11_21_031515_create_settings_table', 1),
(28, '2025_11_21_044202_add_minor_documents_to_kis_applications_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pembalap_profiles`
--

CREATE TABLE `pembalap_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `club_id` bigint(20) UNSIGNED NOT NULL,
  `tempat_lahir` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `no_ktp_sim` varchar(255) DEFAULT NULL,
  `golongan_darah` enum('A','B','AB','O','-') DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembalap_profiles`
--

INSERT INTO `pembalap_profiles` (`id`, `user_id`, `club_id`, `tempat_lahir`, `tanggal_lahir`, `no_ktp_sim`, `golongan_darah`, `phone_number`, `address`, `created_at`, `updated_at`) VALUES
(1, 5, 3, NULL, '1990-01-01', NULL, NULL, NULL, NULL, '2025-12-13 16:31:11', '2025-12-13 16:31:11'),
(2, 6, 3, NULL, '1991-01-01', NULL, NULL, NULL, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(3, 7, 1, NULL, '1992-01-01', NULL, NULL, NULL, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(4, 8, 14, 'Medan', '1999-06-25', '1271000000000001', 'B', '081234500001', 'Jl. Test No. 1, Medan', '2024-03-02 17:00:00', '2025-12-13 16:31:12'),
(5, 9, 7, 'Jakarta', '1993-10-24', '1271000000000002', 'AB', '081234500002', 'Jl. Test No. 2, Medan', '2024-04-11 17:00:00', '2025-12-13 16:31:12'),
(6, 10, 7, 'Pematang Siantar', '1986-02-07', '1271000000000003', 'B', '081234500003', 'Jl. Test No. 3, Medan', '2024-12-21 17:00:00', '2025-12-13 16:31:13'),
(7, 11, 9, 'Bandung', '1997-11-06', '1271000000000004', 'O', '081234500004', 'Jl. Test No. 4, Medan', '2024-07-07 17:00:00', '2025-12-13 16:31:13'),
(8, 12, 14, 'Medan', '2004-06-08', '1271000000000005', 'O', '081234500005', 'Jl. Test No. 5, Medan', '2024-10-13 17:00:00', '2025-12-13 16:31:13'),
(9, 13, 13, 'Medan', '1999-08-16', '1271000000000006', 'AB', '081234500006', 'Jl. Test No. 6, Medan', '2024-05-10 17:00:00', '2025-12-13 16:31:13'),
(10, 14, 13, 'Jakarta', '2001-06-09', '1271000000000007', 'B', '081234500007', 'Jl. Test No. 7, Medan', '2024-05-06 17:00:00', '2025-12-13 16:31:13'),
(11, 15, 4, 'Jakarta', '1988-11-07', '1271000000000008', 'AB', '081234500008', 'Jl. Test No. 8, Medan', '2024-03-12 17:00:00', '2025-12-13 16:31:14'),
(12, 16, 11, 'Bandung', '1986-09-14', '1271000000000009', 'AB', '081234500009', 'Jl. Test No. 9, Medan', '2024-04-13 17:00:00', '2025-12-13 16:31:14'),
(13, 17, 10, 'Pematang Siantar', '1988-09-17', '1271000000000010', 'A', '081234500010', 'Jl. Test No. 10, Medan', '2024-05-19 17:00:00', '2025-12-13 16:31:14'),
(14, 18, 7, 'Medan', '2002-02-23', '1271000000000011', 'AB', '081234500011', 'Jl. Test No. 11, Medan', '2024-08-05 17:00:00', '2025-12-13 16:31:14'),
(15, 19, 10, 'Medan', '1989-01-25', '1271000000000012', 'O', '081234500012', 'Jl. Test No. 12, Medan', '2024-03-04 17:00:00', '2025-12-13 16:31:14'),
(16, 20, 9, 'Medan', '2005-12-20', '1271000000000013', 'A', '081234500013', 'Jl. Test No. 13, Medan', '2024-08-22 17:00:00', '2025-12-13 16:31:15'),
(17, 21, 14, 'Surabaya', '1993-11-25', '1271000000000014', 'A', '081234500014', 'Jl. Test No. 14, Medan', '2024-12-14 17:00:00', '2025-12-13 16:31:15'),
(18, 22, 15, 'Jakarta', '2002-04-23', '1271000000000015', 'A', '081234500015', 'Jl. Test No. 15, Medan', '2024-12-05 17:00:00', '2025-12-13 16:31:15'),
(19, 23, 13, 'Medan', '2005-09-07', '1271000000000016', 'O', '081234500016', 'Jl. Test No. 16, Medan', '2024-12-17 17:00:00', '2025-12-13 16:31:15'),
(20, 24, 15, 'Medan', '1987-02-15', '1271000000000017', 'O', '081234500017', 'Jl. Test No. 17, Medan', '2024-11-09 17:00:00', '2025-12-13 16:31:15'),
(21, 25, 6, 'Pematang Siantar', '1999-11-01', '1271000000000018', 'A', '081234500018', 'Jl. Test No. 18, Medan', '2024-08-26 17:00:00', '2025-12-13 16:31:16'),
(22, 26, 8, 'Bandung', '1992-04-16', '1271000000000019', 'O', '081234500019', 'Jl. Test No. 19, Medan', '2024-09-14 17:00:00', '2025-12-13 16:31:16'),
(23, 27, 3, 'Surabaya', '1997-06-05', '1271000000000020', 'AB', '081234500020', 'Jl. Test No. 20, Medan', '2024-07-15 17:00:00', '2025-12-13 16:31:16'),
(24, 28, 2, 'Medan', '1998-11-25', '1271000000000021', 'O', '081234500021', 'Jl. Test No. 21, Medan', '2024-04-15 17:00:00', '2025-12-13 16:31:16'),
(25, 29, 1, 'Bandung', '1991-09-25', '1271000000000022', 'B', '081234500022', 'Jl. Test No. 22, Medan', '2024-07-09 17:00:00', '2025-12-13 16:31:16'),
(26, 30, 7, 'Jakarta', '1986-05-21', '1271000000000023', 'A', '081234500023', 'Jl. Test No. 23, Medan', '2024-07-14 17:00:00', '2025-12-13 16:31:16'),
(27, 31, 12, 'Bandung', '1998-08-02', '1271000000000024', 'B', '081234500024', 'Jl. Test No. 24, Medan', '2024-03-27 17:00:00', '2025-12-13 16:31:17'),
(28, 32, 7, 'Jakarta', '2000-09-17', '1271000000000025', 'B', '081234500025', 'Jl. Test No. 25, Medan', '2024-11-02 17:00:00', '2025-12-13 16:31:17'),
(29, 33, 2, 'Medan', '1988-09-25', '1271000000000026', 'O', '081234500026', 'Jl. Test No. 26, Medan', '2024-10-05 17:00:00', '2025-12-13 16:31:17'),
(30, 34, 7, 'Surabaya', '1995-08-17', '1271000000000027', 'O', '081234500027', 'Jl. Test No. 27, Medan', '2024-04-11 17:00:00', '2025-12-13 16:31:17'),
(31, 35, 11, 'Jakarta', '2000-09-14', '1271000000000028', 'A', '081234500028', 'Jl. Test No. 28, Medan', '2024-05-11 17:00:00', '2025-12-13 16:31:17'),
(32, 36, 15, 'Jakarta', '1999-10-11', '1271000000000029', 'B', '081234500029', 'Jl. Test No. 29, Medan', '2024-01-25 17:00:00', '2025-12-13 16:31:18'),
(33, 37, 1, 'Surabaya', '1986-12-27', '1271000000000030', 'A', '081234500030', 'Jl. Test No. 30, Medan', '2024-07-23 17:00:00', '2025-12-13 16:31:18'),
(34, 38, 6, 'Pematang Siantar', '2003-06-10', '1271000000000031', 'AB', '081234500031', 'Jl. Test No. 31, Medan', '2024-11-25 17:00:00', '2025-12-13 16:31:18'),
(35, 39, 14, 'Bandung', '1993-09-10', '1271000000000032', 'AB', '081234500032', 'Jl. Test No. 32, Medan', '2024-12-25 17:00:00', '2025-12-13 16:31:18'),
(36, 40, 13, 'Surabaya', '2001-03-02', '1271000000000033', 'B', '081234500033', 'Jl. Test No. 33, Medan', '2024-04-30 17:00:00', '2025-12-13 16:31:18'),
(37, 41, 5, 'Surabaya', '2005-05-15', '1271000000000034', 'B', '081234500034', 'Jl. Test No. 34, Medan', '2024-02-12 17:00:00', '2025-12-13 16:31:19'),
(38, 42, 1, 'Medan', '1985-12-10', '1271000000000035', 'AB', '081234500035', 'Jl. Test No. 35, Medan', '2024-12-24 17:00:00', '2025-12-13 16:31:19'),
(39, 43, 2, 'Medan', '1989-04-14', '1271000000000036', 'A', '081234500036', 'Jl. Test No. 36, Medan', '2024-08-07 17:00:00', '2025-12-13 16:31:19'),
(40, 44, 3, 'Surabaya', '1994-10-02', '1271000000000037', 'O', '081234500037', 'Jl. Test No. 37, Medan', '2024-04-27 17:00:00', '2025-12-13 16:31:19'),
(41, 45, 5, 'Bandung', '2005-07-25', '1271000000000038', 'O', '081234500038', 'Jl. Test No. 38, Medan', '2024-04-23 17:00:00', '2025-12-13 16:31:19'),
(42, 46, 12, 'Medan', '1998-11-18', '1271000000000039', 'B', '081234500039', 'Jl. Test No. 39, Medan', '2024-10-19 17:00:00', '2025-12-13 16:31:19'),
(43, 47, 13, 'Pematang Siantar', '2005-12-27', '1271000000000040', 'B', '081234500040', 'Jl. Test No. 40, Medan', '2024-03-11 17:00:00', '2025-12-13 16:31:20'),
(44, 48, 12, 'Surabaya', '1992-07-20', '1271000000000041', 'O', '081234500041', 'Jl. Test No. 41, Medan', '2024-02-27 17:00:00', '2025-12-13 16:31:20'),
(45, 49, 2, 'Jakarta', '2004-02-07', '1271000000000042', 'AB', '081234500042', 'Jl. Test No. 42, Medan', '2024-07-14 17:00:00', '2025-12-13 16:31:20'),
(46, 50, 6, 'Bandung', '1986-08-11', '1271000000000043', 'AB', '081234500043', 'Jl. Test No. 43, Medan', '2024-09-03 17:00:00', '2025-12-13 16:31:20'),
(47, 51, 6, 'Medan', '2002-04-14', '1271000000000044', 'A', '081234500044', 'Jl. Test No. 44, Medan', '2024-06-07 17:00:00', '2025-12-13 16:31:20'),
(48, 52, 8, 'Surabaya', '1996-06-20', '1271000000000045', 'O', '081234500045', 'Jl. Test No. 45, Medan', '2024-06-17 17:00:00', '2025-12-13 16:31:21'),
(49, 53, 13, 'Bandung', '1988-05-17', '1271000000000046', 'O', '081234500046', 'Jl. Test No. 46, Medan', '2024-03-07 17:00:00', '2025-12-13 16:31:21'),
(50, 54, 12, 'Medan', '1985-07-25', '1271000000000047', 'AB', '081234500047', 'Jl. Test No. 47, Medan', '2024-06-02 17:00:00', '2025-12-13 16:31:21'),
(51, 55, 4, 'Surabaya', '2000-01-24', '1271000000000048', 'B', '081234500048', 'Jl. Test No. 48, Medan', '2024-05-25 17:00:00', '2025-12-13 16:31:21'),
(52, 56, 15, 'Pematang Siantar', '1998-04-18', '1271000000000049', 'AB', '081234500049', 'Jl. Test No. 49, Medan', '2024-03-19 17:00:00', '2025-12-13 16:31:21'),
(53, 57, 4, 'Bandung', '1996-03-19', '1271000000000050', 'B', '081234500050', 'Jl. Test No. 50, Medan', '2024-08-20 17:00:00', '2025-12-13 16:31:22'),
(54, 58, 9, 'Medan', '1999-05-09', '1271000000000051', 'O', '081234500051', 'Jl. Test No. 51, Medan', '2024-03-13 17:00:00', '2025-12-13 16:31:22'),
(55, 59, 1, 'Surabaya', '1997-06-15', '1271000000000052', 'A', '081234500052', 'Jl. Test No. 52, Medan', '2024-10-07 17:00:00', '2025-12-13 16:31:22'),
(56, 60, 8, 'Jakarta', '1996-07-28', '1271000000000053', 'AB', '081234500053', 'Jl. Test No. 53, Medan', '2024-07-17 17:00:00', '2025-12-13 16:31:22'),
(57, 61, 1, 'Pematang Siantar', '1996-08-19', '1271000000000054', 'O', '081234500054', 'Jl. Test No. 54, Medan', '2023-12-31 17:00:00', '2025-12-13 16:31:22'),
(58, 62, 7, 'Pematang Siantar', '2005-08-02', '1271000000000055', 'A', '081234500055', 'Jl. Test No. 55, Medan', '2024-12-10 17:00:00', '2025-12-13 16:31:22'),
(59, 63, 13, 'Jakarta', '1996-02-10', '1271000000000056', 'AB', '081234500056', 'Jl. Test No. 56, Medan', '2024-08-10 17:00:00', '2025-12-13 16:31:23'),
(60, 64, 11, 'Bandung', '2002-10-27', '1271000000000057', 'A', '081234500057', 'Jl. Test No. 57, Medan', '2024-02-01 17:00:00', '2025-12-13 16:31:23'),
(61, 65, 4, 'Jakarta', '2001-03-26', '1271000000000058', 'B', '081234500058', 'Jl. Test No. 58, Medan', '2024-01-01 17:00:00', '2025-12-13 16:31:23'),
(62, 66, 8, 'Surabaya', '1994-04-24', '1271000000000059', 'AB', '081234500059', 'Jl. Test No. 59, Medan', '2024-09-07 17:00:00', '2025-12-13 16:31:23'),
(63, 67, 15, 'Bandung', '1999-07-12', '1271000000000060', 'B', '081234500060', 'Jl. Test No. 60, Medan', '2024-01-26 17:00:00', '2025-12-13 16:31:23'),
(64, 68, 5, 'Medan', '1986-05-10', '1271000000000061', 'B', '081234500061', 'Jl. Test No. 61, Medan', '2024-04-15 17:00:00', '2025-12-13 16:31:24'),
(65, 69, 4, 'Surabaya', '1998-12-25', '1271000000000062', 'B', '081234500062', 'Jl. Test No. 62, Medan', '2024-05-23 17:00:00', '2025-12-13 16:31:24'),
(66, 70, 2, 'Medan', '1994-09-19', '1271000000000063', 'B', '081234500063', 'Jl. Test No. 63, Medan', '2024-07-12 17:00:00', '2025-12-13 16:31:24'),
(67, 71, 11, 'Medan', '2002-10-15', '1271000000000064', 'B', '081234500064', 'Jl. Test No. 64, Medan', '2024-11-22 17:00:00', '2025-12-13 16:31:24'),
(68, 72, 14, 'Pematang Siantar', '1989-11-13', '1271000000000065', 'O', '081234500065', 'Jl. Test No. 65, Medan', '2024-02-11 17:00:00', '2025-12-13 16:31:24'),
(69, 73, 1, 'Pematang Siantar', '1994-01-11', '1271000000000066', 'O', '081234500066', 'Jl. Test No. 66, Medan', '2024-12-22 17:00:00', '2025-12-13 16:31:25'),
(70, 74, 4, 'Medan', '1999-05-14', '1271000000000067', 'B', '081234500067', 'Jl. Test No. 67, Medan', '2024-10-15 17:00:00', '2025-12-13 16:31:25'),
(71, 75, 7, 'Pematang Siantar', '1994-04-12', '1271000000000068', 'A', '081234500068', 'Jl. Test No. 68, Medan', '2024-02-15 17:00:00', '2025-12-13 16:31:25'),
(72, 76, 9, 'Bandung', '2001-08-04', '1271000000000069', 'AB', '081234500069', 'Jl. Test No. 69, Medan', '2024-05-02 17:00:00', '2025-12-13 16:31:25'),
(73, 77, 13, 'Surabaya', '1999-01-03', '1271000000000070', 'A', '081234500070', 'Jl. Test No. 70, Medan', '2024-12-08 17:00:00', '2025-12-13 16:31:25'),
(74, 78, 6, 'Bandung', '2002-04-19', '1271000000000071', 'B', '081234500071', 'Jl. Test No. 71, Medan', '2024-01-11 17:00:00', '2025-12-13 16:31:25'),
(75, 79, 1, 'Surabaya', '1985-12-17', '1271000000000072', 'A', '081234500072', 'Jl. Test No. 72, Medan', '2024-08-08 17:00:00', '2025-12-13 16:31:26'),
(76, 80, 9, 'Surabaya', '2001-12-20', '1271000000000073', 'A', '081234500073', 'Jl. Test No. 73, Medan', '2024-09-19 17:00:00', '2025-12-13 16:31:26'),
(77, 81, 1, 'Medan', '2000-01-19', '1271000000000074', 'A', '081234500074', 'Jl. Test No. 74, Medan', '2024-06-10 17:00:00', '2025-12-13 16:31:26'),
(78, 82, 12, 'Surabaya', '1988-01-02', '1271000000000075', 'O', '081234500075', 'Jl. Test No. 75, Medan', '2024-01-06 17:00:00', '2025-12-13 16:31:26'),
(79, 83, 8, 'Bandung', '1992-10-21', '1271000000000076', 'B', '081234500076', 'Jl. Test No. 76, Medan', '2024-04-06 17:00:00', '2025-12-13 16:31:26'),
(80, 84, 12, 'Jakarta', '1991-12-18', '1271000000000077', 'A', '081234500077', 'Jl. Test No. 77, Medan', '2024-05-10 17:00:00', '2025-12-13 16:31:27'),
(81, 85, 14, 'Bandung', '1991-09-23', '1271000000000078', 'A', '081234500078', 'Jl. Test No. 78, Medan', '2024-11-17 17:00:00', '2025-12-13 16:31:27'),
(82, 86, 13, 'Pematang Siantar', '2001-11-22', '1271000000000079', 'B', '081234500079', 'Jl. Test No. 79, Medan', '2024-08-14 17:00:00', '2025-12-13 16:31:27'),
(83, 87, 4, 'Surabaya', '2005-04-16', '1271000000000080', 'A', '081234500080', 'Jl. Test No. 80, Medan', '2024-07-01 17:00:00', '2025-12-13 16:31:27'),
(84, 88, 15, 'Pematang Siantar', '2002-07-03', '1271000000000081', 'A', '081234500081', 'Jl. Test No. 81, Medan', '2024-06-20 17:00:00', '2025-12-13 16:31:27'),
(85, 89, 7, 'Pematang Siantar', '1995-01-21', '1271000000000082', 'A', '081234500082', 'Jl. Test No. 82, Medan', '2024-10-09 17:00:00', '2025-12-13 16:31:28'),
(86, 90, 13, 'Medan', '1999-02-02', '1271000000000083', 'A', '081234500083', 'Jl. Test No. 83, Medan', '2024-03-15 17:00:00', '2025-12-13 16:31:28'),
(87, 91, 1, 'Jakarta', '1985-09-23', '1271000000000084', 'B', '081234500084', 'Jl. Test No. 84, Medan', '2024-12-24 17:00:00', '2025-12-13 16:31:28'),
(88, 92, 14, 'Jakarta', '1995-02-09', '1271000000000085', 'O', '081234500085', 'Jl. Test No. 85, Medan', '2024-08-03 17:00:00', '2025-12-13 16:31:28'),
(89, 93, 13, 'Surabaya', '1992-02-05', '1271000000000086', 'AB', '081234500086', 'Jl. Test No. 86, Medan', '2024-07-13 17:00:00', '2025-12-13 16:31:28'),
(90, 94, 14, 'Jakarta', '1987-09-17', '1271000000000087', 'O', '081234500087', 'Jl. Test No. 87, Medan', '2024-02-09 17:00:00', '2025-12-13 16:31:28'),
(91, 95, 10, 'Pematang Siantar', '1988-03-22', '1271000000000088', 'A', '081234500088', 'Jl. Test No. 88, Medan', '2024-08-13 17:00:00', '2025-12-13 16:31:29'),
(92, 96, 13, 'Pematang Siantar', '1990-09-21', '1271000000000089', 'B', '081234500089', 'Jl. Test No. 89, Medan', '2024-07-08 17:00:00', '2025-12-13 16:31:29'),
(93, 97, 2, 'Jakarta', '1991-11-28', '1271000000000090', 'B', '081234500090', 'Jl. Test No. 90, Medan', '2024-12-24 17:00:00', '2025-12-13 16:31:29'),
(94, 98, 12, 'Bandung', '2003-10-13', '1271000000000091', 'A', '081234500091', 'Jl. Test No. 91, Medan', '2024-09-26 17:00:00', '2025-12-13 16:31:29'),
(95, 99, 13, 'Surabaya', '1985-03-05', '1271000000000092', 'AB', '081234500092', 'Jl. Test No. 92, Medan', '2024-04-14 17:00:00', '2025-12-13 16:31:29'),
(96, 100, 1, 'Surabaya', '1993-10-08', '1271000000000093', 'O', '081234500093', 'Jl. Test No. 93, Medan', '2024-05-08 17:00:00', '2025-12-13 16:31:30'),
(97, 101, 11, 'Medan', '1990-12-13', '1271000000000094', 'O', '081234500094', 'Jl. Test No. 94, Medan', '2024-10-19 17:00:00', '2025-12-13 16:31:30'),
(98, 102, 3, 'Pematang Siantar', '1995-01-04', '1271000000000095', 'B', '081234500095', 'Jl. Test No. 95, Medan', '2024-07-02 17:00:00', '2025-12-13 16:31:30'),
(99, 103, 5, 'Pematang Siantar', '1996-04-22', '1271000000000096', 'A', '081234500096', 'Jl. Test No. 96, Medan', '2024-07-18 17:00:00', '2025-12-13 16:31:30'),
(100, 104, 7, 'Surabaya', '2001-09-27', '1271000000000097', 'B', '081234500097', 'Jl. Test No. 97, Medan', '2024-10-14 17:00:00', '2025-12-13 16:31:30'),
(101, 105, 8, 'Bandung', '1999-01-14', '1271000000000098', 'O', '081234500098', 'Jl. Test No. 98, Medan', '2024-09-15 17:00:00', '2025-12-13 16:31:31'),
(102, 106, 14, 'Bandung', '1985-10-26', '1271000000000099', 'A', '081234500099', 'Jl. Test No. 99, Medan', '2024-04-23 17:00:00', '2025-12-13 16:31:31'),
(103, 107, 4, 'Medan', '1990-03-28', '1271000000000100', 'AB', '081234500100', 'Jl. Test No. 100, Medan', '2024-12-26 17:00:00', '2025-12-13 16:31:31');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'kis_bank_account', 'Bank BCA\nNo. Rek: 888-123-4567\nA/n IMI Sumatera Utara', 'Informasi rekening bank untuk pembayaran pendaftaran KIS.', '2025-12-13 16:31:12', '2025-12-13 16:31:12'),
(2, 'kis_registration_fee', '150000', 'Biaya pendaftaran pembuatan KIS (dalam Rupiah).', '2025-12-13 16:31:12', '2025-12-13 16:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pembalap','pengurus_imi','pimpinan_imi','penyelenggara_event','super_admin') NOT NULL DEFAULT 'pembalap',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deactivation_reason` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `club_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `deactivation_reason`, `remember_token`, `created_at`, `updated_at`, `club_id`) VALUES
(1, 'Super Admin', 'superadmin@imi.com', NULL, '$2y$12$J28vOnYbKylgTkygRzBvGuncondYbUrNQcAKYqBCWXsR/7bRpqYWy', 'super_admin', 1, NULL, NULL, '2025-12-13 16:31:10', '2025-12-13 16:31:10', NULL),
(2, 'Pengurus IMI', 'pengurus@imi.com', NULL, '$2y$12$bTSvnyTFfGZTLlOjEyXj0edtIZRf2JW0MRNsYpK4pcGK.75Z0GXie', 'pengurus_imi', 1, NULL, NULL, '2025-12-13 16:31:11', '2025-12-13 16:31:11', NULL),
(3, 'Pimpinan IMI', 'pimpinan@imi.com', NULL, '$2y$12$8rr3DUr7mrrNtlBGNL6Sdu3BXzWqoRuhgRfckYjSwrWui7AlWpqYq', 'pimpinan_imi', 1, NULL, NULL, '2025-12-13 16:31:11', '2025-12-13 16:31:11', NULL),
(4, 'Penyelenggara Speeder', 'penyelenggara@imi.com', NULL, '$2y$12$4oDXAXJUDVuNYlUgaXeinOq6bFgWUjE9WDInQEOYwxJj7YYIqR2Y.', 'penyelenggara_event', 1, NULL, NULL, '2025-12-13 16:31:11', '2025-12-13 16:31:11', 2),
(5, 'Pembalap Lunas', 'lunas@imi.com', NULL, '$2y$12$ab0dvb6HE8p901zQRIYcoOiTVln3NM2m1h8VZr3hj7od7jLT39.Je', 'pembalap', 1, NULL, NULL, '2025-12-13 16:31:11', '2025-12-13 16:31:11', NULL),
(6, 'Pembalap Pending', 'pending@imi.com', NULL, '$2y$12$Ued8968Y258tQpndtATqieEPSibeEsjVC4iKKicIMyJwnpqbWQUmm', 'pembalap', 1, NULL, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12', NULL),
(7, 'Pembalap Ditolak', 'ditolak@imi.com', NULL, '$2y$12$.ggqz/eQSGrK4INq.ojp6ualFZ5B2uYZcp47iYjsSHPEoQgjeEQZm', 'pembalap', 1, NULL, NULL, '2025-12-13 16:31:12', '2025-12-13 16:31:12', NULL),
(8, 'Pembalap Historical 1', 'pembalap2024_1@test.com', NULL, '$2y$12$67Gjb8xj1fjNP1hfSg863./5KXgg.xgVD0uWsBEgGDZ6bReNUwjJi', 'pembalap', 1, NULL, NULL, '2024-03-02 17:00:00', '2024-06-18 17:00:00', NULL),
(9, 'Pembalap Historical 2', 'pembalap2024_2@test.com', NULL, '$2y$12$9ygY039TGGh/n3KRgfOqmOQg94GltKYwEskOjSEts6zvjx.lXjYH6', 'pembalap', 1, NULL, NULL, '2024-04-11 17:00:00', '2024-03-15 17:00:00', NULL),
(10, 'Pembalap Historical 3', 'pembalap2024_3@test.com', NULL, '$2y$12$pT7.5pJOE3nbcoh2VFBnoeUIpTmPbUYVHrmsxCnQCFcNdenmpJmJq', 'pembalap', 1, NULL, NULL, '2024-12-21 17:00:00', '2024-12-05 17:00:00', NULL),
(11, 'Pembalap Historical 4', 'pembalap2024_4@test.com', NULL, '$2y$12$rvO01cQbczpot78mO0g82upZfPR.eCSeVIwiWettsNKa1uttbSM/u', 'pembalap', 1, NULL, NULL, '2024-07-07 17:00:00', '2024-09-11 17:00:00', NULL),
(12, 'Pembalap Historical 5', 'pembalap2024_5@test.com', NULL, '$2y$12$bgc0N3s.SwkfYdL97DYv8eQJ3NmzJ7qXg7vGkCIHDe4dG7K9ovpZG', 'pembalap', 1, NULL, NULL, '2024-10-13 17:00:00', '2024-03-08 17:00:00', NULL),
(13, 'Pembalap Historical 6', 'pembalap2024_6@test.com', NULL, '$2y$12$wTs31miGYZ45yDoy1tkGO.YSYp2/CpeSb32BsIc91MEPx/nBaUQci', 'pembalap', 1, NULL, NULL, '2024-05-10 17:00:00', '2024-03-09 17:00:00', NULL),
(14, 'Pembalap Historical 7', 'pembalap2024_7@test.com', NULL, '$2y$12$/LpCpKaBbbc7t8K1GHKVIeaJskfNv.2ewGGKPlSocBlNYP4qF6Lx.', 'pembalap', 1, NULL, NULL, '2024-05-06 17:00:00', '2024-01-01 17:00:00', NULL),
(15, 'Pembalap Historical 8', 'pembalap2024_8@test.com', NULL, '$2y$12$PiuvjGqTLW1dEp0y8tjdk.YOexh7GpJ4m/63tEgDJlldfDuad881O', 'pembalap', 1, NULL, NULL, '2024-03-12 17:00:00', '2024-02-09 17:00:00', NULL),
(16, 'Pembalap Historical 9', 'pembalap2024_9@test.com', NULL, '$2y$12$IxnVCTO3/u2e5CvcNIDwQuoEEqtYDkFFn85lTRfeCBlDcYk3q16/y', 'pembalap', 1, NULL, NULL, '2024-04-13 17:00:00', '2024-11-06 17:00:00', NULL),
(17, 'Pembalap Historical 10', 'pembalap2024_10@test.com', NULL, '$2y$12$EDUe1etczOPneCBvbLPPpOv.3rE1cVFdIOrD/nKlFr.sE1tP5NLEm', 'pembalap', 1, NULL, NULL, '2024-05-19 17:00:00', '2024-02-14 17:00:00', NULL),
(18, 'Pembalap Historical 11', 'pembalap2024_11@test.com', NULL, '$2y$12$Dmn1qV9o9I1Yj0PrVWWD0.ZfAEc4.U48RxObEmVQa5yYFmvlds5Wu', 'pembalap', 1, NULL, NULL, '2024-08-05 17:00:00', '2024-07-16 17:00:00', NULL),
(19, 'Pembalap Historical 12', 'pembalap2024_12@test.com', NULL, '$2y$12$lVLMsV6HIyvPufEc9EaYIuGVv0MTRlispXcfBbHeXN3naQDFO1j/q', 'pembalap', 1, NULL, NULL, '2024-03-04 17:00:00', '2024-09-26 17:00:00', NULL),
(20, 'Pembalap Historical 13', 'pembalap2024_13@test.com', NULL, '$2y$12$kF72AWSPdsLznj1Er2vHs.inj4RmerkpG6kfTqJG5YEluxvG9ZbnK', 'pembalap', 1, NULL, NULL, '2024-08-22 17:00:00', '2024-06-26 17:00:00', NULL),
(21, 'Pembalap Historical 14', 'pembalap2024_14@test.com', NULL, '$2y$12$ATrjI2BypFi666LhD3P3Y.Fubkp1uY4n9g940c6T0XK5BoG/IkPn.', 'pembalap', 1, NULL, NULL, '2024-12-14 17:00:00', '2024-12-03 17:00:00', NULL),
(22, 'Pembalap Historical 15', 'pembalap2024_15@test.com', NULL, '$2y$12$FxkvN.YvoOm8GMHh2LX9Rus0nA8zG3gDhif0rYz8Ptb2.olid0eoO', 'pembalap', 1, NULL, NULL, '2024-12-05 17:00:00', '2024-08-15 17:00:00', NULL),
(23, 'Pembalap Historical 16', 'pembalap2024_16@test.com', NULL, '$2y$12$1Txz.3EYOpC3LIqvcCikEOTGPrRKQ0qBYVwdtRz7K.mPrtGM8ir7W', 'pembalap', 1, NULL, NULL, '2024-12-17 17:00:00', '2024-04-17 17:00:00', NULL),
(24, 'Pembalap Historical 17', 'pembalap2024_17@test.com', NULL, '$2y$12$hxKtQvhPpX3HIeKE8wkzJukirGmF012XK0RN.os1Nak9/4oKgD4Zy', 'pembalap', 1, NULL, NULL, '2024-11-09 17:00:00', '2024-12-20 17:00:00', NULL),
(25, 'Pembalap Historical 18', 'pembalap2024_18@test.com', NULL, '$2y$12$g7DMeomscMjuhcdTC8LQQ.hBQBQ/hn86XJ8H9FYLfCWRD1vR5660K', 'pembalap', 1, NULL, NULL, '2024-08-26 17:00:00', '2024-03-16 17:00:00', NULL),
(26, 'Pembalap Historical 19', 'pembalap2024_19@test.com', NULL, '$2y$12$9.vYoZihov4C/rc6.dCck.1JH6zEn5HdAUWHJ9k0orQhZg2/60nwu', 'pembalap', 1, NULL, NULL, '2024-09-14 17:00:00', '2024-12-27 17:00:00', NULL),
(27, 'Pembalap Historical 20', 'pembalap2024_20@test.com', NULL, '$2y$12$rHLLDsosdVKaB4LLUtn7CeLNMOyHq3fnolvo52bgPMxow.ktKxTVa', 'pembalap', 1, NULL, NULL, '2024-07-15 17:00:00', '2024-03-09 17:00:00', NULL),
(28, 'Pembalap Historical 21', 'pembalap2024_21@test.com', NULL, '$2y$12$C4/LBlaoL3D0CerdjEoeduYFH5VC6TCgFrEslpOUuAPwUe8MCK/em', 'pembalap', 1, NULL, NULL, '2024-04-15 17:00:00', '2024-04-23 17:00:00', NULL),
(29, 'Pembalap Historical 22', 'pembalap2024_22@test.com', NULL, '$2y$12$guqgjeyf5P3m/6FuGKHKkuPhwdBRnkLJm.o0LlwO1PEHHOSEgqbA.', 'pembalap', 1, NULL, NULL, '2024-07-09 17:00:00', '2024-03-07 17:00:00', NULL),
(30, 'Pembalap Historical 23', 'pembalap2024_23@test.com', NULL, '$2y$12$yvFtI9w5Jn0xd/Xfqtmjx.1riBll4HIfVFMR02p2n/NoR.jc//GAS', 'pembalap', 1, NULL, NULL, '2024-07-14 17:00:00', '2024-03-18 17:00:00', NULL),
(31, 'Pembalap Historical 24', 'pembalap2024_24@test.com', NULL, '$2y$12$mgriNpDV0AiGVx11r9AUH.lSqKkELHHm/U/mgNUxPxmBv8v9.Rd1O', 'pembalap', 1, NULL, NULL, '2024-03-27 17:00:00', '2024-07-14 17:00:00', NULL),
(32, 'Pembalap Historical 25', 'pembalap2024_25@test.com', NULL, '$2y$12$pO6UPCrxtVh0rLKNvfsjDOvfzRTRkCCawYdAYWF78XZUjSpFUu2Ia', 'pembalap', 1, NULL, NULL, '2024-11-02 17:00:00', '2024-04-26 17:00:00', NULL),
(33, 'Pembalap Historical 26', 'pembalap2024_26@test.com', NULL, '$2y$12$qPpg1tW00dp0z2JdKbgt4Oidr62NMpNbegC9b/1xdfzHKehfYn25a', 'pembalap', 1, NULL, NULL, '2024-10-05 17:00:00', '2024-05-04 17:00:00', NULL),
(34, 'Pembalap Historical 27', 'pembalap2024_27@test.com', NULL, '$2y$12$hWLI9OvIWKEueRUAMzRzz.OLmIdNM7eLW235VGZv5VyTJQ141PHKy', 'pembalap', 1, NULL, NULL, '2024-04-11 17:00:00', '2024-05-21 17:00:00', NULL),
(35, 'Pembalap Historical 28', 'pembalap2024_28@test.com', NULL, '$2y$12$/vAgABH1UfabTxvwqWpXVeUHOJM1r7v4qGuv/msPye/LUJEsWZtRO', 'pembalap', 1, NULL, NULL, '2024-05-11 17:00:00', '2024-03-26 17:00:00', NULL),
(36, 'Pembalap Historical 29', 'pembalap2024_29@test.com', NULL, '$2y$12$EtWwt5JXaYePfyuhOF0G1O/cjTJVEPDfgik5/9RBJFFm9lbvf3L9q', 'pembalap', 1, NULL, NULL, '2024-01-25 17:00:00', '2024-06-18 17:00:00', NULL),
(37, 'Pembalap Historical 30', 'pembalap2024_30@test.com', NULL, '$2y$12$.lPbqYX5zmbUaCmWpbcBfOBClFtJfA9m8Quuk4XsJqi0K4KjxGgsO', 'pembalap', 1, NULL, NULL, '2024-07-23 17:00:00', '2024-07-13 17:00:00', NULL),
(38, 'Pembalap Historical 31', 'pembalap2024_31@test.com', NULL, '$2y$12$2sywx6KMHSuHk.eBaYDKsOd/vnaW.N.b/KLUiyhF5rOomgGZu20l2', 'pembalap', 1, NULL, NULL, '2024-11-25 17:00:00', '2024-07-05 17:00:00', NULL),
(39, 'Pembalap Historical 32', 'pembalap2024_32@test.com', NULL, '$2y$12$6tAYz8CsOzbcdiBIn28WRO70aIMojYaJ.7hUm4MiahvhFwsWdbwMq', 'pembalap', 1, NULL, NULL, '2024-12-25 17:00:00', '2024-11-10 17:00:00', NULL),
(40, 'Pembalap Historical 33', 'pembalap2024_33@test.com', NULL, '$2y$12$ujgVGRTBGZCkl/Z7QQfN0O6QnSmA6fxZVrlLPzeucxeI2OR9jrsKG', 'pembalap', 1, NULL, NULL, '2024-04-30 17:00:00', '2024-04-06 17:00:00', NULL),
(41, 'Pembalap Historical 34', 'pembalap2024_34@test.com', NULL, '$2y$12$fp/qfUwcO8esxugps0N1KuwQPgJpWAIYNOsnL0HOxHtmV4om53lGO', 'pembalap', 1, NULL, NULL, '2024-02-12 17:00:00', '2024-01-31 17:00:00', NULL),
(42, 'Pembalap Historical 35', 'pembalap2024_35@test.com', NULL, '$2y$12$XXdo7OwFWWQs9K.yJ/CPqeVmno3TK8994X2LHh6ktsi8t/FDDgpD.', 'pembalap', 1, NULL, NULL, '2024-12-24 17:00:00', '2024-10-22 17:00:00', NULL),
(43, 'Pembalap Historical 36', 'pembalap2024_36@test.com', NULL, '$2y$12$iTiCbKBW8OuP9uhwNBC.1uBIVmlsJyQuM6/.j.2GGYSXquMDsMMmK', 'pembalap', 1, NULL, NULL, '2024-08-07 17:00:00', '2024-11-04 17:00:00', NULL),
(44, 'Pembalap Historical 37', 'pembalap2024_37@test.com', NULL, '$2y$12$GZoglWYJ/Z2QRiqPCmguxeQceGlQRyDv6gbXXWvWqWhSdPzVlI0Q2', 'pembalap', 1, NULL, NULL, '2024-04-27 17:00:00', '2024-03-23 17:00:00', NULL),
(45, 'Pembalap Historical 38', 'pembalap2024_38@test.com', NULL, '$2y$12$OpL6wp3MZ/crwhjftrRMRO8GPygmA0/TGKRIgeabgbYjWhYeR/WGG', 'pembalap', 1, NULL, NULL, '2024-04-23 17:00:00', '2024-12-19 17:00:00', NULL),
(46, 'Pembalap Historical 39', 'pembalap2024_39@test.com', NULL, '$2y$12$qE6NZf2siCPNYa.vIx5Wdehio629dEVFy9VCHjznT1qp8RDpkSZ22', 'pembalap', 1, NULL, NULL, '2024-10-19 17:00:00', '2024-11-15 17:00:00', NULL),
(47, 'Pembalap Historical 40', 'pembalap2024_40@test.com', NULL, '$2y$12$kmvlPDgcXkhj1ZCqXWnzfunkV2xbV564Pf.3emy5n0HyFm/WZmnE2', 'pembalap', 1, NULL, NULL, '2024-03-11 17:00:00', '2024-02-26 17:00:00', NULL),
(48, 'Pembalap Historical 41', 'pembalap2024_41@test.com', NULL, '$2y$12$Vpf/IsYNcQdale9R0CWsVeFWhwdWa1do9RU5R0j8H6dYJ6nfYOJFO', 'pembalap', 1, NULL, NULL, '2024-02-27 17:00:00', '2024-05-24 17:00:00', NULL),
(49, 'Pembalap Historical 42', 'pembalap2024_42@test.com', NULL, '$2y$12$MLyIOK9OR1JqKDmYu08lw.pwvNA7zN/.NvROHRvF88UPJ1Ktd9SFm', 'pembalap', 1, NULL, NULL, '2024-07-14 17:00:00', '2024-05-17 17:00:00', NULL),
(50, 'Pembalap Historical 43', 'pembalap2024_43@test.com', NULL, '$2y$12$4d6binZbyeAGB1iNE9pOo.zpvn6sxdNxI4.Vs45Qr6jgnkh6yFTxa', 'pembalap', 1, NULL, NULL, '2024-09-03 17:00:00', '2024-04-06 17:00:00', NULL),
(51, 'Pembalap Historical 44', 'pembalap2024_44@test.com', NULL, '$2y$12$.08pRH4y0O6bORHKEIlMUuBBV6Pinih9Zn12q9Yw3.VrndjLIWjEC', 'pembalap', 1, NULL, NULL, '2024-06-07 17:00:00', '2024-06-10 17:00:00', NULL),
(52, 'Pembalap Historical 45', 'pembalap2024_45@test.com', NULL, '$2y$12$vNbyFkBouL/1.7Xs2CYb2uDKiYVYQeZr6G1x7BQP6cpI1us37GbqO', 'pembalap', 1, NULL, NULL, '2024-06-17 17:00:00', '2024-08-12 17:00:00', NULL),
(53, 'Pembalap Historical 46', 'pembalap2024_46@test.com', NULL, '$2y$12$jnN9Ey7.pDBYww7qDiNHBOK6Q7s.a1GW0zIkksc84wMZdw6rkfaam', 'pembalap', 1, NULL, NULL, '2024-03-07 17:00:00', '2024-09-19 17:00:00', NULL),
(54, 'Pembalap Historical 47', 'pembalap2024_47@test.com', NULL, '$2y$12$e3.uhIB8nOBda2F36X4tn.qMNRqJ8PTnXXocahn1reGZIXuIi2CKW', 'pembalap', 1, NULL, NULL, '2024-06-02 17:00:00', '2024-01-04 17:00:00', NULL),
(55, 'Pembalap Historical 48', 'pembalap2024_48@test.com', NULL, '$2y$12$TBWywyUqJNPMiTLe/CD.hOmG4f3YwIarXuYK5vPgs4nP8pN6oWH8K', 'pembalap', 1, NULL, NULL, '2024-05-25 17:00:00', '2024-03-13 17:00:00', NULL),
(56, 'Pembalap Historical 49', 'pembalap2024_49@test.com', NULL, '$2y$12$llujlCHMgT95EPEp081O6ObM1i7616T5tRoUMZp/BB1iRHYoPgZkK', 'pembalap', 1, NULL, NULL, '2024-03-19 17:00:00', '2024-02-11 17:00:00', NULL),
(57, 'Pembalap Historical 50', 'pembalap2024_50@test.com', NULL, '$2y$12$OpJsQ3YE.WJ/zp0/03hd3.3QAhkzRcp.Ajvqy4bt05GanTYo4A5he', 'pembalap', 1, NULL, NULL, '2024-08-20 17:00:00', '2024-03-25 17:00:00', NULL),
(58, 'Pembalap Historical 51', 'pembalap2024_51@test.com', NULL, '$2y$12$EQPxBGYrSkDhQjHibPKyGuGF7s7E5yWjvpOMCmyVtldjinHz44ik.', 'pembalap', 1, NULL, NULL, '2024-03-13 17:00:00', '2024-07-15 17:00:00', NULL),
(59, 'Pembalap Historical 52', 'pembalap2024_52@test.com', NULL, '$2y$12$MjhzSV6n5U6uoIg6nuO6MOWGSHiBPUB4TwzQ4B.gRLxCDXoc6MZcS', 'pembalap', 1, NULL, NULL, '2024-10-07 17:00:00', '2024-05-08 17:00:00', NULL),
(60, 'Pembalap Historical 53', 'pembalap2024_53@test.com', NULL, '$2y$12$5N8H9mL1LdwA2cZpO/GipOSGJLzaB6P.6wFivgC2II42vBlyjUppu', 'pembalap', 1, NULL, NULL, '2024-07-17 17:00:00', '2024-07-10 17:00:00', NULL),
(61, 'Pembalap Historical 54', 'pembalap2024_54@test.com', NULL, '$2y$12$ZxvIT9EUoCqM6diKRUm8pOLj5I3/azys/UL2FxOa7Zxv9J7MNUSvi', 'pembalap', 1, NULL, NULL, '2023-12-31 17:00:00', '2024-04-16 17:00:00', NULL),
(62, 'Pembalap Historical 55', 'pembalap2024_55@test.com', NULL, '$2y$12$mSNTXxLt8WT0mEhySjgDiufkyvBRmlcbAAE3na338VgvbXeRVrxDG', 'pembalap', 1, NULL, NULL, '2024-12-10 17:00:00', '2024-07-16 17:00:00', NULL),
(63, 'Pembalap Historical 56', 'pembalap2024_56@test.com', NULL, '$2y$12$2eWb9gUqyru0jSt4eCcjg.j70FfTo8o9P8opRg.U/Ry83sBAgsC7q', 'pembalap', 1, NULL, NULL, '2024-08-10 17:00:00', '2024-04-11 17:00:00', NULL),
(64, 'Pembalap Historical 57', 'pembalap2024_57@test.com', NULL, '$2y$12$DUW7VZKX0Y4QqeEK4N.CFedB5ajjEhV5O98fYDiAPBq6i.lVRYYDO', 'pembalap', 1, NULL, NULL, '2024-02-01 17:00:00', '2024-08-20 17:00:00', NULL),
(65, 'Pembalap Historical 58', 'pembalap2024_58@test.com', NULL, '$2y$12$oqrWaA41CyUWJH.5JFmu..Zfz30pM3r/ZCA8BMVqesR2CECImzhNG', 'pembalap', 1, NULL, NULL, '2024-01-01 17:00:00', '2024-04-19 17:00:00', NULL),
(66, 'Pembalap Historical 59', 'pembalap2024_59@test.com', NULL, '$2y$12$q/tD5N0iRPT9ehD2BkwgHOddegsepF.oZPBN.UYgnYJCcEOxKPt1.', 'pembalap', 1, NULL, NULL, '2024-09-07 17:00:00', '2024-12-04 17:00:00', NULL),
(67, 'Pembalap Historical 60', 'pembalap2024_60@test.com', NULL, '$2y$12$.HiFSTN7pTGRTCSKYNsCiuhf4BhCNgYwQMq09J.mUnhywCXMHDK1e', 'pembalap', 1, NULL, NULL, '2024-01-26 17:00:00', '2024-10-03 17:00:00', NULL),
(68, 'Pembalap Historical 61', 'pembalap2024_61@test.com', NULL, '$2y$12$aEbQIix1r3OU6QQCnaTwzuSL0.xD5BJxcxyyNzCFif44K5EWLNuSS', 'pembalap', 1, NULL, NULL, '2024-04-15 17:00:00', '2024-05-12 17:00:00', NULL),
(69, 'Pembalap Historical 62', 'pembalap2024_62@test.com', NULL, '$2y$12$YyTQTOzHF3fGbJGTTUsfUulYJJNUJYPPNAHG1x0doy8x.HO793UT6', 'pembalap', 1, NULL, NULL, '2024-05-23 17:00:00', '2024-09-25 17:00:00', NULL),
(70, 'Pembalap Historical 63', 'pembalap2024_63@test.com', NULL, '$2y$12$kWPf.koEsfH7.LBhJdzSJuMbsLmaaUSN2E1qRANK52aN3fV56gyO2', 'pembalap', 1, NULL, NULL, '2024-07-12 17:00:00', '2024-02-21 17:00:00', NULL),
(71, 'Pembalap Historical 64', 'pembalap2024_64@test.com', NULL, '$2y$12$ktCZt7eGA0CBDeyfEq7CMewo5ahHaqQLv3EDA1Dh00lN17C5x9Bbu', 'pembalap', 1, NULL, NULL, '2024-11-22 17:00:00', '2024-10-23 17:00:00', NULL),
(72, 'Pembalap Historical 65', 'pembalap2024_65@test.com', NULL, '$2y$12$uP1GxbN2/YI3306HvYGQIe2xOJM4GAWycGG59YDIg2qZCDnhvGiXC', 'pembalap', 1, NULL, NULL, '2024-02-11 17:00:00', '2024-06-07 17:00:00', NULL),
(73, 'Pembalap Historical 66', 'pembalap2024_66@test.com', NULL, '$2y$12$6iflUMrB5bwXF5FbEJBVuO4HqxDvTniOWpoLv9tlcqFm7KcZzFXXC', 'pembalap', 1, NULL, NULL, '2024-12-22 17:00:00', '2024-02-05 17:00:00', NULL),
(74, 'Pembalap Historical 67', 'pembalap2024_67@test.com', NULL, '$2y$12$c16sIhgS3ZbRnXVGvW6VzOhuO643UsA.QF33IApPIaYlYA9EE4KrS', 'pembalap', 1, NULL, NULL, '2024-10-15 17:00:00', '2024-06-25 17:00:00', NULL),
(75, 'Pembalap Historical 68', 'pembalap2024_68@test.com', NULL, '$2y$12$iRYq4WIUguf8UJFha31GHe2FIAAkSdvRmXVvYigfGxHKVBdaIMgr.', 'pembalap', 1, NULL, NULL, '2024-02-15 17:00:00', '2024-10-07 17:00:00', NULL),
(76, 'Pembalap Historical 69', 'pembalap2024_69@test.com', NULL, '$2y$12$QpM7iAePK6b7dYK8lJySuuBoSozFMr3iEdMhvg7AjSV6L0lEHnk0m', 'pembalap', 1, NULL, NULL, '2024-05-02 17:00:00', '2024-03-05 17:00:00', NULL),
(77, 'Pembalap Historical 70', 'pembalap2024_70@test.com', NULL, '$2y$12$MIBP54HQghvfQuk2uwmgZuE9saENgyVghrHczApeAx8lEBcwkthxi', 'pembalap', 1, NULL, NULL, '2024-12-08 17:00:00', '2024-04-30 17:00:00', NULL),
(78, 'Pembalap Historical 71', 'pembalap2024_71@test.com', NULL, '$2y$12$JQR1x5l219vd3lWNxzPqruXAPkPRux2LlDwD9bL4umTRIJjPsmFSO', 'pembalap', 1, NULL, NULL, '2024-01-11 17:00:00', '2024-04-08 17:00:00', NULL),
(79, 'Pembalap Historical 72', 'pembalap2024_72@test.com', NULL, '$2y$12$L/QXgs1Ta6bk.qM2BKwzkOWzljluWnIDVx2OUC71LCbfrsKv7UKbe', 'pembalap', 1, NULL, NULL, '2024-08-08 17:00:00', '2024-06-15 17:00:00', NULL),
(80, 'Pembalap Historical 73', 'pembalap2024_73@test.com', NULL, '$2y$12$gbPvzC7piHvzPYrdiPnEWuUT7kF3.a.mNpuI5xVONzjVDdvaG8QqG', 'pembalap', 1, NULL, NULL, '2024-09-19 17:00:00', '2024-04-23 17:00:00', NULL),
(81, 'Pembalap Historical 74', 'pembalap2024_74@test.com', NULL, '$2y$12$BeRcIZzlvEC3VziQAvR71O/LUjTN.rFJhEIIqyr6b4qYyCROQ.Gkm', 'pembalap', 1, NULL, NULL, '2024-06-10 17:00:00', '2024-10-16 17:00:00', NULL),
(82, 'Pembalap Historical 75', 'pembalap2024_75@test.com', NULL, '$2y$12$.w0SvfjfQs/CuJ9BsobC2Okudg4KkEJ/YlsE1xRAoBWrNDBW3mVqi', 'pembalap', 1, NULL, NULL, '2024-01-06 17:00:00', '2024-11-12 17:00:00', NULL),
(83, 'Pembalap Historical 76', 'pembalap2024_76@test.com', NULL, '$2y$12$eAPD.lC1zfbwS6PH8SRos.1BzpQm6jCs7HEMEI3d7HPBs5cOawmUS', 'pembalap', 1, NULL, NULL, '2024-04-06 17:00:00', '2024-02-05 17:00:00', NULL),
(84, 'Pembalap Historical 77', 'pembalap2024_77@test.com', NULL, '$2y$12$5NtxqiYYMiU1s/xuunwaLuMthdbztn2pTs8J2vOL0FhMt4tzBEd0S', 'pembalap', 1, NULL, NULL, '2024-05-10 17:00:00', '2024-03-11 17:00:00', NULL),
(85, 'Pembalap Historical 78', 'pembalap2024_78@test.com', NULL, '$2y$12$sMwm4DTnxzPkvKKsATCs2.XCpIKNXSYxBdyWtcXfI2eZIpykKTWRe', 'pembalap', 1, NULL, NULL, '2024-11-17 17:00:00', '2024-08-20 17:00:00', NULL),
(86, 'Pembalap Historical 79', 'pembalap2024_79@test.com', NULL, '$2y$12$QpBXqT/qzpNqa2DgrXQ/Iuyg2JCZDb19twqPbw9MkwLVDDyW/g39O', 'pembalap', 1, NULL, NULL, '2024-08-14 17:00:00', '2024-09-13 17:00:00', NULL),
(87, 'Pembalap Historical 80', 'pembalap2024_80@test.com', NULL, '$2y$12$65ohhZnkDNfkdxi/uLbyfOyepKRS6.4OX7u4rZnsok.XsnAfuQE0O', 'pembalap', 1, NULL, NULL, '2024-07-01 17:00:00', '2024-03-06 17:00:00', NULL),
(88, 'Pembalap Historical 81', 'pembalap2024_81@test.com', NULL, '$2y$12$VI9iD0IvDE84FFob0eCWHuRrUC55UfAEkcVW2xUWUFrHdXB4iWqtO', 'pembalap', 1, NULL, NULL, '2024-06-20 17:00:00', '2024-04-16 17:00:00', NULL),
(89, 'Pembalap Historical 82', 'pembalap2024_82@test.com', NULL, '$2y$12$X0WXjNpXIPEQP918GLtn2.juLOkxhbq7Ht2O3Y.3jpetofb9ZDoFa', 'pembalap', 1, NULL, NULL, '2024-10-09 17:00:00', '2024-11-20 17:00:00', NULL),
(90, 'Pembalap Historical 83', 'pembalap2024_83@test.com', NULL, '$2y$12$c8zZEzt39bHGnfG5J/WLjekQGHYXbNX9lM3fKpnNmodvryenIDl/i', 'pembalap', 1, NULL, NULL, '2024-03-15 17:00:00', '2024-12-06 17:00:00', NULL),
(91, 'Pembalap Historical 84', 'pembalap2024_84@test.com', NULL, '$2y$12$bbXMuHL7DZbdh0EREQAwT.t7q/CKti9abDGeTJCwMTGUpFK0XqA9q', 'pembalap', 1, NULL, NULL, '2024-12-24 17:00:00', '2024-09-06 17:00:00', NULL),
(92, 'Pembalap Historical 85', 'pembalap2024_85@test.com', NULL, '$2y$12$AIGxFTYEu498aO7zY3lGIuHYl98EnHlmH8lO6VSgN2sL0moYI3xdu', 'pembalap', 1, NULL, NULL, '2024-08-03 17:00:00', '2024-08-08 17:00:00', NULL),
(93, 'Pembalap Historical 86', 'pembalap2024_86@test.com', NULL, '$2y$12$ZOAkebCNctdlbPB7nfBRZumw4xUtZdmCzObJhXuRLejxFqdAWR.a2', 'pembalap', 1, NULL, NULL, '2024-07-13 17:00:00', '2024-07-08 17:00:00', NULL),
(94, 'Pembalap Historical 87', 'pembalap2024_87@test.com', NULL, '$2y$12$i6R2H/GLEc3ULxjXTKVrr.RSIugWTAsFm/q1sxODQ1nfXE3I46zyy', 'pembalap', 1, NULL, NULL, '2024-02-09 17:00:00', '2024-02-15 17:00:00', NULL),
(95, 'Pembalap Historical 88', 'pembalap2024_88@test.com', NULL, '$2y$12$s4PmOiNLHA4lkqeloc/M/uJmOk.QHV2DkxSAmPKKKnhk7LtheK1dC', 'pembalap', 1, NULL, NULL, '2024-08-13 17:00:00', '2024-07-08 17:00:00', NULL),
(96, 'Pembalap Historical 89', 'pembalap2024_89@test.com', NULL, '$2y$12$Gy/W3ysIfTVXW8tNI4.YtewK3xSIAUj9GRV38pI1mB6JUP9DzNcW.', 'pembalap', 1, NULL, NULL, '2024-07-08 17:00:00', '2024-07-20 17:00:00', NULL),
(97, 'Pembalap Historical 90', 'pembalap2024_90@test.com', NULL, '$2y$12$kXZiyUINzeuZG9PYabJbBeDnFcj6QuB3PDVoJ4u93VCP/tzFGxXQy', 'pembalap', 1, NULL, NULL, '2024-12-24 17:00:00', '2024-11-30 17:00:00', NULL),
(98, 'Pembalap Historical 91', 'pembalap2024_91@test.com', NULL, '$2y$12$AvIjD5IWLvMn2n2NZutf9.t4VEppL75gUfHbcED8EUZY.emGltnfW', 'pembalap', 1, NULL, NULL, '2024-09-26 17:00:00', '2024-12-05 17:00:00', NULL),
(99, 'Pembalap Historical 92', 'pembalap2024_92@test.com', NULL, '$2y$12$cZa7nkhHZVLo8k0c0k.wV.6Whd1sc8PBtsNmxi33qBA0Gvwu3p5He', 'pembalap', 1, NULL, NULL, '2024-04-14 17:00:00', '2024-03-02 17:00:00', NULL),
(100, 'Pembalap Historical 93', 'pembalap2024_93@test.com', NULL, '$2y$12$mZIe9A/AmK/lIKYKXhUzl.HcsXtSRBk5.URezJyOiW2Sq5EWYkzAW', 'pembalap', 1, NULL, NULL, '2024-05-08 17:00:00', '2024-11-03 17:00:00', NULL),
(101, 'Pembalap Historical 94', 'pembalap2024_94@test.com', NULL, '$2y$12$81oS4h.r9A1CIuMzHqzvL.TuXfwi5cz6o4trXMyePPt7zJwkL0Ccu', 'pembalap', 1, NULL, NULL, '2024-10-19 17:00:00', '2024-08-11 17:00:00', NULL),
(102, 'Pembalap Historical 95', 'pembalap2024_95@test.com', NULL, '$2y$12$W8LVANdl7Iq.u2Q9P0goKeaycZNhtciGBDRly7UbsVDGgmvGdSd/2', 'pembalap', 1, NULL, NULL, '2024-07-02 17:00:00', '2024-12-16 17:00:00', NULL),
(103, 'Pembalap Historical 96', 'pembalap2024_96@test.com', NULL, '$2y$12$2utDXSKgo3NaQ/wyYkC4nuuy5f9ijlQoSfzlzl7p1I/YQchVj3AOG', 'pembalap', 1, NULL, NULL, '2024-07-18 17:00:00', '2024-02-02 17:00:00', NULL),
(104, 'Pembalap Historical 97', 'pembalap2024_97@test.com', NULL, '$2y$12$sz.rfFAM/wujNSM7FJquzO7TZfxbFHQJv2eMhgHCn7/miIqcxDE.y', 'pembalap', 1, NULL, NULL, '2024-10-14 17:00:00', '2024-02-24 17:00:00', NULL),
(105, 'Pembalap Historical 98', 'pembalap2024_98@test.com', NULL, '$2y$12$vLjS79FPYpccS9Vo1TKaS.nH4k5zR0ZGTUXLxfpmnr3F8WTNz6pje', 'pembalap', 1, NULL, NULL, '2024-09-15 17:00:00', '2024-06-08 17:00:00', NULL),
(106, 'Pembalap Historical 99', 'pembalap2024_99@test.com', NULL, '$2y$12$5UJuwDUMGcVrWy8uapFBaeTlfMO9gbnV9iMN/TMq6KkX70xoAAFRq', 'pembalap', 1, NULL, NULL, '2024-04-23 17:00:00', '2024-03-21 17:00:00', NULL),
(107, 'Pembalap Historical 100', 'pembalap2024_100@test.com', NULL, '$2y$12$1sWO0xl1F.xgiEwoxcRmfeEmNi1t7o9YTxmrS7QyzMe.T2uZYm7t2', 'pembalap', 1, NULL, NULL, '2024-12-26 17:00:00', '2024-10-01 17:00:00', NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `log_user_role_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_dashboard_kpis`
-- (See below for the actual view)
--
CREATE TABLE `view_dashboard_kpis` (
`total_pembalap_aktif` bigint(21)
,`total_klub` bigint(21)
,`total_event_selesai` bigint(21)
,`total_kis_pending` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_detailed_event_results`
-- (See below for the actual view)
--
CREATE TABLE `view_detailed_event_results` (
`registration_id` bigint(20) unsigned
,`event_id` bigint(20) unsigned
,`pembalap_user_id` bigint(20) unsigned
,`kis_category_id` bigint(20) unsigned
,`result_position` int(11)
,`points_earned` int(11)
,`registration_status` enum('Pending Payment','Pending Confirmation','Confirmed','Rejected','Cancelled')
,`pembalap_name` varchar(255)
,`pembalap_email` varchar(255)
,`category_name` varchar(255)
,`category_code` varchar(10)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_event_revenue_ranking`
-- (See below for the actual view)
--
CREATE TABLE `view_event_revenue_ranking` (
`event_id` bigint(20) unsigned
,`event_name` varchar(255)
,`event_date` date
,`proposing_club` varchar(255)
,`total_registrations` bigint(21)
,`confirmed_count` decimal(22,0)
,`pending_payment_count` decimal(22,0)
,`estimated_revenue` decimal(28,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_finished_events`
-- (See below for the actual view)
--
CREATE TABLE `view_finished_events` (
`id` bigint(20) unsigned
,`event_name` varchar(255)
,`event_date` date
,`location` varchar(255)
,`description` text
,`proposing_club_id` bigint(20) unsigned
,`created_by_user_id` bigint(20) unsigned
,`is_published` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
,`proposing_club_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_leaderboard`
-- (See below for the actual view)
--
CREATE TABLE `view_leaderboard` (
`nama_pembalap` varchar(255)
,`kategori` varchar(255)
,`kategori_id` bigint(20) unsigned
,`total_poin` decimal(32,0)
,`jumlah_balapan` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_operational_alerts`
-- (See below for the actual view)
--
CREATE TABLE `view_operational_alerts` (
`alert_type` varchar(39)
,`count` bigint(21)
,`status` varchar(20)
,`alert_date` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_revenue_breakdown_ytd`
-- (See below for the actual view)
--
CREATE TABLE `view_revenue_breakdown_ytd` (
`tahun` int(5)
,`bulan` int(3)
,`periode` varchar(7)
,`total_events` bigint(21)
,`total_registrations` bigint(21)
,`confirmed_registrations` decimal(22,0)
,`revenue_estimate` decimal(28,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_top_clubs_performance`
-- (See below for the actual view)
--
CREATE TABLE `view_top_clubs_performance` (
`club_id` bigint(20) unsigned
,`nama_klub` varchar(255)
,`total_events_organized` bigint(21)
,`total_participants` bigint(21)
,`total_dues_paid` decimal(32,2)
,`last_payment_date` date
,`club_status` varchar(8)
);

-- --------------------------------------------------------

--
-- Structure for view `view_dashboard_kpis`
--
DROP TABLE IF EXISTS `view_dashboard_kpis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_dashboard_kpis`  AS SELECT (select count(`kis_licenses`.`id`) from `kis_licenses` where `kis_licenses`.`expiry_date` >= curdate()) AS `total_pembalap_aktif`, (select count(`clubs`.`id`) from `clubs`) AS `total_klub`, (select count(`events`.`id`) from `events` where `events`.`event_date` < curdate() and `events`.`is_published` = 1) AS `total_event_selesai`, (select count(`kis_applications`.`id`) from `kis_applications` where `kis_applications`.`status` = 'Pending') AS `total_kis_pending``total_kis_pending`  ;

-- --------------------------------------------------------

--
-- Structure for view `view_detailed_event_results`
--
DROP TABLE IF EXISTS `view_detailed_event_results`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_detailed_event_results`  AS SELECT `er`.`id` AS `registration_id`, `er`.`event_id` AS `event_id`, `er`.`pembalap_user_id` AS `pembalap_user_id`, `er`.`kis_category_id` AS `kis_category_id`, `er`.`result_position` AS `result_position`, `er`.`points_earned` AS `points_earned`, `er`.`status` AS `registration_status`, `u`.`name` AS `pembalap_name`, `u`.`email` AS `pembalap_email`, `kc`.`nama_kategori` AS `category_name`, `kc`.`kode_kategori` AS `category_code` FROM ((`event_registrations` `er` left join `users` `u` on(`er`.`pembalap_user_id` = `u`.`id`)) left join `kis_categories` `kc` on(`er`.`kis_category_id` = `kc`.`id`)) WHERE `er`.`result_position` is not null OR `er`.`points_earned` > 00  ;

-- --------------------------------------------------------

--
-- Structure for view `view_event_revenue_ranking`
--
DROP TABLE IF EXISTS `view_event_revenue_ranking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_event_revenue_ranking`  AS SELECT `e`.`id` AS `event_id`, `e`.`event_name` AS `event_name`, `e`.`event_date` AS `event_date`, `c`.`nama_klub` AS `proposing_club`, count(`er`.`id`) AS `total_registrations`, sum(case when `er`.`status` in ('Confirmed','Pending Confirmation') then 1 else 0 end) AS `confirmed_count`, sum(case when `er`.`status` = 'Pending Payment' then 1 else 0 end) AS `pending_payment_count`, sum(case when `er`.`status` in ('Confirmed','Pending Confirmation') then 1 else 0 end) * 100000 AS `estimated_revenue` FROM ((`events` `e` left join `event_registrations` `er` on(`e`.`id` = `er`.`event_id`)) left join `clubs` `c` on(`e`.`proposing_club_id` = `c`.`id`)) WHERE `e`.`is_published` = 1 GROUP BY `e`.`id`, `e`.`event_name`, `e`.`event_date`, `c`.`nama_klub` ORDER BY sum(case when `er`.`status` in ('Confirmed','Pending Confirmation') then 1 else 0 end) * 100000 AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `view_finished_events`
--
DROP TABLE IF EXISTS `view_finished_events`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_finished_events`  AS SELECT `e`.`id` AS `id`, `e`.`event_name` AS `event_name`, `e`.`event_date` AS `event_date`, `e`.`location` AS `location`, `e`.`description` AS `description`, `e`.`proposing_club_id` AS `proposing_club_id`, `e`.`created_by_user_id` AS `created_by_user_id`, `e`.`is_published` AS `is_published`, `e`.`created_at` AS `created_at`, `e`.`updated_at` AS `updated_at`, `c`.`nama_klub` AS `proposing_club_name` FROM (`events` `e` left join `clubs` `c` on(`e`.`proposing_club_id` = `c`.`id`)) WHERE `e`.`is_published` = 1 AND `e`.`event_date` < curdate()  ;

-- --------------------------------------------------------

--
-- Structure for view `view_leaderboard`
--
DROP TABLE IF EXISTS `view_leaderboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_leaderboard`  AS SELECT `u`.`name` AS `nama_pembalap`, `kc`.`nama_kategori` AS `kategori`, `kc`.`id` AS `kategori_id`, sum(`er`.`points_earned`) AS `total_poin`, count(`er`.`id`) AS `jumlah_balapan` FROM ((`event_registrations` `er` join `users` `u` on(`er`.`pembalap_user_id` = `u`.`id`)) left join `kis_categories` `kc` on(`er`.`kis_category_id` = `kc`.`id`)) GROUP BY `er`.`pembalap_user_id`, `u`.`name`, `kc`.`nama_kategori`, `kc`.`id` ORDER BY `kc`.`nama_kategori` ASC, sum(`er`.`points_earned`) AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `view_operational_alerts`
--
DROP TABLE IF EXISTS `view_operational_alerts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_operational_alerts`  AS SELECT 'KIS Application Pending' AS `alert_type`, count(`kis_applications`.`id`) AS `count`, 'Pending' AS `status`, curdate() AS `alert_date` FROM `kis_applications` WHERE `kis_applications`.`status` = 'Pending' union all select 'KIS License Expiring Soon' AS `alert_type`,count(`kis_licenses`.`id`) AS `count`,'Expiring in 30 Days' AS `status`,curdate() AS `alert_date` from `kis_licenses` where `kis_licenses`.`expiry_date` between curdate() and curdate() + interval 30 day union all select 'Event Registration Pending Confirmation' AS `alert_type`,count(`event_registrations`.`id`) AS `count`,'Pending Confirmation' AS `status`,curdate() AS `alert_date` from `event_registrations` where `event_registrations`.`status` = 'Pending Confirmation' union all select 'Club Dues Pending Approval' AS `alert_type`,count(`club_dues`.`id`) AS `count`,'Pending' AS `status`,curdate() AS `alert_date` from `club_dues` where `club_dues`.`status` = 'Pending'  ;

-- --------------------------------------------------------

--
-- Structure for view `view_revenue_breakdown_ytd`
--
DROP TABLE IF EXISTS `view_revenue_breakdown_ytd`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_revenue_breakdown_ytd`  AS SELECT year(`e`.`event_date`) AS `tahun`, month(`e`.`event_date`) AS `bulan`, date_format(`e`.`event_date`,'%Y-%m') AS `periode`, count(distinct `e`.`id`) AS `total_events`, count(`er`.`id`) AS `total_registrations`, sum(case when `er`.`status` in ('Confirmed','Pending Confirmation') then 1 else 0 end) AS `confirmed_registrations`, sum(case when `er`.`status` in ('Confirmed','Pending Confirmation') then 1 else 0 end) * 100000 AS `revenue_estimate` FROM (`events` `e` left join `event_registrations` `er` on(`e`.`id` = `er`.`event_id`)) WHERE `e`.`is_published` = 1 AND year(`e`.`event_date`) = year(curdate()) GROUP BY year(`e`.`event_date`), month(`e`.`event_date`), date_format(`e`.`event_date`,'%Y-%m') ORDER BY year(`e`.`event_date`) DESC, month(`e`.`event_date`) AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `view_top_clubs_performance`
--
DROP TABLE IF EXISTS `view_top_clubs_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_top_clubs_performance`  AS SELECT `c`.`id` AS `club_id`, `c`.`nama_klub` AS `nama_klub`, count(distinct `e`.`id`) AS `total_events_organized`, count(distinct `er`.`id`) AS `total_participants`, sum(case when `cd`.`status` = 'Approved' then `cd`.`amount_paid` else 0 end) AS `total_dues_paid`, max(`cd`.`payment_date`) AS `last_payment_date`, CASE WHEN max(`cd`.`payment_date`) >= curdate() - interval 1 year THEN 'Active' ELSE 'Inactive' END AS `club_status` FROM (((`clubs` `c` left join `events` `e` on(`c`.`id` = `e`.`proposing_club_id`)) left join `event_registrations` `er` on(`e`.`id` = `er`.`event_id`)) left join `club_dues` `cd` on(`c`.`id` = `cd`.`club_id`)) GROUP BY `c`.`id`, `c`.`nama_klub` ORDER BY count(distinct `e`.`id`) DESC, count(distinct `er`.`id`) AS `DESCdesc` ASC  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clubs_nama_klub_unique` (`nama_klub`),
  ADD UNIQUE KEY `clubs_email_klub_unique` (`email_klub`);

--
-- Indexes for table `club_dues`
--
ALTER TABLE `club_dues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_dues_club_id_foreign` (`club_id`),
  ADD KEY `club_dues_processed_by_user_id_foreign` (`processed_by_user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_proposing_club_id_foreign` (`proposing_club_id`),
  ADD KEY `events_created_by_user_id_foreign` (`created_by_user_id`);

--
-- Indexes for table `event_kis_category`
--
ALTER TABLE `event_kis_category`
  ADD PRIMARY KEY (`event_id`,`kis_category_id`),
  ADD KEY `event_kis_category_kis_category_id_foreign` (`kis_category_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_registrations_event_id_foreign` (`event_id`),
  ADD KEY `event_registrations_pembalap_user_id_foreign` (`pembalap_user_id`),
  ADD KEY `event_registrations_kis_category_id_foreign` (`kis_category_id`),
  ADD KEY `event_registrations_payment_processed_by_user_id_foreign` (`payment_processed_by_user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kis_applications`
--
ALTER TABLE `kis_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kis_applications_pembalap_user_id_foreign` (`pembalap_user_id`),
  ADD KEY `kis_applications_processed_by_user_id_foreign` (`processed_by_user_id`);

--
-- Indexes for table `kis_categories`
--
ALTER TABLE `kis_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kis_categories_kode_kategori_unique` (`kode_kategori`);

--
-- Indexes for table `kis_licenses`
--
ALTER TABLE `kis_licenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kis_licenses_pembalap_user_id_unique` (`pembalap_user_id`),
  ADD UNIQUE KEY `kis_licenses_kis_number_unique` (`kis_number`),
  ADD KEY `kis_licenses_application_id_foreign` (`application_id`),
  ADD KEY `kis_licenses_kis_category_id_foreign` (`kis_category_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembalap_profiles`
--
ALTER TABLE `pembalap_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pembalap_profiles_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `pembalap_profiles_no_ktp_sim_unique` (`no_ktp_sim`),
  ADD KEY `pembalap_profiles_club_id_foreign` (`club_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_club_id_foreign` (`club_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `club_dues`
--
ALTER TABLE `club_dues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=945;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kis_applications`
--
ALTER TABLE `kis_applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `kis_categories`
--
ALTER TABLE `kis_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kis_licenses`
--
ALTER TABLE `kis_licenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1725;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pembalap_profiles`
--
ALTER TABLE `pembalap_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `club_dues`
--
ALTER TABLE `club_dues`
  ADD CONSTRAINT `club_dues_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_dues_processed_by_user_id_foreign` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `events_proposing_club_id_foreign` FOREIGN KEY (`proposing_club_id`) REFERENCES `clubs` (`id`);

--
-- Constraints for table `event_kis_category`
--
ALTER TABLE `event_kis_category`
  ADD CONSTRAINT `event_kis_category_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_kis_category_kis_category_id_foreign` FOREIGN KEY (`kis_category_id`) REFERENCES `kis_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `event_registrations_kis_category_id_foreign` FOREIGN KEY (`kis_category_id`) REFERENCES `kis_categories` (`id`),
  ADD CONSTRAINT `event_registrations_payment_processed_by_user_id_foreign` FOREIGN KEY (`payment_processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `event_registrations_pembalap_user_id_foreign` FOREIGN KEY (`pembalap_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `kis_applications`
--
ALTER TABLE `kis_applications`
  ADD CONSTRAINT `kis_applications_pembalap_user_id_foreign` FOREIGN KEY (`pembalap_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `kis_applications_processed_by_user_id_foreign` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `kis_licenses`
--
ALTER TABLE `kis_licenses`
  ADD CONSTRAINT `kis_licenses_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `kis_applications` (`id`),
  ADD CONSTRAINT `kis_licenses_kis_category_id_foreign` FOREIGN KEY (`kis_category_id`) REFERENCES `kis_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `kis_licenses_pembalap_user_id_foreign` FOREIGN KEY (`pembalap_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pembalap_profiles`
--
ALTER TABLE `pembalap_profiles`
  ADD CONSTRAINT `pembalap_profiles_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`),
  ADD CONSTRAINT `pembalap_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
