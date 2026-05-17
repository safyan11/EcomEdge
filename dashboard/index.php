<?php
// dashboard/index.php - Smart redirect: admin → admin dash, user → user dash
require_once '../config.php';
requireLogin();

if (isAdmin()) {
    include 'admin_dashboard.php';
} else {
    include 'user_dashboard.php';
}
