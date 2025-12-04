<?php

declare(strict_types=1);

use App\Models\{FacetModel, FileModel};
use App\Models\User\{UserModel, SettingModel};
use Phphleb\Imageresizer\SimpleImage;
use App\Bootstrap\Services\User\UserData;

class Img
{
    public const PATH = [
        'facets_logo'           => '/uploads/facets/logos/',
        'facets_logo_small'     => '/uploads/facets/logos/small/',
        'facets_cover'          => '/uploads/facets/cover/',
        'facets_cover_small'    => '/uploads/facets/cover/small/',
		'item_content'			=> '/uploads/items/',
        'thumbs'                => '/uploads/items/thumbs/'
    ];
	
	public static function createTempImage($imageData)
	{
		if (!$imageData) {
			return null;
		}

    	 $data = explode( ',', $imageData);

		// Obtain the original content (usually binary data)
		$imageData = base64_decode($data[1]);

		$temp_dir = HLEB_GLOBAL_DIR . '/storage/tmp';

		$tempFile = tempnam($temp_dir, 'og_image');
		if ($tempFile === false) {
			error_log("Не удалось создать временный файл");
			return null;
		}

		file_put_contents($tempFile, $imageData);

		$imageInfo = getimagesize($tempFile);
		if (!$imageInfo) {
			unlink($tempFile);
			error_log("Не удалось получить информацию об изображении");
			return null;
		}

		return [$imageInfo, $tempFile];
	}
	
    /**
     * Generate image tag
     *
     * @param string $file
     * @param string $alt
     * @param string $style
     * @param string $type
     * @return string
     */
    public static function image(string $file, string $alt, string $style, string $type = 'thumb'): string
    {
        switch ($type) {
            case 'logo':
                $path = self::PATH['facets_logo_small'];
                break;
            case 'thumb':
                $path = self::PATH['thumbs'];
				break;
        }
		
        return '<img class="' . $style . '" src="' . $path . $file . '" alt="' . $alt . '">';
    }

	
    public static function thumbImg($img, $item, $redirect)
    {
		[$imageInfo, $tempFile] =  Img::createTempImage($img);
	
        $path = HLEB_PUBLIC_DIR . Img::PATH['thumbs'];
        $year       = date('Y') . '/';
        $month      = date('n') . '/';
        $filename	= 't-' . time();

        self::createDir($path . $year . $month);

        $image = new SimpleImage();
        $image->load($tempFile);
        $image->resizeToWidth(900);
        $image->save($path . $year . $month . $filename . '.webp', "webp");

		unlink($tempFile);

        $item_img = $year . $month . $filename . '.webp';

        // Delete if there is an old one
        $item_thumb_img  = $item['item_thumb_img'] ?? false;
        if ($item_thumb_img != $item_img) {

            if ($item_thumb_img != false) {
                @unlink($path . $item_thumb_img);
            }

            FileModel::removal($item_thumb_img);
        }

        FileModel::set(
            [
                'file_path'         => $item_img,
                'file_type'         => 'item',
                'file_content_id'   => $item['item_id'] ?? 0,
                'file_user_id'      => UserData::getUserId(),
                'file_is_deleted'   => 0
            ]
        );

        return $item_img;
    }
	
    public static function itemImg($img, $type, $content_id)
    {
        $path_img   = HLEB_PUBLIC_DIR . Img::PATH['item_content'];
        $year       = date('Y') . '/';
        $month      = date('n') . '/';
        $file       = $img['tmp_name'];
        $filename   = 'post-' . time();

        // For the body of the post, if png then we will not change the file extension
        // Для тела поста, если png то не будем менять расширение файла
        $file_type = ($img['type'] == 'image/png') ? 'png' : 'webp';

        self::createDir($path_img . $year . $month);

        $image = new SimpleImage();

        $width_h  = getimagesize($file);
        if ($width_h[0] > 1050) {
            $image->load($file);
            $image->resizeToWidth(1050);
            $image->save($path_img . $year . $month . $filename . '.' . $file_type, $file_type, 100);
        } else {
            $image->load($file);
            $image->save($path_img . $year . $month . $filename . '.' . $file_type, $file_type, 100);
        }

        $img_post = Img::PATH['item_content'] . $year . $month . $filename . '.' . $file_type;
        FileModel::set(
            [
                'file_path'         => $img_post,
                'file_type'         => $type ?? 'none',
                'file_content_id'   => $content_id ?? 0,
                'file_user_id'      => UserData::getUserId(),
                'file_is_deleted'   => 0
            ]
        );

        return $img_post;
    }
	
	
    static function createDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
    }
	
    // Удаление обложка
    public static function thumbItemRemove($path_img)
    {
        unlink(HLEB_PUBLIC_DIR . Img::PATH['thumbs'] . $path_img);

        return FileModel::removal($path_img);
    }
}