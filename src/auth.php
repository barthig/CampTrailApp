<?php
if (empty($is_logged_in)) {
    header('Location: /login');
    exit;
}
