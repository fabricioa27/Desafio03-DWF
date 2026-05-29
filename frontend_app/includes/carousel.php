<?php

declare(strict_types=1);

$carouselSlides = [
    [
        'image' => 'assets/images/carousel/teatro.png',
        'title' => 'Teatro & Conciertos',
        'caption' => 'Reserva butacas para espectáculos en vivo',
    ],
    [
        'image' => 'assets/images/carousel/estadio.png',
        'title' => 'Estadios',
        'caption' => 'Eventos deportivos y grandes conciertos',
    ],
    [
        'image' => 'assets/images/carousel/salon.png',
        'title' => 'Salones & Conferencias',
        'caption' => 'Espacios para galas y eventos corporativos',
    ],
    [
        'image' => 'assets/images/carousel/jardin.png',
        'title' => 'Anfiteatros al aire libre',
        'caption' => 'Experiencias únicas en entornos naturales',
    ],
];
?>
<section class="venue-carousel" aria-label="Lugares disponibles para reservar" aria-roledescription="carrusel">
    <div class="carousel-viewport">
        <div class="carousel-track" id="carouselTrack">
            <?php foreach ($carouselSlides as $i => $slide): ?>
                <figure class="carousel-slide<?= $i === 0 ? ' is-active' : '' ?>"
                        data-index="<?= $i ?>"
                        aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
                    <div class="carousel-slide__media">
                        <img src="<?= htmlspecialchars(base_url($slide['image'])) ?>"
                             alt="<?= htmlspecialchars($slide['title']) ?>"
                             loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                             decoding="async">
                    </div>
                    <figcaption class="carousel-caption">
                        <strong><?= htmlspecialchars($slide['title']) ?></strong>
                        <span><?= htmlspecialchars($slide['caption']) ?></span>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
        <button type="button" class="carousel-btn carousel-prev" id="carouselPrev" aria-label="Diapositiva anterior">
            <span aria-hidden="true">‹</span>
        </button>
        <button type="button" class="carousel-btn carousel-next" id="carouselNext" aria-label="Diapositiva siguiente">
            <span aria-hidden="true">›</span>
        </button>
        <div class="carousel-progress" aria-hidden="true">
            <div class="carousel-progress__bar" id="carouselProgress"></div>
        </div>
    </div>
    <div class="carousel-dots" id="carouselDots" role="tablist" aria-label="Seleccionar diapositiva">
        <?php foreach ($carouselSlides as $i => $slide): ?>
            <button type="button"
                    class="carousel-dot<?= $i === 0 ? ' is-active' : '' ?>"
                    data-index="<?= $i ?>"
                    role="tab"
                    aria-label="<?= htmlspecialchars($slide['title']) ?>"
                    <?= $i === 0 ? 'aria-selected="true"' : '' ?>></button>
        <?php endforeach; ?>
    </div>
</section>
