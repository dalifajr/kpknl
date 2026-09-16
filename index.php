<?php
/**
 * KPKNL Palembang Portal Redirector
 * Otomatis mengarahkan request root ke portal SSO KPKNL Palembang
 */
$target = '/sso/public/';
header("Location: " . $target, true, 302);
exit;
