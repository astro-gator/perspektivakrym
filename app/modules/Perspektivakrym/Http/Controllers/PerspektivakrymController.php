<?php
/** @noinspection TypeUnsafeComparisonInspection */

namespace Modules\Perspektivakrym\Http\Controllers;

use Barryvdh\DomPDF\Facade;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Perspektivakrym\Entities\B24;
use Modules\Perspektivakrym\Entities\BDeal;
use Modules\Perspektivakrym\Entities\FactPayment;
use Modules\Perspektivakrym\Entities\OldDoc;
use Modules\Perspektivakrym\Entities\Payment;
use Modules\Perspektivakrym\Entities\PlanPayment;

use Barryvdh\DomPDF\PDF;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PerspektivakrymController extends Controller
{
    protected $mPayments;
//    protected $mOldDoc;
    protected $b24;
    protected $mPlanPayment;
    protected $mFactPayment;


    public function __construct(B24 $b24, Payment $payment, PlanPayment $planPayment, FactPayment $factPayment)
    {
        $this->b24 = $b24;
        $this->mPayments = $payment;
        $this->mPlanPayment = $planPayment;
        $this->mFactPayment = $factPayment;
//        $this->mOldDoc = $oldDoc;
    }

    /**
     * Главная страница рассрочки
     * @param Request $request
     * @param array $data
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, $data = [])
    {
        try {
            if (count($data) == 0) {
                $placementOptions = $request->get('PLACEMENT_OPTIONS', null);

                $auth = $request->get('AUTH_ID', null);

                if (!$this->checkApp($auth)) {
                    throw new \DomainException('Приложение не авторизовано');
                }

                if (is_null($placementOptions)) {
                    throw new \DomainException('Нет данных по сделке');
                }

                $dataRequest = json_decode($placementOptions, true, 512, JSON_THROW_ON_ERROR);
                if (isset($dataRequest['ID'])) {
                    $dealId = $dataRequest['ID'];
                } else {
                    throw new \DomainException('Нет ID сделки');
                }
            } else {
                $dealId = $data['deal_id'];
                $auth = $data['auth'];
                if (isset($data['error'])) {
                    throw new \DomainException($data['error']);
                }
            }

            /**
             *  --------- Плановые платежи
             */

            $planPayments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('number_graph', 0)
                ->get();

            $planPayments2 = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('number_graph', 1)
                ->get();

            if (count($planPayments) == 0) {
                return view('perspektivakrym::index')
                    ->with(['viewData' => [
                        'payments' => [],
                        'dealId' => $dealId,
                        'auth' => $auth,
                    ]]);
            }

            $planMainDownPayments = $planPayments
                ->where('type', $this->mPlanPayment::MAIN)
                ->where('pay_type', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('number_graph', 0)
                ->sortBy('date')
                ->toArray();

            $planMainDownPayments2 = $planPayments2
                ->where('type', $this->mPlanPayment::MAIN)
                ->where('pay_type', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('number_graph', 1)
                ->sortBy('date')
                ->toArray();

            $planMainRegularPayments = $planPayments
                ->where('type', $this->mPlanPayment::MAIN)
                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('number_graph', 0)
                ->sortBy('date')
                ->toArray();

            $planMainRegularPayments2 = $planPayments2
                ->where('type', $this->mPlanPayment::MAIN)
                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('number_graph', 1)
                ->sortBy('date')
                ->toArray();

            $planMainPayments = array_merge($planMainDownPayments, $planMainRegularPayments);

            $planMainPayments2 = array_merge($planMainDownPayments2, $planMainRegularPayments2);

            $amountMainPayments = 0;
            if (count($planMainPayments) != 0) {
                $docNumberMain = trim($planMainPayments[0]['doc_number']);
                $amountMainPayments = $this->mFactPayment
                    ->where('doc_number', 'LIKE', '%' . $docNumberMain . '%')
                    ->sum('amount');
            }

            $amountMainPayments2 = 0;
            if (count($planMainPayments2) != 0) {
                $docNumberMain = trim($planMainPayments2[0]['doc_number']);
                $amountMainPayments2 = $this->mFactPayment
                    ->where('doc_number', 'LIKE', '%' . $docNumberMain . '%')
                    ->sum('amount');
            }

            $resultMainPayments = [];
            $resultMainPayments2 = [];

            foreach ($planMainPayments as $key => $p) {

                $ost = $amountMainPayments - $p['amount'];

                if ($ost == 0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountMainPayments = $ost;
                }

                if ($ost > 0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountMainPayments = $ost;
                }

                if ($ost < 0) {

                    $diff = $this->getDiffDays($p['date']);
//                    $diff = 0;
                    $amountFact = $amountMainPayments;
                    $amountMainPayments = 0;
                }


                $resultMainPayments[$key]['id'] = $p['id'];
                $resultMainPayments[$key]['deal_id'] = $p['deal_id'];
                $resultMainPayments[$key]['type'] = $p['type'];
                $resultMainPayments[$key]['pay_type'] = $p['pay_type'];
                $resultMainPayments[$key]['doc_number'] = $p['doc_number'];
                $resultMainPayments[$key]['amount'] = $p['amount'];
                $resultMainPayments[$key]['date'] = $p['date'];
                $resultMainPayments[$key]['blocked'] = $p['blocked'];
                $resultMainPayments[$key]['note'] = $p['note'];
                $resultMainPayments[$key]['order'] = $p['order'];
                $resultMainPayments[$key]['amount_fact'] = $amountFact;
                $resultMainPayments[$key]['date_fact'] = null;
                $resultMainPayments[$key]['diff'] = $diff;
                $resultMainPayments[$key]['is_text_date'] = $p['is_text_date'];
                $resultMainPayments[$key]['text_date'] = $p['text_date'];
                $resultMainPayments[$key]['add_type'] = $p['add_type'];
            }

            foreach ($planMainPayments2 as $key => $p) {

                $ost = $amountMainPayments2 - $p['amount'];

                if ($ost == 0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountMainPayments2 = $ost;
                }

                if ($ost > 0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountMainPayments2 = $ost;
                }

                if ($ost < 0) {

                    $diff = $this->getDiffDays($p['date']);
//                    $diff = 0;
                    $amountFact = $amountMainPayments2;
                    $amountMainPayments2 = 0;
                }


                $resultMainPayments2[$key]['id'] = $p['id'];
                $resultMainPayments2[$key]['deal_id'] = $p['deal_id'];
                $resultMainPayments2[$key]['type'] = $p['type'];
                $resultMainPayments2[$key]['pay_type'] = $p['pay_type'];
                $resultMainPayments2[$key]['doc_number'] = $p['doc_number'];
                $resultMainPayments2[$key]['amount'] = $p['amount'];
                $resultMainPayments2[$key]['date'] = $p['date'];
                $resultMainPayments2[$key]['blocked'] = $p['blocked'];
                $resultMainPayments2[$key]['note'] = $p['note'];
                $resultMainPayments2[$key]['order'] = $p['order'];
                $resultMainPayments2[$key]['amount_fact'] = $amountFact;
                $resultMainPayments2[$key]['date_fact'] = null;
                $resultMainPayments2[$key]['diff'] = $diff;
                $resultMainPayments2[$key]['is_text_date'] = $p['is_text_date'];
                $resultMainPayments2[$key]['text_date'] = $p['text_date'];
                $resultMainPayments2[$key]['add_type'] = $p['add_type'];
            }

            $planContractorDownPayments = $planPayments
                ->where('type', $this->mPlanPayment::CONTRACT)
                ->where('pay_type', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('number_graph', 0)
                ->sortBy('date')
                ->toArray();

            $planContractorDownPayments2 = $planPayments2
                ->where('type', $this->mPlanPayment::CONTRACT)
                ->where('pay_type', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('number_graph', 1)
                ->sortBy('date')
                ->toArray();

            $planContractorRegularPayments = $planPayments
                ->where('type', $this->mPlanPayment::CONTRACT)
                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('number_graph', 0)
                ->sortBy('date')
                ->toArray();

            $planContractorRegularPayments2 = $planPayments2
                ->where('type', $this->mPlanPayment::CONTRACT)
                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('number_graph', 1)
                ->sortBy('date')
                ->toArray();

            $planContractorPayments = array_merge($planContractorDownPayments, $planContractorRegularPayments);
            $planContractorPayments2 = array_merge($planContractorDownPayments2, $planContractorRegularPayments2);

            $amountContractorPayments = 0;
            if (count($planContractorPayments) != 0) {
                $docNumberContractor = trim($planContractorPayments[0]['doc_number']);

                $amountContractorPayments = $this->mFactPayment
                    ->where('doc_number', 'LIKE', '%' . $docNumberContractor . '%')
                    ->sum('amount');
            }

            $amountContractorPayments2 = 0;
            if (count($planContractorPayments2) != 0) {
                $docNumberContractor = trim($planContractorPayments2[0]['doc_number']);

                $amountContractorPayments2 = $this->mFactPayment
                    ->where('doc_number', 'LIKE', '%' . $docNumberContractor . '%')
                    ->sum('amount');
            }

            $resultContractorPayments = [];

            foreach ($planContractorPayments as $key => $p) {

                $ost = $amountContractorPayments - $p['amount'];
                if ($ost == 0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountContractorPayments = $ost;
                }

                if ($ost >0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountContractorPayments = $ost;
                }

                if ($ost < 0) {
                    $diff = $this->getDiffDays($p['date']);
                    $amountFact = $amountContractorPayments;
                    $amountContractorPayments = 0;
                }

                $resultContractorPayments[$key]['id'] = $p['id'];
                $resultContractorPayments[$key]['deal_id'] = $p['deal_id'];
                $resultContractorPayments[$key]['type'] = $p['type'];
                $resultContractorPayments[$key]['pay_type'] = $p['pay_type'];
                $resultContractorPayments[$key]['doc_number'] = $p['doc_number'];
                $resultContractorPayments[$key]['amount'] = $p['amount'];
                $resultContractorPayments[$key]['date'] = $p['date'];
                $resultContractorPayments[$key]['blocked'] = $p['blocked'];
                $resultContractorPayments[$key]['note'] = $p['note'];
                $resultContractorPayments[$key]['order'] = $p['order'];
                $resultContractorPayments[$key]['amount_fact'] = $amountFact;
                $resultContractorPayments[$key]['date_fact'] = null;
                $resultContractorPayments[$key]['diff'] = $diff;
                $resultContractorPayments[$key]['is_text_date'] = $p['is_text_date'];
                $resultContractorPayments[$key]['text_date'] = $p['text_date'];
                $resultContractorPayments[$key]['add_type'] = $p['add_type'];

            }

            $resultContractorPayments2 = [];

            foreach ($planContractorPayments2 as $key => $p) {

                $ost = $amountContractorPayments2 - $p['amount'];
                if ($ost == 0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountContractorPayments2 = $ost;
                }

                if ($ost >0) {
                    $diff = 0;
                    $amountFact = $p['amount'];
                    $amountContractorPayments2 = $ost;
                }

                if ($ost < 0) {
                    $diff = $this->getDiffDays($p['date']);
                    $amountFact = $amountContractorPayments2;
                    $amountContractorPayments2 = 0;
                }

                $resultContractorPayments2[$key]['id'] = $p['id'];
                $resultContractorPayments2[$key]['deal_id'] = $p['deal_id'];
                $resultContractorPayments2[$key]['type'] = $p['type'];
                $resultContractorPayments2[$key]['pay_type'] = $p['pay_type'];
                $resultContractorPayments2[$key]['doc_number'] = $p['doc_number'];
                $resultContractorPayments2[$key]['amount'] = $p['amount'];
                $resultContractorPayments2[$key]['date'] = $p['date'];
                $resultContractorPayments2[$key]['blocked'] = $p['blocked'];
                $resultContractorPayments2[$key]['note'] = $p['note'];
                $resultContractorPayments2[$key]['order'] = $p['order'];
                $resultContractorPayments2[$key]['amount_fact'] = $amountFact;
                $resultContractorPayments2[$key]['date_fact'] = null;
                $resultContractorPayments2[$key]['diff'] = $diff;
                $resultContractorPayments2[$key]['is_text_date'] = $p['is_text_date'];
                $resultContractorPayments2[$key]['text_date'] = $p['text_date'];
                $resultContractorPayments2[$key]['add_type'] = $p['add_type'];

            }

            //отсортированный список плановых платежей
//            $planPaymentList = [];
            $planPaymentList = array_merge($resultMainPayments, $resultContractorPayments);
            $planPaymentList2 = array_merge($resultMainPayments2, $resultContractorPayments2);

            //получаем данные по сделке
            $dealData = $this->b24->getDealById($dealId);
            $deal = $dealData['result'];

            /**
             * --------- Фактические платежи
             */
            $docNumbers = [];
            foreach ($planPayments->groupBy('doc_number') as $key => $value) {
                $docNumbers[] = trim($key);
            }

            foreach ($planPayments2->groupBy('doc_number') as $key => $value) {
                $docNumbers[] = trim($key);
            }

            if (count($docNumbers) === 0) {
                $factPayment = [];
            }

//            dd($docNumbers);

            $factPayments = $this->mFactPayment->whereIn('doc_number', $docNumbers)->orderBy('date')->get();

            //список юр лиц для выбора при выгрузке графика по земле
            $entities = config('perspektivakrym.land');

            return view('perspektivakrym::index')
                ->with(['viewData' => [
                    'payments' => $planPaymentList,

                    'resultMainPayments' => $resultMainPayments,
                    'resultContractorPayments' => $resultContractorPayments,

                    'payments2' => $planPaymentList2,

                    'resultMainPayments2' => $resultMainPayments2,
                    'resultContractorPayments2' => $resultContractorPayments2,

                    'factPayments' => $factPayments,
                    'dealId' => $dealId,
                    'entities' => $entities,
                    'auth' => $auth,
                    'mainContract' => $deal['UF_CRM_1611646713185'],
                    'contractorContract' => $deal['UF_CRM_1611646728314'],
                ]]);
        } catch (\DomainException $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());

            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());

            return view('perspektivakrym::index')
                ->with(['error' => 'Что-то пошло не так']);
        }
    }

    /**
     * Расчет графика
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function calculatePayments(Request $request, $planPaymentMainAmountReserv = 0, $planPaymentContractAmountReserv = 0, $numberGraph = 0)
    {
        try {
            $dealId = $request->get('deal_id', null);
            $auth = $request->get('auth');
            $numberGraph = $request->get('number_graph', $numberGraph);

            Log::info("=== НАЧАЛО РАСЧЕТА ГРАФИКА ПЛАТЕЖЕЙ ===");
            Log::info("Сделка ID: {$dealId}");
            Log::info("Номер графика: {$numberGraph}");
            Log::info("Резерв основных платежей: {$planPaymentMainAmountReserv}");
            Log::info("Резерв платежей по подряду: {$planPaymentContractAmountReserv}");

            if (!$this->checkApp($auth)) {
                Log::error("ОШИБКА: Приложение не авторизовано");
                throw new \DomainException('Приложение не авторизовано');
            }

            Log::info("✓ Авторизация прошла успешно");

            $deal = $this->b24->getDealById($dealId)['result'];
            Log::info("✓ Данные сделки получены из Bitrix24");

//            dd($deal);

            $paymentCount = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $numberGraph)
                ->count();

            Log::info("Проверка существования графика: найдено {$paymentCount} активных платежей");

            if ($paymentCount !== 0) {
                Log::error("ОШИБКА: График уже составлен (найдено {$paymentCount} платежей)");
                throw new \DomainException('График уже составлен');
            }

            Log::info("✓ График не существует, можно создавать новый");

            Log::info("=== ВХОДНЫЕ ДАННЫЕ ИЗ BITRIX24 ===");
            
            //сумма сделки
            $dealAmount = (int)$deal['OPPORTUNITY'];
            Log::info("Поле OPPORTUNITY (Сумма сделки) значение: {$dealAmount}");

            if($dealAmount == 0) {
                Log::error("ОШИБКА: Сумма сделки равна 0");
                throw new \DomainException('Сумма сделки равна 0');
            }

            //ПВ основной
            $firstPayment = 0;
            $firstPayment = (int)$deal['UF_CRM_1602665856'];
            Log::info("Поле UF_CRM_1602665856 (ПВ основной) значение: {$firstPayment}");

            //Сумма земля
            $landAmount = 0;
            $landAmount = (int)$deal['UF_CRM_1610624104'];
            Log::info("Поле UF_CRM_1610624104 (Сумма земля) значение: {$landAmount}");

            //Сумма подряд
            $contractAmount = 0;
            $contractAmount = (int)$deal['UF_CRM_1610624123'];
            Log::info("Поле UF_CRM_1610624123 (Сумма подряд) значение: {$contractAmount}");
            $contractAmount = $contractAmount - $planPaymentContractAmountReserv;
            Log::info("Формула: Сумма подряд - Резерв подряда = {$contractAmount} ({$deal['UF_CRM_1610624123']} - {$planPaymentContractAmountReserv})");

//            dd($landAmount, $contractAmount);

            Log::info("=== ДАТЫ И ВРЕМЕННЫЕ ПАРАМЕТРЫ ===");
            
            //Дата окончания строительства
            $finishDate = $deal['UF_CRM_1612354364'];
            Log::info("Поле UF_CRM_1612354364 (Дата окончания строительства) значение: {$finishDate}");
            if ($finishDate == '') {
                Log::error("ОШИБКА: Не указана дата окончания строительства");
                throw new \DomainException('Не указана дата окончания строительства');
            }

            //Дней на внесение ПВ (ДДУ / земля / доп лот)
            if ($deal['UF_CRM_1610624156'] == '') {
                $daysFirstPayment = 0;
            } else {
                $daysFirstPayment = (int)$deal['UF_CRM_1610624156'];
            }
            Log::info("Поле UF_CRM_1610624156 (Дней на внесение ПВ основной) значение: {$daysFirstPayment}");

            //Дней на внесение ПВ (подряд)
            if ($deal['UF_CRM_1617787747'] == '') {
                $daysFirstContractPayment = 0;
            } else {
                $daysFirstContractPayment = (int)$deal['UF_CRM_1617787747'];
            }
            Log::info("Поле UF_CRM_1617787747 (Дней на внесение ПВ подряд) значение: {$daysFirstContractPayment}");

            //Рассрочка, мес. (кол-во платежей)
            $installmentPlan = 1;
            $installmentPlan = (int)$deal['UF_CRM_1602666205'];
            Log::info("Поле UF_CRM_1602666205 (Рассрочка, мес.) значение: {$installmentPlan}");

            //ПВ за подряд
            $firstPaymentContract = 0;
            $firstPaymentContract = (int)$deal['UF_CRM_1612333754254'];
            Log::info("Поле UF_CRM_1612333754254 (ПВ за подряд) значение: {$firstPaymentContract}");

            //Дата создания договора
            $dateCreate = $deal['UF_CRM_1602665724'];
            Log::info("Поле UF_CRM_1602665724 (Дата создания договора) значение: {$dateCreate}");

            //Дата заключения ПОДРЯДА
            $dateStartPodryad = $deal['UF_CRM_1611646586442'];
            Log::info("Поле UF_CRM_1611646586442 (Дата заключения подряда) значение: {$dateStartPodryad}");
            if ($firstPaymentContract !== 0) {
                if (!$deal['UF_CRM_1611646586442']) {
                    Log::error("ОШИБКА: Не указана дата заключения подряда");
                    throw new \DomainException('Не указана дата заключения подряда');
                }
            }

            //Дата создания осн. договора (ДДУ, земля, доп. лот)
            $dateStartMain = $deal['UF_CRM_AMO_560317'];
            Log::info("Поле UF_CRM_AMO_560317 (Дата создания основного договора) значение: {$dateStartMain}");
            if (!$dateStartMain) {
                Log::error("ОШИБКА: Не указана дата заключения основного договора");
                throw new \DomainException('Не указана дата заключения основного договора');
            }

            Log::info("=== ЧАСТОТА ПЛАТЕЖЕЙ И НОМЕРА ДОГОВОРОВ ===");
            
            //Частота платежей
            // 2302 - Месяц
            // 2304 - Квартал
            // 2306 - Полгода
            // 2308 - Год
            $paymentFrequency = 2302;
            $paymentFrequency = $deal['UF_CRM_1615449388'];
            Log::info("Поле UF_CRM_1615449388 (Частота платежей) значение: {$paymentFrequency}");

            //№ основного договора (ДДУ, земля, доп. лот)"
            $numberLand = $deal['UF_CRM_1611646713185'];
            Log::info("Поле UF_CRM_1611646713185 (№ основного договора) значение: {$numberLand}");
            if (!$numberLand) {
                Log::error("ОШИБКА: Не указан номер основного договора");
                throw new \DomainException('Не указан номер основного договора');
            }

            //№ договора ПОДРЯД"
            $numberContract = $deal['UF_CRM_1611646728314'];
            Log::info("Поле UF_CRM_1611646728314 (№ договора подряда) значение: {$numberContract}");

            if ($deal['UF_CRM_1611646586442'] !== '') {
                if (!$numberContract) {
                    Log::error("ОШИБКА: Не указан номер договора подряда");
                    throw new \DomainException('Не указан номер договора подряда');
                }
            }

//            dd($paymentFrequency);

            Log::info("=== РАСЧЕТ КОЛИЧЕСТВА ПЛАТЕЖЕЙ ===");
            
            switch ($paymentFrequency) {
                case '2302':
                    $quantityFromFrequency = 1;
                    Log::info("Частота платежей: Месяц (2302) -> количество месяцев: {$quantityFromFrequency}");
                    break;
                case '2304':
                    $quantityFromFrequency = 3;
                    Log::info("Частота платежей: Квартал (2304) -> количество месяцев: {$quantityFromFrequency}");
                    break;
                case '2306':
                    $quantityFromFrequency = 6;
                    Log::info("Частота платежей: Полгода (2306) -> количество месяцев: {$quantityFromFrequency}");
                    break;
                case '2308':
                    $quantityFromFrequency = 12;
                    Log::info("Частота платежей: Год (2308) -> количество месяцев: {$quantityFromFrequency}");
                    break;
                default:
                    $quantityFromFrequency = 3;
                    Log::info("Частота платежей: По умолчанию -> количество месяцев: {$quantityFromFrequency}");
            }

            //count
            $dateStartWithDays = Carbon::create($dateStartMain)->addDays($daysFirstPayment);
            $totalMonths = Carbon::create($finishDate)->diffInMonths($dateStartWithDays);
            $count = floor($totalMonths / $quantityFromFrequency);
            
            Log::info("Формула расчета количества платежей:");
            Log::info("  Дата начала: {$dateStartMain}");
            Log::info("  Дней на ПВ: {$daysFirstPayment}");
            Log::info("  Дата начала с учетом ПВ: {$dateStartWithDays->format('Y-m-d')}");
            Log::info("  Дата окончания: {$finishDate}");
            Log::info("  Общее количество месяцев: {$totalMonths}");
            Log::info("  Количество месяцев в периоде: {$quantityFromFrequency}");
            Log::info("  Количество платежей: {$count}");
//            $test = strtotime($finishDate) - strtotime(Carbon::create($dateStartMain)->addDays($daysFirstPayment));
//            dd($count, $installmentPlan);

            Log::info("=== РАСЧЕТ ПО ОСНОВНОМУ ДОГОВОРУ ===");
            
            //по основному договору
            Log::info("Создание ПВ по основному договору:");
            Log::info("  Сделка ID: {$dealId}");
            Log::info("  Номер договора: {$numberLand}");
            Log::info("  Сумма ПВ: {$firstPayment}");
            Log::info("  Дата начала: {$dateStartMain}");
            Log::info("  Дней на ПВ: {$daysFirstPayment}");
            $this->mainDownPaymentCalculate($dealId, $numberLand, $firstPayment, $dateStartMain, $daysFirstPayment, $numberGraph);
            
            if ($landAmount !== 0) {
                Log::info("Есть сумма по земле, используем её для расчета");
                $landAmount = $landAmount - $planPaymentMainAmountReserv;
                Log::info("Формула: Сумма земля - Резерв основных = {$landAmount} ({$deal['UF_CRM_1610624104']} - {$planPaymentMainAmountReserv})");
                Log::info("Создание регулярных платежей по земле:");
                Log::info("  Общая сумма: {$landAmount}");
                Log::info("  ПВ: {$firstPayment}");
                Log::info("  Количество платежей: {$count}");
                $this->mainRegularPaymentCalculate($dealId, $numberLand, $landAmount, $firstPayment, $dateStartMain, $daysFirstPayment, $count, $paymentFrequency, $finishDate, $numberGraph);
            } else {
                Log::info("Нет суммы по земле, используем общую сумму сделки");
                $dealAmount = $dealAmount - $planPaymentMainAmountReserv;
                Log::info("Формула: Сумма сделки - Резерв основных = {$dealAmount} ({$deal['OPPORTUNITY']} - {$planPaymentMainAmountReserv})");
                Log::info("Создание регулярных платежей по сделке:");
                Log::info("  Общая сумма: {$dealAmount}");
                Log::info("  ПВ: {$firstPayment}");
                Log::info("  Количество платежей: {$count}");
                $this->mainRegularPaymentCalculate($dealId, $numberLand, $dealAmount, $firstPayment, $dateStartMain, $daysFirstPayment, $count, $paymentFrequency, $finishDate, $numberGraph);
            }

            Log::info("=== РАСЧЕТ ПО ПОДРЯДУ ===");
            
            //по подряду
            if ((!is_null($dateStartPodryad)) && ($dateStartPodryad !== '')) {
                Log::info("Есть дата заключения подряда, создаем платежи по подряду");
                $dateStart = Carbon::create($dateStartMain)->addDays($daysFirstPayment)->format('Y-m-d');
                Log::info("Дата начала подряда: {$dateStart} (основная дата + {$daysFirstPayment} дней)");
                
                Log::info("Создание ПВ по подряду:");
                Log::info("  Сделка ID: {$dealId}");
                Log::info("  Номер договора: {$numberContract}");
                Log::info("  Сумма ПВ: {$firstPaymentContract}");
                Log::info("  Дата начала: {$dateStart}");
                Log::info("  Дней на ПВ: {$daysFirstContractPayment}");
                $this->contractDownPaymentCalculate($dealId, $numberContract, $firstPaymentContract, $dateStart, $daysFirstContractPayment, $numberGraph);
                
                Log::info("Создание регулярных платежей по подряду:");
                Log::info("  Общая сумма: {$contractAmount}");
                Log::info("  ПВ: {$firstPaymentContract}");
                Log::info("  Количество платежей: {$count}");
                $this->contractRegularPaymentCalculate($dealId, $numberContract, $contractAmount, $firstPaymentContract, $dateStart, $daysFirstContractPayment, $count, $paymentFrequency, $numberGraph);
            } else {
                Log::info("Нет даты заключения подряда, пропускаем расчет по подряду");
            }

            Log::info("=== ЗАВЕРШЕНИЕ РАСЧЕТА ===");
            Log::info("✓ График платежей успешно создан");
            Log::info("✓ Возврат к странице с результатами");
            
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error("=== ОШИБКА ПРИ РАСЧЕТЕ ===");
            Log::error("ОШИБКА: " . $e->getMessage());
            Log::error("Сделка ID: {$dealId}");
            Log::error("Номер графика: {$numberGraph}");
            
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Пересчет графика
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reCalculatePayments(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $numberGraph = $request->get('number_graph', 0);

            $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('pay_type', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('add_type', $this->mPlanPayment::AUTO)
                ->where('number_graph', $numberGraph)
                ->delete();

            $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('pay_type', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('add_type', $this->mPlanPayment::MANUAL)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $numberGraph)
                ->delete();

            $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $numberGraph)
                ->delete();

            $planPaymentMainAmountReserv = $this->mPlanPayment
                ->where('deal_id', $dealId)
//                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('type', $this->mPlanPayment::MAIN)
                ->where('blocked', $this->mPlanPayment::BLOCK)
                ->where('number_graph', $numberGraph)
                ->sum('amount');

            $planPaymentContractAmountReserv = $this->mPlanPayment
                ->where('deal_id', $dealId)
//                ->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)
                ->where('type', $this->mPlanPayment::CONTRACT)
                ->where('blocked', $this->mPlanPayment::BLOCK)
                ->where('number_graph', $numberGraph)
                ->sum('amount');

//            dd($planPaymentMainAmountReserv, $planPaymentContractAmountReserv);

            $this->calculatePayments($request, $planPaymentMainAmountReserv, $planPaymentContractAmountReserv, $numberGraph);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Добавление планового платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addPlanPayment(Request $request) {
        try {
            $dealId = $request->get('deal_id', null);
            $auth = $request->get('auth');
            $numberGraph = $request->get('number_graph', 0);

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $type = $request->get('type', 'main');
            $payType = $request->get('pay_type', 'down_payment');
            $docNumber = $request->get('doc_number', null);
            $note = $request->get('note', null);
            $isTextDate = $request->get('is_text_date', 0);
            $textDate = $request->get('text_date', '');

            if (!$docNumber) {
                throw new \DomainException('Не указан номер договора');
            }

            $amount = $request->get('amount', 0);
            $date = $request->get('date', Carbon::now());

            //получаем все платежи по сделке, по типу договора и не замороженные
            $planPayments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $numberGraph)
                ->get();

            //получаем сумму по этим сделкам
            $planPaymentAmount = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $numberGraph)
                ->sum('amount');

            $countPayments = count($planPayments);

            if ($countPayments == 0) {
                throw new \DomainException('Платежи не найден или все платежи заморожены');
            }

            //добавляем платеж
            $this->mPlanPayment->create([
                'deal_id' => $dealId,
                'type' => $type,
                'pay_type' => $payType,
                'doc_number' => $docNumber,
                'amount' => $amount,
                'date' => date('Y-m-d', strtotime($date)),
                'blocked' => ($payType === $this->mPlanPayment::DOWN_PAYMENT) ? $this->mPlanPayment::BLOCK : $this->mPlanPayment::ACTIVE,
                'note' => $note,
                'order' => 1,
                'is_text_date' => $isTextDate,
                'text_date' => $textDate,
                'add_type' => $this->mPlanPayment::MANUAL,
                'number_graph' => $numberGraph,
            ]);

            //остаток суммы
            $balanceAmount = $planPaymentAmount - $amount;

            if ($countPayments == 1) {
                $this->mPlanPayment->where('id', $planPayments[0]->id)
                    ->update([
                       'amount' => $balanceAmount,
                    ]);

                return $this->index(new Request(), [
                    'deal_id' => $dealId,
                    'auth' => $auth,
                ]);
            }

            $newAmount = ceil($balanceAmount / $countPayments);

            for ($i = 0; $i < ($countPayments - 1); $i++) {
                $this->mPlanPayment->where('id', $planPayments[$i]->id)
                    ->update([
                        'amount' => $newAmount,
                    ]);
            }

            $newLastAmount = $balanceAmount - ($newAmount * ($countPayments -1));
            $this->mPlanPayment->where('id', $planPayments[$countPayments -1]->id)
                ->update([
                    'amount' => $newLastAmount,
                ]);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Добавление фактического платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addFactPayment(Request $request) {
        try {
            $dealId = $request->get('deal_id', null);
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $docNumber = $request->get('doc_number', null);
            $note = $request->get('note', null);

//            dd($docNumber);

            if (!$docNumber) {
                throw new \DomainException('Не указан номер договора');
            }

            $amount = $request->get('amount', 0);
            $date = $request->get('date', Carbon::now());


            //добавляем платеж
            $this->mFactPayment->create([
                'payment_id' => null,
                'type' => $this->mFactPayment::MANUAL,
                'date' => date('Y-m-d', strtotime($date)),
                'contractor' => null,
                'doc_number' => $docNumber,
                'amount' => $amount,
                'note' => $note,
                'deal_id' => $dealId,
            ]);

            $this->changeFieldContractPaidOfDeal($dealId);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);
        }
    }

    private function changeFieldContractPaidOfDeal($dealId) {
        try {
            //Получаем все факт платежи по сделке
            $amountFactPayment = $this->mFactPayment
                ->where('deal_id', $dealId)
                ->sum('amount');

            //Получаем сумму сделки
            $deal = $this->b24->getDealById($dealId)['result'];
            $dealAmount = (int)$deal['OPPORTUNITY'];

            if ($amountFactPayment >= $dealAmount) {
                //меняем поле сделки
                $this->b24->changeFieldContractPaidOfDeal($dealId, 1);
            } else {
                $this->b24->changeFieldContractPaidOfDeal($dealId, 0);
            }
        } catch(\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);
        }

    }

    /**
     * Редактирование фактического платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editFactPayment(Request $request) {
        try {
            $dealId = $request->get('deal_id', null);
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $docNumber = $request->get('doc_number', null);
            $note = $request->get('note', null);
            $operationId = $request->get('operation_id');

            if (!$docNumber) {
                throw new \DomainException('Не указан номер договора');
            }

            $amount = $request->get('amount', 0);
            $date = $request->get('date', Carbon::now());


            //добавляем платеж
            $this->mFactPayment
                ->where('id', $operationId)
                ->update([
                    'payment_id' => null,
                    'type' => $this->mFactPayment::MANUAL,
                    'date' => date('Y-m-d', strtotime($date)),
                    'contractor' => null,
                    'doc_number' => $docNumber,
                    'amount' => $amount,
                    'note' => $note,
                    'deal_id' => $dealId,
                ]);

            $this->changeFieldContractPaidOfDeal($dealId);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Удаление фактического платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function deleteFactPayment(Request $request) {
        try {
            $dealId = $request->get('deal_id', null);
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $operationId = $request->get('operation_id');

            //удаляем платеж
            $this->mFactPayment
                ->where('id', $operationId)
                ->delete();

            $this->changeFieldContractPaidOfDeal($dealId);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Изменение даты планового платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function changePlanDate(Request $request)
    {
        try {
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $dealId = $request->get('deal_id');
            $paymentId = $request->get('operation_id');
            $planDate = $request->get('date');
            $isTextDate = $request->get('is_text_date', 0);
            $textDate = $request->get('text_date', '');
            $this->mPlanPayment->where('id', $paymentId)
                ->update([
                    'date' => date('Y-m-d', strtotime($planDate)),
                    'is_text_date' => $isTextDate,
                    'text_date' => $textDate,
                ]);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    public function deletePlanPayment(Request $request)
    {
        try {
            $dealId = $request->get('deal_id', null);
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $dealId = $request->get('deal_id');
            $paymentId = $request->get('operation_id');

            //получаем данные по платежу
            $currentPlanPayment = $this->mPlanPayment->where('id', $paymentId)->first();

            if (is_null($currentPlanPayment)) {
                throw new \DomainException('Платеж не найден');
            }

            if ($currentPlanPayment->blocked) {
                throw new \DomainException('Платеж удалить нельзя, платеж заморожен');
            }

            $paymentAmount = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $currentPlanPayment->type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $currentPlanPayment->number_graph)
                ->sum('amount');

            $currentPlanPayment->delete();

            $paymentList = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $currentPlanPayment->type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $currentPlanPayment->number_graph)
                ->get();

            $paymentCount = count($paymentList);

            if ($paymentCount == 0) {
                throw new \DomainException('Платежи не найдены или все заморожены');
            }


            $newAmount = round($paymentAmount  / $paymentCount);

//            dd($paymentCount, $newAmount);

//            if ($countPayment == 1) {
//                $paymentList[0]->amount = $newAmount;
//                $paymentList[0]->save();
//
//                return $this->index(new Request(), [
//                    'deal_id' => $dealId,
//                    'auth' => $auth,
//                ]);
//            }

            for ($i = 0; $i < ($paymentCount - 1); $i++) {
                $paymentList[$i]->amount = $newAmount;
                $paymentList[$i]->save();
            }

            $paymentList[$paymentCount - 1]->amount = $paymentAmount - $newAmount * ($paymentCount - 1);
            $paymentList[$paymentCount - 1]->save();




            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return view('perspektivakrym::index')
                ->with(['error' => $e->getMessage()]);
        }
    }

    /**
     * Изменение суммы планового платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function changePlanAmount(Request $request)
    {
        try {
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $dealId = $request->get('deal_id');
            $paymentId = $request->get('operation_id');
            $planAmount = $request->get('amount');

            //получаем данные по платежу
            $currentPlanPayment = $this->mPlanPayment->where('id', $paymentId)->first();

            if (is_null($currentPlanPayment)) {
                throw new \DomainException('Платеж не найден');
            }

            $paymentList = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $currentPlanPayment->type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $currentPlanPayment->number_graph)
                ->get();

            $paymentAmount = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $currentPlanPayment->type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $currentPlanPayment->number_graph)
                ->sum('amount');

            $paymentCount = count($paymentList);

            if ($paymentCount == 0) {
                throw new \DomainException('Платежи не найдены или все заморожены');
            }

            if ($paymentCount == 1) {
                throw new \DomainException('Платеж один');
            }

            //обновляем сумму у текущего платежа
            $currentPlanPayment->amount = $planAmount;
            $currentPlanPayment->save();

            $paymentList = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $currentPlanPayment->type)
                ->where('blocked', $this->mPlanPayment::ACTIVE)
                ->where('number_graph', $currentPlanPayment->number_graph)
                ->where('id', '!=', $paymentId)
                ->get();

            $countPayment = count($paymentList);

            $newAmount = round(($paymentAmount - $planAmount) / $countPayment);

            if ($countPayment == 1) {
                $paymentList[0]->amount = $newAmount;
                $paymentList[0]->save();

                return $this->index(new Request(), [
                    'deal_id' => $dealId,
                    'auth' => $auth,
                ]);
            }

            for ($i = 0; $i < ($countPayment - 1); $i++) {
                $paymentList[$i]->amount = $newAmount;
                $paymentList[$i]->save();
            }

            $paymentList[$countPayment - 1]->amount = $paymentAmount - $planAmount - $newAmount * ($countPayment - 1);
            $paymentList[$countPayment - 1]->save();


//            dd($paymentList);

//            "id" => 58
//            "deal_id" => 32786
//            "type" => "main"
//            "pay_type" => "regular_payment"
//            "doc_number" => "ДД-101/32786 от 27.01.2021"
//            "amount" => 474999
//            "date" => "2021-04-01"
//            "blocked" => 0
//            "note" => ""
//            "order" => 1
//            "created_at" => "2021-04-08 18:54:26"
//            "updated_at" => "2021-04-08 23:16:07"
//            dd($currenPlanPayment);




            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Получения всех данных для генерации PDF
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPdf(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $numberGraph = $request->get('number_graph', 0);
            $type = $request->get('type');
            $number = $request->get('annex_number', 1);
            $entity = $request->get('entity', null);

            if (is_null($entity)) {
                throw new \DomainException('Необходимо указать юридическое лицо');
            }

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $planPayments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', $type)
                ->where('number_graph', $numberGraph)
                ->get();

            if (count($planPayments) == 0) {
                throw new \DomainException('Нет платежей');
            }

            $deal = $this->b24->getDealById($dealId);

            $contactId = $deal['result']['CONTACT_ID'];
            $userData = [];
            $userData['FULL_NAME'] = null;
            $userData['LAST_NAME'] = null;
            $userData['NAME'] = null;
            $userData['SECOND_NAME'] = null;
            $userData['BIRTHDATE'] = null;
            $userData['SEX'] = null;
            $userData['CITY_BIRTH'] = null;
            $userData['PASSPORT_COUNTRY'] = null;
            $userData['PASSPORT_SERIES'] = null;
            $userData['PASSPORT_NUMBER'] = null;
            $userData['PASSPORT_ORGAN'] = null;
            $userData['PASSPORT_DATE'] = null;
            $userData['PASSPORT_ORGAN_CODE'] = null;
            $userData['SNILS'] = null;
            $userData['ADDRESS'] = null;
            $userData['PHONE'] = null;
            $userData['ENTITY'] = $entity;

            if (is_null($contactId)) {
                $fullName = '___________________';
            } else {
                $contact = $this->b24->getContactId($contactId);

                $userData['NAME'] = $contact['result']['NAME'];
                if (is_null($contact['result']['NAME'])) {
                    $name = null;
                } else {
                    $name = mb_substr($contact['result']['NAME'], 0, 1);
                }

                $userData['SECOND_NAME'] = $contact['result']['SECOND_NAME'];
                if (is_null($contact['result']['SECOND_NAME'])) {
                    $secondName = null;
                } else {
                    $secondName = mb_substr($contact['result']['SECOND_NAME'], 0, 1);
                }

                $userData['LAST_NAME'] = $contact['result']['LAST_NAME'];
                if (is_null($contact['result']['LAST_NAME'])) {
                    $lastName = null;
                } else {
                    $lastName = $contact['result']['LAST_NAME'];
                }

                $fullName = '';
                if (!is_null($name)) {
                    $fullName = $fullName . $name . '. ';
                }

                if (!is_null($secondName)) {
                    $fullName = $fullName . $secondName . '. ';
                }

                if (!is_null($lastName)) {
                    $fullName = $fullName . $lastName;
                }

                $fullName = trim($fullName);

                $userData['FULL_NAME'] = $fullName;

                $userData['BIRTHDATE'] = $contact['result']['BIRTHDATE'];
                //поле
                $userData['SEX'] = $contact['result']['UF_CRM_1611904450'];
                //место рождения
                $userData['CITY_BIRTH'] = $contact['result']['UF_CRM_1602667328'];
                //паспорт гражданина
                $userData['PASSPORT_COUNTRY'] = $contact['result']['UF_CRM_1602666811'];
                //серия паспорта
                $userData['PASSPORT_SERIES'] = $contact['result']['UF_CRM_1610643031'];
                //номер паспорта
                $userData['PASSPORT_NUMBER'] = $contact['result']['UF_CRM_1611904532'];
                //кем выдан
                $userData['PASSPORT_ORGAN'] = $contact['result']['UF_CRM_1602667243'];
                //дата выдачи
                $userData['PASSPORT_DATE'] = $contact['result']['UF_CRM_1602667208'];
                //код подразделения
                $userData['PASSPORT_ORGAN_CODE'] = $contact['result']['UF_CRM_1611904282'];
                //снилс
                $userData['SNILS'] = $contact['result']['UF_CRM_1611584831923'];
                //зарегистрирован по адресу
                $userData['ADDRESS'] = $contact['result']['UF_CRM_1602667289'];
                //телефон
                $userData['PHONE'] = '';
                foreach ($contact['result']['PHONE'] as $ph) {
                    if ($ph['TYPE_ID'] == 'PHONE' && $ph['VALUE_TYPE'] == 'WORK') {
                        $userData['PHONE'] = $ph['VALUE'];
                        break;
                    }
                }
            }

            //ЖК
            $dealGk = $deal['result']['UF_CRM_PB_PROJECT'];
            $dealTest = $deal['result']['UF_CRM_1617025791625'];

            if ($dealGk === 'ЖК ДЕМО (не удалять)') {
//                $this->generatePdfPP($number, $planPayments, $fullName);
                $this->generatePdfPmWithoutB($number, $planPayments, $type, $userData);
//                $this->generatePdfPmWithB($number, $planPayments, $type, $userData);
            }

            if ($dealGk === 'Парк Плаза') {
                $this->generatePdfPP($number, $planPayments, $fullName);
            }

            if ($dealGk === 'Паруса Мечты' && $dealTest != 2356) {
//                dd($dealTest);
                //Земли сформировать по шаблону 111
                //Подряд сформировать по шаблону 222
                $this->generatePdfPmWithoutB($number, $planPayments, $type, $userData);
            }

            if ($dealGk === 'Паруса Мечты' && $dealTest == 2356) {
//                dd($dealTest);
                //при скачивании графика Земли сформировать по шаблону 333
                //при скачивании графика Подряд сформировать по шаблону 222
                $this->generatePdfPmWithB($number, $planPayments, $type, $userData);
            }

            if ($dealGk === 'Жилой комплекс «Монако»') {
                //444
            }

            if ($dealGk === 'Династия') {
                $this->generatePdfDinastiyaWithoutB($number, $planPayments, $type, $userData);
            }

            if (strtolower($dealGk) === strtolower('LUCHI')) {
                $this->generatePdfLuchiWithoutB($number, $planPayments, $type, $userData);
            }

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);

        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }

    }

    public function getGeneralPdf(Request $request)
    {
        try {
            Log::info("=== НАЧАЛО СОЗДАНИЯ ОБЩЕГО PDF ===");
            
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $type = $request->get('type');
            $number = $request->get('annex_number', 1);
            $entity = $request->get('entity', null);
            $numberGraph = $request->get('number_graph', 0);
            
            Log::info("Сделка ID: {$dealId}");
            Log::info("Тип: {$type}");
            Log::info("Номер приложения: {$number}");
            Log::info("Юридическое лицо: {$entity}");
            Log::info("Номер графика: {$numberGraph}");

//            if (is_null($entity)) {
//                throw new \DomainException('Необходимо указать юридическое лицо');
//            }
//
//            if (!$this->checkApp($auth)) {
//                throw new \DomainException('Приложение не авторизовано');
//            }

            Log::info("Получение платежей по основному договору...");
            $planMainPayments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', 'main')
                ->where('number_graph', $numberGraph)
                ->orderBy('date')
                ->get();
            Log::info("Найдено платежей по основному договору: " . $planMainPayments->count());

            Log::info("Получение платежей по подряду...");
            $planContractPayments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('type', 'contract')
                ->where('number_graph', $numberGraph)
                ->orderBy('date')
                ->get();
            Log::info("Найдено платежей по подряду: " . $planContractPayments->count());

//            if (count($planPayments) == 0) {
//                throw new \DomainException('Нет платежей');
//            }
            $planAllPayments =  $planMainPayments->merge($planContractPayments)->sortBy('date');

            $deal = $this->b24->getDealById($dealId);

            $contactId = $deal['result']['CONTACT_ID'];
            $userData = [];
            $userData['FULL_NAME'] = null;
            $userData['LAST_NAME'] = null;
            $userData['NAME'] = null;
            $userData['SECOND_NAME'] = null;
            $userData['BIRTHDATE'] = null;
            $userData['SEX'] = null;
            $userData['CITY_BIRTH'] = null;
            $userData['PASSPORT_COUNTRY'] = null;
            $userData['PASSPORT_SERIES'] = null;
            $userData['PASSPORT_NUMBER'] = null;
            $userData['PASSPORT_ORGAN'] = null;
            $userData['PASSPORT_DATE'] = null;
            $userData['PASSPORT_ORGAN_CODE'] = null;
            $userData['SNILS'] = null;
            $userData['ADDRESS'] = null;
            $userData['PHONE'] = null;
            $userData['ENTITY'] = $entity;

            if (is_null($contactId)) {
                $fullName = '___________________';
            } else {
                $contact = $this->b24->getContactId($contactId);

                $userData['NAME'] = $contact['result']['NAME'];
                if (is_null($contact['result']['NAME'])) {
                    $name = null;
                } else {
                    $name = mb_substr($contact['result']['NAME'], 0, 1);
                }

                $userData['SECOND_NAME'] = $contact['result']['SECOND_NAME'];
                if (is_null($contact['result']['SECOND_NAME'])) {
                    $secondName = null;
                } else {
                    $secondName = mb_substr($contact['result']['SECOND_NAME'], 0, 1);
                }

                $userData['LAST_NAME'] = $contact['result']['LAST_NAME'];
                if (is_null($contact['result']['LAST_NAME'])) {
                    $lastName = null;
                } else {
                    $lastName = $contact['result']['LAST_NAME'];
                }

                $fullName = '';
                if (!is_null($name)) {
                    $fullName = $fullName . $name . '. ';
                }

                if (!is_null($secondName)) {
                    $fullName = $fullName . $secondName . '. ';
                }

                if (!is_null($lastName)) {
                    $fullName = $fullName . $lastName;
                }

                $fullName = trim($fullName);

                $userData['FULL_NAME'] = $fullName;

                $userData['BIRTHDATE'] = $contact['result']['BIRTHDATE'];
                //поле
                $userData['SEX'] = $contact['result']['UF_CRM_1611904450'];
                //место рождения
                $userData['CITY_BIRTH'] = $contact['result']['UF_CRM_1602667328'];
                //паспорт гражданина
                $userData['PASSPORT_COUNTRY'] = $contact['result']['UF_CRM_1602666811'];
                //серия паспорта
                $userData['PASSPORT_SERIES'] = $contact['result']['UF_CRM_1610643031'];
                //номер паспорта
                $userData['PASSPORT_NUMBER'] = $contact['result']['UF_CRM_1611904532'];
                //кем выдан
                $userData['PASSPORT_ORGAN'] = $contact['result']['UF_CRM_1602667243'];
                //дата выдачи
                $userData['PASSPORT_DATE'] = $contact['result']['UF_CRM_1602667208'];
                //код подразделения
                $userData['PASSPORT_ORGAN_CODE'] = $contact['result']['UF_CRM_1611904282'];
                //снилс
                $userData['SNILS'] = $contact['result']['UF_CRM_1611584831923'];
                //зарегистрирован по адресу
                $userData['ADDRESS'] = $contact['result']['UF_CRM_1602667289'];
                //телефон
                $userData['PHONE'] = '';
                foreach ($contact['result']['PHONE'] as $ph) {
                    if ($ph['TYPE_ID'] == 'PHONE' && $ph['VALUE_TYPE'] == 'WORK') {
                        $userData['PHONE'] = $ph['VALUE'];
                        break;
                    }
                }
            }



            //ЖК
            $dealGk = $deal['result']['UF_CRM_PB_PROJECT'];
            //apartment
            $ap = $deal['result']['UF_CRM_1603114986'];
            //type
            $typeApId = $deal['result']['UF_CRM_1608373282'];

//            switch ($typeApId) {
//                case 114:
//                    $typeApText = 'Коттедж';
//                    break;
//                case 116:
//                    $typeApText = 'Квартира';
//                    break;
//                case 118:
//                    $typeApText = 'Апартамент';
//                    break;
//                case 120:
//                    $typeApText = 'Участок';
//                    break;
//                case 122:
//                    $typeApText = 'Кладовая';
//                    break;
//                case 124:
//                    $typeApText = 'Паркинг';
//                    break;
//                default:
//                    $typeApText = '';
//            }

//            $dealTest = $deal['result']['UF_CRM_1617025791625'];

//            dd(public_path('logo.png'));

//            $data = [
//                'title' => 'Привет',
//                'author' => "Проверка"
//            ];
//            $pdf = Facade::loadView('pdf', $data);

//            $pdf = $pdfDoc->loadView('pdf', $data);
            $options = new Options();
            $options->set('defaultFont', 'Times');
            $dompdf = new Dompdf($options);


            $amountMain = $planMainPayments->sum('amount');
            $amountContractor = $planContractPayments->sum('amount');
            $totalAmount = $amountMain + $amountContractor;

//            $pdf = new \Mpdf\Mpdf();
//            $logoInBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(storage_path('app/perspektivakrym/logo.png')));
//            $logoInBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(storage_path('app/perspektivakrym/logo.png')));
            $html = '';
//            <img src="data:image/png;base64,' . base64_encode(file_get_contents(storage_path('app/1576045789.png'))) . '" />
            // Проверяем существование логотипа
            $logoPath = storage_path('app/logo2.png');
            $logoHtml = '';
            if (file_exists($logoPath)) {
                $logoHtml = '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) . '"/>';
                Log::info("Логотип найден: {$logoPath}");
            } else {
                Log::warning("Логотип не найден: {$logoPath}, используем текстовую замену");
                $logoHtml = '<div style="font-size: 18px; font-weight: bold; color: #002060;">ПЕРСПЕКТИВА КРЫМ</div>';
            }
            
            $html = $html . '<table width="100%">
            <tr>
            <td width="50%">
            ' . $logoHtml . '
            </td>
            <td width="50%" style="text-align: right">
            ' . $dealGk . ', <br />' . $typeApId . ' №'. $ap . '<br>Дата расчета: ' . date('d.m.Y') . '
            </td>
            </tr>
            </table>';

            $html = $html . '<br />';

            $html = $html . '<table width="100%" border="1" style="border-collapse: collapse">';
//            Header
            $html = $html . '<thead style="background-color: #002060">';
            $html = $html . '<tr>';
            $html = $html . '<th width="5%">';
            $html = $html . '';
            $html = $html . '</th>';
            $html = $html . '<th width="20%" align="center" valign="center" style="color: #fff">';
            $html = $html . 'Тип платежа';
            $html = $html . '</th>';
            $html = $html . '<th width="20%" align="center" valign="center" style="color: #fff">';
            $html = $html . 'Сумма планового платежа';
            $html = $html . '</th>';
            $html = $html . '<th width="15%" align="center" valign="center" style="color: #fff">';
            $html = $html . 'Дата планового платежа';
            $html = $html . '</th>';
//            $html = $html . '<th width="20%" align="center" valign="center" style="color: #fff">';
//            $html = $html . 'Сумма фактического платежа';
//            $html = $html . '</th>';
            $html = $html . '<th width="40%" align="center" valign="center" style="color: #fff">';
            $html = $html . 'Номер договора';
            $html = $html . '</th>';
            $html = $html . '</tr>';
            $html = $html . '</thead>';
//            End Header
//            Body
            $html = $html . '<tbody>';
            $i = 0;
//            foreach ($planMainPayments as $pm) {
//                if ($pm->amount !== 0) {
//                    $type = ($pm->pay_type === 'down_payment') ? 'ПВ' : 'Регулярный платеж';
//                    $i++;
//                    $html = $html . '<tr>';
//                    $html = $html . '<th width="5%" align="center" valign="center" style="color: #000">';
//                    $html = $html . $i;
//                    $html = $html . '</th>';
//                    $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
//                    $html = $html . $type;
//                    $html = $html . '</th>';
//                    $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
//                    $html = $html . number_format($pm->amount, 2, '.', ' ');
//                    $html = $html . '</th>';
//                    $html = $html . '<th width="15%" align="center" valign="center" style="color: #000">';
//                    $html = $html . date('d.m.Y', strtotime($pm->date));
//                    $html = $html . '</th>';
////                $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
////                $html = $html . '0.00';
////                $html = $html . '</th>';
//                    $html = $html . '<th width="40%" align="center" valign="center" style="color: #000">';
//                    $html = $html . $pm->doc_number;
//                    $html = $html . '</th>';
//                    $html = $html . '</tr>';
//                }
//            }

//            foreach ($planContractPayments as $pc) {
//            if ($pc->amount) {
//                $type = ($pc->pay_type === 'down_payment') ? 'ПВ' : 'Регулярный платеж';
//                $i++;
//                $html = $html . '<tr>';
//                $html = $html . '<th width="5%" align="center" valign="center" style="color: #000">';
//                $html = $html . $i;
//                $html = $html . '</th>';
//                $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
//                $html = $html . $type;
//                $html = $html . '</th>';
//                $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
//                $html = $html . number_format($pc->amount, 2, '.', ' ');
//                $html = $html . '</th>';
//                $html = $html . '<th width="15%" align="center" valign="center" style="color: #000">';
//                $html = $html . date('d.m.Y', strtotime($pc->date));
//                $html = $html . '</th>';
////            $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
////            $html = $html . '0.00';
////            $html = $html . '</th>';
//                $html = $html . '<th width="40%" align="center" valign="center" style="color: #000">';
//                $html = $html . $pc->doc_number;
//                $html = $html . '</th>';
//                $html = $html . '</tr>';
//            }
//        }
            foreach ($planAllPayments as $pm) {
                if ($pm->amount !== 0) {
                    $type = ($pm->pay_type === 'down_payment') ? 'ПВ' : 'Регулярный платеж';
                    $i++;
                    $html = $html . '<tr>';
                    $html = $html . '<th width="5%" align="center" valign="center" style="color: #000">';
                    $html = $html . $i;
                    $html = $html . '</th>';
                    $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
                    $html = $html . $type;
                    $html = $html . '</th>';
                    $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
                    $html = $html . number_format($pm->amount, 2, '.', ' ');
                    $html = $html . '</th>';
                    $html = $html . '<th width="15%" align="center" valign="center" style="color: #000">';
                    $html = $html . date('d.m.Y', strtotime($pm->date));
                    $html = $html . '</th>';
//                $html = $html . '<th width="20%" align="center" valign="center" style="color: #000">';
//                $html = $html . '0.00';
//                $html = $html . '</th>';
                    $html = $html . '<th width="40%" align="center" valign="center" style="color: #000">';
                    $html = $html . $pm->doc_number;
                    $html = $html . '</th>';
                    $html = $html . '</tr>';
                }
            }

            $html = $html . '<th align="right" valign="center" style="color: #000" colspan="5">';
            $html = $html . 'ИТОГО: ' . number_format($totalAmount, 2, '.', ' ');
            $html = $html . '</th>';
            $html = $html . '</tr>';

            $html = $html . '</tbody>';
//            End Body
            $html = $html . '</table>';

            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4', 'landscape');

            $dompdf->render();

            Log::info("=== ЗАВЕРШЕНИЕ СОЗДАНИЯ ОБЩЕГО PDF ===");
            Log::info("✓ PDF файл готов к скачиванию");
            
            $dompdf->stream();

//            $base64  = file_get_contents(storage_path('app/perspektivakrym/logo.png'));
//            ob_start();
//            $base64 = 'data:image/png;base64,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';
            //шапка
//            echo '<div style="display: flex; justify-content: space-around; font-family: \'Times New Roman\'; font-size: 12px;">' .
//               '<div><img src="' . $base64 . '" style="width: 300px; height: auto; margin: 0;" /></div>' .
//               '<div style="text-align: right">'.$dealGk. ', <br />Апартамент #'. $ap . '<br>Дата расчета: ' . date('d.m.Y') . '</div>'.
//            '</div>';
//            dd( public_path('logo.png'));
//            dd());
//            dd($deal['result'], $storage_path('/app/perspektiva/logo.png'serData);
//            $html = ob_get_contents();
//            ob_end_clean();
//            $pdf->Image(storage_path('app/perspektiva/logo.png'), 0, 0, 200, 100, 'png', '' );
//            return $html;
//            $pdf->WriteHTML($html);
//
//            return $pdf->Output('123.pdf', 'D');

        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }

    }

    /**
     * Блокировка платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function block(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $operationId = $request->get('operation_id');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $this->mPlanPayment->where('id', $operationId)->update(['blocked' => $this->mPlanPayment::BLOCK]);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Разблокировка платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function unblock(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $operationId = $request->get('operation_id');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $this->mPlanPayment->where('id', $operationId)->update(['blocked' => $this->mPlanPayment::ACTIVE]);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Синхронизация с 1С
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function syncWith1S(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $this->mFactPayment
                ->where('deal_id', $dealId)
                ->where('type', $this->mFactPayment::С1)
                ->delete();

            $planPayments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->get();

            $docNumbers = [];
            foreach ($planPayments->groupBy('doc_number') as $key => $value) {
                $docNumbers[] = trim($key);
            }

            foreach ($docNumbers as $number) {
                $factPayments = DB::connection('perspektivakrym_remote')
                    ->table('ViewДокументыДДСкратко')
                    ->where('Договор', 'LIKE', '%' . $number)
                    ->orderBy('ДокументДата', 'asc')
                    ->get();

                foreach ($factPayments as $p) {
                    $this->mFactPayment->create([
                        'payment_id' => $p->ДокументGUID,
                        'type' => $this->mFactPayment::С1,
                        'date' => $p->ДокументДата,
                        'contractor' => $p->Контрагент,
                        'doc_number' => $number,
                        'amount' => $p->Сумма,
                        'deal_id' => $dealId,
                    ]);
                }
            }

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    public function getExcelForLawyer(Request $request)
    {
        try {
            Log::info("=== НАЧАЛО СОЗДАНИЯ EXCEL ФАЙЛА ===");
            
            ini_set('memory_limit', '-1');
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $type = $request->get('type');
            $numberGraph = $request->get('number_graph', 0);
            $fileName = $type . '_' . time() . '.xlsx';
            
            Log::info("Сделка ID: {$dealId}");
            Log::info("Тип файла: {$type}");
            Log::info("Номер графика: {$numberGraph}");
            Log::info("Имя файла: {$fileName}");

            if (!$this->checkApp($auth)) {
                Log::error("ОШИБКА: Приложение не авторизовано");
                throw new \DomainException('Приложение не авторизовано');
            }
            
            Log::info("✓ Авторизация прошла успешно");

            $fullName = storage_path('app/perspektivakrym') . '/' . $fileName;
            
            // Создаем директорию, если она не существует
            $directory = storage_path('app/perspektivakrym');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
                Log::info("Создана директория для Excel файлов: {$directory}");
            }

            $titleStyle = [
                'font' => [
                    'name' => 'Arial',
                    'bold' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => '000000'
                        ]
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ]
            ];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Тип платежа');
            $sheet->getStyle('A1')->applyFromArray($titleStyle);

            $sheet->setCellValue('B1', 'Сумма планового платежа');
            $sheet->getStyle('B1')->applyFromArray($titleStyle);

            $sheet->setCellValue('C1', 'Дата планового платежа');
            $sheet->getStyle('C1')->applyFromArray($titleStyle);

            $sheet->setCellValue('D1', 'Номер договора');
            $sheet->getStyle('D1')->applyFromArray($titleStyle);

            $writer = new Xlsx($spreadsheet);

            Log::info("Сохранение Excel файла: {$fullName}");
            $writer->save($fullName);
            Log::info("✓ Excel файл создан успешно");

            if ($type === 'land') {
                Log::info("Обработка платежей по подряду (land)");
                $planContractPayments = $this->mPlanPayment
                    ->where('deal_id', $dealId)
                    ->where('type', 'contract')
                    ->where('number_graph', $numberGraph)
                    ->orderBy('date')
                    ->get();
                Log::info("Найдено платежей по подряду: " . $planContractPayments->count());
                $i = 2;

                foreach ($planContractPayments as $p) {
                    if ($p->amount !== 0) {
                        $spreadsheet = IOFactory::load($fullName);
                        $worksheet = $spreadsheet->getActiveSheet();

                        $worksheet->getCell('A'.$i)->setValue(($p->pay_type === 'down_payment') ? 'ПВ' : 'Регулярный платеж');
                        $worksheet->getCell('B'.$i)->setValue(number_format($p->amount, 2, '.', ' '));
                        $worksheet->getCell('C'.$i)->setValue(date('d.m.Y', strtotime($p->date)));
                        $worksheet->getCell('D'.$i)->setValue($p->doc_number);

                        $worksheet->getStyle('A'.$i.':D'.$i)
                            ->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ],
                                ],
                            ]);
                        $i++;
                    }
                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save($fullName);
                }
            }

            if ($type === 'general') {
                Log::info("Обработка платежей по основному договору (general)");
                $planContractPayments = $this->mPlanPayment
                    ->where('deal_id', $dealId)
                    ->where('type', 'main')
                    ->where('number_graph', $numberGraph)
                    ->orderBy('date')
                    ->get();
                Log::info("Найдено платежей по основному договору: " . $planContractPayments->count());
                $i = 2;


                foreach ($planContractPayments as $p) {
                    if ($p->amount !== 0) {
                        $spreadsheet = IOFactory::load($fullName);
                        $worksheet = $spreadsheet->getActiveSheet();

                        $worksheet->getCell('A'.$i)->setValue(($p->pay_type === 'down_payment') ? 'ПВ' : 'Регулярный платеж');
                        $worksheet->getCell('B'.$i)->setValue(number_format($p->amount, 2, '.', ' '));
                        $worksheet->getCell('C'.$i)->setValue(date('d.m.Y', strtotime($p->date)));
                        $worksheet->getCell('D'.$i)->setValue($p->doc_number);

                        $worksheet->getStyle('A'.$i.':D'.$i)
                            ->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ],
                                ],
                            ]);
                        $i++;
                    }
                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save($fullName);
                }
            }



            chmod($fullName, 0750);
            Log::info("Установлены права доступа к файлу: 0750");

            Log::info("=== ЗАВЕРШЕНИЕ СОЗДАНИЯ EXCEL ФАЙЛА ===");
            Log::info("✓ Файл готов к скачиванию: {$fullName}");
            
            return response()->download($fullName);

        } catch (\DomainException $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }


    /**
     * Первоначальный взнос по основному договору
     * @param $dealId - ID сделки
     * @param $docNumber - номер документа
     * @param $amount - сумма первоначального взноса
     * @param $dateStart - дата подписания основного договора
     * @param $days - через сколько дней должен быть внесен ПВ
     */
    protected function mainDownPaymentCalculate($dealId, $docNumber, $amount, $dateStart, $days, $numberGraph = 0)
    {
        $paymentDate = Carbon::create($dateStart)->addDays($days)->format('Y-m-d');
        
        Log::info("Создание ПВ по основному договору:");
        Log::info("  Сделка ID: {$dealId}");
        Log::info("  Номер документа: {$docNumber}");
        Log::info("  Сумма ПВ: {$amount}");
        Log::info("  Дата начала: {$dateStart}");
        Log::info("  Дней на ПВ: {$days}");
        Log::info("  Дата платежа: {$paymentDate}");
        Log::info("  Номер графика: {$numberGraph}");
        
        $this->mPlanPayment->create([
            'deal_id' => $dealId,
            'type' => $this->mPlanPayment::MAIN,
            'pay_type' => $this->mPlanPayment::DOWN_PAYMENT,
            'doc_number' => $docNumber,
            'amount' => $amount,
            'date' => $paymentDate,
            'blocked' => $this->mPlanPayment::BLOCK,
            'note' => '',
            'order' => 1,
            'number_graph' => $numberGraph,
        ]);
        
        Log::info("✓ ПВ по основному договору создан");
    }

    /**
     * Плановые платежи по основному договору
     * @param $dealId - ID сделки
     * @param $docNumber - номер документа
     * @param $allAmount - общая сумма основного договора
     * @param $firstAmount - ПВ основного договора
     * @param $dateStart - дата подписания основного договора
     * @param $days - чрезе сколько дней должен быть внесен ПВ
     * @param $count - кол-во месяцев
     * @param $period - период
     */
    protected function mainRegularPaymentCalculate($dealId, $docNumber, $allAmount, $firstAmount, $dateStart, $days, $count, $period, $finishDate, $numberGraph = 0)
    {
        Log::info("Создание регулярных платежей по основному договору:");
        Log::info("  Сделка ID: {$dealId}");
        Log::info("  Номер документа: {$docNumber}");
        Log::info("  Общая сумма: {$allAmount}");
        Log::info("  Сумма ПВ: {$firstAmount}");
        Log::info("  Дата начала: {$dateStart}");
        Log::info("  Дней на ПВ: {$days}");
        Log::info("  Количество платежей: {$count}");
        Log::info("  Период: {$period}");
        Log::info("  Дата окончания: {$finishDate}");
        Log::info("  Номер графика: {$numberGraph}");
        
        $dateStart = Carbon::create($dateStart)->addDays($days)->format('Y-m-d');
        $dateStart = $this->getPlanDate($dateStart, $period);
        Log::info("  Дата первого платежа: {$dateStart}");

//        Кол-во мы стали передавать как аргумент
//        $count = $this->getCountPayment($count, $period);
        $amount = ceil(($allAmount - $firstAmount) / $count);
        Log::info("  Формула: ceil((Общая сумма - ПВ) / Количество) = ceil(({$allAmount} - {$firstAmount}) / {$count}) = {$amount}");

        if ($count == 1) {
            Log::info("  Только один платеж, создаем его на всю оставшуюся сумму");
            $this->mPlanPayment->create([
                'deal_id' => $dealId,
                'type' => $this->mPlanPayment::MAIN,
                'pay_type' => $this->mPlanPayment::REGULAR_PAYMENT,
                'doc_number' => $docNumber,
                'amount' => ($allAmount - $firstAmount),
                'date' => $dateStart,
                'blocked' => $this->mPlanPayment::ACTIVE,
                'note' => '',
                'order' => 1,
                'number_graph' => $numberGraph,
            ]);
            Log::info("✓ Единственный регулярный платеж создан");
            return;
        }

        Log::info("  Создание {$count} регулярных платежей по {$amount} каждый");
        
        for ($i = 1; $i < $count; $i++ ) {
            Log::info("  Создание платежа {$i} на сумму {$amount} с датой {$dateStart}");
            $this->mPlanPayment->create([
                'deal_id' => $dealId,
                'type' => $this->mPlanPayment::MAIN,
                'pay_type' => $this->mPlanPayment::REGULAR_PAYMENT,
                'doc_number' => $docNumber,
                'amount' => $amount,
                'date' => $dateStart,
                'blocked' => $this->mPlanPayment::ACTIVE,
                'note' => '',
                'order' => 1,
                'number_graph' => $numberGraph,
            ]);
            $dateStart = $this->getPlanDate($dateStart, $period);
        }

        $lastAmount = $allAmount - $firstAmount - ($amount * ($count - 1));
        Log::info("  Формула последнего платежа: Общая сумма - ПВ - (Сумма платежа × (Количество - 1))");
        Log::info("  Формула: {$allAmount} - {$firstAmount} - ({$amount} × ({$count} - 1)) = {$lastAmount}");
        Log::info("  Создание последнего платежа на сумму {$lastAmount} с датой {$dateStart}");
        
        $this->mPlanPayment->create([
            'deal_id' => $dealId,
            'type' => $this->mPlanPayment::MAIN,
            'pay_type' => $this->mPlanPayment::REGULAR_PAYMENT,
            'doc_number' => $docNumber,
            'amount' => $lastAmount,
            'date' => $dateStart,
            'blocked' => $this->mPlanPayment::ACTIVE,
            'note' => '',
            'order' => 1,
            'number_graph' => $numberGraph,
        ]);
        
        Log::info("✓ Все регулярные платежи по основному договору созданы");
        return;
    }

    /**
     * Первоначальный взнос по подряду
     * @param $dealId - ID сделки
     * @param $docNumber - номер договора
     * @param $amount - сумма ПВ по подряду
     * @param $dateStart - дата подписания договра подряда
     * @param $days - через сколько дней должен быть внесен ПВ
     * Дата пв подряда = дата пв земли.
     */
    protected function contractDownPaymentCalculate($dealId, $docNumber, $amount, $dateStart, $days, $numberGraph = 0)
    {
        Log::info("Создание ПВ по подряду:");
        Log::info("  Сделка ID: {$dealId}");
        Log::info("  Номер документа: {$docNumber}");
        Log::info("  Сумма ПВ: {$amount}");
        Log::info("  Дата начала: {$dateStart}");
        Log::info("  Дней на ПВ: {$days}");
        Log::info("  Дата платежа: {$dateStart}");
        Log::info("  Номер графика: {$numberGraph}");

        $this->mPlanPayment->create([
            'deal_id' => $dealId,
            'type' => $this->mPlanPayment::CONTRACT,
            'pay_type' => $this->mPlanPayment::DOWN_PAYMENT,
            'doc_number' => $docNumber,
            'amount' => $amount,
//            'date' => Carbon::create($dateStart)->addDays($days)->format('Y-m-d'),
            'date' => $dateStart,
            'blocked' => $this->mPlanPayment::BLOCK,
            'note' => '',
            'order' => 1,
            'number_graph' => $numberGraph,
        ]);
        
        Log::info("✓ ПВ по подряду создан");
    }

    /**
     * Плановые платежи по подряду
     * @param $dealId - ID сделки
     * @param $docNumber - номер договора
     * @param $allAmount - общая сумма подряда
     * @param $firstAmount - ПВ подряда
     * @param $dateStart - дата подписания подряда
     * @param $days - через сколько дне должен быть внесен ПВ по подряду
     * @param $count - кол-во месяцев
     * @param $period - период
     */
    protected function contractRegularPaymentCalculate($dealId, $docNumber, $allAmount, $firstAmount, $dateStart, $days, $count, $period, $numberGraph = 0)
    {
        Log::info("Создание регулярных платежей по подряду:");
        Log::info("  Сделка ID: {$dealId}");
        Log::info("  Номер документа: {$docNumber}");
        Log::info("  Общая сумма: {$allAmount}");
        Log::info("  Сумма ПВ: {$firstAmount}");
        Log::info("  Дата начала: {$dateStart}");
        Log::info("  Дней на ПВ: {$days}");
        Log::info("  Количество платежей: {$count}");
        Log::info("  Период: {$period}");
        Log::info("  Номер графика: {$numberGraph}");
        
        $dateStart = Carbon::create($dateStart)->addDays($days)->format('Y-m-d');
        $dateStart = $this->getPlanDate($dateStart, $period);
        Log::info("  Дата первого платежа: {$dateStart}");

//        Кол-во мы стали передавать как аргумент
//        $count = $this->getCountPayment($count, $period);

        $amount = ceil(($allAmount - $firstAmount) / $count);
        Log::info("  Формула: ceil((Общая сумма - ПВ) / Количество) = ceil(({$allAmount} - {$firstAmount}) / {$count}) = {$amount}");

        if ($count == 1) {
            Log::info("  Только один платеж, создаем его на всю оставшуюся сумму");
            $this->mPlanPayment->create([
                'deal_id' => $dealId,
                'type' => $this->mPlanPayment::CONTRACT,
                'pay_type' => $this->mPlanPayment::REGULAR_PAYMENT,
                'doc_number' => $docNumber,
                'amount' => ($allAmount - $firstAmount),
                'date' => $dateStart,
                'blocked' => $this->mPlanPayment::ACTIVE,
                'note' => '',
                'order' => 1,
                'number_graph' => $numberGraph,
            ]);
            Log::info("✓ Единственный регулярный платеж по подряду создан");
            return;
        }

        Log::info("  Создание {$count} регулярных платежей по подряду по {$amount} каждый");
        
        for ($i = 1; $i < $count; $i++ ) {
            Log::info("  Создание платежа по подряду {$i} на сумму {$amount} с датой {$dateStart}");
            $this->mPlanPayment->create([
                'deal_id' => $dealId,
                'type' => $this->mPlanPayment::CONTRACT,
                'pay_type' => $this->mPlanPayment::REGULAR_PAYMENT,
                'doc_number' => $docNumber,
                'amount' => $amount,
                'date' => $dateStart,
                'blocked' => $this->mPlanPayment::ACTIVE,
                'note' => '',
                'order' => 1,
                'number_graph' => $numberGraph,
            ]);
            $dateStart = $this->getPlanDate($dateStart, $period);
        }

        $lastAmount = $allAmount - $firstAmount - ($amount * ($count - 1));
        Log::info("  Формула последнего платежа по подряду: Общая сумма - ПВ - (Сумма платежа × (Количество - 1))");
        Log::info("  Формула: {$allAmount} - {$firstAmount} - ({$amount} × ({$count} - 1)) = {$lastAmount}");
        Log::info("  Создание последнего платежа по подряду на сумму {$lastAmount} с датой {$dateStart}");
        
        $this->mPlanPayment->create([
            'deal_id' => $dealId,
            'type' => $this->mPlanPayment::CONTRACT,
            'pay_type' => $this->mPlanPayment::REGULAR_PAYMENT,
            'doc_number' => $docNumber,
            'amount' => $lastAmount,
            'date' => $dateStart,
            'blocked' => $this->mPlanPayment::ACTIVE,
            'note' => '',
            'order' => 1,
            'number_graph' => $numberGraph,
        ]);
        
        Log::info("✓ Все регулярные платежи по подряду созданы");
        return;
    }

    /**
     * генерация PDF для Парк Плаза
     * @param $name
     * @param $data
     * @param $fullName
     * @return string
     * @throws \Mpdf\MpdfException
     */
    protected function generatePdfPP($name, $data, $fullName)
    {
        $pdf = new \Mpdf\Mpdf();
//        $pdf->AddPage('P','NEXT-ODD', '10');

        $pdf->SetHTMLFooter('
            <table width="100%">
                <tr>
                    <td width="45%" style="text-align: left;">Застройщик  _______________</td>
                    <td width="45%" style="text-align: left;">Участник  _______________</td>
                    <td width="10%" style="text-align: right;">{PAGENO}</td>
    </tr>
</table>');

        $data = $data->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)->sortBy('date');

        $docNumber = $data[count($data) - 1]->doc_number;
        $i = 1;

        ob_start();
        echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
            Приложение № ' . $name . ' к Договору <br />
            участия в долевом строительстве <br />
            № ' . $docNumber . '
        </div>';
        echo '<br /><br />';
        echo '<div style="text-align: center;"><strong>ГРАФИК ПЛАТЕЖЕЙ</strong></div>';

        echo '<br /><br />';
        echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
        echo '<tr>';
        echo '<th width="10%">№ <br />П/П</th>';
        echo '<th width="30%">Дата платежа</th>';
        echo '<th width="60%">Сумма платежа, руб</th>';
        echo '</tr>';
        foreach ($data as $d) {
//            if ($d->number === 1) {
//                continue;
//            }

            // Склонение слова "рубль".
            if ($d->amount !== 0) {
                $rub = $this->getRub($d->amount);

                echo '<tr>';
                echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';

                if ($d->is_text_date) {
                    echo '<td style="text-align: center; vertical-align: middle">' . $d->text_date . '</td>';
                } else {
                    $planDate = date('d.m.Y', strtotime($d->date));
                    echo '<td style="text-align: center; vertical-align: middle">' . $planDate . '</td>';
                }

                echo '<td style="text-align: center; vertical-align: middle">';
                echo number_format($d->amount, 2, ',', ' ');
                echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';
                echo '</tr>';
            }
        }
        echo '</table>';

        echo '<br /><br />';
        echo '<table width="100%" style="margin-left: 20px">';
        echo '<tr>';
        echo '<td style="text-align: left; width: 50%"><strong>ОТ ЗАСТРОЙЩИКА</strong></td>';
        echo '<td style="text-align: left; width: 50%"><strong>УЧАСТНИК</strong></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td style="text-align: left; width: 50%">______________<strong>О.В. Малозёмов</strong></td>';
        echo '<td style="text-align: left; width: 50%">______________<strong>' . $fullName . '</strong></td>';
        echo '</tr>';

        echo '</table>';

        $html = ob_get_contents();
        ob_end_clean();

        $pdf->WriteHTML($html);

        return $pdf->Output($docNumber . '.pdf', 'D');
    }

    /**
     * генерация PDF для Паруса Мечты без Бумажная
     * @param $name
     * @param $data
     * @param $type
     * @param $userData
     * @return string
     * @throws \Mpdf\MpdfException
     */
    protected function generatePdfPmWithoutB($name, $data, $type, $userData)
    {
        $pdf = new \Mpdf\Mpdf();

        $html = '';

        //земля
        if ($type == $this->mPlanPayment::MAIN) {
            $data = $data->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)->sortBy('date');

            $docNumber = $data[count($data) - 1]->doc_number;

            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору <br />
                купли-продажи земельного участка <br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>ГРАФИК ПЛАТЕЖЕЙ</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="30%">Дата платежа</th>';
            echo '<th width="60%">Сумма платежа, руб</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount !== 0) {
                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';


                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> Не позднее ' . $planDate . ' г.</td>';
                    }


                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';
                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>ИТОГО:</strong></td>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек</td>';

            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"><strong>Продавец</strong></td>';
            echo '<td style="text-align: left; width: 50%"><strong>Покупатель</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">' . config("perspektivakrym.land." . $userData['ENTITY'] . '.text') . '</td>';
            echo '<td style="text-align: left; width: 50%">' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' .  $userData['SECOND_NAME'] . '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . config("perspektivakrym.land." . $userData['ENTITY'] . '.name') .  '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }

        //подряд
        if ($type == $this->mPlanPayment::CONTRACT) {
            $data = $data->sortBy('date');
            $docNumber = $data[count($data) - 1]->doc_number;
            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору <br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>График производства оплат</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="60%">Сумма, подлежащая к оплате, руб</th>';
            echo '<th width="30%">Срок оплаты</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount !== 0) {
//                if ($d->number === 1) {
//                    continue;
//                }

                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';

                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo 'аванс ' . number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';

                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> до ' . $planDate . ' года</td>';
                    }


                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="3"><strong>Итого:';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек';

            if($userData['ENTITY'] === 'Новый дом ИЛИ Профстрой (Паруса)') {
                echo ', в том числе НДС 20%.';
            };

            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: center; width: 50%"><strong>ЗАКАЗЧИК:</strong></td>';
            echo '<td style="text-align: center; width: 50%"><strong>ПОДРЯДЧИК:</strong></td>';
            echo '</tr>';
            echo '<tr>';
            //данные по заказчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">';
            echo '<strong>' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' . $userData['SECOND_NAME'] . '</strong>, <br />';
            $sex = '';
            if ($userData['SEX'] == 2096) {
                $sex = 'муж.';
            }
            if ($userData['SEX'] == 2098) {
                $sex = 'жен.';
            }
            echo (is_null($userData['BIRTHDATE']) || $userData['BIRTHDATE'] == '')? '' : date('Y.m.d', strtotime($userData['BIRTHDATE'])) . ' года рождения, <br />пол ' . $sex . ', <br />место рождения: ' . $userData['CITY_BIRTH'] . ', <br />';
            echo 'паспорт гражданина ' . $userData['PASSPORT_COUNTRY'] . ' серии ' . $userData['PASSPORT_SERIES'] . ' номер ' . $userData['PASSPORT_NUMBER'] . ' выдан ' . $userData['PASSPORT_ORGAN'] . ', ';
            echo 'дата выдачи ' . (is_null($userData['PASSPORT_DATE']) || $userData['PASSPORT_DATE'] == '')? '' : date('Y.m.d', strtotime($userData['PASSPORT_DATE'])) . ', код подразделения: ' . $userData['PASSPORT_ORGAN_CODE'] . ', <br />';
            echo 'СНИЛС ' . $userData['SNILS'] . ', <br />зарегистрирован по адресу: ' . $userData['ADDRESS'] . ', <br />';
            echo 'тел. ' . $userData['PHONE'];
            echo '</td>';
            //данные по подрядчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">
<strong>Общество с ограниченной ответственностью «ПРОФСТРОЙ»</strong><br />
Юр. адрес: 298612, РК, г. Ялта, ул. Кривошты, дом 6А, помещение 1,<br />
ОГРН: 1209100008644,<br />
ИНН: 9103093885, КПП: 910301001<br />
р/с: 40702810742580000032 в РНКБ БАНК (ПАО), к/с: 30101810335100000607,<br />
БИК: 043510607

            </td>';
            echo '</tr>';

            echo '<tr><td><strong>Заказчик</strong></td><td><strong>Директор</strong></td></tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>А.И.Шишов</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"></td>';
            echo '<td style="text-align: left; width: 50%">М.П.</td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }

        $pdf->WriteHTML($html);

        return $pdf->Output($docNumber . '.pdf', 'D');
    }

    /**
     * генерация PDF для Паруса Мечты с Бумажная
     * @param $name
     * @param $data
     * @param $type
     * @param $userData
     * @return string
     * @throws \Mpdf\MpdfException
     */
    protected function generatePdfPmWithB($name, $data, $type, $userData)
    {
        $pdf = new \Mpdf\Mpdf();

        $html = '';

        //земля
        if ($type == $this->mPlanPayment::MAIN) {
            $data = $data->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)->sortBy('date');

            $docNumber = $data[count($data) - 1]->doc_number;

            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору <br />
                купли-продажи земельного участка <br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>ГРАФИК ПЛАТЕЖЕЙ</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="30%">Дата платежа</th>';
            echo '<th width="60%">Сумма платежа, руб</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount !== 0) {
//                if ($d->number === 1) {
//                    continue;
//                }

                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';
                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> Не позднее' . $planDate . ' г.</td>';
                    }

                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';
                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>ИТОГО:</strong></td>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек</td>';
            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: left; width: 100%"><strong>ПРОДАВЕЦ:</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 100%">________________________________________________________________________________________________</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 100%"><strong>ПОКУПАТЕЛЬ:</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 100%">________________________________________________________________________________________________</td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }
        //подряд
        if ($type == $this->mPlanPayment::CONTRACT) {
            $data = $data->sortBy('date');

            $docNumber = $data[count($data) - 1]->doc_number;
            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору <br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>График производства оплат</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="60%">Сумма, подлежащая к оплате, руб</th>';
            echo '<th width="30%">Срок оплаты</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount !== 0) {
//                if ($d->number === 1) {
//                    continue;
//                }

                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';

                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo 'аванс ' . number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';
                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> до ' . $planDate . ' года</td>';
                    }

                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="3"><strong>Итого:';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек';

            if($userData['ENTITY'] === 'Новый дом ИЛИ Профстрой (Паруса)') {
                echo ', в том числе НДС 20%.';
            }

            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: center; width: 50%"><strong>ЗАКАЗЧИК:</strong></td>';
            echo '<td style="text-align: center; width: 50%"><strong>ПОДРЯДЧИК:</strong></td>';
            echo '</tr>';
            echo '<tr>';
            //данные по заказчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">';
            echo '<strong>' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' . $userData['SECOND_NAME'] . '</strong>, <br />';
            $sex = '';
            if ($userData['SEX'] == 2096) {
                $sex = 'муж.';
            }
            if ($userData['SEX'] == 2098) {
                $sex = 'жен.';
            }
            echo (is_null($userData['BIRTHDATE']) || $userData['BIRTHDATE'] == '')? '' : date('Y.m.d', strtotime($userData['BIRTHDATE'])) . ' года рождения, <br />пол ' . $sex . ', <br />место рождения: ' . $userData['CITY_BIRTH'] . ', <br />';
            echo 'паспорт гражданина ' . $userData['PASSPORT_COUNTRY'] . ' серии ' . $userData['PASSPORT_SERIES'] . ' номер ' . $userData['PASSPORT_NUMBER'] . ' выдан ' . $userData['PASSPORT_ORGAN'] . ', ';
            echo 'дата выдачи ' . (is_null($userData['PASSPORT_DATE']) || $userData['PASSPORT_DATE'] == '')? '' : date('Y.m.d', strtotime($userData['PASSPORT_DATE'])) . ', код подразделения: ' . $userData['PASSPORT_ORGAN_CODE'] . ', <br />';
            echo 'СНИЛС ' . $userData['SNILS'] . ', <br />зарегистрирован по адресу: ' . $userData['ADDRESS'] . ', <br />';
            echo 'тел. ' . $userData['PHONE'];
            echo '</td>';
            //данные по подрядчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">
