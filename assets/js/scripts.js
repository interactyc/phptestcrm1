$("#product-search").autocomplete({
    source: "search_product.php",
    minLength: 2,
    select: function(event, ui) {
        $("#product-id").val(ui.item.id);
    }
});

setInterval(function() {
    $.get('check_tasks.php', function(response) {
        if (response.newTasks > 0) {
            alert('Новая задача!');
        }
    });
}, 5000); // Проверка каждые 5 секунд