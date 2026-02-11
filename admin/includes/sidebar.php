            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading"><?php echo __('CORE'); ?></div>
                            <a class="nav-link" href="dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                <?php echo __('DASHBOARD'); ?>
                            </a>
                            <div class="sb-sidenav-menu-heading"><?php echo __('PRODUCT_MANAGEMENT'); ?></div>

                            <!--Categories --->
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                <?php echo __('CATEGORIES'); ?>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="add-category.php"><?php echo __('ADD'); ?></a>
                                    <a class="nav-link" href="manage-categories.php"><?php echo __('MANAGE'); ?></a>
                                </nav>
                            </div>

<!--- Sub-Categories --->
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#subcat" aria-expanded="false" aria-controls="subcat">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                <?php echo __('SUB_CATEGORIES'); ?>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="subcat" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="add-subcategories.php"><?php echo __('ADD'); ?></a>
                                    <a class="nav-link" href="manage-subcategories.php"><?php echo __('MANAGE'); ?></a>
                                </nav>
                            </div>

<!--- Products --->
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#product" aria-expanded="false" aria-controls="product">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                <?php echo __('PRODUCTS'); ?>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="product" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="add-product.php"><?php echo __('ADD'); ?></a>
                                    <a class="nav-link" href="manage-products.php"><?php echo __('MANAGE'); ?></a>
                                </nav>
                            </div>



                            <div class="sb-sidenav-menu-heading"><?php echo __('INVENTORY_MANAGEMENT'); ?></div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#inventory" aria-expanded="false" aria-controls="inventory">
                                <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                                <?php echo __('INVENTORY'); ?>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="inventory" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="manage-restock.php"><?php echo __('MANAGE_RESTOCK'); ?></a>
                                    <a class="nav-link" href="usage-tracking.php"><?php echo __('USAGE_TRACKING'); ?></a>
                                    <a class="nav-link" href="manage-history.php"><?php echo __('MANAGE_HISTORY'); ?></a>
                                </nav>
                            </div>

                            <div class="sb-sidenav-menu-heading"><?php echo __('ORDER_MANAGEMENT'); ?></div>
                   <!--- Products --->
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#orders" aria-expanded="false" aria-controls="orders">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                              Orders
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="orders" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
<?php $ret=mysqli_query($con,"select count(id) as totalorders,
count(if((orderStatus='' || orderStatus is null),0,null)) as neworders,
count(if(orderStatus='Packed', 0,null)) as packedorders,
count(if(orderStatus='Cancelled', 0,null)) as cancelledorders
from orders;");
$results=mysqli_fetch_array($ret);
$torders=$results['totalorders'];
$norders=$results['neworders'];
$porders=$results['packedorders'];
$cancelledorders=$results['cancelledorders'];
?>           <a class="nav-link" href="all-orders.php"><?php echo __('ALL_ORDERS'); ?> <span style="color:red"> [<?php echo $torders;?>]</span></a>
            <a class="nav-link" href="new-order.php"><?php echo __('NEW_ORDERS_LABEL'); ?> <span style="color:red"> [<?php echo $norders;?>]</span></a>
            <a class="nav-link" href="packed-orders.php"><?php echo __('PACKED_ORDERS_LABEL'); ?> <span style="color:red"> [<?php echo $porders;?>]</span></a>
            <a class="nav-link" href="cancelled-orders.php"><?php echo __('CANCELLED_ORDERS_LABEL'); ?> <span style="color:red"> [<?php echo $cancelledorders;?>]</span></a>
                                </nav>
                            </div>



  <div class="sb-sidenav-menu-heading"><?php echo __('REPORTS'); ?></div>

                            <!--Categories --->
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#reports" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                <?php echo __('REPORTS'); ?>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="reports" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="bwdates-ordersreport.php"><?php echo __('BWDATES_ORDERS_REPORT'); ?></a>
                                    <a class="nav-link" href="production-report.php"><?php echo __('PRODUCTION_STOCK_REPORT'); ?></a>
                                </nav>
                            </div>


       <a class="nav-link" href="registered-users.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                <?php echo __('REGISTERED_USERS'); ?>
                            </a>
                            <a class="nav-link" href="add-user.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                                <?php echo __('ADD_USER_ADMIN'); ?>
                            </a>
                            <a class="nav-link" href="settings.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                                <?php echo __('SITE_SETTINGS'); ?>
                            </a>






                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small"><?php echo __('LOGGED_IN_AS'); ?></div>
                        <?php echo $_SESSION['alogin'];?>
                    </div>
                </nav>
            </div>