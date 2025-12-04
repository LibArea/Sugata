<?php

declare(strict_types=1);

namespace App\Controllers\Facet;

use Hleb\Static\Request;
use Hleb\Base\Controller;
use App\Models\FacetModel;

use Msg;

class RedirectController extends Controller
{
    public function index(): void
    {
        $facet_id  = Request::param('id')->asPositiveInt();
        $facet = FacetModel::uniqueById($facet_id);

        Msg::redirect(_('msg.change_saved'), 'success', '/mod/admin/edit/facet/category/' . $facet_id);
    }
}
