<?php

declare(strict_types=1);

namespace App\Models\User;

use Hleb\Base\Model;
use Hleb\Static\DB;

class UserModel extends Model
{
    public static function get(int|string $params, string $name = 'id')
    {
        $sort = ($name === 'id') ? "id = :params" : "login = :params";
        $sql = "SELECT 
                    id,
                    login,
                    name,
                    whisper,
                    activated,
                    limiting_mode,
                    scroll,
                    reg_ip,
                    email,
                    avatar,
                    trust_level,
                    cover_art,
                    color,
                    template,
                    lang,
                    invitation_available,
                    about,
                    website,
                    location,
                    public_email,
                    github,
                    skype,
                    telegram,
                    vk,
                    created_at,
                    updated_at,
                    my_post,
                    ban_list,
                    nsfw,
					post_design,
                    hits_count,
                    up_count,
                    is_deleted 
                        FROM users WHERE $sort";

        return DB::run($sql, ['params' => $params])->fetch();
    }

    public static function setLogAgent(array $params)
    {
        $sql = "INSERT INTO users_agent_logs(user_id, user_browser, user_os, user_ip) 
                    VALUES(:user_id, :user_browser, :user_os, :user_ip)";

        return DB::run($sql, $params);
    }

    public static function create(array $params)
    {
        $sql = "INSERT INTO users(login, 
                                    email,
                                    template,
                                    lang,
                                    whisper,
                                    password, 
                                    limiting_mode, 
                                    activated, 
                                    reg_ip, 
                                    trust_level, 
                                    invitation_id) 
                                    
                            VALUES(:login, 
                                    :email, 
                                    :template,
                                    :lang,
                                    :whisper,
                                    :password, 
                                    :limiting_mode, 
                                    :activated, 
                                    :reg_ip, 
                                    :trust_level, 
                                    :invitation_id)";

        DB::run($sql, $params);

        $sql_last_id =  DB::run("SELECT LAST_INSERT_ID() as last_id")->fetch();

        return $sql_last_id['last_id'];
    }


    public static function getUsersAll(int $page, int $limit, string $type): array|false
    {
        $user_id = self::container()->user()->id();
        $sort = ($type == 'new') ? "ORDER BY created_at DESC" : "ORDER BY id = $user_id DESC, avatar ASC";

        $start  = ($page - 1) * $limit;
        $sql = "SELECT  
                    id,
                    login,
                    email,
                    name,
                    avatar,
                    created_at,
                    whisper,
                    updated_at,
                    whisper,
                    trust_level,
                    activated,
                    invitation_id,
                    limiting_mode,
                    reg_ip,
                    ban_list,
                    is_deleted
                        FROM users 
                            WHERE is_deleted != 1 and ban_list != 1
                                $sort
                                    LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    public static function getUsersAllCount(): int
    {
        return  DB::run("SELECT id, is_deleted FROM users WHERE ban_list = 0")->rowCount();
    }

    /**
     * Amount of member content
     * оличество контента участника
     *
     * @return array|false
     */
    public static function contentCount($user_id, $type = 0)
    {
        $condition = $type === 'remote' ? 1 : 0;

        $sql = "SELECT 
                    (SELECT COUNT(post_id) FROM posts WHERE post_user_id = $user_id and post_draft = 0 and post_is_deleted = $condition) AS count_posts,
                  
                    (SELECT COUNT(comment_id) FROM comments WHERE comment_user_id = $user_id and comment_is_deleted = $condition) AS count_comments";

        return DB::run($sql)->fetch();
    }

    // Has the user been deleted?
    // Удален ли пользователь?
    public static function isDeleted($user_id)
    {
        $sql = "SELECT id, is_deleted FROM users WHERE id = :user_id AND is_deleted = 1";

        return DB::run($sql, ['user_id' => $user_id])->fetch();
    }
}
