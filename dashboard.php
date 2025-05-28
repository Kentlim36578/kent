<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = ""; 
$database = "bearfruitsstudios";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$feedback = "";

// --- ALBUM IMAGE UPLOAD CODE START ---
if (isset($_POST['upload_album_image']) && isset($_FILES['album_image'])) {
    $category = isset($_POST['image_category']) && in_array($_POST['image_category'], ['wedding','debut'])
        ? $_POST['image_category']
        : 'wedding'; // Default fallback

    $targetDir = "uploads/$category/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = basename($_FILES["album_image"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["album_image"]["tmp_name"], $targetFile)) {
            $feedback = "<div class='alert success'>Image uploaded successfully to <b>$category</b> album!</div>";
        } else {
            $feedback = "<div class='alert danger'>Sorry, there was an error uploading your file.</div>";
        }
    } else {
        $feedback = "<div class='alert danger'>Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.</div>";
    }
}

// --- DELETE IMAGE LOGIC ---
if (isset($_POST['delete_image']) && isset($_POST['delete_category']) && isset($_POST['delete_file'])) {
    $category = $_POST['delete_category'];
    $file = $_POST['delete_file'];
    $filePath = "uploads/$category/$file";
    // Only delete if within correct folder and is a file
    if (file_exists($filePath) && strpos(realpath($filePath), realpath("uploads/$category")) === 0) {
        if (unlink($filePath)) {
            $feedback = "<div class='alert success'>Image deleted successfully.</div>";
        } else {
            $feedback = "<div class='alert danger'>Failed to delete the image.</div>";
        }
    } else {
        $feedback = "<div class='alert danger'>Invalid image selected for deletion.</div>";
    }
}

// --- SCAN UPLOADED IMAGES ---
function get_album_images($category) {
    $images = [];
    $dir = "uploads/$category/";
    if (is_dir($dir)) {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = $file;
            }
        }
    }
    return $images;
}
$wedding_images = get_album_images('wedding');
$debut_images = get_album_images('debut');

