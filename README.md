# csrf

csrf защита, автоматически встраивается в каждый тег form и проверяется с каждым запросом POST

Данный класс помогает упростить реализацию защиты csrf, путём автоматической генерации токена и вставки его в каждый тег form на странице. 
Вставкой занимается скрипт loadcsrf.js находящийся в каталоге js. 
Если же вы по какой то причине не желаете его использовать, то в классе есть возможность использовать ручную вставку скрытого поля. 
Так же вы можете использовать данную защиту для реализации доступа через ajax/fetch, для этого предусмотрены методы token() и tokenName().

Для того чтобы использовать данный класс, достаточно сделать 3 простых действия:

Разместить у себя в проекте 2 файла(или 1 если не желаете использовать мой loadcsrf.js)
Подключить класс и после создания экземпляра класса он инициализируется.
Сделать необходимые настройки в начале файла csrf.php
В каталоге exemple есть файлы test.php и test_1.php которые показывают реализацию защиты и как всё это подключается.