<strong>Общество с ограниченной ответственностью «ПРОФСТРОЙ»</strong><br />
Юр. адрес: 298612, РК, г. Ялта, ул. Кривошты, дом 6А, помещение 1,<br />
ОГРН: 1209100008644,<br />
ИНН: 9103093885, КПП: 910301001<br />
р/с: 40702810742580000032 в РНКБ БАНК (ПАО), к/с: 30101810335100000607,<br />
БИК: 043510607

            </td>';
            echo '</tr>';

            echo '<tr><td><strong>Заказчик</strong></td><td><strong>Директор</strong></td></tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>А.И.Шишов</strong></td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"></td>';
            echo '<td style="text-align: left; width: 50%">М.П.</td>';
            echo '</tr>';

            echo '</table>';

            $html = ob_get_contents();
            ob_end_clean();

        }

        $pdf->WriteHTML($html);

        return $pdf->Output($docNumber . '.pdf', 'D');


        //-------------------------------------------------

//        $pdf->SetHTMLFooter('
//            <table width="100%">
//                <tr>
//                    <td width="50%" style="text-align: left;">ЗАКАЗЧИК:</td>
//                    <td width="50%" style="text-align: left;">ПОДРЯДЧИК:</td>
//                </tr>
//                <tr>
//                    <td>
//
//                    </td>
//                    <td>
//                        <strong>Общество с ограниченной ответственностью <br /> «ПРОФСТРОЙ»</strong><br>
//                        Юр. адрес: 298612, РК, г. Ялта, ул. Кривошты, дом 6А, помещение 1,
//ОГРН: 1209100008644,
//ИНН: 9103093885, КПП: 910301001
//р/с: 40702810742580000032 в РНКБ БАНК (ПАО), к/с: 30101810335100000607,
//БИК: 043510607
//
//                    </td>
//                </tr>
//                <tr>
//                    <td></td>
//                    <td>
//                    <strong>Директор</strong><br />
//                    _______________________ <strong>А.И.Шишов</strong>
//                    </td>
//                </tr>
//            </table>');



