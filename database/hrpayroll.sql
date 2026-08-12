-- HR Payroll Management System
-- MySQL database dump (schema + core seed data)
-- Database: hrpayroll
-- Import: mysql -u root < database/hrpayroll.sql
-- Or via phpMyAdmin: Import this file

CREATE DATABASE IF NOT EXISTS `hrpayroll` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hrpayroll`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Core Laravel / Auth
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text,
  `role` varchar(255) NOT NULL DEFAULT 'employee',
  `department` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `join_date` date DEFAULT NULL,
  `tax_category` varchar(255) NOT NULL DEFAULT 'general',
  `tin` varchar(20) DEFAULT NULL,
  `weekly_off` json DEFAULT NULL,
  `portal_login` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_employee_code_unique` (`employee_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- HR Payroll domain tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `check_in` varchar(255) DEFAULT NULL,
  `check_out` varchar(255) DEFAULT NULL,
  `hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) NOT NULL DEFAULT 'Present',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_user_id_date_unique` (`user_id`,`date`),
  CONSTRAINT `attendance_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `days` int unsigned NOT NULL,
  `reason` text,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `applied_on` date NOT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `review_comment` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_requests_code_unique` (`code`),
  CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `overtime_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `hours` decimal(5,2) NOT NULL,
  `reason` text,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `overtime_requests_code_unique` (`code`),
  CONSTRAINT `overtime_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `loans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `installments` int unsigned DEFAULT NULL,
  `emi` decimal(12,2) NOT NULL,
  `outstanding` decimal(14,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `start_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loans_code_unique` (`code`),
  CONSTRAINT `loans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hr_queries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `category` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `priority` varchar(255) NOT NULL DEFAULT 'Medium',
  `ai_draft` text,
  `ai_category` varchar(255) DEFAULT NULL,
  `ai_confidence` decimal(5,4) DEFAULT NULL,
  `needs_manual_review` tinyint(1) NOT NULL DEFAULT '0',
  `response` text,
  `submitted_on` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hr_queries_code_unique` (`code`),
  CONSTRAINT `hr_queries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payslips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `month` varchar(255) NOT NULL,
  `year` smallint unsigned NOT NULL,
  `month_num` tinyint unsigned NOT NULL,
  `basic` decimal(12,2) NOT NULL DEFAULT '0.00',
  `hra` decimal(12,2) NOT NULL DEFAULT '0.00',
  `da` decimal(12,2) NOT NULL DEFAULT '0.00',
  `allowances` decimal(12,2) NOT NULL DEFAULT '0.00',
  `overtime_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gross` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tds` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pf_employee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pf_employer` decimal(12,2) NOT NULL DEFAULT '0.00',
  `loan_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `attendance_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unpaid_leave_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) NOT NULL DEFAULT 'Generated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payslips_user_id_year_month_num_unique` (`user_id`,`year`,`month_num`),
  CONSTRAINT `payslips_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bonuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `basic` decimal(12,2) NOT NULL,
  `years_of_service` decimal(5,2) NOT NULL,
  `festival_bonus` decimal(12,2) NOT NULL DEFAULT '0.00',
  `performance_bonus` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bonuses_code_unique` (`code`),
  CONSTRAINT `bonuses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `increments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `current_salary` decimal(12,2) NOT NULL,
  `increment_pct` decimal(5,2) NOT NULL,
  `new_salary` decimal(12,2) NOT NULL,
  `effective_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `increments_code_unique` (`code`),
  CONSTRAINT `increments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settlements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `exit_date` date NOT NULL,
  `last_basic` decimal(12,2) NOT NULL,
  `years_of_service` decimal(5,2) NOT NULL,
  `gratuity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `leave_encashment` decimal(12,2) NOT NULL DEFAULT '0.00',
  `last_increment_pct` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pf_employee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tds` decimal(12,2) NOT NULL DEFAULT '0.00',
  `final_month_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `outstanding_loan` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_settlement` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) NOT NULL DEFAULT 'Initiated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `settlements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `app_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `details` text,
  `severity` varchar(255) NOT NULL DEFAULT 'info',
  `logged_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `designations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `designations_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_minutes` int NOT NULL DEFAULT '15',
  `break_minutes` int NOT NULL DEFAULT '60',
  `ot_starts_after` int NOT NULL DEFAULT '8',
  `is_overnight` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shift_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `shift_id` bigint unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `shift_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_assignments_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `description` text,
  `price_monthly` decimal(12,2) NOT NULL DEFAULT '0.00',
  `price_yearly` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) NOT NULL DEFAULT 'BDT',
  `icon` varchar(255) DEFAULT NULL,
  `badge` varchar(255) DEFAULT NULL,
  `features` json DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_inquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `message` text,
  `status` varchar(255) NOT NULL DEFAULT 'New',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `site_inquiries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Dynamic rules / settings (data-driven payroll)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payroll_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tax_slabs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `regime` varchar(255) NOT NULL DEFAULT 'new',
  `min_income` decimal(14,2) NOT NULL DEFAULT '0.00',
  `max_income` decimal(14,2) DEFAULT NULL,
  `rate_pct` decimal(5,2) NOT NULL DEFAULT '0.00',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `holidays_date_unique` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payroll_faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `keywords` text NOT NULL,
  `response` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `casual` tinyint unsigned NOT NULL DEFAULT '12',
  `sick` tinyint unsigned NOT NULL DEFAULT '6',
  `earned` tinyint unsigned NOT NULL DEFAULT '15',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_balances_user_id_year_unique` (`user_id`,`year`),
  CONSTRAINT `leave_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed: payroll settings
