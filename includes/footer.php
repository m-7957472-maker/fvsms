
    <!-- copyright -->
    <div class="copyright">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <p>GHP INVENTORY MANAGEMENT SYSTEM
                    </p>
                </div>
                <div class="col-lg-6 text-right col-md-12">
                    <div class="social-icons">
                        <ul>
                            <li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
                            <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="#" target="_blank"><i class="fab fa-linkedin"></i></a></li>
                            <li><a href="#" target="_blank"><i class="fab fa-dribbble"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end copyright -->

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var contactLabel = "<?php echo addslashes(__('HERO_BTN_CONTACT')); ?>";
        var primaryLabel = "<?php echo addslashes(__('HERO_BTN_PRIMARY')); ?>";
        var aboutLabel = "<?php echo addslashes(__('ABOUT_US')); ?>";
        var cartLabel = "<?php echo addslashes(__('CART')); ?>";

        document.querySelectorAll('a.bordered-btn').forEach(function(el){
            if(el.textContent.trim() === 'Contact Us' || el.textContent.trim() === 'Contact us') el.textContent = contactLabel;
        });
        document.querySelectorAll('a.boxed-btn').forEach(function(el){
            if(el.textContent.trim() === 'Visit Inventory') el.textContent = primaryLabel;
        });

        document.querySelectorAll('.breadcrumb-text h1').forEach(function(el){
            var t = el.textContent.trim();
            if(t === 'Cart') el.textContent = cartLabel;
            if(t === 'About Us' || t === 'About us') el.textContent = aboutLabel;
        });

        var textReplacements = {
            'Checkout History': "<?php echo addslashes(__('CHECKOUT_HISTORY')); ?>",
            'No checkout history found.': "<?php echo addslashes(__('NO_CHECKOUT_HISTORY')); ?>",
            'Contact us': "<?php echo addslashes(__('CONTACT')); ?>",
            'Contact Us': "<?php echo addslashes(__('CONTACT')); ?>"
        };
        Object.keys(textReplacements).forEach(function(k){
            document.querySelectorAll('td,th,h1,h2,h3,h4,p,span,a').forEach(function(n){
                if(n.textContent.trim() === k) n.textContent = textReplacements[k];
            });
        });
    });
    </script>

    <!-- Enhanced checkout submit handler (global) -->
    <script>
    (function(){
        window.enhancedCheckoutSubmit = function(f, e){
            if (e && e.stopImmediatePropagation) e.stopImmediatePropagation();
            if (e && e.preventDefault) e.preventDefault();
            try {
                var submitBtn = f.querySelector('#checkoutSubmit') || f.querySelector('button[type=submit]');
                var statusEl = document.getElementById('checkoutStatus');
                if (statusEl) { statusEl.classList.remove('d-none'); statusEl.classList.remove('alert-danger','alert-success','alert-warning'); statusEl.classList.add('alert-info'); statusEl.textContent = 'Menghantar pesanan...'; }
                if (submitBtn) submitBtn.disabled = true;
                var fd = new FormData(f);
                if (!fd.has('client_ts')) fd.append('client_ts', Date.now());
                var didRespond = false;
                var timeoutMs = 10000;
                var timer = setTimeout(function(){
                    if (!didRespond) {
                        if (statusEl) { statusEl.classList.remove('alert-info'); statusEl.classList.add('alert-warning'); statusEl.textContent = 'Tiada respons daripada pelayan. Menghantar secara biasa...'; }
                        try { f.submit(); } catch(e){ console.error('fallback submit failed', e); alert('Gagal menghantar - sila cuba lagi.'); if (submitBtn) submitBtn.disabled = false; }
                    }
                }, timeoutMs);

                fetch(f.action, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function(r){ didRespond = true; clearTimeout(timer); return r.text(); })
                .then(function(txt){
                    console.log('enhanced checkout response:', txt);
                    if (statusEl) { statusEl.classList.remove('alert-info','alert-warning'); }
                    try {
                        var jsn = null;
                        if (txt.trim().startsWith('{') || txt.trim().startsWith('[')) jsn = JSON.parse(txt);
                        if (jsn && jsn.success) {
                            if (statusEl) { statusEl.classList.add('alert-success'); statusEl.textContent = jsn.message || 'Pesanan direkodkan.'; }
                            var ord = jsn.orderNumber || '';
                            alert(jsn.message || '<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>' + (ord ? '. Nombor pesanan: ' + ord : ''));
                            window.location = '/fvsms/my-orders.php';
                            return;
                        }
                    } catch(ex) { console.warn('enhancedCheckout: JSON parse failed', ex); }

                    if (txt.indexOf('<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>') !== -1) {
                        if (statusEl) { statusEl.classList.add('alert-success'); statusEl.textContent = '<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>'; }
                        var m = txt.match(/(\d{6,})/);
                        var ord = m ? m[1] : '';
                        alert('<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>' + (ord ? ('. Nombor pesanan: ' + ord) : ''));
                        window.location = '/fvsms/my-orders.php';
                        return;
                    }

                    var mm = txt.match(/alert\((?:"([^\"]+)"|'([^']+)')\)/i);
                    if (mm) { var msg = mm[1] || mm[2]; if (statusEl) { statusEl.classList.add('alert-success'); statusEl.textContent = msg; } alert(msg); if (msg.indexOf('<?php echo addslashes(__('ORDER_PLACED_PREFIX')); ?>') !== -1){ window.location = '/fvsms/my-orders.php'; } return; }

                    var stripped = txt.replace(/<[^>]+>/g,'').trim();
                    if (stripped.length) { if (statusEl) { statusEl.classList.add('alert-danger'); statusEl.textContent = stripped; } alert(stripped); }
                    else { if (statusEl) { statusEl.classList.add('alert-danger'); statusEl.textContent = 'Ralat semasa semak keluar. Sila semak Console (F12) untuk maklumat lanjut.'; } alert('Ralat semasa semak keluar. Sila semak Console (F12) untuk maklumat lanjut.'); console.error('enhanced checkout response empty'); }
                })
                .catch(function(err){ clearTimeout(timer); console.error('enhanced checkout fetch error', err); if (statusEl) { statusEl.classList.remove('alert-info'); statusEl.classList.add('alert-danger'); statusEl.textContent = 'Ralat sambungan ke pelayan: ' + (err.message || err); } alert('Ralat sambungan ke pelayan: ' + (err.message || err)); })
                .finally(function(){ if (submitBtn) submitBtn.disabled = false; if (statusEl) { setTimeout(function(){ statusEl.classList.add('d-none'); }, 3000); } });
            } catch(e) { console.error('enhancedCheckout exception', e); alert('Ralat dalaman. Sila cuba lagi.'); return false; }
            return false;
        };
    })();
    </script>