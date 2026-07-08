<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | SKIN+</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #5D3EBC; --bg-color: #FAFAFC; --text-dark: #2D2543; --text-muted: #756F86; --border-color: #E6E4ED; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; }
        
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 2rem 8%; background: transparent; }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); line-height: 1.1; }
        .logo-area p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        .back-btn { background-color: var(--primary-color); color: white; padding: 0.6rem 2.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; }
        .back-btn:hover { background-color: #4A2E9F; }

        .login-container { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding-bottom: 8rem; }
        .login-container h2 { font-size: 2.8rem; font-weight: 700; color: #1A1230; margin-bottom: 0.2rem; }
        .login-container .welcome-text { color: var(--text-muted); font-size: 1.1rem; margin-bottom: 3rem; }

        .login-form { width: 100%; max-width: 580px; display: flex; flex-direction: column; gap: 1.5rem; }
        .form-row { display: grid; grid-template-columns: 160px 1fr; align-items: center; }
        .form-row label { font-size: 1.05rem; font-weight: 600; color: var(--text-dark); text-align: right; padding-right: 1.5rem; }
        .form-control { width: 100%; padding: 0.85rem 1.2rem; border-radius: 8px; border: 1px solid var(--border-color); background: white; font-size: 1rem; color: var(--text-dark); outline: none; transition: border 0.2s; }
        .form-control:focus { border-color: var(--primary-color); }

        .btn-login { width: 100%; padding: 0.9rem; background-color: var(--primary-color); color: white; border: none; border-radius: 30px; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-top: 1.5rem; transition: background 0.2s; box-shadow: 0 4px 12px rgba(93, 62, 188, 0.2); }
        .btn-login:hover { background-color: #4A2E9F; }
        .error-msg { background: #FCE8E6; color: #A8071A; padding: 0.8rem 1.2rem; border-radius: 8px; font-size: 0.95rem; font-weight: 500; border: 1px solid #FFCCC7; text-align: center; width: 100%; max-width: 580px; margin-bottom: 1.5rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo-area"><h1>SKIN+</h1><p>Smart Price Comparison</p></div>
        <a href="javascript:history.back()" class="back-btn">Back</a>
    </nav>

    <div class="login-container">
        <h2>Log In</h2>
        <p class="welcome-text">Welcome Back!</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php" class="login-form">
            <div class="form-row">
                <label>Username/ Email:</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>
            <div class="form-row">
                <label>Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-row">
                <div></div>
                <button type="submit" name="login" class="btn-login">Log In</button>
            </div>
        </form>
    </div>

</body>
</html>
