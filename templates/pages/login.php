<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<<<<<<< HEAD
<body class="auth-wrapper">
    <main class="auth-card">
        <header>
            <h2>Login</h2>
        </header>

        <?php if(isset($_GET['success'])): ?>
            <p class="alert-success">Account created! You can now log in.</p>
=======
<body>
    <div class="auth-container">
        <h2>Login</h2>
        <?php if(isset($_GET['success'])): ?>
            <p class="success-msg">Account created! You can now log in.</p>
>>>>>>> 69909eefd5f1a3c0f850aa329a194453a87ce25d
        <?php endif; ?>
        
        <form action="/login" method="POST">
            <div class="form-group">
<<<<<<< HEAD
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
=======
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
>>>>>>> 69909eefd5f1a3c0f850aa329a194453a87ce25d
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
<<<<<<< HEAD

        <footer>
            <p>
                <a href="/register">Don't have a user account? Create one.</a>
            </p>
        </footer>
    </main>
=======
        <p style="text-align: center; margin-top: 15px;"><a href="/register">Don't have a user account? Create one.</a></p>
    </div>
>>>>>>> 69909eefd5f1a3c0f850aa329a194453a87ce25d
</body>
</html>