//        if ($type === 1) {
//
//        } else {
//            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
//                Приложение № ' . $name . ' к Договору <br />
//                № ' . $docNumber . '
//            </div>';
//        }
//
//
//        if ($type === 1) {
//
//        } else {
//            echo '<div style="text-align: center;"><strong>График производства оплат</strong></div>';
//        }




//        echo '<br /><br />';




    }

    /**
     * генерация PDF для Династия без Бумажная
     * @param $name
     * @param $data
     * @param $type
     * @param $userData
     * @return string
     * @throws \Mpdf\MpdfException
     */
    protected function generatePdfDinastiyaWithoutB($name, $data, $type, $userData)
    {
        $pdf = new \Mpdf\Mpdf();

        $html = '';

        //земля
        if ($type == $this->mPlanPayment::MAIN) {
            $data = $data->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)->sortBy('date');

            $docNumber = $data[count($data) - 1]->doc_number;

            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору <br />
                купли-продажи земельного участка <br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>ГРАФИК ПЛАТЕЖЕЙ</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="30%">Дата платежа</th>';
            echo '<th width="60%">Сумма платежа, руб</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount) {
                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';


                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> Не позднее ' . $planDate . ' г.</td>';
                    }


                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';
                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>ИТОГО:</strong></td>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек</td>';

            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"><strong>Продавец</strong></td>';
            echo '<td style="text-align: left; width: 50%"><strong>Покупатель</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">' . config("perspektivakrym.land." . $userData['ENTITY'] . '.text') . '</td>';
            echo '<td style="text-align: left; width: 50%">' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' .  $userData['SECOND_NAME'] . '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . config("perspektivakrym.land." . $userData['ENTITY'] . '.name') .  '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }

        //подряд
        if ($type == $this->mPlanPayment::CONTRACT) {
            $data = $data->sortBy('date');
            $docNumber = $data[count($data) - 1]->doc_number;
            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору подряда<br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>График производства оплат</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="60%">Сумма, подлежащая к оплате, руб</th>';
            echo '<th width="30%">Срок оплаты</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount) {
//                if ($d->number === 1) {
//                    continue;
//                }

                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';

                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo 'аванс ' . number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';

                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> до ' . $planDate . ' года</td>';
                    }


                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="3"><strong>Итого:';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек';

            if ($userData['ENTITY'] === 'Династия - ООО Перспектива') {
                echo ', в том числе НДС 20%.';
            }
            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: center; width: 50%"><strong>ЗАКАЗЧИК:</strong></td>';
            echo '<td style="text-align: center; width: 50%"><strong>ПОДРЯДЧИК:</strong></td>';
            echo '</tr>';
            echo '<tr>';
            //данные по заказчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">';
            echo '<strong>' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' . $userData['SECOND_NAME'] . '</strong>, <br />';
            $sex = '';
            if ($userData['SEX'] == 2096) {
                $sex = 'муж.';
            }
            if ($userData['SEX'] == 2098) {
                $sex = 'жен.';
            }
            echo (is_null($userData['BIRTHDATE']) || $userData['BIRTHDATE'] == '')? '' : date('Y.m.d', strtotime($userData['BIRTHDATE'])) . ' года рождения, <br />пол ' . $sex . ', <br />место рождения: ' . $userData['CITY_BIRTH'] . ', <br />';
            echo 'паспорт гражданина ' . $userData['PASSPORT_COUNTRY'] . ' серии ' . $userData['PASSPORT_SERIES'] . ' номер ' . $userData['PASSPORT_NUMBER'] . ' выдан ' . $userData['PASSPORT_ORGAN'] . ', ';
            echo 'дата выдачи ' . (is_null($userData['PASSPORT_DATE']) || $userData['PASSPORT_DATE'] == '')? '' : date('Y.m.d', strtotime($userData['PASSPORT_DATE'])) . ', код подразделения: ' . $userData['PASSPORT_ORGAN_CODE'] . ', <br />';
            echo 'СНИЛС ' . $userData['SNILS'] . ', <br />зарегистрирован по адресу: ' . $userData['ADDRESS'] . ', <br />';
            echo 'тел. ' . $userData['PHONE'];
            echo '</td>';
            //данные по подрядчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">
