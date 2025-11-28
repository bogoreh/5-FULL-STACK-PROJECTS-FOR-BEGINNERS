<?php
// this is a centralized TMDB API client for php 
class TMDBClient {
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';
    private $imageBaseUrl = 'https://image.tmdb.org/t/p/';

   
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

  
    private function makeRequest($endpoint, $params = []) {
        $params['api_key'] = $this->apiKey;
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }

    /**
     * Get trending movies/tv shows
     */
    public function getTrending($mediaType = 'all', $timeWindow = 'day') {
        return $this->makeRequest("/trending/$mediaType/$timeWindow");
    }

    /**
     * Get popular movies
     */
    public function getPopularMovies($page = 1) {
        return $this->makeRequest('/movie/popular', ['page' => $page]);
    }

    /**
     * Get popular TV shows
     */
    public function getPopularTVShows($page = 1) {
        return $this->makeRequest('/tv/popular', ['page' => $page]);
    }

    /**
     * Get movie details
     */
    public function getMovieDetails($id) {
        return $this->makeRequest("/movie/$id", ['append_to_response' => 'credits,videos,images,similar']);
    }

    /**
     * Get TV show details
     */
    public function getTVShowDetails($id) {
        return $this->makeRequest("/tv/$id", ['append_to_response' => 'credits,videos,images,similar,seasons']);
    }

    /**
     * Get TV season details
     */
    public function getTVSeasonDetails($tvId, $seasonNumber) {
        return $this->makeRequest("/tv/$tvId/season/$seasonNumber");
    }

    /**
     * Get TV episode details
     */
    public function getTVEpisodeDetails($tvId, $seasonNumber, $episodeNumber) {
        return $this->makeRequest("/tv/$tvId/season/$seasonNumber/episode/$episodeNumber");
    }

    /**
     * Search TMDB
     */
    public function search($query, $type = 'multi', $page = 1) {
        return $this->makeRequest("/search/$type", [
            'query' => $query,
            'page' => $page
        ]);
    }

    /**
     * Get movie genres
     */
    public function getMovieGenres() {
        return $this->makeRequest('/genre/movie/list');
    }

    /**
     * Get TV genres
     */
    public function getTVGenres() {
        return $this->makeRequest('/genre/tv/list');
    }

    /**
     * Discover movies
     */
    public function discoverMovies($params = []) {
        return $this->makeRequest('/discover/movie', $params);
    }

    /**
     * Discover TV shows
     */
    public function discoverTVShows($params = []) {
        return $this->makeRequest('/discover/tv', $params);
    }

    /**
     * Get person details
     */
    public function getPersonDetails($personId) {
        return $this->makeRequest("/person/$personId", ['append_to_response' => 'combined_credits,images,external_ids']);
    }

    /**
     * Get now playing movies
     */
    public function getNowPlayingMovies($page = 1) {
        return $this->makeRequest('/movie/now_playing', ['page' => $page]);
    }
    
    /**
     * Get upcoming movies
     */
    public function getUpcomingMovies($page = 1) {
        return $this->makeRequest('/movie/upcoming', ['page' => $page]);
    }
    
    /**
     * Get top rated movies
     */
    public function getTopRatedMovies($page = 1) {
        return $this->makeRequest('/movie/top_rated', ['page' => $page]);
    }
    
    /**
     * Get top rated TV shows
     */
    public function getTopRatedTVShows($page = 1) {
        return $this->makeRequest('/tv/top_rated', ['page' => $page]);
    }
    
    /**
     * Get TV shows airing today
     */
    public function getTVAiringToday($page = 1) {
        return $this->makeRequest('/tv/airing_today', ['page' => $page]);
    }
    
    /**
     * Get TV shows on the air
     */
    public function getTVOnTheAir($page = 1) {
        return $this->makeRequest('/tv/on_the_air', ['page' => $page]);
    }
    
    /**
     * Get movie recommendations
     */
    public function getMovieRecommendations($movieId, $page = 1) {
        return $this->makeRequest("/movie/{$movieId}/recommendations", ['page' => $page]);
    }
    
    /**
     * Get TV show recommendations
     */
    public function getTVShowRecommendations($tvId, $page = 1) {
        return $this->makeRequest("/tv/{$tvId}/recommendations", ['page' => $page]);
    }
    
    /**
     * Get movie reviews
     */
    public function getMovieReviews($movieId, $page = 1) {
        return $this->makeRequest("/movie/{$movieId}/reviews", ['page' => $page]);
    }
    
    /**
     * Get TV show reviews
     */
    public function getTVShowReviews($tvId, $page = 1) {
        return $this->makeRequest("/tv/{$tvId}/reviews", ['page' => $page]);
    }

    /**
     * Get popular people
     */
    public function getPopularPeople($page = 1) {
        return $this->makeRequest("person/popular", ['page' => $page]);
    }
    
    /**
     * Get trending people
     */
    public function getTrendingPeople($timeWindow = 'day', $page = 1) {
        return $this->makeRequest("/trending/person/{$timeWindow}", ['page' => $page]);
    }
    
    /**
     * Search for people
     */
    public function searchPeople($query, $page = 1, $includeAdult = false) {
        return $this->makeRequest("/search/person", [
            'query' => $query,
            'page' => $page,
            'include_adult' => $includeAdult
        ]);
    }
}
?>
