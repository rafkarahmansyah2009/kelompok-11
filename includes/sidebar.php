 <!-- Sidebar -->
 <div class="sidebar">
     <div class="sidebar-header">
         <img src="../assets/images/logo_smk5.png" alt="Logo SMKN 5" class="sidebar-logo">
         <h2 class="sidebar-title">SMKN 5</h2>
     </div>

     <ul class="sidebar-menu">
         <li>
             <a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>>
                 <i class="fas fa-tachometer-alt"></i>
                 <span>Dashboard</span>
             </a>
         </li>
         <li>
             <a href="siswa/list.php" <?php echo (strpos(basename($_SERVER['PHP_SELF']), 'siswa') !== false) ? 'class="active"' : ''; ?>>
                 <i class="fas fa-graduation-cap"></i>
                 <span>Data Siswa</span>
             </a>
         </li>
         <li>
             <a href="guru/list.php" <?php echo (strpos(basename($_SERVER['PHP_SELF']), 'guru') !== false) ? 'class="active"' : ''; ?>>
                 <i class="fas fa-chalkboard-teacher"></i>
                 <span>Data Guru</span>
             </a>
         </li>
         <li>
             <a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>>
                 <i class="fas fa-user"></i>
                 <span>Profil</span>
             </a>
         </li>
         <li>
             <a href="logout.php">
                 <i class="fas fa-sign-out-alt"></i>
                 <span>Logout</span>
             </a>
         </li>
     </ul>
 </div>