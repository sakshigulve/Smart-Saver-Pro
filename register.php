<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Handle Image
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $img_name = time() . "_" . $_FILES['profile_pic']['name'];
        $target = "assets/uploads/" . $img_name;

        if (!is_dir('assets/uploads/')) { mkdir('assets/uploads/', 0777, true); }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, profile_pic) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user, $email, $pass, $target])) {
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
            header("Location: login.php?success=1"); 
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Smart Saver Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom File Input Styling */
        .file-input-container {
            position: relative;
            margin-bottom: 20px;
        }
        #file-upload {
            display: none; /* Hide the ugly default button */
        }
        .custom-file-label {
            display: block;
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed var(--primary);
            color: var(--text-dim);
            padding: 15px;
            text-align: center;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
        }
        .custom-file-label:hover {
            background: rgba(0, 210, 255, 0.1);
            color: var(--primary);
        }
        .custom-file-label i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="auth-container">
    <div class="auth-card card">
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="background: var(--secondary); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fa fa-user-plus" style="color: white; font-size: 1.5rem;"></i>
            </div>
            <h2 style="margin: 0; color: var(--text-main);">Join Smart Saver</h2>
            <p style="color: var(--text-dim); font-size: 0.85rem;">Start your journey to financial freedom</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label style="display:block; margin-bottom:5px; font-size:0.8rem; color:var(--text-dim);">Full Name</label>
                <input type="text" name="username" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom:5px; font-size:0.8rem; color:var(--text-dim);">Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom:5px; font-size:0.8rem; color:var(--text-dim);">Password</label>
                <input type="password" name="password" placeholder="Min. 8 characters" required>
            </div>

            <div class="file-input-container">
                <label style="display:block; margin-bottom:5px; font-size:0.8rem; color:var(--text-dim);">Profile Picture</label>
                <label for="file-upload" class="custom-file-label" id="file-label">
                    <i class="fa fa-cloud-upload-alt"></i>
                    <span id="file-text">Click to upload photo</span>
                </label>
                <input id="file-upload" type="file" name="profile_pic" accept="image/*" required onchange="updateFileName()">
            </div>
            
            <button type="submit" class="btn">Create My Account</button>
        </form>

        <div style="margin-top: 20px; text-align: center; border-top: 1px solid var(--glass); padding-top: 20px;">
            <p style="color: var(--text-dim); font-size: 0.85rem;">Already have an account? 
                <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: bold;">Login here</a>
            </p>
        </div>
    </div>

    <script>
        // JavaScript to show the selected file name
        function updateFileName() {
            const input = document.getElementById('file-upload');
            const label = document.getElementById('file-text');
            if (input.files.length > 0) {
                label.innerText = "Selected: " + input.files[0].name;
                document.getElementById('file-label').style.borderColor = "var(--success)";
                document.getElementById('file-label').style.color = "var(--success)";
            }
        }
    </script>
</body>
</html>