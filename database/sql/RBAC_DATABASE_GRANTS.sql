-- ============================================
-- SCRIPT RBAC DATABASE GRANTS - WebIMI
-- Database: imi
-- Last Updated: 2025-12-14
-- ============================================
-- Deskripsi:
-- Script ini membuat 4 MySQL users dengan privileges granular
-- untuk implementasi Role-Based Access Control (RBAC) di level database.
--
-- 5 User Level:
-- 1. super_admin   -> root (default, ALL PRIVILEGES)
-- 2. pengurus_imi  -> pengurus (Full CRUD + DELETE clubs)
-- 3. pimpinan_imi  -> pimpinan (READ-ONLY analytics)
-- 4. penyelenggara_event -> penyelenggara (Event scope only)
-- 5. pembalap      -> pembalap (Minimal public access)
-- ============================================

-- ============================================
-- SECTION 1: CREATE DATABASE USERS
-- ============================================

-- User 1: PENGURUS IMI
CREATE USER IF NOT EXISTS 'pengurus'@'%' IDENTIFIED BY 'pengurus_pass_2024!';

-- User 2: PIMPINAN IMI
CREATE USER IF NOT EXISTS 'pimpinan'@'%' IDENTIFIED BY 'pimpinan_pass_2024!';

-- User 3: PENYELENGGARA EVENT
CREATE USER IF NOT EXISTS 'penyelenggara'@'%' IDENTIFIED BY 'penyelenggara_pass_2024!';

-- User 4: PEMBALAP
CREATE USER IF NOT EXISTS 'pembalap'@'%' IDENTIFIED BY 'pembalap_pass_2024!';

-- ============================================
-- SECTION 2: GRANT PRIVILEGES - PENGURUS IMI
-- ============================================
-- Role: pengurus_imi
-- Privileges: Full CRUD + DELETE clubs only
-- Description: Admin role dengan full access kecuali DELETE untuk most tables
-- ============================================

-- Tables: SELECT, INSERT, UPDATE
GRANT SELECT, INSERT, UPDATE ON imi.users TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.pembalap_profiles TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.kis_applications TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.kis_licenses TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.kis_categories TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.events TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.event_kis_category TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.event_registrations TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.club_dues TO 'pengurus'@'%';
GRANT SELECT, INSERT, UPDATE ON imi.settings TO 'pengurus'@'%';
GRANT SELECT, INSERT ON imi.logs TO 'pengurus'@'%';

-- Clubs: Full CRUD termasuk DELETE (ada fitur destroy di ClubController.php)
GRANT SELECT, INSERT, UPDATE, DELETE ON imi.clubs TO 'pengurus'@'%';

-- Procedures: EXECUTE
GRANT EXECUTE ON PROCEDURE imi.Proc_Admin_RecordDues TO 'pengurus'@'%';
GRANT EXECUTE ON PROCEDURE imi.Proc_ApplyForKIS TO 'pengurus'@'%';
GRANT EXECUTE ON PROCEDURE imi.Proc_GetLeaderboard TO 'pengurus'@'%';
GRANT EXECUTE ON PROCEDURE imi.Proc_RegisterPembalap TO 'pengurus'@'%';
GRANT EXECUTE ON PROCEDURE imi.Proc_RegisterPembalapToEvent TO 'pengurus'@'%';

-- Functions: EXECUTE
GRANT EXECUTE ON FUNCTION imi.Func_GetPembalapTotalPoints TO 'pengurus'@'%';
GRANT EXECUTE ON FUNCTION imi.Func_Get_Event_Status TO 'pengurus'@'%';

-- Views: SELECT (8 Analytics & Dashboard Views)
GRANT SELECT ON imi.View_Dashboard_KPIs TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Finished_Events TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Detailed_Event_Results TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Leaderboard TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Event_Revenue_Ranking TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Operational_Alerts TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Revenue_Breakdown_YTD TO 'pengurus'@'%';
GRANT SELECT ON imi.View_Top_Clubs_Performance TO 'pengurus'@'%';

-- ============================================
-- SECTION 3: GRANT PRIVILEGES - PIMPINAN IMI
-- ============================================
-- Role: pimpinan_imi
-- Privileges: READ-ONLY (SELECT only)
-- Description: Executive dashboard dengan analytics & reporting access
-- ============================================

-- Tables: SELECT only
GRANT SELECT ON imi.users TO 'pimpinan'@'%';
GRANT SELECT ON imi.pembalap_profiles TO 'pimpinan'@'%';
GRANT SELECT ON imi.kis_applications TO 'pimpinan'@'%';
GRANT SELECT ON imi.kis_licenses TO 'pimpinan'@'%';
GRANT SELECT ON imi.kis_categories TO 'pimpinan'@'%';
GRANT SELECT ON imi.events TO 'pimpinan'@'%';
GRANT SELECT ON imi.event_kis_category TO 'pimpinan'@'%';
GRANT SELECT ON imi.event_registrations TO 'pimpinan'@'%';
GRANT SELECT ON imi.clubs TO 'pimpinan'@'%';
GRANT SELECT ON imi.club_dues TO 'pimpinan'@'%';
GRANT SELECT ON imi.logs TO 'pimpinan'@'%';
GRANT SELECT ON imi.settings TO 'pimpinan'@'%';

-- Procedures: EXECUTE hanya untuk reporting
GRANT EXECUTE ON PROCEDURE imi.Proc_GetLeaderboard TO 'pimpinan'@'%';

-- Functions: EXECUTE untuk analytics
GRANT EXECUTE ON FUNCTION imi.Func_GetPembalapTotalPoints TO 'pimpinan'@'%';
GRANT EXECUTE ON FUNCTION imi.Func_Get_Event_Status TO 'pimpinan'@'%';

