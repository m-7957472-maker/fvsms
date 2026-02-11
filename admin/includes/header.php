<?php include_once(__DIR__ . '/../../includes/lang.php'); ini_set('display_errors',1); ini_set('display_startup_errors',1); error_reporting(E_ALL); ?>
                        <style>
                        /* UI tweaks: clearer text, smoother buttons, notification pulse */
                        .notif-pulse { position:relative; }
                        .notif-pulse .fa-bell { transition: transform .18s ease-in-out; }
                        .notif-pulse.pulse .fa-bell { animation: pulse 1.6s infinite; transform-origin:center; }
                        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
                        .admin-card { background: #fff; color:#222; border-radius:8px; box-shadow:0 6px 22px rgba(0,0,0,0.06); }
                        .admin-card .card-header { background:linear-gradient(90deg,#0d6efd,#6610f2); color:#fff; border-radius:8px 8px 0 0 }
                        .btn-smooth { transition: all .12s ease; border-radius:6px }
                        .products-table th, .products-table td { vertical-align: middle }
                        </style>

                        <script>
                            (function(){
                                var lastId = 0;
                                var badge = document.getElementById('adminNotifBadge');
                                var items = document.getElementById('adminNotifItems');
                                function makeEven(n){ n = parseInt(n)||0; return (n%2===0)?n:n+1; }
                                var adminStorageKey = 'fvsms_admin_unread';
                                var adminUnread = parseInt(localStorage.getItem(adminStorageKey) || '0') || 0;
                                function updateBadge(c){ if(!badge) return; var link = document.getElementById('adminNotifLink'); if(c>0){ badge.style.display='inline-block'; badge.textContent=makeEven(c); if(link) link.classList.add('pulse'); } else { badge.style.display='none'; if(link) link.classList.remove('pulse'); }}
                                // initialize admin badge from persisted value
                                updateBadge(adminUnread);
                                function addItem(n){ if(!items) return; var el=document.createElement('div'); el.style.padding='8px'; el.style.borderBottom='1px solid #f1f1f1';
                                    // choose message based on action
                                    if (n.action === 'restock') {
                                        el.textContent = 'Product '+n.productId+' qty '+n.qty+' '+n.unit;
                                    } else if (n.action && (n.action.indexOf('checkout') === 0)) {
                                        // show admin checkout message (single or cart); try to resolve product name and requester id
                                        var meta = {};
                                        try { meta = n.meta ? JSON.parse(n.meta) : {}; } catch(e) { meta = {}; }
                                        var requester = meta.requestedBy ? ('#'+meta.requestedBy) : 'user';
                                        el.textContent = 'User '+requester+' requested '+n.qty+' '+n.unit+' of product '+n.productId;
                                        // replace product id with name if possible
                                        fetch('/fvsms/scripts/get_product_names.php?ids=' + encodeURIComponent(n.productId), { credentials: 'same-origin' }).then(r=>r.json()).then(d=>{ if (d && d.ok && d.names && d.names[n.productId]) { el.textContent = 'User '+requester+' requested '+n.qty+' '+n.unit+' of product '+d.names[n.productId]; } }).catch(()=>{});
                                    } else {
                                        el.textContent = 'Product '+n.productId+' qty '+n.qty+' '+n.unit;
                                    }
                                    items.insertBefore(el, items.firstChild); }
                                function refreshProducts(ids){ if(!ids||!ids.length) return; fetch('/fvsms/scripts/get_product_qty.php?ids='+encodeURIComponent(ids.join(',')), {credentials:'same-origin'}).then(r=>r.json()).then(d=>{ if(d&&d.quantities){ Object.keys(d.quantities).forEach(function(id){ var qty=d.quantities[id]; var els=document.querySelectorAll('[data-product-id="'+id+'"]'); els.forEach(function(el){ var grams = qty*1000; var display=''; if (grams>=1000 && grams%1000===0) display=(grams/1000)+' kg'; else display=Math.round(grams)+' gram'; el.textContent = display; }); }); } }).catch(()=>{}); }
                                function poll(){ fetch('/fvsms/scripts/notifications_poll.php?last_id='+encodeURIComponent(lastId), {credentials:'same-origin'}).then(r=>r.json()).then(d=>{ if(!d) return; if(d.current_max && (!d.notifications || d.notifications.length===0) && lastId===0){ lastId = parseInt(d.current_max||0); return; } if(d&&d.notifications&&d.notifications.length){ var touched={}; d.notifications.forEach(function(n){ lastId=Math.max(lastId, parseInt(n.id||0)); addItem(n); touched[n.productId]=true; }); adminUnread = (parseInt(localStorage.getItem(adminStorageKey) || '0')||0) + d.notifications.length; localStorage.setItem(adminStorageKey, adminUnread); updateBadge(adminUnread); refreshProducts(Object.keys(touched)); } else if(d.current_max){ lastId = Math.max(lastId, parseInt(d.current_max||0)); } }).catch(()=>{}).finally(()=>setTimeout(poll,5000)); }
                                // dropdown toggle
                                var link = document.getElementById('adminNotifLink');
                                if(link){ link.classList.add('notif-pulse'); link.addEventListener('click', function(e){ e.preventDefault(); var dd=document.getElementById('adminNotifDropdown'); if(!dd) return; dd.style.display = dd.style.display==='block'?'none':'block'; adminUnread = 0; localStorage.setItem(adminStorageKey, 0); updateBadge(0); }); }
                                if (document.readyState === 'complete' || document.readyState === 'interactive') setTimeout(poll,1000); else document.addEventListener('DOMContentLoaded', function(){ setTimeout(poll,1000); });
                            })();
                        </script>

     <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand (dynamic logo) -->
            <a class="navbar-brand ps-3" href="dashboard.php">
                <?php if (!empty($site_settings['logo_choice']) && $site_settings['logo_choice'] === 'both'): ?>
                    <img src="../assets/img/logo1.png" style="height:34px;margin-right:8px;" alt="logo1">
                    <img src="../assets/img/logo2.png" style="height:34px;" alt="logo2">
                <?php else: ?>
                    <?php $adminLogo = !empty($site_settings['site_logo']) ? $site_settings['site_logo'] : 'logo.png'; ?>
                    <img src="../assets/img/<?php echo htmlentities($adminLogo); ?>" style="height:34px;" alt="logo">
                <?php endif; ?>
                &nbsp; <span style="font-weight:600;color:#fff;">GHP INVENTORY MANAGEMENT SYSTEM</span>
            </a>

            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0" method="post" action="search-orders.php">
                <div class="input-group">
                    <input class="form-control" type="text" name="searchinputdata" placeholder="<?php echo __('SEARCH_PLACEHOLDER'); ?>" aria-label="Search for..." aria-describedby="btnNavbarSearch" required />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="submit" name="search"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <div style="position:relative;margin-right:12px;">
                        <a id="adminNotifLink" href="#" style="color:#fff;">
                            <i class="fas fa-bell"></i>
                        </a>
                        <span id="adminNotifBadge" style="position:absolute;top:-6px;right:-6px;background:#dc3545;color:#fff;padding:2px 6px;border-radius:12px;font-size:12px;display:none">0</span>
                        <div id="adminNotifDropdown" style="display:none;position:absolute;right:0;top:28px;min-width:300px;background:#fff;color:#333;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,0.15);overflow:hidden;z-index:99999">
                            <div id="adminNotifItems" style="max-height:320px;overflow:auto;padding:8px"></div>
                            <div style="text-align:center;border-top:1px solid #eee;padding:6px"><a href="all-orders.php"><?php echo __('VIEW_ALL'); ?></a></div>
                        </div>
                    </div>
                    <!-- language selector removed (Malay-only deployment) -->
                    <li class="nav-item" style="display:flex;align-items:center;margin-right:8px;">
                        <div style="color:#fff;font-weight:600;">Bahasa Melayu</div>
                    </li>

                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="admin-profile.php"><?php echo __('PROFILE'); ?></a></li>
                        <li><a class="dropdown-item" href="change-password.php"><?php echo __('CHANGE_PASSWORD'); ?></a></li>
                        <li><a class="dropdown-item" href="settings.php"><?php echo __('SITE_SETTINGS'); ?></a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="logout.php"><?php echo __('LOGOUT'); ?></a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <script>
            // Lightweight polling for notifications (admin only)
            (function(){
                var lastId = 0;
                var polling = true;
                function showToast(msg, bg) {
                    var el = document.createElement('div');
                    el.style.position = 'fixed';
                    el.style.right = '16px';
                    el.style.top = '72px';
                    el.style.background = bg || '#0d6efd';
                    el.style.color = '#fff';
                    el.style.padding = '10px 14px';
                    el.style.borderRadius = '6px';
                    el.style.zIndex = 99999;
                    el.textContent = msg;
                    document.body.appendChild(el);
                    setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); }, 6000);
                }

                function poll() {
                    if (!polling) return;
                    fetch('/fvsms/scripts/notifications_poll.php?last_id=' + encodeURIComponent(lastId), { credentials: 'same-origin' })
                        .then(function(res){ return res.json(); })
                        .then(function(data){
                            var I18N_ADMIN = {
                                PRODUCT_RESTOCKED: "<?php echo addslashes(__('PRODUCT_RESTOCKED')); ?>",
                                PRODUCT_CHECKOUT_ADMIN: "<?php echo addslashes(__('PRODUCT_CHECKOUT_ADMIN')); ?>"
                            };
                function i18nFormatAdmin(tpl) { for (var i=1;i<arguments.length;i++) tpl = tpl.replace('%s', arguments[i]); return tpl; }
                // Format quantities for display (same helper as public header)
                function formatQtyAdmin(q) {
                    var num = parseFloat(q);
                    if (!isFinite(num)) return q;
                    if (Math.abs(Math.round(num) - num) < 1e-9) return String(Math.round(num));
                    var s = num.toFixed(4);
                    s = s.replace(/\.0+$/, '');
                    s = s.replace(/(\.\d*?)0+$/, '$1');
                    s = s.replace(/\.$/, '');
                    return s;
                }
                if (data && data.notifications && data.notifications.length) {
                                data.notifications.forEach(function(n){
                                    lastId = Math.max(lastId, parseInt(n.id || 0));
                                    var msg = '';
                                    if (n.action === 'restock') {
                                        msg = i18nFormatAdmin(I18N_ADMIN.PRODUCT_RESTOCKED, n.productId, formatQtyAdmin(n.qty), n.unit);
                                        showToast(msg, '#0d6efd');
                                    } else if (n.action && n.action.indexOf('checkout') === 0) {
                                        var meta = {};
                                        try { meta = n.meta ? JSON.parse(n.meta) : {}; } catch(e) { meta = {}; }
                                        var requester = meta.requestedBy ? meta.requestedBy : '';
                                        // Attempt to resolve product name, fallback to id
                                        fetch('/fvsms/scripts/get_product_names.php?ids=' + encodeURIComponent(n.productId), { credentials: 'same-origin' }).then(r=>r.json()).then(d=>{
                                            var pname = (d && d.ok && d.names && d.names[n.productId]) ? d.names[n.productId] : n.productId;
                                            msg = i18nFormatAdmin(I18N_ADMIN.PRODUCT_CHECKOUT_ADMIN, requester || 'user', formatQtyAdmin(n.qty), n.unit, pname);
                                            showToast(msg, '#28a745');
                                        }).catch(function(){
                                            msg = i18nFormatAdmin(I18N_ADMIN.PRODUCT_CHECKOUT_ADMIN, requester || 'user', formatQtyAdmin(n.qty), n.unit, n.productId);
                                            showToast(msg, '#28a745');
                                        });
                                    } else {
                                        msg = 'Product '+n.productId+' qty '+n.qty+' '+n.unit;
                                        showToast(msg, '#0d6efd');
                                    }
                                });
                            }
                        })
                        .catch(function(err){ console.error('poll err', err); })
                        .finally(function(){ setTimeout(poll, 5000); });
                }

                // start polling after DOM ready
                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    setTimeout(poll, 1000);
                } else {
                    document.addEventListener('DOMContentLoaded', function(){ setTimeout(poll, 1000); });
                }
            })();
        </script>