-- --------------------------------------------------------

INSERT INTO `payroll_settings` (`key`, `value`, `group`, `label`, `created_at`, `updated_at`) VALUES
('salary_basic_pct','60','salary','Basic % of CTC',NOW(),NOW()),
('salary_hra_pct','20','salary','HRA / House Rent %',NOW(),NOW()),
('salary_da_pct','10','salary','DA / Medical %',NOW(),NOW()),
('salary_allowance_pct','10','salary','Other Allowances %',NOW(),NOW()),
('pf_employee_pct','10','pf','Employee PF %',NOW(),NOW()),
('pf_employer_pct','10','pf','Employer PF %',NOW(),NOW()),
('tax_regime','nbr','tax','Tax Regime',NOW(),NOW()),
('tax_employment_deduction_pct','33.3333','tax','NBR Employment Deduction %',NOW(),NOW()),
('tax_employment_deduction_max','500000','tax','NBR Employment Deduction Max',NOW(),NOW()),
('tax_free_general','375000','tax','NBR Tax-Free (General)',NOW(),NOW()),
('tax_free_woman','425000','tax','NBR Tax-Free (Woman/Senior)',NOW(),NOW()),
('tax_free_disabled','500000','tax','NBR Tax-Free (Disabled)',NOW(),NOW()),
('tax_free_freedom_fighter','525000','tax','NBR Tax-Free (Freedom Fighter)',NOW(),NOW()),
('tax_minimum_tax','5000','tax','NBR Minimum Tax',NOW(),NOW()),
('tax_cess_pct','0','tax','Cess %',NOW(),NOW()),
('currency','BDT','general','Currency',NOW(),NOW()),
('late_threshold','09:15','attendance','Default Late Threshold',NOW(),NOW()),
('lates_per_absence','3','attendance','Lates Equal One Absence',NOW(),NOW()),
('weekend_days','5','attendance','Weekend Day Numbers (0=Sun)',NOW(),NOW()),
('working_days_per_month','26','payroll','Working Days / Month',NOW(),NOW()),
('ot_hourly_multiplier','1.5','payroll','OT Hourly Multiplier',NOW(),NOW()),
('default_increment_pct','10','payroll','Default Increment %',NOW(),NOW()),
('festival_bonus_full_pct','50','bonus','Full Festival Bonus % of Basic',NOW(),NOW()),
('festival_bonus_prorata_pct','25','bonus','Pro-rata Festival Bonus %',NOW(),NOW()),
('festival_bonus_full_years','1','bonus','Years for Full Bonus',NOW(),NOW()),
('loan_salary_protection_pct','50','loan','Max Loan Deduction % of Net',NOW(),NOW()),
('leave_casual_per_year','12','leave','Casual Leaves / Year',NOW(),NOW()),
('leave_sick_per_year','6','leave','Sick Leaves / Year',NOW(),NOW()),
('leave_earned_per_year','15','leave','Earned Leaves / Year',NOW(),NOW()),
('leave_encashment_divisor','26','settlement','Leave Encashment Divisor',NOW(),NOW()),
('gratuity_min_years','5','settlement','Min Years for Gratuity',NOW(),NOW()),
('gratuity_days_per_year','15','settlement','Gratuity Days per Year',NOW(),NOW()),
('ai_confidence_threshold','0.35','ai','AI Match Threshold',NOW(),NOW()),
('ai_high_confidence','0.55','ai','AI High Confidence',NOW(),NOW())
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `updated_at`=NOW();

