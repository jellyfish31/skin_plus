<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/favicon.png?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | SKIN+</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #5D3EBC; --bg-color: #FAFAFC; --text-dark: #2D2543; --text-muted: #756F86; --border-color: #E6E4ED; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); min-height: 100vh; display: flex; flex-direction: column; }
        
        .navbar { display: flex; align-items: center; padding: 2rem 8%; background: transparent; }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); line-height: 1.1; }
        .logo-area p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        .back-nav { display: flex; align-items: center; gap: 0.8rem; text-decoration: none; color: var(--text-dark); transition: all 0.3s ease; }
        .back-nav:hover { opacity: 0.8; transform: translateX(-2px); }

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

        
        @media (max-width: 768px) {
            body { padding: 0 1rem; }
            .navbar { padding: 1.5rem 0; }
            .logo-area h1 { font-size: 1.5rem; }
            .logo-area p { font-size: 0.75rem; }
            
            .login-container { padding-bottom: 4rem; justify-content: flex-start; margin-top: 1rem; }
            .login-container h2 { font-size: 2rem; }
            .login-container .welcome-text { font-size: 1rem; margin-bottom: 2rem; }
            
            .login-form { width: 100%; padding: 0 0.5rem; gap: 1.2rem; }
            .form-row { display: flex; flex-direction: column; align-items: stretch; gap: 0.4rem; }
            .form-row label { text-align: left; padding-right: 0; font-size: 0.95rem; }
            .form-control { padding: 0.75rem 1rem; font-size: 0.95rem; }
            .btn-login { margin-top: 0.5rem; font-size: 1rem; padding: 0.8rem; }
            .form-row div { display: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="javascript:history.back()" class="back-nav">
            <i class="fa-solid fa-chevron-left" style="color: var(--text-dark); font-size: 1.2rem;"></i>
            <div class="logo-area">
                <h1>SKIN+</h1>
                <p>Smart Price Comparison</p>
            </div>
        </a>
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
