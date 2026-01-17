<?php

declare(strict_types=1);

namespace App\Controllers\Facet;

use Hleb\Static\Request;
use Hleb\Base\Controller;
use App\Models\User\UserModel;
use App\Models\FacetModel;
use Img, Meta, Msg, Html, Validator;

use App\Traits\Author;
use App\Traits\Related;

class EditFacetController extends Controller
{
    /**
     * Topic or Blog editing form
     * Форма редактирования Topic or Blog
     *
     * @return void
     */
    public function index()
    {
        $facet_type = Request::param('type')->asString();
        $facet  = Validator::facet(Request::param('id')->asInt(), 'id', $facet_type);

        // Доступ получает только автор и админ
        if ($facet['facet_user_id'] != $this->container->user()->id() && !$this->container->user()->admin()) {
            $this->container->redirect()->to('/', status: 303);
        }

        render(
            '/content/facets/edit',
            [
                'meta'  => Meta::get(__('app.edit') . ' | ' . $facet['facet_title']),
                'data'  => [
                    'low_matching'      => FacetModel::getLowMatching($facet['facet_id']),
                    'low_arr'           => FacetModel::getLowLevelList($facet['facet_id']),
                    'user'              => UserModel::get($facet['facet_user_id'], 'id'),
                    'sheet'             => $facet_type,
                    'facet_inf'         => $facet,
                ]
            ]
        );
    }

    public function edit()
    {
        $data = Request::getParsedBody(); // allPost()

        // Получим массив данных существующего фасета и проверим его наличие
        $facet = FacetModel::uniqueById((int)$data['facet_id'] ?? 0);

        $new_type = Validator::editFacet($data, $facet);

        // Img::set($_FILES, $facet['facet_id'], 'facet');

        $facet_user_id = Html::selectAuthor($facet['facet_user_id'], Request::post('user_id')->value());

        $post_related = Html::relatedPost();

        $facet_top_level = $data['facet_top_level'] ?? false;
        $facet_is_comments = $data['facet_is_comments'] ?? false;

        FacetModel::edit(
            [
                'facet_id'                  => $data['facet_id'],
                'facet_title'               => $data['facet_title'],
                'facet_description'         => $data['facet_description'],
                'facet_info'                => $data['facet_info'],
                'facet_slug'                => strtolower($data['facet_slug']),
                'facet_user_id'             => $facet_user_id,
                'facet_top_level'           => $facet_top_level == 'on' ? 1 : 0,
                'facet_post_related'        => $post_related,
                'facet_type'                => $new_type,
                'facet_is_comments'         => $facet_is_comments == 'on' ? 1 : 0,
            ]
        );

        self::setModification($data);

        Msg::redirect(__('msg.change_saved'), 'success', url('structure'));
    }

    /**
     * Avatar and cover upload form
     * Форма загрузки аватарки и обложики
     *
     * @return void
     */
    function logoForm()
    {
        $type = Request::param('type')->asString();
        $facet  = Validator::facet(Request::param('id')->asInt(), 'id', $type);

        // Доступ получает только автор и админ
        if ($facet['facet_user_id'] != $this->container->user()->id() && !$this->container->user()->admin()) {
            $this->container->redirect()->to('/', status: 303);
        }

        render(
            '/content/facets/edit-logo',
            [
                'meta'  => Meta::get(__('app.logo')),
                'data'  => [
                    'type'        => $type,
                    'facet_inf'    => $facet,
                ]
            ]
        );
    }

    function logoEdit()
    {
        $type = Request::param('type')->asString();
        $facet  = Validator::facet(Request::param('facet_id')->asInt(), 'id', $type);

        // Доступ получает только автор и админ
        if ($facet['facet_user_id'] != $this->container->user()->id() && !$this->container->user()->admin()) {
            $this->container->redirect()->to('/', status: 303);
        }

        // Img::set($_FILES, $facet['facet_id'], 'facet');
    }

    public static function setModification($data)
    {
        // Выбор детей в дереве
        $lows  = $data['low_facet_id'] ?? false;
        if ($lows) {
            $low_facet = json_decode($lows, true);
            $low_arr = $low_facet ?? [];

            FacetModel::addLowFacetRelation($low_arr, (int)$data['facet_id']);
        } else {
            FacetModel::deleteRelation((int)$data['facet_id'], 'topic');
        }

        // Связанные темы, дети 
        $matching = $data['facet_matching'] ?? false;
        if ($matching) {
            $match_facet    = json_decode($matching, true);
            $match_arr      = $match_facet ?? [];

            FacetModel::addLowFacetMatching($match_arr, (int)$data['facet_id']);
        } else {
            FacetModel::deleteRelation((int)$data['facet_id'], 'matching');
        }

        return true;
    }
}
