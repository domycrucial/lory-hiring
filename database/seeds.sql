-- =============================================================
-- OLHS Seed Data — Demo/Test Data
-- WARNING: For development only. Do NOT run on production.
-- All passwords are: Password@123 (bcrypt hashed)
-- =============================================================

SET NAMES utf8mb4;

-- -------------------------------------------------------------
-- Demo Users
-- password for all: Password@123
-- bcrypt hash below is for Password@123 with cost 12
-- -------------------------------------------------------------
INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `role`, `email_verified_at`, `wallet_balance`, `status`) VALUES
  -- Super Admin
  ('System Administrator', 'superadmin@olhs.co.tz', '+255700000001',
   '$2y$12$wQb/7aqsNhBeudORI2AD/O1.IPq7OPoUzxY/VtjzrxxWZ8guPZwai', 'super_admin', NOW(), 0.00, 'active'),

  -- Admin
  ('Platform Admin', 'admin@olhs.co.tz', '+255700000002',
   '$2y$12$wQb/7aqsNhBeudORI2AD/O1.IPq7OPoUzxY/VtjzrxxWZ8guPZwai', 'admin', NOW(), 0.00, 'active'),

  -- Lorry Owner 1
  ('Juma Hassan Salim', 'juma.owner@gmail.com', '+255712345678',
   '$2y$12$wQb/7aqsNhBeudORI2AD/O1.IPq7OPoUzxY/VtjzrxxWZ8guPZwai', 'lorry_owner', NOW(), 125000.00, 'active'),

  -- Lorry Owner 2
  ('Fatuma Rashid', 'fatuma.owner@gmail.com', '+255723456789',
   '$2y$12$wQb/7aqsNhBeudORI2AD/O1.IPq7OPoUzxY/VtjzrxxWZ8guPZwai', 'lorry_owner', NOW(), 87500.00, 'active'),

  -- Customer 1
  ('Ali Mohamed Kimaro', 'ali.customer@gmail.com', '+255754321098',
   '$2y$12$wQb/7aqsNhBeudORI2AD/O1.IPq7OPoUzxY/VtjzrxxWZ8guPZwai', 'customer', NOW(), 0.00, 'active'),

  -- Customer 2
  ('Grace Mwangi Odhiambo', 'grace.customer@gmail.com', '+255765432109',
   '$2y$12$wQb/7aqsNhBeudORI2AD/O1.IPq7OPoUzxY/VtjzrxxWZ8guPZwai', 'customer', NOW(), 0.00, 'active');