<strong>Общество с ограниченной ответственностью «ПЕРСПЕКТИВА»</strong><br />
Юр. адрес: 298612, Республика Крым, г. Ялта, ул. Московская, дом № 33-А, квартира 2,<br />
ОГРН:1159102113917;<br />
ИНН: 9103075830, <br />
КПП: 910301001<br />
р/с.: 40702810200000005426, Банк: ООО "ЖИВАГО БАНК", БИК: 046126744, Корр. счет: 30101810700000000744

            </td>';
            echo '</tr>';

            echo '<tr><td><strong>Заказчик</strong></td><td><strong>Директор</strong></td></tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>А.Н.Мезенцев</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"></td>';
            echo '<td style="text-align: left; width: 50%">М.П.</td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }

        $pdf->WriteHTML($html);

        return $pdf->Output($docNumber . '.pdf', 'D');
    }

    /**
     * генерация PDF для LUCHI без Бумажная
     * @param $name
     * @param $data
     * @param $type
     * @param $userData
     * @return string
     * @throws \Mpdf\MpdfException
     */
    protected function generatePdfLuchiWithoutB($name, $data, $type, $userData)
    {
        $pdf = new \Mpdf\Mpdf();

        $html = '';

        //земля
        if ($type == $this->mPlanPayment::MAIN) {
            $data = $data->where('pay_type', $this->mPlanPayment::REGULAR_PAYMENT)->sortBy('date');

            $docNumber = $data[count($data) - 1]->doc_number;

            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору <br />
                купли-продажи земельного участка <br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>ГРАФИК ПЛАТЕЖЕЙ</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="30%">Дата платежа</th>';
            echo '<th width="60%">Сумма платежа, руб</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount) {
                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';


                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> Не позднее ' . $planDate . ' г.</td>';
                    }


                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';
                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>ИТОГО:</strong></td>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="2"><strong>';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек</td>';

            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"><strong>Продавец</strong></td>';
            echo '<td style="text-align: left; width: 50%"><strong>Покупатель</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">' . config("perspektivakrym.land." . $userData['ENTITY'] . '.text') . '</td>';
            echo '<td style="text-align: left; width: 50%">' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' .  $userData['SECOND_NAME'] . '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . config("perspektivakrym.land." . $userData['ENTITY'] . '.name') .  '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }

        //подряд
        if ($type == $this->mPlanPayment::CONTRACT) {
            $data = $data->sortBy('date');
            $docNumber = $data[count($data) - 1]->doc_number;
            $i = 1;
            $amount = 0;

            ob_start();
            //шапка
            echo '<div style="text-align: right; font-family: \'Times New Roman\'; font-size: 12px;">
                Приложение № ' . $name . ' к Договору подряда<br />
                № ' . $docNumber . '
            </div>';

            //заголовок
            echo '<br /><br />';
            echo '<div style="text-align: center;"><strong>График производства оплат</strong></div>';

            //данные
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left 20px; border-collapse: collapse" border="1">';
            echo '<tr>';
            echo '<th width="10%">№ <br />П/П</th>';
            echo '<th width="60%">Сумма, подлежащая к оплате, руб</th>';
            echo '<th width="30%">Срок оплаты</th>';
            echo '</tr>';
            foreach ($data as $d) {
                if ($d->amount !== 0) {
//                if ($d->number === 1) {
//                    continue;
//                }

                    // Склонение слова "рубль".
                    $rub = $this->getRub($d->amount);

                    echo '<tr>';
                    echo '<td style="text-align: center; vertical-align: middle">' . $i++ . '</td>';

                    echo '<td style="text-align: center; vertical-align: middle">';
                    $amount += $d->amount;
                    echo 'аванс ' . number_format($d->amount, 2, ',', ' ');
                    echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $d->amount]) . ') ' . $rub . ' 00 копеек</td>';

                    if ($d->is_text_date == 1) {
                        echo '<td style="text-align: center; vertical-align: middle"> ' . $d->text_date . '</td>';
                    } else {
                        $planDate = date('d.m.Y', strtotime($d->date));
                        echo '<td style="text-align: center; vertical-align: middle"> до ' . $planDate . ' года</td>';
                    }


                    echo '</tr>';
                }
            }
            echo '<tr>';
            echo '<td style="text-align: center; vertical-align: middle" colspan="3"><strong>Итого:';
            echo number_format($amount, 2, ',', ' ');
            $rub = $this->getRub($amount);
            echo '(' . (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $amount]) . ') ' . $rub . ' 00 копеек';

            if ($userData['ENTITY'] === 'LUCHI - ООО Перспектива') {
                echo ', в том числе НДС 20%.';
            }

            echo '</strong></td>';
            echo '</tr>';

            echo '</table>';

            //подписи
            echo '<br /><br />';
            echo '<table width="100%" style="margin-left: 20px">';
            echo '<tr>';
            echo '<td style="text-align: center; width: 50%"><strong>ЗАКАЗЧИК:</strong></td>';
            echo '<td style="text-align: center; width: 50%"><strong>ПОДРЯДЧИК:</strong></td>';
            echo '</tr>';
            echo '<tr>';
            //данные по заказчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">';
            echo '<strong>' . $userData['LAST_NAME'] . ' ' . $userData['NAME'] . ' ' . $userData['SECOND_NAME'] . '</strong>, <br />';
            $sex = '';
            if ($userData['SEX'] == 2096) {
                $sex = 'муж.';
            }
            if ($userData['SEX'] == 2098) {
                $sex = 'жен.';
            }
            echo (is_null($userData['BIRTHDATE']) || $userData['BIRTHDATE'] == '')? '' : date('Y.m.d', strtotime($userData['BIRTHDATE'])) . ' года рождения, <br />пол ' . $sex . ', <br />место рождения: ' . $userData['CITY_BIRTH'] . ', <br />';
            echo 'паспорт гражданина ' . $userData['PASSPORT_COUNTRY'] . ' серии ' . $userData['PASSPORT_SERIES'] . ' номер ' . $userData['PASSPORT_NUMBER'] . ' выдан ' . $userData['PASSPORT_ORGAN'] . ', ';
            echo 'дата выдачи ' . (is_null($userData['PASSPORT_DATE']) || $userData['PASSPORT_DATE'] == '')? '' : date('Y.m.d', strtotime($userData['PASSPORT_DATE'])) . ', код подразделения: ' . $userData['PASSPORT_ORGAN_CODE'] . ', <br />';
            echo 'СНИЛС ' . $userData['SNILS'] . ', <br />зарегистрирован по адресу: ' . $userData['ADDRESS'] . ', <br />';
            echo 'тел. ' . $userData['PHONE'];
            echo '</td>';
            //данные по подрядчику
            echo '<td style="text-align: left; width: 50%; vertical-align: top">
