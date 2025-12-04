<?php

declare(strict_types=1);

namespace App\Bootstrap\Services\Auth;

use Hleb\Static\Request;
use App\Models\User\UserModel;
use App\Bootstrap\Services\Auth\Remember;
use Meta, Validator;

class Login
{
    public function index(): void
    {
        $data = Request::allPost();
		
        $user = Validator::login($data);

        // If you clicked "Remember", it establishes a user session and registers it
        // Если нажал "Запомнить", то устанавливает сеанс пользователя и регистрирует его
        $rememberMe = $data['rememberme'] ?? false;
        if ($rememberMe == 1) {
            Remember::rememberMe($user['id']);
        }

        Action::set($user['id']);

        self::setUserLog($user['id']);

        redirect('/');
    }

    /**
     * Login page
     * Страница авторизации
     *
     * @return void
     */
    public function showLoginForm()
    {
        $m = [
            'og'    => false,
            'url'   => url('login'),
        ];

        return render(
            '/auth/login',
            [
                'meta'  => Meta::get(__('app.sign_in'), __('auth.login_info'), $m),
                'data'  => [
                    'sheet' => 'sign.in',
                    'type'  => 'login',
                ]
            ]
        );
    }

    /**
     * Let's record the participant's data: browser, platform...
     * Запишем данные участника: браузера, платформы...
     *
     * @param [type] $user_id
     * @return void
     */
    public static function setUserLog(int $user_id): void
    {
        UserModel::setLogAgent(
            [
                'user_id'       => $user_id,
                'user_browser'  => '',
                'user_os'       => '',
                'user_ip'       => Request::getUri()->getIp(),
            ]
        );
    }
}
