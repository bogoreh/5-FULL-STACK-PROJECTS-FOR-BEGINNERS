<?php
require_once 'config.php';
require_once 'TMDBClient.php';


$tmdb = new TMDBClient(TMDB_API_KEY);


$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';

if (isset($_GET['theme']) && ($_GET['theme'] == 'dark' || $_GET['theme'] == 'light')) {
    $theme = $_GET['theme'];
    setcookie('theme', $theme, time() + (86400 * 30), "/"); // 30 days
}


$trendingAll = $tmdb->getTrending('all', 'day');
$trendingMovies = $tmdb->getTrending('movie', 'week');
$trendingTVShows = $tmdb->getTrending('tv', 'week');

//here you can update how many slides should appear at a time 
$heroSlides = [];
$movieCount = 0;
$tvCount = 0;
$maxPerType = 3;


foreach ($trendingAll['results'] as $item) {
    if (
        isset($item['backdrop_path']) && !empty($item['backdrop_path']) &&
        isset($item['overview']) && !empty($item['overview'])
    ) {

        $isMovie = isset($item['title']);


        if ($isMovie && $movieCount < $maxPerType) {
            $heroSlides[] = $item;
            $movieCount++;
        } elseif (!$isMovie && $tvCount < $maxPerType) {
            $heroSlides[] = $item;
            $tvCount++;
        }


        if ($movieCount >= $maxPerType && $tvCount >= $maxPerType) {
            break;
        }
    }
}
//here you are suffling the slides to be mixed between movies and slideshows and a maximum of 5 combined
shuffle($heroSlides);
$heroSlides = array_slice($heroSlides, 0, 5);


if ($page == 'home') {
    $upcomingMovies = $tmdb->getUpcomingMovies();
    $nowPlayingMovies = $tmdb->getNowPlayingMovies();
    $topRatedMovies = $tmdb->getTopRatedMovies();
    $airingToday = $tmdb->getTVAiringToday();
}


$searchResults = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchResults = $tmdb->search($_GET['search']);
}

