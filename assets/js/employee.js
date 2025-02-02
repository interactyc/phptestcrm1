function updateTaskStatus(taskId, status) {
    if (confirm('Вы уверены, что хотите изменить статус задачи?')) {
        fetch('update_task_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ task_id: taskId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Статус задачи обновлен!');
                location.reload(); // Перезагрузка страницы для обновления данных
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            alert('Произошла ошибка: ' + error);
        });
    }
}