<?php

declare(strict_types=1);

namespace App\Controllers\Facet;

use Hleb\Static\Request;
use Hleb\Base\Controller;
use App\Models\{FacetModel, SubscriptionModel, ActionModel};
use Meta, Msg, Validator;

class AddFacetController extends Controller
{
    /**
     * Add form: topic | blog | category
     *
     * @return void
     */
    public function index()
    {
        $facet_type = Request::param('type')->asString();

        render(
            '/admin/pages/facets/add',
            [
                'meta'  => Meta::get(__('app.add_' . $facet_type)),
                'data'  => [
                    'sheet' => $facet_type,
                    'facets' => FacetModel::getTree('category', 'all'),
                ]
            ]
        );
    }

    /**
     * Add topic | blog | category
     *
     * @return void
     */
    public function add()
    {
        $facet_type = Request::param('type')->asString();

        $data = Request::getParsedBody(); // allPost()

        Validator::addFacet($data, $facet_type);

        $type = $facet_type ?? 'topic';

        $slug    = strtolower($data['facet_slug']);
        $parent  = (int)($data['facet_parent_id'] ?? 0);
        $parentPath = $parent > 0 ? FacetModel::getParentPath($parent) : '';
        $facetPath  = $parentPath !== '' ? $parentPath . '/' . $slug : $slug;

        // Уникальность по полному пути (одинаковый slug в разных ветках допустим)
        if (FacetModel::uniquePath($facetPath, $type)) {
            Msg::redirect(__('msg.repeat_url'), 'error', url('facet.form.add', ['type' => $facet_type]));
        }

        $new_facet_id = FacetModel::add(
            [
                'facet_title'       => $data['facet_title'],
                'facet_description' => __('app.meta_description'),
                'facet_slug'        => $slug,
                'facet_img'         => 'facet-default.png',
                'facet_user_id'     => $this->container->user()->id(),
                'facet_type'        => $type,
                'facet_path'        => $facetPath,
                'facet_parent_id'   => $parent,
            ]
        );

        SubscriptionModel::focus($new_facet_id['facet_id'], 'facet');

        $msg = $type === 'blog' ? __('msg.blog_added') : __('msg.change_saved');

        $url = url('redirect.facet', ['id' => $new_facet_id['facet_id']]);

        ActionModel::addLogs(
            [
                'id_content'    => $new_facet_id['facet_id'],
                'action_type'   => $type,
                'action_name'   => 'added',
                'url_content'   => $url,
            ]
        );

        Msg::redirect($msg, 'success', $url);
    }
}
