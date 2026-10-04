<?php
// Zenkit Database Configuration & Auto-Provisioning
// Supports MySQL / MariaDB and automatic zero-config SQLite PDO fallback

define('DB_TYPE', 'sqlite'); // 'mysql' or 'sqlite'
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'zenkit_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbType = DB_TYPE;
    $sqlitePath = __DIR__ . '/../database/zenkit.sqlite';

    if ($dbType === 'mysql') {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            error_log("MySQL connection failed, falling back to SQLite: " . $e->getMessage());
            $dbType = 'sqlite';
        }
    }

    $isNewDb = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;
    
    if (!is_dir(dirname($sqlitePath))) {
        mkdir(dirname($sqlitePath), 0777, true);
    }

    $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $pdo->exec("PRAGMA foreign_keys = ON;");

    if ($isNewDb) {
        seedDatabase($pdo);
    } else {
        try {
            $test = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            if ($test == 0) {
                seedDatabase($pdo);
            }
        } catch (Exception $e) {
            seedDatabase($pdo);
        }
    }

    // Ensure business tables are created
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_inquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(30),
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(30) DEFAULT 'New',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(150) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS wishlist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
            UNIQUE(user_id, product_id)
        );
    ");

    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN options_json TEXT");
    } catch (Exception $e) {
        // Column already exists
    }

    return $pdo;
}