-- Views: SELECT (8 Analytics & Dashboard Views)
GRANT SELECT ON imi.View_Dashboard_KPIs TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Finished_Events TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Detailed_Event_Results TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Leaderboard TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Event_Revenue_Ranking TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Operational_Alerts TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Revenue_Breakdown_YTD TO 'pimpinan'@'%';
GRANT SELECT ON imi.View_Top_Clubs_Performance TO 'pimpinan'@'%';

-- ============================================
-- SECTION 4: GRANT PRIVILEGES - PENYELENGGARA EVENT
-- ============================================
-- Role: penyelenggara_event
-- Privileges: Event management only (limited scope)
-- Description: Manage event results & payment approval
-- ============================================

-- Tables: SELECT event-related
GRANT SELECT ON imi.events TO 'penyelenggara'@'%';
GRANT SELECT ON imi.event_kis_category TO 'penyelenggara'@'%';
GRANT SELECT ON imi.event_registrations TO 'penyelenggara'@'%';
GRANT SELECT ON imi.users TO 'penyelenggara'@'%';
GRANT SELECT ON imi.pembalap_profiles TO 'penyelenggara'@'%';
GRANT SELECT ON imi.kis_licenses TO 'penyelenggara'@'%';
GRANT SELECT ON imi.kis_categories TO 'penyelenggara'@'%';
GRANT SELECT ON imi.kis_applications TO 'penyelenggara'@'%';
GRANT SELECT ON imi.clubs TO 'penyelenggara'@'%';

-- NOTE: event_results table tidak ada, data hasil event ada di event_registrations (result_position, points_earned)

-- Tables: INSERT untuk create test registration, UPDATE untuk payment approval & hasil
GRANT INSERT, UPDATE ON imi.event_registrations TO 'penyelenggara'@'%';

-- Views: SELECT (Event-related views only)
GRANT SELECT ON imi.View_Finished_Events TO 'penyelenggara'@'%';
GRANT SELECT ON imi.View_Detailed_Event_Results TO 'penyelenggara'@'%';
GRANT SELECT ON imi.View_Leaderboard TO 'penyelenggara'@'%';

-- NOTE: Penyelenggara menggunakan Eloquent, tidak ada stored procedure

-- ============================================
-- SECTION 5: GRANT PRIVILEGES - PEMBALAP
-- ============================================
-- Role: pembalap
-- Privileges: Minimal public access (own data + public events)
-- Description: Apply KIS, register event, view leaderboard
-- ============================================

-- Tables: SELECT public data
GRANT SELECT ON imi.events TO 'pembalap'@'%';
GRANT SELECT ON imi.event_kis_category TO 'pembalap'@'%';
GRANT SELECT ON imi.kis_categories TO 'pembalap'@'%';
GRANT SELECT ON imi.clubs TO 'pembalap'@'%';
GRANT SELECT ON imi.settings TO 'pembalap'@'%';

-- Tables: SELECT own data (dibatasi WHERE user_id di application layer)
GRANT SELECT ON imi.users TO 'pembalap'@'%';
GRANT SELECT ON imi.pembalap_profiles TO 'pembalap'@'%';
GRANT SELECT ON imi.kis_applications TO 'pembalap'@'%';
GRANT SELECT ON imi.kis_licenses TO 'pembalap'@'%';
GRANT SELECT ON imi.event_registrations TO 'pembalap'@'%';

-- Tables: INSERT untuk pendaftaran
GRANT INSERT ON imi.kis_applications TO 'pembalap'@'%';
GRANT INSERT ON imi.event_registrations TO 'pembalap'@'%';

-- Tables: UPDATE own data
GRANT UPDATE ON imi.users TO 'pembalap'@'%';
GRANT UPDATE ON imi.pembalap_profiles TO 'pembalap'@'%';
GRANT UPDATE ON imi.event_registrations TO 'pembalap'@'%'; -- untuk upload payment proof

-- Procedures: EXECUTE
GRANT EXECUTE ON PROCEDURE imi.Proc_ApplyForKIS TO 'pembalap'@'%';
GRANT EXECUTE ON PROCEDURE imi.Proc_RegisterPembalapToEvent TO 'pembalap'@'%';
GRANT EXECUTE ON PROCEDURE imi.Proc_GetLeaderboard TO 'pembalap'@'%';

-- Functions: EXECUTE
GRANT EXECUTE ON FUNCTION imi.Func_GetPembalapTotalPoints TO 'pembalap'@'%';

-- Views: SELECT (Public views only)
GRANT SELECT ON imi.View_Finished_Events TO 'pembalap'@'%';
GRANT SELECT ON imi.View_Leaderboard TO 'pembalap'@'%';
GRANT SELECT ON imi.View_Detailed_Event_Results TO 'pembalap'@'%';

-- ============================================
-- SECTION 6: APPLY CHANGES
-- ============================================

FLUSH PRIVILEGES;

-- ============================================
-- SECTION 7: VERIFICATION QUERIES
-- ============================================
-- Jalankan query berikut untuk verifikasi:

-- Cek semua users yang dibuat:
-- SELECT User, Host FROM mysql.user WHERE User IN ('pengurus', 'pimpinan', 'penyelenggara', 'pembalap');

-- Cek privileges per user:
-- SHOW GRANTS FOR 'pengurus'@'%';
-- SHOW GRANTS FOR 'pimpinan'@'%';
-- SHOW GRANTS FOR 'penyelenggara'@'%';
-- SHOW GRANTS FOR 'pembalap'@'%';

-- ============================================
-- END OF SCRIPT
-- ============================================
