# Звіт до лабораторної роботи №14
**Тема:** Архітектура MVC, Front Controller, маршрутизація та рендеринг представлень у PHP

---

## 1. Фрагменти коду (`Router.php` та `BaseController.php`)

### Метод `dispatch()` у `Router.php`

```php
    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $method = strtoupper($method);

        if (!isset($this->routes[$method][$path])) {
            $this->handleNotFound($method, $path);
            return;
        }

        [$controllerClass, $actionMethod] = $this->routes[$method][$path];

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Клас контролера '{$controllerClass}' не знайдено.");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionMethod)) {
            throw new RuntimeException("Метод '{$actionMethod}' не знайдено в класі '{$controllerClass}'.");
        }

        $controller->$actionMethod();
    }
```

### Метод `render()` у `BaseController.php`

```php
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewFile = __DIR__ . '/../../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("Файл представлення не знайдено: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../../views/layouts/main.php';

        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Файл макета не знайдено: {$layoutFile}");
        }

        require $layoutFile;
    }
```

---

## 2. Скриншоти

### Структура каталогів проєкту в IDE (`public/`, `src/`, `views/`)

![Структура каталогів проєкту в IDE](скрин/Screen1.png)

### Список завдань за маршрутом `/` після авторизації

![Список завдань після авторизації](скрин/Screen2.png)

### Сторінка 404 при переході за неіснуючим маршрутом

![Сторінка 404](скрин/Screen3.png)

---

## 3. Відповіді на контрольні запитання

### 1. Призначення складових MVC (Model–View–Controller) та їхня взаємодія під час HTTP-запиту

- **Model (Модель)** — відповідає за дані, бізнес-логіку та роботу з БД/сховищем (у проєкті — `Task`, `TaskRepository`).
- **View (Представлення)** — відповідає за візуальне відображення даних користувачу (HTML-шаблони в `views/`).
- **Controller (Контролер)** — обробляє запит, викликає модель/репозиторій і передає дані у представлення (`render()`) або виконує перенаправлення (`redirect`).

**Взаємодія:** HTTP-запит надходить до вхідної точки → `Router` визначає контролер і метод → контролер звертається до моделі/репозиторію → рендериться потрібний шаблон через `View` або виконується перенаправлення.

### 2. Концепція Front Controller та її переваги над набором окремих PHP-файлів

**Front Controller** — це єдина точка входу в застосунок (`public/index.php`), через яку проходять усі запити і далі розподіляються маршрутизатором.

**Переваги:**
- **Єдина точка налаштування:** сесії, автозавантаження, конфіги та обробка помилок підключаються в одному місці.
- **Централізований контроль доступу:** зручніше перевіряти авторизацію та фільтрувати запити.
- **Гнучка маршрутизація:** URL не прив'язані напряму до назв файлів на диску.

### 3. Чому корінь веб-сервера (`DocumentRoot`) має вказувати на `public/`, а `src/` та `views/` бути поза ним

У `public/` лежать лише публічні файли (`index.php`, стилі, скрипти), а в `src/` і `views/` — вихідний код, логіка, шаблони та запити до БД. Винесення `src/` і `views/` за межі `public/` не дає відкрити їх напряму через браузер і захищає вихідний код при помилках конфігурації сервера.

### 4. Як працює `Router` та навіщо викликати `parse_url($uri, PHP_URL_PATH)` перед пошуком маршруту

`Router` зберігає таблицю маршрутів за HTTP-методами (`GET`, `POST`) і шляхами. У `dispatch()` він нормалізує шлях, прибирає зайвий слеш наприкінці (крім `/`), шукає відповідний маршрут, створює контролер і викликає його метод (або показує сторінку `404`).

`parse_url($uri, PHP_URL_PATH)` потрібен, щоб **відкинути query-параметри** (наприклад, `?page=2`) і залишити тільки шлях маршруту — інакше маршрут із параметрами запиту не збігся б із ключем у масиві маршрутів.  ### 5. Механізм рендерингу через `extract($data)`, `ob_start()` / `ob_get_clean()` та «чистота» шаблонів

- `extract($data)` створює локальні змінні з ключів масиву `$data`, щоб у шаблоні можна було звертатися до них напряму.
- `ob_start()` вмикає буферизацію виводу — HTML із шаблону спочатку збирається в буфер, а не відправляється одразу в браузер.
- `ob_get_clean()` забирає зібраний HTML у змінну `$content` і очищує буфер, щоб потім підставити `$content` усередину загального макета (`views/layouts/main.php`).

Шаблони залишаються «чистими» від бізнес-логіки, бо контролер готує дані заздалегідь, а шаблон лише виводить їх у потрібній розмітці без SQL-запитів та складної логіки.