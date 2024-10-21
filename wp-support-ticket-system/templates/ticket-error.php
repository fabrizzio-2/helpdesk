<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error al Crear el Ticket</title>
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
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #dc3545;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <img src="<?php echo get_site_icon_url(); ?>" alt="Logo del sitio" class="logo">
    <div class="error-message">
        <h1>Error al Crear el Ticket</h1>
        <p>Lo sentimos, ha ocurrido un error al intentar crear tu ticket de soporte.</p>
        <p>Por favor, inténtalo de nuevo más tarde o ponte en contacto con nosotros directamente.</p>
    </div>
    <a href="<?php echo home_url(); ?>" class="button">Volver a la página principal</a>
</body>
</html>