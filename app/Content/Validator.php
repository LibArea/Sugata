<?php

declare(strict_types=1);

use Hleb\Static\Container;
use App\Models\{FacetModel, ItemModel};
use App\Models\Auth\AuthModel;
use App\Models\User\InvitationModel;

use Respect\Validation\Validator as v;

class Validator
{
    public static function item(int $id): array
    {
        $item = ItemModel::getItem($id, 'id');

        notEmptyOrView404($item);

        return $item;
    }
	
	
    public static function publication($data, $redirect)
    {
		$title = str_replace("&nbsp;", '', $data['item_title']);
		if (v::stringType()->length(6, 250)->validate($title) === false) {
			Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.title') . '»']), 'error', $redirect);
		}

        if (v::stringType()->length(6, 25000)->validate($data['item_content']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.content') . '»']), 'error', $redirect);
        }

        // Let's check the presence of the facet before adding it	
        // Проверим наличие фасета перед добавлением	
        if (!$data['facet_select'] ?? false) {
            Msg::redirect(__('msg.select_topic'), 'error', $redirect);
        }

        return true;
    }

    public static function login($data)
    {
        $redirect = '/';

        if (v::email()->isValid($data['email']) === false) {
            Msg::redirect(__('msg.email_correctness'), 'error', $redirect);
        }

        $user = AuthModel::getUser($data['email'], 'email');

        if (empty($user['id'])) {
            Msg::redirect(__('msg.no_user'), 'error', $redirect);
        }

        // Is it on the ban list
        // Находится ли в бан- листе
        if (AuthModel::isBan((int)$user['id'])) {
            Msg::redirect(__('msg.account_verified'), 'error', $redirect);
        }

        if (!AuthModel::isActivated((int)$user['id'])) {
            Msg::redirect(__('msg.not_activated'), 'error', $redirect);
        }

        if (AuthModel::isDeleted((int)$user['id'])) {
            Msg::redirect(__('msg.no_user'), 'error', '/');
        }

        if (!password_verify($data['password'], $user['password'])) {
            Msg::redirect(__('msg.not_correct'), 'error', $redirect);
        }

        return $user;
    }

    public static function addFacet($data, $facet_type)
    {
        $container = Container::getContainer();
        $redirect = ($facet_type === 'category') ? url('structure') : url('facet.form.add', ['type' => $facet_type]);

        if (v::slug()->length(3, 43)->validate($data['facet_slug']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.slug') . '»']), 'error', $redirect);
        }

        if (FacetModel::uniqueSlug($data['facet_slug'], $facet_type)) {
            Msg::redirect(__('msg.repeat_url'), 'error', $redirect);
        }

        if (preg_match('/\s/', $data['facet_slug']) || strpos($data['facet_slug'], ' ')) {
            Msg::redirect(__('msg.url_gaps'), 'error', $redirect);
        }
    }
	
    public static function editFacet($data, $facet)
    {
        $container = Container::getContainer();

		$data['facet_type'] = 'category';

        if ($facet == false) {
            Msg::redirect(__('msg.went_wrong'), 'error');
        }

        $redirect = url('facet.form.edit', ['type' => $facet['facet_type'], 'id' => $facet['facet_id']]);

        // Доступ получает только автор и админ
        if ($facet['facet_user_id'] != $container->user()->id() && !$container->user()->admin()) {
            Msg::redirect(__('msg.went_wrong'), 'error', $redirect);
        }

        // Изменять тип темы может только персонал
        $new_type = $facet['facet_type'];
        if ($data['facet_type'] != $facet['facet_type']) {
            if ($container->user()->admin()) $new_type = $data['facet_type'];
        }
 
        // Проверка длины
        if (v::stringType()->length(3, 64)->validate($data['facet_title']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.title') . '»']), 'error', $redirect);
        }
 
        if (v::stringType()->length(3, 225)->validate($data['facet_description']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.meta_description') . '»']), 'error', $redirect);
        }
 
        if (v::stringType()->length(9, 160)->validate($data['facet_info']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.short_description') . '»']), 'error', $redirect);
        }

        // Slug
        if (v::slug()->length(3, 43)->validate($data['facet_slug']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.slug') . '»']), 'error', $redirect);
        }

        // Проверим повтор URL                       
        if ($data['facet_slug'] != $facet['facet_slug']) {
            if (FacetModel::uniqueSlug($data['facet_slug'], $new_type)) {
                Msg::redirect(__('msg.repeat_url'), 'error', $redirect);
            }
        }

        return $new_type;
    }
	
    public static function content(int|string $element, string $type_element = 'id'): array
    {
        $content = ItemModel::getItem($element, $type_element);

        notEmptyOrView404($content);

        return $content;
    }

    // mixed $element (> PHP 8.0)
    public static function facet(int|string $element, string $type_element = 'id', string $type = 'topic'): array
    {
        $facet = FacetModel::get($element, $type_element, $type);

        notEmptyOrView404($facet);

        return $facet;
    }

    public static function allFacet(int $id): array
    {
        $facet = FacetModel::uniqueById($id);

        notEmptyOrView404($facet);

        return $facet;
    }
	
    public static function setting($data)
    {
        $redirect = url('setting');
		
		$container = Container::getContainer();

        if (v::stringType()->length(3, 11)->validate($data['login']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.login') . '»']), 'error', $redirect);
        }

        if ($data['email']) {
            if (v::email()->isValid($data['email']) === false) {
                Msg::redirect(__('msg.email_correctness'), 'error', $redirect);
            }
        }

        return true;
    }
	
    public static function security(array $data, string $email)
    {
        $redirect   = url('setting.security');

        if ($data['password2'] != $data['password3']) {
            Msg::redirect(__('msg.pass_match_err'), 'error', $redirect);
        }

        if (substr_count($data['password2'], ' ') > 0) {
            Msg::redirect(__('msg.password_spaces'), 'error', $redirect);
        }

        if (v::stringType()->length(6, 10000)->validate($data['password2']) === false) {
            Msg::redirect(__('msg.string_length', ['name' => '«' . __('msg.password') . '»']), 'error', $redirect);
        }

        $userInfo   = AuthModel::getUser($email, 'email');
        if (!password_verify($data['password'], $userInfo['password'])) {
            Msg::redirect(__('msg.old_error'), 'error', $redirect);
        }

        return true;
    }
}
