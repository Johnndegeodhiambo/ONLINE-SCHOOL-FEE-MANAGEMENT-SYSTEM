<?php
// includes/functions.php

/**
 * Redirect user to a specific page
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Sanitize input
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency for display
 */
function format_currency($amount)
{
    return 'Ksh ' . number_format($amount, 2);
}

/**
 * Show flash message
 */
function show_message($message, $type = 'info')
{
    $color = match ($type) {
        'success' => '#4CAF50',
        'error' => '#E74C3C',
        'warning' => '#FFC107',
        default => '#3498DB',
    };

    echo "<div style='background: {$color}; color: white; padding: 10px; border-radius: 6px; margin: 10px 0;'>{$message}</div>";
}
