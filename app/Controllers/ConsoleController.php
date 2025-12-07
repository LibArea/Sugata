<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Static\Request;
use Hleb\Base\Module;
use App\Bootstrap\Services\Auth\RegType;
use App\Models\{ConsoleModel, SearchModel};

use Msg;

use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Entity\Indexable;
use S2\Rose\Indexer;


class ConsoleController extends Module
{
    public static function index()
    {
        $choice  = Request::post('type')->value();

        $allowed = ['css', 'topic', 'post', 'up', 'tl', 'allContents', 'allFacets', 'allIndex', 'newIndex'];
        if (!in_array($choice, $allowed, true)) {
            redirect(url('admin.tools'));
        }
        self::$choice();
    }

    public static function all()
    {
        self::topic();
        self::post();
        self::up();
        self::tl();

        self::consoleRedirect();
    }

    public static function topic()
    {
        ConsoleModel::recalculateTopic();

        self::consoleRedirect();
    }

    public static function post()
    {
        ConsoleModel::recalculateCountCommentPost();

        self::consoleRedirect();
    }

    public static function up()
    {
        $users = ConsoleModel::allUsers();
        foreach ($users as $row) {
            $row['count']   =  ConsoleModel::allUp($row['id']);
            ConsoleModel::setAllUp($row['id'], $row['count']);
        }

        self::consoleRedirect();
    }

    /**
     * If the user has a 1 level of trust (tl) but he has UP > 2, then we raise it to 2
     * Если пользователь имеет 1 уровень доверия (tl) но ему UP > 2, то повышаем до 2
     *
     * @return void
     */
    public static function tl()
    {
        $users = ConsoleModel::getTrustLevel(RegType::USER_FIRST_LEVEL);
        foreach ($users as $row) {
            if ($row['up_count'] > 2) {
                ConsoleModel::setTrustLevel($row['id'], RegType::USER_SECOND_LEVEL);
            }
        }

        self::consoleRedirect();
    }

    public static function css()
    {
        (new \App\Controllers\SassController)->collect();

        self::consoleRedirect();
    }

    public static function consoleRedirect()
    {
        if (PHP_SAPI !== 'cli') {
            Msg::add(__('admin.completed'), 'success');
        }
        return true;
    }

    public static function migrations()
    {
        return true;
    }

    public static function allIndex()
    {
        // Удалим и заново построим таблицы при полной индексации
        $storage = SearchModel::PdoStorage();
        $storage->erase();

        self::allFacets();
    }

    public static function allFacets()
    {
        $indexer = self::indexer();

        // Индексируем Фасеты  
        $facets = SearchModel::getFacetsAll();
        foreach ($facets as $facet) {

            $indexableCat = new Indexable(
                (string)$facet['facet_id'],
                $facet['facet_title'],
                $facet['facet_info'] ?? '-',
                2 // 2 - фасеты
            );

            $indexableCat->setUrl($facet['facet_slug']);

            $indexer->index($indexableCat);
        }

        self::consoleRedirect();
    }


    public static function indexer()
    {
        $storage = SearchModel::PdoStorage();

        $stemmer = new PorterStemmerRussian(new PorterStemmerEnglish());

        return new Indexer($storage, $stemmer);
    }
}
