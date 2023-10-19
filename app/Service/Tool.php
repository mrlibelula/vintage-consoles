<?php

namespace App\Service;

use DateTime;
use App\Models\User;

class Tool
{
    /**
     * base64 URL encode
     *
     * @param string $data
     * @return string
     */
    public static function base64url_encode(string $data) : string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * base64 URL decode
     *
     * @param string $data
     * @return string
     */
    public static function base64url_decode(string $data) : string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Encrypt encode ('base_64' or 'laravel')
     *
     * @param string $data, bool $method = 'base_64'
     * @return string
     */
    public static function encode(string $data, string $method = 'base_64') : string
    {
        if ($method == 'base_64') {
            return self::base64url_encode($data);
        } else if($method == 'laravel') { // laravel internal encoder
            return encrypt($data);
        } else {
            return self::base64url_encode($data);
        }
    }
    
    /**
     * Encrypt decode ('base_64' or 'laravel')
     *
     * @param string $data, bool $method = 'base_64'
     * @return string
     */
    public static function decode(string $data, string $method = 'base_64') : string
    {
        if ($method == 'base_64') {
            return self::base64url_decode($data);
        } else if ($method == 'laravel') { // laravel internal encoder
            return decrypt($data);
        } else {
            return self::base64url_decode($data);
        }
    }

    /**
     * Returns a random array item
     *
     * @param array $array
     * @return mixed
     */
    public static function randomItem(array $array)
    {
        if (!empty($array)) {
            return $array[rand(0, count($array) - 1)];
        }
    }

    /**
     * Returns a random image link
     *
     * @return string
     */
    public static function randomImage($wallps = [
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/9fa60a87711043.5dc0b3f7600c5.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/feff4f99264417.5f00bef987d5b.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/86117599264417.5f00bef8b8288.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/ed1d1599264417.5f00bef985e86.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/0f45f999264417.5f00bef98701f.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/8c3c7c100736353.5f282083bca00.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/eda317102944637.5f4260ee8c0e1.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/88e6b1106082287.5f8f0fcf6804f.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/662e79106082287.5f8f0fcf668df.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/da368a106082287.5f8f0fce33692.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/3421a8106800239.5f9976e68c0e5.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/3b0538111495737.600301bce6045.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/e45461116734023.6067df698e9c5.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/9af17187711043.5dc0b3f75d923.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/69fb7187711043.5dc0b3f75df49.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/87d4c787711043.5dc0b3f75ef0f.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/61c75387711043.5dc0b3f75c533.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/9fa60a87711043.5dc0b3f7600c5.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/1ed3da87711043.5dc0b3f75e82d.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/19098487711043.5dc0b3f75f84d.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/2e738587711043.5dc0b7748172d.jpg',
        'https://mir-s3-cdn-cf.behance.net/project_modules/1400/31f9d273718375.5c127ab020d5c.jpg',
        'https://www.wallpaperflare.com/static/806/713/236/blue-background-orange-fruit-blue-sliced-wallpaper.jpg'
    ]): string
    {
        return self::randomItem($wallps);
    }

    /**
     * Generates a random float|int number
     *
     * @param integer $st_num
     * @param integer $end_num
     * @param integer $mul
     * @return float|integer|false
     */
    public static function randFloat($st_num = 0, $end_num = 1, $mul = 1000000): float|int|false
    {
        if ($st_num > $end_num) return false;
        return mt_rand($st_num * $mul, $end_num * $mul) / $mul;
    }

    /**
     * Finds an array item inside an array
     * by given key and value
     * and returns that [key => value] pair
     *
     * @param array $array_of_arrays
     * @param string $find_key
     * @param [type] $find_value
     * @return array
     */
    public static function findItemByKey(array $array_of_arrays, string $find_key, $find_value): array
    {
        foreach ($array_of_arrays as $key => $array) {
            if (array_key_exists($find_key, $array)) {
                if ($array[$find_key] == $find_value) {
                    return collect([$key => $array])->first();
                }
            }
        }
        return [];
    }
    
    /**
     * Returns game route
     *
     * @param array $console
     * @param array $game
     * @return string
     */
    public static function gameRoute(array $console, array $game): string
    {
        return route('play', [
            self::encode($game['id']), 
            $console['short_name'], 
            $game['title'],
        ]);
    }
    
    /**
     * Dispatches a Livewire event to turn off all loaders
     *
     * @param \Livewire\Component $dispatch_component
     * @return void
     */
    public static function loadersOff(\Livewire\Component $dispatch_component): void
    {
        $dispatch_component->dispatch('loader-off');
        $dispatch_component->dispatch('loader-top-off');
        
        // skeletons off
        $dispatch_component->dispatch('skeleton-group-off');
        $dispatch_component->dispatch('skeleton-lista-off');
        $dispatch_component->dispatch('skeleton-square-off');
    }
    
    /**
     * Sort array by any key
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function sortBy(array $array, string $sort_by = 'timestamp', string $order_by = 'desc'): array
    {
        usort($array, function($a, $b) use($sort_by, $order_by) {
            return strtolower($order_by) === 'asc' 
                ? strtotime($a[$sort_by]) - strtotime($b[$sort_by])
                : strtotime($b[$sort_by]) - strtotime($a[$sort_by]);
        });
        return $array;
    }

    /**
     * Gets user name for chat
     * Returns "Guest" if not found
     *
     * @param integer|string|null $user_id
     * @return string
     */
    public static function userName(int|string|null $user_id): string
    {
        if ($user_id) {
            $user = User::find($user_id);
            if ($user) return $user->name;
        }
        return 'Guest';
    }

}