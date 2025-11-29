<?php

function get_base_url()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    $dir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $dir = rtrim(dirname($dir), '/\\');

    return $protocol . '://' . $host . $dir;
}

function url($path = '')
{
    $base = 'https://biblioteca.cedhinuevaarequipa.edu.pe';
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

function redirect_to($route)
{
    $full = url($route);
    echo "Redirecting to: $full";
    header("Location: " . $full);
    exit;
}