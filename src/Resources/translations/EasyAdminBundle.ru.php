<?php

return [
    'page_title' => [
        // 'dashboard' => '',
        'detail' => '%entity_label_singular% (#%entity_id%)',
        'edit' => '%entity_label_singular% (#%entity_id%)',
        'index' => '%entity_label_plural%',
        'new' => 'Создать новый %entity_label_singular%',
        'exception' => 'Ошибка|Ошибки|Ошибок',
    ],

    'datagrid' => [
        // 'hidden_results' => '',
        'no_results' => 'Ничего не найдено.',
    ],

    'paginator' => [
        'first' => 'Первая',
        'previous' => 'Предыдущая',
        'next' => 'Следующая',
        'last' => 'Последняя',
        'counter' => '<strong>%start%</strong> - <strong>%end%</strong> из <strong>%results%</strong>',
        'results' => '<strong>%count%</strong> результат|<strong>%count%</strong> результата|<strong>%count%</strong> результатов',
    ],

    'label' => [
        'true' => 'Да',
        'false' => 'Нет',
        'empty' => 'Пусто',
        'null' => 'Null',
        'nullable_field' => 'Оставить пустым',
        'object' => 'PHP-объект',
        'inaccessible' => 'Недоступно',
        'inaccessible.explanation' => 'Нет геттера для этого поля или свойство не общедоступно',
        'form.empty_value' => 'Пусто',
    ],

    'field' => [
        // 'code_editor.view_code' => '',
        // 'text_editor.view_content' => '',
    ],

    'action' => [
        'entity_actions' => 'Действия',
        'new' => 'Создать %entity_label_singular%',
        'search' => 'Поиск',
        'detail' => 'Показать',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'cancel' => 'Отклонить',
        'index' => 'Вернуться к списку',
        'deselect' => 'Снять выбор',
        'add_new_item' => 'Добавить новый элемент',
        'remove_item' => 'Удалить элемент',
        'choose_file' => 'Выберите файл',
        // 'close' => '',
        // 'create' => '',
        // 'create_and_add_another' => '',
        // 'create_and_continue' => '',
        // 'save' => '',
        // 'save_and_continue' => '',
    ],

    'batch_action_modal' => [
        // 'title' => '',
        // 'content' => '',
        // 'action' => '',
    ],

    'delete_modal' => [
        'title' => 'Вы действительно хотите удалить этот объект?',
        'content' => 'Эту операцию нельзя отменить.',
    ],

    'filter' => [
        'title' => 'Фильтры',
        'button.clear' => 'Очистить',
        'button.apply' => 'Применить',
        'label.is_equal_to' => 'равно',
        'label.is_not_equal_to' => 'не равно',
        'label.is_greater_than' => 'больше чем',
        'label.is_greater_than_or_equal_to' => 'больше чем или равно',
        'label.is_less_than' => 'меньше чем',
        'label.is_less_than_or_equal_to' => 'меньше чем или равно',
        // 'label.is_between' => '',
        'label.contains' => 'содержит',
        'label.not_contains' => 'не содержит',
        'label.starts_with' => 'начинается с',
        'label.ends_with' => 'заканчивается на',
        'label.exactly' => 'точно',
        'label.not_exactly' => 'не точно',
        'label.is_same' => 'так же',
        'label.is_not_same' => 'не так же',
        'label.is_after' => 'после',
        'label.is_after_or_same' => 'после или же',
        'label.is_before' => 'до',
        'label.is_before_or_same' => 'до или же',
    ],

    'form' => [
        'are_you_sure' => 'Вы не сохранили сделанные изменения.',
        // 'tab.error_badge_title' => '',
    ],

    'user' => [
        'logged_in_as' => 'Вы вошли как',
        'unnamed' => 'Безымянный пользователь',
        'anonymous' => 'Анонимный пользователь',
        'sign_out' => 'Выход',
        // 'exit_impersonation' => '',
    ],

    'login_page' => [
        'username' => 'Логин',
        'password' => 'Пароль',
        'sign_in' => 'Войти',
    ],

    'exception' => [
        'entity_not_found' => 'Элемент больше не доступен.',
        'entity_remove' => 'Элемент не может быть удалён, потому что другой элемент зависит от него.',
        'forbidden_action' => 'Запрашиваемое действие запрещено для этого элемента.',
    ],
];
