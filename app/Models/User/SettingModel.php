<?php

declare(strict_types=1);

namespace App\Models\User;

use Hleb\Base\Model;
use Hleb\Static\DB;

class SettingModel extends Model
{
    // Editing a profile
    // Редактирование профиля
    public static function edit($params)
    {
        $sql = "UPDATE users SET 
                    email           = :email,  
                    login           = :login, 
                    whisper         = :whisper, 
                    name            = :name,
                    activated       = :activated,
                    limiting_mode   = :limiting_mode,
                    scroll          = :scroll,
                    nsfw            = :nsfw,
					post_design 	= :post_design,
                    template        = :template,
                    lang            = :lang,
                    updated_at      = :updated_at,
                    color           = :color,
                    about           = :about,
                    trust_level     = :trust_level,
                    website         = :website,
                    location        = :location,
                    public_email    = :public_email,
                    github          = :github,
                    skype           = :skype,
                    telegram        = :telegram,
                    vk              = :vk
                        WHERE id    = :id";

        return DB::run($sql, $params);
    }

    // Changing the password
    // Изменение пароля
    public static function editPassword($params)
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";

        return  DB::run($sql, $params);
    }

    // Change of mail
    public static function getNewEmail()
    {
        $sql = "SELECT email FROM users_email_story WHERE user_id = :user_id AND email_activate_flag = :flag";

        return DB::run($sql, ['user_id' => self::container()->user()->id(), 'flag' => 0])->fetch();
    }
    
    public static function setNewEmail($email, $code)
    {
        $params = [
            'user_id'               => self::container()->user()->id(),
            'email'                 => $email,
            'email_code'            => $code,
        ];
        
        $sql = "INSERT INTO users_email_story(user_id, email, email_code) VALUES(:user_id, :email, :email_code)";

        return DB::run($sql, $params);
    }

    public static function available($code)
    {
        $sql = "SELECT email_activate_flag FROM users_email_story WHERE email_code = :code AND user_id = :user_id AND email_activate_flag = :flag";

        return DB::run($sql, ['code' => $code, 'user_id' => self::container()->user()->id(), 'flag' => 0])->fetch();
    }

    public static function editEmail($email)
    {
        DB::run("UPDATE users SET email = :email WHERE id = :user_id", ['user_id' => self::container()->user()->id(), 'email' => $email]);
        
        $sql = "UPDATE users_email_story SET email_activate_flag = :flag WHERE user_id = :user_id AND email = :email";

        return DB::run($sql, ['user_id' => self::container()->user()->id(), 'email' => $email, 'flag' => 1]);
    }
    
    public static function deletionUser($user_id)
    {
        return  DB::run("UPDATE users SET is_deleted = :deleted WHERE id = :user_id", ['user_id' => $user_id, 'deleted' => 1]);
    }
}
