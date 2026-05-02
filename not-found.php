<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Página Não Encontrada - 404</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .not-found-container {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 2rem;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .not-found-container:hover {
            transform: translateY(-5px);
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .illustration {
            margin: 2rem 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .illustration svg {
            max-width: 200px;
            width: 100%;
            height: auto;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .search-box {
            margin: 2rem 0 1rem;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 9999px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .suggestions {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .suggestions-title {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.75rem;
            font-weight: 500;
        }

        .suggestions-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .suggestions-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .suggestions-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .not-found-container {
                padding: 2rem 1.5rem;
            }

            .error-code {
                font-size: 5rem;
            }

            .error-title {
                font-size: 1.4rem;
            }

            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.875rem;
            }
        }

        /* Animação do 404 */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .illustration svg {
            animation: float 3s ease-in-out infinite;
        }

        /* Ícones SVG inline */
        .icon-home,
        .icon-search {
            width: 18px;
            height: 18px;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="not-found-container">
        <div class="error-code">404</div>
        <h1 class="error-title">Ops! Página não encontrada</h1>
        <p class="error-message">
            Parece que você se perdeu no caminho.<br>
            A página que você está procurando pode ter sido removida,<br>
            renomeada ou está temporariamente indisponível.
        </p>

        <div class="illustration">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="80" stroke="url(#gradient)" stroke-width="4" stroke-dasharray="10 10" />
                <path d="M100 60 L100 110 M100 140 L100 145" stroke="url(#gradient)" stroke-width="6" stroke-linecap="round" />
                <circle cx="70" cy="90" r="5" fill="#667eea" />
                <circle cx="130" cy="90" r="5" fill="#667eea" />
                <path d="M75 125 Q100 140 125 125" stroke="#764ba2" stroke-width="4" stroke-linecap="round" fill="none" />
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#667eea" />
                        <stop offset="100%" stop-color="#764ba2" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <div class="suggestions">
            <div class="suggestions-links">
                <a href="<?= URL ?>">Página inicial</a>
            </div>
        </div>
    </div>

</body>

</html>