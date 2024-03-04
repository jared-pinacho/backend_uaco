<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: #007BFF;
            color: #fff;
            padding: 20px 0;
            margin: 0;
        }

        h2 {
            font-size: 24px;
            margin: 20px;
        }
    </style>
</head>

<body>
    <h1>UNIVERSIDAD AUTÓNOMA COMUNAL DE OAXACA</h1>
    <h2>{{$res}}</h2>
    <h2>Puede cerrar la pestana</h2>
</body>

</html>
