<?php

namespace App\Services;

use Exception;

/**
 * Instagram Graph API Service
 * 
 * Handles all interactions with Instagram's Graph API
 * - OAuth token management
 * - Fetching following/followers lists
 * - Getting user profile data
 * - Managing rate limits and retries
 */
class InstagramApiService
{
    private $apiBase = 'https://graph.instagram.com/v20.0';
    private $oauthBase = 'https://api.instagram.com/oauth';
    
    private $appId;
    private $appSecret;
    private $redirectUri;
    
    private $userToken;
    private $businessAccountId;
    
    // Rate limiting
    private $rateLimitRemaining = 200;
    private $rateLimitResetTime = 0;
    private $maxRetries = 3;
    private $retryDelay = 1000; // milliseconds
    
    public function __construct($appId, $appSecret, $redirectUri, $userToken = null)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->redirectUri = $redirectUri;
        $this->userToken = $userToken;
    }

    /**
     * Set user access token
     */
    public function setUserToken($token)
    {
        $this->userToken = $token;
        return $this;
    }

    /**
     * Get OAuth authorization URL
     * 
     * @param string $state CSRF protection state token
     * @return string Authorization URL
     */
    public function getAuthorizationUrl($state)
    {
        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'user_profile,instagram_basic,instagram_graph_user_media',
            'response_type' => 'code',
            'state' => $state,
        ];

        return $this->oauthBase . '/authorize?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     * 
     * @param string $code Authorization code from OAuth callback
     * @return array ['access_token' => '...', 'user_id' => '...', 'expires_in' => 5184000]
     */
    public function exchangeCodeForToken($code)
    {
        $params = [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ];

        $response = $this->request('POST', $this->oauthBase . '/access_token', $params);

        if (isset($response['access_token'])) {
            $this->userToken = $response['access_token'];
            return $response;
        }

        throw new Exception('Failed to exchange code for token: ' . ($response['error_description'] ?? 'Unknown error'));
    }

    /**
     * Refresh long-lived access token
     * Converts a 2-hour token to 60-day token
     * 
     * @param string $token Short-lived access token
     * @return array ['access_token' => '...', 'expires_in' => 5184000]
     */
    public function refreshAccessToken($token)
    {
        $params = [
            'grant_type' => 'ig_refresh_access_token',
            'access_token' => $token,
        ];

        $response = $this->request('GET', $this->apiBase . '/refresh_access_token', $params);

        if (isset($response['access_token'])) {
            $this->userToken = $response['access_token'];
            return $response;
        }

        throw new Exception('Failed to refresh access token: ' . ($response['error']['message'] ?? 'Unknown error'));
    }

    /**
     * Get Instagram business account information
     * Uses the token to identify the current user's Instagram account
     * 
     * @return array Account info: id, username, name, website, profile_picture_url, biography, verified
     */
    public function getBusinessAccount()
    {
        $params = [
            'fields' => 'id,username,name,website,profile_picture_url,biography,ig_id,followers_count,follows_count',
            'access_token' => $this->userToken,
        ];

        $response = $this->request('GET', $this->apiBase . '/me', $params);

        if (isset($response['id'])) {
            $this->businessAccountId = $response['id'];
            return $response;
        }

        throw new Exception('Failed to get business account: ' . ($response['error']['message'] ?? 'Unknown error'));
    }

    /**
     * Fetch list of accounts this user is following
     * Paginated to handle large follower lists
     * 
     * @param string $after Pagination cursor
     * @return array [
     *     'data' => [['id' => '...', 'username' => '...', 'name' => '...'], ...],
     *     'paging' => ['cursors' => ['after' => '...', 'before' => '...']]
     * ]
     */
    public function getFollowing($after = null)
    {
        $params = [
            'fields' => 'id,username,name,profile_picture_url,followers_count,follows_count,ig_id,biography',
            'limit' => 100, // Max allowed by API
            'access_token' => $this->userToken,
        ];

        if ($after) {
            $params['after'] = $after;
        }

        $response = $this->request('GET', $this->apiBase . '/' . $this->businessAccountId . '/ig_following', $params);

        if (isset($response['data'])) {
            return $response;
        }

        throw new Exception('Failed to fetch following list: ' . ($response['error']['message'] ?? 'Unknown error'));
    }

    /**
     * Fetch list of followers (accounts following this user)
     * Paginated to handle large follower lists
     * 
     * @param string $after Pagination cursor
     * @return array [
     *     'data' => [['id' => '...', 'username' => '...', 'profile_picture_url' => '...'], ...],
     *     'paging' => ['cursors' => ['after' => '...', 'before' => '...']]
     * ]
     */
    public function getFollowers($after = null)
    {
        $params = [
            'fields' => 'id,username,name,profile_picture_url,followers_count,follows_count,ig_id,biography',
            'limit' => 100, // Max allowed by API
            'access_token' => $this->userToken,
        ];

        if ($after) {
            $params['after'] = $after;
        }

        $response = $this->request('GET', $this->apiBase . '/' . $this->businessAccountId . '/ig_followers', $params);

        if (isset($response['data'])) {
            return $response;
        }

        throw new Exception('Failed to fetch followers list: ' . ($response['error']['message'] ?? 'Unknown error'));
    }

    /**
     * Get detailed information about a specific Instagram user
     * 
     * @param string $userId Instagram user ID
     * @return array User info: id, username, name, profile_picture_url, biography, followers_count, follows_count
     */
    public function getUserInfo($userId)
    {
        $params = [
            'fields' => 'id,username,name,profile_picture_url,biography,followers_count,follows_count,ig_id,website',
            'access_token' => $this->userToken,
        ];

        $response = $this->request('GET', $this->apiBase . '/' . $userId, $params);

        if (isset($response['id'])) {
            return $response;
        }

        throw new Exception('Failed to get user info: ' . ($response['error']['message'] ?? 'Unknown error'));
    }

    /**
     * Unfollow an Instagram user
     * Removes the user from your following list
     * 
     * @param string $userId Instagram user ID to unfollow
     * @return bool True if successful
     */
    public function unfollow($userId)
    {
        // Construct endpoint: /me/ig_following with user to remove
        $params = [
            'user_id' => $userId,
            'access_token' => $this->userToken,
        ];

        // DELETE request to remove from following
        $response = $this->request('POST', $this->apiBase . '/' . $this->businessAccountId . '/ig_unfollow', $params);

        if (isset($response['success']) && $response['success']) {
            return true;
        }

        // Some endpoints return just success: true
        if ($response === true || (is_array($response) && empty($response) === false && isset($response['success']))) {
            return true;
        }

        throw new Exception('Failed to unfollow user: ' . ($response['error']['message'] ?? 'Unknown error'));
    }

    /**
     * Check if a user has a verified badge
     * 
     * @param string $userId Instagram user ID
     * @return bool True if user is verified
     */
    public function isVerified($userId)
    {
        try {
            $userInfo = $this->getUserInfo($userId);
            return isset($userInfo['verified']) && $userInfo['verified'] === true;
        } catch (Exception $e) {
            // If we can't get info, assume not verified
            return false;
        }
    }

    /**
     * Check rate limit status
     * 
     * @return array ['remaining' => 195, 'reset_time' => 1234567890, 'has_limits' => true]
     */
    public function getRateLimitStatus()
    {
        return [
            'remaining' => $this->rateLimitRemaining,
            'reset_time' => $this->rateLimitResetTime,
            'has_limits' => $this->rateLimitResetTime > time(),
        ];
    }

    /**
     * Check if we should back off due to rate limits
     * 
     * @return bool True if we're rate limited
     */
    public function isRateLimited()
    {
        return $this->rateLimitResetTime > time() && $this->rateLimitRemaining < 50;
    }

    /**
     * Make HTTP request to API
     * Handles retries and rate limit headers
     * 
     * @param string $method GET, POST, DELETE
     * @param string $url Full API endpoint URL
     * @param array $params Query/body parameters
     * @return array API response decoded as array
     */
    private function request($method, $url, $params = [])
    {
        $retryCount = 0;

        while ($retryCount < $this->maxRetries) {
            try {
                // Check rate limit before making request
                if ($this->isRateLimited()) {
                    $waitTime = $this->rateLimitResetTime - time();
                    sleep(max(1, min($waitTime, 30))); // Max 30 second wait
                }

                // Build URL with query params for GET requests
                $requestUrl = $url;
                if ($method === 'GET') {
                    $requestUrl .= '?' . http_build_query($params);
                }

                // Initialize cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $requestUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                // Set method and body for POST
                if ($method === 'POST') {
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                }

                // Set headers
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                ]);

                // Execute request
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                // Handle cURL errors
                if ($curlError) {
                    if ($retryCount < $this->maxRetries - 1) {
                        $retryCount++;
                        usleep($this->retryDelay * 1000);
                        continue;
                    }
                    throw new Exception('Request failed: ' . $curlError);
                }

                // Parse response
                $data = json_decode($response, true);

                // Update rate limit info from response headers if available
                // Instagram returns x-ratelimit-* headers in response
                // Note: This is best effort - may not always be available

                // Handle HTTP errors
                if ($httpCode >= 400) {
                    if ($httpCode === 429) {
                        // Too many requests - wait and retry
                        if ($retryCount < $this->maxRetries - 1) {
                            $retryCount++;
                            sleep(5); // Wait 5 seconds before retry
                            continue;
                        }
                        throw new Exception('Rate limited by Instagram API');
                    }

                    if (isset($data['error'])) {
                        throw new Exception('API Error: ' . ($data['error']['message'] ?? 'Unknown error'));
                    }

                    throw new Exception('API Error: HTTP ' . $httpCode);
                }

                // Success
                return $data ?? [];
            } catch (Exception $e) {
                if ($retryCount < $this->maxRetries - 1) {
                    $retryCount++;
                    usleep($this->retryDelay * 1000);
                    continue;
                }

                throw $e;
            }
        }

        throw new Exception('Request failed after ' . $this->maxRetries . ' retries');
    }

    /**
     * Test API connection
     * Verifies the token is valid and can reach Instagram API
     * 
     * @return bool True if connection is valid
     */
    public function testConnection()
    {
        try {
            $this->getBusinessAccount();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
