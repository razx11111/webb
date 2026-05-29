<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
        <h2>Login</h2>
        <?php if(isset($_GET['success'])): ?>
            <p style="color: green;">Account created! You can now log in.</p>
        <?php endif; ?>
        
        <form action="/login" method="POST">
            <div style="margin-bottom: 10px;">
                <label>Email (User) or Phone No. (Admin):</label><br>
                <input type="text" name="identifier" required>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Password:</label><br>
                <input type="password" name="password" required>
            </div>
            <div style="margin-bottom: 10px;">
                <label>Role:</label><br>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <button type="submit">Sign In</button>
        </form>
        <p><a href="/register">Don't have a user account? Create one.</a></p>
    </div>
</body>
</html>