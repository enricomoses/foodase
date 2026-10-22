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
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            ${cardsHtml}
          </div>
        </section>
      `;

      $container.append(sectionHtml);
    });

    $container.removeClass('hidden');
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
  // INISIALISASI AWAL
  // ============================================================
  loadCategories();
  loadMenus();
});
