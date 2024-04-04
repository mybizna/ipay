<?php

namespace Modules\Ipay\Classes;

use App\Classes\PaymentProcessor;
use Modules\Ipay\Entities\IpayPayment;
use Modules\Payment\Entities\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpayPaymentProcessor
{
    public function paymentIpayChecker($request)
    {
        $paymentProcessor = new PaymentProcessor();

        $this->processIpayPayments();

        $paymentIpayChecker = Cache::get('paymentIpayChecker');
        if (!is_null($paymentIpayChecker)) {
            return;
        }

        Cache::put('paymentIpayChecker', random_int(1, 7), 3600);

        $secretKey = Config::get('paymentipay.secret_key');
        $vid = Config::get('paymentipay.vid');

        $dateTo = Carbon::now()->subMinutes(15);
        $dateFrom = Carbon::now()->subDays(2);

        $payments = Payment::where('type', 'ipay')
            ->where('completed', true)
            ->where('successful', false)
            ->where('is_confirmed', false)
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->take(100)
            ->get();

        $gateway = $paymentProcessor->getGatewayByName('ipay');

        foreach ($payments as $payment) {
            $oid = $payment->id;

            $newParams = [
                ['oid', $oid], ['vid', $vid],
            ];

            $processedHash = $this->getHashForChecker($newParams);

            $newParams[] = ['hash', $processedHash];

            try {
                $response = Http::post(
                    'https://apis.ipayafrica.com/payments/v2/transaction/search',
                    $newParams
                );
                $ipayData = $response->json();
            } catch (\Exception $e) {
                $ipayData = ['status' => 0];
                Log::error($e->getMessage());
            }

            if ($ipayData['status'] == 1) {
                $payment = Payment::find($payment->id);
                $ipayPayment = IpayPayment::where('item_id', $payment->id)
                    ->where('status', 'aei7p7yrx4ae34')
                    ->first();

                if ($ipayPayment || $payment->is_confirmed) {
                    continue;
                }

                $deductions = json_decode($payment->deductions, true) ?? [];
                $requiredAmount = isset($deductions['amount']) ? Decimal($deductions['amount']) : $payment->amount;
                $paidAmount = Decimal($ipayData['data']['transaction_amount']);

                $paidAmount = $paymentProcessor->getGatewayConverterAmount($paidAmount, $gateway, false);
                $requiredAmount = $paymentProcessor->getGatewayConverterAmount($requiredAmount, $gateway, false);

                $paymentProcessor->savePaidAmount($payment, $requiredAmount, $paidAmount);

                if ($paidAmount >= $requiredAmount) {
                    $payment->code = $ipayData['data']['transaction_code'];
                    $payment->save();

                    $paymentProcessor->successfulTransaction($payment, $ipayData['data']['transaction_code']);
                } else {
                    $paymentProcessor->failTransaction($payment);
                }

                $payment->completed = true;
                $payment->is_confirmed = true;
                $payment->save();

                IpayPayment::create([
                    'item_id' => $payment->id,
                    'status' => 'aei7p7yrx4ae34',
                    'txncd' => $ipayData['data']['transaction_code'],
                    'ivm' => $payment->id,
                    'mc' => $ipayData['data']['transaction_amount'],
                    'p1' => $payment->id,
                    'p2' => '',
                    'p3' => '',
                    'p4' => '',
                    'payment_id' => $payment->id,
                ]);
            }
        }

        Cache::forget('paymentIpayChecker');
    }

    public function processIpayPayments()
    {
        $paymentProcessor = new PaymentProcessor();

        $dateTo = Carbon::now()->subMinutes(1);
        $dateFrom = Carbon::now()->subDays(2);

        $ipayPayments = IpayPayment::where('is_processed', false)
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->take(100)
            ->get();

        $gateway = $paymentProcessor->getGatewayByName('ipay');

        foreach ($ipayPayments as $ipayPayment) {
            $payment = Payment::find($ipayPayment->item_id);

            if (!$payment->successful) {
                $deductions = json_decode($payment->deductions, true) ?? [];
                $requiredAmount = isset($deductions['amount']) ? Decimal($deductions['amount']) : $payment->amount;
                $paidAmount = isset($ipayPayment->mc) ? Decimal($ipayPayment->mc) : 0;

                $paidAmount = $paymentProcessor->getGatewayConverterAmount($paidAmount, $gateway, false);
                $requiredAmount = $paymentProcessor->getGatewayConverterAmount($requiredAmount, $gateway, false);

                $paymentProcessor->savePaidAmount($payment, $requiredAmount, $paidAmount);

                if ($paidAmount >= $requiredAmount) {
                    $paymentProcessor->successfulTransaction($payment, $ipayPayment->txncd);
                } else {
                    $paymentProcessor->failTransaction($payment);
                }

                $payment->completed = true;
                $payment->is_confirmed = true;
                $payment->save();
            }

            $ipayPayment->is_processed = true;
            $ipayPayment->save();
        }
    }
    public function paymentIpayReturn(Request $request)
    {
        $paymentId = $request->input('payment_id');
        $phone = $request->input('phone');
        $returnUrl = $request->input('return_url');

        $phone = trim($phone);

        $payment = Payment::find($paymentId);
        $secretKey = config('paymentipay.secret_key');
        $vid = config('paymentipay.vid');

        $live = '0';
        $oid = strval($payment->id);
        $inv = strval($payment->id);
        $amount = strval($payment->amount_required);
        $tel = $payment->user->profile->phone ?? '0722232323';
        $eml = $payment->user->email;
        $curr = 'KES';
        $p1 = '';
        $p2 = '';
        $p3 = '';
        $p4 = '';
        $cbk = $returnUrl;
        $cst = '0';
        $crl = '2';

        $datastring = $live . $oid . $inv . $amount . $tel .
            $eml . $vid . $curr . $p1 . $p2 . $p3 . $p4 . $cst . $cbk;
        $processedHash = $this->getHash($datastring);

        $newParams = [
            ['live', $live], ['vid', $vid], ['oid', $oid], ['inv', $inv],
            ['amount', $amount], ['tel', $tel], ['eml', $eml], ['curr', $curr],
            ['p1', $p1], ['p2', $p2], ['p3', $p3], ['p4', $p4],
            ['cbk', $cbk], ['cst', $cst], ['crl', $crl], ['hash', $processedHash],
        ];

        $response = Http::post(
            'https://apis.ipayafrica.com/payments/v2/transact',
            $newParams
        );
        $ipayDict = $response->json();

        if ($ipayDict['status']) {
            $sid = $ipayDict['data']['sid'];

            $datastring = $phone . $vid . $sid;
            $processedHash = $this->getHash($datastring);

            $newParams = [
                ['phone', $phone], ['vid', $vid], ['sid', $sid], ['hash', $processedHash],
            ];

            $response = Http::post(
                'https://apis.ipayafrica.com/payments/v2/transact/push/mpesa',
                $newParams
            );
            $ipayDict = $response->json();

            if ($ipayDict['status']) {
                $payment->completed = true;
                $payment->save();
            }
        }

        return $payment;
    }

    public function paymentIpayNotify(Request $request)
    {
        $paymentId = $request->input('id');

        if ($paymentId) {
            $paymentId = $request->input('id');
        }

        $payment = Payment::find($paymentId);
        $ipayData = $this->getIpayParams($payment, $request);

        if (!$payment->successful) {
            $this->processIpaypayment($payment, $ipayData);
        }

        return $payment;
    }

    public function getHashForChecker($newParams)
    {
        $datastring = '';
        $processedHash = '';
        $processingPassed = false;

        $secretKey = config('paymentipay.secret_key');

        foreach ($newParams as $newParam) {
            $datastring .= strval($newParam[1]);
        }

        $newParams[] = ['hashkey', $secretKey];

        $response = Http::post(
            'https://globalinternetfortunes.com/gifhashing-search.php',
            ['data' => $newParams]
        );

        if ($response->status() == 200) {
            $ipayDict = $response->json();
            $processedHash = $ipayDict['hash'];
            $processingPassed = true;
        }

        if (!$processingPassed) {
            $processedHash = $this->getHash($datastring);
        }

        return [$datastring, $processedHash];
    }

    public function getHash($datastring)
    {
        $vid = config('paymentipay.vid');
        $secretKey = config('paymentipay.secret_key');

        $rawDatastring = $this->toRaw($datastring);
        $bytesDatastring = mb_convert_encoding($rawDatastring, 'UTF-8');
        $bytesSecretKey = mb_convert_encoding($secretKey, 'UTF-8');

        $signature = hash_hmac('sha256', $bytesDatastring, $bytesSecretKey);

        return $signature;
    }

    private function toRaw($string)
    {
        return $string;
    }

    private function processIpaypayment($payment, $ipayData)
    {
        $paymentProcessor = new PaymentProcessor();

        $gateway = $paymentProcessor->getGatewayByName('ipay');

        $deductions = json_decode($payment->deductions, true) ?? [];
        $requiredAmount = isset($deductions['amount']) ? Decimal::create($deductions['amount']) : $payment->amount;
        $paidAmount = isset($ipayData['mc']) ? Decimal::create($ipayData['mc']) : 0;

        $paidAmount = $paymentProcessor->getGatewayConverterAmount($paidAmount, $gateway, false);
        $requiredAmount = $paymentProcessor->getGatewayConverterAmount($requiredAmount, $gateway, false);

        $globalPreferences = global_preferences_registry_manager();
        $vendorRef = $globalPreferences['paymentipay__vid'];

        $payment->gateway = $gateway;
        $payment->completed = true;
        $payment->save();

        if (in_array($ipayData['status'], ['aei7p7yrx4ae34', 'eq3i7p5yt7645e', 'dtfi4p7yty45wq'])) {

            $paymentProcessor->savePaidAmount($payment, $requiredAmount, $paidAmount);

            if ($paidAmount >= $requiredAmount) {
                $paymentProcessor->successfulTransaction($payment, $ipayData['txncd']);
            } else {
                $paymentProcessor->failTransaction($payment);
            }

        } elseif (in_array($ipayData['status'], ['fe2707etr5s4wq', 'cr5i3pgy9867e1'])) {

            $paymentProcessor->failTransaction($payment);

        } elseif (in_array($ipayData['status'], ['bdi6p2yy76etrs', ''])) {

            $paymentProcessor->pendingTransaction($payment);
        }
    }

    private function getIpayParams($payment, $request)
    {
        $ipayPaymentAdded = Ipaypayment::where('txncd', $request->input('txncd'))->first();

        if (!$ipayPaymentAdded) {

            $ipayPayment = new Ipaypayment();
            $ipayPayment->payment_id = $payment->id;
            $ipayPayment->item_id = $request->input('id');
            $ipayPayment->status = $request->input('status');
            $ipayPayment->txncd = $request->input('txncd');
            $ipayPayment->ivm = $request->input('ivm');
            $ipayPayment->qwh = $request->input('qwh');
            $ipayPayment->afd = $request->input('afd');
            $ipayPayment->poi = $request->input('poi');
            $ipayPayment->uyt = $request->input('uyt');
            $ipayPayment->ifd = $request->input('ifd');
            $ipayPayment->agd = $request->input('agd');
            $ipayPayment->mc = $request->input('mc');
            $ipayPayment->p1 = $request->input('p1');
            $ipayPayment->p2 = $request->input('p2');
            $ipayPayment->p3 = $request->input('p3');
            $ipayPayment->p4 = $request->input('p4');
            $ipayPayment->save();

            return $ipayPayment;
        }

        return $ipayPaymentAdded;
    }

}
