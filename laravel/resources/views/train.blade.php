<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск маршрута поезда</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Поиск маршрута поезда</h2>
    <form id="trainRouteForm" class="mt-4">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="train">Номер поезда</label>
                <input type="text" class="form-control" id="train" name="train" placeholder="Например, 016А" required>
                <div class="invalid-feedback">Пожалуйста, введите номер поезда.</div>
            </div>
            <div class="form-group col-md-6">
                <label for="from">Станция отправления</label>
                <input type="text" class="form-control" id="from" name="from" placeholder="Код станции" required>
                <div class="invalid-feedback">Пожалуйста, введите станцию отправления.</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="to">Станция прибытия</label>
                <input type="text" class="form-control" id="to" name="to" placeholder="Код станции" required>
                <div class="invalid-feedback">Пожалуйста, введите станцию прибытия.</div>
            </div>
            <div class="form-group col-md-3">
                <label for="day">День отправления</label>
                <input type="number" class="form-control" id="day" name="day" min="1" max="31" required>
                <div class="invalid-feedback">Введите день отправления (1-31).</div>
            </div>
            <div class="form-group col-md-3">
                <label for="month">Месяц отправления</label>
                <input type="number" class="form-control" id="month" name="month" min="1" max="12" required>
                <div class="invalid-feedback">Введите месяц отправления (1-12).</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Найти маршрут</button>
    </form>

    <div id="result" class="mt-4">
        <!-- Здесь будут выводиться маршрут или сообщения об ошибках -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="{{ asset('js/train.js') }}"></script>
</body>
</html>
