<?php
session_start();
include_once(__DIR__ . '/../includes/config.php');
include_once(__DIR__ . '/../includes/lang.php');
if(strlen( $_SESSION['aid'])==0) { header('location:logout.php'); }

// load current settings via $site_settings from includes/lang.php
// Force Malay-only deployment: treat current language as 'ms'
$curr_lang = 'ms';
$curr_logo = isset($site_settings['site_logo']) ? $site_settings['site_logo'] : 'logo.png';
$curr_choice = isset($site_settings['logo_choice']) ? $site_settings['logo_choice'] : (isset($site_settings['site_logo_choice']) ? $site_settings['site_logo_choice'] : 'logo1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = '';
    $success = '';

    // Only 'ms' is supported in this deployment
    $lang = 'ms';
    $logo_choice = isset($_POST['logo_choice']) ? $_POST['logo_choice'] : 'logo1';

    // handle optional file upload
    if (!empty($_FILES['upload_logo']) && $_FILES['upload_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['upload_logo'];
        $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $error = __('SOMETHING_WENT_WRONG');
        } elseif ($f['size'] > 1024*1024) {
            $error = 'File too large (max 1MB)';
        } elseif (!array_key_exists(mime_content_type($f['tmp_name']), $allowed)) {
            $error = 'Invalid file type';
        } else {
            // decide target name based on selected logo_choice
            $ext = $allowed[mime_content_type($f['tmp_name'])];
            $targetName = ($logo_choice === 'logo2') ? 'logo2.' . $ext : 'logo1.' . $ext;
            $targetPath = __DIR__ . '/../assets/img/' . $targetName;
            if (move_uploaded_file($f['tmp_name'], $targetPath)) {
                @chmod($targetPath, 0644);
                $success = __('SAVE_SETTINGS') . ' - ' . __('SITE_LOGO') . ' uploaded.';
                // set site_logo to the target (primary)
                $site_logo = $targetName;
                // ensure settings reflect uploaded file
                mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('site_logo','".mysqli_real_escape_string($con,$site_logo)."') ON DUPLICATE KEY UPDATE value=VALUES(value)");
                mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('logo_choice','".mysqli_real_escape_string($con,$logo_choice)."') ON DUPLICATE KEY UPDATE value=VALUES(value)");
            } else {
                $error = __('SOMETHING_WENT_WRONG');
            }
        }
    } else {
        // no upload — just persist choices
        $site_logo = 'logo1.png';
        if ($logo_choice === 'logo2') $site_logo = 'logo2.png';
        if ($logo_choice === 'both') $site_logo = 'logo1.png'; // primary when both
        mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('site_logo','".mysqli_real_escape_string($con,$site_logo)."') ON DUPLICATE KEY UPDATE value=VALUES(value)");
        mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('logo_choice','".mysqli_real_escape_string($con,$logo_choice)."') ON DUPLICATE KEY UPDATE value=VALUES(value)");
    }

    // always persist language and mark saved
    mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('language','".mysqli_real_escape_string($con,$lang)."') ON DUPLICATE KEY UPDATE value=VALUES(value)");
    mysqli_query($con, "INSERT INTO settings (name,value) VALUES ('setup_completed','1') ON DUPLICATE KEY UPDATE value=VALUES(value)");

    // update session and reload
    $_SESSION['lang'] = $lang;
    // reload settings into $site_settings (so the same request reflects changes)
    $r = mysqli_query($con, "SELECT name,value FROM settings"); if ($r) { while ($rw = mysqli_fetch_assoc($r)) $site_settings[$rw['name']] = $rw['value']; }

    if ($error === '') $success = $success ?: __('SAVE_SETTINGS') . ' - OK';
}

?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?php echo __('SITE_SETTINGS'); ?></title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/header.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4"><?php echo __('SITE_SETTINGS'); ?></h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?php echo __('DASHBOARD'); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo __('SITE_SETTINGS'); ?></li>
                    </ol>
                    <div class="card mb-4 admin-card">
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <?php if(isset($success)): ?>
                                    <div class="alert alert-success"><?php echo htmlentities($success); ?></div>
                                <?php endif; ?>
                                <?php if(isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label"><?php echo __('LANGUAGE'); ?></label>
                                        <div class="form-control" style="background:transparent;border:0;padding:6px;">Bahasa Melayu</div>
                                        <input type="hidden" name="language" value="ms">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><?php echo __('SITE_LOGO'); ?></label>
                                        <select name="logo_choice" class="form-control">
                                            <option value="logo1" <?php echo $curr_choice==='logo1'?'selected':''; ?>><?php echo __('LOGO_1'); ?></option>
                                            <option value="logo2" <?php echo $curr_choice==='logo2'?'selected':''; ?>><?php echo __('LOGO_2'); ?></option>
                                            <option value="both" <?php echo $curr_choice==='both'?'selected':''; ?>><?php echo __('LOGO_BOTH'); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" style="display:flex;align-items:flex-end;">
                                        <button class="btn btn-primary btn-smooth" type="submit"><?php echo __('SAVE_SETTINGS'); ?></button>
                                    </div>
                                </div>

                                <hr />
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><?php echo __('UPLOAD_LOGO'); ?></label>
                                        <input type="file" name="upload_logo" accept="image/png,image/jpeg" class="form-control">
                                        <small class="text-muted">PNG/JPEG, max 1MB. Upload will replace the selected logo file.</small>
                                    </div>
                                    <div class="col-md-6" style="display:flex;align-items:flex-end;">
                                        <button class="btn btn-secondary btn-smooth" type="submit" name="upload_action"><?php echo __('UPLOAD_AND_SAVE'); ?></button>
                                    </div>
                                </div>
                            </form>

                            <hr />
                            <p><?php echo __('LOGO_SELECTION_NOTE'); ?></p>
                            <div style="margin-top:12px">
                                <img src="../assets/img/logo1.png" style="height:64px;margin-right:12px;" alt="logo1"> <span><?php echo __('LOGO_1'); ?></span>
                                <img src="../assets/img/logo2.png" style="height:64px;margin-left:20px;margin-right:12px;" alt="logo2"> <span><?php echo __('LOGO_2'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include_once('includes/footer.php'); ?>
        </div>
    </div>
</body>
</html>