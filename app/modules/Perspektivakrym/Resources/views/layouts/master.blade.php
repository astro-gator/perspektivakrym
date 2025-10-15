<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Калькулятор рассрочки</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://api.bitrix24.com/api/v1"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

        <style>
            /* Стили для заблокированных элементов */
            .blocked {
                opacity: 0.5;
            }
            
            /* Стили для заблокированных кнопок удаления */
            .delete-plan-pay.blocked,
            .delete-fact-pay.blocked {
                opacity: 0.5;
                cursor: not-allowed;
            }
            
            .delete-plan-pay.blocked:hover,
            .delete-fact-pay.blocked:hover {
                opacity: 0.5;
            }
        </style>

       {{-- Laravel Mix - CSS File --}}
       {{-- <link rel="stylesheet" href="{{ mix('css/perspektivakrym.css') }}"> --}}

    </head>
    <body>
        <div style="padding: 10px">
            @yield('content')
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>
        {{-- Локальные JS файлы --}}
        {{-- <script src="{{ asset('js/datepicker_ru.js') }}"></script> --}}
        {{-- <script src="{{ asset('js/perspektivakrym/app.js?v=1.3.2') }}"></script> --}}
        
        <script>
        $(document).ready(function() {
            // Обработчик для кнопки "Пересчитать"
            $('.recalculate').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно подтверждения
                $('#recalculate').modal('show');
            });
            
            // Обработчик для кнопки "Добавить плановый платеж"
            $('.add-plan-pay').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно добавления платежа
                $('#add-plan-pay').modal('show');
            });
            
            // Обработчик для кнопки "Заморозить все"
            $('.freeze-all').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно заморозки всех платежей
                $('#freeze_all').modal('show');
            });
            
            // Обработчик для кнопки "Разморозить все"
            $('.unfreeze-all').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно разморозки всех платежей
                $('#unfreeze_all').modal('show');
            });
            
            // Обработчик для кнопки "Заморозить основные"
            $('.freeze-main').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно заморозки основных платежей
                $('#freeze_main').modal('show');
            });
            
            // Обработчик для кнопки "Разморозить основные"
            $('.unfreeze-main').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно разморозки основных платежей
                $('#unfreeze_main').modal('show');
            });
            
            // Обработчик для кнопки "Заморозить подряд"
            $('.freeze-contract').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно заморозки платежей по подряду
                $('#freeze_contract').modal('show');
            });
            
            // Обработчик для кнопки "Разморозить подряд"
            $('.unfreeze-contract').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно разморозки платежей по подряду
                $('#unfreeze_contract').modal('show');
            });
            
            // Обработчик для кнопки "Основной график"
            $('.get-pdf-land').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно для PDF
                $('#get-pdf-land').modal('show');
            });
            
            // Обработчик для кнопки "График по подряду"
            $('.get-pdf-contract').on('click', function() {
                var graphNumber = $(this).data('graph');
                var dealId = $('input[name="deal_id"]').val();
                var auth = $('input[name="auth"]').val();
                
                // Показываем модальное окно для PDF
                $('#get-pdf-contract').modal('show');
            });
            
            // Обработчик для кнопки удаления планового платежа
            $(document).on('click', '.delete-plan-pay', function(e) {
                e.preventDefault();
                
                // Проверяем, не заблокирован ли платеж
                if ($(this).hasClass('blocked')) {
                    alert('Платеж удалить нельзя, платеж заморожен');
                    return false;
                }
                
                var paymentId = $(this).data('id');
                
                // Устанавливаем ID платежа в скрытое поле модального окна
                $('#delete-plan-pay input[name="operation_id"]').val(paymentId);
                
                // Показываем модальное окно подтверждения удаления
                $('#delete-plan-pay').modal('show');
            });
            
            // Обработчик для кнопки удаления фактического платежа
            $(document).on('click', '.delete-fact-pay', function(e) {
                e.preventDefault();
                
                // Проверяем, не заблокирован ли платеж
                if ($(this).hasClass('blocked')) {
                    alert('Платеж удалить нельзя, платеж заморожен');
                    return false;
                }
                
                var paymentId = $(this).data('id');
                
                // Устанавливаем ID платежа в скрытое поле модального окна
                $('#delete-fact-pay input[name="operation_id"]').val(paymentId);
                
                // Показываем модальное окно подтверждения удаления
                $('#delete-fact-pay').modal('show');
            });
        });
        </script>
    </body>
</html>
