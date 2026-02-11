<?php include_once(__DIR__ . '/lang.php'); ?>
<!-- header -->
    <div class="top-header-area" id="sticker">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12 text-center">
                    <div class="main-menu-wrap">
                        <!-- logo (dynamic via site settings) -->
                        <div class="site-logo">
                            <a href="index.php">
                                <?php if (!empty($site_settings['logo_choice']) && $site_settings['logo_choice'] === 'both'): ?>
                                    <img src="assets/img/logo1.png" alt="" style="height:48px;margin-right:8px;">
                                    <img src="assets/img/logo2.png" alt="" style="height:48px;">
                                <?php else: ?>
                                    <?php $logo = !empty($site_settings['site_logo']) ? $site_settings['site_logo'] : 'logo.png'; ?>
                                    <img src="assets/img/<?php echo htmlentities($logo); ?>" alt="">
                                <?php endif; ?>
                            </a>
                        </div>
                        <!-- logo -->

                        <!-- menu start -->
                        <nav class="main-menu">
                            <ul>
                               
                                <li class="current-list-item"><a href="index.php"><?php echo __('HOME'); ?></a></li>
                                <li><a href="about.php"><?php echo __('ABOUT'); ?></a></li>
                             
                              
                                <li><a href="contact.php"><?php echo __('CONTACT'); ?></a></li>
                                <li><a href="shop.php"><?php echo __('INVENTORY'); ?></a>
                                    <ul class="sub-menu">
                                        <li><a href="shop.php"><?php echo __('ALL'); ?></a></li>
                                        <?php $query=mysqli_query($con,"select category.id as catid,category.categoryName,category.categoryDescription,category.creationDate,category.updationDate,tbladmin.username from category join tbladmin on tbladmin.id=category.createdBy");
$cnt=1;
while($row=mysqli_fetch_array($query))
{
?>  
                                        <li><a href="categorywise.php?cid=<?php echo $row['catid']?>"><?php echo htmlentities($row['categoryName']);?></a></li><?php $cnt=$cnt+1; } ?>
                                      
                                    </ul>
                                </li>
                                <?php if($_SESSION['id']==0){?>
                                  <li style="color: white;font-weight: bolder;"><?php echo __('USERS'); ?>
                                    <ul class="sub-menu">
                                        <li><a href="login.php"><?php echo __('LOGIN'); ?></a></li>
                                        <li><a href="registration.php">Registration</a></li>
                                    </ul>
                                </li>
                                <li class="current-list-item"><a href="admin/index.php"><?php echo __('ADMIN'); ?></a></li>
                                <?php } else {?>
                                  <li style="color: white;font-weight: bolder;"><?php echo __('MY_ACCOUNT'); ?>
                                    <ul class="sub-menu">
                                       
                                        <li><a href="profile.php"><?php echo __('PROFILE'); ?></a></li>
                                        <li><a href="setting.php"><?php echo __('CHANGE_PASSWORD'); ?></a></li>
                                        <li><a href="my-orders.php"><?php echo __('CHECKOUT_HISTORY'); ?></a></li>
                                        <li><a href="logout.php"><?php echo __('LOGOUT'); ?></a></li>
                                    </ul>
                                </li>
                                <li><a href="my-wishlist.php"><?php echo __('HISTORY'); ?></a></li>
                                <?php } ?> 
                                <li>
                                    <div class="header-icons">
                                        <!-- language selector removed (Malay-only) -->
                                        <span style="display:inline-block;margin-right:12px;font-weight:600;">Bahasa Melayu</span>
                                        <?php 
$uid=$_SESSION['id'];
                        $ret=mysqli_query($con,"select sum(productQty) as qtyy from cart where userId='$uid'");
