

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 資料庫： `projectdb`
--

-- --------------------------------------------------------

--
-- 資料表結構 `category`
--

CREATE TABLE `category` (
  `cid` int(11) NOT NULL,
  `cname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `category`
--

INSERT INTO `category` (`cid`, `cname`) VALUES
(1, 'Car'),
(2, 'Plane'),
(3, 'Robot'),
(4, 'Helicopter'),
(5, 'Cat Robot');

-- --------------------------------------------------------

--
-- 資料表結構 `customer`
--

CREATE TABLE `customer` (
  `cid` int(11) NOT NULL,
  `cname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cpassword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ctel` int(11) DEFAULT NULL,
  `caddr` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `customer`
--

INSERT INTO `customer` (`cid`, `cname`, `cpassword`, `ctel`, `caddr`, `company`) VALUES
(1, 'Alex Wong', 'itp4235m', 21232123, 'G/F, ABC Building, King Yip Street, KwunTong, Kowloon, Hong Kong', 'Fat Cat Company Limited'),
(2, 'Tina Chan', 'itp4235m', 31233123, '303, Mei Hing Center, Yuen Long, NT, Hong Kong', 'XDD LOL Company'),
(3, 'Bowie', 'itp4235m', 61236123, '401, Sing Kei Building, Kowloon, Hong Kong', 'GPA4 Company'),
(4, 'testC1', '$2y$10$QgYPx9HK7dLVokUjANbYg.OtuJFm1rhYeFbUYuCbcSE1gL27pUOiS', 12345679, 'HSBC Shek Mun Building', 'testC1'),
(5, 'testC2', '$2y$10$iZ7nqKZvTZ/A7MoNTCcwIOGz.K0pPz0D7jyJI4NRKwai5hMV2a876', 22345678, 'Kings Wing Plaza Phase 1', 'testC2');

-- --------------------------------------------------------

--
-- 資料表結構 `customize`
--

CREATE TABLE `customize` (
  `cid` int(11) NOT NULL,
  `oid` int(11) NOT NULL,
  `customize_color` varchar(255) DEFAULT NULL,
  `customize_desc` text DEFAULT NULL,
  `customize_image` varchar(255) DEFAULT NULL,
  `customize_other_field` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `quote_status` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `customize`
--

INSERT INTO `customize` (`cid`, `oid`, `customize_color`, `customize_desc`, `customize_image`, `customize_other_field`, `created_at`, `quote_status`) VALUES
(4, 9, '', 'Hi, I’d love to create a one-of-a-kind plush toy for my daughter’s birthday. I’m thinking of a flying dinosaur robot hybrid — soft and safe, but with LED eyes and detachable wings. She adores bright colors like teal and lavender, and I’d like it to make gentle sounds when hugged. Ideally, the material should be eco-friendly, and it would be great if we can embroider her name “Maya” on its belly. My budget is around $60, and I’d need just one unit. Can you let me know if this is possible and how long production would take?', NULL, NULL, '2025-07-08 01:02:49', 0),
(5, 11, '0', '', NULL, NULL, '2025-07-08 01:10:06', 0),
(7, 13, '', 'Hi, I’d love to create a one-of-a-kind plush toy for my daughter’s birthday. I’m thinking of a flying dinosaur robot hybrid — soft and safe, but with LED eyes and detachable wings. She adores bright colors like teal and lavender, and I’d like it to make gentle sounds when hugged. Ideally, the material should be eco-friendly, and it would be great if we can embroider her name “Maya” on its belly. My budget is around $60, and I’d need just one unit. Can you let me know if this is possible and how long production would take?', NULL, NULL, '2025-07-08 03:26:46', 0),
(8, 14, '', 'Hi, I’d love to create a one-of-a-kind plush toy for my daughter’s birthday. I’m thinking of a flying dinosaur robot hybrid — soft and safe, but with LED eyes and detachable wings. She adores bright colors like teal and lavender, and I’d like it to make gentle sounds when hugged. Ideally, the material should be eco-friendly, and it would be great if we can embroider her name “Maya” on its belly. My budget is around $60, and I’d need just one unit. Can you let me know if this is possible and how long production would take?', NULL, NULL, '2025-07-08 03:35:09', 0),
(9, 16, '', 'Hi, I’d love to create a one-of-a-kind plush toy for my daughter’s birthday. I’m thinking of a flying dinosaur robot hybrid — soft and safe, but with LED eyes and detachable wings. She adores bright colors like teal and lavender, and I’d like it to make gentle sounds when hugged. Ideally, the material should be eco-friendly, and it would be great if we can embroider her name “Maya” on its belly. My budget is around $60, and I’d need just one unit. Can you let me know if this is possible and how long production would take?', NULL, NULL, '2025-07-08 04:33:54', 0),
(10, 17, '', 'Hi, I’d love to create a one-of-a-kind plush toy for my daughter’s birthday. I’m thinking of a flying dinosaur robot hybrid — soft and safe, but with LED eyes and detachable wings. She adores bright colors like teal and lavender, and I’d like it to make gentle sounds when hugged. Ideally, the material should be eco-friendly, and it would be great if we can embroider her name “Maya” on its belly. My budget is around $60, and I’d need just one unit. Can you let me know if this is possible and how long production would take?', NULL, NULL, '2025-07-08 15:58:11', 0);

-- --------------------------------------------------------

--
-- 資料表結構 `feedback`
--

CREATE TABLE `feedback` (
  `fid` int(11) NOT NULL,
  `oid` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `feedback`
--

INSERT INTO `feedback` (`fid`, `oid`, `feedback_text`, `created_at`, `rating`) VALUES
(1, 13, 'it\'s good.', '2025-07-08 17:38:18', 5),
(2, 11, 'it\'s good.', '2025-07-08 17:45:53', 5),
(3, 18, 'It\'s good!!!!!!', '2025-07-08 21:47:15', 5),
(4, 19, 'It\'s good!!!!!!', '2025-07-08 21:47:21', 5),
(5, 20, 'It\'s bad', '2025-07-08 21:48:48', 1),
(6, 21, 'It\'s good!!!!!!', '2025-07-08 21:49:08', 5),
(7, 22, 'It\'s ok.', '2025-07-08 21:49:27', 3),
(8, 23, 'It\'s good!!!!!!', '2025-07-08 21:49:38', 5),
(9, 24, 'It\'s bad', '2025-07-08 21:49:52', 1),
(10, 25, 'It\'s good!!!!!!', '2025-07-08 21:50:02', 5);

-- --------------------------------------------------------

--
-- 資料表結構 `material`
--

CREATE TABLE `material` (
  `mid` int(11) NOT NULL,
  `mname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mqty` int(11) NOT NULL,
  `mrqty` int(11) NOT NULL,
  `munit` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mreorderqty` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `material`
--

INSERT INTO `material` (`mid`, `mname`, `mqty`, `mrqty`, `munit`, `mreorderqty`, `is_deleted`) VALUES
(1, 'Rubber 3233', 98755, 1000, 'KG', 10000, 0),
(2, 'Cotten CDC24', 4490, 400, 'KG', 600, 0),
(3, 'Wood RAW77', 1832, 1000, 'KG', 1200, 0),
(4, 'ABS LL Chem 5026', 1190, 200, 'KG', 400, 0),
(5, '4 x 1 Flat Head Stainless Steel Screws', 6202, 200, 'PC', 1700, 0),
(7, 'cpu', 99814, 3000, 'PC', 500, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `orders`
--

CREATE TABLE `orders` (
  `oid` int(11) NOT NULL,
  `odate` datetime NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `oqty` int(11) NOT NULL,
  `ocost` decimal(20,2) NOT NULL,
  `cid` int(11) NOT NULL,
  `odeliverdate` datetime DEFAULT NULL,
  `ostatus` int(11) NOT NULL,
  `material_selected` int(11) DEFAULT NULL,
  `custom_desc` text DEFAULT NULL,
  `pay_currency` varchar(10) DEFAULT NULL,
  `pay_amount` decimal(20,2) DEFAULT NULL,
  `staff_response` text DEFAULT NULL,
  `design_image` varchar(255) DEFAULT NULL,
  `delivery_status` int(11) NOT NULL DEFAULT 0 COMMENT '0=Not shipped, 1=Shipped, 2=In transit, 3=Delivered',
  `quote_status` int(11) NOT NULL DEFAULT 0 COMMENT '0=pending,1=accepted,2=rejected,9=deleted',
  `quote_reject_count` int(11) NOT NULL DEFAULT 0,
  `current_quote_value` decimal(10,2) DEFAULT NULL,
  `current_estimated_date` date DEFAULT NULL,
  `customer_expected_budget` decimal(10,2) DEFAULT NULL,
  `customer_expected_date` date DEFAULT NULL,
  `quote_accepted` tinyint(1) DEFAULT NULL,
  `quote_round` tinyint(1) DEFAULT 0,
  `region` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `orders`
--

INSERT INTO `orders` (`oid`, `odate`, `pid`, `oqty`, `ocost`, `cid`, `odeliverdate`, `ostatus`, `material_selected`, `custom_desc`, `pay_currency`, `pay_amount`, `staff_response`, `design_image`, `delivery_status`, `quote_status`, `quote_reject_count`, `current_quote_value`, `current_estimated_date`, `customer_expected_budget`, `customer_expected_date`, `quote_accepted`, `quote_round`, `region`) VALUES
(9, '2025-07-07 19:02:49', NULL, 1, 0.00, 4, NULL, 2, NULL, NULL, 'USD', 60.00, NULL, '', 0, 0, 0, 60.00, NULL, NULL, NULL, 1, 0, NULL),
(11, '2025-07-07 19:10:06', 1, 2, 39.00, 4, NULL, 2, NULL, NULL, 'USD', 39.00, NULL, NULL, 2, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(13, '2025-07-07 21:26:46', NULL, 1, 0.00, 4, NULL, 2, NULL, NULL, 'USD', 0.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(14, '2025-07-07 21:35:09', NULL, 1, 0.00, 4, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(15, '2025-07-07 22:32:54', 1, 2, 39.00, 4, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(16, '2025-07-07 22:33:54', NULL, 3, 0.00, 4, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(17, '2025-07-08 09:58:11', NULL, 1, 0.00, 4, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 60.00, '2025-07-31', NULL, 0, NULL),
(18, '2025-07-08 15:46:44', 1, 130, 2587.00, 5, NULL, 2, NULL, NULL, 'USD', 2587.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(19, '2025-07-08 15:46:44', 2, 403, 3989.00, 5, NULL, 2, NULL, NULL, 'USD', 3989.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(20, '2025-07-08 15:46:44', 3, 40, 9996.00, 5, NULL, 2, NULL, NULL, 'USD', 9996.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(21, '2025-07-08 15:46:44', 4, 45, 1350.00, 5, NULL, 2, NULL, NULL, 'USD', 1350.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(22, '2025-07-08 15:46:44', 5, 50, 24950.00, 5, NULL, 2, NULL, NULL, 'USD', 24950.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(23, '2025-07-08 15:46:44', 6, 65, 6500.00, 5, NULL, 2, NULL, NULL, 'USD', 6500.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(24, '2025-07-08 15:46:44', 8, 54, 1074.00, 5, NULL, 2, NULL, NULL, 'USD', 1074.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(25, '2025-07-08 15:46:44', 9, 64, 633.00, 5, NULL, 2, NULL, NULL, 'USD', 633.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(26, '2025-07-08 15:46:44', 10, 42, 10495.00, 5, NULL, 2, NULL, NULL, 'USD', 10495.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(27, '2025-07-08 15:46:44', 11, 45, 1350.00, 5, NULL, 2, NULL, NULL, 'USD', 1350.00, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `prodmat`
--

CREATE TABLE `prodmat` (
  `pid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `pmqty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `prodmat`
--

INSERT INTO `prodmat` (`pid`, `mid`, `pmqty`) VALUES
(1, 4, 1),
(1, 5, 6),
(2, 3, 10),
(2, 5, 2),
(3, 1, 10),
(3, 5, 1),
(4, 1, 5),
(4, 5, 8),
(5, 2, 1),
(6, 1, 1),
(6, 2, 5),
(6, 4, 1),
(7, 1, 1),
(7, 2, 5),
(7, 4, 1),
(8, 4, 1),
(8, 7, 1),
(9, 3, 2),
(9, 5, 1),
(10, 1, 10),
(10, 7, 1),
(11, 1, 3),
(11, 7, 1),
(12, 2, 3),
(12, 7, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `product`
--

CREATE TABLE `product` (
  `pid` int(11) NOT NULL,
  `pname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `pdesc` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pcost` decimal(12,2) NOT NULL,
  `cid` int(11) DEFAULT NULL,
  `default_mid` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `product`
--

INSERT INTO `product` (`pid`, `pname`, `pdesc`, `pcost`, `cid`, `default_mid`, `price`, `is_deleted`) VALUES
(1, 'Cyberpunk Truck C204', 'Explore the world of imaginative play with our vibrant and durable toy truck. Perfect for little hands, this truck will inspire endless storytelling adventures both indoors and outdoors. Made from high-quality materials, it is built to withstand hours of creative playtime.', 19.90, 1, 4, 0.00, 0),
(2, 'XDD Wooden Plane', 'Take to the skies with our charming wooden plane toy. Crafted from eco-friendly and child-safe materials, this beautifully designed plane sparks the imagination and encourages interactive play. With smooth edges and a sturdy construction, it\'s a delightful addition to any young aviator\'s toy collection.', 9.90, 2, 3, 0.00, 0),
(3, 'iRobot 3233GG', 'Introduce your child to the wonders of technology and robotics with our smart robot companion. Packed with interactive features and educational benefits, this futuristic toy engages curious minds and promotes STEM learning in a fun and engaging way. Watch as your child explores coding, problem-solving, and innovation with this cutting-edge robot friend.', 249.90, 3, 1, 0.00, 0),
(4, 'Apex Ball Ball Helicopter M1297', 'Experience the thrill of flight with our ball helicopter toy. Easy to launch and navigate, this exciting toy provides hours of entertainment for children of all ages. With colorful LED lights and a durable design, it\'s a fantastic outdoor toy that brings joy and excitement to playtime.', 30.00, 4, 1, 0.00, 0),
(5, 'RoboKat AI Cat Robot', 'Meet our AI Cat Robot – the purr-fect blend of technology and cuddly companionship. This interactive robotic feline offers lifelike movements, sounds, and responses, providing a realistic pet experience without the hassle. With customizable features and playful interactions, this charming cat robot is a delightful addition to your child\'s playroom.', 499.00, 5, 2, 0.00, 0),
(6, 'Fuzzy Dino Buddy', 'Meet your child’s new prehistoric pal! This adorable plush dinosaur toy is handcrafted with ultra-soft fabric and detailed stitching for maximum cuddle power. Designed for children ages 3 and up, it’s lightweight, safe, and built to withstand adventure after adventure. Whether it\'s bedtime snuggles or daytime play, this dino brings Jurassic joy to every moment.', 100.00, NULL, 2, 100.00, 0),
(7, 'test1', 'gfdgdhfgh', 100.00, NULL, 2, 100.00, 1),
(8, 'Cyberpunk Truck', 'Truck	Vibrant, durable toy truck for creative play.', 19.90, NULL, 4, 19.90, 0),
(9, 'Wooden Plane', 'Eco-friendly wooden plane, safe and fun for kids.', 9.90, NULL, 3, 9.90, 0),
(10, 'iRobot', 'Smart robot companion, interactive and educational.', 249.90, NULL, 1, 249.90, 0),
(11, 'Ball Helicopter', 'Easy-to-fly ball helicopter with LED lights.', 30.00, NULL, 1, 30.00, 0),
(12, 'RoboKat AI Cat mini', 'Interactive AI cat robot, lifelike and customizable.', 499.00, NULL, 1, 499.00, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `staff`
--

CREATE TABLE `staff` (
  `sid` int(11) NOT NULL,
  `spassword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `sname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `srole` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `stel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `staff`
--

INSERT INTO `staff` (`sid`, `spassword`, `sname`, `srole`, `stel`) VALUES
(1, 'itp4523m', 'Hachi Leung', 'admin', 25669197),
(2, '$2y$10$dIkDhbbOoBEW6yqsTWfuBOcE9ppW6NplIIOrqpYadueOkMG6SZ3P.', 'Mary Chan', 'staff', 98765432);

-- --------------------------------------------------------

--
-- 資料表結構 `staff_response`
--

CREATE TABLE `staff_response` (
  `srid` int(11) NOT NULL,
  `oid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `response_text` text DEFAULT NULL,
  `design_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `quote_value` decimal(10,2) DEFAULT NULL,
  `estimated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `staff_response`
--

INSERT INTO `staff_response` (`srid`, `oid`, `sid`, `response_text`, `design_image`, `created_at`, `quote_value`, `estimated_date`) VALUES
(1, 11, 2, '', '', '2025-07-08 03:12:51', NULL, NULL),
(2, 11, 2, '', '', '2025-07-08 03:12:58', NULL, NULL),
(3, 16, 2, 'ok', 'design_16_1751921594.jpg', '2025-07-08 04:53:14', NULL, NULL),
(4, 16, 2, '', '', '2025-07-08 04:54:19', NULL, NULL),
(5, 15, 2, '', '', '2025-07-08 14:47:50', NULL, NULL),
(6, 16, 2, 'ok', 'design_16_1751959905.jpg', '2025-07-08 15:31:45', NULL, NULL),
(7, 17, 2, 'juyydjdjyhjkhgjyj', 'design_17_1751961554.jpg', '2025-07-08 15:59:14', NULL, NULL),
(8, 9, 2, 'Thank you for your wonderfully creative request! We absolutely love the idea of a flying dinosaur robot hybrid — it sounds like the perfect birthday companion for Maya. 🦕✨\r\n\r\nWe can definitely work with teal and lavender tones, soft eco-friendly fabrics, and include features like LED eyes, gentle hug-activated sounds, and detachable wings. Embroidering her name on the belly is no problem at all. For one unit with all these features, your $60 budget is reasonable, and we estimate production will take approximately 10–12 business days.\r\n\r\nWe’ll upload a design sketch shortly for your approval. Stay tuned!', 'design_9_1751963814.jpg', '2025-07-08 16:36:54', NULL, NULL),
(9, 9, 2, 'Thank you for your wonderfully creative request! We absolutely love the idea of a flying dinosaur robot hybrid — it sounds like the perfect birthday companion for Maya. 🦕✨\r\n\r\nWe can definitely work with teal and lavender tones, soft eco-friendly fabrics, and include features like LED eyes, gentle hug-activated sounds, and detachable wings. Embroidering her name on the belly is no problem at all. For one unit with all these features, your $60 budget is reasonable, and we estimate production will take approximately 10–12 business days.\r\n\r\nWe’ll upload a design sketch shortly for your approval. Stay tuned!', 'design_9_1751964315.jpg', '2025-07-08 16:45:15', NULL, NULL),
(10, 9, 2, 'Thank you for your wonderfully creative request! We absolutely love the idea of a flying dinosaur robot hybrid — it sounds like the perfect birthday companion for Maya. 🦕✨\r\n\r\nWe can definitely work with teal and lavender tones, soft eco-friendly fabrics, and include features like LED eyes, gentle hug-activated sounds, and detachable wings. Embroidering her name on the belly is no problem at all. For one unit with all these features, your $60 budget is reasonable, and we estimate production will take approximately 10–12 business days.\r\n\r\nWe’ll upload a design sketch shortly for your approval. Stay tuned!', 'design_9_1751964637.jpg', '2025-07-08 16:50:37', NULL, NULL),
(11, 9, 2, 'Thank you for your wonderfully creative request! We absolutely love the idea of a flying dinosaur robot hybrid — it sounds like the perfect birthday companion for Maya. 🦕✨\r\n\r\nWe can definitely work with teal and lavender tones, soft eco-friendly fabrics, and include features like LED eyes, gentle hug-activated sounds, and detachable wings. Embroidering her name on the belly is no problem at all. For one unit with all these features, your $60 budget is reasonable, and we estimate production will take approximately 10–12 business days.\r\n\r\nWe’ll upload a design sketch shortly for your approval. Stay tuned!', 'design_9_1751964810.jpg', '2025-07-08 16:53:30', NULL, NULL),
(12, 9, 2, '', 'design_9_1751965791.jpg', '2025-07-08 17:09:51', NULL, NULL),
(13, 9, 2, '', '', '2025-07-08 17:14:06', NULL, NULL);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cid`);

--
-- 資料表索引 `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cid`);

--
-- 資料表索引 `customize`
--
ALTER TABLE `customize`
  ADD PRIMARY KEY (`cid`),
  ADD KEY `customize_ibfk_1` (`oid`);

--
-- 資料表索引 `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`fid`),
  ADD KEY `oid` (`oid`);

--
-- 資料表索引 `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`mid`);

--
-- 資料表索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`oid`),
  ADD KEY `pid_PK_idx` (`pid`),
  ADD KEY `cid_pk_idx` (`cid`);

--
-- 資料表索引 `prodmat`
--
ALTER TABLE `prodmat`
  ADD PRIMARY KEY (`pid`,`mid`),
  ADD KEY `mid_fk_idx` (`mid`);

--
-- 資料表索引 `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`pid`);

--
-- 資料表索引 `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`sid`);

--
-- 資料表索引 `staff_response`
--
ALTER TABLE `staff_response`
  ADD PRIMARY KEY (`srid`),
  ADD KEY `oid` (`oid`),
  ADD KEY `sid` (`sid`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `category`
--
ALTER TABLE `category`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `customer`
--
ALTER TABLE `customer`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `customize`
--
ALTER TABLE `customize`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `feedback`
--
ALTER TABLE `feedback`
  MODIFY `fid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `material`
--
ALTER TABLE `material`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `orders`
--
ALTER TABLE `orders`
  MODIFY `oid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `product`
--
ALTER TABLE `product`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `staff`
--
ALTER TABLE `staff`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `staff_response`
--
ALTER TABLE `staff_response`
  MODIFY `srid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `customize`
--
ALTER TABLE `customize`
  ADD CONSTRAINT `customize_ibfk_1` FOREIGN KEY (`oid`) REFERENCES `orders` (`oid`) ON DELETE CASCADE;

--
-- 資料表的限制式 `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`oid`) REFERENCES `orders` (`oid`);

--
-- 資料表的限制式 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `cid_pk` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`),
  ADD CONSTRAINT `pid_pk` FOREIGN KEY (`pid`) REFERENCES `product` (`pid`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 資料表的限制式 `prodmat`
--
ALTER TABLE `prodmat`
  ADD CONSTRAINT `mid_fk` FOREIGN KEY (`mid`) REFERENCES `material` (`mid`),
  ADD CONSTRAINT `pid_fk` FOREIGN KEY (`pid`) REFERENCES `product` (`pid`);

--
-- 資料表的限制式 `staff_response`
--
ALTER TABLE `staff_response`
  ADD CONSTRAINT `staff_response_ibfk_1` FOREIGN KEY (`oid`) REFERENCES `orders` (`oid`),
  ADD CONSTRAINT `staff_response_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `staff` (`sid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
