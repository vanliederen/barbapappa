<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Validator;

use App\Helpers\ValidationDefaults;

class FinanceController extends Controller {

    /**
     * Economy finance overview page.
     *
     * @return Response
     */
    public function overview(Request $request, $communityId, $economyId) {
        // Get the user, community, find the products
        $user = barauth()->getUser();
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);

        $wallets = $economy->wallets;
        $walletSum = $economy->sumAmounts($wallets, 'balance');
        $paymentsProgressing = $economy->payments()->inProgress()->get();
        $paymentProgressingSum = $economy->sumAmounts($paymentsProgressing, 'money');

        // Gether balance for every member
        $members = $economy->members;
        $memberData = $members
            ->map(function($member) {
                return [
                    'member' => $member,
                    'balance' => $member->sumBalance(),
                ];
            })
            ->filter(function($data) {
                return $data['balance']->amount != 0;
            })
            ->sortByDesc(function($data) {
                return $data['balance']->amount;
            });

        return view('community.economy.finance.overview')
            ->with('economy', $economy)
            ->with('walletSum', $walletSum)
            ->with('paymentProgressingSum', $paymentProgressingSum)
            ->with('memberData', $memberData);
    }

    /**
     * The permission required for viewing.
     * @return PermsConfig The permission configuration.
     */
    public static function permsView() {
        return EconomyController::permsView();
    }
}