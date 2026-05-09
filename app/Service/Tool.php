<?php

namespace App\Service;

use App\Models\User;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'console_short_name' => $console['short_name'], 
            'game_title_slug' => $game['slug'] ?? Str::slug($game['title']),
        ]);
    }
    
    /**
     * Dispatches a Livewire event to turn off all loaders and skeletons
     *
     * @param \Livewire\Component $dispatch_component
     * @return void
     */
    public static function loadersOff(\Livewire\Component $dispatch_component, ?array $events = null): void
    {
        $events ??= [
            'loader-off', 'loader-top-off', 'skeleton-group-off', 'skeleton-lista-off', 'skeleton-square-off',
        ];
        foreach ($events as $event) {
            $dispatch_component->dispatch($event);
        }
    }
    
    /**
     * Sort array by date (strtotime)
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function sortByDate(array $array, string $sort_by = 'timestamp', string $order_by = 'desc'): array
    {
        usort($array, function($a, $b) use($sort_by, $order_by) {
            return strtolower($order_by) === 'asc' 
                ? strtotime($a[$sort_by]) - strtotime($b[$sort_by])
                : strtotime($b[$sort_by]) - strtotime($a[$sort_by]);
        });
        return $array;
    }

    /**
     * Sort array by any key
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function sortBy(array $array, string $sort_by, string $order_by = 'desc'): array
    {
        usort($array, function($a, $b) use($sort_by, $order_by) {
            return strtolower($order_by) === 'asc' 
                ? $a[$sort_by] <=> $b[$sort_by]
                : $b[$sort_by] <=> $a[$sort_by];
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

    /**
     * Get unique array of genre name strings from session consoles data.
     * Throws ErrorException if session('consoles') is not set.
     */
    public static function getGenres(): array
    {
        $consoles = session('consoles');
        $genres = [];

        foreach ($consoles as $console) {
            if (isset($console['games'])) {
                foreach ($console['games'] as $game) {
                    if (isset($game['genres'])) {
                        foreach ($game['genres'] as $genre) {
                            $genres[] = $genre['name'];
                        }
                    }
                }
            }
        }

        return array_values(array_unique($genres));
    }

    /**
     * Create/Get unique [array] of genres (games) from existing consoles Session data
     */
    public static function genres($disk = 'data', $consolesJson = 'vintage-consoles.json'): array
    {
        if (!Session::has('consoles')) {
            new GameSession();
        }

        // For genres, we need full data - load from file when needed
        $gameSession = new GameSession();
        $consoles = $gameSession->getFullConsoleData();

        $genres = [];
        
        foreach ($consoles as $console) {
            if (isset($console['games'])) {
                foreach ($console['games'] as $game) {
                    if (isset($game['genres'])) {
                        foreach ($game['genres'] as $genre) {
                            $genreName = $genre['name'];
                            if (!isset($genres[$genreName])) {
                                $genres[$genreName] = [
                                    'name' => $genreName,
                                    'description' => $genre['description'] ?? '',
                                    'games_count' => 0
                                ];
                            }
                            $genres[$genreName]['games_count']++;
                        }
                    }
                }
            }
        }

        return array_values($genres);
    }

    /**
     * Create/Get unique [array] of publishers (games) from existing consoles Session data
     */
    public static function publishers(): array
    {
        if (!Session::has('consoles')) {
            new GameSession();
        }

        // For publishers, we need full data - load from file when needed  
        $gameSession = new GameSession();
        $consoles = $gameSession->getFullConsoleData();

        $publishers = [];
        
        foreach ($consoles as $console) {
            if (isset($console['games'])) {
                foreach ($console['games'] as $game) {
                    if (isset($game['publisher'])) {
                        $publisherName = $game['publisher'];
                        if (!isset($publishers[$publisherName])) {
                            $publishers[$publisherName] = [
                                'name' => $publisherName,
                                'games_count' => 0
                            ];
                        }
                        $publishers[$publisherName]['games_count']++;
                    }
                }
            }
        }

        return array_values($publishers);
    }

    /**
     * Checks if all db image urls exist and also checks for redirects
     * Must be run in terminal
     *
     * @return mixed
     */
    public static function checkIfDBImageUrlsExist()
    {
        // check if terminal
        if (php_sapi_name() !== 'cli') {
            echo "This script must be run in the terminal.\n";
            return;
        }
        
        echo "\n📋 Starting URL check process...\n\n";
        
        // Obtain all image urls from db
        $imageUrls = [];
        $consolesJson = 'vintage-consoles.json';
        $storage = Storage::disk('data');
        $consoles = collect($storage->exists($consolesJson) 
            ? (json_decode($storage->get($consolesJson), true)['consoles'] ?? []) 
            : []);
        
        // Get all image urls
        $consoles->each(function($console) use(&$imageUrls) {
            $imageUrls[] = url($console['console_logo']);
            $imageUrls[] = url($console['console_icon']);
            $imageUrls = array_merge($imageUrls, array_map(function($bg) {
                return url($bg);
            }, ($console['console_bgs'] ? $console['console_bgs'] : [])));
            foreach ($console['games'] as $game) {
                $imageUrls[] = url($game['box']);
                $imageUrls[] = url($game['poster']);
                $imageUrls[] = url($game['cartridge']);
                $imageUrls = array_merge($imageUrls, array_map(function($screenshot) {
                    return url($screenshot);
                }, ($game['screenshots'] ? $game['screenshots'] : [])));
            }
        });

        $totalUrls = count($imageUrls);
        echo "🔍 Found {$totalUrls} URLs to check\n\n";
        
        // Check if they exist
        $report = [];
        $processed = 0;
        $failed = 0;
        $failedUrls = [];
        
        foreach ($imageUrls as $imageUrl) {
            $processed++;
            
            // Clear line and show progress
            echo "\r\033[K"; // Clear the current line
            echo "⏳ Progress: {$processed}/{$totalUrls} (" . round(($processed / $totalUrls) * 100, 1) . "%)";
            
            $headers = @get_headers($imageUrl, 1);
            if ($headers === false) {
                $report[$imageUrl] = 'failed_to_connect';
                $failed++;
                $failedUrls[] = $imageUrl;
                echo "\r\033[K"; // Clear the current line
                echo "❌ Failed to connect: " . $imageUrl . "\n";
                continue;
            }

            // Check status line
            $statusLine = is_array($headers) && isset($headers[0]) ? (string)$headers[0] : '';
            
            if (empty($statusLine)) {
                $report[$imageUrl] = 'invalid_response';
                $failed++;
                $failedUrls[] = $imageUrl;
                continue;
            }

            // Determine status
            if (str_contains($statusLine, '200')) {
                $report[$imageUrl] = 'ok';
            } 
            else if (str_contains($statusLine, '301')) {
                $finalLocation = $headers['Location'] ?? $headers['location'] ?? null;
                $report[$imageUrl] = 'redirect_301' . ($finalLocation ? " -> {$finalLocation}" : '');
                $failed++;
                $failedUrls[] = $imageUrl;
                echo "\r\033[K"; // Clear the current line
                echo "↪️ Permanent Redirect: {$imageUrl} -> {$finalLocation}\n";
            }
            else if (str_contains($statusLine, '302')) {
                $finalLocation = $headers['Location'] ?? $headers['location'] ?? null;
                $report[$imageUrl] = 'redirect_302' . ($finalLocation ? " -> {$finalLocation}" : '');
                $failed++;
                $failedUrls[] = $imageUrl;
                echo "\r\033[K"; // Clear the current line
                echo "↪️ Temporary Redirect: {$imageUrl} -> {$finalLocation}\n";
            }
            else if (str_contains($statusLine, '404')) {
                $report[$imageUrl] = 'not_found';
                $failed++;
                echo "\r\033[K"; // Clear the current line
                echo "❌ Not Found: " . $imageUrl . "\n";
            }
            else if (str_contains($statusLine, '403')) {
                $report[$imageUrl] = 'forbidden';
                $failed++;
                echo "\r\033[K"; // Clear the current line
                echo "🚫 Forbidden: " . $imageUrl . "\n";
            }
            else {
                $report[$imageUrl] = 'unknown_status: ' . $statusLine;
                $failed++;
                $failedUrls[] = $imageUrl;
                echo "\r\033[K"; // Clear the current line
                echo "❓ Unknown Status: " . $imageUrl . " (" . $statusLine . ")\n";
            }
        }

        // Final Summary
        echo "\n\n📊 URL Check Complete!\n";
        echo str_repeat("=", 50) . "\n";
        echo "✓ Total processed: {$processed}\n";
        echo "✗ Failed URLs: {$failed}\n";
        echo "📈 Success rate: " . round((($processed - $failed) / $processed) * 100, 2) . "%\n";
        echo str_repeat("=", 50) . "\n\n";
        
        if ($failed > 0) {
            echo "❌ Failed URLs Summary:\n";
            echo str_repeat("-", 100) . "\n";
            
            foreach ($report as $url => $status) {
                if ($status !== 'ok') {
                    echo "• {$url}\n  └─ Status: {$status}\n";
                }
            }
            
            echo str_repeat("-", 100) . "\n\n";
        }
        
        // Save report in json file
        $reportJson = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_urls' => $totalUrls,
            'processed' => $processed,
            'failed' => $failed,
            'success_rate' => round((($processed - $failed) / $processed) * 100, 2) . "%",
            'report' => $report,
            'failed_urls' => array_filter($report, fn($status) => $status !== 'ok'),
        ];
        
        $storage->put('url-check-report.json', json_encode($reportJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // return;
    }

    /**
     * Updates or creates JSON properties values and returns updated JSON string
     *
     * @param string $original_json_data
     * @param array $updated_data_array
     * @return string|false
     */
    public static function updateOrCreateJsonColumns(string $original_json_data, array $updated_data_array): string|false
    {
        $updated_json_columns = json_decode($original_json_data, true);
        foreach ($updated_data_array as $column => $new_value) {
            $updated_json_columns[$column] = $new_value;
        }
        $updated_json_columns = json_encode($updated_json_columns);
        return $updated_json_columns;
    }
}