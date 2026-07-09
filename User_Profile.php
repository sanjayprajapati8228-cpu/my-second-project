<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:UserLogin.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$messages = [];

// Keep DB ready for profile photo persistence.
$profile_col = mysqli_query($conn, "SHOW COLUMNS FROM `user_registration1` LIKE 'profile_image'");
if ($profile_col && mysqli_num_rows($profile_col) === 0) {
    @mysqli_query($conn, "ALTER TABLE `user_registration1` ADD `profile_image` VARCHAR(255) NULL");
}

$result = mysqli_query($conn, "SELECT * FROM `user_registration1` WHERE id = '$user_id' LIMIT 1");
$fetch = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

if (!$fetch) {
    session_destroy();
    header('location:UserLogin.php');
    exit();
}

if (isset($_POST['update_profile'])) {
    $u_name = mysqli_real_escape_string($conn, trim($_POST['update_name']));
    $u_phone = mysqli_real_escape_string($conn, trim($_POST['update_phone']));
    $u_addr = mysqli_real_escape_string($conn, trim($_POST['update_address']));
    $new_pass = trim($_POST['new_pass']);
    $confirm_pass = trim($_POST['confirm_pass']);

    mysqli_query($conn, "UPDATE `user_registration1` SET Fname = '$u_name', Phone = '$u_phone', Address = '$u_addr' WHERE id = '$user_id'");

    if ($new_pass !== '' || $confirm_pass !== '') {
        if ($new_pass === $confirm_pass) {
            $new_p = mysqli_real_escape_string($conn, $new_pass);
            mysqli_query($conn, "UPDATE `user_registration1` SET Password = '$new_p' WHERE id = '$user_id'");
            $messages[] = ['type' => 'ok', 'text' => 'Profile and password updated successfully.'];
        } else {
            $messages[] = ['type' => 'warn', 'text' => 'New Password and Confirm Password do not match.'];
        }
    } else {
        $messages[] = ['type' => 'ok', 'text' => 'Profile updated successfully.'];
    }

    if (!empty($_FILES['profile_image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $img_name = $_FILES['profile_image']['name'];
        $img_size = (int)$_FILES['profile_image']['size'];
        $img_tmp = $_FILES['profile_image']['tmp_name'];
        $img_error = (int)$_FILES['profile_image']['error'];
        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

        if ($img_error !== 0) {
            $messages[] = ['type' => 'warn', 'text' => 'Image upload failed. Please try again.'];
        } elseif (!in_array($ext, $allowed, true)) {
            $messages[] = ['type' => 'warn', 'text' => 'Only JPG, JPEG, PNG, WEBP are allowed.'];
        } elseif ($img_size > 2 * 1024 * 1024) {
            $messages[] = ['type' => 'warn', 'text' => 'Image size must be less than 2MB.'];
        } else {
            $upload_dir = 'uploaded_img/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }

            $new_file = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $target = $upload_dir . $new_file;

            if (move_uploaded_file($img_tmp, $target)) {
                $old_image = isset($fetch['profile_image']) ? $fetch['profile_image'] : '';
                mysqli_query($conn, "UPDATE `user_registration1` SET profile_image = '$new_file' WHERE id = '$user_id'");

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

    $result = mysqli_query($conn, "SELECT * FROM `user_registration1` WHERE id = '$user_id' LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $fetch = mysqli_fetch_assoc($result);
    }
}

$avatar = (!empty($fetch['profile_image']) && file_exists('uploaded_img/' . $fetch['profile_image']))
    ? 'uploaded_img/' . $fetch['profile_image']
    : 'uploaded_img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <style>
        :root {
            --bg: #f0f4f9;
            --card: #ffffff;
            --line: #dfe7ef;
            --text: #173247;
            --muted: #5a7386;
            --primary: #2463eb;
            --primary-dark: #1749b5;
            --accent: #f2f6ff;
            --ok: #1f8a5b;
            --warn: #b13b3b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: linear-gradient(180deg, #f8fbff 0%, #edf3f9 100%);
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 24px 12px;
        }

        .shell {
            max-width: 1180px;
            margin: 0 auto;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fbfdff;
            box-shadow: 0 16px 40px rgba(16, 42, 67, 0.08);
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

        .shell-head h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .shell-body {
            display: grid;
            grid-template-columns: 330px 1fr;
            gap: 0;
            min-height: 640px;
        }

        .left-pane {
            border-right: 1px solid var(--line);
            padding: 20px;
            background: #fcfdff;
        }

        .right-pane {
            padding: 20px;
            background: #fff;
        }

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

        .upload-wrap {
            display: flex;
            gap: 8px;
            align-items: center;
        }

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

        .btn-edit {
            background: var(--primary);
            color: #fff;
        }

        .btn-edit:hover { background: var(--primary-dark); }

        .btn-cancel {
            background: #f1f5fb;
            color: #2e4c62;
            display: none !important;
        }

        .btn-save {
            background: #1f8a5b;
            color: #fff;
            display: none !important;
        }

        .btn-back {
            background: #eef3fb;
            color: #31516a;
            border: 1px solid var(--line);
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

        .save-row {
            display: none;
            margin-top: 14px;
        }

        .editing .password-section,
        .editing .save-row,
        .editing .btn-cancel,
        .editing .btn-save {
            display: inline-flex !important;
        }

        .editing .password-section {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
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

        @media (max-width: 980px) {
            .shell-body { grid-template-columns: 1fr; }
            .left-pane { border-right: 0; border-bottom: 1px solid var(--line); }
        }

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
            .field-full { grid-column: span 1; }
            .editing .password-section { grid-template-columns: 1fr; }
            .bottom-actions { justify-content: flex-start; flex-wrap: wrap; }
        }
    </style>
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
    <div class="shell">
        <div class="shell-head">
            <h2>User Profile</h2>
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
                            <input class="hidden-file" type="file" name="profile_image" id="profileImageInput" accept=".jpg,.jpeg,.png,.webp" disabled>
                        </div>
                        <div class="hint">JPG, PNG, WEBP | Max 2MB</div>
                    </div>
                    <div class="user-meta">
                        <div><strong>Name:</strong> <?php echo htmlspecialchars($fetch['Fname']); ?></div>
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
                            <label>Full Name</label>
                            <input class="input edit-field" type="text" name="update_name" value="<?php echo htmlspecialchars($fetch['Fname']); ?>" required readonly>
                        </div>

                        <div class="field">
                            <label>Gender</label>
                            <input class="input edit-field" type="text" value="<?php echo htmlspecialchars($fetch['Gender']); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Phone</label>
                            <input class="input edit-field" type="tel" name="update_phone" value="<?php echo htmlspecialchars($fetch['Phone']); ?>" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required readonly>
                        </div>

                        <div class="field">
                            <label>Email</label>
                            <input class="input" type="email" value="<?php echo htmlspecialchars($fetch['Email']); ?>" readonly>
                        </div>

                        <div class="field field-full">
                            <label>Address</label>
                            <input class="input edit-field" type="text" name="update_address" value="<?php echo htmlspecialchars($fetch['Address']); ?>" required readonly>
                        </div>
                    </div>

                    <div class="password-section" id="passwordSection">
                        <div class="field">
                            <label>Add New Password</label>
                            <input class="input edit-field" type="password" name="new_pass" id="newPass" placeholder="Enter new password" readonly>
                        </div>
                        <div class="field">
                            <label>Confirm Password</label>
                            <input class="input edit-field" type="password" name="confirm_pass" id="confirmPass" placeholder="Re-enter password" readonly>
                        </div>
                    </div>

                    <div class="save-row"></div>

                    <div class="bottom-actions">
                        <button type="button" class="btn btn-edit btn-secondary-action" id="editBtn"><i class="fas fa-pen"></i> Edit Profile</button>
                        <button type="button" class="btn btn-cancel btn-danger-action" id="cancelBtn"><i class="fas fa-xmark"></i> Cancel</button>
                        <button type="submit" name="update_profile" class="btn btn-save btn-secondary-action" id="saveBtn"><i class="fas fa-check"></i> Save Changes</button>
                        <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
                    </div>
                </section>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('profileForm');
        const editBtn = document.getElementById('editBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');
        const fileInput = document.getElementById('profileImageInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const editFields = document.querySelectorAll('.edit-field');

        const initialValues = {};
        editFields.forEach((field, index) => {
            initialValues[index] = field.value || '';
        });
        const initialAvatar = avatarPreview.src;

        function setEditMode(enable) {
            if (enable) {
                form.classList.remove('readonly');
                form.classList.add('editing');
                editFields.forEach((field) => {
                    if (field.name === 'new_pass' || field.name === 'confirm_pass') {
                        field.removeAttribute('readonly');
                    } else if (field.name === 'update_name' || field.name === 'update_phone' || field.name === 'update_address') {
                        field.removeAttribute('readonly');
                    }
                });
                fileInput.disabled = false;
                return;
            }

            form.classList.remove('editing');
            form.classList.add('readonly');
            editFields.forEach((field, index) => {
                if (field.name === 'new_pass' || field.name === 'confirm_pass') {
                    field.value = '';
                    field.setAttribute('readonly', 'readonly');
                } else if (field.name === 'update_name' || field.name === 'update_phone' || field.name === 'update_address') {
                    field.value = initialValues[index] || '';
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




