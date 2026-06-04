<?php
require_once __DIR__ . '/auth.php';
admin_logout();
header('Location: ' . admin_url('login'));
exit;
