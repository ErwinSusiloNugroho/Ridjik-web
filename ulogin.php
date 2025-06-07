<?php  
session_start();       

// Konfigurasi database 
$host = 'localhost'; 
$dbname = 'ridjik'; 
$db_username = 'root'; 
$db_password = '';  

try {     
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);     
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e) {     
    die("Koneksi database gagal: " . $e->getMessage()); 
}  

if ($_SERVER["REQUEST_METHOD"] == "POST") {     
    $username = trim($_POST['username']); // Perbaiki dari 'user' ke 'username'
    $password = $_POST['password'];          
    
    if (!empty($username) && !empty($password)) {         
        // Query untuk mencari user berdasarkan username (bukan admin)
        $query = "SELECT * FROM user WHERE username = :username";         
        $stmt = $pdo->prepare($query);         
        $stmt->bindParam(':username', $username);         
        $stmt->execute();                  
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Ubah dari $admin ke $user                 
        
        if ($user && $password === $user['password']) {             
            // Login berhasil             
            $_SESSION['user_id'] = $user['id_user']; // Sesuaikan dengan kolom tabel user
            $_SESSION['username'] = $user['username']; // Ubah dari admin_username             
            $_SESSION['user_type'] = 'user'; // Ubah dari 'admin' ke 'user'                         
            
            header("Location:udashboard.php");     
            exit;         
        } else {             
            $error = "Username atau password salah.";         
        }     
    } else {         
        $error = "Mohon isi semua field.";     
    } 
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="login-container">
    <div class="login-left">
      <div class="login-header">
        <h2>RIDJIK INVENTORY MANAGEMENT STOCK</h2>
        <h1>Login</h1>
      </div>

      <?php if (isset($error)): ?>
        <div class="error-message"><?= $error ?></div>
      <?php endif; ?>

      <form method="post" action="" class="login-form">
        <div class="input-group">
          <input type="text" class="input-field" name="username" placeholder="Username" required>
        </div>
        <div class="input-group password-group">
          <input type="password" class="input-field" name="password" placeholder="Password" required>
          <span class="toggle-password" data-visible="false">&#128065;</span>
        </div>
        <div class="options">
          <label><input type="checkbox" name="remember"> <strong>Remember me</strong></label>
          <a href="#">Forget Password?</a>
        </div>
        <button type="submit" class="login-btn">Login</button>
        <div class="options" style="margin-top: 15px; justify-content: center;">
          <a href="udaftar.php">Create an account</a>
        </div>
      </form>
    </div>
    <div class="login-right">
      <img src="img/4.jpg" alt="Right side image">
    </div>
  </div>

  <script>
  const icon = document.querySelector('.toggle-password');
  const passwordField = document.querySelector('input[name="password"]');

  
  icon.innerHTML = '🙈';
  icon.setAttribute('data-visible', 'false');

  icon.addEventListener('click', function () {
    const isVisible = icon.getAttribute('data-visible') === 'true';

    if (isVisible) {
      passwordField.setAttribute('type', 'password');
      icon.innerHTML = '🙈'; // Mata tertutup
      icon.setAttribute('data-visible', 'false');
    } else {
      passwordField.setAttribute('type', 'text');
      icon.innerHTML = '🙉'; // Mata terbuka🙈
      icon.setAttribute('data-visible', 'true');
    }
  });
</script>


</body>
</html>
