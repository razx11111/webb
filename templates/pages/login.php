<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<body>
    <main class="auth-container">
        <h2>Login</h2>
        <?php if(isset($_GET['success'])): ?>
            <p class="success-msg">Account created! You can now log in.</p>
        <?php endif; ?>
        
        <?php 
        // Show error messages from URL
        if(isset($_GET['error'])): 
        ?>
            <p class="error-msg"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form action="/login" method="POST">
            <section class="form-group">
                <label>Email (User) or Phone No. (Admin):</label>
                <input type="text" name="identifier" required>
            </section>
            <section class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </section>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
        <p style="text-align: center; margin-top: 15px;"><a href="/register">Don't have a user account? Create one.</a></p>
    </main>
</body>
</html>
