$(document).ready(function() {
    $("#product-search").autocomplete({
        source: function(request, response) {
            $.getJSON("handle_product.php", { term: request.term }, function(data) {
                if (data.length > 0) {
                    // Формируем список с названием и заводским номером
                    response(data.map(item => ({
                        label: `${item.name} (${item.factory_number})`, // Название + номер
                        value: `${item.name} (${item.factory_number})`, // Значение для поля ввода
                        id: item.id // ID изделия
                    })));
                } else {
                    // Если совпадений нет, предлагаем создать новое изделие
                    showCreateProductModal(request.term);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#product-id").val(ui.item.id); // Сохраняем ID
            $("#product-search").val(ui.item.value); // Показываем название и номер
        }
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        // Подсветка совпадений
        const term = this.term; // Текущий поисковый запрос
        const regex = new RegExp(`(${term})`, "gi"); // Регулярное выражение для поиска
        const highlightedLabel = item.label.replace(regex, "<strong>$1</strong>"); // Подсветка

        return $("<li>")
            .append(`<div>${highlightedLabel}</div>`) // Отображаем с подсветкой
            .appendTo(ul);
    };
});

$(document).ready(function() {
    // Привязка события к кнопке
    $("#create-task-btn").click(function(event) {
        event.preventDefault(); // Предотвращаем отправку формы
        console.log("Кнопка нажата!"); // Отладочное сообщение
        createTask();
    });

    // Функция создания задачи
    function createTask() {
        console.log("Функция createTask вызвана!"); // Отладочное сообщение

        const productId = $("#product-id").val();
        const quantity = $("#quantity").val();
        const employeeId = $("#employee").val();
        const deadline = $("#deadline").val();
        const estimatedTime = $("#estimated-time").val();

        console.log("Данные формы:", { productId, quantity, employeeId, deadline, estimatedTime }); // Отладочное сообщение

        if (!productId) {
            alert("Выберите изделие!");
            return;
        }

        const data = {
            product_id: productId,
            quantity: quantity,
            employee_id: employeeId,
            deadline: deadline,
            estimated_time: estimatedTime
        };

        console.log("Данные для отправки:", data); // Отладочное сообщение

        $.post("create_task.php", data)
            .done(function(response) {
                console.log("Ответ сервера:", response); // Отладочное сообщение
                alert("Задача создана!");
                location.reload();
            })
            .fail(function(error) {
                console.error("Ошибка:", error); // Отладочное сообщение
                alert("Ошибка: " + error.responseText);
            });
    }
});

function showCreateProductModal(searchTerm) {
    // Создание модального окна
    const modalHtml = `
        <div id="product-modal" style="position:fixed;top:20%;left:30%;background:white;padding:20px;border:1px solid #ccc">
            <h3>Новое изделие</h3>
            <input type="text" id="modal-project" placeholder="Название проекта" required>
            <input type="text" id="modal-factory" placeholder="Заводской номер" required>
            <input type="text" id="modal-name" value="${searchTerm}" placeholder="Название" required>
            <button onclick="createNewProduct()">Создать</button>
            <button onclick="$('#product-modal').remove()">Отмена</button>
        </div>
    `;
    
    $('body').append(modalHtml);
}

function createNewProduct() {
    const data = {
        project_name: $('#modal-project').val(),
        factory_number: $('#modal-factory').val(),
        name: $('#modal-name').val()
    };

    $.ajax({
        url: 'handle_product.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#product-id').val(response.id);
            $('#product-search').val(response.name);
            $('#product-modal').remove();
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.error || 'Ошибка создания');
        }
    });
}