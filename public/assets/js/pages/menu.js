/**
 * Script Halaman Menu Pelanggan (Gaya Uber Eats).
 * FR-CMENU-01 s.d. FR-CMENU-04, prd.md §12.3-12.4.
 */
$(function () {
  'use strict';

  // Pastikan halaman menu pelanggan SELALU bertema putih bersih (Clean Light Mode)
  document.documentElement.classList.remove('dark');
  try {
    localStorage.setItem('nola_theme', 'light');
    localStorage.setItem('foodease_theme', 'light');
  } catch (e) {}

  // SVG Ikon Plus
  const SVG_PLUS = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';

  // Helper terjemahan singkat untuk menu.js
  function t(key, fallback) {
    return (window.I18n && typeof window.I18n.t === 'function') ? window.I18n.t(key, fallback) : (fallback || key);
  }

  // Cache data
  let allCategories = [];
  let allMenus = [];
  let currentSearchQuery = '';
  let currentCategoryId = '';
  let searchDebounceTimer = null;

  // State modal detail
  let activeDetailMenu = null;
  let activeDetailQty = 1;

  // Placeholder default
  const defaultPlaceholder = 'assets/img/placeholder-menu.svg';

  // Helper format rupiah
  function formatRupiah(amount) {
    if (window.Fmt && typeof window.Fmt.rupiah === 'function') {
      return window.Fmt.rupiah(amount);
    }
    return 'Rp ' + (parseInt(amount, 10) || 0).toLocaleString('id-ID');
  }

  // ============================================================
  // LOAD KATEGORI (CHIPS)
  // ============================================================
  function loadCategories() {
    Api.get('api/categories/list.php')
      .then(function (res) {
        allCategories = (res && res.data) ? res.data : [];
        renderCategoryChips();
      })
      .catch(function () {
        // Fallback: biarkan hanya chip "Semua"
      });
  }

  function getCategoryEmoji(name) {
    const n = (name || '').toLowerCase();
    if (n.includes('kopi') || n.includes('coffee') || n.includes('espresso')) return '☕';
    if (n.includes('smooth') || n.includes('shake') || n.includes('ice cream') || n.includes('milk')) return '🍦';
    if (n.includes('side') || n.includes('camilan') || n.includes('snack') || n.includes('toast') || n.includes('pastry')) return '🍰';
    if (n.includes('makan') || n.includes('food') || n.includes('meal') || n.includes('rice') || n.includes('dimsum')) return '🍱';
    if (n.includes('tea') || n.includes('teh') || n.includes('non') || n.includes('beverage') || n.includes('juice')) return '🥤';
    return '🍽️';
  }

  function renderCategoryChips() {
    const $chipsContainer = $('#category-chips');
    // Hapus chip selain "Semua"
    $chipsContainer.find('.category-chip:not([data-category-id=""])').remove();

    allCategories.forEach(function (cat) {
      const catDisplayName = window.I18n ? window.I18n.translateCategory(cat.name) : cat.name;
      const isActive = String(cat.id) === String(currentCategoryId);
      const emoji = getCategoryEmoji(cat.name);
      const activeClass = isActive
        ? 'is-active bg-blue-600 text-white shadow-sm font-semibold'
        : 'bg-[#F1F5F9] text-[#475569] hover:bg-gray-200 hover:text-gray-900 font-medium';
      const chipHtml = `
        <button
          type="button"
          class="category-chip h-10 px-5 rounded-full text-xs sm:text-sm whitespace-nowrap transition-all duration-200 ${activeClass} flex items-center gap-2 cursor-pointer"
          data-category-id="${cat.id}"
        >
          <span class="text-sm">${emoji}</span>
          <span>${UI.escape(catDisplayName)}</span>
        </button>
      `;
      $chipsContainer.append(chipHtml);
    });

    // Perbarui style chip "Semua"
    const isAllActive = !currentCategoryId;
    $chipsContainer.find('.category-chip[data-category-id=""]')
      .toggleClass('is-active bg-blue-600 text-white shadow-sm font-semibold', isAllActive)
      .toggleClass('bg-[#F1F5F9] text-[#475569] hover:bg-gray-200 hover:text-gray-900 font-medium', !isAllActive);

    setTimeout(updateChipsScrollButtons, 100);
  }

  // Kontrol Navigasi Scroll Horizontal Chip Kategori
  function updateChipsScrollButtons() {
    const $area = $('#category-chips-scroll-area');
    if (!$area.length) return;
    const el = $area[0];
    const scrollLeft = el.scrollLeft;
    const maxScroll = el.scrollWidth - el.clientWidth;
    const hasOverflow = maxScroll > 4;

    const $wrapper = $('.category-chips-wrapper');
    const canScrollLeft = scrollLeft > 10;
    const canScrollRight = (maxScroll - scrollLeft) > 10;

    $wrapper.toggleClass('has-scroll-left', hasOverflow && canScrollLeft);
    $wrapper.toggleClass('has-scroll-right', hasOverflow && canScrollRight);

    $('#btn-chips-scroll-left')
      .css({ opacity: canScrollLeft ? 1 : 0, 'pointer-events': canScrollLeft ? 'auto' : 'none' });
    $('#btn-chips-scroll-right')
      .css({ opacity: canScrollRight ? 1 : 0, 'pointer-events': canScrollRight ? 'auto' : 'none' });
  }

  $('#btn-chips-scroll-left').on('click', function () {
    const $area = $('#category-chips-scroll-area');
    $area.animate({ scrollLeft: $area.scrollLeft() - 240 }, 250);
  });

  $('#btn-chips-scroll-right').on('click', function () {
    const $area = $('#category-chips-scroll-area');
    $area.animate({ scrollLeft: $area.scrollLeft() + 240 }, 250);
  });

  $('#category-chips-scroll-area').on('scroll', function () {
    updateChipsScrollButtons();
  });

  $(window).on('resize', function () {
    updateChipsScrollButtons();
  });

  // Klik chip kategori
  $('#category-chips').on('click', '.category-chip', function () {
    const $btn = $(this);
    const catId = $btn.data('category-id');
    currentCategoryId = catId ? String(catId) : '';

    // Perbarui style chip aktif (aktif biru, tidak aktif abu terang)
    $('#category-chips .category-chip')
      .removeClass('is-active bg-blue-600 text-white shadow-sm font-semibold')
      .addClass('bg-[#F1F5F9] text-[#475569] hover:bg-gray-200 hover:text-gray-900 font-medium');

    $btn
      .removeClass('bg-[#F1F5F9] text-[#475569] hover:bg-gray-200 hover:text-gray-900 font-medium')
      .addClass('is-active bg-blue-600 text-white shadow-sm font-semibold');

    // Scroll chip ke tengah area scroll jika terpotong
    if (this.scrollIntoView) {
      this.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    loadMenus();

    // Smooth scroll ke section kategori yang dipilih (setelah render selesai)
    if (catId) {
      var targetCatName = $btn.find('span:last').text().trim();
      setTimeout(function () {
        var $section = $('#menu-container').find('.category-section').filter(function () {
          return $(this).attr('data-category') === targetCatName;
        });
        if (!$section.length) $section = $('#menu-catalog-section');
        if ($section.length) {
          $('html, body').animate({ scrollTop: $section.offset().top - 100 }, 450);
        }
      }, 400);
    }
  });

  // ============================================================
  // PENCARIAN DENGAN DEBOUNCE 300 MS
  // ============================================================
  function handleSearchInput(val) {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(function () {
      currentSearchQuery = $.trim(val);
      loadMenus();
    }, 300);
  }

  // Sinkronkan kolom cari desktop dan mobile
  $('#global-search-input').on('input', function () {
    const val = $(this).val();
    $('#mobile-search-input').val(val);
    $('#btn-clear-search').toggleClass('hidden', !val);
    handleSearchInput(val);
  });

  $('#mobile-search-input').on('input', function () {
    const val = $(this).val();
    $('#global-search-input').val(val);
    $('#btn-clear-search').toggleClass('hidden', !val);
    handleSearchInput(val);
  });

  $('#btn-clear-search').on('click', function () {
    $('#global-search-input, #mobile-search-input').val('');
    $(this).addClass('hidden');
    handleSearchInput('');
    $('#global-search-input').focus();
  });

  // Reset filter dari state kosong
  $('#btn-reset-filter').on('click', function () {
    currentSearchQuery = '';
    currentCategoryId = '';
    $('#global-search-input, #mobile-search-input').val('');
    $('#btn-clear-search').addClass('hidden');

    $('#category-chips .category-chip')
      .removeClass('is-active bg-blue-600 text-white shadow-sm font-semibold')
      .addClass('bg-[#F1F5F9] text-[#475569] hover:bg-gray-200 hover:text-gray-900 font-medium');

    $('#category-chips .category-chip[data-category-id=""]')
      .removeClass('bg-[#F1F5F9] text-[#475569] hover:bg-gray-200 hover:text-gray-900 font-medium')
      .addClass('is-active bg-blue-600 text-white shadow-sm font-semibold');

    loadMenus();
  });

  // ============================================================
  // LOAD & RENDER MENU (GROUPED BY CATEGORY)
  // ============================================================
  function loadMenus() {
    $('#menu-loading').removeClass('hidden');
    $('#menu-error').addClass('hidden');
    $('#menu-empty').addClass('hidden');
    $('#menu-container').addClass('hidden');

    const params = {
      include_unavailable: 1,
      per_page: 100,
    };
    if (currentSearchQuery) {
      params.q = currentSearchQuery;
    }
    if (currentCategoryId) {
      params.category_id = currentCategoryId;
    }

    Api.get('api/menus/list.php', params)
      .then(function (res) {
        $('#menu-loading').addClass('hidden');
        allMenus = (res && res.data && res.data.data) ? res.data.data : [];
        syncHeroMenus(allMenus);
        renderMenuCatalog();
      })
      .catch(function (err) {
        $('#menu-loading').addClass('hidden');
        $('#menu-error-message').text(err.message || (t('err_load_menu', 'Gagal memuat katalog menu.')));
        $('#menu-error').removeClass('hidden');
      });
  }

  function renderMenuCatalog() {
    if (!allMenus.length) {
      if (currentSearchQuery) {
        $('#menu-empty-message').text(`Tidak ada menu yang sesuai dengan kata kunci "${currentSearchQuery}".`);
      } else {
        $('#menu-empty-message').text((t('empty_category', 'Belum ada menu yang tersedia untuk kategori ini.')));
      }
      $('#menu-empty').removeClass('hidden');
      $('#menu-container').addClass('hidden');
      return;
    }

    // Kelompokkan menu berdasarkan kategori
    const grouped = {};
    allMenus.forEach(function (m) {
      const catName = (m.category_name || t('cat_other', 'Lainnya'));
      if (!grouped[catName]) {
        grouped[catName] = [];
      }
      grouped[catName].push(m);
    });

    const $container = $('#menu-container');
    $container.empty();

    // Rekomendasi Menu Favorit (Sliding Carousel Image)
    if (!currentSearchQuery && !currentCategoryId) {
      const popularItems = allMenus.filter(function (m) { return !!m.is_available; }).slice(0, 8);
      if (popularItems.length > 0) {
        let slidesHtml = '';
        popularItems.forEach(function (m) {
          const imgSrc = m.image_url || defaultPlaceholder;
          const menuName = window.I18n ? window.I18n.translateMenuName(m.name) : m.name;
          const menuCategory = window.I18n ? window.I18n.translateCategory(m.category_name || 'Kafe') : (m.category_name || 'Kafe');
          const menuDesc = window.I18n ? window.I18n.translateMenuDesc(m.description, m.name) : m.description;
          slidesHtml += `
            <div
              class="carousel-slide menu-card fe-menu-card relative w-56 sm:w-64 rounded-3xl border border-gray-100 bg-white p-3.5 hover:border-blue-500/30 transition-all cursor-pointer group flex flex-col justify-between shrink-0 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
              data-id="${m.id}"
              tabindex="0"
              role="button"
              aria-label="${UI.escape(menuName)} - ${formatRupiah(m.price)}"
            >
              <!-- Container Foto Menu Favorit (Foto Real, Bersih, Proporsional) -->
              <div class="relative w-full h-36 sm:h-40 rounded-2xl overflow-hidden bg-gray-100 mb-3">
                <img
                  src="${imgSrc}"
                  alt="${UI.escape(menuName)}"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                  onerror="this.src='${defaultPlaceholder}'"
                  loading="lazy"
                  decoding="async"
                >
              </div>

              <!-- Deskripsi & Info Menu -->
              <div class="flex-1 flex flex-col justify-between">
                <div>
                  <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">${UI.escape(menuCategory)}</span>
                  <h3 class="font-bold text-[#0F172A] text-sm sm:text-base leading-snug line-clamp-1 mt-0.5 group-hover:text-blue-600 transition-colors">
                    ${UI.escape(menuName)}
                  </h3>
                  <p class="mt-1 text-xs text-[#64748B] line-clamp-2 leading-relaxed min-h-[2rem]">
                    ${menuDesc ? UI.escape(menuDesc) : 'Racikan istimewa khas racikan kafe.'}
                  </p>
                </div>

                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between">
                  <span class="menu-price font-bold text-sm sm:text-base text-blue-600 font-mono tabular-nums">
                    ${formatRupiah(m.price)}
                  </span>
                  <button
                    type="button"
                    class="btn-card-add h-8 px-3.5 rounded-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer shadow-sm hover:shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1"
                    data-id="${m.id}"
                    title="${t('btn_add', '+ Tambah')}"
                    aria-label="${t('btn_add', 'Tambah')} ${UI.escape(menuName)}"
                  >
                    <span>${t('btn_add', '+ Tambah')}</span>
                  </button>
                </div>
              </div>
            </div>
          `;
        });

        const carouselSectionHtml = `
          <section class="favorite-carousel-section mb-8">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[#0F172A]">
                  ${t('favorite_title', 'Menu Favorit & Paling Laris')}
                </h2>
                <p class="text-xs sm:text-sm text-[#64748B] mt-0.5">${t('favorite_desc', 'Paling sering dipesan oleh pelanggan')}</p>
              </div>

              <!-- Tombol Navigasi Geser (Prev & Next) -->
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  id="btn-carousel-prev"
                  class="w-9 h-9 rounded-full bg-[#F1F5F9] border border-gray-200 text-gray-700 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 active:scale-95 transition-all flex items-center justify-center cursor-pointer shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1"
                  title="Sebelumnya"
                  aria-label="Geser ke produk sebelumnya"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"></polyline>
                  </svg>
                </button>
                <button
                  type="button"
                  id="btn-carousel-next"
                  class="w-9 h-9 rounded-full bg-[#F1F5F9] border border-gray-200 text-gray-700 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 active:scale-95 transition-all flex items-center justify-center cursor-pointer shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1"
                  title="Selanjutnya"
                  aria-label="Geser ke produk selanjutnya"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Track Geser Horizontal -->
            <div id="favorite-carousel-track" class="carousel-track flex gap-4 overflow-x-auto no-scrollbar py-1">
              ${slidesHtml}
            </div>
          </section>
        `;
        $container.append(carouselSectionHtml);
      }
    }

    Object.keys(grouped).forEach(function (categoryName) {
      const items = grouped[categoryName] || [];
      var displayCategoryName = categoryName;
      if (window.I18n && typeof window.I18n.translateCategory === 'function') {
        try {
          displayCategoryName = window.I18n.translateCategory(categoryName) || categoryName;
        } catch (e) {
          displayCategoryName = categoryName;
        }
      }

      // Ambil subtitle dari item pertama (semua item dalam kategori punya subtitle sama)
      var catSubtitle = (items[0] && items[0].category_subtitle) ? items[0].category_subtitle : 'UNGGULAN';

      let cardsHtml = '';
      items.forEach(function (m) {
        const isAvail = !!m.is_available;
        const imgSrc = m.image_url || defaultPlaceholder;
        const disabledClass = !isAvail ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer';
        const menuName = window.I18n ? window.I18n.translateMenuName(m.name) : m.name;
        const menuDesc = window.I18n ? window.I18n.translateMenuDesc(m.description, m.name) : m.description;

        // Badge dari DB (badge), fallback ke null
        const badgeText = (m.badge && m.badge !== '') ? m.badge : null;

        // Serving tag dari DB, fallback ke deteksi nama
        let itemTag = (m.serving_tag && m.serving_tag !== '') ? m.serving_tag : 'Iced';
        if (!m.serving_tag || m.serving_tag === '') {
          const nameLower = (m.name || '').toLowerCase();
          if (nameLower.includes('hot') || nameLower.includes('panas')) {
            itemTag = 'Hot';
          } else if (nameLower.includes('toast') || nameLower.includes('croissant') || nameLower.includes('pastry') || nameLower.includes('kentang') || nameLower.includes('dimsum')) {
            itemTag = 'Dish';
          }
        }

        // Warna pill serving tag berdasarkan tipe
        let tagClass = 'bg-sky-50 text-sky-600';
        if (itemTag === 'Hot')   tagClass = 'bg-orange-50 text-orange-600';
        if (itemTag === 'Dish')  tagClass = 'bg-green-50 text-green-700';
        if (itemTag === 'Snack') tagClass = 'bg-amber-50 text-amber-700';
        if (itemTag === 'Bottle') tagClass = 'bg-purple-50 text-purple-700';

        // Warna badge berdasarkan tipe
        let badgeClass = 'bg-blue-600 text-white';
        if (badgeText === 'Recommended') badgeClass = 'bg-green-600 text-white';
        if (badgeText === 'Signature')   badgeClass = 'bg-purple-600 text-white';

        cardsHtml += `
          <div
            class="menu-card fe-menu-card group relative bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 ${disabledClass}"
            data-id="${m.id}"
            tabindex="0"
            role="button"
            aria-label="${UI.escape(menuName)} - ${formatRupiah(m.price)}"
            style="opacity:0;transform:translateY(20px)"
          >
            <!-- Area Foto Menu Vertikal Khas Calf (Foto Real Kopi Calf, Aspect Ratio 4:3, Object Cover Proporsional) -->
            <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden">
              <img
                src="${imgSrc}"
                alt="${UI.escape(menuName)}"
                loading="lazy"
                decoding="async"
                class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                onerror="this.src='${defaultPlaceholder}'"
              >

              <!-- Badge di Pojok Kiri Atas Foto -->
              ${badgeText ? `
                <span class="absolute top-3.5 left-3.5 z-10 px-3.5 py-1 rounded-full text-[11px] font-extrabold tracking-wide shadow-md ${badgeClass}">
                  ${UI.escape(badgeText)}
                </span>
              ` : ''}

              ${!isAvail ? `
                <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px] flex items-center justify-center">
                  <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-red-600 text-white shadow-md">
                    ${t('badge_out_of_stock', 'Habis')}
                  </span>
                </div>
              ` : ''}
            </div>

            <!-- Detail Konten Menu -->
            <div class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <h3 class="font-ui font-extrabold text-[#0F172A] text-base leading-snug group-hover:text-blue-600 transition-colors line-clamp-1">
                  ${UI.escape(menuName)}
                </h3>
                <p class="mt-1.5 text-xs sm:text-sm text-[#64748B] line-clamp-2 leading-relaxed min-h-[2.5rem]">
                  ${menuDesc ? UI.escape(menuDesc) : 'Racikan istimewa khas racikan kafe.'}
                </p>
              </div>

              <!-- Baris Bawah: Harga Biru di Kiri, Serving Tag & Tombol Tambah di Kanan -->
              <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between gap-3">
                <div class="menu-price font-extrabold text-base sm:text-lg text-blue-600 font-mono tracking-tight tabular-nums">
                  ${formatRupiah(m.price)}
                </div>

                <div class="flex items-center gap-2">
                  <span class="px-3 py-1 rounded-full text-xs font-bold ${tagClass}">
                    ${UI.escape(itemTag)}
                  </span>
                  ${isAvail ? `
                    <button
                      type="button"
                      class="btn-card-add w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-600 text-white shadow hover:bg-blue-700 transition-all flex items-center justify-center cursor-pointer hover:scale-105 active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
                      data-id="${m.id}"
                      title="${t('btn_add_to_cart', 'Tambah ke keranjang')}"
                      aria-label="${t('btn_add_to_cart', 'Tambah ke keranjang')} ${UI.escape(menuName)}"
                    >
                      ${SVG_PLUS}
                    </button>
                  ` : ''}
                </div>
              </div>
            </div>
          </div>
        `;
      });

      const sectionHtml = `
        <section class="category-section space-y-4" data-category="${UI.escape(categoryName)}">
          <div>
            <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">— ${UI.escape(catSubtitle)}</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight text-gray-900 font-ui">
              ${UI.escape(displayCategoryName)}
            </h2>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6 lg:gap-7">
            ${cardsHtml}
          </div>
        </section>
      `;

      $container.append(sectionHtml);
    });

    $container.removeClass('hidden');

    // ---- Stagger entrance animation ----
    var $cards = $container.find('.menu-card');
    $cards.each(function (i) {
      var $card = $(this);
      setTimeout(function () {
        $card.css({
          transition: 'opacity 0.4s cubic-bezier(0.32,0.72,0,1), transform 0.4s cubic-bezier(0.32,0.72,0,1)',
          opacity: 1,
          transform: 'translateY(0)'
        });
      }, i * 55);
    });
  }

  // Tombol Coba Lagi pada state error
  $('#btn-retry-menu').on('click', loadMenus);

  // ============================================================
  // KLIK TOMBOL "+" PADA KARTU (QUICK ADD QTY 1)
  // ============================================================
  $('#menu-container').on('click', '.btn-card-add', function (e) {
    e.stopPropagation(); // Mencegah membuka modal detail
    const id = parseInt($(this).data('id'), 10);
    const menu = allMenus.find(function (it) {
      return parseInt(it.id, 10) === id;
    });

    if (!menu || !menu.is_available) return;

    const updatedCart = CartStore.add(menu, 1);
    const currentItem = updatedCart.find(function (it) {
      return parseInt(it.id, 10) === id;
    });
    const totalQty = currentItem ? currentItem.qty : 1;
    const menuName = window.I18n ? window.I18n.translateMenuName(menu.name) : menu.name;

    UI.toast('success', `${menuName} × ${totalQty}`, 'Berhasil Ditambahkan');
  });

  // ============================================================
  // KLIK KARTU MENU (BUKA MODAL DETAIL / BOTTOM SHEET)
  // ============================================================
  $('#menu-container').on('click', '.menu-card', function (e) {
    // Abaikan jika klik tombol "+"
    if ($(e.target).closest('.btn-card-add').length) return;

    const id = parseInt($(this).data('id'), 10);
    const menu = allMenus.find(function (it) {
      return parseInt(it.id, 10) === id;
    });

    if (!menu) return;

    // Jika menu habis, jangan buka atau tampilkan info habis
    if (!menu.is_available) {
      UI.toast('warning', (t('toast_unavailable', 'Menu ini sedang tidak tersedia.')));
      return;
    }

    openDetailModal(menu);
  });

  function openDetailModal(menu) {
    activeDetailMenu = menu;
    activeDetailQty = 1;

    const imgSrc = menu.image_url || defaultPlaceholder;
    const menuTitle = window.I18n ? window.I18n.translateMenuName(menu.name) : menu.name;
    const catTitle = window.I18n ? window.I18n.translateCategory(menu.category_name || 'Menu') : (menu.category_name || 'Menu');
    const descText = window.I18n ? window.I18n.translateMenuDesc(menu.description, menu.name) : menu.description;

    $('#detail-image').attr('src', imgSrc).attr('alt', menuTitle);
    $('#detail-category').text(catTitle);
    $('#detail-name').text(menuTitle);
    $('#detail-price').text(formatRupiah(menu.price));
    $('#detail-description').text((descText || t('no_description', 'Tidak ada deskripsi.')));

    if (menu.is_available) {
      $('#detail-badge-habis').addClass('hidden');
      $('#btn-detail-add').prop('disabled', false);
    } else {
      $('#detail-badge-habis').removeClass('hidden');
      $('#btn-detail-add').prop('disabled', true);
    }

    updateDetailStepper();
    UI.openModal('modal-menu-detail');
  }

  function updateDetailStepper() {
    $('#detail-qty').text(activeDetailQty);
    $('#btn-stepper-minus').prop('disabled', activeDetailQty <= 1);

    if (activeDetailMenu) {
      const total = (parseInt(activeDetailMenu.price, 10) || 0) * activeDetailQty;
      $('#detail-add-text').html(`${t('btn_add_detail', 'Tambah')} &middot; ${formatRupiah(total)}`);
    }
  }

  // Tombol Stepper Minus & Plus di Modal Detail
  $('#btn-stepper-minus').on('click', function () {
    if (activeDetailQty > 1) {
      activeDetailQty--;
      updateDetailStepper();
    }
  });

  $('#btn-stepper-plus').on('click', function () {
    activeDetailQty++;
    updateDetailStepper();
  });

  // Tombol "Tambah · Rp xx.xxx" di Modal Detail
  $('#btn-detail-add').on('click', function () {
    if (!activeDetailMenu || !activeDetailMenu.is_available) return;

    const updatedCart = CartStore.add(activeDetailMenu, activeDetailQty);
    const currentItem = updatedCart.find(function (it) {
      return parseInt(it.id, 10) === parseInt(activeDetailMenu.id, 10);
    });
    const totalQty = currentItem ? currentItem.qty : activeDetailQty;
    const menuName = window.I18n ? window.I18n.translateMenuName(activeDetailMenu.name) : activeDetailMenu.name;

    UI.closeModal('modal-menu-detail');
    UI.toast('success', `${menuName} × ${totalQty}`, 'Berhasil Ditambahkan');
  });

  // ============================================================
  // KOPI NUSA HERO STAGE: 4 SIGNATURE DRINKS (FOTO PRODUK ASLI)
  // ============================================================
  const GLACE_SLIDES = [
    {
      index: 0,
      ghostText: 'Kopi Susu Nusantara',
      title1: 'Es Kopi Susu',
      title2: '& Gula Aren',
      desc: 'Espresso Robusta pilihan dengan gula aren asli, creamy dan manis alami — andalan kami.',
      ctaText: 'Pesan Sekarang',
      menuId: 0,
      menuNameOriginal: 'Es Kopi Susu Regular',
      menuName: 'Es Kopi Susu Regular',
      menuPrice: 22000,
      cupImage: 'uploads/menus/menu_iced_latte.jpg',
      tagline: 'Diseduh Hati, Dinikmati Hati',
      subDesc: 'Biji kopi nusantara pilihan, diproses dengan teliti dan disajikan segar setiap hari.'
    },
    {
      index: 1,
      ghostText: 'Smooth Cold Brew',
      title1: 'Classic Cold Brew',
      title2: '& Nitro Smooth',
      desc: 'Kopi hitam direndam 12 jam dalam air dingin, smooth dan tidak pahit sama sekali.',
      ctaText: 'Pesan Sekarang',
      menuId: 0,
      menuNameOriginal: 'Classic Cold Brew',
      menuName: 'Classic Cold Brew',
      menuPrice: 32000,
      cupImage: 'uploads/menus/menu_iced_chocolate.jpg',
      tagline: 'Slow Steeped, Served Bold',
      subDesc: 'Cold brew 12 jam dengan biji kopi premium, lembut dan segar tanpa rasa pahit.'
    },
    {
      index: 2,
      ghostText: 'Matcha Ceremonial',
      title1: 'Matcha Latte',
      title2: '& Ceremonial Grade',
      desc: 'Matcha ceremonial grade digiling halus, dikocok lalu disajikan dengan susu segar.',
      ctaText: 'Pesan Sekarang',
      menuId: 0,
      menuNameOriginal: 'Matcha Latte',
      menuName: 'Matcha Latte',
      menuPrice: 28000,
      cupImage: 'uploads/menus/menu_iced_matcha.jpg',
      tagline: 'From Garden to Glass',
      subDesc: 'Matcha upacara dengan susu lembut dan es, diseduh satu per satu dengan penuh perhatian.'
    },
    {
      index: 3,
      ghostText: 'Smooth Pistachio Dream',
      title1: 'Smooth Series',
      title2: '& Pistachio Dream',
      desc: 'Es kopi susu premium dengan pasta pistachio Italia, kaya dan nutty.',
      ctaText: 'Pesan Sekarang',
      menuId: 0,
      menuNameOriginal: 'Smooth Pistachio',
      menuName: 'Smooth Pistachio',
      menuPrice: 36000,
      cupImage: 'uploads/menus/menu_iced_lemon_tea.jpg',
      tagline: 'Premium Blends, Every Sip',
      subDesc: 'Smooth series spesial kami — perpaduan kopi dengan cream premium yang memanjakan lidah.'
    }
  ];

  let currentGlaceSlide = 0;
  let glaceTimer = null;
  let signatureMenus = [];

  /**
   * Cocokkan slide hero dengan data menu dari database.
   * Foto, harga, dan ID yang dipakai selalu berasal dari server.
   */
  function syncHeroMenus(items) {
    if (!Array.isArray(items) || items.length === 0) {
      return;
    }

    GLACE_SLIDES.forEach(function (slide, index) {
      const wanted = slide.menuNameOriginal.toLowerCase();
      const menu = items.find(function (item) {
        const originalName = String(item.name_original || item.name || '').trim().toLowerCase();
        return originalName === wanted;
      });

      if (!menu) {
        return;
      }

      signatureMenus[index] = menu;
      slide.menuId = parseInt(menu.id, 10) || slide.menuId;
      slide.menuPrice = parseInt(menu.price, 10) || slide.menuPrice;
      slide.menuName = menu.name || slide.menuName;

      if (menu.image_url) {
        slide.cupImage = menu.image_url;
      }

      const $thumb = $('#glace-selectors-row .glace-thumb-card').filter(function () {
        return String($(this).data('menu-name') || '').toLowerCase() === wanted;
      });
      if (menu.image_url && $thumb.length) {
        $thumb.find('.glace-thumb-img').attr('src', menu.image_url);
      }
    });

    showGlaceSlide(currentGlaceSlide, true);
  }

  function showGlaceSlide(index, instant) {
    if (!$('#glace-hero-stage').length || !GLACE_SLIDES.length) return;
    if (index >= GLACE_SLIDES.length) index = 0;
    if (index < 0) index = GLACE_SLIDES.length - 1;
    currentGlaceSlide = index;

    const slide = GLACE_SLIDES[index];

    // Update kontainer data-active-slide (memicu background gradient transition)
    $('#glace-hero-stage').attr('data-active-slide', String(index));

    // Update Ghost Typography Watermark di Belakang
    $('#glace-ghost-text').text(slide.ghostText);

    // Update Teks Headline Serif, Deskripsi, dan CTA Button
    $('#glace-title-1').text(slide.title1);
    $('#glace-title-2').text(slide.title2);
    $('#glace-desc').text(slide.desc);
    $('#glace-order-text').text(slide.ctaText);
    $('#glace-order-btn')
      .attr('data-menu-id', String(slide.menuId))
      .attr('data-menu-name', slide.menuName)
      .attr('data-menu-price', String(slide.menuPrice));

    // Update Tagline Italic dan Sub-Deskripsi di Sisi Kanan
    $('.glace-tagline-italic').text(slide.tagline);
    $('#glace-sub-desc').text(slide.subDesc);

    // Update Gambar Cup Minuman Tengah dengan transisi halus
    const $cupImg = $('#glace-cup-img');
    if ($cupImg.length && $cupImg.attr('src') !== slide.cupImage) {
      if (instant) {
        $cupImg.attr('src', slide.cupImage).attr('alt', slide.title1 + ' ' + slide.title2);
      } else {
        $cupImg.css({ opacity: 0, transform: 'scale(0.94) translateY(8px)' });
        setTimeout(function () {
          $cupImg
            .attr('src', slide.cupImage)
            .attr('alt', slide.title1 + ' ' + slide.title2)
            .css({ opacity: 1, transform: 'scale(1) translateY(0)' });
        }, 160);
      }
    }

    // Update 4 Kartu Thumbnail Selector Horisontal
    $('#glace-selectors-row .glace-thumb-card').each(function () {
      const sIdx = parseInt($(this).data('slide'), 10);
      const isActive = sIdx === index;
      $(this)
        .toggleClass('is-active', isActive)
        .attr('aria-selected', isActive ? 'true' : 'false');
    });
  }

  function startGlaceTimer() {
    stopGlaceTimer();
    if (!$('#glace-hero-stage').length) return;
    glaceTimer = setInterval(function () {
      showGlaceSlide(currentGlaceSlide + 1);
    }, 6500);
  }

  function stopGlaceTimer() {
    if (glaceTimer) {
      clearInterval(glaceTimer);
      glaceTimer = null;
    }
  }

  // Event Klik Kartu Thumbnail Selector di Kanan
  $(document).on('click', '.glace-thumb-card', function (e) {
    e.preventDefault();
    const idx = parseInt($(this).data('slide'), 10) || 0;
    showGlaceSlide(idx);
    startGlaceTimer();
  });

  // Pause Timer saat Hover di Area Hero Stage
  $('#glace-hero-stage')
    .on('mouseenter', stopGlaceTimer)
    .on('mouseleave', startGlaceTimer);

  // Swipe Gesture di Layar Sentuh Mobile
  let glaceTouchStartX = null;
  $('#glace-hero-stage')
    .on('touchstart', function (e) {
      const touch = e.originalEvent.touches[0];
      glaceTouchStartX = touch ? touch.clientX : null;
    })
    .on('touchend', function (e) {
      if (glaceTouchStartX === null) return;
      const touch = e.originalEvent.changedTouches[0];
      const deltaX = touch ? touch.clientX - glaceTouchStartX : 0;
      glaceTouchStartX = null;
      if (Math.abs(deltaX) < 40) return;
      showGlaceSlide(currentGlaceSlide + (deltaX < 0 ? 1 : -1));
      startGlaceTimer();
    });

  // Tombol "Pesan Sekarang" langsung tambahkan menu terkait ke keranjang
  $('#glace-order-btn').on('click', function (e) {
    e.preventDefault();

    const slide = GLACE_SLIDES[currentGlaceSlide];
    const targetMenu = signatureMenus[currentGlaceSlide] || allMenus.find(function (m) {
      return String(m.name_original || m.name || '').toLowerCase() === slide.menuNameOriginal.toLowerCase();
    });
    const menuName = (targetMenu && targetMenu.name) || slide.menuName || 'Minuman Kopi Nusa';

    if (!targetMenu) {
      UI.toast('danger', `Maaf, ${menuName} belum tersedia di menu.`, 'Menu Tidak Ditemukan');
      return;
    }

    if (!targetMenu.is_available) {
      UI.toast('danger', `Maaf, ${menuName} baru saja habis!`, 'Stok Habis');
      return;
    }

    if (window.CartStore) {
      CartStore.add(targetMenu, 1);
      const cartItem = CartStore.getItems().find(function (item) {
        return Number(item.id) === Number(targetMenu.id);
      });
      const totalInCart = cartItem ? cartItem.qty : 1;
      UI.toast('success', `${menuName} × ${totalInCart}`, 'Berhasil Ditambahkan');

      // Animasi feedback pada tombol keranjang di navbar
      $('#navbar-cart-btn, #btn-cart').addClass('scale-110');
      setTimeout(function () {
        $('#navbar-cart-btn, #btn-cart').removeClass('scale-110');
      }, 300);
    }
  });

  // Inisialisasi slide pertama
  showGlaceSlide(0, true);
  startGlaceTimer();

  // Tombol filter cepat kategori di dalam slide
  $(document).on('click', '.btn-filter-category-quick', function (e) {
    e.preventDefault();
    const catName = String($(this).data('cat-name') || '').toLowerCase();
    const matched = allCategories.find(function (c) {
      return c.name.toLowerCase().includes(catName);
    });
    if (matched) {
      $(`#category-chips .category-chip[data-category-id="${matched.id}"]`).trigger('click');
      const $chips = $('#category-chips');
      if ($chips.length) {
        $('html, body').animate({ scrollTop: $chips.offset().top - 80 }, 400);
      }
    }
  });

  // ============================================================
  // CAROUSEL NAVIGASI PREV & NEXT (MENU FAVORIT)
  // ============================================================
  $(document).on('click', '#btn-carousel-prev', function (e) {
    e.preventDefault();
    const $track = $('#favorite-carousel-track');
    const scrollAmount = ($track.width() || 300) * 0.75;
    $track.stop().animate({ scrollLeft: $track.scrollLeft() - scrollAmount }, 300);
  });

  $(document).on('click', '#btn-carousel-next', function (e) {
    e.preventDefault();
    const $track = $('#favorite-carousel-track');
    const scrollAmount = ($track.width() || 300) * 0.75;
    $track.stop().animate({ scrollLeft: $track.scrollLeft() + scrollAmount }, 300);
  });

  // ============================================================
  // INISIALISASI AWAL & CEK PESANAN AKTIF REAL-TIME
  // ============================================================
  function checkActiveOrder() {
    try {
      const lastOrderCode = localStorage.getItem('nola_last_order') || localStorage.getItem('Kopi Nusa_last_order');
      if (!lastOrderCode) {
        $('#active-order-banner').addClass('hidden');
        return;
      }

      // Verifikasi status pesanan langsung dari server
      Api.get('api/orders/track.php', { code: lastOrderCode })
        .then(function (res) {
          const order = (res && res.data) ? (res.data.order || res.data) : null;
          if (!order || !order.status) {
            localStorage.removeItem('nola_last_order');
            localStorage.removeItem('Kopi Nusa_last_order');
            $('#active-order-banner').addClass('hidden');
            return;
          }

          const status = String(order.status || '').toLowerCase();
          // Hanya tampilkan jika status benar-benar aktif (belum selesai/batal)
          if (status === 'pending' || status === 'processing' || status === 'ready') {
            $('#active-order-code').text('#' + (order.order_code || lastOrderCode));
            $('#active-order-link').attr('href', 'track.php?code=' + encodeURIComponent(order.order_code || lastOrderCode));

            let statusDesc = t('status_processing', 'sedang disiapkan');
            if (status === 'pending') {
              statusDesc = t('status_pending', 'menunggu pembayaran');
            } else if (status === 'ready') {
              statusDesc = t('status_ready', 'siap diambil');
            }
            $('#active-order-status-desc').text(statusDesc);
            $('#active-order-banner').removeClass('hidden');
          } else {
            // Pesanan sudah selesai (completed) atau dibatalkan (cancelled)
            localStorage.removeItem('nola_last_order');
            localStorage.removeItem('Kopi Nusa_last_order');
            $('#active-order-banner').addClass('hidden');
          }
        })
        .catch(function () {
          // Jika pesanan tidak ditemukan atau otorisasi gagal, bersihkan
          localStorage.removeItem('nola_last_order');
          localStorage.removeItem('Kopi Nusa_last_order');
          $('#active-order-banner').addClass('hidden');
        });
    } catch (e) {
      $('#active-order-banner').addClass('hidden');
    }
  }

  // Dismiss notifikasi pesanan aktif
  $(document).on('click', '#btn-dismiss-active-order', function (e) {
    e.preventDefault();
    try {
      localStorage.removeItem('nola_last_order');
      localStorage.removeItem('Kopi Nusa_last_order');
    } catch (err) {}
    $('#active-order-banner').addClass('hidden');
  });

  // Tanggapi perubahan bahasa in-place (tanpa reload, tetap di posisi dan halaman saat ini)
  $(document).on('nola:lang-changed Kopi Nusa:lang-changed', function () {
    renderCategoryChips();
    renderMenuCatalog();
    checkActiveOrder();
    showGlaceSlide(currentGlaceSlide, true);
    if (activeDetailMenu) {
      const menuName = window.I18n ? window.I18n.translateMenuName(activeDetailMenu.name) : activeDetailMenu.name;
      const menuCategory = window.I18n ? window.I18n.translateCategory(activeDetailMenu.category_name || 'Kafe') : (activeDetailMenu.category_name || 'Kafe');
      const menuDesc = window.I18n ? window.I18n.translateMenuDesc(activeDetailMenu.description, activeDetailMenu.name) : activeDetailMenu.description;
      $('#modal-menu-title').text(menuName);
      $('#modal-menu-category').text(menuCategory);
      $('#modal-menu-desc').text(menuDesc || t('no_description', 'Tidak ada deskripsi.'));
    }
  });

  // ============================================================
  // LANDING REVEAL & SCROLL CONTROLS
  // Panorama / camera engine lives in js/cafe-motion.js
  // ============================================================

  // 1. Line-by-Line Title Reveal
  setTimeout(function () {
    $('#lp-title').addClass('is-revealed');
  }, 100);

  // 2. Scroll Reveal Observer [data-rv]
  if (typeof IntersectionObserver !== 'undefined') {
    const rvObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          $(entry.target).addClass('is-in');
          rvObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

    $('[data-rv]').each(function () {
      rvObserver.observe(this);
    });
  } else {
    $('[data-rv]').addClass('is-in');
  }

  // 3. Pinned Sticky Menu Track (Scroll down -> horizontal shift + blue bar)
  const $menuSec = $('#lp-menu');
  const $track = $('#lp-track');
  const $mprogBar = $('#lp-mprog-bar');

  let targetTrackP = 0;
  let currentTrackP = 0;
  let isTrackRunning = false;

  function updateMenuTrackScroll() {
    if (!$menuSec.length || !$track.length) return;
    const secEl = $menuSec[0];
    const rect = secEl.getBoundingClientRect();
    const scrollableDist = secEl.offsetHeight - window.innerHeight;
    if (scrollableDist <= 0) return;

    // Progress p: 0 when top of #lp-menu hits top of window, 1 when bottom arrives
    let p = -rect.top / scrollableDist;
    targetTrackP = Math.min(Math.max(p, 0), 1);

    if (!isTrackRunning) {
      isTrackRunning = true;
      requestAnimationFrame(renderTrackScroll);
    }
  }

  function renderTrackScroll() {
    // Lerp damping untuk pergeseran horizontal yang sangat halus (60fps)
    currentTrackP += (targetTrackP - currentTrackP) * 0.16;
    if (Math.abs(targetTrackP - currentTrackP) < 0.0005) {
      currentTrackP = targetTrackP;
    }

    if ($track.length) {
      const trackWidth = $track[0].scrollWidth || 2400;
      const viewWidth = window.innerWidth;
      const maxShift = Math.max(0, trackWidth - viewWidth + 80);
      $track[0].style.transform = `translate3d(${-maxShift * currentTrackP}px, 0, 0)`;
    }

    if ($mprogBar.length) {
      $mprogBar[0].style.transform = `scaleX(${currentTrackP})`;
    }

    if (Math.abs(targetTrackP - currentTrackP) >= 0.0005) {
      requestAnimationFrame(renderTrackScroll);
    } else {
      isTrackRunning = false;
    }
  }

  $(window).on('scroll', updateMenuTrackScroll);
  $(window).on('resize', updateMenuTrackScroll);
  updateMenuTrackScroll();

  // 4. Timeline Scroll Fill & Active Step Highlight
  function updateTimelineProgress() {
    const $tl = $('#lp-timeline');
    const $fill = $('#lp-timeline-fill');
    if (!$tl.length || !$fill.length) return;

    const tlTop = $tl.offset().top;
    const tlHeight = $tl.outerHeight();
    const winScroll = $(window).scrollTop();
    const winHeight = $(window).height();
    const focusPoint = winScroll + (winHeight * 0.55);

    if (focusPoint > tlTop) {
      const progress = Math.min(Math.max((focusPoint - tlTop) / tlHeight, 0), 1);
      $fill.css('transform', 'scaleY(' + progress + ')');

      $tl.find('li').each(function () {
        const $li = $(this);
        const liTop = $li.offset().top;
        if (focusPoint >= liTop) {
          $li.addClass('on');
        } else {
          $li.removeClass('on');
        }
      });
    } else {
      $fill.css('transform', 'scaleY(0)');
      $tl.find('li').removeClass('on');
    }
  }
  $(window).on('scroll', updateTimelineProgress);

  // 5. Stat Counter Animation (Count-up)
  let statsCounted = false;
  function initStatsCounter() {
    const $target = $('#lp-outlets-stats, #lp-statgrid, #lp-outlets');
    if (!$target.length || statsCounted) return;

    if (typeof IntersectionObserver !== 'undefined') {
      const statsObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting && !statsCounted) {
            statsCounted = true;
            $('.lp-counter').each(function () {
              const $el = $(this);
              const target = parseInt($el.data('target'), 10) || 0;
              const duration = 1400;
              const startTime = performance.now();

              function updateCount(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(ease * target);
                $el.text(current);

                if (progress < 1) {
                  requestAnimationFrame(updateCount);
                } else {
                  $el.text(target);
                }
              }
              requestAnimationFrame(updateCount);
            });
            statsObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15 });

      statsObserver.observe($target[0]);
    } else {
      $('.lp-counter').each(function () {
        $(this).text($(this).data('target'));
      });
    }
  }
  initStatsCounter();

  // Scroll Navbar Motion (Kopi Calf inspired)
  const $lpNav = $('#lp-nav');
  const $categoryChipsBar = $('#category-chips-bar');
  let lastNavScrollY = window.pageYOffset || document.documentElement.scrollTop;
  let navTicking = false;

  // Spy on active sections: #lp-menu, #lp-steps, #lp-outlets, #menu-catalog-section
  const navSpySections = [
    { id: '#lp-menu', el: $('#lp-menu') },
    { id: '#lp-steps', el: $('#lp-steps') },
    { id: '#lp-outlets', el: $('#lp-outlets') },
    { id: '#menu-catalog-section', el: $('#menu-catalog-section') }
  ];

  function updateNavbarOnScroll() {
    const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;

    // 1. .is-scrolled saat scroll > 30px (tinggi menyusut 60px -> 52px, background solid glass)
    if (currentScrollY > 30) {
      $lpNav.addClass('is-scrolled solid');
    } else {
      $lpNav.removeClass('is-scrolled solid');
    }

    // 2. Navbar selalu terlihat stabil di atas (tidak pernah hilang saat scroll-down)
    $lpNav.removeClass('is-hidden');
    $('body').removeClass('nav-hidden');

    // 3. Highlight active nav link dengan garis bawah biru animasi scaleX
    const midPoint = currentScrollY + window.innerHeight * 0.35;
    let activeSectionId = null;
    navSpySections.forEach(function (sec) {
      if (sec.el.length) {
        const top = sec.el.offset().top;
        const bottom = top + sec.el.outerHeight();
        if (midPoint >= top && midPoint < bottom) {
          activeSectionId = sec.id;
        }
      }
    });

    $('.lp-nav-links a').each(function () {
      const href = $(this).attr('href');
      if (href === activeSectionId) {
        $(this).addClass('on');
      } else {
        $(this).removeClass('on');
      }
    });

    lastNavScrollY = Math.max(0, currentScrollY);
    navTicking = false;
  }

  $(window).on('scroll', function () {
    if (!navTicking) {
      window.requestAnimationFrame(updateNavbarOnScroll);
      navTicking = true;
    }
  });
  updateNavbarOnScroll();

  // Smooth scroll anchor link
  $(document).on('click', 'a[href^="#"]', function (e) {
    const href = $(this).attr('href');
    if (!href || href === '#') return;
    const $target = $(href);
    if ($target.length) {
      e.preventDefault();
      $('html, body').animate({
        scrollTop: $target.offset().top - 80
      }, 500);
    }
  });

  checkActiveOrder();
  loadCategories();
  loadMenus();
});

