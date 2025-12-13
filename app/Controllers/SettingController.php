<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Static\Request;
use Hleb\Base\Controller;

use App\Models\User\{SettingModel, UserModel};
use Meta, Html, Msg;

use App\Content\Validator;

class SettingController extends Controller
{

    /**
     * Profile setup form
     * Форма настройки профиля
     *
     * @return void
     */
    public function index()
    {
        render(
            '/content/user/setting',
            [
                'meta'  => Meta::get(__('app.setting')),
                'data'  => [
                    'sheet' => 'setting',
                    'user'  => UserModel::get($this->container->user()->id(), 'id'),
                ]
            ]
        );
    }

    function edit()
    {
        \Validator::setting($data = Request::getParsedBody());

        $user = UserModel::get($this->container->user()->id(), 'id');

        SettingModel::edit(
            [
                'id'        => $user['id'],
                'email'     => $data['email'],
                'login'     => $data['login'],
                'website'   => $data['website'],
            ]
        );

        Msg::redirect(__('msg.change_saved'), 'success', url('setting'));
    }

    /**
     * Change password form
     * Форма изменение пароля
     *
     * @return void
     */
    function securityForm()
    {
        render(
            '/content/user/security',
            [
                'meta'  => Meta::get(__('app.security')),
                'data'  => ['sheet' => 'setting']
            ]
        );
    }

    function securityEdit()
    {
        $data = Request::allPost();

        \Validator::security($data, $this->container->user()->email());

        $newpass = password_hash($data['password2'], PASSWORD_BCRYPT);

        SettingModel::editPassword(['id' => $this->container->user()->id(), 'password' => $newpass]);

        Msg::redirect(__('msg.successfully'), 'success', url('setting.security'));
    }
}
