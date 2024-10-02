Запуск проекта
---
- git clone https://github.com/makscraft/symfony-task
- composer install
- docker-compose up -d --build
- docker-compose exec php bin/console doctrine:database:create
- docker-compose exec php bin/console doctrine:migrations:diff
- docker-compose exec php bin/console doctrine:migrations:migrate

Загрузка тестовых данных
---
http://localhost:8080/upload

Вопросы и статистика
---
http://localhost:8080
