USE foodease;

INSERT INTO categories (id, name, sort_order) VALUES
  (1, 'Kopi', 1),
  (2, 'Non-Kopi', 2),
  (3, 'Makanan Berat', 3),
  (4, 'Camilan', 4);

INSERT INTO menus (category_id, name, description, price, is_available) VALUES
  (1, 'Espresso',                 'Single shot espresso.',                      18000, 1),
  (1, 'Americano',                'Espresso dengan air panas atau es.',         20000, 1),
  (1, 'Kopi Susu Gula Aren',      'Espresso, susu, dan gula aren.',             24000, 1),
  (1, 'Cappuccino',               'Espresso dengan susu dan foam tebal.',       26000, 1),
  (2, 'Teh Lemon',                'Teh dingin dengan perasan lemon.',           16000, 1),
  (2, 'Coklat Panas',             'Coklat susu hangat.',                        24000, 1),
  (2, 'Matcha Latte',             'Matcha dengan susu.',                        27000, 1),
  (3, 'Nasi Goreng Kampung',      'Nasi goreng dengan telur dan kerupuk.',      32000, 1),
  (3, 'Mie Goreng Telur',         'Mie goreng dengan telur dan sayur.',         28000, 1),
  (3, 'Ayam Geprek Rice Bowl',    'Ayam geprek sambal bawang dengan nasi.',     30000, 1),
  (4, 'Kentang Goreng',           'Kentang goreng dengan saus.',                20000, 1),
  (4, 'Roti Bakar Coklat Keju',   'Roti bakar isi coklat dan keju.',            22000, 1),
  (4, 'Pisang Goreng',            'Pisang goreng renyah.',                      18000, 0);