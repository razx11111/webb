<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-wrapper">
    <main class="auth-card">
        <header>
            <h2>Login</h2>
        </header>

        <?php if(isset($_GET['success'])): ?>
            <p class="alert-success">Account created! You can now log in.</p>
        <?php endif; ?>
        
        <form action="/login" method="POST">
            <div class="form-group">
                <label for="identifier">Email (User) or Phone No. (Admin):</label>
                <input type="text" name="identifier" id="identifier" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="role">Log in as:</label>
                <select name="role" id="role" class="form-control">
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <footer>
            <p>
                <a href="/register">Don't have a user account? Create one.</a>
            </p>
        </footer>
    </main>
</body>
</html>
