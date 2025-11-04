<?php
// Fungsi untuk memeriksa apakah user sudah login
function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk menampilkan notifikasi
function show_notification()
{
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
}

// Fungsi untuk membersihkan input
function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk format tanggal
function format_date($date)
{
    if (empty($date)) return '-';
    $date_obj = date_create($date);
    return date_format($date_obj, 'd/m/Y');
}

// Fungsi untuk mendapatkan statistik
function get_stats($conn)
{
    $stats = array();

    // Total siswa
    $result = $conn->query("SELECT COUNT(*) as total FROM siswa");
    $row = $result->fetch_assoc();
    $stats['total_siswa'] = $row['total'];

    // Total guru
    $result = $conn->query("SELECT COUNT(*) as total FROM guru");
    $row = $result->fetch_assoc();
    $stats['total_guru'] = $row['total'];

    // Total user
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $row = $result->fetch_assoc();
    $stats['total_user'] = $row['total'];

    return $stats;
}

// Fungsi untuk pagination
function pagination($query, $per_page = 10, $page = 1, $url = '?')
{
    global $conn;

    $result = $conn->query($query);
    $total = $result->num_rows;

    $adjacents = "2";

    $prevlabel = "&lsaquo; Prev";
    $nextlabel = "Next &rsaquo;";

    $lastpage = ceil($total / $per_page);

    $lpm1 = $lastpage - 1;

    $pagination = "";

    if ($lastpage > 1) {
        $pagination .= "<ul class='pagination'>";

        // Previous button
        if ($page > 1) {
            $pagination .= "<li><a href='{$url}page=" . ($page - 1) . "'>$prevlabel</a></li>";
        } else {
            $pagination .= "<li class='disabled'><span>$prevlabel</span></li>";
        }

        // Pages
        if ($lastpage < 7 + ($adjacents * 2)) {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page) {
                    $pagination .= "<li class='active'><span>$counter</span></li>";
                } else {
                    $pagination .= "<li><a href='{$url}page=$counter'>$counter</a></li>";
                }
            }
        } elseif ($lastpage > 5 + ($adjacents * 2)) {
            if ($page < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $page) {
                        $pagination .= "<li class='active'><span>$counter</span></li>";
                    } else {
                        $pagination .= "<li><a href='{$url}page=$counter'>$counter</a></li>";
                    }
                }
                $pagination .= "<li class='disabled'><span>...</span></li>";
                $pagination .= "<li><a href='{$url}page=$lpm1'>$lpm1</a></li>";
                $pagination .= "<li><a href='{$url}page=$lastpage'>$lastpage</a></li>";
            } elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                $pagination .= "<li><a href='{$url}page=1'>1</a></li>";
                $pagination .= "<li><a href='{$url}page=2'>2</a></li>";
                $pagination .= "<li class='disabled'><span>...</span></li>";

                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                    if ($counter == $page) {
                        $pagination .= "<li class='active'><span>$counter</span></li>";
                    } else {
                        $pagination .= "<li><a href='{$url}page=$counter'>$counter</a></li>";
                    }
                }

                $pagination .= "<li class='disabled'><span>...</span></li>";
                $pagination .= "<li><a href='{$url}page=$lpm1'>$lpm1</a></li>";
                $pagination .= "<li><a href='{$url}page=$lastpage'>$lastpage</a></li>";
            } else {
                $pagination .= "<li><a href='{$url}page=1'>1</a></li>";
                $pagination .= "<li><a href='{$url}page=2'>2</a></li>";
                $pagination .= "<li class='disabled'><span>...</span></li>";

                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $pagination .= "<li class='active'><span>$counter</span></li>";
                    } else {
                        $pagination .= "<li><a href='{$url}page=$counter'>$counter</a></li>";
                    }
                }
            }
        }

        // Next button
        if ($page < $counter - 1) {
            $pagination .= "<li><a href='{$url}page=" . ($page + 1) . "'>$nextlabel</a></li>";
        } else {
            $pagination .= "<li class='disabled'><span>$nextlabel</span></li>";
        }

        $pagination .= "</ul>\n";
    }

    return $pagination;
}
