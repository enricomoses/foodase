/**
 * Script Halaman Menu Pelanggan (Gaya Uber Eats).
 * FR-CMENU-01 s.d. FR-CMENU-04, prd.md §12.3-12.4.
 */
$(function () {
  'use strict';

  // SVG Ikon Plus
  const SVG_PLUS = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';

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

  function renderCategoryChips() {
    const $chipsContainer = $('#category-chips');
    // Hapus chip selain "Semua"
    $chipsContainer.find('.category-chip:not([data-category-id=""])').remove();

    allCategories.forEach(function (cat) {
      const chipHtml = `
        <button
          type="button"
          class="category-chip h-9 px-4 rounded-full text-xs font-semibold whitespace-nowrap transition-colors bg-surface text-subtle hover:bg-neutral-200 hover:text-ink cursor-pointer"
          data-category-id="${cat.id}"
        >
          ${UI.escape(cat.name)}
        </button>
      `;
      $chipsContainer.append(chipHtml);
    });
  }

  // Klik chip kategori
  $('#category-chips').on('click', '.category-chip', function () {
    const $btn = $(this);
    const catId = $btn.data('category-id');
    currentCategoryId = catId ? String(catId) : '';

    // Perbarui style chip aktif (aktif hitam, tidak aktif abu surface)
    $('#category-chips .category-chip')
      .removeClass('bg-black text-white')
      .addClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink');

    $btn
      .removeClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink')
      .addClass('bg-black text-white');

    loadMenus();
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
    handleSearchInput(val);
  });

  $('#mobile-search-input').on('input', function () {
    const val = $(this).val();
    $('#global-search-input').val(val);
    handleSearchInput(val);
  });

  $(document).on('click', '#btn-mobile-nav-search', function (e) {
    e.preventDefault();
    const $search = $('#mobile-search-input:visible, #global-search-input:visible').first();
    if ($search.length) {
      $('html, body').animate({ scrollTop: Math.max(0, $search.offset().top - 80) }, 300, function () {
        $search.trigger('focus');
      });
    }
  });

  // Reset filter dari state kosong
  $('#btn-reset-filter').on('click', function () {
    currentSearchQuery = '';
    currentCategoryId = '';
    $('#global-search-input, #mobile-search-input').val('');

    $('#category-chips .category-chip')
      .removeClass('bg-black text-white')
      .addClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink');

    $('#category-chips .category-chip[data-category-id=""]')
      .removeClass('bg-surface text-subtle hover:bg-neutral-200 hover:text-ink')
      .addClass('bg-black text-white');

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
        renderMenuCatalog();
      })
      .catch(function (err) {
        $('#menu-loading').addClass('hidden');
        $('#menu-error-message').text(err.message || 'Gagal memuat katalog menu.');
        $('#menu-error').removeClass('hidden');
      });
  }

  function renderMenuCatalog() {
    if (!allMenus.length) {
      if (currentSearchQuery) {
        $('#menu-empty-message').text(`Tidak ada menu yang sesuai dengan kata kunci "${currentSearchQuery}".`);
      } else {
        $('#menu-empty-message').text('Belum ada menu yang tersedia untuk kategori ini.');
      }
      $('#menu-empty').removeClass('hidden');
      $('#menu-container').addClass('hidden');
      return;
    }

    // Kelompokkan menu berdasarkan kategori
    const grouped = {};
    allMenus.forEach(function (m) {
      const catName = m.category_name || 'Lainnya';
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
          slidesHtml += `
            <div
              class="carousel-slide menu-card relative w-60 sm:w-68 rounded-2xl border border-line bg-white p-3 shadow-sm hover:shadow-md hover:border-black/30 transition-all cursor-pointer group flex flex-col justify-between shrink-0"
              data-id="${m.id}"
            >
              <!-- Container Foto Menu Favorit -->
              <div class="relative w-full h-36 sm:h-40 rounded-xl overflow-hidden bg-surface mb-2.5">
                <img
                  src="${imgSrc}"
                  alt="${UI.escape(m.name)}"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  onerror="this.src='${defaultPlaceholder}'"
                >
                <span class="absolute top-2 left-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-black/80 backdrop-blur-sm text-white shadow">
                  <svg class="h-2.5 w-2.5 text-accent" fill="currentColor" viewBox="0 0 24 24">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                  <span>Paling Disukai</span>
                </span>
                <span class="absolute bottom-2 right-2 px-2.5 py-0.5 rounded-full bg-white/95 backdrop-blur-sm text-ink text-xs font-bold shadow tabular-nums">
                  ${formatRupiah(m.price)}
                </span>
              </div>

              <!-- Deskripsi & Info Menu -->
              <div class="flex-1 flex flex-col justify-between">
                <div>
                  <span class="text-[10px] font-bold text-subtle uppercase tracking-wider">${UI.escape(m.category_name || 'Kafe')}</span>
                  <h3 class="font-bold text-ink text-sm sm:text-base leading-snug line-clamp-1 mt-0.5">
                    ${UI.escape(m.name)}
                  </h3>
                  ${m.description ? `<p class="mt-1 text-xs text-subtle line-clamp-2 leading-relaxed">${UI.escape(m.description)}</p>` : ''}
                </div>

                <div class="mt-3 pt-2.5 border-t border-line flex items-center justify-between">
                  <span class="font-bold text-xs sm:text-sm text-ink tabular-nums">
                    ${formatRupiah(m.price)}
                  </span>
                  <button
                    type="button"
                    class="btn-card-add h-8 px-3 rounded-full bg-black text-white text-xs font-semibold hover:bg-neutral-800 transition-colors flex items-center gap-1 cursor-pointer"
                    data-id="${m.id}"
                    title="Tambah ke pesanan"
                  >
                    <span>+ Tambah</span>
                  </button>
                </div>
              </div>
            </div>
          `;
        });

        const carouselSectionHtml = `
          <section class="favorite-carousel-section mb-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
              <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-accent-light text-accent text-[11px] font-bold mb-1">
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                  <span>Pilihan Favorit Kafe</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-ink">
                  Menu Favorit & Paling Laris
                </h2>
                <p class="text-xs text-subtle mt-0.5">Geser ke samping untuk menjelajah menu favorit pilihan pelanggan</p>
              </div>

              <!-- Tombol Navigasi Geser (Prev & Next) -->
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  id="btn-carousel-prev"
                  class="w-9 h-9 rounded-full bg-surface border border-line text-ink hover:bg-neutral-200 transition-colors flex items-center justify-center cursor-pointer shadow-sm"
                  title="Geser ke kiri"
                  aria-label="Sebelumnya"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                  </svg>
                </button>
                <button
                  type="button"
                  id="btn-carousel-next"
                  class="w-9 h-9 rounded-full bg-surface border border-line text-ink hover:bg-neutral-200 transition-colors flex items-center justify-center cursor-pointer shadow-sm"
                  title="Geser ke kanan"
                  aria-label="Selanjutnya"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Track Geser Horizontal -->
            <div id="favorite-carousel-track" class="carousel-track flex gap-3.5 sm:gap-4 overflow-x-auto no-scrollbar py-1">
              ${slidesHtml}
            </div>
          </section>
        `;
        $container.append(carouselSectionHtml);
      }
    }

    Object.keys(grouped).forEach(function (categoryName) {
      const items = grouped[categoryName];

      let cardsHtml = '';
      items.forEach(function (m) {
        const isAvail = !!m.is_available;
        const imgSrc = m.image_url || defaultPlaceholder;
        const disabledClass = !isAvail ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-black/30 hover:shadow-sm';

        cardsHtml += `
          <div
            class="menu-card relative flex items-center justify-between p-3.5 sm:p-4 rounded-xl border border-line bg-white transition-all ${disabledClass}"
            data-id="${m.id}"
          >
            <!-- Teks di Kiri -->
            <div class="flex-1 pr-3 min-w-0">
              <h3 class="font-bold text-ink text-sm sm:text-base leading-snug line-clamp-2">
                ${UI.escape(m.name)}
              </h3>
              <div class="mt-1 font-semibold text-xs sm:text-sm text-ink tabular-nums">
                ${formatRupiah(m.price)}
              </div>
              ${m.description ? `<p class="mt-1 text-xs text-subtle line-clamp-2 leading-relaxed">${UI.escape(m.description)}</p>` : ''}
              ${!isAvail ? '<span class="inline-block mt-2 px-2 py-0.5 rounded-full text-[11px] font-bold bg-danger-light text-danger">Habis</span>' : ''}
            </div>

            <!-- Foto Persegi 112 px di Kanan -->
            <div class="relative w-28 h-28 shrink-0">
              <img
                src="${imgSrc}"
                alt="${UI.escape(m.name)}"
                class="w-28 h-28 rounded-xl object-cover bg-surface border border-line"
                onerror="this.src='${defaultPlaceholder}'"
              >
              ${isAvail ? `
                <!-- Tombol "+" Bulat Putih di Pojok Kanan Bawah Foto -->
                <button
                  type="button"
                  class="btn-card-add absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-full bg-white text-ink shadow-md border border-line flex items-center justify-center hover:bg-black hover:text-white hover:border-black transition-all cursor-pointer"
                  data-id="${m.id}"
                  title="Tambah ke keranjang"
                >
                  ${SVG_PLUS}
                </button>
              ` : ''}
            </div>
          </div>
        `;
      });

      const sectionHtml = `
        <section class="category-section">
          <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-ink mb-3 sm:mb-4">
            ${UI.escape(categoryName)}
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4">
            ${cardsHtml}
          </div>
        </section>
      `;

      $container.append(sectionHtml);
    });

    $container.removeClass('hidden');

    if (window.Theme && typeof window.Theme.isDark === 'function' && window.Theme.isDark()) {
      window.Theme._applyTheme(true);
    }
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

    CartStore.add(menu, 1);
    UI.toast('success', 'Ditambahkan ke keranjang');
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
      UI.toast('warning', 'Menu ini sedang tidak tersedia (Habis).');
      return;
    }

    openDetailModal(menu);
  });

  function openDetailModal(menu) {
    activeDetailMenu = menu;
    activeDetailQty = 1;

    const imgSrc = menu.image_url || defaultPlaceholder;
    $('#detail-image').attr('src', imgSrc).attr('alt', menu.name);
    $('#detail-category').text(menu.category_name || 'Menu');
    $('#detail-name').text(menu.name);
    $('#detail-price').text(formatRupiah(menu.price));
    $('#detail-description').text(menu.description || 'Tidak ada deskripsi.');

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
      $('#detail-add-text').html(`Tambah &middot; ${formatRupiah(total)}`);
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

    CartStore.add(activeDetailMenu, activeDetailQty);
    UI.closeModal('modal-menu-detail');
    UI.toast('success', 'Ditambahkan ke keranjang');
  });

  // ============================================================
  // HERO SHOWCASE BANNER CAROUSEL
  // ============================================================
  let currentHeroSlide = 0;
  const $heroSlides = $('#hero-carousel-slides .hero-slide');
  const totalHeroSlides = $heroSlides.length;
  let heroTimer = null;

  function showHeroSlide(index) {
    if (index >= totalHeroSlides) index = 0;
    if (index < 0) index = totalHeroSlides - 1;
    currentHeroSlide = index;

    $heroSlides.each(function (i) {
      if (i === currentHeroSlide) {
        $(this)
          .removeClass('opacity-0 pointer-events-none z-0')
          .addClass('active opacity-100 z-10');
      } else {
        $(this)
          .removeClass('active opacity-100 z-10')
          .addClass('opacity-0 pointer-events-none z-0');
      }
    });

    // Perbarui indikator dots
    $('#hero-carousel-dots .hero-dot').each(function (i) {
      if (i === currentHeroSlide) {
        $(this).removeClass('w-2 bg-white/40').addClass('w-7 bg-white');
      } else {
        $(this).removeClass('w-7 bg-white').addClass('w-2 bg-white/40');
      }
    });
  }

  function startHeroTimer() {
    stopHeroTimer();
    heroTimer = setInterval(function () {
      showHeroSlide(currentHeroSlide + 1);
    }, 5000);
  }

  function stopHeroTimer() {
    if (heroTimer) {
      clearInterval(heroTimer);
      heroTimer = null;
    }
  }

  $('#btn-hero-slide-prev').on('click', function (e) {
    e.preventDefault();
    showHeroSlide(currentHeroSlide - 1);
    startHeroTimer();
  });

  $('#btn-hero-slide-next').on('click', function (e) {
    e.preventDefault();
    showHeroSlide(currentHeroSlide + 1);
    startHeroTimer();
  });

  $('#hero-carousel-dots').on('click', '.hero-dot', function (e) {
    e.preventDefault();
    const idx = parseInt($(this).data('slide'), 10) || 0;
    showHeroSlide(idx);
    startHeroTimer();
  });

  $('#hero-carousel-wrap')
    .on('mouseenter', stopHeroTimer)
    .on('mouseleave', startHeroTimer);

  startHeroTimer();

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
  // INISIALISASI AWAL
  // ============================================================
  function checkActiveOrder() {
    try {
      const lastOrderCode = localStorage.getItem('foodease_last_order');
      if (lastOrderCode) {
        $('#active-order-code').text('#' + lastOrderCode);
        $('#active-order-link').attr('href', 'track.php?code=' + encodeURIComponent(lastOrderCode));
        $('#active-order-banner').removeClass('hidden');
      }
    } catch (e) {}
  }

  checkActiveOrder();
  loadCategories();
  loadMenus();
});