<strong>Общество с ограниченной ответственностью «ПЕРСПЕКТИВА»</strong><br />
Юр. адрес: 298612, Республика Крым, г. Ялта, ул. Московская, дом № 33-А, квартира 2,<br />
ОГРН:1159102113917;<br />
ИНН: 9103075830, <br />
КПП: 910301001<br />
р/с.: 40702810200000005426, Банк: ООО "ЖИВАГО БАНК", БИК: 046126744, Корр. счет: 30101810700000000744

            </td>';
            echo '</tr>';

            echo '<tr><td><strong>Заказчик</strong></td><td><strong>Директор</strong></td></tr>';

            echo '<tr>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>' . $userData['FULL_NAME'] . '</strong></td>';
            echo '<td style="text-align: left; width: 50%">______________/<strong>А.Н.Мезенцев</strong></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td style="text-align: left; width: 50%"></td>';
            echo '<td style="text-align: left; width: 50%">М.П.</td>';
            echo '</tr>';

            echo '</table>';



            $html = ob_get_contents();
            ob_end_clean();

        }

        $pdf->WriteHTML($html);

        return $pdf->Output($docNumber . '.pdf', 'D');
    }

//    public function getPayment(Request $request)
//    {
//        try {
//            $dealId = $request->get('deal_id', null);
//            $deal = $this->b24->getDealById($dealId)['result'];
//            $auth = $request->get('auth');
//
//            //№ основного договора (ДДУ, земля, доп. лот)"
//            $numberLand = $deal['UF_CRM_1611646713185'];
//
//            //№ договора ПОДРЯД"
//            $numberContract = $deal['UF_CRM_1611646728314'];
//
//            if (!is_null($numberLand) && $numberLand != '') {
//                //получаем платежи из 1с
//                $factPayments = $this->getFactPaymentsByDoc($numberLand);
//                $planPayments = $this->mPayments
//                    ->where('doc_number', $numberLand)
//                    ->orderBy('created_at', 'asc')
//                    ->get();
//
//                if (count($factPayments)>count($planPayments)) {
//                    throw new \DomainException('Фактических платежей больше, чем платежей по плану');
//                }
//
//                foreach ($factPayments as $key => $val) {
//                    $planPayments[$key]->fact_amount = $val->Сумма;
//                    $planPayments[$key]->fact_date = $val->ДокументДата;
//                    $planPayments[$key]->save();
//                }
//            }
//
////            dd($numberContract . ' | ' . $numberContract);
//
//            if (!is_null($numberContract) && $numberContract != '') {
//                //получаем платежи из 1с
//                $factPayments = $this->getFactPaymentsByDoc($numberContract);
//                $planPayments = $this->mPayments
//                    ->where('doc_number', $numberContract)
//                    ->orderBy('created_at', 'asc')
//                    ->get();
//
//                if (count($factPayments)>count($planPayments)) {
//                    throw new \DomainException('Фактических платежей больше, чем платежей по плану');
//                }
//
//                foreach ($factPayments as $key => $val) {
//                    $planPayments[$key]->fact_amount = $val->Сумма;
//                    $planPayments[$key]->fact_date = $val->ДокументДата;
//                    $planPayments[$key]->save();
//                }
//            }
//
//            return $this->index(new Request(), [
//                'deal_id' => $dealId,
//                'auth' => $auth,
//            ]);
//        } catch (\Exception $e) {
//            Log::error('Perspektivakrym: ' . $e->getMessage());
//            return $this->index(new Request(), [
//                'deal_id' => $dealId,
//                'error' => $e->getMessage(),
//                'auth' => $auth,
//            ]);
//        }
//    }
//
//    public function getFactPaymentsByDoc($doc)
//    {
//        return DB::connection('perspektivakrym_remote')
//            ->table('ViewДокументыДДСкратко')
//            ->where('Договор', 'LIKE', '%' . $doc)
//            ->orderBy('ДокументДата', 'asc')
//            ->get();
//    }

    /**
     * Получение следующей даты платежа
     * @param $date - текущая дата
     * @param $type - период:
     *          1 - учитывается сколько дней прибавить
     *          2302 - месяц
     *          2304 - квартал
     *          2306 - полгода
     *          2308 - год
     * @param int $days - сколько дней прибавить, если тип = 1
     * @return string
     */
    protected function getPlanDate($date, $type, $days = 0)
    {
        $carbDate = Carbon::create($date);

        switch ($type) {
            case '1':
                $carbDate = $carbDate->addDays($days);
                break;
            case '2302':
                $carbDate = $carbDate->addMonth();
                break;
            case '2304':
                $carbDate = $carbDate->addMonths(3);
                break;
            case '2306':
                $carbDate = $carbDate->addMonths(6);
                break;
            case '2308':
                $carbDate = $carbDate->addYear();
                break;
            default:
                throw new \DomainException('Не указана частота платежей');
        }

        return $carbDate->format('Y-m-d');
    }

    /**
     * Получение кол-ва платежей (округление по математическим законам)
     * @param $countMonths - кол-во месяцев
     * @param $type - период:
     *          2302 - месяц
     *          2304 - квартал
     *          2306 - полгода
     *          2308 - год
     * @return float|int
     */
    protected function getCountPayment($countMonths, $type)
    {
        switch ($type) {
            case '2302':
                $count = $countMonths / 1;
                break;
            case '2304':
                $count = round($countMonths / 3);
                break;
            case '2306':
                $count = round($countMonths / 6);
                break;
            case '2308':
                $count = round($countMonths / 12);
                break;
            default:
                $count = 1;
        }

        return $count;
    }

    protected function getDiffDays($date)
    {
        $dateNow = Carbon::now();
        $carbDate = Carbon::create($date);

        $diffDays = $carbDate->diffInDays($dateNow, false);

        if ($diffDays <= 0) {
            $diffDays = 0;
        }

        return (int)$diffDays;
    }

    protected function getOldDocByDealId($dealId)
    {
        return $this->mOldDoc->where('deal', $dealId)->first();
    }

    /**
     * Проверка аутентификации приложения
     * @param $auth
     * @return bool
     */
    protected function checkApp($auth)
    {
        if ($auth === config('perspektivakrym.app_id')) {
            return true;
        }

        $appInfo = $this->b24->getAppInfo($auth);

        if(isset($appInfo['result']['CODE'])) {
            if($appInfo['result']['CODE'] == "local.6867d4682b0226.24518665") {
                return true;
            }
        }

        return false;
    }

    /**
     * Склонения "рубля"
     * @param $amount - сумма
     * @return string
     */
    protected function getRub($amount)
    {
        $num = $amount % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1:
                $rub = 'рубль';
                break;
            case 2:
            case 3:
            case 4:
                $rub = 'рубля';
                break;
            default:
                $rub = 'рублей';
        }

        return $rub;
    }

    /**
     * Заморозка платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function freezePayment(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $paymentType = $request->get('type', 'all');
            $numberGraph = $request->get('number_graph', 0);

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $payments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('pay_type', '<>', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('number_graph', $numberGraph);
            if ($paymentType !== 'all') {
                $payments = $payments->where('type', $paymentType);
            }
            $payments->update(['blocked' => $this->mPlanPayment::BLOCK]);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Разморозка платежа
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function unfreezePayment(Request $request)
    {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $paymentType = $request->get('type', 'all');
            $numberGraph = $request->get('number_graph', 0);

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            $payments = $this->mPlanPayment
                ->where('deal_id', $dealId)
                ->where('pay_type', '<>', $this->mPlanPayment::DOWN_PAYMENT)
                ->where('number_graph', $numberGraph);
            if ($paymentType !== 'all') {
                $payments = $payments->where('type', $paymentType);
            }
            $payments->update(['blocked' => $this->mPlanPayment::ACTIVE]);

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }

    /**
     * Добавления комментария в плановый платеж
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editComment(Request $request) {
        try {
            $dealId = $request->get('deal_id');
            $auth = $request->get('auth');
            $paymentType = $request->get('payment_type', 'plan');
            $operationId = $request->get('operation_id', 0);
            $note = $request->get('note', '');

            if ($operationId === 0) {
                throw new \Exception('Не смогли подтянуть ID операции');
            }

            if (!$this->checkApp($auth)) {
                throw new \DomainException('Приложение не авторизовано');
            }

            if ($paymentType === 'plan') {
                $this->mPlanPayment
                    ->where('id', $operationId)
                    ->update(['note' => $note]);
            }

            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'auth' => $auth,
            ]);
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return $this->index(new Request(), [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'auth' => $auth,
            ]);
        }
    }


    public function test()
    {
        $res = $this->b24->getDeals();
        dd($res);
    }
}

