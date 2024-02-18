Для сборки окружения необходимо запустить docker-контейнеры.
Если в вашей системе есть команда `make`, то можно просто набрать:
```bash
$ make init
```
Все команды в **Makefile** уже прописаны. 
Либо, если команда `make` недоступна, можно набрать в консоли:
```bash
$ docker-compose up -d --build
$ docker-compose run --rm php composer install
```

Для запуска варианта php-скрипта можно будет набрать: 
```bash
$ make run-script
```
или
```bash
$ docker-compose run --rm php php public/index.php
```

Для запуска варианта sql, нужно будет в консоли управления БД создать хранимую процедуру(код лежит здесь `sql/script.sql`), 
и после вызвать:
```sql
CALL correct_duplicates();
```