if ($page == 'discover') {
    $discoverType = isset($_GET['type']) ? $_GET['type'] : 'movie';
    $filters = [];


    if (isset($_GET['sort_by']) && !empty($_GET['sort_by'])) {
        $filters['sort_by'] = $_GET['sort_by'];
    }


    if (isset($_GET['with_genres']) && !empty($_GET['with_genres'])) {
        $filters['with_genres'] = $_GET['with_genres'];
    }

    if (isset($_GET['year']) && !empty($_GET['year'])) {
        if ($discoverType == 'movie') {
            $filters['primary_release_year'] = $_GET['year'];
        } else {
            $filters['first_air_date_year'] = $_GET['year'];
        }
    }


    if (isset($_GET['vote_average']) && !empty($_GET['vote_average'])) {
        $filters['vote_average.gte'] = $_GET['vote_average'];
    }


    $currentPage = isset($_GET['discover_page']) ? intval($_GET['discover_page']) : 1;
    $filters['page'] = $currentPage;

    $discoverResults = $discoverType == 'movie'
        ? $tmdb->discoverMovies($filters)
        : $tmdb->discoverTVShows($filters);


    $genres = $discoverType == 'movie'
        ? $tmdb->getMovieGenres()
        : $tmdb->getTVGenres();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $theme ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="manases">
    <title>TMDB Explorer - Your Movie & TV Database</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="images\tmdb-logo.jpg" type="image/x-icon">
    <link rel="icon" href="images\tmdb-logo.jpg" type="image/jpg">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="styles.css" rel="stylesheet">
    <!-- Preload hero slideshow images -->
    <?php foreach ($heroSlides as $slide): ?>
        <link rel="preload" as="image" href="<?= TMDB_IMAGE_BASE_URL . 'original' . $slide['backdrop_path'] ?>">
    <?php endforeach; ?>
</head>

<body class="<?= $theme ?>-mode">

    <a href="https://github.com/manasess896" class="github-corner" aria-label="View source on GitHub">
        <svg width="80" height="80" viewBox="0 0 250 250" style="fill:#151513; color:#fff; position: absolute; top: 0; border: 0; right: 0; z-index: 9999;" aria-hidden="true">
            <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
            <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path>
            <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path>
        </svg>
        <style>
            .github-corner:hover .octo-arm {
                animation: octocat-wave 560ms ease-in-out
            }

            @keyframes octocat-wave {

                0%,
                100% {
                    transform: rotate(0)
                }

                20%,
                60% {
                    transform: rotate(-25deg)
                }

                40%,
                80% {
                    transform: rotate(10deg)
                }
            }

            @media (max-width:500px) {
                .github-corner:hover .octo-arm {
                    animation: none
                }

                .github-corner .octo-arm {
                    animation: octocat-wave 560ms ease-in-out
                }
            }
        </style>
    </a>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home">
                <span class="text-primary fw-bold">TMDB</span><span class="text-light">Explorer</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'home' ? 'active' : '' ?>" href="home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'movies' ? 'active' : '' ?>" href="home?page=movies">Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'tvshows' ? 'active' : '' ?>" href="home?page=tvshows">TV Shows</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'discover' ? 'active' : '' ?>" href="home?page=discover">Discover</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'people' ? 'active' : '' ?>" href="home?page=people">People</a>
                    </li>
                </ul>
                <form class="d-flex me-2" action="home" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search..."
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <div class="theme-switch-wrapper">
                    <a href="?<?= http_build_query(array_merge($_GET, ['theme' => $theme == 'dark' ? 'light' : 'dark'])) ?>"
                        class="theme-switch btn btn-sm <?= $theme == 'dark' ? 'btn-light' : 'btn-dark' ?>"
                        aria-label="Toggle theme">
                        <i class="fas <?= $theme == 'dark' ? 'fa-sun' : 'fa-moon' ?>"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>

        <section class="search-results py-5">
            <div class="container">
                <h2 class="mb-4">Search Results for "<?= htmlspecialchars($_GET['search']) ?>"</h2>
                <div class="row">
                    <?php if ($searchResults && isset($searchResults['results']) && count($searchResults['results']) > 0): ?>
                        <?php foreach ($searchResults['results'] as $item): ?>
                            <?php if (($item['media_type'] == 'movie' || $item['media_type'] == 'tv') && isset($item['poster_path'])): ?>
                                <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="fade-up">
                                    <div class="card content-card h-100">
                                        <a href="home?page=<?= $item['media_type'] == 'movie' ? 'movie' : 'tvshow' ?>&id=<?= $item['id'] ?>">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $item['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($item['title'] ?? $item['name']) ?>">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($item['title'] ?? $item['name']) ?></h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-primary"><?= ucfirst($item['media_type']) ?></span>
                                                    <span class="rating">
                                                        <i class="fas fa-star text-warning"></i>
                                                        <?= number_format($item['vote_average'], 1) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No results found.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php elseif ($page == 'home'): ?>

        <section class="hero-slideshow">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <?php
                $isMovie = isset($slide['title']);
                $title = $isMovie ? $slide['title'] : $slide['name'];
                $id = $slide['id'];
                $type = $isMovie ? 'movie' : 'tvshow';
                $releaseDate = $isMovie ?
                    (isset($slide['release_date']) ? $slide['release_date'] : null) : (isset($slide['first_air_date']) ? $slide['first_air_date'] : null);
                $year = $releaseDate ? date('Y', strtotime($releaseDate)) : 'N/A';
                $mediaType = $isMovie ? 'Movie' : 'TV Show';
                ?>
                <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>"
                    style="background-image: url('<?= TMDB_IMAGE_BASE_URL . 'original' . $slide['backdrop_path'] ?>');"
                    data-id="<?= $index ?>">
                    <div class="container">
                        <div class="hero-content">
                            <h1 class="hero-title"><?= htmlspecialchars($title) ?></h1>
                            <div class="hero-info">
                                <span class="badge bg-primary"><?= $mediaType ?></span>
                                <?php if ($releaseDate): ?>
                                    <span><i class="far fa-calendar-alt"></i> <?= $year ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-star text-warning"></i> <?= number_format($slide['vote_average'], 1) ?></span>
                            </div>
                            <p class="hero-overview"><?= htmlspecialchars($slide['overview']) ?></p>

                            <a href="home?page=<?= $type ?>&id=<?= $id ?>" class="btn btn-primary btn-lg">
                                View Details <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="hero-controls">
                <button class="hero-control prev-slide">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="hero-control next-slide">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>


            <div class="hero-indicators">
                <?php foreach ($heroSlides as $index => $slide): ?>
                    <div class="hero-indicator <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></div>
                <?php endforeach; ?>
            </div>
        </section>


        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 data-aos="fade-right">Now Playing Movies</h2>
                    <a href="home?page=discover&type=movie&sort_by=popularity.desc" class="btn btn-outline-primary" data-aos="fade-left">View All</a>
                </div>
                <div class="row content-slider">
                    <?php foreach (array_slice($nowPlayingMovies['results'], 0, 12) as $index => $movie): ?>
                        <?php if (isset($movie['poster_path'])): ?>
                            <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                                <div class="card content-card h-100">
                                    <a href="home?page=movie&id=<?= $movie['id'] ?>">
                                        <div class="card-img-wrapper">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $movie['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($movie['title']) ?></h6>
                                            <div class="rating">
                                                <i class="fas fa-star text-warning"></i>
                                                <?= number_format($movie['vote_average'], 1) ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>


        <section class="py-5 bg-alt">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 data-aos="fade-right">Upcoming Movies</h2>
                    <a href="home?page=discover&type=movie&sort_by=release_date.desc" class="btn btn-outline-primary" data-aos="fade-left">View All</a>
                </div>
                <div class="row content-slider">
                    <?php foreach (array_slice($upcomingMovies['results'], 0, 6) as $index => $movie): ?>
                        <?php if (isset($movie['poster_path'])): ?>
                            <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                                <div class="card content-card h-100">
                                    <a href="home?page=movie&id=<?= $movie['id'] ?>">
                                        <div class="card-img-wrapper">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $movie['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($movie['title']) ?></h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="release-date">
                                                    <?= date('M j, Y', strtotime($movie['release_date'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 data-aos="fade-right">Trending TV Shows</h2>
                    <a href="home?page=tvshows" class="btn btn-outline-primary" data-aos="fade-left">View All</a>
                </div>
                <div class="row content-slider">
                    <?php foreach (array_slice($trendingTVShows['results'], 0, 12) as $index => $tvshow): ?>
                        <?php if (isset($tvshow['poster_path'])): ?>
                            <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                                <div class="card content-card h-100">
                                    <a href="home?page=tvshow&id=<?= $tvshow['id'] ?>">
                                        <div class="card-img-wrapper">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $tvshow['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($tvshow['name']) ?>">
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($tvshow['name']) ?></h6>
                                            <div class="rating">
                                                <i class="fas fa-star text-warning"></i>
                                                <?= number_format($tvshow['vote_average'], 1) ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>


        <section class="py-5 bg-alt">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 data-aos="fade-right">TV Shows Airing Today</h2>
                    <a href="home?page=discover&type=tv&sort_by=popularity.desc" class="btn btn-outline-primary" data-aos="fade-left">View All</a>
                </div>
                <div class="row content-slider">
                    <?php foreach (array_slice($airingToday['results'], 0, 6) as $index => $tvshow): ?>
                        <?php if (isset($tvshow['poster_path'])): ?>
                            <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                                <div class="card content-card h-100">
                                    <a href="home?page=tvshow&id=<?= $tvshow['id'] ?>">
                                        <div class="card-img-wrapper">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $tvshow['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($tvshow['name']) ?>">
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($tvshow['name']) ?></h6>
                                            <div class="rating">
                                                <i class="fas fa-star text-warning"></i>
                                                <?= number_format($tvshow['vote_average'], 1) ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php elseif ($page == 'discover'): ?>

        <section class="py-5">
            <div class="container">
                <h1 class="mb-4">Discover <?= ucfirst($discoverType) ?>s</h1>

                <div class="card mb-4">
                    <div class="card-body">
                        <form action="home" method="GET" class="row g-3">
                            <input type="hidden" name="page" value="discover">
                            <div class="col-md-2">
                                <label for="type" class="form-label">Type</label>
                                <select name="type" id="type" class="form-select" onchange="this.form.submit()">
                                    <option value="movie" <?= $discoverType == 'movie' ? 'selected' : '' ?>>Movies</option>
                                    <option value="tv" <?= $discoverType == 'tv' ? 'selected' : '' ?>>TV Shows</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort_by" class="form-label">Sort By</label>
                                <select name="sort_by" id="sort_by" class="form-select">
                                    <option value="popularity.desc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'popularity.desc' ? 'selected' : '' ?>>Popularity (Desc)</option>
                                    <option value="popularity.asc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'popularity.asc' ? 'selected' : '' ?>>Popularity (Asc)</option>
                                    <option value="vote_average.desc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'vote_average.desc' ? 'selected' : '' ?>>Rating (Desc)</option>
                                    <option value="vote_average.asc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'vote_average.asc' ? 'selected' : '' ?>>Rating (Asc)</option>
                                    <?php if ($discoverType == 'movie'): ?>
                                        <option value="release_date.desc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'release_date.desc' ? 'selected' : '' ?>>Release Date (Desc)</option>
                                        <option value="release_date.asc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'release_date.asc' ? 'selected' : '' ?>>Release Date (Asc)</option>
                                    <?php else: ?>
                                        <option value="first_air_date.desc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'first_air_date.desc' ? 'selected' : '' ?>>First Air Date (Desc)</option>
                                        <option value="first_air_date.asc" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'first_air_date.asc' ? 'selected' : '' ?>>First Air Date (Asc)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="with_genres" class="form-label">Genre</label>
                                <select name="with_genres" id="with_genres" class="form-select">
                                    <option value="">All Genres</option>
                                    <?php if (isset($genres['genres']) && is_array($genres['genres'])): ?>
                                        <?php foreach ($genres['genres'] as $genre): ?>
                                            <option value="<?= $genre['id'] ?>" <?= isset($_GET['with_genres']) && $_GET['with_genres'] == $genre['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($genre['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="year" class="form-label">Year</label>
                                <select name="year" id="year" class="form-select">
                                    <option value="">All Years</option>
                                    <?php for ($year = date("Y"); $year >= 1900; $year--): ?>
                                        <option value="<?= $year ?>" <?= isset($_GET['year']) && $_GET['year'] == $year ? 'selected' : '' ?>>
                                            <?= $year ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="vote_average" class="form-label">Min Rating</label>
                                <select name="vote_average" id="vote_average" class="form-select">
                                    <option value="">Any Rating</option>
                                    <option value="9" <?= isset($_GET['vote_average']) && $_GET['vote_average'] == '9' ? 'selected' : '' ?>>9+</option>
                                    <option value="8" <?= isset($_GET['vote_average']) && $_GET['vote_average'] == '8' ? 'selected' : '' ?>>8+</option>
                                    <option value="7" <?= isset($_GET['vote_average']) && $_GET['vote_average'] == '7' ? 'selected' : '' ?>>7+</option>
                                    <option value="6" <?= isset($_GET['vote_average']) && $_GET['vote_average'] == '6' ? 'selected' : '' ?>>6+</option>
                                    <option value="5" <?= isset($_GET['vote_average']) && $_GET['vote_average'] == '5' ? 'selected' : '' ?>>5+</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="home?page=discover&type=<?= $discoverType ?>" class="btn btn-outline-secondary">Reset Filters</a>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="row">
                    <?php if (isset($discoverResults['results']) && count($discoverResults['results']) > 0): ?>
                        <?php foreach ($discoverResults['results'] as $index => $item): ?>
                            <?php if (isset($item['poster_path'])): ?>
                                <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="<?= $index % 6 * 50 ?>">
                                    <div class="card content-card h-100">
                                        <a href="home?page=<?= $discoverType == 'movie' ? 'movie' : 'tvshow' ?>&id=<?= $item['id'] ?>">
                                            <div class="card-img-wrapper">
                                                <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $item['poster_path'] ?>"
                                                    class="card-img-top" alt="<?= htmlspecialchars($item['title'] ?? $item['name']) ?>">
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($item['title'] ?? $item['name']) ?></h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?= isset($item['release_date'])
                                                            ? substr($item['release_date'], 0, 4)
                                                            : (isset($item['first_air_date']) ? substr($item['first_air_date'], 0, 4) : 'N/A') ?>
                                                    </small>
                                                    <span class="rating">
                                                        <i class="fas fa-star text-warning"></i>
                                                        <?= number_format($item['vote_average'], 1) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No results found with the selected filters.</div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($discoverResults['total_pages']) && $discoverResults['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= '?' . http_build_query(array_merge($_GET, ['discover_page' => $currentPage - 1])) ?>">Previous</a>
                            </li>
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($discoverResults['total_pages'], $currentPage + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= '?' . http_build_query(array_merge($_GET, ['discover_page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $currentPage >= $discoverResults['total_pages'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= '?' . http_build_query(array_merge($_GET, ['discover_page' => $currentPage + 1])) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif ($page == 'movie' && isset($_GET['id'])): ?>
        <?php
        $movieId = intval($_GET['id']);
        $movie = $tmdb->getMovieDetails($movieId);
        ?>

        <section class="content-detail">
            <?php if (isset($movie['backdrop_path'])): ?>
                <div class="backdrop" style="background-image: url('<?= TMDB_IMAGE_BASE_URL . TMDB_BACKDROP_SIZE . $movie['backdrop_path'] ?>')"></div>
            <?php endif; ?>
            <div class="container py-5">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (isset($movie['poster_path'])): ?>
                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $movie['poster_path'] ?>"
                                class="img-fluid rounded shadow" alt="<?= htmlspecialchars($movie['title']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h1 class="display-4"><?= htmlspecialchars($movie['title']) ?></h1>
                        <div class="d-flex flex-wrap align-items-center mb-4">
                            <span class="badge bg-primary me-2"><?= date('Y', strtotime($movie['release_date'])) ?></span>
                            <span class="me-3"><i class="fas fa-star text-warning"></i> <?= number_format($movie['vote_average'], 1) ?>/10</span>
                            <span class="me-3"><i class="fas fa-clock text-secondary"></i> <?= $movie['runtime'] ?> min</span>
                        </div>

                        <div class="mb-4">
                            <?php foreach ($movie['genres'] as $genre): ?>
                                <span class="badge bg-secondary me-2"><?= $genre['name'] ?></span>
                            <?php endforeach; ?>
                        </div>
                        <h5>Overview</h5>
                        <p class="lead"><?= htmlspecialchars($movie['overview']) ?></p>
                        <?php if (isset($movie['credits']) && count($movie['credits']['cast']) > 0): ?>
                            <h5 class="mt-4">Cast</h5>
                            <div class="row">
                                <?php foreach (array_slice($movie['credits']['cast'], 0, 6) as $actor): ?>
                                    <div class="col-4 col-md-2 mb-3">
                                        <div class="card cast-card h-100">
                                            <a href="home?page=person&id=<?= $actor['id'] ?>">
                                                <?php if (isset($actor['profile_path'])): ?>
                                                    <img src="<?= TMDB_IMAGE_BASE_URL . 'w185' . $actor['profile_path'] ?>"
                                                        class="card-img-top" alt="<?= htmlspecialchars($actor['name']) ?>">
                                                <?php else: ?>
                                                    <div class="no-image"><i class="fas fa-user"></i></div>
                                                <?php endif; ?>
                                                <div class="card-body p-2">
                                                    <p class="card-title small mb-0"><?= htmlspecialchars($actor['name']) ?></p>
                                                    <p class="character small text-muted"><?= htmlspecialchars($actor['character']) ?></p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($movie['videos']) && isset($movie['videos']['results']) && count($movie['videos']['results']) > 0): ?>
                            <h5 class="mt-4">Videos</h5>
                            <div class="row">
                                <?php
                                $trailer = null;
                                foreach ($movie['videos']['results'] as $video) {
                                    if ($video['type'] == 'Trailer' && $video['site'] == 'YouTube') {
                                        $trailer = $video;
                                        break;
                                    }
                                }
                                if ($trailer):
                                ?>
                                    <div class="col-md-12 mb-4">
                                        <div class="ratio ratio-16x9">
                                            <iframe src="https://www.youtube.com/embed/<?= $trailer['key'] ?>" title="<?= htmlspecialchars($trailer['name']) ?>" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (isset($movie['similar']) && isset($movie['similar']['results']) && count($movie['similar']['results']) > 0): ?>
                    <h3 class="mt-5 mb-4">Similar Movies</h3>
                    <div class="row">
                        <?php foreach (array_slice($movie['similar']['results'], 0, 6) as $similarMovie): ?>
                            <?php if (isset($similarMovie['poster_path'])): ?>
                                <div class="col-6 col-md-2 mb-4">
                                    <div class="card content-card h-100">
                                        <a href="home?page=movie&id=<?= $similarMovie['id'] ?>">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . 'w342' . $similarMovie['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($similarMovie['title']) ?>">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($similarMovie['title']) ?></h6>
                                                <div class="rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?= number_format($similarMovie['vote_average'], 1) ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif ($page == 'tvshow' && isset($_GET['id'])): ?>
        <?php
        $tvId = intval($_GET['id']);
        $tvshow = $tmdb->getTVShowDetails($tvId);
        ?>

        <section class="content-detail">
            <?php if (isset($tvshow['backdrop_path'])): ?>
                <div class="backdrop" style="background-image: url('<?= TMDB_IMAGE_BASE_URL . TMDB_BACKDROP_SIZE . $tvshow['backdrop_path'] ?>')"></div>
            <?php endif; ?>
            <div class="container py-5">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (isset($tvshow['poster_path'])): ?>
                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $tvshow['poster_path'] ?>"
                                class="img-fluid rounded shadow" alt="<?= htmlspecialchars($tvshow['name'] ?? 'TV Show') ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h1 class="display-4"><?= htmlspecialchars($tvshow['name'] ?? 'TV Show Details') ?></h1>
                        <div class="d-flex flex-wrap align-items-center mb-4">
                            <?php if (isset($tvshow['first_air_date']) && !empty($tvshow['first_air_date'])): ?>
                                <span class="badge bg-primary me-2"><?= date('Y', strtotime($tvshow['first_air_date'])) ?></span>
                            <?php endif; ?>
                            <?php if (isset($tvshow['vote_average'])): ?>
                                <span class="me-3"><i class="fas fa-star text-warning"></i> <?= number_format($tvshow['vote_average'], 1) ?>/10</span>
                            <?php endif; ?>
                            <?php if (isset($tvshow['seasons']) && is_array($tvshow['seasons'])): ?>
                                <span class="me-3"><i class="fas fa-film"></i> <?= count($tvshow['seasons']) ?> Seasons</span>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($tvshow['genres']) && is_array($tvshow['genres']) && !empty($tvshow['genres'])): ?>
                            <div class="mb-4">
                                <?php foreach ($tvshow['genres'] as $genre): ?>
                                    <span class="badge bg-secondary me-2"><?= $genre['name'] ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($tvshow['overview']) && !empty($tvshow['overview'])): ?>
                            <h5>Overview</h5>
                            <p class="lead"><?= htmlspecialchars($tvshow['overview']) ?></p>
                        <?php endif; ?>

                        <?php if (isset($tvshow['seasons']) && is_array($tvshow['seasons']) && !empty($tvshow['seasons'])): ?>
                            <h5 class="mt-4">Seasons</h5>
                            <div class="accordion" id="seasonsAccordion">
                                <?php foreach ($tvshow['seasons'] as $index => $season): ?>
                                    <?php if (isset($season['season_number']) && $season['season_number'] > 0): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading<?= $season['id'] ?>">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse<?= $season['id'] ?>" aria-expanded="false"
                                                    aria-controls="collapse<?= $season['id'] ?>">
                                                    Season <?= $season['season_number'] ?> (<?= $season['episode_count'] ?? '?' ?> Episodes)
                                                </button>
                                            </h2>
                                            <div id="collapse<?= $season['id'] ?>" class="accordion-collapse collapse"
                                                aria-labelledby="heading<?= $season['id'] ?>" data-bs-parent="#seasonsAccordion">
                                                <div class="accordion-body d-flex">
                                                    <?php if (isset($season['poster_path']) && !empty($season['poster_path'])): ?>
                                                        <img src="<?= TMDB_IMAGE_BASE_URL . 'w185' . $season['poster_path'] ?>"
                                                            class="me-3 rounded" alt="Season <?= $season['season_number'] ?>" style="max-height: 150px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h5>Season <?= $season['season_number'] ?></h5>
                                                        <?php if (isset($season['overview']) && !empty($season['overview'])): ?>
                                                            <p><?= htmlspecialchars($season['overview']) ?></p>
                                                        <?php else: ?>
                                                            <p class="text-muted">No overview available.</p>
                                                        <?php endif; ?>
                                                        <a href="home?page=season&id=<?= $tvId ?>&season=<?= $season['season_number'] ?>"
                                                            class="btn btn-sm btn-primary">View Episodes</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($tvshow['credits']) && isset($tvshow['credits']['cast']) && !empty($tvshow['credits']['cast'])): ?>
                            <h5 class="mt-4">Cast</h5>
                            <div class="row">
                                <?php foreach (array_slice($tvshow['credits']['cast'], 0, 6) as $actor): ?>
                                    <div class="col-4 col-md-2 mb-3">
                                        <div class="card cast-card h-100">
                                            <a href="home?page=person&id=<?= $actor['id'] ?>">
                                                <?php if (isset($actor['profile_path']) && !empty($actor['profile_path'])): ?>
                                                    <img src="<?= TMDB_IMAGE_BASE_URL . 'w185' . $actor['profile_path'] ?>"
                                                        class="card-img-top" alt="<?= htmlspecialchars($actor['name']) ?>">
                                                <?php else: ?>
                                                    <div class="no-image"><i class="fas fa-user"></i></div>
                                                <?php endif; ?>
                                                <div class="card-body p-2">
                                                    <p class="card-title small mb-0"><?= htmlspecialchars($actor['name']) ?></p>
                                                    <p class="character small text-muted"><?= htmlspecialchars($actor['character'] ?? 'Unknown role') ?></p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($tvshow['videos']) && isset($tvshow['videos']['results']) && !empty($tvshow['videos']['results'])): ?>
                            <h5 class="mt-4">Videos</h5>
                            <div class="row">
                                <?php
                                $trailer = null;
                                foreach ($tvshow['videos']['results'] as $video) {
                                    if ($video['type'] == 'Trailer' && $video['site'] == 'YouTube') {
                                        $trailer = $video;
                                        break;
                                    }
                                }
                                if ($trailer):
                                ?>
                                    <div class="col-md-12 mb-4">
                                        <div class="ratio ratio-16x9">
                                            <iframe src="https://www.youtube.com/embed/<?= $trailer['key'] ?>" title="<?= htmlspecialchars($trailer['name']) ?>" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (isset($tvshow['similar']) && isset($tvshow['similar']['results']) && !empty($tvshow['similar']['results'])): ?>
                    <h3 class="mt-5 mb-4">Similar TV Shows</h3>
                    <div class="row">
                        <?php foreach (array_slice($tvshow['similar']['results'], 0, 6) as $similarShow): ?>
                            <?php if (isset($similarShow['poster_path']) && !empty($similarShow['poster_path'])): ?>
                                <div class="col-6 col-md-2 mb-4">
                                    <div class="card content-card h-100">
                                        <a href="home?page=tvshow&id=<?= $similarShow['id'] ?>">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . 'w342' . $similarShow['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($similarShow['name'] ?? 'TV Show') ?>">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($similarShow['name'] ?? 'Unknown title') ?></h6>
                                                <div class="rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?= number_format($similarShow['vote_average'] ?? 0, 1) ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif ($page == 'movies'): ?>
        <?php
        $currentPage = isset($_GET['movie_page']) ? intval($_GET['movie_page']) : 1;
        $popularMovies = $tmdb->getPopularMovies($currentPage);
        $totalPages = $popularMovies['total_pages'];
        ?>

        <section class="py-5">
            <div class="container">
                <h1 class="mb-4">Popular Movies</h1>
                <div class="row">
                    <?php foreach ($popularMovies['results'] as $movie): ?>
                        <?php if (isset($movie['poster_path'])): ?>
                            <div class="col-6 col-md-3 col-lg-2 mb-4">
                                <div class="card content-card h-100">
                                    <a href="home?page=movie&id=<?= $movie['id'] ?>">
                                        <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $movie['poster_path'] ?>"
                                            class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($movie['title']) ?></h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted"><?= substr($movie['release_date'], 0, 4) ?></small>
                                                <span class="rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?= number_format($movie['vote_average'], 1) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>


                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="home?page=movies&movie_page=<?= $currentPage - 1 ?>">Previous</a>
                        </li>
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="home?page=movies&movie_page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="home?page=movies&movie_page=<?= $currentPage + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>
    <?php elseif ($page == 'tvshows'): ?>
        <?php
        $currentPage = isset($_GET['tv_page']) ? intval($_GET['tv_page']) : 1;
        $popularTVShows = $tmdb->getPopularTVShows($currentPage);
        $totalPages = isset($popularTVShows['total_pages']) ? $popularTVShows['total_pages'] : 1;
        ?>

        <section class="py-5">
            <div class="container">
                <h1 class="mb-4">Popular TV Shows</h1>
                <div class="row">
                    <?php if (isset($popularTVShows['results']) && is_array($popularTVShows['results'])): ?>
                        <?php foreach ($popularTVShows['results'] as $tvshow): ?>
                            <?php if (isset($tvshow['poster_path']) && !empty($tvshow['poster_path'])): ?>
                                <div class="col-6 col-md-3 col-lg-2 mb-4">
                                    <div class="card content-card h-100">
                                        <a href="home?page=tvshow&id=<?= $tvshow['id'] ?>">
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $tvshow['poster_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($tvshow['name']) ?>">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($tvshow['name']) ?></h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?= isset($tvshow['first_air_date']) && !empty($tvshow['first_air_date']) ?
                                                            substr($tvshow['first_air_date'], 0, 4) : 'N/A' ?>
                                                    </small>
                                                    <span class="rating">
                                                        <i class="fas fa-star text-warning"></i>
                                                        <?= number_format($tvshow['vote_average'] ?? 0, 1) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No TV shows available at this time.</div>
                        </div>
                    <?php endif; ?>
                </div>


                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="home?page=tvshows&tv_page=<?= $currentPage - 1 ?>">Previous</a>
                        </li>
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="home?page=tvshows&tv_page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="home?page=tvshows&tv_page=<?= $currentPage + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>
    <?php elseif ($page == 'person' && isset($_GET['id'])): ?>
        <?php
        $personId = intval($_GET['id']);
        $person = $tmdb->getPersonDetails($personId);
        ?>

        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <?php if (isset($person['profile_path'])): ?>
                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $person['profile_path'] ?>"
                                class="img-fluid rounded shadow" alt="<?= htmlspecialchars($person['name']) ?>">
                        <?php else: ?>
                            <div class="no-image-large rounded shadow d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-5x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="mt-4">
                            <h4>Personal Info</h4>
                            <p>
                                <strong>Known For:</strong><br>
                                <?= $person['known_for_department'] ?? 'N/A' ?>
                            </p>
                            <p>
                                <strong>Birthday:</strong><br>
                                <?= isset($person['birthday']) ? date('F j, Y', strtotime($person['birthday'])) : 'N/A' ?>
                            </p>
                            <?php if (isset($person['place_of_birth'])): ?>
                                <p>
                                    <strong>Place of Birth:</strong><br>
                                    <?= htmlspecialchars($person['place_of_birth']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (isset($person['external_ids']) && is_array($person['external_ids'])): ?>
                                <h4 class="mt-4">Social Media</h4>
                                <div class="social-links">
                                    <?php if (isset($person['external_ids']['instagram_id']) && !empty($person['external_ids']['instagram_id'])): ?>
                                        <a href="https://instagram.com/<?= $person['external_ids']['instagram_id'] ?>" target="_blank" class="me-2">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($person['external_ids']['twitter_id']) && !empty($person['external_ids']['twitter_id'])): ?>
                                        <a href="https://twitter.com/<?= $person['external_ids']['twitter_id'] ?>" target="_blank" class="me-2">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($person['external_ids']['facebook_id']) && !empty($person['external_ids']['facebook_id'])): ?>
                                        <a href="https://facebook.com/<?= $person['external_ids']['facebook_id'] ?>" target="_blank" class="me-2">
                                            <i class="fab fa-facebook"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($person['external_ids']['imdb_id']) && !empty($person['external_ids']['imdb_id'])): ?>
                                        <a href="https://www.imdb.com/name/<?= $person['external_ids']['imdb_id'] ?>" target="_blank">
                                            <i class="fab fa-imdb"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h1 class="display-4"><?= htmlspecialchars($person['name']) ?></h1>
                        <?php if (isset($person['biography']) && !empty($person['biography'])): ?>
                            <h4 class="mt-4">Biography</h4>
                            <p><?= nl2br(htmlspecialchars($person['biography'])) ?></p>
                        <?php endif; ?>
                        <?php if (isset($person['combined_credits'])): ?>
                            <h4 class="mt-4">Known For</h4>
                            <div class="row">
                                <?php
                                $knownFor = [];
                                if (isset($person['combined_credits']['cast'])) {
                                    $knownFor = $person['combined_credits']['cast'];
                                    usort($knownFor, function ($a, $b) {
                                        return $b['popularity'] <=> $a['popularity'];
                                    });
                                    $knownFor = array_slice($knownFor, 0, 8);
                                }
                                foreach ($knownFor as $credit):
                                    $isMovie = isset($credit['title']);
                                    $title = $isMovie ? $credit['title'] : $credit['name'];
                                    $linkPage = $isMovie ? 'movie' : 'tvshow';
                                ?>
                                    <?php if (isset($credit['poster_path'])): ?>
                                        <div class="col-6 col-md-3 mb-4">
                                            <div class="card content-card h-100">
                                                <a href="home?page=<?= $linkPage ?>&id=<?= $credit['id'] ?>">
                                                    <img src="<?= TMDB_IMAGE_BASE_URL . 'w342' . $credit['poster_path'] ?>"
                                                        class="card-img-top" alt="<?= htmlspecialchars($title) ?>">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?= htmlspecialchars($title) ?></h6>
                                                        <?php if (isset($credit['character']) && !empty($credit['character'])): ?>
                                                            <p class="small text-muted"><?= htmlspecialchars($credit['character']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <h4 class="mt-4">Filmography</h4>
                            <ul class="nav nav-tabs" id="creditsTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="movies-tab" data-bs-toggle="tab" data-bs-target="#movies"
                                        type="button" role="tab" aria-controls="movies" aria-selected="true">Movies</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tvshows-tab" data-bs-toggle="tab" data-bs-target="#tvshows"
                                        type="button" role="tab" aria-controls="tvshows" aria-selected="false">TV Shows</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="creditsTabContent">
                                <div class="tab-pane fade show active" id="movies" role="tabpanel" aria-labelledby="movies-tab">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th>Movie</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $movieCredits = [];
                                            if (isset($person['combined_credits']['cast'])) {
                                                foreach ($person['combined_credits']['cast'] as $credit) {
                                                    if (isset($credit['title'])) {
                                                        $movieCredits[] = $credit;
                                                    }
                                                }
                                                usort($movieCredits, function ($a, $b) {
                                                    $aYear = isset($a['release_date']) ? substr($a['release_date'], 0, 4) : 0;
                                                    $bYear = isset($b['release_date']) ? substr($b['release_date'], 0, 4) : 0;
                                                    return $bYear <=> $aYear;
                                                });
                                            }
                                            foreach ($movieCredits as $credit):
                                                $year = isset($credit['release_date']) ? substr($credit['release_date'], 0, 4) : 'N/A';
                                            ?>
                                                <tr>
                                                    <td><?= $year ?></td>
                                                    <td>
                                                        <a href="home?page=movie&id=<?= $credit['id'] ?>">
                                                            <?= htmlspecialchars($credit['title']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= isset($credit['character']) ? htmlspecialchars($credit['character']) : 'N/A' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="tvshows" role="tabpanel" aria-labelledby="tvshows-tab">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th>TV Show</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $tvCredits = [];
                                            if (isset($person['combined_credits']['cast'])) {
                                                foreach ($person['combined_credits']['cast'] as $credit) {
                                                    if (isset($credit['name'])) {
                                                        $tvCredits[] = $credit;
                                                    }
                                                }
                                                usort($tvCredits, function ($a, $b) {
                                                    $aYear = isset($a['first_air_date']) ? substr($a['first_air_date'], 0, 4) : 0;
                                                    $bYear = isset($b['first_air_date']) ? substr($b['first_air_date'], 0, 4) : 0;
                                                    return $bYear <=> $aYear;
                                                });
                                            }
                                            foreach ($tvCredits as $credit):
                                                $year = isset($credit['first_air_date']) ? substr($credit['first_air_date'], 0, 4) : 'N/A';
                                            ?>
                                                <tr>
                                                    <td><?= $year ?></td>
                                                    <td>
                                                        <a href="home?page=tvshow&id=<?= $credit['id'] ?>">
                                                            <?= htmlspecialchars($credit['name']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= isset($credit['character']) ? htmlspecialchars($credit['character']) : 'N/A' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php elseif ($page == 'season' && isset($_GET['id']) && isset($_GET['season'])): ?>
        <?php
        $tvId = intval($_GET['id']);
        $seasonNumber = intval($_GET['season']);
        $season = $tmdb->getTVSeasonDetails($tvId, $seasonNumber);
        $tvDetails = $tmdb->getTVShowDetails($tvId);
        ?>
        <section class="py-5">
            <div class="container">
                <div class="d-flex align-items-center mb-4">
                    <h1 class="mb-0"><?= htmlspecialchars($tvDetails['name'] ?? 'TV Show') ?></h1>
                    <span class="mx-2">-</span>
                    <h2 class="mb-0">Season <?= $seasonNumber ?></h2>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <?php if (isset($season['poster_path']) && !empty($season['poster_path'])): ?>
                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $season['poster_path'] ?>"
                                class="img-fluid rounded shadow" alt="Season <?= $seasonNumber ?>">
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="home?page=tvshow&id=<?= $tvId ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to TV Show
                            </a>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <?php if (isset($season['overview']) && !empty($season['overview'])): ?>
                            <div class="mb-4">
                                <h4>Overview</h4>
                                <p><?= htmlspecialchars($season['overview']) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($season['episodes']) && is_array($season['episodes']) && count($season['episodes']) > 0): ?>
                            <h4>Episodes</h4>
                            <div class="list-group">
                                <?php foreach ($season['episodes'] as $episode): ?>
                                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <h5 class="mb-1">
                                                <?= $episode['episode_number'] ?>. <?= htmlspecialchars($episode['name'] ?? 'Episode ' . $episode['episode_number']) ?>
                                            </h5>
                                            <?php if (isset($episode['air_date']) && !empty($episode['air_date'])): ?>
                                                <small><?= date('M j, Y', strtotime($episode['air_date'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <?php if (isset($episode['still_path']) && !empty($episode['still_path'])): ?>
                                                    <img src="<?= TMDB_IMAGE_BASE_URL . 'w300' . $episode['still_path'] ?>"
                                                        class="img-fluid rounded" alt="Episode <?= $episode['episode_number'] ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-9">
                                                <?php if (isset($episode['overview']) && !empty($episode['overview'])): ?>
                                                    <p class="mb-1"><?= htmlspecialchars($episode['overview']) ?></p>
                                                <?php else: ?>
                                                    <p class="mb-1 text-muted">No overview available.</p>
                                                <?php endif; ?>
                                                <div class="d-flex align-items-center mt-2">
                                                    <?php if (isset($episode['vote_average'])): ?>
                                                        <span class="badge bg-primary me-2"><?= number_format($episode['vote_average'], 1) ?></span>
                                                    <?php endif; ?>
                                                    <small class="text-muted"><?= isset($episode['runtime']) ? $episode['runtime'] : 'N/A' ?> min</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No episodes information available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php elseif ($page == 'people'): ?>
        <?php
        $currentPage = isset($_GET['people_page']) ? intval($_GET['people_page']) : 1;
        $timeWindow = isset($_GET['time_window']) ? $_GET['time_window'] : 'day';
        $searchQuery = isset($_GET['person_query']) ? $_GET['person_query'] : '';


        if (!empty($searchQuery)) {
            $peopleResults = $tmdb->searchPeople($searchQuery, $currentPage);
            $pageTitle = 'Search Results for "' . htmlspecialchars($searchQuery) . '"';
        } else {

            $peopleResults = $tmdb->getTrendingPeople($timeWindow, $currentPage);
            $pageTitle = 'Trending People';
        }

        $totalPages = isset($peopleResults['total_pages']) ? $peopleResults['total_pages'] : 1;
        ?>

        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?= $pageTitle ?></h1>
                    <?php if (empty($searchQuery)): ?>
                        <!-- Time window selector (only show when not searching) -->
                        <div class="btn-group" role="group" aria-label="Time window selector">
                            <a href="home?page=people&time_window=day&people_page=1"
                                class="btn <?= $timeWindow == 'day' ? 'btn-primary' : 'btn-outline-primary' ?>">Today</a>
                            <a href="home?page=people&time_window=week&people_page=1"
                                class="btn <?= $timeWindow == 'week' ? 'btn-primary' : 'btn-outline-primary' ?>">This Week</a>
                        </div>
                    <?php endif; ?>
                </div>


                <div class="card mb-4">
                    <div class="card-body">
                        <form action="home" method="GET" class="row g-3">
                            <input type="hidden" name="page" value="people">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="person_query" class="form-control" placeholder="Search for actors, directors, and more..."
                                        value="<?= htmlspecialchars($searchQuery) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                        <?php if (!empty($searchQuery)): ?>
                            <div class="mt-3">
                                <a href="home?page=people" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Trending
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <?php if (isset($peopleResults['results']) && count($peopleResults['results']) > 0): ?>
                        <?php foreach ($peopleResults['results'] as $index => $person): ?>
                            <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="<?= $index % 6 * 50 ?>">
                                <div class="card content-card h-100">
                                    <a href="home?page=person&id=<?= $person['id'] ?>">
                                        <?php if (isset($person['profile_path']) && $person['profile_path']): ?>
                                            <img src="<?= TMDB_IMAGE_BASE_URL . TMDB_POSTER_SIZE . $person['profile_path'] ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($person['name']) ?>">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-user fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?= htmlspecialchars($person['name']) ?></h6>
                                            <?php if (isset($person['known_for_department'])): ?>
                                                <span class="badge bg-secondary mb-2"><?= $person['known_for_department'] ?></span>
                                            <?php endif; ?>
                                            <p class="small text-muted">
                                                <?php
                                                if (isset($person['known_for']) && !empty($person['known_for'])) {
                                                    $knownTitles = array_map(function ($item) {
                                                        return isset($item['title']) ? $item['title'] : $item['name'];
                                                    }, array_slice($person['known_for'], 0, 2));
                                                    echo htmlspecialchars(implode(', ', $knownTitles));
                                                }
                                                ?>
                                            </p>
                                            <?php if (isset($person['popularity'])): ?>
                                                <div class="d-flex align-items-center mt-2">
                                                    <i class="fas fa-fire-alt text-danger me-1"></i>
                                                    <small><?= number_format($person['popularity'], 1) ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <?php if (!empty($searchQuery)): ?>
                                <div class="alert alert-info">No people found matching "<?= htmlspecialchars($searchQuery) ?>".</div>
                            <?php else: ?>
                                <div class="alert alert-info">No trending people found.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>


                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= '?' . http_build_query(array_merge($_GET, ['people_page' => $currentPage - 1])) ?>">Previous</a>
                            </li>
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= '?' . http_build_query(array_merge($_GET, ['people_page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= '?' . http_build_query(array_merge($_GET, ['people_page' => $currentPage + 1])) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>


    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">TMDB Explorer</h5>
                    <p class="small">This product uses the TMDB API but is not endorsed or certified by TMDB.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="https://www.themoviedb.org/assets/2/v4/logos/v2/blue_short-8e7b30f73a4020692ccca9c88bafe5dcb6f8a62a4c6bc55cd9ba82bb2cd95f6c.svg"
                        alt="TMDB Logo" style="max-height: 40px;">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <div class="container">
                        <p class="text-center mb-0">Created by @manases</p>
                    </div>
                    <p class="small mb-0">
                        &copy; <?php echo date("Y"); ?> Manases Kamau &amp; TMDB Explorer
                        | All Rights Reserved. |<a href="https://instagram.com/manases___" target="_blank" class="text-white">
                            <i class="fab fa-instagram"></i>
                        </a>|
                        <a href="https://github.com/manasess896" target="_blank" class="text-white">
                            GitHub
                        </a> |
                        <a href="https://code-craft-website-solutions-2d68a0b57273.herokuapp.com/contact.php" target="_blank" class="text-white">
                            Contact Us
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animations
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true
            }); // Hero Slideshow Logic
            const slides = document.querySelectorAll('.hero-slide');
            const indicators = document.querySelectorAll('.hero-indicator');
            const nextBtn = document.querySelector('.next-slide');
            const prevBtn = document.querySelector('.prev-slide');

            if (slides && slides.length > 0) {
                let currentSlide = 0;
                let isAnimating = false;
                let slideInterval = setInterval(nextSlide, 7000); // Auto slide every 7 seconds

                // Make sure all slides are properly initialized
                initSlides();

                function initSlides() {
                    slides.forEach((slide, i) => {
                        if (i === 0) {
                            slide.style.opacity = '1';
                            slide.style.visibility = 'visible';
                            slide.classList.add('active');
                        } else {
                            slide.style.opacity = '0';
                            slide.style.visibility = 'hidden';
                            slide.classList.remove('active');
                        }
                    });

                    // Set active indicator
                    indicators.forEach((indicator, i) => {
                        if (i === 0) {
                            indicator.classList.add('active');
                        } else {
                            indicator.classList.remove('active');
                        }
                    });
                }

                function nextSlide() {
                    if (isAnimating) return;
                    goToSlide((currentSlide + 1) % slides.length);
                }

                function prevSlide() {
                    if (isAnimating) return;
                    goToSlide(currentSlide === 0 ? slides.length - 1 : currentSlide - 1);
                }

                function goToSlide(n) {
                    if (currentSlide === n || isAnimating) return;
                    isAnimating = true;

                    slides[n].style.visibility = 'visible';
                    slides[n].style.opacity = '0';

                    setTimeout(() => {

                        slides[currentSlide].style.opacity = '0';
                        slides[n].style.opacity = '1';

                        slides[currentSlide].classList.remove('active');
                        indicators[currentSlide].classList.remove('active');


                        slides[n].classList.add('active');
                        indicators[n].classList.add('active');

                        const previousSlide = currentSlide;
                        currentSlide = n;

                        setTimeout(() => {
                            slides[previousSlide].style.visibility = 'hidden';
                            isAnimating = false;
                        }, 1000);
                    }, 50);
                }


                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearInterval(slideInterval);
                        nextSlide();
                        slideInterval = setInterval(nextSlide, 7000);
                    });
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        clearInterval(slideInterval);
                        prevSlide();
                        slideInterval = setInterval(nextSlide, 7000);
                    });
                }


                indicators.forEach(function(indicator, index) {
                    indicator.addEventListener('click', function() {
                        if (isAnimating) return;
                        clearInterval(slideInterval);
                        goToSlide(index);
                        slideInterval = setInterval(nextSlide, 7000);
                    });
                });


                document.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft') {
                        clearInterval(slideInterval);
                        prevSlide();
                        slideInterval = setInterval(nextSlide, 7000);
                    } else if (e.key === 'ArrowRight') {
                        clearInterval(slideInterval);
                        nextSlide();
                        slideInterval = setInterval(nextSlide, 7000);
                    }
                });


                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        clearInterval(slideInterval);
                    } else {
                        slideInterval = setInterval(nextSlide, 7000);
                    }
                });
            }
        });
    </script>


    <div class="text-center bg-dark text-white py-2" style="font-size: 12px;">
        <a href="https://github.com/Manasess896/TMDB-Explorer" target="_blank" class="text-white text-decoration-none">
            <i class="fab fa-github me-1"></i> View this project on GitHub: https://github.com/Manasess896/TMDB-Explorer
        </a>
    </div>
</body>

</html>