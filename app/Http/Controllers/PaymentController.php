<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Validation\ValidationException;

class PaymentController
{
    private $userIdValidationRules = 'required|integer|min:1';
    private $tryCount = 5;

    public function getBalance(Request $request)
    {
        $userKey = 'user';

        $request->validate([
            $userKey => $this->userIdValidationRules,
        ]);

        $userId = $request->query($userKey);
        $user = User::findOrFail($userId);

        $ret = [
            'balance' => (float) $user->balance,
        ];

        return $ret;
    }

    public function postDeposit(Request $request)
    {
        $userKey = 'user';

        $request->validate([
            $userKey => $this->userIdValidationRules,
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = $request->post('amount');
        $userId = $request->post($userKey);

        DB::transaction(function() use ($userId, $amount) {
            $user = User::firstOrCreate(['id' => $userId], ['id' => $userId, 'balance' => 0]);
            $user->balance += $amount;
            $user->save();
        }, $this->tryCount);
    }

    public function postWithdraw(Request $request)
    {
        $userKey = 'user';

        $request->validate([
            $userKey => $this->userIdValidationRules,
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = $request->post('amount');
        $userId = $request->post($userKey);

        DB::transaction(function() use ($userId, $amount) {
            $user = User::findOrFail($userId);

            if (($user->balance - $amount) < 0) {
                throw new ValidationException('Insufficient funds');
            }

            $user->balance -= $amount;
            $user->save();
        }, $this->tryCount);
    }

    public function postTransfer(Request $request)
    {
        $fromKey = 'from';
        $toKey = 'to';

        $request->validate([
            $fromKey => $this->userIdValidationRules,
            $toKey => $this->userIdValidationRules,
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = $request->post('amount');
        $fromUserId = $request->post($fromKey);
        $toUserId = $request->post($toKey);

        DB::transaction(function() use ($fromUserId, $toUserId, $amount) {
            $fromUser = User::findOrFail($fromUserId);
            $toUser = User::findOrFail($toUserId);

            if (($fromUser->balance - $amount) < 0) {
                throw new ValidationException('Insufficient funds');
            }

            $fromUser->balance -= $amount;
            $toUser->balance += $amount;

            $fromUser->save();
            $toUser->save();
        }, $this->tryCount);
    }
}