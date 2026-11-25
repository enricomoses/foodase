/**
 * cafe-motion.js — Café 3D Virtual Tour & Panorama Walkthrough Engine.
 *
 * Mendukung penggabungan multi-image interior café menjadi satu pengalaman
 * 3D Virtual Tour yang interaktif dan mulus:
 * - Multi-spot interior nodes (6 spot foto kafe).
 * - Smooth transition (dissolve cross-fade & zoom push) antar ruangan/spot.
 * - Kontrol scroll untuk berjalan maju di landing page & tour mode.
 * - Kontrol drag/mouse/touch untuk menoleh 360°/pan & tilt.
 * - Mini thumbnail spot switcher + navigasi Prev/Next spot.
 * - Auto-walk & rotasi lembut otomatis saat idle.
 */
(function () {
  'use strict';

  var stage = document.getElementById('cafe-stage');
  if (!stage) return;

  var inner = document.getElementById('cafe-stage-inner');
  var layerCurr = document.getElementById('tour-layer-curr');
  var layerNext = document.getElementById('tour-layer-next');
  var hero = document.getElementById('landing-hero');
  var heroIn = document.getElementById('lp-hero-in');
  var hint = document.getElementById('cafe-hint');
  var nav = document.getElementById('lp-nav');
  var hud = document.getElementById('tour-hud');
  var progress = document.getElementById('tour-progress');
  var autoBtn = document.getElementById('btn-tour-auto');
  var prevBtn = document.getElementById('btn-tour-prev');
  var nextBtn = document.getElementById('btn-tour-next');
  var spotNameEl = document.getElementById('tour-spot-name');
  var spotsTrack = document.querySelector('.tour-spots-track');
  var startButtons = document.querySelectorAll('[data-tour-start]');

  // Definisi Spot Virtual Tour dari gambar interior kafe yang digabungkan
  var TOUR_SPOTS = [
    {
      id: 0,
      name: 'Main Bar & Counter Area',
      image: 'public/assets/images/tour/tour-4.webp',
      desc: 'Area pemesanan dan barista counter dengan nuansa hangat modern.'
    },
    {
      id: 1,
      name: 'Central Dining & Indoor Garden',
      image: 'public/assets/images/tour/tour-5.webp',
      desc: 'Area tempat duduk utama dengan pepohonan hijau dan meja panjang.'
    },
    {
      id: 2,
      name: 'Warm Lounge & Iconic Wall',
      image: 'public/assets/images/tour/tour-6.webp',
      desc: 'Ruang tengah yang lega dengan pencahayaan estetik dan aksen ikonik.'
    },
    {
      id: 3,
      name: 'Cozy Workspace & Red Table',
      image: 'public/assets/images/tour/tour-3.webp',
      desc: 'Area produktif yang nyaman untuk kerja santai maupun hangout kelompok.'
    },
    {
      id: 4,
      name: 'Glass Facade & Drive-Through View',
      image: 'public/assets/images/tour/tour-2.webp',
      desc: 'Pemandangan jendela kaca luas menghadap ke luar kafe.'
    },
    {
      id: 5,
      name: 'Private Room & Fluted Glass Corner',
      image: 'public/assets/images/tour/tour-1.webp',
      desc: 'Sudut tenang dengan dinding kaca tekstur dan tanaman hias.'
    }
  ];

  var CONFIG = {
    maxYaw: 110,
    maxPitch: 25,
    yawSensitivity: 0.18,
    pitchSensitivity: 0.14,
    dragLerp: 0.12,
    walkLerp: 0.08,
    pointerYaw: 2.8,
    pointerPitch: 1.6,
    pointerLerp: 0.06,
    idleDelay: 1000,
    idleYaw: 1.6,
    pulseSpeed: 0.0016,
    autoWalkSpeed: 0.035,
    tourWheelSensitivity: 0.0009,
    // Landing Page continuous ambient walk & pan
    ambientWalkSpeed: 0.045,
    ambientPanSpeed: 2.5
  };

  var state = {
    activeSpotIndex: 0,
    isTransitioning: false,
    walk: 0,
    walkTarget: 0,
    ambientWalk: 0,
    ambientWalkDir: 1,
    ambientPan: 0,
    ambientPanDir: 1,
    yaw: 0,
    yawTarget: 0,
    pitch: 0,
    pitchTarget: 0,
    pointerYaw: 0,
    pointerYawTarget: 0,
    pointerPitch: 0,
    pointerPitchTarget: 0,
    dragging: false,
    pointerId: null,
    dragStartX: 0,
    dragStartY: 0,
    dragBaseYaw: 0,
    dragBasePitch: 0,
    dragIsTouch: false,
    lastInput: performance.now(),
    touring: false,
    tourWalk: 0.05,
    tourAuto: false,
    tourDirection: 1,
    scrollY: window.pageYOffset || 0,
    viewportH: window.innerHeight || 1,
    viewportW: window.innerWidth || 1,
    heroH: 600,
    lastFocus: null,
    savedScroll: 0
  };

  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  function lerp(from, to, amount) {
    return from + (to - from) * amount;
  }

  function isInteractive(target) {
    if (!(target instanceof Element)) return false;
    return !!target.closest(
      'a, button, input, select, textarea, label, [role="button"], [data-no-look], ' +
      '.lp-nav, .lang, .lp-card, .modal-dialog, .tour-top, .tour-bottom, .tour-spots-bar'
    );
  }

  function cacheViewport() {
    state.viewportW = window.innerWidth || 1;
    state.viewportH = window.innerHeight || 1;
    state.scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
    state.heroH = hero ? hero.offsetHeight : state.viewportH;
    if (nav) nav.classList.toggle('solid', state.scrollY > 40);
    if (hint && !state.touring) hint.classList.toggle('is-hidden', state.scrollY > 90);
  }

  function onScroll() {
    state.scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
    if (nav) nav.classList.toggle('solid', state.scrollY > 40);
    if (hint && !state.touring) hint.classList.toggle('is-hidden', state.scrollY > 90);

    // Otomatis ganti spot sesuai kedalaman scroll jika tidak dalam tour modal
    if (!state.touring) {
      var scrollFraction = clamp(state.scrollY / Math.max(1, state.viewportH * 2.2), 0, 0.999);
      var spotIndex = Math.floor(scrollFraction * TOUR_SPOTS.length);
      if (spotIndex !== state.activeSpotIndex && !state.isTransitioning) {
        goToSpot(spotIndex, false);
      }
    }
  }

  /* ── Multi-Node Virtual Tour Switcher ──────────────────────── */

  function renderSpotsNavigator() {
    if (!spotsTrack) return;
    spotsTrack.innerHTML = '';
    TOUR_SPOTS.forEach(function (spot, idx) {
      var thumb = document.createElement('button');
      thumb.type = 'button';
      thumb.className = 'tour-spot-thumb' + (idx === state.activeSpotIndex ? ' is-active' : '');
      thumb.title = spot.name;
      thumb.setAttribute('data-spot-id', idx);
      thumb.innerHTML = '<img src="' + spot.image + '" alt="' + spot.name + '">';
      thumb.addEventListener('click', function () {
        goToSpot(idx, true);
      });
      spotsTrack.appendChild(thumb);
    });
  }

  function goToSpot(index, animated) {
    if (index < 0 || index >= TOUR_SPOTS.length) return;
    if (index === state.activeSpotIndex && !animated) return;

    state.activeSpotIndex = index;
    var spot = TOUR_SPOTS[index];

    if (spotNameEl) {
      spotNameEl.textContent = spot.name;
    }

    if (spotsTrack) {
      var thumbs = spotsTrack.querySelectorAll('.tour-spot-thumb');
      thumbs.forEach(function (t, i) {
        t.classList.toggle('is-active', i === index);
      });
    }

    if (!layerCurr || !layerNext) {
      if (layerCurr) layerCurr.style.backgroundImage = 'url("' + spot.image + '")';
      return;
    }

    state.isTransitioning = true;
    layerNext.style.backgroundImage = 'url("' + spot.image + '")';
    layerNext.classList.add('active');

    setTimeout(function () {
      layerCurr.style.backgroundImage = 'url("' + spot.image + '")';
      layerNext.classList.remove('active');
      state.isTransitioning = false;
    }, 600);
  }

  function prevSpot() {
    var nextIdx = (state.activeSpotIndex - 1 + TOUR_SPOTS.length) % TOUR_SPOTS.length;
    goToSpot(nextIdx, true);
  }

  function nextSpot() {
    var nextIdx = (state.activeSpotIndex + 1) % TOUR_SPOTS.length;
    goToSpot(nextIdx, true);
  }

  /* ── Pointer & Look Controls ───────────────────────────────── */

  function onPointerDown(event) {
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    if (isInteractive(event.target)) return;
    if (!state.touring && !(event.target instanceof Element && event.target.closest('#landing-hero, #cafe-stage'))) {
      return;
    }

    state.dragging = true;
    state.pointerId = event.pointerId;
    state.dragStartX = event.clientX;
    state.dragStartY = event.clientY;
    state.dragBaseYaw = state.yawTarget;
    state.dragBasePitch = state.pitchTarget;
    state.dragIsTouch = event.pointerType === 'touch';
    state.pointerYawTarget = 0;
    state.pointerPitchTarget = 0;
    state.lastInput = performance.now();
    document.body.classList.add('is-dragging');
  }

  function onPointerMove(event) {
    if (state.dragging && event.pointerId === state.pointerId) {
      var dx = event.clientX - state.dragStartX;
      var dy = event.clientY - state.dragStartY;

      if (state.dragIsTouch && !state.touring && Math.abs(dx) < Math.abs(dy) * 1.1) {
        return;
      }

      state.yawTarget = clamp(
        state.dragBaseYaw + dx * CONFIG.yawSensitivity,
        -CONFIG.maxYaw,
        CONFIG.maxYaw
      );
      state.pitchTarget = clamp(
        state.dragBasePitch - dy * CONFIG.pitchSensitivity,
        -CONFIG.maxPitch,
        CONFIG.maxPitch
      );
      state.lastInput = performance.now();
      return;
    }

    if (event.pointerType === 'mouse') {
      var nx = event.clientX / state.viewportW - 0.5;
      var ny = event.clientY / state.viewportH - 0.5;
      state.pointerYawTarget = clamp(nx * 2 * CONFIG.pointerYaw, -CONFIG.pointerYaw, CONFIG.pointerYaw);
      state.pointerPitchTarget = clamp(-ny * 2 * CONFIG.pointerPitch, -CONFIG.pointerPitch, CONFIG.pointerPitch);
      state.lastInput = performance.now();
    }
  }

  function onPointerUp(event) {
    if (event && state.pointerId !== null && event.pointerId !== state.pointerId) return;
    state.dragging = false;
    state.pointerId = null;
    document.body.classList.remove('is-dragging');
    state.lastInput = performance.now();
  }

  /* ── Tour Modal Controls ───────────────────────────────────── */

  function setAuto(active) {
    state.tourAuto = !!active;
    if (autoBtn) {
      autoBtn.classList.toggle('is-on', state.tourAuto);
      autoBtn.setAttribute('aria-pressed', state.tourAuto ? 'true' : 'false');
    }
  }

  function openTour() {
    if (state.touring) return;
    state.touring = true;
    state.savedScroll = state.scrollY;
    state.tourWalk = 0.08;
    state.yawTarget = 0;
    state.pitchTarget = 0;
    state.lastFocus = document.activeElement;

    document.body.classList.add('is-touring');
    document.documentElement.style.overflow = 'hidden';

    if (hud) {
      hud.hidden = false;
      var focusable = hud.querySelector('.tour-close, button');
      if (focusable) focusable.focus();
    }
    renderSpotsNavigator();
    goToSpot(state.activeSpotIndex, false);
    setAuto(false);
  }

  function closeTour() {
    if (!state.touring) return;
    setAuto(false);
    state.touring = false;
    document.body.classList.remove('is-touring');
    document.documentElement.style.overflow = '';
    if (hud) hud.hidden = true;
    window.scrollTo(0, state.savedScroll);

    if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
      state.lastFocus.focus({ preventScroll: true });
    }
  }

  function toggleTour() {
    if (state.touring) closeTour();
    else openTour();
  }

  Array.prototype.forEach.call(startButtons, function (button) {
    button.addEventListener('click', openTour);
  });

  if (hud) {
    var closeBtn = hud.querySelector('[data-tour-exit]');
    if (closeBtn) closeBtn.addEventListener('click', closeTour);
    if (autoBtn) {
      autoBtn.addEventListener('click', function () {
        setAuto(!state.tourAuto);
      });
    }
    if (prevBtn) prevBtn.addEventListener('click', prevSpot);
    if (nextBtn) nextBtn.addEventListener('click', nextSpot);
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && state.touring) {
      event.preventDefault();
      closeTour();
      return;
    }
    if (!state.touring) return;

    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      state.yawTarget = clamp(state.yawTarget - 8, -CONFIG.maxYaw, CONFIG.maxYaw);
      state.lastInput = performance.now();
    } else if (event.key === 'ArrowRight') {
      event.preventDefault();
      state.yawTarget = clamp(state.yawTarget + 8, -CONFIG.maxYaw, CONFIG.maxYaw);
      state.lastInput = performance.now();
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      prevSpot();
    } else if (event.key === 'ArrowDown') {
      event.preventDefault();
      nextSpot();
    }
  });

  window.addEventListener('wheel', function (event) {
    if (!state.touring) return;
    event.preventDefault();
    setAuto(false);
    state.tourWalk = clamp(
      state.tourWalk + event.deltaY * CONFIG.tourWheelSensitivity,
      0,
      1
    );
    // Jika scroll melewati threshold, auto switch spot
    if (state.tourWalk >= 0.96) {
      state.tourWalk = 0.08;
      nextSpot();
    } else if (state.tourWalk <= 0.02) {
      state.tourWalk = 0.88;
      prevSpot();
    }
    state.lastInput = performance.now();
  }, { passive: false });

  window.addEventListener('pointerdown', onPointerDown, { passive: true });
  window.addEventListener('pointermove', onPointerMove, { passive: true });
  window.addEventListener('pointerup', onPointerUp, { passive: true });
  window.addEventListener('pointercancel', onPointerUp, { passive: true });

  /* ── 3D Render Loop ────────────────────────────────────────── */

  var lastFrame = performance.now();

  function frame(now) {
    var dt = Math.min((now - lastFrame) / 1000, 0.05);
    lastFrame = now;

    if (state.touring && state.tourAuto) {
      state.tourWalk += dt * CONFIG.autoWalkSpeed * state.tourDirection;
      state.yawTarget += dt * 4 * state.tourDirection;
      if (state.tourWalk >= 0.94) {
        state.tourWalk = 0.94;
        state.tourDirection = -1;
      } else if (state.tourWalk <= 0.06) {
        state.tourWalk = 0.06;
        state.tourDirection = 1;
      }
    }

    // On landing page, keep walking and panning continuously like a live walkthrough!
    if (!state.touring) {
      // Continuous ambient walk forward / oscillation
      state.ambientWalk += dt * CONFIG.ambientWalkSpeed * state.ambientWalkDir;
      if (state.ambientWalk >= 1) {
        state.ambientWalk = 1;
        state.ambientWalkDir = -1;
      } else if (state.ambientWalk <= 0) {
        state.ambientWalk = 0;
        state.ambientWalkDir = 1;
      }

      // Continuous slow camera sweep / pan
      if (!state.dragging && (now - state.lastInput > CONFIG.idleDelay)) {
        state.ambientPan += dt * CONFIG.ambientPanSpeed * state.ambientPanDir;
        if (state.ambientPan >= 16) {
          state.ambientPan = 16;
          state.ambientPanDir = -1;
        } else if (state.ambientPan <= -16) {
          state.ambientPan = -16;
          state.ambientPanDir = 1;
        }
      }
    }

    var scrollWalk = clamp(state.scrollY / Math.max(1, state.viewportH * 1.45), 0, 1);
    var walkTarget = state.touring
      ? state.tourWalk
      : clamp(scrollWalk + state.ambientWalk * 0.28, 0, 1);

    state.walk = lerp(state.walk, walkTarget, CONFIG.walkLerp);
    state.pointerYaw = lerp(state.pointerYaw, state.pointerYawTarget, CONFIG.pointerLerp);
    state.pointerPitch = lerp(state.pointerPitch, state.pointerPitchTarget, CONFIG.pointerLerp);

    var idleYaw = 0;
    if (!state.touring && !state.dragging) {
      idleYaw = state.ambientPan + Math.sin(now * CONFIG.pulseSpeed) * CONFIG.idleYaw;
    }

    var yaw = state.yawTarget + state.pointerYaw + idleYaw;
    var pitch = state.pitchTarget + state.pointerPitch;

    state.yaw = lerp(state.yaw, clamp(yaw, -CONFIG.maxYaw, CONFIG.maxYaw), CONFIG.dragLerp);
    state.pitch = lerp(state.pitch, clamp(pitch, -CONFIG.maxPitch, CONFIG.maxPitch), CONFIG.dragLerp);

    // Update Transform CSS 3D pada Stage
    if (inner) {
      var x = -state.yaw * 1.8;
      var y = state.pitch * 2.2;
      var scale = 1.05 + state.walk * 0.12;
      inner.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0) scale(' + scale + ')';
    }

    // Hero Text Fade
    if (heroIn && !state.touring) {
      var heroProgress = clamp(state.scrollY / Math.max(1, state.heroH * 0.85), 0, 1);
      heroIn.style.transform = 'translate3d(0,' + (-84 * heroProgress) + 'px,0)';
      heroIn.style.opacity = String(clamp(1 - heroProgress * 1.35, 0, 1));
    }

    // Progress Bar Tour
    if (state.touring && progress) {
      progress.style.transform = 'scaleX(' + state.tourWalk + ')';
    }

    requestAnimationFrame(frame);
  }

  function onResize() {
    cacheViewport();
  }

  cacheViewport();
  renderSpotsNavigator();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onResize);
  window.addEventListener('load', cacheViewport);
  requestAnimationFrame(frame);

  window.CafeTour = {
    start: openTour,
    end: closeTour,
    toggle: toggleTour,
    goToSpot: goToSpot,
    prevSpot: prevSpot,
    nextSpot: nextSpot,
    isOpen: function () { return state.touring; }
  };
})();
