<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск маршрута поезда</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<form id="trainForm">
    @csrf
    <label>Номер поезда:</label>
    <input type="text" name="train" required><br>

    <label>Станция отправления:</label>
    <input type="text" name="from" required><br>

    <label>Станция прибытия:</label>
    <input type="text" name="to" required><br>

    <label>Дата отправления:</label>
    <input type="number" name="day" min="1" max="31" required><br>

    <label>Месяц отправления:</label>
    <input type="number" name="month" min="1" max="12" required><br>

    <button type="button" onclick="submitForm()">Найти маршрут</button>
</form>

<div id="result"></div>

<script>
    function submitForm() {
        const formData = new FormData(document.getElementById('trainForm'));

        fetch("{{ route('train.route') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('result').innerText = `Ошибка: ${data.error}`;
                } else {
                    document.getElementById('result').innerText = `Маршрут: ${JSON.stringify(data.route)}`;
                }
            })
            .catch(error => {
                document.getElementById('result').innerText = `Ошибка: ${error}`;
            });
    }
</script>
</body>
</html>
