$(document).ready(function () {
    $('#trainRouteForm').on('submit', function (e) {
        e.preventDefault();

        // Очистка предыдущих ошибок
        $('.form-control').removeClass('is-invalid');
        $('#result').empty();

        // Получение данных формы
        const formData = {
            train: $('#train').val(),
            from: $('#from').val(),
            to: $('#to').val(),
            day: $('#day').val(),
            month: $('#month').val(),
        };

        // Проверка данных перед отправкой на сервер
        if (!validateForm(formData)) {
            return;
        }

        // Отправка данных на сервер с использованием AJAX
        $.ajax({
            url: '/train-route', // Путь к вашему маршруту
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    let routeHtml = `
                        <h3>Маршрут поезда ${response.route.train_number}</h3>
                        <p><strong>Отправление:</strong> ${response.route.from_station}</p>
                        <p><strong>Прибытие:</strong> ${response.route.to_station}</p>
                        <h4>Остановки:</h4>
                        <ul>`;
                    response.route.stops.forEach(stop => {
                        routeHtml += `
                            <li>
                                <strong>Станция:</strong> ${stop.station},
                                <strong>Прибытие:</strong> ${stop.arrival_time || 'Не указано'},
                                <strong>Отправление:</strong> ${stop.departure_time || 'Не указано'},
                                <strong>Стоянка:</strong> ${stop.stop_time} мин.
                            </li>`;
                    });
                    routeHtml += '</ul>';
                    $('#result').html(routeHtml);
                } else {
                    displayError(response.message);
                }
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Произошла ошибка при обработке запроса';
                displayError(errorMessage);
            }
        });
    });

    // Функция для валидации формы на стороне клиента
    function validateForm(formData) {
        let isValid = true;

        // Проверка для поля "train"
        if (!formData.train) {
            $('#train').addClass('is-invalid');
            isValid = false;
        }

        // Проверка для поля "from" (Origin)
        if (!formData.from || !/^\d{3,5}$/.test(formData.from)) {  // проверка на допустимый формат кода станции (например, 3-5 цифр)
            $('#from').addClass('is-invalid');
            isValid = false;
        }

        // Проверка для поля "to" (Destination)
        if (!formData.to || !/^\d{3,5}$/.test(formData.to)) {  // проверка на допустимый формат кода станции
            $('#to').addClass('is-invalid');
            isValid = false;
        }

        // Проверка для дня
        if (!formData.day || formData.day < 1 || formData.day > 31) {
            $('#day').addClass('is-invalid');
            isValid = false;
        }

        // Проверка для месяца
        if (!formData.month || formData.month < 1 || formData.month > 12) {
            $('#month').addClass('is-invalid');
            isValid = false;
        }

        return isValid;
    }


    // Функция для вывода ошибки на страницу
    function displayError(message) {
        $('#result').html(`<div class="alert alert-danger">${message}</div>`);
    }
});
