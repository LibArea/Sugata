<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use Hleb\Static\Request;
use Hleb\Base\Controller;
use App\Bootstrap\Services\Auth\{Login, Action, Remember};

class LoginController extends Controller
{
    /**
     * Authorization
     * Авторизация
     *
     * @return void
     */
    public function index(): void
    {
		(new Login)->index();
    }

    /**
     * Log out of the system
     * Выход из системы
     *
     * @return void
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
            session_destroy();

        setcookie("remember", "", time() - 3600, "/", httponly: true);

        redirect('/');
    }
}