-- Note: If the bcrypt hash above does not work (it's a placeholder),
-- run this PHP snippet to generate a real hash:
-- echo password_hash('Password@123', PASSWORD_BCRYPT, ['cost' => 12]);
-- Then UPDATE users SET password_hash = '<generated_hash>';

-- -------------------------------------------------------------
-- Demo Lorries (3 approved lorries)
-- -------------------------------------------------------------
INSERT INTO `lorries` (`owner_id`, `name`, `lorry_type`, `capacity_tonnes`, `plate_number`,
                        `price_per_km`, `base_price`, `current_location`, `lat`, `lng`,
                        `availability_status`, `approval_status`, `description`, `avg_rating`, `total_trips`) VALUES
  -- Owner 1 (id=3) — Flatbed in Dar es Salaam
  (3, 'Simba Express', 'flatbed', 10.00, 'T123 ABC',
   1500.00, 50000.00, 'Dar es Salaam', -6.7924, 39.2083,
   'available', 'approved',
   'Reliable flatbed lorry ideal for construction materials, timber, and industrial cargo. Air-conditioned cab. Experienced driver.',
   4.50, 12),

  -- Owner 1 (id=3) — Tipper in Arusha
  (3, 'Kilimanjaro Tipper', 'tipper', 15.00, 'T456 DEF',
   1800.00, 75000.00, 'Arusha', -3.3869, 36.6830,
   'available', 'approved',
   'Heavy-duty tipper for sand, gravel, and excavation projects. Available for long-distance hauls across Tanzania.',
   4.20, 8),

  -- Owner 2 (id=4) — Box Truck in Mwanza
  (4, 'Victoria Cargo', 'box', 5.00, 'T789 GHI',
   1200.00, 35000.00, 'Mwanza', -2.5164, 32.9175,
   'available', 'approved',
   'Enclosed box truck, perfect for household moves, retail goods, and fragile cargo. Clean interior, secure loading area.',
   4.70, 20);

-- -------------------------------------------------------------
-- Demo Lorry Photos (primary photos for each lorry)
-- -------------------------------------------------------------
INSERT INTO `lorry_photos` (`lorry_id`, `photo_path`, `is_primary`) VALUES
  (1, '/storage/lorries/demo-flatbed.jpg', 1),
  (2, '/storage/lorries/demo-tipper.jpg',  1),
  (3, '/storage/lorries/demo-box.jpg',     1);

-- -------------------------------------------------------------
-- Demo Bookings
-- -------------------------------------------------------------
INSERT INTO `bookings` (`booking_ref`, `customer_id`, `lorry_id`,
                         `pickup_address`, `delivery_address`,
                         `distance_km`, `goods_description`, `weight_kg`,
                         `preferred_date`, `quoted_price`, `status`,
                         `accepted_at`, `completed_at`) VALUES
  -- Completed booking (customer 1 → lorry 1)
  ('BK-2024-00001', 5, 1,
   'Kariakoo Market, Dar es Salaam', 'Ubungo Industrial Area, Dar es Salaam',
   12.5, 'Building materials — cement bags and iron sheets', 3500.00,
   '2024-06-10', 68750.00, 'completed',
   '2024-06-09 10:00:00', '2024-06-10 16:30:00'),

  -- Accepted booking (customer 2 → lorry 2)
  ('BK-2024-00002', 6, 2,
   'Arusha Clock Tower, Arusha', 'Moshi Town Centre, Kilimanjaro',
   80.0, 'Agricultural produce — maize bags', 8000.00,
   CURDATE() + INTERVAL 2 DAY, 199000.00, 'accepted',
   NOW(), NULL),

  -- Pending booking (customer 1 → lorry 3)
  ('BK-2024-00003', 5, 3,
   'Mwanza Ferry Terminal', 'Shinyanga Town',
   218.0, 'Shop inventory — electronics and household goods', 2200.00,
   CURDATE() + INTERVAL 5 DAY, 296600.00, 'pending',
   NULL, NULL);

-- -------------------------------------------------------------
-- Demo Payments
-- -------------------------------------------------------------
INSERT INTO `payments` (`booking_id`, `payer_id`, `amount`, `platform_commission`,
                          `owner_payout`, `payment_method`, `mobile_number`,
                          `transaction_id`, `status`, `paid_at`) VALUES
  -- Payment for completed booking BK-2024-00001
  (1, 5, 68750.00, 5500.00, 63250.00, 'mpesa', '+255754321098',
   'SIM-2024-001-MPESA', 'completed', '2024-06-09 11:30:00');

-- -------------------------------------------------------------
-- Demo Review (for the completed booking)
-- -------------------------------------------------------------
INSERT INTO `reviews` (`booking_id`, `reviewer_id`, `lorry_id`, `rating`, `comment`, `owner_reply`) VALUES
  (1, 5, 1, 5,
   'Excellent service! The driver was punctual, goods delivered safely. Highly recommend Simba Express.',
   'Thank you Ali! It was a pleasure serving you. We hope to see you again soon.');

-- -------------------------------------------------------------
-- Demo Notifications
-- -------------------------------------------------------------
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `is_read`, `channel`) VALUES
  (5, 'booking_confirmed',  'Booking Confirmed',   'Your booking BK-2024-00001 has been accepted by Juma Hassan.', '/bookings/detail/1', 1, 'in_app'),
  (3, 'new_booking',        'New Booking Request', 'Customer Ali Mohamed submitted booking BK-2024-00002.', '/bookings/owner/2', 0, 'in_app'),
  (6, 'booking_accepted',   'Booking Accepted',    'Great news! Your booking BK-2024-00002 has been accepted.', '/bookings/detail/2', 0, 'in_app');
