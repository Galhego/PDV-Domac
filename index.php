<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if ($usuario && $senha) {
        try {
            // Buscar usuário
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar hash SHA-256
            if ($user && hash('sha256', $senha) === $user['senha_hash']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                header('Location: vendas/index.php');
                exit;
            } else {
                $error = 'Usuário ou senha inválidos.';
            }
        } catch (PDOException $e) {
            $error = 'Erro no sistema. Tente novamente mais tarde.';
        }
    } else {
        $error = 'Por favor, preencha todos os campos.';
    }
}

// Redirecionar se já estiver logado
if (isset($_SESSION['user_id'])) {
    header('Location: vendas/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Coxinha - Login</title>
    <script src="https://cdn.tailwindcss.com "></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css ">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins :wght@300;400;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FFF5F5;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FEEBC8' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .food-icon {
            position: absolute;
            opacity: 0.1;
            z-index: 0;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #E53E3E 0%, #C53030 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .input-field:focus {
            border-color: #F6AD55;
            box-shadow: 0 0 0 3px rgba(246, 173, 85, 0.3);
        }
        
        .coxinha-icon {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Food icons decoration -->
    <i class="food-icon text-4xl text-yellow-400 top-10 left-10 hidden md:block"><i class="fas fa-hamburger"></i></i>
    <i class="food-icon text-3xl text-red-400 bottom-20 right-20 hidden md:block"><i class="fas fa-hotdog"></i></i>
    <i class="food-icon text-5xl text-yellow-400 top-1/3 right-10 hidden md:block"><i class="fas fa-ice-cream"></i></i>
    <i class="food-icon text-2xl text-red-400 bottom-1/4 left-15 hidden md:block"><i class="fas fa-drumstick-bite"></i></i>
    
    <!-- Main card -->
    <div class="w-full max-w-md bg-white rounded-2xl overflow-hidden shadow-xl z-10 relative">
        <!-- Header with gradient -->
        <div class="bg-gradient-to-r from-red-600 to-red-500 py-6 px-8 text-center">
            <div class="flex items-center justify-center space-x-3">
                <div class="coxinha-icon text-yellow-300 text-4xl">
                    <i class="fas fa-drumstick-bite"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">
                    <span class="block text-yellow-300 text-sm font-normal">PDV GENÉRICO</span>
                    SEU NETINHO
                </h1>
            </div>
        </div>
        
        <!-- Form section -->
        <div class="px-8 py-8">
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <p><?= $error ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-6">
                    <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Usuário</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" id="username" name="usuario"
                            class="input-field w-full pl-10 pr-3 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-yellow-500"
                            placeholder="Digite seu usuário" required>
                    </div>
                </div>
                
                <div class="mb-8">
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="senha"
                            class="input-field w-full pl-10 pr-3 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-yellow-500"
                            placeholder="Digite sua senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login w-full py-3 px-4 rounded-lg text-white font-bold text-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50">
                    ENTRAR <i class="fas fa-sign-in-alt ml-2"></i>
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">© 2027 Seu Netinho. Todos os direitos reservados.</p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animations
            const foodIcons = document.querySelectorAll('.food-icon');
            foodIcons.forEach((icon, index) => {
                icon.style.animationDelay = `${index * 0.5}s`;
            });
            
            // Button animation
            const loginBtn = document.querySelector('.btn-login');
            loginBtn.addEventListener('mouseenter', () => {
                loginBtn.classList.add('animate-bounce');
            });
            loginBtn.addEventListener('mouseleave', () => {
                loginBtn.classList.remove('animate-bounce');
            });
        });
    </script>
</body>
</html>