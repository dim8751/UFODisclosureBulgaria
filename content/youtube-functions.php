<?php
function getYoutubeVideosRSS($channelId) {
    // This function remains the same
    $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id=" . $channelId;
    
    try {
        $xml = @simplexml_load_file($feedUrl);
        if ($xml === false) {
            error_log('Failed to fetch RSS feed for channel: ' . $channelId);
            return [];
        }
        
        $videos = [];
        foreach ($xml->entry as $entry) {
            $videoId = (string)$entry->children('yt', true)->videoId;
            $mediaGroup = $entry->children('media', true)->group;
            $thumbnail = (string)$mediaGroup->children('media', true)->thumbnail->attributes()->url;
            
            $videos[] = [
                'id' => $videoId,
                'title' => (string)$entry->title,
                'thumbnail' => $thumbnail,
                'publishedAt' => strtotime((string)$entry->published)
            ];
        }
        
        return $videos;
        
    } catch (Exception $e) {
        error_log('Error fetching RSS feed: ' . $e->getMessage());
        return [];
    }
}

function getVideosPage($channelId, $page = 1, $perPage = 15, $sortOrder = 'newest') {
    // Get all videos first
    $allVideos = getYoutubeVideosRSS($channelId);
    
    // Sort all videos before pagination
    if ($sortOrder === 'newest') {
        usort($allVideos, function($a, $b) {
            return $b['publishedAt'] - $a['publishedAt'];
        });
    } else if ($sortOrder === 'oldest') {
        usort($allVideos, function($a, $b) {
            return $a['publishedAt'] - $b['publishedAt'];
        });
    }
    
    // Calculate pagination
    $offset = ($page - 1) * $perPage;
    $totalVideos = count($allVideos);
    $pageVideos = array_slice($allVideos, $offset, $perPage);
    
    $nextPage = ($offset + $perPage) < $totalVideos ? $page + 1 : null;
    
    return [
        'videos' => $pageVideos,
        'hasMore' => ($offset + $perPage) < $totalVideos,
        'totalVideos' => $totalVideos,
        'nextPage' => $nextPage
    ];
}
?>