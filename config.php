<?php
// Global configuration
// Adjust this timezone to your local region. Example: 'Asia/Kolkata', 'Europe/Berlin', etc.
// This ensures all time() and date() calls use a consistent expected timezone for voting schedule logic.
date_default_timezone_set('Asia/Kolkata');

// Helper to format server time consistently
function server_now_iso()
{
    return date('Y-m-d H:i:s');
}
