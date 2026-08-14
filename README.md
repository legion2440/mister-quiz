# Mister Quiz

Mister Quiz — quiz game на Laravel. Пользователь может зарегистрироваться, войти в аккаунт, пройти викторину, получить XP, посмотреть профиль со статистикой и таблицу лидеров.

## Что уже есть

- регистрация и вход;
- кнопки Login/Profile, Leaderboard, Start Quiz и Logout;
- доступ к quiz/profile только для авторизованных пользователей;
- категории Art, History, Geography, Science и Sports;
- по 2 случайных вопроса из каждой категории — 10 вопросов на quiz;
- сохранение выбранных вопросов и их порядка после обновления страницы;
- подсчёт правильных ответов и XP;
- статистика по категориям в формате `x/y`;
- профиль с rank, XP, процентами и количеством ответов;
- leaderboard top 10 по XP;
- SQLite для простого локального запуска;
- seed-пул из 20 вопросов и 80 перемешанных вариантов ответов;
- feature-тесты основных требований audit.

## Ranks

Rank зависит от количества XP:

- меньше `1500` XP — `Quiz Aprentice`;
- от `1500` до `4999` XP — `Average Quizer`;
- от `5000` до `9999` XP — `Epic Quizer`;
- `10000` XP и больше — `Quiz Master`.

## Требования

- PHP `^7.3` или `^8.0` согласно `composer.json`; проект проверен на PHP `8.5.9`;
- Composer;
- расширение PDO SQLite.

Frontend уже собран в `public/css/app.css`, поэтому Node.js для обычного запуска не нужен.

## Быстрый запуск

После клонирования репозитория выполни из корня проекта:

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate --seed
php artisan test
php artisan serve
```

Команды `php -r` работают одинаково в Bash, PowerShell и cmd, поэтому отдельный `touch` для Windows не требуется.

Открой URL, который покажет `php artisan serve`, обычно:

```text
http://127.0.0.1:8000
```

Если порт `8000` занят, Laravel может использовать следующий свободный порт.

## База данных

По умолчанию проект использует SQLite, поэтому MySQL/XAMPP для запуска не нужны.

В `.env.example` уже настроено:

```env
DB_CONNECTION=sqlite
DB_HOST=
DB_PORT=
DB_DATABASE=database/database.sqlite
DB_USERNAME=
DB_PASSWORD=
```

Чтобы полностью пересоздать локальную базу и seed-данные:

```bash
php artisan migrate:fresh --seed
```

Тесты используют отдельную SQLite-базу `:memory:` и не изменяют локальный `database/database.sqlite`.

## Основные страницы

- `/` — главная страница;
- `/register` — регистрация;
- `/login` — вход;
- `/quiz` — старт или продолжение текущего quiz;
- `/quiz/{quiz}/results` — сохранённые результаты завершённого quiz;
- `/profile` — профиль текущего пользователя;
- `/leaderboard` — таблица лидеров.

Logout выполняется POST-запросом из кнопки `Logout`; GET `/logout` намеренно не является страницей.

## Поведение quiz

- незавершённый quiz сохраняется в базе;
- F5 и возврат Home → Start Quiz показывают тот же набор вопросов в том же порядке;
- все 5 категорий присутствуют в каждом quiz;
- отправить quiz без ответа на каждый вопрос нельзя;
- после submit результат сохраняется и открывается отдельной GET-страницей;
- повторная отправка уже завершённого quiz перенаправляет на его Results и не начисляет XP повторно.

## Frontend

Для обычного запуска ничего собирать не нужно. Если требуется пересобрать frontend:

```bash
npm install
npm run dev
```

## Частые проблемы

### `composer` не найден

Установи Composer и проверь:

```bash
composer --version
```

### PDO SQLite не установлен

Если Laravel сообщает `could not find driver`, включи/установи расширения `pdo_sqlite` и `sqlite3` для используемой версии PHP.

### Connection refused

Если приложение пытается подключиться к MySQL и выдаёт `SQLSTATE[HY000] [2002] Connection refused`, проверь, что `.env` содержит SQLite-настройки выше, затем очисти кэш конфигурации:

```bash
php artisan config:clear
```

### Address already in use

Если порт занят, запусти сервер на другом порту:

```bash
php artisan serve --port=8001
```

### PHP 8.4+

Проект проверен на PHP `8.5.9`. `composer.lock` обновлён до версий зависимостей, совместимых с актуальными версиями PHP.

## Тесты

```bash
php artisan test
```

## Авторы
Yerkanat Nurmakhanov (@ynurmakh)
Nazar Yestayev (@nyestaye)
