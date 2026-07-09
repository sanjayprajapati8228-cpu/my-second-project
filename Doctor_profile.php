<?php
include 'config.php';
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header('location:Doctor_Login.php');
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];
$messages = [];

$select = mysqli_query($conn, "SELECT * FROM add_doctor WHERE id = '$doctor_id' LIMIT 1") or die('query failed');
$fetch = mysqli_num_rows($select) > 0 ? mysqli_fetch_assoc($select) : null;

if (!$fetch) {
    session_destroy();
    header('location:Doctor_Login.php');
    exit();
}

if (isset($_POST['update_profile'])) {
    $update_spec = mysqli_real_escape_string($conn, trim($_POST['update_specialization']));
    $update_name = mysqli_real_escape_string($conn, trim($_POST['update_name']));
    $update_phone = mysqli_real_escape_string($conn, trim($_POST['update_phone']));
    $update_email = mysqli_real_escape_string($conn, trim($_POST['update_email']));

    mysqli_query($conn, "UPDATE add_doctor SET DoctorSpecialization = '$update_spec', DoctorName = '$update_name', DoctorContectNO = '$update_phone', Email = '$update_email' WHERE id = '$doctor_id'") or die('query failed');

    $old_pass = trim($_POST['old_pass']);
    $new_pass = trim($_POST['new_pass']);
    $confirm_pass = trim($_POST['confirm_pass']);

    if ($old_pass !== '' || $new_pass !== '' || $confirm_pass !== '') {
        if ($old_pass === '' || $new_pass === '' || $confirm_pass === '') {
            $messages[] = ['type' => 'warn', 'text' => 'Please fill old password, new password, and confirm password.'];
        } elseif ($old_pass !== (string)$fetch['Password']) {
            $messages[] = ['type' => 'warn', 'text' => 'Old password is incorrect.'];
        } elseif ($new_pass !== $confirm_pass) {
            $messages[] = ['type' => 'warn', 'text' => 'New password and confirm password do not match.'];
        } else {
            $new_pass_esc = mysqli_real_escape_string($conn, $new_pass);
            mysqli_query($conn, "UPDATE add_doctor SET Password = '$new_pass_esc', CPassword = '$new_pass_esc' WHERE id = '$doctor_id'") or die('query failed');
            $messages[] = ['type' => 'ok', 'text' => 'Profile and password updated successfully.'];
        }
    } else {
        $messages[] = ['type' => 'ok', 'text' => 'Profile updated successfully.'];
    }

    if (!empty($_FILES['update_image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $img_name = $_FILES['update_image']['name'];
        $img_size = (int)$_FILES['update_image']['size'];
        $img_tmp = $_FILES['update_image']['tmp_name'];
        $img_error = (int)$_FILES['update_image']['error'];
        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

        if ($img_error !== 0) {
            $messages[] = ['type' => 'warn', 'text' => 'Image upload failed. Please try again.'];
        } elseif (!in_array($ext, $allowed, true)) {
            $messages[] = ['type' => 'warn', 'text' => 'Only JPG, JPEG, PNG, WEBP are allowed.'];
        } elseif ($img_size > 5 * 2560 * 2560) {
            $messages[] = ['type' => 'warn', 'text' => 'Image size must be less than 2MB.'];
        } else {
            $upload_dir = 'uploaded_img/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }

            $new_file = 'doctor_' . $doctor_id . '_' . time() . '.' . $ext;
            $target = $upload_dir . $new_file;

            if (move_uploaded_file($img_tmp, $target)) {
                $old_image = isset($fetch['image']) ? $fetch['image'] : '';
                mysqli_query($conn, "UPDATE add_doctor SET image = '$new_file' WHERE id = '$doctor_id'");

                if (!empty($old_image)) {
                    $old_path = $upload_dir . $old_image;
                    if ($old_image !== $new_file && file_exists($old_path)) {
                        @unlink($old_path);
                    }
                }
                $messages[] = ['type' => 'ok', 'text' => 'Profile picture updated.'];
            } else {
                $messages[] = ['type' => 'warn', 'text' => 'Unable to save image file.'];
            }
        }
    }

    $select = mysqli_query($conn, "SELECT * FROM add_doctor WHERE id = '$doctor_id' LIMIT 1") or die('query failed');
    $fetch = mysqli_num_rows($select) > 0 ? mysqli_fetch_assoc($select) : $fetch;
}

$avatar = (!empty($fetch['image']) && file_exists('uploaded_img/' . $fetch['image']))
    ? 'uploaded_img/' . $fetch['image']
    : 'default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css">
    <link rel="stylesheet" href="hms-theme.css">
    <style>
        :root {
            --bg-dark: #072a43;
            --bg-mid: #0e3b5f;
            --line: #dfe7ef;
            --text: #173247;
            --muted: #5a7386;
            --primary: #2463eb;
            --primary-dark: #1749b5;
            --ok: #1f8a5b;
            --warn: #b13b3b;
            --shadow: 0 10px 28px rgba(7, 42, 67, 0.1);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 15% 5%, #d8f3ff 0%, transparent 34%), radial-gradient(circle at 92% 92%, #dff0ff 0%, transparent 28%), #eef4f8;
            min-height: 100vh;
        }

        .mobile-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: linear-gradient(120deg, var(--bg-dark), var(--bg-mid));
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1100;
        }

        .menu-btn {
            border: 0;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
        }

        .layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--bg-dark), #062338);
            color: #dce9f4;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.16);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }

        .sidebar-header h3 { margin: 0; color: #fff; }
        .sidebar-menu { list-style: none; padding: 18px 0 0; margin: 0; }
        .sidebar-menu li { margin-bottom: 6px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 12px;
            color: #dce9f4;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active { background: rgba(255, 255, 255, 0.13); color: #fff; }

        .has-submenu .dropdown-icon { margin-left: auto; transition: transform 0.3s ease; }
        .has-submenu:hover > a .dropdown-icon { transform: rotate(180deg); }
        .has-submenu .submenu {
            list-style: none;
            margin: 6px 0 0;
            padding: 0 0 0 12px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }
        .has-submenu:hover .submenu { max-height: 220px; }
        .has-submenu .submenu a { font-size: 0.92rem; color: #bdd2e4; padding: 9px 12px; }

        .main-content { flex: 1; margin-left: 280px; padding: 24px; }

        .shell {
            max-width: 1100px;
            margin: 0 auto;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fbfdff;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .shell-head {
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .shell-head h2 { margin: 0; font-size: 1.1rem; font-weight: 700; }

        .shell-body {
            display: grid;
            grid-template-columns: 320px 1fr;
            min-height: 620px;
        }

        .left-pane {
            border-right: 1px solid var(--line);
            padding: 20px;
            background: #fcfdff;
        }

        .right-pane { padding: 20px; background: #fff; }

        .panel-title {
            margin: 0 0 14px;
            color: #2f4d63;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .avatar-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            background: #fff;
        }

        .avatar-preview {
            width: 100%;
            height: 250px;
            border-radius: 10px;
            border: 1px solid var(--line);
            object-fit: cover;
            background: #f3f7fb;
            margin-bottom: 12px;
        }

        .upload-wrap { display: flex; gap: 8px; align-items: center; }
        .upload-btn {
            flex: 1;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fbff;
            padding: 9px 10px;
            font-weight: 600;
            color: #2d4860;
            text-align: center;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .upload-btn:hover { background: #eef4ff; }
        .hidden-file { display: none; }

        .user-meta {
            margin-top: 12px;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .field-full { grid-column: span 2; }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.84rem;
            font-weight: 600;
            color: #49657b;
        }

        .input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px 11px;
            font-size: 0.92rem;
            color: #163145;
            background: #fff;
        }

        .readonly .input {
            background: #f8fbff;
            color: #6b7f8f;
            pointer-events: none;
        }

        .input:focus {
            outline: none;
            border-color: #8cb2f6;
            box-shadow: 0 0 0 4px rgba(36, 99, 235, 0.14);
        }

        .password-section {
            display: none;
            margin-top: 10px;
            border-top: 1px dashed var(--line);
            padding-top: 12px;
        }

        .editing .password-section,
        .editing .btn-cancel,
        .editing .btn-save {
            display: inline-flex !important;
        }

        .editing .password-section {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .alert {
            border-radius: 8px;
            padding: 9px 11px;
            margin-bottom: 9px;
            font-size: 0.88rem;
            border: 1px solid;
        }

        .alert-ok { background: #e7f6ef; border-color: #cbe9da; color: var(--ok); }
        .alert-warn { background: #fdeaea; border-color: #f3cbcb; color: var(--warn); }

        .hint {
            margin-top: 8px;
            color: #7b8f9d;
            font-size: 0.78rem;
        }

        .bottom-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-edit { background: var(--primary); color: #fff; }
        .btn-edit:hover { background: var(--primary-dark); }
        .btn-cancel { background: #f1f5fb; color: #2e4c62; display: none !important; }
        .btn-save { background: #1f8a5b; color: #fff; display: none !important; }
        .btn-back { background: #eef3fb; color: #31516a; border: 1px solid var(--line); }

        @media (max-width: 991px) {
            .mobile-topbar { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; padding: 16px; }
            .shell-body { grid-template-columns: 1fr; }
            .left-pane { border-right: 0; border-bottom: 1px solid var(--line); }
        }

        @media (max-width: 760px) {
            .form-grid { grid-template-columns: 1fr; }
            .field-full { grid-column: span 1; }
            .editing .password-section { grid-template-columns: 1fr; }
            .bottom-actions { justify-content: flex-start; flex-wrap: wrap; }
        }
    </style>
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
    <div class="mobile-topbar">
        <strong>HMS Doctor Panel</strong>
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button>
    </div>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" width="40" height="40" alt="Logo">
                <h3>HMS</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="Doctor_dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="Doctor_profile.php" class="active"><i class="fa fa-user-md"></i>My Profile</a></li>
                <li class="has-submenu">
                    <a href="javascript:void(0)"><i class="fas fa-calendar-alt"></i>Appointments <i class="fas fa-caret-down dropdown-icon"></i></a>
                    <ul class="submenu">
                        <li><a href="DoctorPersonalAppointments.php"><i class="fas fa-list"></i>View Appointments</a></li>
                        <li><a href="DoctorsPersonalPatient.php"><i class="fas fa-user-injured"></i>My Patients</a></li>
                    </ul>
                </li>
                <li><a href="DoctorSchedule.php"><i class="fas fa-clock"></i>Doctor Schedule</a></li>
                <li><a href="Doctor_dashboard.php?logout=true" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="shell">
                <div class="shell-head">
                    <h2>Doctor Profile</h2>
                    <small style="color:#6f8392;">Manage account details</small>
                </div>

                <form method="POST" enctype="multipart/form-data" id="profileForm" class="readonly">
                    <div class="shell-body">
                        <aside class="left-pane">
                            <h3 class="panel-title">Account Management</h3>
                            <div class="avatar-card">
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Photo" class="avatar-preview" id="avatarPreview">
                                <div class="upload-wrap">
                                    <label class="upload-btn" for="profileImageInput"><i class="fas fa-upload"></i> Upload Photo</label>
                                    <input class="hidden-file" type="file" name="update_image" id="profileImageInput" accept=".jpg,.jpeg,.png,.webp" disabled>
                                </div>
                                <div class="hint">JPG, PNG, WEBP | Max 2MB</div>
                            </div>
                            <div class="user-meta">
                                <div><strong>Name:</strong> Dr. <?php echo htmlspecialchars($fetch['DoctorName']); ?></div>
                                <div><strong>Email:</strong> <?php echo htmlspecialchars($fetch['Email']); ?></div>
                            </div>
                        </aside>

                        <section class="right-pane">
                            <?php foreach ($messages as $m) { ?>
                                <div class="alert <?php echo $m['type'] === 'ok' ? 'alert-ok' : 'alert-warn'; ?>">
                                    <?php echo htmlspecialchars($m['text']); ?>
                                </div>
                            <?php } ?>

                            <h3 class="panel-title">Profile Information</h3>
                            <div class="form-grid">
                                <div class="field">
                                    <label>Doctor Name</label>
                                    <input class="input edit-field" type="text" name="update_name" value="<?php echo htmlspecialchars($fetch['DoctorName']); ?>" required readonly>
                                </div>

                                <div class="field">
                                    <label>Specialization</label>
                                    <input class="input edit-field" type="text" name="update_specialization" value="<?php echo htmlspecialchars($fetch['DoctorSpecialization']); ?>" required readonly>
                                </div>

                                <div class="field">
                                    <label>Phone</label>
                                    <input class="input edit-field" type="tel" name="update_phone" value="<?php echo htmlspecialchars($fetch['DoctorContectNO']); ?>" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required readonly>
                                </div>

                                <div class="field">
                                    <label>Email</label>
                                    <input class="input edit-field" type="email" name="update_email" value="<?php echo htmlspecialchars($fetch['Email']); ?>" required readonly>
                                </div>

                            </div>

                            <div class="password-section" id="passwordSection">
                                <div class="field">
                                    <label>Old Password</label>
                                    <input class="input edit-field" type="password" name="old_pass" id="oldPass" placeholder="Enter old password" readonly>
                                </div>
                                <div class="field">
                                    <label>New Password</label>
                                    <input class="input edit-field" type="password" name="new_pass" id="newPass" placeholder="Enter new password" readonly>
                                </div>
                                <div class="field">
                                    <label>Confirm Password</label>
                                    <input class="input edit-field" type="password" name="confirm_pass" id="confirmPass" placeholder="Re-enter password" readonly>
                                </div>
                            </div>

                            <div class="bottom-actions">
                                <button type="button" class="btn btn-edit btn-secondary-action" id="editBtn"><i class="fas fa-pen"></i> Edit Profile</button>
                                <button type="button" class="btn btn-cancel btn-danger-action" id="cancelBtn"><i class="fas fa-xmark"></i> Cancel</button>
                                <button type="submit" name="update_profile" class="btn btn-save btn-secondary-action" id="saveBtn"><i class="fas fa-check"></i> Save Profile Changes</button>
                                <a href="Doctor_dashboard.php" class="btn btn-back btn-navigation"><i class="fas fa-arrow-left"></i> Dashboard</a>
                            </div>
                        </section>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        if (menuBtn) {
            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('show');
            });
        }

        const form = document.getElementById('profileForm');
        const editBtn = document.getElementById('editBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const fileInput = document.getElementById('profileImageInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const editFields = document.querySelectorAll('.edit-field');

        const initialValues = {};
        editFields.forEach((field, index) => {
            initialValues[index] = field.value || '';
        });
        const initialAvatar = avatarPreview.src;

        function isEditableName(name) {
            return [
                'update_name',
                'update_specialization',
                'update_phone',
                'update_email',
                'old_pass',
                'new_pass',
                'confirm_pass'
            ].includes(name);
        }

        function setEditMode(enable) {
            if (enable) {
                form.classList.remove('readonly');
                form.classList.add('editing');
                editFields.forEach((field) => {
                    if (isEditableName(field.name)) {
                        field.removeAttribute('readonly');
                    }
                });
                fileInput.disabled = false;
                return;
            }

            form.classList.remove('editing');
            form.classList.add('readonly');
            editFields.forEach((field, index) => {
                if (field.name === 'old_pass' || field.name === 'new_pass' || field.name === 'confirm_pass') {
                    field.value = '';
                } else {
                    field.value = initialValues[index] || '';
                }
                if (isEditableName(field.name)) {
                    field.setAttribute('readonly', 'readonly');
                }
            });
            fileInput.value = '';
            fileInput.disabled = true;
            avatarPreview.src = initialAvatar;
        }

        editBtn.addEventListener('click', function () {
            setEditMode(true);
        });

        cancelBtn.addEventListener('click', function () {
            setEditMode(false);
        });

        fileInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        });
    </script>
</body>
</html>





