<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="/img/valencia-fondo-transparente.png">
        <title>{{ config('app.name', 'D. VALENCIA') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        
        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .login-container {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                overflow: hidden;
                width: 90%;
                max-width: 650px;
            }
            
            @media (max-width: 640px) {
                .login-container {
                    width: 95%;
                    max-width: 100%;
                    margin: 1rem;
                }
            }
            
            .login-header {
                background: linear-gradient(135deg, #F15533 0%, #e6432d 100%);
                padding: 2rem 2.5rem;
                text-align: center;
            }
            
            .login-body {
                padding: 2.5rem 3rem;
            }
            
            @media (max-width: 640px) {
                .login-header {
                    padding: 2rem 1.5rem;
                }
                
                .login-body {
                    padding: 2rem 1.5rem;
                }
            }
            
            .form-input {
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                padding: 14px 18px;
                transition: all 0.3s ease;
                font-size: 15px;
            }
            
            .form-input:focus {
                border-color: #F15533;
                box-shadow: 0 0 0 3px rgba(241, 85, 51, 0.1);
                outline: none;
            }
            
            .btn-login {
                background: linear-gradient(135deg, #F15533 0%, #e6432d 100%);
                border: none;
                border-radius: 10px;
                padding: 14px 32px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(241, 85, 51, 0.3);
                font-size: 16px;
            }
            
            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(241, 85, 51, 0.4);
            }
            
            @media (max-width: 640px) {
                .form-input {
                    padding: 12px 14px;
                    font-size: 14px;
                }
                
                .btn-login {
                    padding: 12px 24px;
                    font-size: 15px;
                }
            }
            
            .logo-container {
                width: 140px;
                height: 140px;
                margin: 0 auto;
                border-radius: 50%;
                border: 5px solid white;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }
            
            @media (max-width: 640px) {
                .logo-container {
                    width: 110px;
                    height: 110px;
                    border: 4px solid white;
                }
            }
            
            .forgot-password {
                color: #667eea;
                transition: color 0.3s ease;
            }
            
            .forgot-password:hover {
                color: #F15533;
            }
            
            .btn-login:disabled {
                opacity: 0.8;
                cursor: not-allowed;
            }
            
            .spinner {
                width: 18px;
                height: 18px;
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                border-top-color: white;
                animation: spin 0.8s linear infinite;
                vertical-align: middle;
                display: none;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            .btn-login.loading .spinner {
                display: inline-block;
            }
            
            .btn-login.loading .btn-text {
                display: none;
            }
        </style>

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}" defer></script>
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
    </body>
</html>
