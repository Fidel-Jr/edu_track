<?php
    $current_page = basename($_SERVER['PHP_SELF']);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
?>




<nav id="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-school"></i> Skol Monitoring System</h3>
    </div>
    
    <ul class="list-unstyled sidebar-nav">
        <?php 
            if(!empty($_SESSION["class_id"])):
        ?>
        <li class="nav-section">Main</li>
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span class="link-text">Dashboard</span>
            </a>
        </li>
        
        <li class="nav-section">Management</li>
        <li class="nav-item">
            <a href="attendance.php" class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
                <i class="fa-regular fa-circle-check"></i>
                <span class="link-text">Attendance</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="grades.php" class="nav-link <?php echo ($current_page == 'grades.php') ? 'active' : ''; ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span class="link-text">Grades</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="./students.php" class="nav-link <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span class="link-text">Students</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../backend/switch_class.php" class="nav-link <?php echo ($current_page == '../welcome.php') ? 'active' : ''; ?>">
                <i class="bi bi-table"></i>
                <span class="link-text">Switch Class</span>
            </a>
        </li>
        <?php endif; ?>
        
        <li class="nav-section">Account</li>
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                <span class="link-text">Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../backend/logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i>
                <span class="link-text">Logout</span>
            </a>
        </li>
    </ul>
</nav>
