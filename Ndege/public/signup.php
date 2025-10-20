<?php
require_once __DIR__ . '/../includes/db.php';

$message = ''; // <-- Add this line
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'parent' or 'student'
    $full_name = trim($_POST['full_name']);
    $message = ''; // Initialize message variable

    try {
        $pdo->beginTransaction();

        // 1️⃣ Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role]);
        $user_id = $pdo->lastInsertId();

        // 2️⃣ Based on role, insert into respective table
        if ($role === 'student') {
            $admission_no = $_POST['admission_no'] ?? null;
            $class = $_POST['class'] ?? null;
            $parent_id = $_POST['parent_id'] ?? null;

            $stmt = $pdo->prepare("INSERT INTO students (id, admission_no, class, parent_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $admission_no, $class, $parent_id]);
        }

        if ($role === 'parent') {
            $email= $_POST['email'] ?? null;
            $phone = $_POST['phone'] ?? null;
            $address = $_POST['address'] ?? null;
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


            $stmt = $pdo->prepare("INSERT INTO parents (parent_id, email,phone, address, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $email, $phone, $address, $password]);
        }

        $pdo->commit();
        echo "Registration successful!";

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - OSFMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #6a5acd;
            --secondary: #00bcd4;
            --danger: #e74c3c;
            --success: #2ecc71;
            --text-dark: #222;
            --text-light: #777;
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        /* ✅ Background Image Styling */
        body {
            background: url('../assets/image/R.JFIF') no-repeat center center/cover;
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            position: relative;
        }

        /* ✅ Dark Overlay for contrast */
        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            padding: 2.5rem;
            border-radius: 20px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.8s ease-in-out;
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-size: 1.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .alert {
            padding: 0.9rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease-in-out;
            display: none;
            display: block;
            opacity: 0.9;
            transition: opacity 0.3s;
        }
        .alert.success { background: var(--success); color: #fff; }
        .alert.error { background: var(--danger); color: #fff; }

        .alert a { color: #fff; text-decoration: underline; font-weight: 600; }

        form .form-group { margin-bottom: 1.3rem; }
        form label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-dark);
            letter-spacing: 0.5px;

        }

        form input, form select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 0.95rem;
            background: rgba(255,255,255,0.9);
            transition: 0.3s;
            color: var(--text-dark);
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        form input:focus, form select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(106,90,205,0.2);
            outline: none;
        }

        .password-wrapper { position: relative; }
        .toggle-password {
            position: absolute;
            top: 50%; right: 15px; transform: translateY(-50%);
            cursor: pointer; color: var(--text-light);
            font-size: 1.1rem;
            transition: color 0.3s;
            user-select: none;
            z-index: 2;
            background: rgba(255,255,255,0.7);
            padding: 5px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            backdrop-filter: blur(8px); 
            border: 1px solid #ccc;
            font-weight: 600;

        }
        .toggle-password:hover { color: var(--primary); }

        .password-strength {
            margin-top: 0.4rem; height: 6px; border-radius: 6px;
            background: #ddd; width: 100%;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            user-select: none;
            font-weight: 600;
            backdrop-filter: blur(8px);
            border: 1px solid #ccc;
            background: rgba(255,255,255,0.7);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: background 0.3s;
            z-index: 1;
            position: relative;
        
        }
        .password-strength span { display: block; height: 100%; width: 0%; transition: width 0.3s; }
        .strength-weak { background: var(--danger); }
        .strength-medium { background: #f1c40f; }
        .strength-strong { background: var(--success); }

        button.btn {
            width: 100%; padding: 0.9rem;
            border: none; border-radius: 10px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #fff; font-size: 1rem; cursor: pointer;
            font-weight: 600; transition: 0.3s;
        }
        button.btn:hover {
            background: linear-gradient(90deg, #5a4ad4, #03acc1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106,90,205,0.3);
        }

        .signup-link {
            text-align: center;
            margin-top: 1.3rem;
            font-size: 0.95rem;
            color: var(--text-light);
        }
        .signup-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .signup-link a:hover { color: #3f3fd1; }

        @keyframes fadeIn {
            from { opacity:0; transform:translateY(-20px); }
            to { opacity:1; transform:translateY(0); }
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-user-plus"></i> Create Account</h2>

    <?= $message ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Full Name</label>
            <input type="text" name="full_name" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" placeholder="john@example.com" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-user-shield"></i> Username</label>
            <input type="text" name="username" placeholder="john123" required>
        </div>

        <div class="form-group password-wrapper">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            <div class="password-strength"><span id="strengthBar"></span></div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-users"></i> Role</label>
            <select name="role" required>
                <option value="">Select role...</option>
                <option value="parent">Parent</option>
                <option value="student">Student</option>
            </select>
        </div>

        <!-- Parent fields -->
        <div id="parentFields" style="display:none;">
            <label>Phone:</label>
            <input type="text" name="phone"><br>
            <label>Address:</label>
            <input type="text" name="address"><br>
            <label>Parent_id</label>
            <input type="number" name="parent_id"><br>
        </div>

        <!-- Student fields -->
        <div id="studentFields" style="display:none;">
            <label>Admission No:</label>
            <input type="text" name="admission_no"><br>
            <label>Class:</label>
            <input type="text" name="class"><br>
            <label>Parent ID:</label>
            <input type="number" name="parent_id"><br>
        </div>

        <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Sign Up</button>
    </form>

    <p class="signup-link">
        Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login here</a>
    </p>
</div>

<script>
    const roleSelect = document.querySelector('select[name="role"]');
    const parentFields = document.getElementById('parentFields');
    const studentFields = document.getElementById('studentFields');
    roleSelect.addEventListener('change', function() {
        if (this.value === 'parent') {
            parentFields.style.display = 'block';
            studentFields.style.display = 'none';
        } else if (this.value === 'student') {
            parentFields.style.display = 'none';
            studentFields.style.display = 'block';
        } else {
            parentFields.style.display = 'none';
            studentFields.style.display = 'none';
        }
    });
    
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    password.addEventListener('input', function() {
        const val = password.value;
        let strength = 0;

        if(val.length >= 6) strength++;
        if(/[A-Z]/.test(val)) strength++;
        if(/[0-9]/.test(val)) strength++;
        if(/[\W]/.test(val)) strength++;

        if(strength <= 1) {
            strengthBar.style.width = "25%";
            strengthBar.className = "strength-weak";
        } else if(strength == 2 || strength == 3) {
            strengthBar.style.width = "65%";
            strengthBar.className = "strength-medium";
        } else if(strength >= 4) {
            strengthBar.style.width = "100%";
            strengthBar.className = "strength-strong";
        }
    });
</script>
</body>
</html>
