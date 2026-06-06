<?php
require_once __DIR__ . '/app/helpers.php';
session_destroy();
redirect('index.php');

