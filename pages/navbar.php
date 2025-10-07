<nav class="navbar navbar-expand-lg sticky-top" style="background-color: var(--primary-color);" id="top-navbar">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            <button id="sidebar-toggle" class="me-2 btn btn-link text-white">
                <i class="bi bi-list"></i>
            </button>
            <?php 
                // Fetch class name from database
                require "../db/connect.php";
                // session_start();
                if(isset($_SESSION["class_id"])) {
                    $stmt = $pdo->prepare("SELECT course_name FROM class WHERE id = :class_id");
                    $stmt->execute([':class_id' => $_SESSION["class_id"]]);
                    $class = $stmt->fetch(PDO::FETCH_ASSOC);
                    $className = $class["course_name"];
                    echo "<h5 class='mb-0 text-white'>$className</h5>";
                }
                
            ?>
        </div>
        
        <!-- <div class="search-container flex-grow-1">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search...">
                <button class="btn btn-success" type="button">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div> -->
    </div>
</nav>
