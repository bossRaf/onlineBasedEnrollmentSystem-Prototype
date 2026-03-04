<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fa-solid fa-school"></i> DNHS-OBES
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" 
                aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
    				<a class="nav-link" href="/elementary.php">
    					<i class="fa-solid fa-child me-2"></i> Elementary Students
    				</a>
				</li>

				<li class="nav-item">
    				<a class="nav-link" href="/juniorhigh.php">
    					<i class="fa-solid fa-user-graduate me-2"></i> Junior High Students
    				</a>
				</li>

				<li class="nav-item">
    				<a class="nav-link" href="/seniorhigh.php">
    					<i class="fa-solid fa-graduation-cap me-2"></i> Senior High Students
    				</a>
				</li>

                <?php if (!isset($_SESSION['admin_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

