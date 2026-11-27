<?php
declare(strict_types=1);

/**
 * Terjemahan nama kategori dan menu (terpisah dari layer App\Core).
 */
return [
    'categories' => [
        'Kopi'           => ['id' => 'Kopi',           'en' => 'Coffee',             'zh' => '咖啡'],
        'Kopi Susu'      => ['id' => 'Kopi Susu',      'en' => 'Milk Coffee',        'zh' => '拿铁咖啡'],
        'Non-Kopi'       => ['id' => 'Non-Kopi',       'en' => 'Non-Coffee',         'zh' => '非咖啡'],
        'Non Kopi'       => ['id' => 'Non Kopi',       'en' => 'Non-Coffee',         'zh' => '非咖啡'],
        'Makanan Berat'  => ['id' => 'Makanan Berat',  'en' => 'Main Course',        'zh' => '正餐主食'],
        'Makanan'        => ['id' => 'Makanan',        'en' => 'Food',               'zh' => '美食'],
        'Camilan'        => ['id' => 'Camilan',        'en' => 'Snacks',             'zh' => '小吃点心'],
        'Minuman Segar'  => ['id' => 'Minuman Segar',  'en' => 'Fresh Drinks',       'zh' => '清爽鲜饮'],
        'Paket & Promo'  => ['id' => 'Paket & Promo',  'en' => 'Packages & Promos',  'zh' => '特惠套餐'],
        'Semua'          => ['id' => 'Semua',          'en' => 'All',                'zh' => '全部'],
        'Lainnya'        => ['id' => 'Lainnya',        'en' => 'Other',              'zh' => '其他'],
    ],
    'menus' => [
        'Espresso' => [
            'name' => ['id' => 'Espresso', 'en' => 'Espresso', 'zh' => '意式浓缩咖啡'],
            'desc' => ['id' => 'Single shot espresso.', 'en' => 'Single shot espresso.', 'zh' => '经典单份意式特浓黑咖啡。'],
        ],
        'Americano' => [
            'name' => ['id' => 'Americano', 'en' => 'Americano', 'zh' => '美式咖啡'],
            'desc' => ['id' => 'Espresso dengan air panas atau es.', 'en' => 'Espresso with hot water or iced.', 'zh' => '浓缩咖啡加热水或冰块。'],
        ],
        'Kopi Susu Gula Aren' => [
            'name' => ['id' => 'Kopi Susu Gula Aren', 'en' => 'Palm Sugar Milk Coffee', 'zh' => '棕榈糖拿铁咖啡'],
            'desc' => ['id' => 'Espresso, susu, dan gula aren.', 'en' => 'Espresso, fresh milk, and organic palm sugar.', 'zh' => '浓缩咖啡、鲜奶与纯正棕榈糖。'],
        ],
        'Cappuccino' => [
            'name' => ['id' => 'Cappuccino', 'en' => 'Cappuccino', 'zh' => '卡布奇诺'],
            'desc' => ['id' => 'Espresso dengan susu dan foam tebal.', 'en' => 'Espresso with steamed milk and thick foam.', 'zh' => '浓缩咖啡与细密厚奶泡。'],
        ],
        'Matcha Latte' => [
            'name' => ['id' => 'Matcha Latte', 'en' => 'Matcha Latte', 'zh' => '日式抹茶拿铁'],
            'desc' => ['id' => 'Matcha dengan susu.', 'en' => 'Premium Japanese matcha with fresh milk.', 'zh' => '精选日式抹茶与鲜奶融合。'],
        ],
        'Iced Latte' => [
            'name' => ['id' => 'Iced Latte', 'en' => 'Iced Latte', 'zh' => '冰拿铁'],
            'desc' => ['id' => 'Espresso ganda dengan susu segar dan es batu.', 'en' => 'Double espresso poured over fresh milk and ice.', 'zh' => '双份浓缩咖啡配鲜牛奶与冰块。'],
        ],
        'Iced Chocolate' => [
            'name' => ['id' => 'Iced Chocolate', 'en' => 'Iced Chocolate', 'zh' => '冰巧克力'],
            'desc' => ['id' => 'Cokelat dingin pekat dengan susu creamy.', 'en' => 'Rich iced chocolate with creamy milk.', 'zh' => '浓郁冰巧克力配香滑牛奶。'],
        ],
        'Iced Matcha' => [
            'name' => ['id' => 'Iced Matcha', 'en' => 'Iced Matcha', 'zh' => '冰抹茶拿铁'],
            'desc' => ['id' => 'Matcha ceremonial disajikan dingin.', 'en' => 'Ceremonial matcha served over ice.', 'zh' => '仪式级抹茶冰镇呈现。'],
        ],
        'Iced Lemon Tea' => [
            'name' => ['id' => 'Iced Lemon Tea', 'en' => 'Iced Lemon Tea', 'zh' => '冰柠檬茶'],
            'desc' => ['id' => 'Teh hitam seduh dingin dengan irisan lemon segar.', 'en' => 'Cold-steeped black tea with fresh lemon slices.', 'zh' => '冷泡红茶配新鲜柠檬片。'],
        ],
        'Kentang Goreng' => [
            'name' => ['id' => 'Kentang Goreng', 'en' => 'French Fries', 'zh' => '香脆炸薯条'],
            'desc' => ['id' => 'Kentang goreng dengan saus.', 'en' => 'Crispy golden french fries with dipping sauce.', 'zh' => '现炸黄金脆薯条配特调蘸酱。'],
        ],
        'Croissant' => [
            'name' => ['id' => 'Croissant', 'en' => 'Butter Croissant', 'zh' => '法式黄油可颂'],
            'desc' => ['id' => 'Croissant mentega lapis-lapis.', 'en' => 'Flaky, buttery layered French croissant.', 'zh' => '酥软多层现烤法国黄油可颂。'],
        ],
    ],
];
