(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var printBtn = document.getElementById('btn-print-receipt');
    var closeBtn = document.getElementById('btn-close-receipt');
    if (printBtn) {
      printBtn.addEventListener('click', function () {
        window.print();
      });
    }
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        window.close();
      });
    }
  });
})();
