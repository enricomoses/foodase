<?php
declare(strict_types=1);

namespace App\Core;

final class I18n
{
    private static ?string $currentLang = null;

    private static array $categoryTranslations = [
        'Kopi' => [
            'id' => 'Kopi',
            'en' => 'Coffee',
            'zh' => '咖啡',
        ],
        'Non-Kopi' => [
            'id' => 'Non-Kopi',
            'en' => 'Non-Coffee',
            'zh' => '非咖啡',
        ],
        'Makanan Berat' => [
            'id' => 'Makanan Berat',
            'en' => 'Main Course',
            'zh' => '正餐主食',
        ],
        'Camilan' => [
            'id' => 'Camilan',
            'en' => 'Snacks',
            'zh' => '小吃点心',
        ],
        'Minuman Segar' => [
            'id' => 'Minuman Segar',
            'en' => 'Fresh Drinks',
            'zh' => '清爽鲜饮',
        ],
        'Paket & Promo' => [
            'id' => 'Paket & Promo',
            'en' => 'Packages & Promos',
            'zh' => '特惠套餐',
        ],
        'Semua' => [
            'id' => 'Semua',
            'en' => 'All',
            'zh' => '全部',
        ],
        'Lainnya' => [
            'id' => 'Lainnya',
            'en' => 'Other',
            'zh' => '其他',
        ],
    ];