INSERT INTO `tax_slabs` (`regime`,`min_income`,`max_income`,`rate_pct`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('nbr',0,375000,0,1,1,NOW(),NOW()),
('nbr',375000,675000,10,2,1,NOW(),NOW()),
('nbr',675000,1075000,15,3,1,NOW(),NOW()),
('nbr',1075000,1575000,20,4,1,NOW(),NOW()),
('nbr',1575000,3575000,25,5,1,NOW(),NOW()),
('nbr',3575000,NULL,30,6,1,NOW(),NOW());

INSERT INTO `holidays` (`date`,`name`,`is_active`,`created_at`,`updated_at`) VALUES
('2025-02-21','International Mother Language Day',1,NOW(),NOW()),
('2025-03-17','Bangabandhu Birthday / Children Day',1,NOW(),NOW()),
('2025-03-26','Independence Day',1,NOW(),NOW()),
('2025-05-01','May Day',1,NOW(),NOW()),
('2025-08-15','National Mourning Day',1,NOW(),NOW()),
('2025-12-16','Victory Day',1,NOW(),NOW()),
('2026-02-21','International Mother Language Day',1,NOW(),NOW()),
('2026-03-26','Independence Day',1,NOW(),NOW()),
('2026-12-16','Victory Day',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

INSERT INTO `payroll_faqs` (`category`,`title`,`keywords`,`response`,`is_active`,`created_at`,`updated_at`) VALUES
('Tax / TDS','Income tax deduction','tax,tds,income tax,slab','TDS is deducted monthly based on projected annual taxable income using government tax slabs. Salary revisions trigger recalculation.',1,NOW(),NOW()),
('Leave','Leave balance entitlement','leave,balance,casual,sick','You are entitled to 12 casual, 6 sick and 15 earned leaves per calendar year. Unused casual leaves may be encashed on settlement.',1,NOW(),NOW()),
('Provident Fund','PF contribution','pf,provident,epf','PF contribution is 12% of basic for both employee and employer, configurable in payroll settings.',1,NOW(),NOW()),
('Payslip','Monthly payslip','payslip,salary,net pay','Payslips are generated after monthly payroll processing and include earnings, OT, TDS, PF and loan deductions.',1,NOW(),NOW()),
('Loan','Loan EMI and protection','loan,emi,advance,deduction','Loan EMIs are deducted automatically. Maximum deduction is capped at 50% of net salary (salary protection rule). Excess EMI is deferred.',1,NOW(),NOW()),
('Bonus','Festival bonus','bonus,festival,diwali','Festival bonus is 50% of basic after 1 year of service, otherwise pro-rata 25% of basic.',1,NOW(),NOW()),
('Overtime','Overtime payment','overtime,ot,extra hours','Approved overtime is paid at 1.5× hourly rate based on basic salary during payroll processing.',1,NOW(),NOW()),
('Settlement','Final settlement','settlement,gratuity,exit,resignation','Final settlement includes last month salary, leave encashment, gratuity (if eligible), minus PF, TDS and outstanding loans.',1,NOW(),NOW()),
('Attendance','Late and absence rule','attendance,late,absent,punch','Three late arrivals in a month count as one absence and affect attendance-based salary deductions.',1,NOW(),NOW()),
('Increment','Salary increment','increment,raise,appraisal','Annual increments (minimum 10%) can be applied after completing 1 year of service and update the employee basic salary.',1,NOW(),NOW());

-- Demo admin (password: admin1234) — bcrypt hash
INSERT INTO `users` (`employee_code`,`name`,`email`,`password`,`role`,`department`,`job_title`,`salary`,`status`,`join_date`,`email_verified_at`,`created_at`,`updated_at`)
VALUES ('ADM001','Admin','admin@corp.com','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','HR','System Administrator',120000.00,'Active','2018-01-01',NOW(),NOW(),NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

SET FOREIGN_KEY_CHECKS = 1;

-- NOTE:
-- Full demo employees / attendance / payslips are seeded by:
--   php artisan migrate:fresh --seed
-- Prefer Laravel migrate+seed for complete data.
-- This SQL file creates the schema and core dynamic rules for MySQL/XAMPP.
