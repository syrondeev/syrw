# 📚 SYRW Widget Development - Complete Notes

**تاریخ:** 2024-12-19  
**وضعیت:** آماده برای توسعه ویجت Post Card

---

## 🎯 ساختار کلی پروژه

### 1. ساختار فایل ویجت (3 فایل):
```
widgets/
  widget-name/
    ├── module.php      // کلاس اصلی ویجت
    ├── pipeline.php    // تبدیل settings به array منظم
    └── template.php    // رندر HTML
```

### 2. نکات کلیدی از sample-widget:

#### **module.php:**
- از `widget_base` extend می‌کنه
- متد `define_controls()` برای تعریف کنترل‌ها
- از `$this->namer` برای نام‌گذاری استفاده می‌کنه
- از `collect` برای کار با array استفاده می‌کنه
- کنترل‌ها با prefix و group سازماندهی می‌شن

#### **pipeline.php:**
- از `pipeline_core` extend می‌کنه  
- متد `get_configs(collect $configs): collect`
- داده‌های کنترل‌ها از `$this->configs->get("layout", [])` گرفته می‌شه
- داده‌ها به صورت nested array منظم می‌شن
- از `collect` برای map, filter, chunk استفاده می‌شه

#### **template.php:**
- از `template_core` extend می‌کنه
- متد `render(collect $configs, widgetable $module): void`
- از `$this->element->create()` برای ساخت HTML استفاده می‌شه
- از BEM naming با پیشوند `syron` استفاده می‌شه
- از `Icons_Manager::render_icon()` برای آیکون‌ها

---

## 🔧 کلاس‌های پایه

### **collect (مشابه Laravel Collection):**
- `get($key, $default)` - دریافت مقدار
- `put($key, $value)` - ست کردن مقدار
- `map(closure)` - تبدیل آیتم‌ها
- `filter(closure)` - فیلتر کردن
- `chunk($size)` - تقسیم به بخش‌ها
- `walk(closure)` - loop روی آیتم‌ها
- `match($key, $value)` - چک کردن برابری
- `is_empty_key($key)` - چک کردن خالی بودن
- `is_not_empty_key($key)` - چک کردن پر بودن
- `to_array()` - تبدیل به array
- `all()` - گرفتن همه آیتم‌ها

### **namer (برای نام‌گذاری کنترل‌ها):**
- `prefix($name)` - ست کردن prefix
- `group($name)` - ست کردن group
- `get($control_name)` - دریافت نام کامل
- `reset()` - ریست کردن prefix/group

### **element (برای ساخت HTML):**
- `create($tag, $attributes)` - ساخت المان
- `render(closure)` - رندر کردن با محتوا
- `classes($classes)` - اضافه کردن class
- `attributes($attributes)` - اضافه کردن attribute

---

## 📋 استانداردهای نام‌گذاری

### **BEM CSS Classes:**
```css
.syron-post-card                    /* Block */
.syron-post-card__header            /* Element */
.syron-post-card__header--featured  /* Modifier */
```

### **Control Naming:**
```php
// با استفاده از namer:
$this->namer->prefix("layout");           // prefix: layout_
$this->namer->group("title");              // group: title_
$this->namer->get("text");                 // result: layout_title_text

// بدون group:
$this->namer->get("columns");              // result: layout_columns
```

### **Config Structure در pipeline:**
```php
$configs->put("title", [
    "text" => $controls->get("title_text", ""),
    "html_tag" => $controls->get("title_html_tag", "h4"),
    "visibility" => $controls->get("title_visibility", "visible"),
]);
```

---

## 🎨 الگوهای رایج کدنویسی

### **1. Conditional Rendering:**
```php
if ($config->match("visibility", "visible")) {
    // render element
}
```

### **2. Loop با walk:**
```php
$items->walk(function ($item): void {
    // render each item
});
```

### **3. Nested Elements:**
```php
$el_wrap = $this->element->create("div", ["class" => ["wrapper"]]);
$el_wrap->render(function () use ($config): void {
    // nested content
});
```

### **4. Icon Rendering:**
```php
Icons_Manager::render_icon(
    $icon_config->to_array()->all(),
    ["class" => ["icon-class"], "aria-hidden" => "true"],
    "span"
);
```

---

## 📝 نمونه کد کامل از feature2

### **Controls Pattern:**
```php
$this->add_control(
    $this->namer->get("visibility"),
    [
        "label" => esc_html__("Visibility", $this->text_domain),
        "type" => Controls_Manager::SELECT,
        "options" => [
            "hidden" => esc_html__("Hidden", $this->text_domain),
            "visible" => esc_html__("Visible", $this->text_domain),
        ],
        "default" => "visible",
    ]
);
```

### **Pipeline Pattern:**
```php
$configs->put("element", [
    "text" => $controls->get("element_text", ""),
    "visibility" => $controls->get("element_visibility", "visible"),
]);
```

### **Template Pattern:**
```php
$el_wrap = $this->element->create("div", ["class" => ["element"]]);
$el_wrap->render(function () use ($config): void {
    echo $config->get("text", "");
});
```

---

## 🚀 قوانین توسعه Post Card

### **1. نام‌گذاری:**
- BEM: `syron-post-card__*`
- Controls: با namer
- متن‌ها: انگلیسی در کد، ترجمه از فایل جداگانه

### **2. کنترل‌ها:**
- همیشه `SELECT` یا `SELECT2` برای گزینه‌ها
- هیچ SWITCHER استفاده نمی‌شه
- همه visibility ها با SELECT

### **3. Data Flow:**
```
Controls (module.php) 
  ↓
Process (pipeline.php) 
  ↓
Render (template.php)
```

### **4. Required Features:**
- Query Builder (Post Type, Taxonomy, Author, Date)
- Layout Options (Grid, Masonry, List, Carousel)
- Card Elements (Image, Title, Excerpt, Meta)
- Pagination (Numbers, Load More, Infinite Scroll)
- Styling (BEM + Tailwind utility classes)

---

## ✅ Checklist قبل از شروع کدنویسی:

- [x] فایل‌های نمونه خوانده شد
- [x] ساختار درک شد
- [x] الگوهای کدنویسی شناسایی شد
- [ ] کلاس‌های helper آماده شد
- [ ] فایل ترجمه آماده شد
- [ ] ویجت Post Card ساخته شد

---

## 📌 یادداشت‌های اضافی:

- همه array ها باید با collect کار کنن
- از `to_self()` برای تبدیل array به collect
- از `all()` برای تبدیل collect به array
- متن‌های داخل template رو escape نکنید (قبلاً escape شده)
- از `sprintf()` برای format کردن استفاده کنید
