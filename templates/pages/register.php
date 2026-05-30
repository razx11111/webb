<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Crisis Containment</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<<<<<<< HEAD
<body class="auth-wrapper">
    <main class="auth-card">
        <header>
            <h2>User Registration</h2>
        </header>

        <form action="/register" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
=======
<body>
    <div class="auth-container">
        <h2>User Registration</h2>
        <form action="/register" method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
>>>>>>> 69909eefd5f1a3c0f850aa329a194453a87ce25d
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
<<<<<<< HEAD

        <footer>
            <p>
                <a href="/login">Already have an account? Back to login.</a>
            </p>
        </footer>
    </main>
=======
        <p style="text-align: center; margin-top: 15px;"><a href="/login">Back to login</a></p>
    </div>
>>>>>>> 69909eefd5f1a3c0f850aa329a194453a87ce25d
</body>
</html>