    private static array $menuTranslations = [
        'Espresso' => [
            'name' => [
                'id' => 'Espresso',
                'en' => 'Espresso',
                'zh' => '意式浓缩咖啡',
            ],
            'desc' => [
                'id' => 'Single shot espresso.',
                'en' => 'Single shot espresso.',
                'zh' => '经典单份意式特浓黑咖啡。',
            ],
        ],
        'Americano' => [
            'name' => [
                'id' => 'Americano',
                'en' => 'Americano',
                'zh' => '美式咖啡',
            ],
            'desc' => [
                'id' => 'Espresso dengan air panas atau es.',
                'en' => 'Espresso with hot water or iced.',
                'zh' => '浓缩咖啡加热水或冰块。',
            ],
        ],
        'Kopi Susu Gula Aren' => [
            'name' => [
                'id' => 'Kopi Susu Gula Aren',
                'en' => 'Palm Sugar Milk Coffee',
                'zh' => '棕榈糖拿铁咖啡',
            ],
            'desc' => [
                'id' => 'Espresso, susu, dan gula aren.',
                'en' => 'Espresso, fresh milk, and organic palm sugar.',
                'zh' => '浓缩咖啡、鲜奶与纯正棕榈糖。',
            ],
        ],
        'Cappuccino' => [
            'name' => [
                'id' => 'Cappuccino',
                'en' => 'Cappuccino',
                'zh' => '卡布奇诺',
            ],
            'desc' => [
                'id' => 'Espresso dengan susu dan foam tebal.',
                'en' => 'Espresso with steamed milk and thick foam.',
                'zh' => '浓缩咖啡与细密厚奶泡。',
            ],
        ],
        'Teh Lemon' => [
            'name' => [
                'id' => 'Teh Lemon',
                'en' => 'Iced Lemon Tea',
                'zh' => '柠檬红茶',
            ],
            'desc' => [
                'id' => 'Teh dingin dengan perasan lemon.',
                'en' => 'Fresh brewed tea with real lemon juice.',
                'zh' => '冰爽红茶配新鲜鲜榨柠檬汁。',
            ],
        ],
        'Coklat Panas' => [
            'name' => [
                'id' => 'Coklat Panas',
                'en' => 'Hot Chocolate',
                'zh' => '丝滑热巧克力',
            ],
            'desc' => [
                'id' => 'Coklat susu hangat.',
                'en' => 'Warm and velvety chocolate milk.',
                'zh' => '香浓纯正温热巧克牛奶。',
            ],
        ],
        'Matcha Latte' => [
            'name' => [
                'id' => 'Matcha Latte',
                'en' => 'Matcha Latte',
                'zh' => '日式抹茶拿铁',
            ],
            'desc' => [
                'id' => 'Matcha dengan susu.',
                'en' => 'Premium Japanese matcha with fresh milk.',
                'zh' => '精选日式抹茶与鲜奶融合。',
            ],
        ],
        'Nasi Goreng Kampung' => [
            'name' => [
                'id' => 'Nasi Goreng Kampung',
                'en' => 'Village Fried Rice',
                'zh' => '乡村风味炒饭',
            ],
            'desc' => [
                'id' => 'Nasi goreng dengan telur dan kerupuk.',
                'en' => 'Indonesian style fried rice with egg and crackers.',
                'zh' => '传统印尼风味炒饭配荷包蛋及虾饼。',
            ],
        ],
        'Mie Goreng Telur' => [
            'name' => [
                'id' => 'Mie Goreng Telur',
                'en' => 'Fried Noodles with Egg',
                'zh' => '经典鸡蛋炒面',
            ],
            'desc' => [
                'id' => 'Mie goreng dengan telur dan sayur.',
                'en' => 'Stir-fried noodles with egg and fresh vegetables.',
                'zh' => '香炒面条搭配鲜蛋与新鲜时蔬。',
            ],
        ],
        'Ayam Geprek Rice Bowl' => [
            'name' => [
                'id' => 'Ayam Geprek Rice Bowl',
                'en' => 'Spicy Smashed Chicken Rice Bowl',
                'zh' => '蒜香碎辣炸鸡盖饭',
            ],
            'desc' => [
                'id' => 'Ayam geprek sambal bawang dengan nasi.',
                'en' => 'Crispy smashed chicken with garlic chili sambal and steamed rice.',
                'zh' => '酥脆蒜香辣酱碎炸鸡配热腾腾白米饭。',
            ],
        ],
        'Kentang Goreng' => [
            'name' => [
                'id' => 'Kentang Goreng',
                'en' => 'French Fries',
                'zh' => '香脆炸薯条',
            ],
            'desc' => [
                'id' => 'Kentang goreng dengan saus.',
                'en' => 'Crispy golden french fries with dipping sauce.',
                'zh' => '现炸黄金脆薯条配特调蘸酱。',
            ],
        ],
        'Roti Bakar Coklat Keju' => [
            'name' => [
                'id' => 'Roti Bakar Coklat Keju',
                'en' => 'Chocolate Cheese Toast',
                'zh' => '巧克力芝士烤吐司',
            ],
            'desc' => [
                'id' => 'Roti bakar isi coklat dan keju.',
                'en' => 'Grilled toast filled with melted chocolate and cheddar cheese.',
                'zh' => '酥脆烤吐司夹浓香巧克力与芝士。',
            ],
        ],
        'Pisang Goreng' => [
            'name' => [
                'id' => 'Pisang Goreng',
                'en' => 'Crispy Fried Banana',
                'zh' => '传统香脆炸香蕉',
            ],
            'desc' => [
                'id' => 'Pisang goreng renyah.',
                'en' => 'Traditional crispy golden fried bananas.',
                'zh' => '传统金黄酥脆炸香蕉。',
            ],
        ],
        'Cold Brew' => [
            'name' => [
                'id' => 'Cold Brew',
                'en' => 'Cold Brew Coffee',
                'zh' => '冷萃黑咖啡',
            ],
            'desc' => [
                'id' => 'Kopi hitam dingin direndam 12 jam, lembut dan tidak pahit',
                'en' => 'Steeped cold for 12 hours, smooth and low acidity',
                'zh' => '12小时慢速低温冷萃，顺滑无酸涩',
            ],
        ],
        'Vietnam Drip' => [
            'name' => [
                'id' => 'Vietnam Drip',
                'en' => 'Vietnamese Drip Coffee',
                'zh' => '越南滴漏咖啡',
            ],
            'desc' => [
                'id' => 'Kopi tetes gaya Vietnam dengan susu kental manis',
                'en' => 'Traditional drip coffee with sweet condensed milk',
                'zh' => '传统越南风味滴滤黑咖啡配炼乳',
            ],
        ],
        'Latte' => [
            'name' => [
                'id' => 'Latte',
                'en' => 'Caffè Latte',
                'zh' => '经典拿铁',
            ],
            'desc' => [
                'id' => 'Espresso lembut dengan susu steamed yang creamy',
                'en' => 'Smooth espresso with velvety steamed milk',
                'zh' => '柔顺浓缩咖啡配细腻蒸奶',
            ],
        ],
        'Flat White' => [
            'name' => [
                'id' => 'Flat White',
                'en' => 'Flat White',
                'zh' => '澳白咖啡',
            ],
            'desc' => [
                'id' => 'Double ristretto dengan micro-foam susu segar pilihan',
                'en' => 'Double ristretto with velvety micro-foam milk',
                'zh' => '双份精萃浓缩配丝绒微奶泡',
            ],
        ],
        'Thai Tea' => [
            'name' => [
                'id' => 'Thai Tea',
                'en' => 'Authentic Thai Tea',
                'zh' => '泰式奶茶',
            ],
            'desc' => [
                'id' => 'Teh hitam khas Thailand dengan susu evaporasi yang creamy',
                'en' => 'Classic Thai black tea with creamy evaporated milk',
                'zh' => '地道泰式红茶配浓郁淡奶',
            ],
        ],
        'Blue Pea Lemonade' => [
            'name' => [
                'id' => 'Blue Pea Lemonade',
                'en' => 'Blue Pea Lemonade',
                'zh' => '蝶豆花柠檬汽水',
            ],
            'desc' => [
                'id' => 'Minuman bunga telang biru segar dengan perasan lemon',
                'en' => 'Butterfly pea flower iced tea with freshly squeezed lemon',
                'zh' => '天然蝶豆花汁配新鲜鲜榨柠檬',
            ],
        ],
        'Strawberry Smoothie' => [
            'name' => [
                'id' => 'Strawberry Smoothie',
                'en' => 'Strawberry Smoothie',
                'zh' => '草莓果肉冰沙',
            ],
            'desc' => [
                'id' => 'Smoothie stroberi segar tanpa pemanis tambahan',
                'en' => 'Fresh strawberry smoothie without added sweeteners',
                'zh' => '新鲜草莓纯果冰沙，无人工甜味剂',
            ],
        ],
        'Milo Dinosaur' => [
            'name' => [
                'id' => 'Milo Dinosaur',
                'en' => 'Milo Dinosaur',
                'zh' => '恐龙美禄',
            ],
            'desc' => [
                'id' => 'Milo panas dengan topping Milo bubuk berlimpah',
                'en' => 'Chilled iced Milo topped with mounds of Milo powder',
                'zh' => '冰镇麦芽可可饮撒满香浓美禄粉',
            ],
        ],
        'Nasi Uduk Ayam' => [
            'name' => [
                'id' => 'Nasi Uduk Ayam',
                'en' => 'Fragrant Coconut Rice with Chicken',
                'zh' => '椰浆香米饭配炸鸡',
            ],
            'desc' => [
                'id' => 'Nasi uduk gurih dengan ayam goreng, lalapan, dan sambal terasi',
                'en' => 'Savory coconut rice with crispy fried chicken, salad, and sambal',
                'zh' => '浓郁椰香米饭配黄金炸鸡、新鲜蔬菜与传统辣酱',
            ],
        ],
        'Pasta Carbonara' => [
            'name' => [
                'id' => 'Pasta Carbonara',
                'en' => 'Pasta Carbonara',
                'zh' => '奶油培根意面',
            ],
            'desc' => [
                'id' => 'Pasta creamy dengan bacon, telur, dan parmesan',
                'en' => 'Creamy pasta with smoked beef bacon, egg, and parmesan',
                'zh' => '浓郁白汁意大利面配烟熏培根与帕玛森芝士',
            ],
        ],
        'Sandwich Club' => [
            'name' => [
                'id' => 'Sandwich Club',
                'en' => 'Club Sandwich',
                'zh' => '经典三明治',
            ],
            'desc' => [
                'id' => 'Lapis tiga dengan ayam panggang, selada, tomat, dan mayo',
                'en' => 'Triple-decker with roast chicken, lettuce, tomato, and mayo',
                'zh' => '三层烤面包夹烤鸡胸肉、生菜、番茄与蛋黄酱',
            ],
        ],
        'Chicken Steak' => [
            'name' => [
                'id' => 'Chicken Steak',
                'en' => 'Grilled Chicken Steak',
                'zh' => '香煎黑椒鸡排',
            ],
            'desc' => [
                'id' => 'Dada ayam panggang dengan saus mushroom dan kentang wedges',
                'en' => 'Grilled chicken breast with rich mushroom sauce and potato wedges',
                'zh' => '鲜嫩多汁烤鸡胸肉配特调蘑菇酱与香脆薯角',
            ],
        ],
        'Nasi Lemak' => [
            'name' => [
                'id' => 'Nasi Lemak',
                'en' => 'Traditional Nasi Lemak',
                'zh' => '经典椰浆饭',
            ],
            'desc' => [
                'id' => 'Nasi santan dengan ikan teri, kacang, telur rebus, dan sambal',
                'en' => 'Coconut rice with crispy anchovies, peanuts, boiled egg, and sambal',
                'zh' => '传统椰浆米饭配香脆小银鱼、花生、水煮蛋与辣椒酱',
            ],
        ],
        'Churros' => [
            'name' => [
                'id' => 'Churros',
                'en' => 'Cinnamon Churros',
                'zh' => '西班牙吉事果',
            ],
            'desc' => [
                'id' => 'Churros goreng garing tabur kayu manis dengan saus coklat',
                'en' => 'Golden crisp churros dusted with cinnamon sugar and chocolate dip',
                'zh' => '香脆现炸吉事果裹肉桂糖配浓醇黑巧酱',
            ],
        ],
        'Onion Ring' => [
            'name' => [
                'id' => 'Onion Ring',
                'en' => 'Crispy Onion Rings',
                'zh' => '黄金洋葱圈',
            ],
            'desc' => [
                'id' => 'Cincin bawang garing dengan saus sriracha mayo',
                'en' => 'Crispy battered onion rings served with sriracha mayo',
                'zh' => '金黄香脆炸洋葱圈配是拉差蛋黄酱',
            ],
        ],
        'Croissant' => [
            'name' => [
                'id' => 'Croissant',
                'en' => 'Butter Croissant',
                'zh' => '法式黄油可颂',
            ],
            'desc' => [
                'id' => 'Croissant mentega lapis-lapis, fresh from oven',
                'en' => 'Flaky, buttery layered French croissant baked fresh daily',
                'zh' => '酥软多层现烤法国传统黄油可颂',
            ],
        ],
        'Es Jeruk Segar' => [
            'name' => [
                'id' => 'Es Jeruk Segar',
                'en' => 'Fresh Orange Juice',
                'zh' => '鲜榨纯正橙汁',
            ],
            'desc' => [
                'id' => 'Perasan jeruk nipis asli tanpa pemanis buatan',
                'en' => '100% freshly squeezed sweet orange juice without artificial sweeteners',
                'zh' => '100%纯正鲜榨甜橙汁，天然健康无添加',
            ],
        ],
        'Lemon Mint Cooler' => [
            'name' => [
                'id' => 'Lemon Mint Cooler',
                'en' => 'Lemon Mint Cooler',
                'zh' => '柠檬薄荷苏打',
            ],
            'desc' => [
                'id' => 'Air lemon, mint segar, dan soda yang menyegarkan',
                'en' => 'Zesty lemon juice, fresh crushed mint leaves, and sparkling soda',
                'zh' => '清新柠檬汁、新鲜碎薄荷叶与爽口苏打水',
            ],
        ],
        'Alpukat Kocok' => [
            'name' => [
                'id' => 'Alpukat Kocok',
                'en' => 'Creamy Avocado Shake',
                'zh' => '浓郁牛油果奶昔',
            ],
            'desc' => [
                'id' => 'Alpukat kocok creamy dengan susu dan coklat',
                'en' => 'Rich blended ripe avocado layered with fresh milk and chocolate syrup',
                'zh' => '精选成熟牛油果泥配鲜牛奶与浓郁巧克力酱',
            ],
        ],
        'Air Kelapa Muda' => [
            'name' => [
                'id' => 'Air Kelapa Muda',
                'en' => 'Fresh Young Coconut',
                'zh' => '天然清凉椰子水',
            ],
            'desc' => [
                'id' => 'Kelapa muda segar langsung dari buahnya',
                'en' => 'Pure natural chilled young coconut water straight from the shell',
                'zh' => '天然现开新鲜青椰子水配嫩椰肉',
            ],
        ],
        'Kopi Antigravity Test Edit' => [
            'name' => [
                'id' => 'Kopi Antigravity Test Edit',
                'en' => 'Antigravity Signature Coffee',
                'zh' => '反重力特调咖啡',
            ],
            'desc' => [
                'id' => 'Updated deskripsi',
                'en' => 'Special house blend coffee.',
                'zh' => '咖啡厅特调招牌咖啡。',
            ],
        ],
    ];

