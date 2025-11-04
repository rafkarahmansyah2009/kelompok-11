<?php
// Fungsi untuk menampilkan alert
function alert($message, $type = 'info', $dismissible = true)
{
    $class = "alert alert-$type";
    if ($dismissible) {
        $class .= " alert-dismissible fade show";
    }

    $alert = "<div class='$class' role='alert'>";
    $alert .= $message;

    if ($dismissible) {
        $alert .= "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    }

    $alert .= "</div>";

    return $alert;
}

// Fungsi untuk menampilkan notifikasi dari session
function show_alerts()
{
    $output = '';

    if (isset($_SESSION['success'])) {
        $output .= alert($_SESSION['success'], 'success');
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        $output .= alert($_SESSION['error'], 'danger');
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['warning'])) {
        $output .= alert($_SESSION['warning'], 'warning');
        unset($_SESSION['warning']);
    }

    if (isset($_SESSION['info'])) {
        $output .= alert($_SESSION['info'], 'info');
        unset($_SESSION['info']);
    }

    return $output;
}
