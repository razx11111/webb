<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<body>
    <div class="auth-container">
        <h2>Login</h2>
        <?php if(isset($_GET['success'])): ?>
            <p class="success-msg">Account created! You can now log in.</p>
        <?php endif; ?>
        
        <form action="/login" method="POST">
            <div class="form-group">
                <label>Email (User) or Phone No. (Admin):</label>
                <input type="text" name="identifier" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <button type="submit">Sign In</button>
        </form>
        <p style="text-align: center; margin-top: 15px;"><a href="/register">Don't have a user account? Create one.</a></p>
    </div>
</body>
</html>