    private static array $translations = [
        'id' => [
            'app_name' => 'FoodEase',
            'nav_menu' => 'Menu',
            'nav_search' => 'Cari',
            'nav_track' => 'Lacak',
            'nav_orders' => 'Pesanan',
            'nav_cart' => 'Keranjang',
            'nav_account' => 'Akun',
            'nav_login' => 'Masuk',
            'nav_logout' => 'Keluar',
            'nav_search_placeholder' => 'Cari makanan atau minuman...',
            'role_admin' => 'Administrator Kafe',
            'role_cashier' => 'Kasir Kafe',
            'dashboard_admin' => 'Dashboard Admin',
            'panel_cashier' => 'Panel Kasir',
            'my_orders' => 'Pesanan Saya',
            'favorite_badge' => 'Pilihan Favorit Kafe',
            'favorite_title' => 'Menu Favorit & Paling Laris',
            'favorite_desc' => 'Menu pilihan paling banyak dipesan',
            'btn_add' => '+ Tambah',
            'badge_out_of_stock' => 'Habis',
            'cat_all' => 'Semua',
            'cat_coffee' => 'Kopi',
            'cat_non_coffee' => 'Non-Kopi',
            'cat_main_course' => 'Makanan Berat',
            'cat_snacks' => 'Camilan',
            'cat_fresh_drinks' => 'Minuman Segar',
            'cat_packages' => 'Paket & Promo',
            'notes_label' => 'Catatan Pesanan',
            'notes_placeholder' => 'Mis. Sedikit es, tanpa sedotan',
            'qty_label' => 'Jumlah',
            'btn_add_to_cart' => 'Tambah ke Keranjang',
            'toast_added' => 'Menu berhasil ditambahkan ke keranjang!',
            'cart_title' => 'Keranjang Pesanan',
            'cart_empty_title' => 'Keranjang Belanja Kosong',
            'cart_empty_desc' => 'Anda belum memilih menu apa pun. Yuk, jelajahi menu lezat kami!',
            'cart_btn_explore' => 'Jelajahi Menu',
            'cart_summary_title' => 'Ringkasan Pesanan',
            'cart_subtotal' => 'Subtotal',
            'cart_total' => 'Total Pembayaran',
            'cart_btn_checkout' => 'Lanjut Pembayaran',
            'cart_btn_clear' => 'Kosongkan',
            'checkout_title' => 'Konfirmasi & Pembayaran',
            'customer_name' => 'Nama Pelanggan',
            'name_placeholder' => 'Mis. Budi Santoso',
            'order_type' => 'Tipe Pesanan',
            'dine_in' => 'Makan di Tempat',
            'takeaway' => 'Bawa Pulang',
            'table_number' => 'Nomor Meja',
            'table_placeholder' => 'Pilih nomor meja anda',
            'payment_method' => 'Metode Pembayaran',
            'pay_cash' => 'Tunai (Bayar di Kasir)',
            'pay_qris' => 'QRIS (Scan QR Digital)',
            'btn_submit_order' => 'Buat Pesanan Sekarang',
            'order_success_title' => 'Pesanan Berhasil Dibuat!',
            'order_code' => 'Kode Pesanan',
            'orders_title' => 'Status & Riwayat Pesanan',
            'status_pending' => 'Menunggu Pembayaran',
            'status_processing' => 'Sedang Diproses',
            'status_ready' => 'Siap Disajikan',
            'status_completed' => 'Selesai',
            'status_cancelled' => 'Dibatalkan',
            'status_unpaid' => 'Belum bayar',
            'status_paid' => 'Lunas',
            'status_refunded' => 'Dikembalikan',
            'btn_add_detail' => 'Tambah',
            'toast_unavailable' => 'Menu ini sedang tidak tersedia.',
            'no_description' => 'Tidak ada deskripsi.',
            'err_load_menu' => 'Gagal memuat katalog menu.',
            'empty_category' => 'Belum ada menu yang tersedia untuk kategori ini.',
            'cat_other' => 'Lainnya',
            'active_order_prefix' => 'Pesanan aktif',
            'track_status' => 'Lacak Status →',
            'history_subtitle' => 'Daftar pesanan Anda yang dibuat dari perangkat ini.',
            'history_track_by_code_link' => 'Lacak dengan kode & nama →',
            'history_empty_title' => 'Belum ada riwayat pesanan',
            'history_empty_desc' => 'Anda belum membuat pesanan dari perangkat ini. Jelajahi menu kami dan pesan hidangan favorit Anda!',
            'history_btn_start' => 'Mulai Pesan Menu',
            'history_btn_track' => 'Lacak dengan Kode',
            'history_err_load' => 'Gagal memuat riwayat pesanan.',
            'history_btn_retry' => 'Coba Lagi',
            'history_track_order' => 'Lacak Pesanan',
            'history_table' => 'Meja',
            'history_items' => 'item',
            'hero_badge_open' => 'Buka Sekarang • Meja & Bawa Pulang',
            'hero_slide1_title' => 'Kopi Istimewa & Santapan Hangat Kafe',
            'hero_slide1_desc' => 'Pesan menu favorit Anda langsung dari meja tanpa harus antre di kasir. Segar, nikmat, dan disajikan tepat waktu.',
            'hero_badge_fast' => 'Penyajian Cepat 10 Menit',
            'hero_badge_beans' => '100% Biji Kopi Arabika',
            'hero_badge_payment' => 'QRIS & Tunai',
            'hero_promo_badge' => 'Promo Sarapan Hemat • Diskon 15%',
            'hero_slide2_title' => 'Cold Brew Segar & Pastry Hangat',
            'hero_slide2_desc' => 'Diseduh dingin selama 12 jam untuk rasa kopi yang halus tanpa rasa asam tajam, dipadu croissant renyah mentega pilihan.',
            'hero_btn_explore_coffee' => 'Jelajah Menu Kopi',
            'hero_avail_morning' => 'Tersedia Setiap Pagi',
            'hero_slide3_badge' => 'Menu Santap Siang Terfavorit',
            'hero_slide3_title' => 'Rice Bowl & Nasi Goreng Istimewa',
            'hero_slide3_desc' => 'Santapan mengenyangkan dengan racikan bumbu rempah autentik, ayam krispi gurih, dan telur mata sapi segar.',
            'hero_btn_explore_food' => 'Lihat Makanan Berat',
            'hero_freshly_cooked' => 'Dimasak Fresh Setiap Hari',
            'login_title' => 'Masuk',
            'login_subtitle' => 'Masuk untuk memesan makanan dan pantau pesanan Anda.',
            'login_username' => 'Username',
            'login_password' => 'Password',
            'login_btn' => 'Masuk',
            'login_forgot' => 'Lupa password?',
            'login_no_account' => 'Belum memiliki akun?',
            'login_register_link' => 'Daftar sekarang',
            'login_back_home' => 'Kembali ke Beranda',
            'register_title' => 'Buat Akun Baru',
            'register_subtitle' => 'Daftarkan akun kasir atau staf kafe baru.',
            'register_name' => 'Nama Lengkap',
            'register_username' => 'Username',
            'register_role' => 'Peran Akun',
            'register_cashier' => 'Kasir',
            'register_admin' => 'Administrator',
            'register_password' => 'Password',
            'register_btn' => 'Daftar Sekarang',
            'register_has_account' => 'Sudah punya akun?',
            'register_login_link' => 'Masuk',
            'admin_portal_badge' => 'Portal Staf',
            'admin_login_title' => 'Masuk Staf & Admin',
            'admin_login_subtitle' => 'Akses khusus kasir dan administrator kafe.',
            'admin_login_role' => 'Peran Akun Staf',
            'admin_login_btn' => 'Masuk ke Panel',
            'back_to_menu' => 'Kembali ke Menu',
            'checkout_back' => '← Tambah Menu Lain',
            'checkout_btn' => 'Checkout',
            'optional' => 'opsional',
            'sidebar_dashboard' => 'Dashboard',
            'sidebar_orders' => 'Pesanan',
            'sidebar_pos' => 'POS',
            'sidebar_history' => 'Riwayat',
            'sidebar_menus' => 'Kelola Menu',
            'sidebar_categories' => 'Kategori',
            'sidebar_users' => 'Pengguna',
            'sidebar_reports' => 'Laporan',
            'sidebar_cashier' => 'Pesanan Masuk',
            'footer_copyright' => 'FoodEase — Sistem Pemesanan & Kasir Kafe',
            'lang_id' => 'Indonesia',
            'lang_en' => 'English',
            'lang_zh' => '中文 (Mandarin)',
        ],
        'en' => [
            'app_name' => 'FoodEase',
            'nav_menu' => 'Menu',
            'nav_search' => 'Search',
            'nav_track' => 'Track',
            'nav_orders' => 'Orders',
            'nav_cart' => 'Cart',
            'nav_account' => 'Account',
            'nav_login' => 'Log In',
            'nav_logout' => 'Log Out',
            'nav_search_placeholder' => 'Search food or drinks...',
            'role_admin' => 'Café Administrator',
            'role_cashier' => 'Café Cashier',
            'dashboard_admin' => 'Admin Dashboard',
            'panel_cashier' => 'Cashier Panel',
            'my_orders' => 'My Orders',
            'favorite_badge' => 'Café Favorites',
            'favorite_title' => 'Favorites & Best Sellers',
            'favorite_desc' => 'Most ordered items by customers',
            'btn_add' => '+ Add',
            'badge_out_of_stock' => 'Sold Out',
            'cat_all' => 'All',
            'cat_coffee' => 'Coffee',
            'cat_non_coffee' => 'Non-Coffee',
            'cat_main_course' => 'Main Course',
            'cat_snacks' => 'Snacks',
            'cat_fresh_drinks' => 'Fresh Drinks',
            'cat_packages' => 'Packages & Promos',
            'notes_label' => 'Order Notes',
            'notes_placeholder' => 'E.g. Less ice, no straw',
            'qty_label' => 'Quantity',
            'btn_add_to_cart' => 'Add to Cart',
            'toast_added' => 'Item successfully added to cart!',
            'cart_title' => 'Your Cart',
            'cart_empty_title' => 'Your Cart is Empty',
            'cart_empty_desc' => 'You haven\'t selected any items yet. Let\'s explore our delicious menu!',
            'cart_btn_explore' => 'Explore Menu',
            'cart_summary_title' => 'Order Summary',
            'cart_subtotal' => 'Subtotal',
            'cart_total' => 'Total Payment',
            'cart_btn_checkout' => 'Proceed to Checkout',
            'cart_btn_clear' => 'Clear Cart',
            'checkout_title' => 'Confirmation & Payment',
            'customer_name' => 'Customer Name',
            'name_placeholder' => 'E.g. John Doe',
            'order_type' => 'Order Type',
            'dine_in' => 'Dine In',
            'takeaway' => 'Takeaway',
            'table_number' => 'Table Number',
            'table_placeholder' => 'Select your table number',
            'payment_method' => 'Payment Method',
            'pay_cash' => 'Cash (Pay at Counter)',
            'pay_qris' => 'QRIS (Digital QR Scan)',
            'btn_submit_order' => 'Place Order Now',
            'order_success_title' => 'Order Placed Successfully!',
            'order_code' => 'Order Code',
            'orders_title' => 'Order Status & History',
            'status_pending' => 'Pending Payment',
            'status_processing' => 'In Preparation',
            'status_ready' => 'Ready to Serve',
            'status_completed' => 'Completed',
            'status_cancelled' => 'Cancelled',
            'status_unpaid' => 'Unpaid',
            'status_paid' => 'Paid',
            'status_refunded' => 'Refunded',
            'btn_add_detail' => 'Add',
            'toast_unavailable' => 'This menu item is currently unavailable.',
            'no_description' => 'No description available.',
            'err_load_menu' => 'Failed to load menu catalog.',
            'empty_category' => 'No items available for this category.',
            'cat_other' => 'Other',
            'active_order_prefix' => 'Active order',
            'track_status' => 'Track Status →',
            'history_subtitle' => 'List of your orders placed from this device.',
            'history_track_by_code_link' => 'Track with code & name →',
            'history_empty_title' => 'No order history yet',
            'history_empty_desc' => 'You haven\'t placed any orders from this device yet. Explore our delicious menu and order your favorites!',
            'history_btn_start' => 'Explore Menu',
            'history_btn_track' => 'Track with Code',
            'history_err_load' => 'Failed to load order history.',
            'history_btn_retry' => 'Retry',
            'history_track_order' => 'Track Order',
            'history_table' => 'Table',
            'history_items' => 'items',
            'hero_badge_open' => 'Open Now • Dine In & Takeaway',
            'hero_slide1_title' => 'Specialty Coffee & Warm Café Meals',
            'hero_slide1_desc' => 'Order your favorite items directly from your table without queuing. Fresh, delightful, and timely served.',
            'hero_badge_fast' => 'Fast 10-Min Serving',
            'hero_badge_beans' => '100% Arabica Coffee Beans',
            'hero_badge_payment' => 'QRIS & Cash',
            'hero_promo_badge' => 'Breakfast Special • 15% Off',
            'hero_slide2_title' => 'Fresh Cold Brew & Warm Pastry',
            'hero_slide2_desc' => 'Cold-brewed for 12 hours for a silky smooth taste, paired with flaky fresh-baked butter croissants.',
            'hero_btn_explore_coffee' => 'Explore Coffee Menu',
            'hero_avail_morning' => 'Available Every Morning',
            'hero_slide3_badge' => 'Lunchtime Favorite',
            'hero_slide3_title' => 'Signature Rice Bowl & Fried Rice',
            'hero_slide3_desc' => 'Hearty meals crafted with authentic spices, savory crispy chicken, and fresh sunny-side-up eggs.',
            'hero_btn_explore_food' => 'Explore Main Course',
            'hero_freshly_cooked' => 'Freshly Cooked Daily',
            'login_title' => 'Log In',
            'login_subtitle' => 'Log in to order food and track your orders.',
            'login_username' => 'Username',
            'login_password' => 'Password',
            'login_btn' => 'Log In',
            'login_forgot' => 'Forgot password?',
            'login_no_account' => 'Don\'t have an account?',
            'login_register_link' => 'Register now',
            'login_back_home' => 'Back to Home',
            'register_title' => 'Create Account',
            'register_subtitle' => 'Register a new cashier or café staff account.',
            'register_name' => 'Full Name',
            'register_username' => 'Username',
            'register_role' => 'Account Role',
            'register_cashier' => 'Cashier',
            'register_admin' => 'Administrator',
            'register_password' => 'Password',
            'register_btn' => 'Register Now',
            'register_has_account' => 'Already have an account?',
            'register_login_link' => 'Log In',
            'admin_portal_badge' => 'Staff Portal',
            'admin_login_title' => 'Staff & Admin Login',
            'admin_login_subtitle' => 'Exclusive access for cashiers and café administrators.',
            'admin_login_role' => 'Staff Account Role',
            'admin_login_btn' => 'Enter Panel',
            'back_to_menu' => 'Back to Menu',
            'checkout_back' => '← Add More Items',
            'checkout_btn' => 'Checkout',
            'optional' => 'optional',
            'sidebar_dashboard' => 'Dashboard',
            'sidebar_orders' => 'Orders',
            'sidebar_pos' => 'POS',
            'sidebar_history' => 'History',
            'sidebar_menus' => 'Manage Menus',
            'sidebar_categories' => 'Categories',
            'sidebar_users' => 'Users',
            'sidebar_reports' => 'Reports',
            'sidebar_cashier' => 'Incoming Orders',
            'footer_copyright' => 'FoodEase — Café Ordering & Cashier System',
            'lang_id' => 'Indonesia',
            'lang_en' => 'English',
            'lang_zh' => '中文 (Mandarin)',
        ],
        'zh' => [
            'app_name' => 'FoodEase',
            'nav_menu' => '菜单',
            'nav_search' => '搜索',
            'nav_track' => '追踪',
            'nav_orders' => '订单',
            'nav_cart' => '购物车',
            'nav_account' => '账户',
            'nav_login' => '登录',
            'nav_logout' => '退出',
            'nav_search_placeholder' => '搜索美食或饮品...',
            'role_admin' => '咖啡厅管理员',
            'role_cashier' => '收银员',
            'dashboard_admin' => '管理员后台',
            'panel_cashier' => '收银台',
            'my_orders' => '我的订单',
            'favorite_badge' => '咖啡厅精选',
            'favorite_title' => '最受喜爱与热销榜',
            'favorite_desc' => '顾客最常选购的热门餐品',
            'btn_add' => '+ 添加',
            'badge_out_of_stock' => '售罄',
            'cat_all' => '全部',
            'cat_coffee' => '咖啡',
            'cat_non_coffee' => '非咖啡',
            'cat_main_course' => '正餐主食',
            'cat_snacks' => '小吃点心',
            'cat_fresh_drinks' => '清爽鲜饮',
            'cat_packages' => '特惠套餐',
            'notes_label' => '订单备注',
            'notes_placeholder' => '例如：少冰、不要吸管',
            'qty_label' => '数量',
            'btn_add_to_cart' => '加入购物车',
            'toast_added' => '已成功加入购物车！',
            'cart_title' => '购物车',
            'cart_empty_title' => '购物车是空的',
            'cart_empty_desc' => '您还没有选择任何餐品。快来浏览我们的美味菜单吧！',
            'cart_btn_explore' => '去逛逛菜单',
            'cart_summary_title' => '订单明细',
            'cart_subtotal' => '商品小计',
            'cart_total' => '实付总额',
            'cart_btn_checkout' => '去结账',
            'cart_btn_clear' => '清空',
            'checkout_title' => '确认订单与支付',
            'customer_name' => '顾客姓名',
            'name_placeholder' => '例如：张三',
            'order_type' => '就餐方式',
            'dine_in' => '堂食',
            'takeaway' => '外带打包',
            'table_number' => '桌号',
            'table_placeholder' => '请选择您的桌号',
            'payment_method' => '支付方式',
            'pay_cash' => '现金（前台现金付款）',
            'pay_qris' => 'QRIS（扫码电子支付）',
            'btn_submit_order' => '立即提交订单',
            'order_success_title' => '下单成功！',
            'order_code' => '订单号',
            'orders_title' => '订单状态与历史',
            'status_pending' => '等待支付',
            'status_processing' => '正在制作中',
            'status_ready' => '制作完成可取餐',
            'status_completed' => '已完成',
            'status_cancelled' => '已取消',
            'status_unpaid' => '未支付',
            'status_paid' => '已付款',
            'status_refunded' => '已退款',
            'btn_add_detail' => '添加',
            'toast_unavailable' => '该菜品暂时无货。',
            'no_description' => '暂无描述。',
            'err_load_menu' => '加载菜单失败，请重试。',
            'empty_category' => '该分类暂无可用菜品。',
            'cat_other' => '其他',
            'active_order_prefix' => '进行中的订单',
            'track_status' => '追踪状态 →',
            'history_subtitle' => '通过此设备提交的订单列表。',
            'history_track_by_code_link' => '通过订单号和姓名追踪 →',
            'history_empty_title' => '暂无订单历史',
            'history_empty_desc' => '您尚未在此设备上下单。立即浏览我们的菜单，挑选心仪的美味吧！',
            'history_btn_start' => '去逛逛菜单',
            'history_btn_track' => '根据订单号查询',
            'history_err_load' => '加载订单历史记录失败。',
            'history_btn_retry' => '重试',
            'history_track_order' => '追踪订单',
            'history_table' => '桌号',
            'history_items' => '件餐品',
            'hero_badge_open' => '营业中 • 堂食与外带',
            'hero_slide1_title' => '精选特调咖啡与暖心美食',
            'hero_slide1_desc' => '无需在收银台排队，就座直接点餐。新鲜现制、美味可口、准时送达。',
            'hero_badge_fast' => '10分钟快速出餐',
            'hero_badge_beans' => '100% 精选阿拉比卡咖啡豆',
            'hero_badge_payment' => '支持扫码支付及现金',
            'hero_promo_badge' => '特惠早餐 • 享15%立减',
            'hero_slide2_title' => '清爽冷萃与温热现烤酥点',
            'hero_slide2_desc' => '12小时慢速低温冷萃，口感丝滑无酸涩，搭配酥脆纯正法国黄油可颂。',
            'hero_btn_explore_coffee' => '探索咖啡精选',
            'hero_avail_morning' => '每日早晨新鲜供应',
            'hero_slide3_badge' => '午餐人气之选',
            'hero_slide3_title' => '招牌盖饭与特色印尼炒饭',
            'hero_slide3_desc' => '浓郁地道香料秘制，酥脆多汁炸鸡搭配金黄荷包蛋，饱腹满足。',
            'hero_btn_explore_food' => '查看正餐美食',
            'hero_freshly_cooked' => '每日现点现做新鲜出炉',
            'login_title' => '登录',
            'login_subtitle' => '登录以点餐并跟踪您的订单。',
            'login_username' => '用户名',
            'login_password' => '密码',
            'login_btn' => '登录',
            'login_forgot' => '忘记密码？',
            'login_no_account' => '还没有账户？',
            'login_register_link' => '立即注册',
            'login_back_home' => '返回首页',
            'register_title' => '创建账户',
            'register_subtitle' => '注册新的收银员或咖啡厅员工账户。',
            'register_name' => '全名',
            'register_username' => '用户名',
            'register_role' => '账户角色',
            'register_cashier' => '收银员',
            'register_admin' => '管理员',
            'register_password' => '密码',
            'register_btn' => '立即注册',
            'register_has_account' => '已有账户？',
            'register_login_link' => '登录',
            'admin_portal_badge' => '员工门户',
            'admin_login_title' => '员工与管理员登录',
            'admin_login_subtitle' => '仅限收银员和咖啡厅管理员访问。',
            'admin_login_role' => '员工账户角色',
            'admin_login_btn' => '进入后台',
            'back_to_menu' => '返回菜单',
            'checkout_back' => '← 返回继续点餐',
            'checkout_btn' => '去结账',
            'optional' => '可选',
            'sidebar_dashboard' => '仪表盘',
            'sidebar_orders' => '订单管理',
            'sidebar_pos' => '收银点餐',
            'sidebar_history' => '历史记录',
            'sidebar_menus' => '菜单管理',
            'sidebar_categories' => '分类管理',
            'sidebar_users' => '用户管理',
            'sidebar_reports' => '销售报表',
            'sidebar_cashier' => '待处理订单',
            'footer_copyright' => 'FoodEase — 咖啡厅点餐与收银系统',
            'lang_id' => 'Indonesia',
            'lang_en' => 'English',
            'lang_zh' => '中文 (Mandarin)',
        ],
    ];

