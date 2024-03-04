<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->

    <!-- Styles -->
    <style>
        body {
            background-color: #f5f5f5; /* Fondo gris claro */
            font-family: Arial, sans-serif;
        }
        h2 {
            text-align: center;
            margin-top: 50px;
        }
        form {
            background-color: #ffffff; /* Fondo blanco para el formulario */
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px auto;
            max-width: 400px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #007BFF; /* Color de fondo azul */
            color: #fff; /* Color de texto blanco */
            padding: 10px 20px;
            border: none;
            margin-top: 20px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3; /* Color de fondo azul más oscuro en el hover */
        }
    </style>
</head>

<body>
    <h2>Restablecer Contraseña</h2>

    <form method="POST" action="{{route('reset-password')}}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Correo Electrónico:</label>
        <input type="email" name="email" id="email"  required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>

        <label for="password_confirmation">Confirmar Contraseña:</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>

        <button type="submit">Restablecer Contraseña</button>
    </form>
 </body>

</html>