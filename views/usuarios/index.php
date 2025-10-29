<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Laboratorio N/A</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg1: #0f172a;
            --bg2: #0b3a5b;
            --card: rgba(255, 255, 255, 0.04);
            --accent: #06b6d4;
            /* cyan */
            --muted: rgba(255, 255, 255, 0.7);
            --radius: 14px;
            --glass: rgba(255, 255, 255, 0.06);
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: linear-gradient(135deg, var(--bg1), var(--bg2));
            color: #fff;
            -webkit-font-smoothing: antialiased;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .container {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media(min-width:900px) {
            .container {
                grid-template-columns: 460px 1fr;
                align-items: center
            }
        }

        .card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.02));
            border-radius: var(--radius);
            padding: 28px;
            box-shadow: 0 10px 30px rgba(42, 66, 173, 0.6);
            backdrop-filter: blur(8px) saturate(120%);
        }

        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px
        }

        .logo {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #032;
        }

        h1 {
            margin: 0;
            font-size: 20px
        }

        p.lead {
            margin: 6px 0 18px;
            color: var(--muted);
            font-size: 14px
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px
        }

        .field {
            position: relative
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            color: var(--muted);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 44px 12px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: transparent;
            color: #fff;
            font-size: 15px;
            outline: none
        }

        .field .eye {
            position: absolute;
            right: 10px;
            top: 36px;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0.9
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 6px
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted)
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--accent), #7c3aed);
            color: #042;
            border: rgba(3, 6, 23, 0.45)
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--muted)
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.03), transparent);
            margin: 12px 0;
            border-radius: 2px
        }

        .socials {
            display: flex;
            gap: 8px
        }

        .socials button {
            flex: 1
        }

        .side {
            display: none;
            padding: 28px;
            color: var(--muted);
            font-size: 14px;
        }

        @media(min-width:900px) {
            .side {
                display: block
            }
        }

        .illustration {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            display: flex;
            align-items: center;
            justify-content: center
        }

        .small {
            font-size: 13px;
            color: var(--muted)
        }

        .error {
            color: #ffb4b4;
            font-size: 13px
        }

        .success {
            color: #b9f6ca;
            font-size: 13px
        }

        /* Floating label example (if wanted later) */
        .note {
            margin-top: 10px;
            font-size: 13px;
            color: var(--muted)
        }
    </style>
</head>

<body>
    <main class="container">
        <section class="card" aria-labelledby="loginTitle">
            <div class="brand">
                <img src="../../assets/img/logo.png" alt="Logo" class="logo-img" width="40" height="40">
                <div>
                    <h1 id="loginTitle">Bienvenido</h1>
                </div>
            </div>
            <form id="loginForm" method="POST" action="../../index.php" novalidate>
                <div class="field">
                    <label for="username">Usuario</label>
                    <input id="usuario" name="usuario" type="text" required autocomplete="username">
                    Ingresa tu usuario
                </div>
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
                    
                </div>

                <button class="btn btn-primary" type="submit">Iniciar sesión</button>
                <div class="divider" aria-hidden="true"></div>
            </form>


            <div id="message" style="margin-top:12px" aria-live="polite"></div>
        </section>
    </main>
</body>

</html>