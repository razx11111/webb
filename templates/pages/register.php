<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<body>
    <main class="auth-container">
        <h2>User Registration</h2>

        <?php 
        // Show error messages from URL
        if(isset($_GET['error'])): 
        ?>
            <p class="error-msg"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        
        <form action="/register" method="POST">
            <section class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </section>
            <section class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </section>
            <section class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </section>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <footer>
            <p>
                <a href="/login">Already have an account? Back to login.</a>
            </p>
        </footer>
    </main>
</body>
</html>
