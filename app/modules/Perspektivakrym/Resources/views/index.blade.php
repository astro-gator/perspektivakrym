@extends('perspektivakrym::layouts.master')

@section('content')
    <h2>Рассрочка</h2>
    @if(isset($error))
        <div class="alert alert-danger" role="alert">
            {{ $error }}
        </div>
    @endif

    @if(isset($viewData))
        <input type="hidden" id="main_doc_number" value="{{ $viewData['resultMainPayments'][0]['doc_number'] ?? '' }}">
        <input type="hidden" id="contract_doc_number" value="{{ $viewData['resultContractorPayments'][0]['doc_number'] ?? '' }}">
        <div class="row">
            <div class="col-12 table-responsive">
                @if( count($viewData['payments']) !== 0)
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="graph" data-toggle="tab" href="#graph-tab" role="tab">График</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="payments" data-toggle="tab" href="#payments-tab" role="tab" >Платежи</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="fields" data-toggle="tab" href="#fields-tab" role="tab">Поля расчета</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Tab График --}}
                        <div class="tab-pane active" id="graph-tab" role="tabpanel" aria-labelledby="home-tab">
                            <h2>График</h2>
                            @include('perspektivakrym::_partical.graph')
                        </div>
                        {{-- Tab платежи--}}
                        <div class="tab-pane" id="payments-tab" role="tabpanel" aria-labelledby="home-tab">
                            <h2>Фактические платежи</h2>
                            @include('perspektivakrym::_partical.payments')
                        </div>
                        {{-- Tab поля расчета --}}
                        <div class="tab-pane" id="fields-tab" role="tabpanel" aria-labelledby="fields-tab">
                            <h2>Поля расчета рассрочки</h2>
                            
                            @if(isset($viewData['dealFields']) && count($viewData['dealFields']) > 0)
                                {{-- Основные поля сделки --}}
                                <h4>Основные поля сделки</h4>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Название поля</th>
                                                        <th>Код поля</th>
                                                        <th>Значение</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($viewData['dealFields'] as $field)
                                                        <tr>
                                                            <td>{{ $field['name'] }}</td>
                                                            <td><code>{{ $field['code'] }}</code></td>
                                                            <td>
                                                                @if(isset($field['description']))
                                                                    {{ $field['description'] }}
                                                                @elseif(is_numeric($field['value']))
                                                                    {{ number_format($field['value'], 0, '.', ' ') }}
                                                                @else
                                                                    {{ $field['value'] ?: 'Не заполнено' }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($viewData['calculatedFields']) && count($viewData['calculatedFields']) > 0)
                                {{-- Вычисленные значения --}}
                                <h4>Вычисленные значения</h4>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Название поля</th>
                                                        <th>Формула</th>
                                                        <th>Значение</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($viewData['calculatedFields'] as $field)
                                                        <tr>
                                                            <td>{{ $field['name'] }}</td>
                                                            <td><code>{{ $field['code'] }}</code></td>
                                                            <td>
                                                                @if(is_numeric($field['value']))
                                                                    {{ number_format($field['value'], 0, '.', ' ') }}
                                                                @else
                                                                    {{ $field['value'] ?: 'Не заполнено' }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="graph" data-toggle="tab" href="#graph-tab" role="tab">График</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="payments" data-toggle="tab" href="#payments-tab" role="tab" >Платежи</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="fields" data-toggle="tab" href="#fields-tab" role="tab">Поля расчета</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        {{-- Tab График --}}
                        <div class="tab-pane active" id="graph-tab" role="tabpanel" aria-labelledby="home-tab">
                            <h2>График</h2>
                            <p>По сделке данных нет</p>
                            <form action="{{ route('perspektivakrym_perspektivakrym_calculatePayments') }}" method="POST">
                                @csrf
                                <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                                <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                                <button type="submit" class="btn btn-outline-success">Рассчитать</button>
                            </form>
                        </div>
                        {{-- Tab платежи --}}
                        <div class="tab-pane" id="payments-tab" role="tabpanel" aria-labelledby="home-tab">
                            <h2>Фактические платежи</h2>
                        </div>
                        {{-- Tab поля расчета --}}
                        <div class="tab-pane" id="fields-tab" role="tabpanel" aria-labelledby="fields-tab">
                            <h2>Поля расчета рассрочки</h2>
                            
                            @if(isset($viewData['dealFields']) && count($viewData['dealFields']) > 0)
                                {{-- Основные поля сделки --}}
                                <h4>Основные поля сделки</h4>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Название поля</th>
                                                        <th>Код поля</th>
                                                        <th>Значение</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($viewData['dealFields'] as $field)
                                                        <tr>
                                                            <td>{{ $field['name'] }}</td>
                                                            <td><code>{{ $field['code'] }}</code></td>
                                                            <td>
                                                                @if(isset($field['description']))
                                                                    {{ $field['description'] }}
                                                                @elseif(is_numeric($field['value']))
                                                                    {{ number_format($field['value'], 0, '.', ' ') }}
                                                                @else
                                                                    {{ $field['value'] ?: 'Не заполнено' }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($viewData['calculatedFields']) && count($viewData['calculatedFields']) > 0)
                                {{-- Вычисленные значения --}}
                                <h4>Вычисленные значения</h4>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Название поля</th>
                                                        <th>Формула</th>
                                                        <th>Значение</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($viewData['calculatedFields'] as $field)
                                                        <tr>
                                                            <td>{{ $field['name'] }}</td>
                                                            <td><code>{{ $field['code'] }}</code></td>
                                                            <td>
                                                                @if(is_numeric($field['value']))
                                                                    {{ number_format($field['value'], 0, '.', ' ') }}
                                                                @else
                                                                    {{ $field['value'] ?: 'Не заполнено' }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ----Modal plan date---- -->
        <div class="modal fade" id="plan-date" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Изменить плановую дату</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym-planDate') }}" name="plan-date">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <div class="form-group">
                                <label for="annex_number">Введите дату платежа</label>
                                <input type="text" class="form-control" value="" name="date">
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="is_text_date" name="is_text_date" value="1">
                                <label for="is_text_date">Дата в виде текста</label>
                            </div>
                            <div class="form-group">
                                <label for="type">Текст вместо даты</label>
                                <input type="text" class="form-control" value="" name="text_date">
                            </div>
                        </div>
                        <div class="modal-footer">
                            {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>--}}
                            <button type="submit" class="btn btn-primary" id="plan-date-save">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal изменить сумму планового платежа -->
        <div class="modal fade" id="plan-amount" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Редактировать сумму платежа</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym-planAmount') }}" name="fact-amount">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <div class="form-group">
                                <label for="amount">Введите сумму платежа</label>
                                <input type="number" min="0" class="form-control" value="" name="amount" autocomplete="off">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal генерации pdf contract---- -->
        <div class="modal fade" id="get-pdf-contract" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Скачать приложение</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_getpdf') }}">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="type" value="contract">
                        <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                        <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                        <input type="hidden" name="number_graph" value="0">
                        <div class="form-group">
                            <label for="annex_number">Введите № приложения</label>
                            <input type="text" class="form-control" value="" name="annex_number">
                        </div>
                        <div class="form-group">
                            <label for="entity">Выберите ЮЛ</label>
                            <select class="form-control" name="entity">
                                @if(isset($viewData['entities']))
                                    @foreach($viewData['entities'] as $key => $val)
                                        <option value="{{ $key }}">{{ $key }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
    {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>--}}
                        <button type="submit" class="btn btn-primary">Скачать</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal генерации pdf land---- -->
        <div class="modal fade" id="get-pdf-land" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Скачать приложение</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_getpdf') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="type" value="main">
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                <label for="annex_number">Введите № приложения</label>
                                <input type="text" class="form-control" value="" name="annex_number">
                            </div>
                            <div class="form-group">
                                <label for="entity">Выберите ЮЛ</label>
                                <select class="form-control" name="entity">
                                    @if(isset($viewData['entities']))
                                        @foreach($viewData['entities'] as $key => $val)
                                            <option value="{{ $key }}">{{ $key }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            {{--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>--}}
                            <button type="submit" class="btn btn-primary">Скачать</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal подтверждение перерасчета графика---- -->
        <div class="modal fade" id="recalculate" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Пересчет графика</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_recalculatePayments') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите пересчитать график?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal добавить плановый платеж---- -->
        <div class="modal fade" id="add-plan-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Добавить плановый платеж</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_addPlanPayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="type">Введите тип договора</label>
                                    <select class="form-control" name="type">
                                        <option value="main">Основной</option>
                                        <option value="contract">Подряд</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите тип платежа</label>
                                    <select class="form-control" name="pay_type">
                                        <option value="down_payment">ПВ</option>
                                        <option value="regular_payment">Регулярный платеж</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите номер договора</label>
                                    <input type="text" class="form-control" value="" name="doc_number">
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите сумму платежа</label>
                                    <input type="number" class="form-control" value="" name="amount">
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите дату платежа</label>
                                    <input type="text" class="form-control add-plan-pay__date" value="" name="date" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label for="type">Комментарий</label>
                                    <input type="text" class="form-control" value="" name="note">
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="is_text_date" name="is_text_date" value="1">
                                    <label for="is_text_date">Дата в виде текста</label>
                                </div>
                                <div class="form-group">
                                    <label for="type">Текст вместо даты</label>
                                    <input type="text" class="form-control" value="" name="text_date">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
{{--                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>--}}
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal заморозить платеж---- -->
        <div class="modal fade" id="block-plan-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Блокировать платеж</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_block') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <div class="form-group">
                                Вы действительно хотите заблокировать платеж?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal разморозить платеж---- -->
        <div class="modal fade" id="unblock-plan-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Разблокировать платеж</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_unblock') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <div class="form-group">
                                Вы действительно хотите разблокировать платеж?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal синхронизировать с 1С---- -->
        <div class="modal fade" id="sync-1s" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Синхронизировать с 1С</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_syncWith1S') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <div class="form-group">
                                Вы действительно хотите синхронизировать платежи с 1С?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal добавить фактический платеж---- -->
        <div class="modal fade" id="add-fact-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Добавить фактический платеж</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_addFactPayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <div class="form-group">
                                <div class="form-group">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="doc_number_for_fact" id="main_doc_number_for_fact" value="{{ $viewData['mainContract'] ?? '' }}" checked>
                                        <label class="form-check-label" for="main_doc_number_for_fact">{{ $viewData['mainContract'] ?? '' }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="doc_number_for_fact" id="contract_doc_number_for_fact" value="{{ $viewData['contractorContract'] ?? '' }}">
                                        <label class="form-check-label" for="contract_doc_number_for_fact">{{ $viewData['contractorContract'] ?? '' }}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите номер договора</label>
                                    <input type="text" class="form-control" value="{{ $viewData['resultMainPayments'][0]['doc_number'] ?? ''}}" name="doc_number">
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите сумму платежа</label>
                                    <input type="number" class="form-control" value="" name="amount">
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите дату платежа</label>
                                    <input type="text" class="form-control add-plan-pay__date" value="" name="date" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label for="type">Комментарий</label>
                                    <input type="text" class="form-control" value="" name="note">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            {{--                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>--}}
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal редактировать фактический платеж---- -->
        <div class="modal fade" id="edit-fact-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Добавить фактический платеж</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_editFactPayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="type">Введите номер договора</label>
                                    <input type="text" class="form-control" value="" name="doc_number">
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите сумму платежа</label>
                                    <input type="number" class="form-control" value="" name="amount">
                                </div>
                                <div class="form-group">
                                    <label for="type">Введите дату платежа</label>
                                    <input type="text" class="form-control add-plan-pay__date" value="" name="date" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label for="type">Комментарий</label>
                                    <input type="text" class="form-control" value="" name="note">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            {{--                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>--}}
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal удаление фактический платеж---- -->
        <div class="modal fade" id="delete-fact-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Удаление фактического платежа</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_deleteFactPayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <div class="form-group">
                                Вы действительно хотите удалить платеж?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal удаление планового платежа---- -->
        <div class="modal fade" id="delete-plan-pay" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Удаление планового платежа</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_deletePlanPayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <div class="form-group">
                                Вы действительно хотите удалить платеж?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal заморозить основные платежи ---- -->
        <div class="modal fade" id="freeze_main" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Заморозить платежи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_freezePayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="type" value="main">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите заморозить основные платежи?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal разморозить основные платежи ---- -->
        <div class="modal fade" id="unfreeze_main" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Заморозить платежи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_unfreezePayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="type" value="main">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите разморозить основные платежи?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal заморозить платежи по подряду ---- -->
        <div class="modal fade" id="freeze_contract" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Заморозить платежи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_freezePayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="type" value="contract">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите заморозить платежи по подряду?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal разморозить платежи по подряду ---- -->
        <div class="modal fade" id="unfreeze_contract" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Заморозить платежи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_unfreezePayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="type" value="contract">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите разморозить платежи по подряду?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal заморозить все платежи ---- -->
        <div class="modal fade" id="freeze_all" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Заморозить платежи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_freezePayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="type" value="all">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите заморозить все платежи?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ----Modal разморозить все платежи ---- -->
        <div class="modal fade" id="unfreeze_all" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Заморозить платежи</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_unfreezePayment') }}">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="type" value="all">
                            <input type="hidden" name="number_graph" value="0">
                            <div class="form-group">
                                Вы действительно хотите разморозить все платежи?
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Нет</button>
                            <button type="submit" class="btn btn-primary">Да</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal изменить комментарий планового платежа -->
        <div class="modal fade" id="edit-comment-plan-payment" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Редактировать комментарий</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('perspektivakrym_perspektivakrym_edit_comment') }}" name="fact-amount">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="deal_id" value="{{ $viewData['dealId'] }}">
                            <input type="hidden" name="operation_id" value="">
                            <input type="hidden" name="auth" value="{{ $viewData['auth'] }}">
                            <input type="hidden" name="payment_type" value="plan">
                            <div class="form-group">
                                <label for="type">Комментарий</label>
                                <textarea type="text" class="form-control" name="note"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop
