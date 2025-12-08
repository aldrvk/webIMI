-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 08, 2025 at 05:44 AM
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
-- Database: `imi_sumut`
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_RegisterPembalap` (IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password_hash` VARCHAR(255), IN `p_club_id` BIGINT, IN `p_tanggal_lahir` DATE, IN `p_phone_number` VARCHAR(20))   BEGIN
    DECLARE v_user_id BIGINT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL; 
    END;
    START TRANSACTION;
    INSERT INTO users (
        name, email, password, role, created_at, updated_at
    ) 
    VALUES (
        p_name, p_email, p_password_hash, 'pembalap', NOW(), NOW()
    );
    SET v_user_id = LAST_INSERT_ID();
    INSERT INTO pembalap_profiles (
        user_id, club_id, tanggal_lahir, phone_number, created_at, updated_at
    ) 
    VALUES (
        v_user_id, p_club_id, p_tanggal_lahir, p_phone_number, NOW(), NOW()
    );
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Proc_RegisterPembalapToEvent` (IN `p_pembalap_user_id` BIGINT, IN `p_event_id` BIGINT, IN `p_category` VARCHAR(100))   BEGIN
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
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `Func_GetPembalapTotalPoints` (`p_pembalap_user_id` BIGINT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE total_points INT;
    SELECT IFNULL(SUM(points_earned), 0)
    INTO total_points
    FROM event_registrations
    WHERE pembalap_user_id = p_pembalap_user_id;
    RETURN total_points;
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
(1, 'IMI Sumut Official', 'Jl. Taruma No. 52 Medan', 'Harun Nasution', '081234567890', 'admin@imi-sumut.or.id', '2025-11-24 08:51:01', '2025-11-24 08:51:01'),
(2, 'SPEED\'ER MOTORSPORT', 'Jl. Jorlang Hatoran No. 85 A, Siantar', 'Hasanuddin Lubis', '081234567891', 'speeder@example.com', '2025-11-24 08:51:01', '2025-11-24 08:51:01'),
(3, 'Kitakita Motorsport', 'Medan', 'Adek Hidayat', '081234567892', 'kitakita@example.com', '2025-11-24 08:51:01', '2025-11-24 08:51:01');

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
(1, 'Kejuaraan Tes (SELESAI)', '2025-11-17', '2025-11-15 23:59:59', 'Sirkuit Pancing', NULL, '100000.00', 'BCA 12345 a/n Klub Speeder', NULL, NULL, NULL, 2, 2, 1, '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(2, 'Kejuaraan Tes (AKAN DATANG)', '2025-12-24', '2025-12-15 23:59:59', 'Sirkuit Karting IMI', NULL, '250000.00', 'Mandiri 98765 a/n Panitia Speeder', NULL, NULL, 'event-posters/dummy-poster.jpg', 2, 2, 1, '2025-11-24 08:51:02', '2025-11-24 08:51:02');

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
(1, 1, 5, 1, 1, NULL, 25, 'Confirmed', 'payment-proofs/dummy-lunas.jpg', NULL, '2025-11-24 08:51:02', 4, '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(2, 2, 6, 1, NULL, NULL, 0, 'Pending Confirmation', 'payment-proofs/dummy-pending.jpg', NULL, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(3, 2, 7, 8, NULL, NULL, 0, 'Rejected', 'payment-proofs/dummy-ditolak.jpg', 'Bukti transfer tidak jelas/buram. Harap upload ulang.', '2025-11-24 08:51:02', 4, '2025-11-24 08:51:02', '2025-11-24 08:51:02');

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
(1, 5, 1, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, 2, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(2, 6, 1, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, 2, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(3, 7, 8, NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, 2, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(4, 8, 7, NULL, 'kis_documents/pas_foto/Xffjs5GSpje6eXR60QLNjzNVXGOemd7zER412Pqk.png', 'kis_documents/kk/e66Kss8XWYgGGeqXUze6eFEAuGvQUSGAyt0JcUz8.png', 'kis_documents/surat_izin/LQrJQhGRyrZlsMegiZanfl3fXH0SNTt1GmCazj3J.png', 'Approved', 'kis_documents/surat_sehat/SnfPlAQ6xFfiZQUz71byDmVKwYWbMSle1LIqTMdp.png', 'kis_documents/bukti_bayar/sCRwvoYdefUztlP35a4LSwpDDJz3PAFYID1m2krQ.png', 2, NULL, '2025-11-24 08:58:04', '2025-11-24 08:54:35', '2025-11-24 08:58:04'),
(5, 9, 7, NULL, 'kis_documents/pas_foto/MagNFj1Uj1iMzjNonynWfpElqoP4g0B1XYZZS0su.png', 'kis_documents/kk/sA4EkK3DTuu7Fdaj5kE5t0twGwIUIhR8CVEVqmRy.png', 'kis_documents/surat_izin/sIwhovFLHNUUEm9WV3zHGrbOjW3mQkNCYOfKXMf7.png', 'Approved', 'kis_documents/surat_sehat/iGhiVbz6Izo82rpfglHgkiFuRkhJDG6ixp3dctln.png', 'kis_documents/bukti_bayar/4D4vWhDXsCmBPnlpt3zEfP1mMHCcYjLBDjc19tQW.png', 2, NULL, '2025-11-24 08:58:15', '2025-11-24 08:56:03', '2025-11-24 08:58:15'),
(6, 10, 1, 'kis_documents/ktp/6iWtexXTa916uPUVa1hv3KmpO67A7vcqWdqEVRsF.png', 'kis_documents/pas_foto/6XAzpICQeWYcedFRZlcZVuRLerfvLSa9oFbqt4Tb.png', NULL, NULL, 'Approved', 'kis_documents/surat_sehat/LKYxfbjKf3quSazxYz3cUcFLWLY2rxKk8Jof1hnx.png', 'kis_documents/bukti_bayar/LABeqKsjkEXWUdrNXDJP8jwjQM51iEKwZUfIsSTL.png', 2, NULL, '2025-12-08 04:46:27', '2025-12-08 04:45:23', '2025-12-08 04:46:27');

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

-- --------------------------------------------------------

--
-- Table structure for table `kis_categories`
--

CREATE TABLE `kis_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_kategori` varchar(10) NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `tipe` enum('Mobil','Motor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kis_categories`
--

