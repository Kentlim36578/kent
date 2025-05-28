<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Album - The BearFruits Studios</title>
    <link rel="stylesheet" href="GStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #234b3a;
            --accent: #f8b400;
            --bg: #f7fafc;
        }
        html {
            box-sizing: border-box;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            background: var(--bg);
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: var(--primary);
            color: #fff;
            padding: 22px 0;
            box-shadow: 0 2px 8px rgba(35,75,58,0.09);
        }
        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 22px;
            flex-wrap: wrap;
            position: relative;
        }
        .logo {
            font-weight: 800;
            font-size: 1.7rem;
            letter-spacing: 1.2px;
            display: flex;
            align-items: center;
        }
        .logo i {
            margin-right: 10px;
            color: var(--accent);
        }
        .navbar-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            transition: max-height 0.3s ease;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            margin-left: 28px;
            font-size: 1.07rem;
            padding: 9px 15px;
            border-radius: 7px;
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
        }
        .navbar a:hover, .navbar .book-btn {
            background:rgba(109, 109, 109, 0);
            color:rgb(255, 255, 255);
        }
        .navbar-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            cursor: pointer;
            background: none;
            border: none;
            position: absolute;
            right: 22px;
            top: 18px;
            z-index: 10;
        }
        .navbar-toggle span {
            display: block;
            width: 26px;
            height: 3px;
            background: #fff;
            margin: 5px 0;
            transition: 0.4s;
            border-radius: 2px;
        }

        .main-content {
            max-width: 1100px;
            margin: 32px auto 0 auto;
            padding: 0 16px 40px 16px;
            flex: 1 0 auto;
        }
        .album-title {
            text-align: center;
            font-size: 2.1rem;
            color: var(--primary);
            margin-bottom: 12px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 28px;
            font-size: 1.08rem;
        }
        .album-section {
            margin-bottom: 38px;
        }
        .album-section h2 {
            color: var(--accent);
            font-size: 1.3rem;
            margin-bottom: 16px;
            margin-top: 18px;
            text-align: left;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .album-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 28px;
            justify-content: flex-start;
        }
        .album-image-container {
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 2px 14px rgba(35,75,58,0.08);
            overflow: hidden;
            transition: transform 0.18s, box-shadow 0.18s;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 0 14px 0;
            flex: 1 1 220px;
            max-width: 300px;
            min-width: 180px;
            margin: 0;
        }
        .album-image-container:hover {
            transform: translateY(-5px) scale(1.035);
            box-shadow: 0 8px 24px rgba(35, 75, 58, 0.18);
        }
        .album-image-container img {
            width: 95%;
            max-width: 300px;
            max-height: 250px;
            object-fit: cover;
            border-radius: 7px;
            margin-bottom: 10px;
            box-shadow: 0 1px 5px #ddd;
            background: #f6f6f6;
        }
        .no-images {
            color: #aaa;
            font-size: 1.1em;
            text-align: center;
            margin-top: 38px;
            width: 100%;
        }
        footer {
            background: var(--primary);
            color: #fff;
            text-align: center;
            padding: 30px 0 18px 0;
            margin-top: 38px;
            flex-shrink: 0;
        }
        /* Responsive Styles */
        @media (max-width: 900px) {
            .main-content {
                max-width: 95vw;
                padding: 0 6vw 40px 6vw;
            }
            .album-gallery {
                gap: 14px;
            }
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 0 5vw;
            }
            .navbar-links {
                width: 100%;
                justify-content: flex-end;
                margin-top: 10px;
                gap: 0;
            }
            .logo {
                font-size: 1.3rem;
            }
        }
        @media (max-width: 700px) {
            .album-title { font-size: 1.4rem; }
            .album-section h2 { font-size: 1.05rem; }
            .album-gallery { gap: 10px; }
            .album-image-container {
                min-width: 120px;
                max-width: 49vw;
                flex: 1 1 120px;
                padding: 7px 0 10px 0;
            }
            .album-image-container img { max-height: 120px; }
            .subtitle { font-size: 1rem; }
            .main-content {
                padding: 0 2vw 24px 2vw;
            }
            .navbar-toggle {
                display: flex;
            }
            .navbar-links {
                flex-direction: column;
                width: 100%;
                max-height: 0;
                overflow: hidden;
                background: var(--primary);
                margin: 0;
                transition: max-height 0.3s ease;
            }
            .navbar-links.open {
                max-height: 500px;
                margin-top: 8px;
            }
            .navbar a {
                margin: 0;
                padding: 13px 0;
                border-bottom: 1px solid rgba(255,255,255,0.07);
                width: 100%;
                text-align: left;
            }
        }
        @media (max-width: 500px) {
            .navbar, .navbar-links { flex-direction: column; align-items: stretch; }
            .navbar a { margin: 0; margin-bottom: 4px; }
            .album-title { font-size: 1.1rem; }
            .album-section h2 { font-size: 0.97rem; }
            .album-image-container {
                min-width: 88px;
                max-width: 100vw;
                flex: 1 1 88px;
            }
            .album-image-container img { max-height: 80px; }
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar" id="navbar">
        <div class="logo"><i class="fa-solid fa-leaf"></i> The BearFruits Studios</div>
        <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="navbar-links" id="navbar-links">
            <a href="homepage.html"><i class="fas fa-home"></i> Home</a>
            <a href="about.html"><i class="fas fa-user"></i> About</a>
            <a href="book.html" class="book-btn"><i class="fa fa-calendar-plus"></i> Book Now</a>
        </div>
    </nav>
</header>

<main class="main-content">
    <div class="album-title">Photo Album</div>
    <div class="subtitle">See your uploaded memories below. Click on any photo to view full size.</div>
    
    <div class="album-section">
        <h2><i class="fa fa-heart"></i> Wedding</h2>
        <div class="album-gallery">
        <?php
        $wedDir = "uploads/wedding/";
        $found = false;
        if (is_dir($wedDir)) {
            $images = array_diff(scandir($wedDir), array('.', '..'));
            foreach ($images as $img) {
                $imgUrl = $wedDir . $img;
                $ext = strtolower(pathinfo($imgUrl, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    echo "<div class='album-image-container'>
                            <a href='$imgUrl' target='_blank'><img src='$imgUrl' alt='Wedding Image'></a>
                          </div>";
                    $found = true;
                }
            }
        }
        if (!$found) {
            echo "<div class='no-images'>No wedding images yet.</div>";
        }
        ?>
        </div>
    </div>
    
    <div class="album-section">
        <h2><i class="fa fa-crown"></i> Debut</h2>
        <div class="album-gallery">
        <?php
        $debDir = "uploads/debut/";
        $found = false;
        if (is_dir($debDir)) {
            $images = array_diff(scandir($debDir), array('.', '..'));
            foreach ($images as $img) {
                $imgUrl = $debDir . $img;
                $ext = strtolower(pathinfo($imgUrl, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    echo "<div class='album-image-container'>
                            <a href='$imgUrl' target='_blank'><img src='$imgUrl' alt='Debut Image'></a>
                          </div>";
                    $found = true;
                }
            }
        }
        if (!$found) {
            echo "<div class='no-images'>No debut images yet.</div>";
        }
        ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; 2025 The BearFruits Studios. All rights reserved.</p>
</footer>
<script src="GApps.js"></script>
<script>
    // Responsive Navbar Toggle Script
    const toggle = document.getElementById('navbar-toggle');
    const links = document.getElementById('navbar-links');
    toggle.addEventListener('click', function() {
        links.classList.toggle('open');
        const expanded = toggle.getAttribute('aria-expanded') === 'true' || false;
        toggle.setAttribute('aria-expanded', !expanded);
    });
</script>
</body>
</html>