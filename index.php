<?php
require_once 'config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/includes/csrf.php';

require_once __DIR__ . '/includes/community-status.php';

$stmt = $pdo->prepare(
    "SELECT id, title, description, image, published_at
    FROM news
    ORDER BY published_at DESC"
);

$stmt->execute();

$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT
        comments.id,
        comments.news_id,
        comments.content,
        comments.created_at,
        users.name
    FROM comments
    INNER JOIN users
        ON comments.user_id = users.id
    ORDER BY comments.created_at DESC"
);

$stmt->execute();

$allComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$commentsByNews = [];

foreach ($allComments as $comment) {
    $commentsByNews[$comment['news_id']][] = $comment;
}

$favoriteAlbums = [];

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare(
        "SELECT album_key
        FROM album_favorites
        WHERE user_id = ?"
    );

    $stmt->execute([(int) $_SESSION['user_id']]);

    $favoriteAlbums = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Welcome to the ultimate BTS fan hub!">
    <title>BTS Fan Hub</title>

    <link rel="stylesheet" href="css/main.css">
</head>
<body>

    <header class="header">
        <div class="container">

            <div class="header__content">

                <a href="#!">
                    <img src="img/icon/logo.jpg" alt="BTS Logo" class="logo">
                </a>

                <nav class="nav">
                    <ul>
                        <li><a href="#about">About</a></li>
                        <li><a href="#members">Members</a></li>
                        <li><a href="#albums">Albums</a></li>
                        <li><a href="#timeline">Timeline</a></li>
                        <li><a href="#news">News</a></li>
                        <li><a href="#join">Join</a></li>
                    </ul>
                </nav>

                <button class="nav-button">
                    <span class="nav-button-icon"></span>
                </button>

                <div class="mobile-nav">
                    <div class="nav">
                        <ul>
                            <li><a href="#hero">Home</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#members">Members</a></li>
                            <li><a href="#albums">Albums</a></li>
                            <li><a href="#news">News</a></li>
                            <li><a href="#join">Join</a></li>
                        </ul>
                    </div>
                </div>


                <?php if (isset($_SESSION['user_id'])): ?>

                    <a href="profile.php" class="btn">Profile</a>

                    <?php if ($isCommunityMember): ?>
                        <span
                        class="community-badge"
                        title="Member of the BTS Fan Community"
                        >
                        💜ARMY
                    </span>
                    <?php endif; ?>

                    <?php else: ?>

                        <a href="login.php" class="btn">Log in</a>
                        <a href="register.php" class="btn">Register</a>

                    <?php endif; ?>
                    <button class="themeButton" aria-label="Toggle Dark/Light Theme">
                        <span class="themeButton-icon">☀</span>
                    </button>
            </div>


        </div>
    </header>

    <main>

        <section class="hero" id="hero">
            <div class="container">

                <div class="hero__content">
                    <div class="hero__text">
                        <h1 class="hero__title">BTS</h1>
                        <p class="hero__para"><span>Seven</span> members.<br> One <span>story</span>.<br> Millions of <span>hearts</span> connected
                            through music.</p>

                        <a class="btn" href="#about">Explore BTS</a>
                    </div>

                    <div class="hero__image">

                        <img class="hero__image--main" src="img/hero/hero.jpg" alt="BTS Members">

                        <img class="hero__image--all1" src="img/hero/all-members.jpg" alt="All BTS Members">
                        <img class="hero__image--all2" src="img/hero/all-members2.jpg" alt="All BTS Members 2">
                        <img class="hero__image--jh" src="img/hero/j-hope.jpg" alt="J-Hope">
                        <img class="hero__image--jm" src="img/hero/jimin.jpg" alt="Jimin">
                        <img class="hero__image--jin" src="img/hero/jin.jpg" alt="Jin">
                        <img class="hero__image--jk" src="img/hero/jk-th.jpg" alt="JK">
                        <img class="hero__image--rm" src="img/hero/rm.jpg" alt="RM">
                        <img class="hero__image--sg" src="img/hero/suga.jpg" alt="Suga">

                    </div>
                </div>

            </div>

        </section>

        <section class="about" id="about">
            <div class="container">
                <div class="about__content">

                    <div class="about__text">
                        <h2>About BTS</h2>
                        <p>BTS, also known as Bangtan Sonyeondan and Beyond the Scene, is a South Korean group formed by BIGHIT MUSIC. The group
                            made their official debut on June 13, 2013, with seven members: RM, Jin, SUGA, j-hope, Jimin, V, and Jung Kook.</p>
                        <p>From their early hip-hop roots, BTS developed a musical identity built around youth, identity, love, growth, and the
                            challenges of modern life. Their music has continued to evolve while keeping a strong connection between the members
                            and
                            their fans, known as ARMY.</p>
                        <p>Over the years, BTS became one of the most influential Korean acts in the world, earning major achievements on
                            international charts and performing at some of the world's biggest stadiums. Their message has also extended beyond
                            music through projects such as the LOVE MYSELF campaign and their appearances at the United Nations.</p>
                        <p>In 2026, BTS entered a new chapter as a complete group. Following the members' military service, they returned with
                            their fifth album ARIRANG, beginning a new era for BTS and ARMY.</p>
                    </div>

                    <div class="about__image">

                        <img src="img/about//about-main.jpg" alt="">
                    </div>

                </div>

            </div>
        </section>

        <section class="members" id="members">
            <div class="container">

                <h2>Meet the Members</h2>

                <form action="" class="members__search">
                    <label for="Search">Search Members</label>
                    <input id="Search" type="text" placeholder="Enter the name">
                </form>

                <div class="filters">
                    <button class="filter-btn" data-category="all">All</button>
                    <button class="filter-btn" data-category="rap">Rap</button>
                    <button class="filter-btn" data-category="vocal">Vocal</button>
                    <button class="filter-btn" data-category="dance">Dance</button>
                </div>

                <article class="members__cards" id="members__cards">
                </article>
            </div>

        </section>

        <section class="discography" id="albums">
            <div class="container">

                <h2>Discography</h2>
                <p class="discography__para">BTS's discography has developed through several distinct eras, moving from youthful hip-hop and school-themed releases
                to albums exploring identity, love, self-acceptance, and life in a changing world.</p>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <p class="favorites__count">
                        Favorites: <?= count($favoriteAlbums) ?>
                    </p>

                <?php else: ?>

                    <p class="favorites__count">
                        <a href="login.php">Log in</a>
                        to save your favorite albums.
                    </p>

                <?php endif; ?>

                <div class="albums">

                    <article class="album" data-album="2Cool4Skool">
                        <img class="album__img" src="img/discography/2 Cool 4 Skool.webp" alt="2 Cool 4 Skool">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>2 Cool 4 Skool</h3>
                        <p>2013</p>
                        <p>BTS's debut single album introduced the group's hip-hop-focused sound and their message about the pressures facing young
                        people.</p>
                        <a href="https://open.spotify.com/album/6egaEe9JaULuUCkihSnYlH?si=0fKfv29mRoqalvnRQ6An2Q">View tracks</a>
                    </article>

                    <article class="album" data-album="ORUL8,2?">
                        <img class="album__img" src="img/discography/ORUL8,2.jpg" alt="O!RUL8,2?">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>O!RUL8,2?</h3>
                        <p>2013</p>
                        <p>The second mini album continued BTS's school-era themes while questioning dreams, success, and the choices young people
                        make.</p>
                        <a href="https://open.spotify.com/album/6rWmdSJIaGTVtdMJQ35Lvf?si=5sJnLdYkRwCfZMgr3zNOtA">View tracks</a>
                    </article>

                    <article class="album" data-album="The Most Beautiful Moment in Life">
                        <img class="album__img" src="img/discography/The Most Beautiful Moment in Life.webp" alt="The Most Beautiful Moment in Life">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>The Most Beautiful Moment in Life</h3>
                        <p>2015 - 2016</p>
                        <p>This era marked an important musical transformation for BTS. The series explored youth, uncertainty, friendship,
                        ambition, and the complicated transition into adulthood.</p>
                        <a href="https://open.spotify.com/album/1k5bJ8l5oL5xxVBVHjil09?si=rTjwxXhxQrCD4xtuNZFyAw">View tracks</a>
                    </article>

                    <article class="album" data-album="WINGS">
                        <img class="album__img" src="img/discography/WINGS.jpg" alt="WINGS">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>WINGS</h3>
                        <p>2016</p>
                        <p>A major step forward in BTS's artistic development, WINGS combined personal stories with a darker and more mature sound.</p>
                        <a href="https://open.spotify.com/album/1vhNGBTFoaSTLbHjPGFIlF?si=CJa1YHHiSNCDOV0FFMH02Q">View tracks</a>
                    </article>

                    <article class="album" data-album="Love Yourself">
                        <img class="album__img" src="img/discography/Love Yourself.webp" alt="Love Yourself">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>Love Yourself</h3>
                        <p>2017 - 2018</p>
                        <p>The trilogy Love Yourself: Her, Tear, and Answer explored different aspects of love and eventually turned toward the
                        importance of loving oneself.</p>
                        <a href="https://open.spotify.com/album/07Rq17GzCnIdWJcyVHb57G?si=LotKW6buRTmtxJrAOBGDGQ">View tracks</a>
                    </article>

                    <article class="album" data-album="Map of the Soul: Persona">
                        <img class="album__img" src="img/discography/Map of the Soul Persona.webp" alt="Map of the Soul: Persona">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>Map of the Soul: Persona</h3>
                        <p>2019</p>
                        <p>The album explored identity and the different versions of ourselves that exist in relationships with other people. Its
                        title track, "Boy With Luv," featured Halsey.</p>
                        <a href="https://open.spotify.com/album/2KqlAl1Kl5fZvbFgJ0qFB6?si=Q_x7t6qeSgWkfolp9n7Wng">View tracks</a>
                    </article>

                    <article class="album" data-album="Map of the Soul: 7">
                        <img class="album__img" src="img/discography/Map of the Soul7.webp" alt="Map of the Soul: 7">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>Map of the Soul: 7</h3>
                        <p>2020</p>
                        <p>The album reflected on BTS's journey, bringing together themes and ideas from their previous work.</p>
                        <a href="https://open.spotify.com/album/5W1XY5ucNATjTULERvXx9j?si=NsLolMeZS1WbTGtgE38aSg">View tracks</a>
                    </article>

                    <article class="album" data-album="BE">
                        <img class="album__img" src="img/discography/BE.webp" alt="BE">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>BE</h3>
                        <p>2020</p>
                        <p>A major step forward in BTS's artistic development, WINGS combined personal stories with a darker and more mature sound.</p>
                        <a href="https://open.spotify.com/album/6nYfHQnvkvOTNHnOhDT3sr?si=i6q31_S_TJ-lpa6zEIm2Qg">View tracks</a>
                    </article>

                    <article class="album" data-album="Proof">
                        <img class="album__img" src="img/discography/Proof.webp" alt="Proof">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>Proof</h3>
                        <p>2022</p>
                        <p>An anthology album celebrating BTS's career up to that point, Proof brought together music from different stages of
                        their journey along with new tracks.</p>
                        <a href="https://open.spotify.com/album/6al2VdKbb6FIz9d7lU7WRB?si=5jzY5sCUSOyMa_kra1FyCg">View tracks</a>
                    </article>

                    <article class="album" data-album="ARIRANG">
                        <img class="album__img" src="img/discography/ARIRANG.webp" alt="ARIRANG">

                        <button class="btn__favorite">
                            <svg class="favorite" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <h3>ARIRANG</h3>
                        <p>2026</p>
                        <p>BTS's fifth album marked their long-awaited full-group return. With 14 tracks, the album reflects on the members'
                        experiences and the years they spent apart before reuniting with ARMY.</p>
                        <a href="https://open.spotify.com/album/3ukkRHDHbN8tNRPKsGZR1h?si=C8z_EAuNRdC4f8KH_WcQsg">View tracks</a>
                    </article>

                </div>
            </div>

        </section>

        <section class="timeline" id="timeline">
            <div class="container">

                <h2>BTS Timeline</h2>

                <table class="timeline__table">
                    <caption>
                        Major Events in BTS History
                    </caption>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2013</td>
                            <td>The Beginning</td>
                        </tr>
                        <tr>
                            <td>2015</td>
                            <td>Youth Era</td>
                        </tr>
                        <tr>
                            <td>2016</td>
                            <td>WINGS</td>
                        </tr>
                        <tr>
                            <td>2017 - 2018</td>
                            <td>Love yourself</td>
                        </tr>
                        <tr>
                            <td>2019 - 2020</td>
                            <td>Global Breakthrough</td>
                        </tr>
                        <tr>
                            <td>2022</td>
                            <td>A New Chapter</td>
                        </tr>
                        <tr>
                            <td>2025</td>
                            <td>Seven Members Return</td>
                        </tr>
                        <tr>
                            <td>2026</td>
                            <td>ARIRANG</td>
                        </tr>
                        <tr>
                            <td>June 2026</td>
                            <td>BTS FESTA</td>
                        </tr>
                        <tr>
                            <td>2026</td>
                            <td>ARIRANG WORLD TOUR</td>
                        </tr>
                    </tbody>
                </table>

                <a class="btn btn--timeline" href="events.php">Events</a>
            </div>

        </section>

        <section class="news" id="news">
            <div class="container">

                <h2>Latest News</h2>

                <?php if (($_SESSION['comment_status'] ?? '') === 'success'): ?>

                    <p class="success-message">
                        Your comment has been added successfully.
                    </p>

                    <?php unset($_SESSION['comment_status']); ?>

                <?php endif; ?>

                <?php
                $commentError = $_GET['comment_error'] ?? '';

                $commentErrors = [
                    'csrf' => 'Invalid security token. Please refresh the page.',
                    'invalid_id' => 'Invalid news ID.',
                    'invalid_content' => 'The comment must contain between 1 and 1000 characters.',
                    'news_not_found' => 'This news item was not found.'
                ];
                ?>

                <?php if (isset($commentErrors[$commentError])): ?>

                    <p class="error-message">
                        <?= htmlspecialchars($commentErrors[$commentError]) ?>
                    </p>

                <?php endif; ?>

                <form action="" class="news__search" id="newsForm">
                    <div class="form__field">
                        <label for="search">Search News:</label>
                        <input type="search" id="search" name="search" placeholder="Search by title or description...">
                    </div>

                    <div class="form__field">
                        <label for="sort">Sort by:</label>
                        <select name="news" id="sort">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                        </select>
                        <span class="form__error" id="members-error"></span>
                    </div>
                </form>

                <div class="news__results">

                    <?php if (!empty($news)): ?>

                        <?php foreach ($news as $item): ?>

                            <article
                                class="news__card"
                                id="news-<?= (int) $item['id'] ?>"
                            >
                            <img
                                src="<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['title']) ?>"
                            >
                            <div class="news__data">
                                <h3>
                                    <?= htmlspecialchars($item['title']) ?>
                                </h3>
                                <p>
                                    <?= nl2br(htmlspecialchars($item['description'])) ?>
                                </p>
                                <time datetime="<?= htmlspecialchars(
                                    date('Y-m-d\TH:i:s', strtotime($item['published_at']))
                                ) ?>">
                                    <?= htmlspecialchars(
                                        date('F j, Y', strtotime($item['published_at']))
                                    ) ?>
                                </time>
                            </div>

                            <div class="news__comment">

                                <h4>Comments</h4>


                                    <div class="comments">

                                        <?php $itemComments = $commentsByNews[$item['id']] ?? []; ?>

                                        <?php if (!empty($itemComments)): ?>

                                            <?php foreach ($itemComments as $comment): ?>

                                                <div class="comment">

                                                    <p class="comment__author">
                                                        <?= htmlspecialchars($comment['name']) ?>
                                                    </p>

                                                    <p class="comment__text">
                                                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                                    </p>

                                                    <time datetime="<?= htmlspecialchars(
                                                        date('Y-m-d\TH:i:s', strtotime($comment['created_at']))
                                                    ) ?>">
                                                        <?= htmlspecialchars(
                                                            date('F j, Y H:i', strtotime($comment['created_at']))
                                                        ) ?>
                                                    </time>

                                                </div>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <p>No comments yet.</p>

                                        <?php endif; ?>

                                    </div>


                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="comment-create.php" method="POST">
                                        <input type="hidden" name="news_id" value="<?= (int) $item['id'] ?>" >
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" >
                                        <div class="form__field">
                                            <label for="comment-<?= (int) $item['id'] ?>"> Your comment: </label>
                                            <textarea name="content" id="comment-<?= (int) $item['id'] ?>" maxlength="1000" required ></textarea>
                                        </div>
                                    <button type="submit" class="commet__btn"> Comment </button>
                                </form>
                                <?php else: ?>
                                    <p>
                                        <a href="login.php">Log in</a> to leave a comment.
                                    </p>
                                <?php endif; ?>
                            </div>
                            </article>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <p>No news has been published yet.</p>
                    <?php endif; ?>

                </div>

            </div>
        </section>

        <section class="join" id="join">
            <div class="container">

                <h2>Join the Fan Community</h2>

                <?php if (($_GET['join_status'] ?? '') === 'success'): ?>

                    <p class="success-message">
                        Thank you for joining the fan community!
                    </p>

                <?php endif; ?>

                <?php
                $joinError = $_GET['join_error'] ?? '';

                $joinErrors = [
                    'csrf' => 'Invalid security token. Please refresh the page.',
                    'invalid' => 'Please check your form fields and try again.',
                    'duplicate' => 'This email has already joined the community.',
                    'failed' => 'Something went wrong. Please try again.'
                ];
                ?>

                <?php if (isset($joinErrors[$joinError])): ?>

                    <p class="error-message">
                        <?= htmlspecialchars($joinErrors[$joinError]) ?>
                    </p>

                <?php endif; ?>

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <div class="community-login-notice">
                        <p>
                            Want to join the BTS Fan Community?
                            Please log in to your account first!
                        </p>

                        <a href="login.php" class="btn">
                            Log in to join
                        </a>
                    </div>

                <?php elseif ($isCommunityMember): ?>

                    <p class="community-message">
                        💜 You are already a member of the BTS Fan Community!
                    </p>

                <?php endif; ?>

                <?php if (!$isCommunityMember): ?>
                <form
                    action="join-create.php"
                    method="POST"
                    class="join__form"
                    <?= !isset($_SESSION['user_id']) ? 'onsubmit="return false;"' : '' ?>
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                    <div class="form__field">
                        <label for="name">Name:</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>
                        >
                        <span class="form__error" id="name-error"></span>
                    </div>

                    <div class="form__field">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                        <span class="form__error" id="email-error"></span>
                    </div>

                    <div class="form__field">
                        <label for="member">Favorite Member:</label>
                        <select name="member" id="member" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                            <option value="">--Please choose an option--</option>
                            <option value="rm">RM</option>
                            <option value="jk">Jung Kook</option>
                            <option value="v">V</option>
                            <option value="jimin">Jimin</option>
                            <option value="suga">Suga</option>
                            <option value="j-hope">J-Hope</option>
                            <option value="jin">Jin</option>
                        </select>
                        <span class="form__error" id="member-error"></span>
                    </div>

                    <div class="form__field">
                        <label for="song">Favorite Song:</label>
                        <input type="text" id="song" name="song" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                    </div>

                    <div class="form__field form__field--full">
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>></textarea>
                        <span class="form__error" id="message-error"></span>
                    </div>

                    <div class="form__checkbox">
                        <input type="checkbox" id="updates" name="updates" value="1" <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                        <label for="updates">I want to receive BTS updates.</label>
                    </div>

                    <span class="form__success"></span>
                    <button
                        class="form__button"
                        type="submit"
                        <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>
                    >
                        Join the community
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </section>

        <section class="facts">
            <div class="container">

                <h2>Did you know?</h2>
                <p>Here are some interesting facts about BTS!</p>

                <p class="facts__list">
                </p>

                <button class="facts__btn">Show random fact</button>
            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="container">

            <div class="footer__content">
                <p>ARMY@2026</p>

                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>

                <!-- здесь будут иконки контактов -->
                <img src="" alt="">

            </div>

        </div>
    </footer>

    <script src="js/script.js"></script>
    <script src="js/mobile-nav.js"></script>
    <script src="js/switcher-theme.js"></script>
    <script src="js/members.js"></script>
    <script src="js/search.js"></script>
    <script src="js/filter.js"></script>

    <script>
    window.favoriteConfig = {
        loggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,

        csrfToken: <?= json_encode(
            $_SESSION['csrf_token'],
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?>,

        favoriteAlbums: <?= json_encode(
            $favoriteAlbums,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?>
    };
    </script>

    <script src="js/favorite.js"></script>

    <script src="js/random-facts.js"></script>
    <!-- <script src="js/form-join.js"></script> -->
    <script src="js/news.js"></script>
    <!-- <script src="js/searchInput.js"></script> -->
</body>
</html>