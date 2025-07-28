#!/bin/bash

# Скрипт для конвертации документации в Word
# Автор: AI Assistant
# Дата: $(date)

echo "=== Конвертация документации в Word ==="

# Проверяем наличие pandoc
if command -v pandoc &> /dev/null; then
    echo "✓ Pandoc найден"
    
    # Проверяем наличие исходного файла
    if [ -f "Документация_расчета_графика_платежей_Word.md" ]; then
        echo "✓ Исходный файл найден"
        
        # Конвертируем в Word
        echo "Конвертация..."
        pandoc "Документация_расчета_графика_платежей_Word.md" \
            -o "Документация_расчета_графика_платежей.docx" \
            --toc \
            --number-sections \
            --top-level-division=chapter \
            --from markdown \
            --to docx
        
        if [ $? -eq 0 ]; then
            echo "✓ Файл успешно создан: Документация_расчета_графика_платежей.docx"
            echo ""
            echo "Рекомендации по форматированию в Word:"
            echo "1. Проверьте заголовки и их уровни"
            echo "2. Настройте стили таблиц"
            echo "3. Отформатируйте блоки кода"
            echo "4. Обновите оглавление"
        else
            echo "✗ Ошибка при конвертации"
            exit 1
        fi
    else
        echo "✗ Файл 'Документация_расчета_графика_платежей_Word.md' не найден"
        echo "Убедитесь, что файл находится в текущей директории"
        exit 1
    fi
else
    echo "✗ Pandoc не установлен"
    echo ""
    echo "Установка Pandoc:"
    echo ""
    echo "Ubuntu/Debian:"
    echo "  sudo apt-get install pandoc"
    echo ""
    echo "macOS:"
    echo "  brew install pandoc"
    echo ""
    echo "Windows:"
    echo "  Скачайте с https://pandoc.org/installing.html"
    echo ""
    echo "Альтернативно, используйте онлайн конвертеры:"
    echo "- https://dillinger.io/"
    echo "- https://stackedit.io/"
    echo "- https://word.aippt.com/markdown-to-word"
    exit 1
fi

echo ""
echo "=== Конвертация завершена ===" 