// --- BOOKINGS LOGIC ---
if (isset($_POST['update_booking'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("UPDATE bookings SET name = ?, email = ?, phone = ?, service = ?, message = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $service, $message, $id);

    if ($stmt->execute()) {
        $feedback = "<div class='alert success'>Booking updated successfully!</div>";
    } else {
        $feedback = "<div class='alert danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $feedback = "<div class='alert success'>Booking deleted successfully!</div>";
    } else {
        $feedback = "<div class='alert danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

if (isset($_GET['accept'])) {
    $id = $_GET['accept'];
    $stmt = $conn->prepare("SELECT name, email, phone, booking_date FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name, $email, $phone, $booking_date);
    if ($stmt->fetch()) {
        $stmt->close();
        $stmt2 = $conn->prepare("INSERT INTO bookings2 (name, email, phone, booking_date) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("ssss", $name, $email, $phone, $booking_date);
        if ($stmt2->execute()) {
            $stmt2->close();
            $stmt3 = $conn->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt3->bind_param("i", $id);
            if ($stmt3->execute()) {
                $feedback = "<div class='alert success'>Booking accepted!</div>";
            } else {
                $feedback = "<div class='alert danger'>Error: " . $stmt3->error . "</div>";
            }
            $stmt3->close();
        } else {
            $feedback = "<div class='alert danger'>Error: " . $stmt2->error . "</div>";
            $stmt2->close();
        }
    } else {
        $feedback = "<div class='alert danger'>Error: Booking not found.</div>";
        $stmt->close();
    }
}

if (isset($_GET['done'])) {
    $id = $_GET['done'];
    $stmt = $conn->prepare("DELETE FROM bookings2 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $feedback = "<div class='alert success'>Booking marked as done!</div>";
    } else {
        $feedback = "<div class='alert danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

$sql = "SELECT * FROM bookings";
$result = $conn->query($sql);

$sql2 = "SELECT * FROM bookings2";
$result2 = $conn->query($sql2);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The BearFruits Studios</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #234b3a;
            --accent: #f8b400; 
            --light: #f7f8fa;
            --danger: #e74c3c;
            --success: #27ae60;
            --sidebar-width: 220px;
        }
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background: var(--light);
        }
        .dashboard {
            display: flex;
            min-height: 100vh;
            background: var(--light);
        }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 30px;
            position: fixed;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 10;
        }
        .sidebar .logo {
            font-size: 1.7rem;
            font-weight: bold;
            text-align: center;
            padding-bottom: 30px;
            letter-spacing: 1px;
        }
        .sidebar nav a {
            color: #fff;
            display: flex;
            align-items: center;
            padding: 16px 32px;
            text-decoration: none;
            font-size: 1.04rem;
            border-left: 5px solid transparent;
            transition: background 0.15s, border-color 0.15s;
        }
        .sidebar nav a.active,
        .sidebar nav a:hover {
            background: rgba(255,255,255,0.09);
            border-left: 5px solid var(--accent);
        }
        .sidebar nav a .fa-fw {
            width: 25px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px 30px 30px 30px;
            flex: 1;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }
        .dashboard-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--primary);
        }
        .dashboard-actions a {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 1rem;
            margin-left: 12px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .dashboard-actions a:hover {
            background: #1a372a;
        }
        .section-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.09);
            padding: 26px 30px 24px 30px;
            margin-bottom: 30px;
            overflow-x: auto;
            display: none;
        }
        .section-card.active {
            display: block;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 18px;
        }
        .alert {
            margin-bottom: 20px;
            padding: 12px 18px;
            border-radius: 7px;
            font-size: 1rem;
        }
        .alert.success { background: #eafaf1; color: var(--success); }
        .alert.danger { background: #fdeaea; color: var(--danger); }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th { background: var(--primary); color: #fff; font-weight: 700; }
        tr:nth-child(even) { background: #f2f4f7; }
        tr:hover { background: #f7f7f7; }

        .table-actions a, .table-actions button {
            margin: 0 4px;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.97rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .edit-btn { background: #fffbe7; color: #c58700; border: 1px solid #f8b400; }
        .edit-btn:hover { background: #f8e3a3; }
        .delete-btn { background: #fdeaea; color: var(--danger); border: 1px solid #e74c3c;}
        .delete-btn:hover { background: #f9c9c5;}
        .accept-btn { background: #eafaf1; color: var(--success); border: 1px solid #27ae60;}
        .accept-btn:hover { background: #c5f3d8;}
        .done-btn { background: #e0e9fd; color: #1252c6; border: 1px solid #366cd2;}
        .done-btn:hover { background: #c9deff;}

        /* Album image list styles */
        .image-list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .image-list-table th, .image-list-table td {
            border: 1px solid #eee;
            padding: 7px 10px;
            text-align: left;
        }
        .image-thumb {
            max-width: 80px; max-height: 70px; border-radius: 3px; box-shadow: 1px 1px 3px #ccc;
        }
        .delete-image-btn {
            background: #fdeaea; color: #e74c3c; border: 1px solid #e74c3c; padding: 5px 14px; border-radius: 5px; cursor: pointer;
        }
        .delete-image-btn:hover {
            background: #f9c9c5;
        }

        /* Modal */
        .modal, .modal-overlay {
            display: none;
        }
        .modal-overlay.show {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 100;
        }
        .modal.show {
            display: block;
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            border-radius: 14px;
            z-index: 101;
            width: 95vw;
            max-width: 470px;
            padding: 36px 30px 30px 30px;
        }
        .modal form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .modal label {
            font-weight: 700;
            color: var(--primary);
        }
        .modal input, .modal textarea {
            padding: 9px 11px;
            border-radius: 5px;
            border: 1px solid #bbb;
            font-size: 1rem;
        }
        .modal textarea { min-height: 80px; }
        .modal .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 13px;
        }
        .modal .modal-actions button {
            padding: 7px 16px;
            border-radius: 5px;
            font-weight: 600;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .modal .save-btn { background: var(--primary); color: #fff;}
        .modal .cancel-btn { background: #bbb; color: #fff;}
        .modal .save-btn:hover { background: #18412b; }
        .modal .cancel-btn:hover { background: #999; }

        /* Responsive */
        @media (max-width: 900px) {
            .main-content { padding: 30px 10px 10px 10px; }
            .section-card { padding: 18px 6px; }
            .dashboard-header { flex-direction: column; gap: 12px; }
        }
        @media (max-width: 700px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo">
            <i class="fa-solid fa-leaf"></i> <br>The BearFruits Admin</br>
        </div>
        <nav>
            <a href="#" class="active" id="nav-bookings"><i class="fa-fw fa-solid fa-calendar-check"></i>Bookings</a>
            <a href="#" id="nav-accepted"><i class="fa-fw fa-solid fa-check"></i>Accepted</a>
            <a href="#" id="nav-security"><i class="fa-fw fa-solid fa-user-shield"></i>Security</a>
            <a href="#" id="nav-upload"><i class="fa-fw fa-solid fa-image"></i>Upload Image</a>
            <a href="logout.php" class="dashboard-actions"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <div class="dashboard-header">
            <span class="dashboard-title" id="dashboard-title">Bookings Overview</span>
            <div class="dashboard-actions">
                <a href="homepage.html"><i class="fa fa-home"></i> Home Page</a>
                <a href="#" id="refreshBtn"><i class="fa fa-rotate"></i> Refresh</a>
            </div>
        </div>
        <?php if (!empty($feedback)) echo $feedback; ?>

        <!-- IMAGE UPLOAD SECTION (hidden by default, toggled by nav) -->
        <div class="section-card" id="section-upload">
            <div class="section-title"><i class="fa fa-image"></i> Upload New Album Picture</div>
            <form method="POST" enctype="multipart/form-data">
                <label for="image_category">Album Category:</label>
                <select name="image_category" id="image_category" required>
                    <option value="wedding">Wedding</option>
                    <option value="debut">Debut</option>
                </select>
                <input type="file" name="album_image" accept="image/*" required>
                <button type="submit" name="upload_album_image" class="save-btn" style="margin-top:10px;">Upload</button>
            </form>
            <div style="margin-top:10px;font-size:0.95em;color:#555;">
                Uploaded images will appear in the <strong>Album</strong> page under the selected category.
            </div>
            <hr style="margin:26px 0 16px 0;">
            <div class="section-title" style="font-size:1.12rem;"><i class="fa fa-list"></i> Wedding Album Images</div>
            <?php if (count($wedding_images)): ?>
            <table class="image-list-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Filename</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wedding_images as $img): ?>
                    <tr>
                        <td><img src="uploads/wedding/<?php echo urlencode($img); ?>" class="image-thumb"></td>
                        <td><?php echo htmlspecialchars($img); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_image" value="1">
                                <input type="hidden" name="delete_category" value="wedding">
                                <input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($img); ?>">
                                <button type="submit" class="delete-image-btn" onclick="return confirm('Delete this image?');">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="color:#888;">No images uploaded yet.</div>
            <?php endif; ?>

            <div class="section-title" style="font-size:1.12rem; margin-top:28px;"><i class="fa fa-list"></i> Debut Album Images</div>
            <?php if (count($debut_images)): ?>
            <table class="image-list-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Filename</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($debut_images as $img): ?>
                    <tr>
                        <td><img src="uploads/debut/<?php echo urlencode($img); ?>" class="image-thumb"></td>
                        <td><?php echo htmlspecialchars($img); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_image" value="1">
                                <input type="hidden" name="delete_category" value="debut">
                                <input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($img); ?>">
                                <button type="submit" class="delete-image-btn" onclick="return confirm('Delete this image?');">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="color:#888;">No images uploaded yet.</div>
            <?php endif; ?>
        </div>

        <div class="section-card active" id="section-bookings">
            <div class="section-title"><i class="fa fa-hourglass-start"></i> Pending Bookings</div>
            <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th><th>Email</th><th>Phone</th><th>Service</th>
                        <th>Message</th><th>Booking Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr data-booking='<?php echo json_encode($row); ?>'>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['service']); ?></td>
                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                        <td><?php echo $row['booking_date']; ?></td>
                        <td class="table-actions">
                            <a href="#" class="edit-btn" data-id="<?php echo $row['id']; ?>"><i class="fa fa-pencil"></i> Edit</a>
                            <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this booking?');"><i class="fa fa-trash"></i> Delete</a>
                            <a href="?accept=<?php echo $row['id']; ?>" class="accept-btn" onclick="return confirm('Accept this booking?');"><i class="fa fa-check"></i> Accept</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert">No new bookings found.</div>
            <?php endif; ?>
        </div>

        <div class="section-card" id="section-accepted">
            <div class="section-title"><i class="fa fa-check"></i> Accepted Bookings</div>
            <?php if ($result2->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th><th>Email</th><th>Phone</th><th>Booking Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row2 = $result2->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row2['name']); ?></td>
                        <td><?php echo htmlspecialchars($row2['email']); ?></td>
                        <td><?php echo htmlspecialchars($row2['phone']); ?></td>
                        <td><?php echo $row2['booking_date']; ?></td>
                        <td class="table-actions">
                            <a href="?done=<?php echo $row2['id']; ?>" class="done-btn" onclick="return confirm('Mark this booking as done?');"><i class="fa fa-check-double"></i> Done</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert">No accepted bookings found.</div>
            <?php endif; ?>
        </div>

        <div class="section-card" id="section-security">
            <div class="section-title"><i class="fa fa-user-shield"></i> Security Manager</div>
            <form id="securityForm" method="POST" action="update_admin.php">
                <label for="username">New Username:</label>
                <input type="text" id="username" name="username" required />
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" required />
                <div style="margin-top: 12px;">
                    <button type="submit" class="save-btn">Update Credentials</button>
                    <button type="button" class="cancel-btn" id="resetBtn">Reset Password</button>
                </div>
            </form>
            <div class="alert" style="margin-top:20px;">Security manager functionality coming soon.</div>
        </div>
    </main>
</div>

<!-- Modal for editing booking -->
<div class="modal-overlay" id="modal-overlay"></div>
<div class="modal" id="editModal">
    <h2>Edit Booking</h2>
    <form method="POST" id="editForm">
        <input type="hidden" name="id" id="edit-id">
        <label>Name</label>
        <input type="text" name="name" id="edit-name" required>
        <label>Email</label>
        <input type="email" name="email" id="edit-email" required>
        <label>Phone</label>
        <input type="text" name="phone" id="edit-phone" required>
        <label>Service</label>
        <input type="text" name="service" id="edit-service" required>
        <label>Message</label>
        <textarea name="message" id="edit-message" required></textarea>
        <div class="modal-actions">
            <button type="submit" name="update_booking" class="save-btn">Save</button>
            <button type="button" class="cancel-btn" id="closeEditModal">Cancel</button>
        </div>
    </form>
</div>
<script>
    // Navigation
    const navBookings = document.getElementById('nav-bookings');
    const navAccepted = document.getElementById('nav-accepted');
    const navSecurity = document.getElementById('nav-security');
    const navUpload = document.getElementById('nav-upload');
    const sectionBookings = document.getElementById('section-bookings');
    const sectionAccepted = document.getElementById('section-accepted');
    const sectionSecurity = document.getElementById('section-security');
    const sectionUpload = document.getElementById('section-upload');
    const dashboardTitle = document.getElementById('dashboard-title');
    function showSection(section) {
        sectionBookings.classList.remove('active');
        sectionAccepted.classList.remove('active');
        sectionSecurity.classList.remove('active');
        sectionUpload.classList.remove('active');
        navBookings.classList.remove('active');
        navAccepted.classList.remove('active');
        navSecurity.classList.remove('active');
        navUpload.classList.remove('active');
        if (section === 'bookings') {
            sectionBookings.classList.add('active');
            navBookings.classList.add('active');
            dashboardTitle.innerText = "Bookings Overview";
        } else if (section === 'accepted') {
            sectionAccepted.classList.add('active');
            navAccepted.classList.add('active');
            dashboardTitle.innerText = "Accepted Bookings";
        } else if (section === 'security') {
            sectionSecurity.classList.add('active');
            navSecurity.classList.add('active');
            dashboardTitle.innerText = "Security Manager";
        } else if (section === 'upload') {
            sectionUpload.classList.add('active');
            navUpload.classList.add('active');
            dashboardTitle.innerText = "Upload New Album Picture";
        }
    }
    navBookings.onclick = e => { e.preventDefault(); showSection('bookings'); }
    navAccepted.onclick = e => { e.preventDefault(); showSection('accepted'); }
    navSecurity.onclick = e => { e.preventDefault(); showSection('security'); }
    navUpload.onclick = e => { e.preventDefault(); showSection('upload'); }
    document.getElementById('refreshBtn').onclick = () => window.location.reload();

    // Show Bookings section by default
    showSection('bookings');

    // Edit modal logic
    const editModal = document.getElementById('editModal');
    const modalOverlay = document.getElementById('modal-overlay');
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const tr = btn.closest('tr');
            const booking = JSON.parse(tr.dataset.booking);
            document.getElementById('edit-id').value = booking.id;
            document.getElementById('edit-name').value = booking.name;
            document.getElementById('edit-email').value = booking.email;
            document.getElementById('edit-phone').value = booking.phone;
            document.getElementById('edit-service').value = booking.service;
            document.getElementById('edit-message').value = booking.message;
            editModal.classList.add('show');
            modalOverlay.classList.add('show');
        });
    });
    document.getElementById('closeEditModal').onclick = function() {
        editModal.classList.remove('show');
        modalOverlay.classList.remove('show');
    }
    modalOverlay.onclick = function() {
        editModal.classList.remove('show');
        modalOverlay.classList.remove('show');
    }

    // Reset password
    document.getElementById("resetBtn").addEventListener("click", function () {
        if (confirm("Are you sure you want to reset the password to default?")) {
            fetch("reset_password.php", { method: "POST" })
                .then(response => response.text())
                .then(data => { alert(data); })
                .catch(error => { alert("An error occurred while resetting the password."); });
        }
    });
</script>
</body>
</html>
<?php $conn->close(); ?>