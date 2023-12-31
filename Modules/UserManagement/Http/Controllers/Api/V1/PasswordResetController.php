<?php

namespace Modules\UserManagement\Http\Controllers\Api\V1;

use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\SMSModule\Lib\SMS_gateway;
use Modules\UserManagement\Emails\OTPMail;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserVerification;

class PasswordResetController extends Controller
{
    public function __construct(
        private User             $user,
        private UserVerification $user_verification
    )
    {
    }


    /**
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|max:255',
            'identity_type' => 'required|in:phone,email'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        //exist check
        $user = $this->user->where($request['identity_type'], $request['identity'])->first();

        if (!isset($user))
            return response()->json(response_formatter(AUTH_LOGIN_404), 404);


        //verification method check
        $forget_password_verification_method = business_config('forget_password_verification_method', 'business_information')?->live_values;
        if ($request['identity_type'] != $forget_password_verification_method)
            return response()->json(response_formatter(DEFAULT_SENT_OTP_FAILED_200), 200);

        //resend time check
        $user_verification = $this->user_verification->where('identity', $request['identity'])->first();
        $otp_resend_time = business_config('otp_resend_time', 'otp_login_setup')?->live_values;
        if (isset($user_verification) && Carbon::parse($user_verification->created_at)->DiffInSeconds() < $otp_resend_time) {
            $time = $otp_resend_time - Carbon::parse($user_verification->created_at)->DiffInSeconds();

            return response()->json(response_formatter([
                "response_code" => "auth_login_401",
                "message" => translate('Please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans(),
            ]), 401);
        }

        $otp = env('APP_ENV') != 'live' ? '1234' : rand(1000, 9999);
        $this->user_verification->updateOrCreate([
            'identity' => $request['identity'],
            'identity_type' => $request['identity_type']
        ], [
            'identity' => $request['identity'],
            'identity_type' => $request['identity_type'],
            'user_id' => null,
            'otp' => $otp,
            'expires_at' => now()->addMinute(3),
        ]);

        //send otp
        if ($request['identity_type'] == 'phone') {
            $response = SMS_gateway::send($request['identity'], $otp);
        } else if ($request['identity_type'] == 'email') {
            try {
                Mail::to($request['identity'])->send(new OTPMail($otp));
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        } else {
            $response = 'error';
        }


        if ($response == 'success')
            return response()->json(response_formatter(DEFAULT_SENT_OTP_200), 200);
        else
            return response()->json(response_formatter(DEFAULT_SENT_OTP_FAILED_200), 200);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'identity_type' => 'required',
            'otp' => 'required|max:4'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $max_otp_hit = business_config('maximum_otp_hit', 'otp_login_setup')->test_values ?? 5;
        $max_otp_hit_time = business_config('otp_resend_time', 'otp_login_setup')->test_values ?? 60;// seconds
        $temp_block_time = business_config('temporary_otp_block_time', 'otp_login_setup')->test_values ?? 600; // seconds

        $verify = $this->user_verification->where(['identity' => $request['identity'], 'otp' => $request['otp']])->first();

        if (isset($verify)) {
            if (isset($verify->temp_block_time) && Carbon::parse($verify->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($verify->temp_block_time)->DiffInSeconds();
                return response()->json(response_formatter([
                    "response_code" => "auth_login_401",
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                ]), 403);

            }

            $this->user_verification->where(['identity' => $request['identity']])->delete();
            return response()->json(response_formatter(OTP_VERIFICATION_SUCCESS_200), 200);
        } else {
            $verification_data = $this->user_verification->where('identity', $request['identity'])->first();

            if (isset($verification_data)) {
                if (isset($verification_data->temp_block_time) && Carbon::parse($verification_data->temp_block_time)->DiffInSeconds() <= $temp_block_time) {
                    $time = $temp_block_time - Carbon::parse($verification_data->temp_block_time)->DiffInSeconds();
                    return response()->json(response_formatter([
                        "response_code" => "auth_login_401",
                        'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ]), 403);
                }

                if ($verification_data->is_temp_blocked == 1 && Carbon::parse($verification_data->updated_at)->DiffInSeconds() >= $max_otp_hit_time) {

                    $user_verify = $this->user_verification->where(['identity' => $request['identity']])->first();
                    if (!isset($user_verify)) {
                        $user_verify = $this->user_verification;
                    }
                    $user_verify->hit_count = 0;
                    $user_verify->is_temp_blocked = 0;
                    $user_verify->temp_block_time = null;
                    $user_verify->save();
                }


                if ($verification_data->hit_count >= $max_otp_hit && Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time && $verification_data->is_temp_blocked == 0) {

                    $user_verify = $this->user_verification->where(['identity' => $request['identity']])->first();
                    if (!isset($user_verify)) {
                        $user_verify = $this->user_verification;
                    }
                    $user_verify->is_temp_blocked = 1;
                    $user_verify->temp_block_time = now();
                    $user_verify->save();

                    $time = $temp_block_time - Carbon::parse($verification_data->temp_block_time)->DiffInSeconds();
                    return response()->json(response_formatter([
                        "response_code" => "auth_login_401",
                        'message' => translate('Too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ]), 403);
                }

            }

            $user_verify = $this->user_verification->where(['identity' => $request['identity']])->first();
            if (!isset($user_verify)) {
                $user_verify = $this->user_verification;
            }
            $user_verify->hit_count += 1;
            $user_verify->temp_block_time = null;
            $user_verify->save();
        }

        return response()->json(response_formatter(OTP_VERIFICATION_FAIL_403), 403);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset_password(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'identity_type' => 'required|in:email,phone',
            'otp' => 'required|max:4',

            'password' => 'required',
            'confirm_password' => 'required|same:confirm_password'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $user_verification = $this->user_verification
            ->where('identity_type', $request['identity_type'])
            ->where('identity', $request['identity'])
            ->where(['otp' => $request['otp']])
            ->where('expires_at', '>', now())
            ->first();

        if (isset($user_verification)) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $this->user->where($request['identity_type'], $request['identity'])
            ->update([
                'password' => bcrypt(str_replace(' ', '', $request['password']))
            ]);

        $this->user_verification
            ->where('identity_type', $request['identity_type'])
            ->where('identity', $request['identity'])
            ->where(['otp' => $request['otp']])->delete();

        return response()->json(response_formatter(DEFAULT_PASSWORD_RESET_200), 200);
    }
}
