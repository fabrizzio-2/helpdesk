<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Creado con Éxito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <img src="<?php echo get_site_icon_url(); ?>" alt="Logo del sitio" class="logo">
    <div class="success-message">
        <h1>¡Ticket Creado con Éxito!</h1>
        <p>Tu ticket de soporte ha sido registrado correctamente en nuestro sistema.</p>
        <p>Nos pondremos en contacto contigo lo antes posible.</p>
    </div>
    <a href="<?php echo home_url(); ?>" class="button">Volver a la página principal</a>
</body>
</html>