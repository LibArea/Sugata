<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use Hleb\Static\Request;
use Hleb\Base\Controller;
use App\Models\User\{InvitationModel, UserModel};
use App\Models\Auth\AuthModel;
use App\Bootstrap\Services\Auth\{Action, Register};
use SendEmail, Meta, Html, Msg;

class RegisterController extends Controller
{
    /**
     * Show registration form
     * Показ формы регистрации
     *
     * @return void
     */
    public function showRegisterForm(): void
    {
        // If the invite system is enabled
        if (config('general', 'invite') == true) {
            redirect(url('invite'));
        }

        $m = [
            'og'    => false,
            'url'   => url('register'),
        ];

        render(
            '/auth/register',
            [
                'meta'  => Meta::get(__('app.registration'), __('help.security_info'), $m),
                'data'  => [
                    'sheet' => 'registration',
                    'type'  => 'register'
                ]
            ]
        );
    }
}
