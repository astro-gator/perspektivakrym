<div class="col-12" style="padding-bottom: 5px">
    <button type="button" class="btn btn-outline-success sync-1s">Синхронизировать</button>
    <button type="button" class="btn btn-outline-primary add-fact-pay">Добавить фактический платеж</button>
{{--    <button type="button" class="btn btn-outline-info get-pdf-land">Основной график (ДДУ, земля, доп. лот) - скачать</button>--}}
{{--    <button type="button" class="btn btn-outline-info get-pdf-contract">График по подряду (скачать)</button>--}}
</div>
<div class="col-12">
    <div class="table-responsive">
        <table class="table table-sm table-striped sortable-table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Тип платежа</th>
                <th scope="col">Номер договора</th>
                <th scope="col">Сумма платежа</th>
                <th scope="col">Дата платежа</th>
                <th scope="col">Комментарий</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            @php $i = 1; @endphp
            @foreach($viewData['factPayments'] as $fp)
                <tr>
                    <td>{{ $i }}</td>
                    <td>
                        {{ $fp->type }}
                    </td>
                    <td>{{ $fp->doc_number }}</td>
                    <td>
                        {{ number_format($fp->amount, 2, '.', ' ') }}
                    </td>
                    <td

                    >
                        {{ date('d.m.Y', strtotime($fp->date)) }}
                    </td>
                    <td>{{ $fp->note }}</td>
                    <td>
                        @if ($fp->type === 'manual')
                            <button class="btn btn-secondary edit-fact-pay"
                                    data-id="{{ $fp->id }}"
                                    data-docnumber = "{{ $fp->doc_number }}"
                                    data-amount = "{{ $fp->amount }}"
                                    data-note = "{{ $fp->note }}"
                                    data-date = "{{ date('d.m.Y', strtotime($fp->date)) }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                </svg>
                            </button>
                            <button class="btn btn-danger delete-fact-pay"
                                    data-id="{{ $fp->id }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                            </button>
                        @endif
                    </td>
                </tr>
                @php $i++ @endphp
            @endforeach
            </tbody>
        </table>
    </div>
</div>