    public static function getLang(): string
    {
        if (self::$currentLang !== null) {
            return self::$currentLang;
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en', 'zh'], true)) {
            self::setLang((string) $_GET['lang']);
            return self::$currentLang;
        }

        $sessionLang = Session::get('foodease_lang');
        if (is_string($sessionLang) && in_array($sessionLang, ['id', 'en', 'zh'], true)) {
            self::$currentLang = $sessionLang;
            return self::$currentLang;
        }

        if (isset($_COOKIE['foodease_lang']) && in_array($_COOKIE['foodease_lang'], ['id', 'en', 'zh'], true)) {
            self::$currentLang = (string) $_COOKIE['foodease_lang'];
            return self::$currentLang;
        }

        self::$currentLang = 'id';
        return self::$currentLang;
    }

    public static function setLang(string $lang): void
    {
        if (in_array($lang, ['id', 'en', 'zh'], true)) {
            self::$currentLang = $lang;
            Session::set('foodease_lang', $lang);
            @setcookie('foodease_lang', $lang, time() + (365 * 86400), '/');
        }
    }

    public static function get(string $key, array $replace = [], ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $text = self::$translations[$lang][$key]
            ?? self::$translations['id'][$key]
            ?? $key;

        foreach ($replace as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, (string) $value, $text);
        }

        return $text;
    }

    public static function translateCategory(string $categoryName, ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $trimmed = trim($categoryName);

        if (isset(self::$categoryTranslations[$trimmed][$lang])) {
            return self::$categoryTranslations[$trimmed][$lang];
        }

        // Cek case-insensitive
        foreach (self::$categoryTranslations as $catKey => $trans) {
            if (strcasecmp($catKey, $trimmed) === 0 && isset($trans[$lang])) {
                return $trans[$lang];
            }
        }

        return $categoryName;
    }

    public static function translateMenuName(string $name, ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $trimmed = trim($name);

        if (isset(self::$menuTranslations[$trimmed]['name'][$lang])) {
            return self::$menuTranslations[$trimmed]['name'][$lang];
        }

        foreach (self::$menuTranslations as $menuKey => $trans) {
            if (strcasecmp($menuKey, $trimmed) === 0 && isset($trans['name'][$lang])) {
                return $trans['name'][$lang];
            }
        }

        return $name;
    }

    public static function translateMenuDesc(?string $desc, string $name, ?string $lang = null): string
    {
        $lang = $lang ?? self::getLang();
        $trimmedName = trim($name);

        if (isset(self::$menuTranslations[$trimmedName]['desc'][$lang])) {
            return self::$menuTranslations[$trimmedName]['desc'][$lang];
        }

        foreach (self::$menuTranslations as $menuKey => $trans) {
            if (strcasecmp($menuKey, $trimmedName) === 0 && isset($trans['desc'][$lang])) {
                return $trans['desc'][$lang];
            }
        }

        return $desc ?? '';
    }

    public static function all(?string $lang = null): array
    {
        $lang = $lang ?? self::getLang();
        return self::$translations[$lang] ?? self::$translations['id'];
    }

    public static function rawTranslations(): array
    {
        return self::$translations;
    }
}