INSERT INTO `kis_categories` (`id`, `kode_kategori`, `nama_kategori`, `tipe`) VALUES
(1, 'C1', 'Balap Motor, Dragsbike', 'Motor'),
(2, 'C2', 'Motocross, Supercross, Grasstrack', 'Motor'),
(3, 'C3', 'Rally', 'Motor'),
(4, 'A1', 'Racing, Drag Race', 'Mobil'),
(5, 'B1', 'Rally/Sprint', 'Mobil'),
(6, 'B3', 'Offroad Adventure/Sprint', 'Mobil'),
(7, 'B4', 'Drift', 'Mobil'),
(8, 'B5', 'Karting', 'Mobil'),
(9, 'B6', 'Slalom', 'Mobil');

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
(1, 5, 1, 1, 'TEST-KIS-001', '2025-11-24', '2026-11-24', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(2, 6, 2, 1, 'TEST-KIS-002', '2025-11-24', '2026-11-24', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(3, 7, 3, 8, 'TEST-KIS-003', '2025-11-24', '2026-11-24', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(4, 8, 4, 7, '4/B4/MDN/XI/2025', '2025-11-24', '2025-12-31', '2025-11-24 08:58:04', '2025-11-24 08:58:04'),
(5, 9, 5, 7, '5/B4/MDN/XI/2025', '2025-11-24', '2025-12-31', '2025-11-24 08:58:15', '2025-11-24 08:58:15'),
(6, 10, 6, 1, '6/C1/MDN/XII/2025', '2025-12-08', '2025-12-31', '2025-12-08 04:46:27', '2025-12-08 04:46:27');

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
(11, '2025_10_31_041008_create_pembalap_profiles_table', 1),
(12, '2025_10_31_044305_create_database_functions_and_views', 1),
(13, '2025_10_31_044305_create_database_procedures', 1),
(14, '2025_10_31_044305_create_database_triggers', 1),
(15, '2025_10_31_055233_create_sessions_table', 1),
(16, '2025_11_02_074622_create_club_dues_table', 1),
(17, '2025_11_02_153603_add_active_status_and_reason_to_users_table', 1),
(18, '2025_11_09_084852_add_club_id_to_users_table', 1),
(19, '2025_11_10_062150_add_kis_category_id_to_kis_licenses_table', 1),
(20, '2025_11_10_065151_add_details_to_events_table', 1),
(21, '2025_11_10_072430_create_event_kis_category_pivot_table', 1),
(22, '2025_11_10_094212_add_image_banner_url_to_events_table', 1),
(23, '2025_11_10_152306_add_registration_deadline_to_events_table', 1),
(24, '2025_11_13_143022_add_bank_account_info_to_events_table', 1),
(25, '2025_11_16_070028_add_identity_files_to_kis_applications_table', 1),
(26, '2025_11_21_031515_create_settings_table', 1),
(27, '2025_11_21_044202_add_minor_documents_to_kis_applications_table', 1),
(28, '2025_11_22_090047_update_kis_license_trigger', 1);

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
(1, 5, 3, NULL, '1990-01-01', NULL, NULL, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(2, 6, 3, NULL, '1991-01-01', NULL, NULL, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(3, 7, 1, NULL, '1992-01-01', NULL, NULL, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(4, 8, 3, 'fdsf', '2010-11-20', '1234567890123456', 'AB', '081236482', 'JL.LINGGARJATI NO.192', '2025-11-24 08:54:35', '2025-11-24 08:54:35'),
(5, 9, 1, 'Medan', '2010-11-05', '1234567890123457', 'A', '08124142152', 'jdadna', '2025-11-24 08:56:03', '2025-11-24 08:56:03'),
(6, 10, 1, 'Pematangsiantar', '2006-09-15', '1234567887654321', 'O', '085279731599', 'Jalan Linggarjati No.192', '2025-12-08 04:45:23', '2025-12-08 04:45:23');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1PpDS5CUPQRAHHi76tlXscjY1ER0l6fSyBFe2KZl', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVEhxUjRTM3ZVb0ZFQ1dERWRORDh5dDBBZWhxQ0pGVTFhbDNDZDhFdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yYWNlcnMvNS9zaGFyZS9jYXJkIjtzOjU6InJvdXRlIjtzOjE3OiJyYWNlcnMuc2hhcmUuY2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEwO30=', 1765172215),
('WpMMZzNIEVIETBYtrVf6D6h6MQI19wS3Wpwftekg', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibTBIaWxtcHl6Q0tRTXlKVlBlNkxYUDB3ZHExTjJzS0ZrbmFwNll1diI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7fQ==', 1765172616);

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
(1, 'kis_bank_account', 'Bank BCA\nNo. Rek: 888-123-4567\nA/n IMI Sumatera Utara', 'Informasi rekening bank untuk pembayaran pendaftaran KIS.', '2025-11-24 08:51:02', '2025-11-24 08:51:02'),
(2, 'kis_registration_fee', '150000', 'Biaya pendaftaran pembuatan KIS (dalam Rupiah).', '2025-11-24 08:51:02', '2025-11-24 08:51:02');

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
(1, 'Super Admin', 'superadmin@imi.com', NULL, '$2y$12$aelGSBq8qtI4FEweF33zXOHL6TrUFjMNB5yvqGwBaVfaHq8TfdtfO', 'super_admin', 1, NULL, NULL, '2025-11-24 08:51:01', '2025-11-24 08:51:01', NULL),
(2, 'Pengurus IMI', 'pengurus@imi.com', NULL, '$2y$12$faAwYDyH2ehoyzcD4OXOeeW/pmK6Tf.pWHgc7UNWDM4hNTH1/xFG.', 'pengurus_imi', 1, NULL, NULL, '2025-11-24 08:51:01', '2025-11-24 08:51:01', NULL),
(3, 'Pimpinan IMI', 'pimpinan@imi.com', NULL, '$2y$12$bNhEgFEp4DG6ULhC15Rh0.7MxN3a5LGDCiOGOJqQMiOfqHJgjWqFS', 'pimpinan_imi', 1, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', NULL),
(4, 'Penyelenggara Speeder', 'penyelenggara@imi.com', NULL, '$2y$12$BX5NqVAGHz9803VYzTCmCOWF888nTsfE.Y00a.xc6E3LDtRfF3lve', 'penyelenggara_event', 1, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', 2),
(5, 'Pembalap Lunas', 'lunas@imi.com', NULL, '$2y$12$LRO.jENqn68gNR6DZ7W5YeY/60fpTnYoza9RvR2v.I64dlygYE9HS', 'pembalap', 1, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', NULL),
(6, 'Pembalap Pending', 'pending@imi.com', NULL, '$2y$12$lCiYYLZKhE.eNoV.eDFql.pPaIlWyJ1wh16FhxTeymxhXPQZuLNIa', 'pembalap', 1, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', NULL),
(7, 'Pembalap Ditolak', 'ditolak@imi.com', NULL, '$2y$12$11.aOirS6rllUXGtHfFqqusCWsK3d4VS53jqe2Ry7rqcKoYvWO1aS', 'pembalap', 1, NULL, NULL, '2025-11-24 08:51:02', '2025-11-24 08:51:02', NULL),
(8, 'jon', 'jon1@gmail.com', NULL, '$2y$12$khDfzA7kofeE5bhUrMqKyeP2nCBoQo6y3t8kK3gpBZfWeubsQLosu', 'pembalap', 1, NULL, NULL, '2025-11-24 08:52:47', '2025-11-24 08:52:47', NULL),
(9, 'doe', 'doe1@gmail.com', NULL, '$2y$12$mNS5NjVaJHqg//TXnqhXnONwS94yq8np86.UR3OPPLSJZqP.oJwKa', 'pembalap', 1, NULL, NULL, '2025-11-24 08:54:56', '2025-11-24 08:54:56', NULL),
(10, 'Leondo Admiral', 'leondo@gmail.com', NULL, '$2y$12$2TqmzkCOFZQZpmRXSkfxlOzKMevxgH5XUf5jKE9leLqMT.fl5bdDq', 'pembalap', 1, NULL, NULL, '2025-12-08 04:41:23', '2025-12-08 04:41:23', NULL);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `club_dues`
--
ALTER TABLE `club_dues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kis_categories`
--
ALTER TABLE `kis_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kis_licenses`
--
ALTER TABLE `kis_licenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pembalap_profiles`
--
ALTER TABLE `pembalap_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
