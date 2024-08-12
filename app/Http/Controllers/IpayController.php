<?php
use Modules\Coinpayment\Classes\IpaypaymentProcessor;
use Modules\Core\Classes\DBManager;
use Modules\Account\Classes\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class IpayController extends Controller
{
    public function index(Request $request)
    {
        $context = [
            'title' => "Payment",
        ];

        return view('index', $context);
    }

    public function paymentIpayChecker(Request $request)
    {
        $ipaypaymentProcessor = new IpaypaymentProcessor();
        $payment = $ipaypaymentProcessor->paymentIpayChecker($request);
        return redirect('user_payment_list');
    }

    public function paymentIpayReturn(Request $request)
    {
        $ipaypaymentProcessor = new IpaypaymentProcessor();
        $dbManager = new DBManager();

        $payment = $ipaypaymentProcessor->paymentIpayReturn($request);
        $response = $request->input('response');

        if ($response === 'json') {
            $message = 'Payment was Successful';
            $newPayment = $payment->toArray();
            $response = [
                'status' => 200,
                'message' => $message,
                'payment' => $newPayment,
            ];
            return JsonResponse::create($response);
        }

        if ($payment->next_to) {
            return redirect($payment->next_to);
        } else {
            return redirect('user_payment_list');
        }
    }

    public function paymentIpayNotify(Request $request)
    {
        $ipaypaymentProcessor = new IpaypaymentProcessor();
        $dbManager = new DBManager();

        $payment = $ipaypaymentProcessor->paymentIpayNotify($request);
        $response = $request->input('response');

        $message = 'Payment was Successful';
        $newPayment = $dbManager->serialModel($payment);
        $response = [
            'status' => 200,
            'message' => $message,
        ];
        
        return JsonResponse::create($response);
    }
}