function seedDatabase(PDO $pdo) {
    // 1. Run Schema
    $schemaFile = __DIR__ . '/../schema.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
    }

    // 2. Seed Categories
    $catStmt = $pdo->prepare("INSERT OR REPLACE INTO categories (id, name, slug, description, icon) VALUES (?, ?, ?, ?, ?)");
    $categories = [
        [1, 'Laptops & PCs', 'laptops-pcs', 'High performance ultra-thin notebooks and creative workstations.', 'laptop'],
        [2, 'Audio & Earbuds', 'audio-earbuds', 'Active noise cancelling wireless earbuds and spatial headphones.', 'headphones'],
        [3, 'Smartwatches', 'smartwatches', 'AMOLED fitness trackers, heart-rate monitors and calling smartwatches.', 'watch'],
        [4, 'Keyboards & Gaming', 'keyboards-gaming', 'Mechanical tactile keyboards, gaming mice and ergonomic desk gear.', 'keyboard'],
        [5, 'Smart Ambient Lamps', 'smart-lamps', 'Touch-control dimmable LED desk lights and ambient lighting systems.', 'sun']
    ];
    foreach ($categories as $cat) {
        $catStmt->execute($cat);
    }

    // 3. Seed Consumer Tech Products
    $prodStmt = $pdo->prepare("INSERT OR REPLACE INTO products 
        (id, category_id, name, slug, subtitle, description, specs_json, price, original_price, stock, rating, reviews_count, is_featured, is_bestseller, image_url, model_3d_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $products = [
        [
            1,
            1,
            'Zenkit ProBook Ultrathin 15 (16GB RAM / 512GB SSD / Intel Core i7)',
            'zenkit-probook-ultrathin-15',
            '15.6" QHD 120Hz IPS Display • Aerospace Aluminum Chassis • All-day 14h Battery',
            'Engineered for creators, developers, and everyday productivity. The Zenkit ProBook Ultrathin 15 combines a featherlight aerospace aluminum unibody with a 15.6-inch 2.5K 120Hz display, backlit precision scissor keyboard, Thunderbolt 4 connectivity, and 65W fast USB-C PD charging.',
            json_encode([
                'Processor' => 'Intel Core i7 13th Gen (10 Cores, up to 4.9 GHz)',
                'RAM & Storage' => '16GB LPDDR5 5200MHz RAM + 512GB NVMe PCIe Gen 4 SSD',
                'Display' => '15.6" QHD (2560x1440) Anti-Glare IPS 100% sRGB',
                'Battery & Charger' => '70Wh Li-Ion • 65W Fast Type-C Charger included',
                'Weight & Build' => '1.38 kg • Space Gray Anodized Aluminum'
            ]),
            64999.00,
            84999.00,
            24,
            4.85,
            1420,
            1,
            1,
            'assets/images/products/zen_laptop.jpg',
            'keyboard'
        ],
        [
            2,
            2,
            'Zenkit AeroPods Pro ANC True Wireless Earbuds',
            'zenkit-aeropods-pro-anc',
            '45dB Hybrid Active Noise Cancellation • 38h Battery • Low Latency Gaming Mode',
            'Experience immersive acoustic separation with the Zenkit AeroPods Pro. Equipped with 11mm titanium diaphragm drivers, 4-mic ENC for crystal-clear HD calling, spatial audio surround, and a compact wireless charging case with fast USB-C input.',
            json_encode([
                'Driver Unit' => '11mm Dynamic Titanium Composite Diaphragm',
                'Noise Cancellation' => '45dB Hybrid Active Noise Cancellation (ANC)',
                'Battery Life' => '8.5 Hours on Buds / 38 Hours with Charging Case',
                'Water Resistance' => 'IPX5 Sweat & Water Resistant',
                'Bluetooth Version' => 'Bluetooth 5.3 with AAC / SBC Low-Latency'
            ]),
            2999.00,
            4999.00,
            65,
            4.80,
            3890,
            1,
            1,
            'assets/images/products/zen_buds.jpg',
            'buds'
        ],
        [
            3,
            3,
            'Zenkit ChronoFit AMOLED Calling Smartwatch',
            'zenkit-chronofit-amoled-smartwatch',
            '1.96" Ultra-Bright AMOLED • Bluetooth Calling • 120+ Sports Modes • 10-Day Battery',
            'Elevate your daily health telemetry and fitness tracking. The Zenkit ChronoFit features a curved sapphire glass 1.96" AMOLED always-on display, 24/7 SpO2 and continuous heart-rate tracking, high-fidelity Bluetooth calling speaker/mic, and rugged IP68 water resistance.',
            json_encode([
                'Display' => '1.96" Super AMOLED 410x502 px (1,000 Nits Brightness)',
                'Health Telemetry' => 'Heart Rate, Blood Oxygen (SpO2), Sleep & Stress Monitor',
                'Calling Feature' => 'Built-in HD Speaker & AI Noise Cancelling Mic',
                'Battery Backup' => 'Up to 10 Days Regular / 4 Days Heavy Bluetooth Calling',
                'Casing & Strap' => 'Titanium Grey Metal Alloy with Quick-Release Strap'
            ]),
            4499.00,
            6999.00,
            42,
            4.90,
            2140,
            1,
            1,
            'assets/images/products/zen_watch.jpg',
            'watch'
        ],
        [
            4,
            4,
            'Zenkit Tactile RGB Mechanical Gaming Keyboard',
            'zenkit-tactile-rgb-mechanical-keyboard',
            'Custom Hot-Swappable Switches • PBT Pudding Keycaps • Multi-Device BT/2.4G/Wired',
            'Built for typists and gamers alike. Features factory pre-lubed mechanical switches with gold contacts for smooth actuation, customizable per-key RGB backlighting, sound-dampening silicone internal pads, and full multi-device pairing support.',
            json_encode([
                'Switch Type' => 'Zen Custom Pre-Lubed Red Linear / Brown Tactile',
                'Keycaps' => 'Double-Shot PBT Frosted Translucent Pudding Caps',
                'Connectivity' => 'Tri-Mode: 2.4GHz Wireless, Bluetooth 5.0 & Braided Type-C',
                'Hot-Swappable' => 'Full 5-Pin Hot-Swap Switch Sockets',
                'Battery' => '4,000 mAh Rechargeable Li-Ion (Up to 200h without RGB)'
            ]),
            3999.00,
            5999.00,
            35,
            4.92,
            1840,
            1,
            0,
            'assets/images/products/zen_key.jpg',
            'keyboard'
        ],
        [
            5,
            5,
            'Zenkit Halo Smart Touch LED Desk Lamp',
            'zenkit-halo-smart-touch-led-desk-lamp',
            'Eye-Caring Stepless Dimming • 5 Color Temps • Dual-Axis Adjustable Arm • USB Charging',
            'A modern minimalist illumination solution for your workspace. The Zenkit Halo Smart Lamp produces flicker-free, non-glare circular lighting designed to eliminate eye strain during long study or coding sessions. Includes sensitive touch slider for brightness and phone charging port.',
            json_encode([
                'Light Source' => 'Premium Full-Spectrum LED (CRI > 95 Ra)',
                'Brightness & Temp' => 'Stepless 10% - 100% / 2700K Warm to 6500K Cool White',
                'Flexibility' => 'Dual-Axis 180° Foldable Metal Arm & 360° Rotatable Head',
                'Power & Output' => '12W High-Efficiency / Includes 5V 2A USB Output Port',
                'Material' => 'Anodized Aviation Aluminum + Weighted Non-Slip Base'
            ]),
            1999.00,
            2999.00,
            50,
            4.88,
            960,
            1,
            1,
            'assets/images/products/zen_lamp.jpg',
            'orb'
        ],
        [
            6,
            4,
            'Zenkit Aerox Ultralight Wireless Optical Gaming Mouse',
            'zenkit-aerox-ultralight-wireless-mouse',
            '58g Lightweight Shell • 26,000 DPI Optical Sensor • 100% Pure PTFE Skates',
            'Crafted for lightning-fast reflexes and all-day ergonomic comfort. Weighing just 58 grams, the Aerox features zero-smoothing optical tracking up to 26,000 DPI, optical micro-switches rated for 80 million clicks, and smooth glide curved PTFE feet.',
            json_encode([
                'Weight' => '58 Grams Ultralight Balanced Structure',
                'Sensor' => 'Zen Optical 26K High-Precision Sensor (650 IPS)',
                'Battery' => 'Up to 90 Hours Continuous High-Performance Gaming',
                'Polling Rate' => '1,000Hz (1ms Response Time)',
                'Charging' => 'Superflex Paracord Type-C Quick Charging Cable'
            ]),
            1499.00,
            2499.00,
            70,
            4.79,
            1520,
            0,
            0,
            'assets/images/products/zen_mouse.jpg',
            'mouse'
        ],
        [
            7,
            2,
            'Zenkit SpatialStudio Pro ANC Wireless Over-Ear Headphones',
            'zenkit-spatialstudio-pro-anc-headphones',
            'Lossless Spatial Audio • 40mm Beryllium Drivers • 60-Hour Playtime • Memory Foam Cups',
            'Studio-grade acoustic transparency and deep immersive bass. Equipped with 40mm custom beryllium diaphragms, multi-mic adaptive ANC, ultra-low latency wireless mode, and plush memory foam protein leather earcups.',
            json_encode([
                'Drivers' => '40mm Custom Beryllium Diaphragm Drivers',
                'ANC' => 'Adaptive Hybrid Active Noise Cancellation with Transparency Mode',
                'Battery Life' => 'Up to 60 Hours Playback (Fast Charge 10m = 5h)',
                'Audio Codecs' => 'LDAC, AAC, SBC, aptX HD Lossless Support',
                'Weight' => '245 Grams Lightweight Ergonomic Metal Band'
            ]),
            7999.00,
            11999.00,
            38,
            4.93,
            1120,
            1,
            1,
            'assets/images/products/zen_headset.jpg',
            'buds'
        ],
        [
            8,
            5,
            'Zenkit Nebula Interactive Smart RGB Ambient Orb',
            'zenkit-nebula-smart-rgb-ambient-orb',
            '16M Color Dynamic Sync • Music Visualizer • App & Touch Control • Magnetic Base',
            'Transform your desktop atmosphere with fluid RGB illumination. Features 16 million colors with dynamic music beat-sync, touch-sensitive mode toggling, magnetic 360-degree rotation base, and rechargeable battery for cordless use.',
            json_encode([
                'Lighting' => '16.8 Million Addressable RGB Colors + Tunable Warm/Cool White',
                'Control' => 'Capacitive Touch Top + Bluetooth App Control',
                'Battery' => '2,600 mAh Rechargeable Li-Ion (Up to 12 Hours Cordless)',
                'Mount' => 'Magnetic 360° Ball Joint Base + Non-Slip Pad',
                'Audio Sync' => 'Built-in High-Sensitivity Acoustic Pickup Sensor'
            ]),
            2499.00,
            3499.00,
            55,
            4.87,
            840,
            1,
            0,
            'assets/images/products/zen_orb.jpg',
            'orb'
        ],
        [
            9,
            1,
            'Zenkit Tab OLED Ultra 11 (128GB / 256GB / Wi-Fi + 5G Cellular)',
            'zenkit-tab-oled-ultra-11',
            '11" 120Hz 2.8K OLED Display • M-Series Octa-Core Chip • Quad Dolby Atmos Speakers',
            'Redefining mobile productivity and artistic creation. The Zenkit Tab OLED Ultra 11 boasts an ultra-vivid 2.8K OLED screen with 120Hz refresh rate and 1,000 nits peak brightness.',
            json_encode([
                'Display' => '11.0" 2.8K (2880x1800) OLED 120Hz ProMotion (1,000 Nits)',
                'Processor' => 'Zen Octa-Core High-Efficiency Architecture',
                'RAM & Storage' => '8GB LPDDR5X / 128GB or 256GB UFS 4.0 Storage',
                'Battery' => '8,200 mAh / 45W Fast Type-C Charging (Up to 14h video)'
            ]),
            499.00,
            629.00,
            30,
            4.95,
            1420,
            1,
            1,
            'assets/images/products/zen_tablet.jpg',
            'laptop'
        ],
        [
            10,
            2,
            'Zenkit Aura 360 Spatial Hi-Fi Bluetooth Smart Speaker',
            'zenkit-aura-360-spatial-hifi-speaker',
            '360° Omnidirectional Room-Filling Audio • Ambient Warm Glow • 50W Peak Output',
            'Fusing acoustic mastery with ambient aesthetics. Expansive 360-degree high-fidelity audio driven by custom neodymium drivers and dual passive radiators.',
            json_encode([
                'Drivers' => '2x 2.5" Neodymium Drivers + Dual Sub-Bass Radiators',
                'Output' => '50W Peak Dynamic Class-D Clean Amplification',
                'Battery' => '5,200 mAh Rechargeable Li-Ion (Up to 24 Hours)',
                'Connectivity' => 'Bluetooth 5.3, Wi-Fi 6, AirPlay 2, Aux 3.5mm'
            ]),
            149.00,
            199.00,
            45,
            4.91,
            980,
            1,
            0,
            'assets/images/products/zen_speaker.jpg',
            'orb'
        ],
        [
            11,
            1,
            'Zenkit VisionCurve 34" WQHD 165Hz Ultrawide Curved Gaming Monitor',
            'zenkit-visioncurve-34-ultrawide-monitor',
            '3440x1440 UWQHD • 1500R Curvature • 1ms GtG • 99% sRGB Color Accuracy',
            'Immerse yourself in cinematic wide-angle gaming and expansive multi-window multitasking with 165Hz refresh rate and 90W USB-C single cable dock.',
            json_encode([
                'Screen' => '34-Inch UltraWide 21:9 Curved VA (1500R Curvature)',
                'Resolution' => '3440 x 1440 Ultra-Sharp WQHD (110 PPI)',
                'Refresh Rate' => '165Hz Refresh Rate with 1ms GtG Response',
                'Ports' => 'USB-C (90W PD), 2x DisplayPort 1.4, 2x HDMI 2.1'
            ]),
            449.00,
            599.00,
            22,
            4.94,
            870,
            1,
            1,
            'assets/images/products/zen_monitor.jpg',
            'keyboard'
        ],
        [
            12,
            4,
            'Zenkit Nexus Elite Pro Wireless Esports Gamepad',
            'zenkit-nexus-elite-pro-wireless-gamepad',
            'Hall Effect Anti-Drift Sticks • Mechanical Microswitch Buttons • Multi-Platform',
            'Engineered for uncompromising competitive tournament play with electromagnetic Hall Effect joysticks and triggers.',
            json_encode([
                'Joysticks' => 'Electromagnetic Hall Effect Sensors (Zero Drift)',
                'Buttons' => 'Mechanical Microswitches (0.3mm Actuation)',
                'Battery' => '1,200 mAh Li-Po (Up to 35 Hours continuous play)',
                'Polling Rate' => '1,000Hz Ultra-Low Latency Wireless'
            ]),
            79.00,
            109.00,
            60,
            4.89,
            1650,
            0,
            1,
            'assets/images/products/zen_controller.jpg',
            'keyboard'
        ],
        [
            13,
            1,
            'Zenkit MagVolt 25,000mAh 100W PD Fast-Charging Power Bank',
            'zenkit-magvolt-25000mah-fast-powerbank',
            '100W Laptop Fast Charging • Smart LED Wattage Display • Airline Flight Safe',
            'The ultimate portable powerhouse delivering up to 100W single-port USB-C Power Delivery with real-time digital display.',
            json_encode([
                'Capacity' => '25,000 mAh / 92.5Wh Airline Carry-On Approved',
                'Output' => '100W Max USB-C PD 3.0 PPS Laptop Fast Charging',
                'Wireless' => '15W Magnetic Wireless Qi Charging Pad',
                'Screen' => 'Real-Time Power Draw Watts & Battery % Screen'
            ]),
            59.00,
            89.00,
            75,
            4.92,
            2310,
            0,
            1,
            'assets/images/products/zen_powerbank.jpg',
            'orb'
        ],
        [
            14,
            2,
            'Zenkit AeroPods ANC Wireless Sport Earbuds (Waterproof IP68)',
            'zenkit-aeropods-anc-wireless-sport-earbuds',
            'IP68 Sweatproof • Secure-Fit Earhooks • BassBoost Sound Engine • 45h Playtime',
            'Built for intense marathon runs with secure liquid silicone ear hooks and IP68 waterproof immersion certification.',
            json_encode([
                'Waterproof' => 'IP68 Certified Submersible Protection',
                'Battery' => '10h Buds + 35h Case (45h Total Endurance)',
                'Drivers' => '12mm Graphene BassBoost Drivers'
            ]),
            69.00,
            99.00,
            50,
            4.88,
            1140,
            0,
            0,
            'assets/images/products/zen_buds.jpg',
            'buds'
        ],
        [
            15,
            3,
            'Zenkit Chronos Rugged Adventure GPS Smartwatch',
            'zenkit-chronos-rugged-adventure-gps-smartwatch',
            'Titanium Bezel • 100m Dive Rating • Offline Topo Maps • Dual-Band GPS',
            'Forged with grade-5 titanium bezel, 100m water dive rating, offline topographic maps, and 24-day battery endurance.',
            json_encode([
                'Bezel' => 'Grade-5 Aerospace Titanium + Sapphire AMOLED (2,200 Nits)',
                'Water Rating' => '10 ATM (100-Meter Water & Dive Resistance)',
                'Battery' => 'Up to 24 Days Smartwatch / 60 Hours Continuous GPS'
            ]),
            169.00,
            229.00,
            32,
            4.96,
            890,
            1,
            0,
            'assets/images/products/zen_watch.jpg',
            'watch'
        ],
        [
            16,
            5,
            'Zenkit Lumina Smart Sunset Projection & Aurora Lamp',
            'zenkit-lumina-smart-sunset-aurora-lamp',
            '360° Rotatable Glass Crystal • App Ambient Presets • Cinematic Golden Hour',
            'Captivating golden hour sunset and northern aurora projection with thickened optical crystal lens and 360° swivel arm.',
            json_encode([
                'Lens' => 'High-Transmission Optical Crystal Glass Lens',
                'Effects' => '16 Preset Atmospheric Cinematic Moods',
                'Control' => 'Touch In-line Switch + Wireless Remote'
            ]),
            45.00,
            65.00,
            60,
            4.85,
            620,
            0,
            1,
            'assets/images/products/zen_lamp.jpg',
            'orb'
        ]
    ];

    foreach ($products as $prod) {
        $prodStmt->execute($prod);
    }

    // 4. Seed Admin & Demo Users
    $userStmt = $pdo->prepare("INSERT OR REPLACE INTO users (id, name, email, password, phone, address, city, postal_code, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $demoPassword = password_hash('demo123', PASSWORD_DEFAULT);
    
    $userStmt->execute([1, 'Zenkit Admin', 'admin@zenkit.io', $adminPassword, '9876543210', 'Tech Park Boulevard', 'Bangalore', '560103', 'admin']);
    $userStmt->execute([2, 'Rahul Sharma', 'demo@zenkit.io', $demoPassword, '9810123456', 'Flat 402, Green Valley Apartments', 'New Delhi', '110001', 'customer']);

    // 5. Seed Reviews
    $revStmt = $pdo->prepare("INSERT OR REPLACE INTO reviews (id, product_id, user_name, rating, comment, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
    $reviews = [
        [1, 1, 'Amit Patel', 5, 'The ProBook 15 screen is crisp and vibrant. Build quality is top-notch aluminum like a MacBook at half the cost! Great for programming.', 1],
        [2, 1, 'Priya Nair', 5, 'Super fast boot and battery easily lasts 10-12 hours of office work. Zenkit delivery arrived the very next day.', 1],
        [3, 2, 'Vikas Singh', 5, 'Active Noise Cancellation on AeroPods Pro is unreal. Blocks bus and metro noise completely. Earbuds are very comfortable.', 1],
        [4, 3, 'Sneha Joshi', 5, 'The AMOLED display on this watch is bright even in direct sunlight. Calling quality is crystal clear.', 1],
        [5, 4, 'Rohit Gupta', 5, 'Keyboard typing sound is so satisfying. The pre-lubed switches make a huge difference. RGB looks awesome on my desk.', 1],
        [6, 5, 'Ananya Sen', 5, 'The Halo smart lamp looks so elegant on my study table. Stepless dimming is gentle on the eyes late at night.', 1]
    ];
    foreach ($reviews as $rev) {
        $revStmt->execute($rev);
    }

    // 6. Seed Discount Coupons
    $couponStmt = $pdo->prepare("INSERT OR REPLACE INTO coupons (code, discount_percent, max_discount, min_spend) VALUES (?, ?, ?, ?)");
    $coupons = [
        ['ZENKIT20', 20, 2500.00, 999.00],
        ['WELCOME15', 15, 1500.00, 499.00],
        ['FESTIVE50', 50, 5000.00, 2499.00]
    ];
    foreach ($coupons as $coup) {
        $couponStmt->execute($coup);
    }

    // 7. Seed Sample Orders
    $orderStmt = $pdo->prepare("INSERT OR REPLACE INTO orders 
        (id, order_number, user_id, customer_name, customer_email, customer_phone, shipping_address, city, postal_code, payment_method, payment_status, total_amount, discount_amount, final_amount, order_status, tracking_code, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $orderStmt->execute([
        1,
        'ZK-748921',
        2,
        'Rahul Sharma',
        'demo@zenkit.io',
        '9810123456',
        'Flat 402, Green Valley Apartments',
        'New Delhi',
        '110001',
        'UPI (Google Pay)',
        'Paid',
        838.00,
        83.80,
        754.20,
        'Delivered',
        'TRK-DEL-994821',
        'Delivered with OTP verification'
    ]);

    $itemStmt = $pdo->prepare("INSERT OR REPLACE INTO order_items (id, order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $itemStmt->execute([1, 1, 1, 'Zenkit ProBook Ultrathin 15 (16GB RAM / 512GB SSD)', 749.00, 1, 749.00]);
    $itemStmt->execute([2, 1, 2, 'Zenkit AeroPods Pro ANC True Wireless Earbuds', 89.00, 1, 89.00]);
}
