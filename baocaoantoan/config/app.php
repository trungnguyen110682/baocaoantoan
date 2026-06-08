<?php
define('SITE_URL', 'https://antoan-mmb.io.vn');
define('UPLOAD_DIR', __DIR__ . '/../images/');
define('UPLOAD_URL', '/images/');
define('SESSION_VIEWER', 'at_viewer');
define('SESSION_ADMIN',  'at_admin');
define('TOKEN_SECRET', 'AT_MMB_2024_SECRET_KEY');

// Webhook event keys (match rows in webhook table)
define('WH_BAOCAO_MOI',  'baocao_moi');
define('WH_PHE_DUYET',   'phe_duyet');
define('WH_PHE_DUYET_2', 'phe_duyet_2');
define('WH_DA_KP',       'da_khac_phuc');
