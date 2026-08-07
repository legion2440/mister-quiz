# Mister Quiz

Mister Quiz - это quiz game на Laravel. Пользователь может зарегистрироваться, войти в аккаунт, пройти викторину, получить XP, посмотреть профиль со статистикой и таблицу лидеров.

## Что уже есть

- регистрация и вход;
- кнопки Login/Profile, Leaderboard, Start Quiz и Logout;
- доступ к quiz/profile только для авторизованных пользователей;
- quiz из категорий Art, History, Geography, Science и Sports;
- сохранение начатого quiz после обновления страницы;
- подсчёт правильных ответов и XP;
- статистика по категориям в формате `x/y`;
- профиль с rank, XP, процентами и количеством ответов;
- leaderboard top 10 по XP;
- SQLite база для простого локального запуска;
- seed-данные: 20 вопросов и 80 ответов.

## Ranks

Rank зависит от количества XP:

- меньше `1500` XP - `Quiz Aprentice`;
- от `1500` до `4999` XP - `Average Quizer`;
- от `5000` до `9999` XP - `Epic Quizer`;
- `10000` XP и больше - `Quiz Master`.

## Быстрый запуск

Если проект уже содержит папку `vendor/`, Composer не нужен.

```bash
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Открой сайт:

```text
http://127.0.0.1:8000
```

Если порт `8000` занят, Laravel сам запустит проект на следующем порту, например:

```text
http://127.0.0.1:8001
```

## Настройка базы

По умолчанию проект использует SQLite, поэтому MySQL запускать не нужно.

В `.env` должны быть такие настройки:

```env
DB_CONNECTION=sqlite
DB_HOST=
DB_PORT=
DB_DATABASE=database/database.sqlite
DB_USERNAME=
DB_PASSWORD=
```

После этого достаточно выполнить:

```bash
php artisan migrate --seed
```

Команда создаст таблицы и добавит тестовые вопросы.

## Если нет папки vendor

Если после клонирования нет папки `vendor/`, нужно установить PHP-зависимости:

```bash
composer install
```

Если команда `composer` не найдена, установи Composer или используй окружение, где он уже есть.

## Frontend

Стили уже собраны в:

```text
public/css/app.css
```

Поэтому `npm install` не нужен для обычного запуска.

Если нужно пересобрать frontend:

```bash
npm install
npm run dev
```

## Основные страницы

- `/` - главная страница;
- `/register` - регистрация;
- `/login` - вход;
- `/logout` - выход;
- `/quiz` - старт или продолжение quiz;
- `/profile` - профиль текущего пользователя;
- `/leaderboard` - таблица лидеров.

## Частые проблемы

### Connection refused

Ошибка вида:

```text
SQLSTATE[HY000] [2002] Connection refused
```

означает, что Laravel пытается подключиться к MySQL. Для простого запуска используй SQLite-настройки из этого README.

### Address already in use

Сообщение:

```text
Failed to listen on 127.0.0.1:8000
```

значит, что порт `8000` занят. Laravel обычно сам выбирает `8001`. Просто открой URL, который показан в терминале.

### PHP 8.5 и Laravel 8

Проект содержит небольшой compatibility fix для новых PHP-версий, чтобы старые Laravel 8 зависимости не падали на deprecated warning во время запуска.

## Тесты

```bash
php artisan test
```