$result=mysqli_fetch_array($ret);
$cartcount=$result['qtyy'];
                        ?>
                                        <a class="shopping-cart" href="my-cart.php"><i class="fas fa-shopping-cart"></i> <?php if($cartcount==0):?>
                            <span class="badge bg-dark text-white ms-1 rounded-pill">0</span>
                        <?php else: ?>
                            <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartcount; ?></span>
                            <?php endif;?></a>

                                        <span id="notifWrapper" style="position:relative;display:inline-block;">
                                            <a id="notifLink" class="mobile-hide search-bar-icon" href="my-wishlist.php" aria-label="Notifications"><i class="fas fa-bell"></i></a>
                                            <span id="notifBadge" style="position:absolute;top:-6px;right:-6px;background:#dc3545;color:#fff;padding:2px 6px;border-radius:12px;font-size:12px;display:none">0</span>
                                            <div id="notifDropdown" style="display:none;position:absolute;right:0;top:28px;min-width:260px;background:#fff;color:#333;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,0.15);overflow:hidden;z-index:99999">
                                                <div id="notifItems" style="max-height:320px;overflow:auto;padding:8px"></div>
                                                <div style="text-align:center;border-top:1px solid #eee;padding:6px"><a href="my-wishlist.php"><?php echo __('VIEW_ALL'); ?></a></div>
                                            </div>
                                        </span>
                                    </div>
                                </li>
                                  
                            </ul>
                        </nav>
                    
                        <div class="mobile-menu"></div>
                        <!-- menu end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end header -->
    <style>
    /* small notification pulse for public header */
    #notifLink.pulse .fa-bell { animation: pulse 1.6s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
    </style>
            <script>
        // Polling for notifications and product quantity refresh (public)
        (function(){
            var lastId = 0;
            var notifItemsEl = null;
            var badgeEl = document.getElementById('notifBadge');
            var notifLinkEl = document.getElementById('notifLink');

            var I18N = {
                PRODUCT_RESTOCKED: "<?php echo addslashes(__('PRODUCT_RESTOCKED')); ?>",
                PRODUCT_CHECKOUT_USER: "<?php echo addslashes(__('PRODUCT_CHECKOUT_USER')); ?>"
            };
            function i18nFormat(tpl) { for (var i=1;i<arguments.length;i++) tpl = tpl.replace('%s', arguments[i]); return tpl; }

            // Format quantities for display: trim trailing zeros and unnecessary decimals
            function formatQty(q) {
                var num = parseFloat(q);
                if (!isFinite(num)) return q;
                if (Math.abs(Math.round(num) - num) < 1e-9) return String(Math.round(num));
                var s = num.toFixed(4);
                s = s.replace(/\.0+$/, '');
                s = s.replace(/(\.\d*?)0+$/, '$1');
                s = s.replace(/\.$/, '');
                return s;
            }

            function makeEven(n) { n = parseInt(n)||0; return (n % 2 === 0) ? n : n + 1; }

            function addNotifToDropdown(n) {
                if (!notifItemsEl) notifItemsEl = document.getElementById('notifItems');
                if (!notifItemsEl) return;
                var el = document.createElement('div');
                el.style.padding = '8px';
                el.style.borderBottom = '1px solid #f1f1f1';
                // Choose template based on action
                if (n.action && n.action === 'restock') {
                    el.textContent = i18nFormat(I18N.PRODUCT_RESTOCKED, n.productId, formatQty(n.qty), n.unit);
                } else if (n.action && (n.action.indexOf('checkout') === 0)) {
                    // For user notifications (single or cart), show an acknowledgement message
                    el.textContent = i18nFormat(I18N.PRODUCT_CHECKOUT_USER, formatQty(n.qty), n.unit, n.productId);
                    // async replace productId with product name if available
                    fetch('/fvsms/scripts/get_product_names.php?ids=' + encodeURIComponent(n.productId), { credentials: 'same-origin' })
                        .then(r => r.json()).then(d => {
                            if (d && d.ok && d.names && d.names[n.productId]) {
                                el.textContent = i18nFormat(I18N.PRODUCT_CHECKOUT_USER, formatQty(n.qty), n.unit, d.names[n.productId]);
                            }
                        }).catch(()=>{});
                } else {
                    // fallback
                    el.textContent = i18nFormat(I18N.PRODUCT_RESTOCKED, n.productId, formatQty(n.qty), n.unit);
                }
                notifItemsEl.insertBefore(el, notifItemsEl.firstChild);
            }

            // persistent unread count (per-browser). Use localStorage to survive reloads.
            var storageKey = 'fvsms_unread_count';
            var unreadCount = parseInt(localStorage.getItem(storageKey) || '0') || 0;
            function setBadgeCount(c) {
                unreadCount = makeEven(parseInt(c) || 0);
                localStorage.setItem(storageKey, unreadCount);
                if (!badgeEl) return;
                if (unreadCount > 0) {
                    badgeEl.style.display = 'inline-block';
                    badgeEl.textContent = unreadCount;
                    if (notifLinkEl) notifLinkEl.classList.add('pulse');
                } else {
                    badgeEl.style.display = 'none';
                    badgeEl.textContent = '0';
                    if (notifLinkEl) notifLinkEl.classList.remove('pulse');
                }
            }

            // initialize badge
            setBadgeCount(unreadCount);

            function refreshProductQuantities(productIds) {
                if (!productIds || productIds.length === 0) return;
                var ids = productIds.join(',');
                fetch('/fvsms/scripts/get_product_qty.php?ids=' + encodeURIComponent(ids), { credentials: 'same-origin' })
                    .then(function(res){ return res.json(); })
                    .then(function(data){
                        if (data && data.quantities) {
                            Object.keys(data.quantities).forEach(function(id){
                                var qty = parseFloat(data.quantities[id]);
                                var els = document.querySelectorAll('[data-product-id="' + id + '"]');
                                els.forEach(function(el){
                                    // update textual display, assume element expects kg grams display
                                    var grams = qty * 1000;
                                    var display = '';
                                    if (grams >= 1000 && grams % 1000 === 0) {
                                        display = (grams / 1000) + ' kg';
                                    } else {
                                        display = Math.round(grams) + ' gram';
                                    }
                                    el.textContent = display;
                                });
                            });
                        }
                    }).catch(function(){/*ignore*/});
            }

            function poll() {
                fetch('/fvsms/scripts/notifications_poll.php?last_id=' + encodeURIComponent(lastId), { credentials: 'same-origin' })
                    .then(function(res){ return res.json(); })
                    .then(function(data){
                        if (!data) return;
                        // if server returned current_max only (first poll), seed lastId and don't display backlog
                        if (data.current_max && (!data.notifications || data.notifications.length === 0) && lastId === 0) {
                            lastId = parseInt(data.current_max || 0);
                            return;
                        }
                        if (data && data.notifications && data.notifications.length) {
                            var touched = {};
                            for (var i=0;i<data.notifications.length;i++) {
                                var n = data.notifications[i];
                                lastId = Math.max(lastId, parseInt(n.id || 0));
                                addNotifToDropdown(n);
                                touched[n.productId] = true;
                            }
                            // increment unread and ensure displayed number is even
                            setBadgeCount((parseInt(localStorage.getItem(storageKey) || '0') || 0) + data.notifications.length);
                            // refresh quantities for touched products
                            refreshProductQuantities(Object.keys(touched));
                        } else if (data.current_max) {
                            // keep in sync
                            lastId = Math.max(lastId, parseInt(data.current_max || 0));
                        }
                    })
                    .catch(function(err){ /* ignore */ })
                    .finally(function(){ setTimeout(poll, 5000); });
            }

            // Toggle dropdown on click
            var wrapper = document.getElementById('notifWrapper');
            if (wrapper) {
                wrapper.addEventListener('click', function(e){
                    e.preventDefault();
                    var dd = document.getElementById('notifDropdown');
                    if (!dd) return;
                    dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
                    // reset badge when opened and mark as seen (persist last seen = current_max)
                    setBadgeCount(0);
                    try { localStorage.setItem('fvsms_last_seen_max', lastId); } catch (ex) {}
                });
            }

            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(poll, 1000);
            } else {
                document.addEventListener('DOMContentLoaded', function(){ setTimeout(poll, 1000); });
            }
        })();
    </script>
   

