# Документация по системе расчета графиков платежей

## Описание

Данная папка содержит полную документацию по методу `calculatePayments` системы расчета графиков платежей для Bitrix24.

## Структура файлов

### 📄 Основные файлы документации

| Файл | Описание | Формат |
|------|----------|--------|
| `Документация_расчета_графика_платежей.md` | Базовая документация | Markdown |
| `Документация_расчета_графика_платежей_Word.md` | Оптимизированная версия для Word | Markdown |
| `Документация_расчета_графика_платежей.docx` | Готовый файл Word | Word |

### 🛠️ Инструменты

| Файл | Описание | Тип |
|------|----------|-----|
| `convert_to_word.sh` | Автоматический скрипт конвертации | Bash |
| `Инструкция_конвертации_в_Word.md` | Инструкция по конвертации | Markdown |

## Содержание документации

### 1. Входные данные
- Параметры из Bitrix24
- Финансовые данные
- Даты и временные параметры
- Частоты платежей
- Номера договоров

### 2. Алгоритм расчета
- Проверки перед расчетом
- Расчет количества платежей
- Расчет по основному договору
- Расчет по подряду
- Расчет дат платежей

### 3. Результат расчета
- Структура данных платежа
- Типы платежей
- Обработка ошибок

### 4. Примеры
- Конкретные числовые примеры
- Особенности алгоритма

## Использование

### Быстрый старт

1. **Открыть готовый файл Word:**
   ```bash
   # Откройте файл в любом редакторе Word
   Документация_расчета_графика_платежей.docx
   ```

2. **Конвертировать заново:**
   ```bash
   cd docs
   ./convert_to_word.sh
   ```

### Ручная конвертация

Если у вас нет Pandoc, используйте онлайн конвертеры:
- [Dillinger.io](https://dillinger.io/)
- [StackEdit](https://stackedit.io/)
- [Markdown to Word](https://word.aippt.com/markdown-to-word)

## Структура документации

```
docs/
├── README.md                                    # Этот файл
├── Документация_расчета_графика_платежей.md     # Базовая документация
├── Документация_расчета_графика_платежей_Word.md # Для Word
├── Документация_расчета_графика_платежей.docx   # Готовый Word
├── Инструкция_конвертации_в_Word.md            # Инструкция
└── convert_to_word.sh                          # Скрипт конвертации
```

## Требования

### Для конвертации:
- Pandoc (устанавливается автоматически скриптом)
- Bash shell

### Для просмотра:
- Любой текстовый редактор (для .md файлов)
- Microsoft Word или совместимый редактор (для .docx)

## Обновление документации

При изменении кода метода `calculatePayments`:

1. Обновите соответствующие разделы в Markdown файлах
2. Запустите скрипт конвертации:
   ```bash
   cd docs
   ./convert_to_word.sh
   ```
3. Проверьте результат в Word файле

## Контакты

Для вопросов по документации обращайтесь к разработчику системы.

---

**Последнее обновление:** $(date)
**Версия документации:** 1.0 