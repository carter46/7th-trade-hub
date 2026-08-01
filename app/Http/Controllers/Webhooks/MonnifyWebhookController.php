<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Payments\Monnify\MonnifyWebhookProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonnifyWebhookController extends Controller
{
    public function __invoke(Request $request, MonnifyWebhookProcessor $processor): Response
    {
        $raw = $request->getContent();
        $processor->receive($raw, $request->headers->all(), $request->ip());

        return response('ok', 200);
    }
}
